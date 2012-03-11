<?php

// ACP Header - START
if (!empty($setmodules))
{
	$module['Mods']['Release_Templates'] = basename(__FILE__);
	return;
}
require('./pagestart.php');
// ACP Header - END

require(LANG_DIR .'lang_admin_bt.php');

$mode = (string) request_var('mode', '');

if ($mode == 'templates')
{
	$sql = "SELECT * FROM ". BB_TOPIC_TPL_OLD ." ORDER BY tpl_name";

	foreach (DB()->fetch_rowset($sql) as $i => $row)
	{
		$template->assign_block_vars('tpl', array(
			'ROW_CLASS'   => !($i % 2) ? 'row4' : 'row5',
			'ID'          => $row['tpl_id'],
			'NAME'        => $row['tpl_name'],
		));
	}

    $template->assign_vars(array(
		'TPL_LIST'      => true,
		'S_ACTION'      => "admin_topic_templates.php?mode=delete",
	));
}
else if ($mode == 'add' || $mode == 'edit')
{    $template->assign_vars(array(
		'TPL'    => true,
	));

    if($mode == 'edit')
    {    	$tpl_id = (int) request_var('tpl', '');
    	if(!$tpl_id) bb_die('');

    	$row = DB()->fetch_row("SELECT * FROM ". BB_TOPIC_TPL_OLD ." WHERE tpl_id = $tpl_id");
        if(!$row) bb_die('');

        $template->assign_vars(array(
			'S_ACTION'      => "admin_topic_templates.php?mode=edit&tpl=$tpl_id",
		));
    }
    else
    {    	$template->assign_vars(array(
			'S_ACTION'      => "admin_topic_templates.php?mode=add",
		));    }

    $tpl_name =	isset($_POST['tpl_name']) ? $_POST['tpl_name'] : @$row['tpl_name'];
    $tpl_script = isset($_POST['tpl_script']) ? $_POST['tpl_script'] : @$row['tpl_script'];
    $tpl_template = isset($_POST['tpl_template']) ? $_POST['tpl_template'] : @$row['tpl_template'];
    $tpl_desc = isset($_POST['tpl_desc']) ? $_POST['tpl_desc'] : @$row['tpl_desc'];

    $template->assign_vars(array(
		'NAME'      => $tpl_name,
		'SCRIPT'    => $tpl_script,
		'TEMP'      => $tpl_template,
		'DESC'      => $tpl_desc,
	));

    if(isset($_POST['submit']))
    {
	    if($mode == 'edit')
	    {	    	DB()->query("UPDATE ". BB_TOPIC_TPL_OLD ." SET
					tpl_name = '". DB()->escape($tpl_name) ."',
					tpl_script = '". DB()->escape($tpl_script) ."',
					tpl_template = '". DB()->escape($tpl_template) ."',
					tpl_desc = '". DB()->escape($tpl_desc) ."'
				WHERE tpl_id = $tpl_id
			");
			$message = 'изменено';	    }
	    else
	    {	    	DB()->query("INSERT INTO ". BB_TOPIC_TPL_OLD ." (tpl_name, tpl_script, tpl_template, tpl_desc)
				VALUES ('". DB()->escape($tpl_name) ."', '". DB()->escape($tpl_script) ."', '". DB()->escape($tpl_template) ."', '". DB()->escape($tpl_desc) ."')");
			$message = 'добавлено';	    }

	    bb_die($message);
	}
}
else if ($mode == 'delete')
{    $tpl_ids = isset($_POST['tpl_id']) ? $_POST['tpl_id'] : bb_die('вы ничего не выбрали');

    foreach ($tpl_ids as $tpl_id)
	{
		$hidden_fields['tpl_id'][] = $tpl_id;
	}

    if (isset($_POST['confirm']))
	{
		DB()->query("DELETE ". BB_TOPIC_TPL_OLD ." WHERE tpl_id IN(". join(',', $tpl_ids) .")");
		bb_die('Удалено');
	}
	else
	{
		$names = DB()->fetch_rowset("SELECT tpl_name FROM ". BB_TOPIC_TPL_OLD ." WHERE tpl_id IN(". join(',', $tpl_ids) .") ORDER BY tpl_name", 'tpl_name');

		print_confirmation(array(
			'QUESTION'      => 'Вы уверены, что хотите удалить?',
			'ITEMS_LIST'    => join("\n</li>\n<li>\n", $names),
			'FORM_ACTION'   => "admin_topic_templates.php?mode=delete",
			'HIDDEN_FIELDS' => build_hidden_fields($hidden_fields),
		));
	}}
else
{
	$forums = DB()->fetch_rowset("
		SELECT f.forum_id, f.forum_parent, f.topic_tpl_id, f.forum_name
		FROM ". BB_CATEGORIES ." c, ". BB_FORUMS ." f
		WHERE f.cat_id = c.cat_id
		ORDER BY c.cat_order, f.forum_order
	");

	$tpl_ary = array();
	$available_tpl_id = array(0);
	$tpl_select = array($lang['TPL_NONE'] => 0);

	$sql = "SELECT * FROM ". BB_TOPIC_TPL_OLD ." ORDER BY tpl_name";

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
		$message .= sprintf($lang['RETURN_CONFIG'], '<a href="admin_topic_templates.php">', '</a>') .'<br /><br />';
		$message .= sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>');

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
		'FORUM_LIST'    => true,
		'S_ACTION'      => "admin_topic_templates.php",
	));
}
print_page('admin_topic_templates.tpl', 'admin');
