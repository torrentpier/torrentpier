<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$can_edit_tpl = IS_SUPER_ADMIN;
$edit_tpl_mode = ($can_edit_tpl && !empty($_REQUEST['edit_tpl']));

// forum_data
$sql = "SELECT forum_name, allow_reg_tracker, forum_tpl_id FROM ". BB_FORUMS ." WHERE forum_id = $forum_id LIMIT 1";

if (!$forum_id OR !$f_data = DB()->fetch_row($sql))
{
	bb_die('Форум не найден');
}
// tpl_data
$tpl_data = array();
$sql = "SELECT * FROM ". BB_TOPIC_TPL ." WHERE tpl_id = {$f_data['forum_tpl_id']} LIMIT 1";

if (!$f_data['forum_tpl_id'] OR !$tpl_data = DB()->fetch_row($sql))
{
	if (!$edit_tpl_mode)
	{
		redirect(POSTING_URL . "?mode=newtopic&f=$forum_id");
	}
}

$template->assign_vars(array(
	'PAGE_TITLE'         => 'Новый релиз',
	'FORUM_NAME'         => $f_data['forum_name'],
	'FORUM_ID'           => $forum_id,
	'TPL_FORM_ACTION'    => POSTING_URL . "?mode=newtopic&amp;f=$forum_id",
	'REGULAR_TOPIC_HREF' => POSTING_URL . "?mode=newtopic&amp;f=$forum_id",
	'TOR_REQUIRED'       => $f_data['allow_reg_tracker'],
	'EDIT_TPL'           => $edit_tpl_mode,
	'CAN_EDIT_TPL'       => $can_edit_tpl,
	'EDIT_TPL_URL'       => POSTING_URL . "?mode=new_rel&amp;f=$forum_id&amp;edit_tpl=1",
));

if ($tpl_data)
{
	// tpl_rules_html
	$tpl_rules_html = '';

	if ($tpl_data['tpl_rules_post_id'])
	{
		if (!$tpl_rules_html = bbcode2html(DB()->fetch_row("SELECT post_text FROM ". BB_POSTS_TEXT ." WHERE post_id = ". $tpl_data['tpl_rules_post_id'], 'post_text')))
		{
			$tpl_data['tpl_rules_post_id'] = 0;
			DB()->query("UPDATE ". BB_TOPIC_TPL ." SET tpl_rules_post_id = 0 WHERE tpl_id = {$f_data['forum_tpl_id']} LIMIT 1");
		}
	}

	$template->assign_vars(array(
		'TPL_ID'            => $tpl_data['tpl_id'],
		'TPL_NAME'          => $tpl_data['tpl_name'],
		'TPL_SRC_FORM_VAL'  => $tpl_data['tpl_src_form'],
		'TPL_SRC_TITLE_VAL' => $tpl_data['tpl_src_title'],
		'TPL_SRC_MSG_VAL'   => $tpl_data['tpl_src_msg'],
		'TPL_RULES_HTML'    => $tpl_rules_html,
	));
}

if ($edit_tpl_mode)
{
	$template->assign_vars(array(
		'NO_TPL_ASSIGNED' => !($f_data['forum_tpl_id']),
		'TPL_SELECT'      => get_select('forum_tpl', $f_data['forum_tpl_id']),
	));

	if ($tpl_data)
	{
		$template->assign_vars(array(
			'TPL_COMMENT'             => $tpl_data['tpl_comment'],
			'TPL_RULES_POST_ID'       => $tpl_data['tpl_rules_post_id'],
			'TPL_LAST_EDIT_TIME'      => bb_date($tpl_data['tpl_last_edit_tm'], 'd-M-y H:i'),
			'TPL_LAST_EDIT_USER'      => get_username(intval($tpl_data['tpl_last_edit_by'])),
			'TPL_LAST_EDIT_TIMESTAMP' => $tpl_data['tpl_last_edit_tm'],
		));
	}
}

print_page(TEMPLATES_DIR . 'posting_tpl.tpl');