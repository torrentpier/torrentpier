<?php

// ACP Header - START
if (!empty($setmodules))
{
	$module['Forums']['Prune'] = basename(__FILE__);
	return;
}
require('./pagestart.php');
// ACP Header - END

$all_forums = -1;

$pruned_total = 0;
$prune_performed = false;

function return_msg_prune ($status_msg)
{
	return $status_msg;
}

if (isset($_REQUEST['submit']))
{
	if (!$var =& $_REQUEST['f'] OR !$f_selected = get_id_ary($var))
	{
		message_die(GENERAL_MESSAGE, return_msg_prune('Forum not selected'));
	}
	if (!$var =& $_REQUEST['prunedays'] OR !$prunedays = abs(intval($var)))
	{
		message_die(GENERAL_MESSAGE, return_msg_prune('Prune days not selected'));
	}

	$prunetime = TIMENOW - 86400*$prunedays;
	$forum_csv = in_array($all_forums, $f_selected) ? $all_forums : join(',', $f_selected);

	$where_sql = ($forum_csv != $all_forums) ? "WHERE forum_id IN($forum_csv)" : '';

	$sql = "SELECT forum_id, forum_name FROM ". BB_FORUMS ." $where_sql";

	foreach (DB()->fetch_rowset($sql) as $i => $row)
	{
		$pruned_topics = topic_delete('prune', $row['forum_id'], $prunetime, !empty($_POST['prune_all_topic_types']));
		$pruned_total += $pruned_topics;
		$prune_performed = true;

		$template->assign_block_vars('pruned', array(
			'ROW_CLASS'     => !($i % 2) ? 'row1' : 'row2',
			'FORUM_NAME'    => htmlCHR($row['forum_name']),
			'PRUNED_TOPICS' => $pruned_topics,
		));
	}
	if (!$prune_performed)
	{
		message_die(GENERAL_MESSAGE, return_msg_prune($lang['NONE_SELECTED']));
	}
	if (!$pruned_total)
	{
		message_die(GENERAL_MESSAGE, return_msg_prune($lang['NO_SEARCH_MATCH']));
	}
}

$template->assign_vars(array(
	'PRUNED_TOTAL'   => $pruned_total,
	'S_PRUNE_ACTION' => basename(__FILE__),
	'SEL_FORUM'      => get_forum_select('admin', 'f[]', null, 65, 16, '', $all_forums),
));

print_page('admin_forum_prune.tpl', 'admin');