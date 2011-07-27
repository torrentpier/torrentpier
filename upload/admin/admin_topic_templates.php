<?php

// ACP Header - START
if (!empty($setmodules))
{
	$module['TorrentPier']['Release_Templates'] = basename(__FILE__);
	return;
}
require('./pagestart.php');
// ACP Header - END

require(LANG_DIR .'lang_admin_bt.php');

$forums = DB()->fetch_rowset("
	SELECT f.forum_id, f.forum_parent, f.topic_tpl_id, f.forum_name
	FROM ". BB_CATEGORIES ." c, ". BB_FORUMS ." f
	WHERE f.cat_id = c.cat_id
	ORDER BY c.cat_order, f.forum_order
");

$tpl_ary = array();
$available_tpl_id = array(0);
$tpl_select = array($lang['TPL_NONE'] => 0);

$sql = "SELECT * FROM ". BB_TOPIC_TPL ." ORDER BY tpl_name";

foreach (DB()->fetch_rowset($sql) as $row)
{
	$tpl_ary[$row['tpl_id']] = $row;
	$available_tpl_id[] = $row['tpl_id'];

	$name = isset($lang[strtoupper('TPL_'. $row['tpl_name'])]) ? $lang[strtoupper('TPL_'. $row['tpl_name'])] : $row['tpl_desc'];
	$tpl_select[$name] = $row['tpl_id'];
}

if (isset($_POST['submit']) && @is_array($_POST['forum_tpl']))
{
	$cur_val = $new_val = array();

	foreach ($forums as $forum)
	{
		$cur_val["{$forum['forum_id']}"] = (int) $forum['topic_tpl_id'];
	}
	foreach ($_POST['forum_tpl'] as $forum_id => $tpl_id)
	{
		if (isset($cur_val["$forum_id"]) && in_array($tpl_id, $available_tpl_id))
		{
			$new_val["$forum_id"] = (int) $tpl_id;
		}
	}
	if ($new_settings = array_diff_assoc($new_val, $cur_val))
	{
		foreach ($new_settings as $forum_id => $tpl_id)
		{
			DB()->query("
				UPDATE ". BB_FORUMS ." SET
					topic_tpl_id = ". (int) $tpl_id ."
				WHERE forum_id = ". (int) $forum_id ."
			");
		}
	}

	$message = $lang['CONFIG_UPD'] .'<br /><br />';
	$message .= sprintf($lang['RETURN_CONFIG'], '<a href="'. append_sid("admin_topic_templates.php") .'">', '</a>') .'<br /><br />';
	$message .= sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="'. append_sid("index.php?pane=right") .'">', '</a>');

	message_die(GENERAL_MESSAGE, $message);
}

foreach ($forums as $i => $forum)
{
	$template->assign_block_vars('forum', array(
		'ROW_CLASS'   => !($i % 2) ? 'row4' : 'row5',
		'SF_PAD'      => ($forum['forum_parent']) ? 'padding-left: 20px;' : '',
		'TPL_SELECT'  => build_select("forum_tpl[{$forum['forum_id']}]", $tpl_select, $forum['topic_tpl_id']),
		'FORUM_CLASS' => ($forum['forum_parent']) ? 'gen' : 'gen',
		'FORUM_STYLE' => ($forum['topic_tpl_id']) ? 'font-weight: bold;' : '',
		'FORUM_ID'    => $forum['forum_id'],
		'FORUM_NAME'  => htmlCHR($forum['forum_name']),
	));
}

$template->assign_vars(array(
	'S_ACTION'      => append_sid("admin_topic_templates.php"),
));

print_page('admin_topic_templates.tpl', 'admin');
