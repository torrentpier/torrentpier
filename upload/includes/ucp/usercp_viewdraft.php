<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

if (empty($_GET[POST_USERS_URL]) || $_GET[POST_USERS_URL] == GUEST_UID) bb_die($lang['NO_USER_ID_SPECIFIED']);

if (!$profiledata = get_userdata($_GET[POST_USERS_URL])) bb_die($lang['NO_USER_ID_SPECIFIED']);

if (!$userdata['session_logged_in']) redirect("login.php?redirect={$_SERVER['REQUEST_URI']}");

if ($profiledata['user_id'] != $userdata['user_id'] && !IS_ADMIN) bb_die($lang['CANNOT_VIEW_DRAFT']);

$sql = "
	SELECT t.*, f.forum_name, f.cat_id, f.forum_parent AS parent_id, f2.forum_name AS parent_name, c.cat_title
	FROM ". BB_TOPICS." t
	LEFT JOIN ". BB_FORUMS." f ON (f.forum_id = t.forum_id)
	LEFT JOIN ". BB_CATEGORIES." c ON (f.cat_id = c.cat_id)
	LEFT JOIN ". BB_FORUMS." f2 ON (f.forum_parent = f2.forum_id)
	WHERE t.topic_poster = ". $profiledata['user_id'] ."
	AND t.is_draft = 1
";

if(!$rows = DB()->fetch_rowset($sql))
{
	bb_die($lang['NO_DRAFTS'] . '<br /><br /><a href="'. PROFILE_URL . $profiledata['user_id'] .'">'. $lang['RETURN_PROFILE'] .'</a><br /><br />'. sprintf($lang['CLICK_RETURN_INDEX'],  '<a href="index.php">', '</a>'));
}

$i = 0;
foreach ($rows as $row)
{
	$category = '<a href="'. CAT_URL .  $row['cat_id'] .'">'. $row['cat_title'] .'</a>';
	$forum = '<a href="'. FORUM_URL . $row['forum_id'] .'">'. $row['forum_name'] .'</a>';
	$topic = '<a href="'. TOPIC_URL . $row['topic_id'] .'">'. $row['topic_title'] .'</a>';

	if($row["parent_id"] != 0) $forum .= '&nbsp;<em>&raquo;</em>&nbsp;<a href="'. FORUM_URL . $row['parent_id'] .'">'. $row['parent_name'] .'</a>';

	$template->assign_block_vars('DRAFT', array(
		"ROW_CLASS"		=> ($i % 2) ? 2 : 1,
		"TOPIC_ID"		=> $row['topic_id'],
		"TOPIC"			=> $topic,
		'FORUM'			=> $forum,
		"CATEGORY"		=> $category,
		"DT_CREATE"     => bb_date($row['topic_time'], 'Y-m-d H:i'),
		"EDIT_POST"		=> make_url('posting.php?mode=editpost&p='. $row['topic_first_post_id'])
	));
	$i++;
}

$template->assign_vars(array(
	"PAGE_TITLE"	=> $lang['DRAFTS'],
	"USERNAME"		=> $profiledata['username'],
	"PROFILE"		=> profile_url(array('username' => $profiledata['username'], 'user_id' => $profiledata['user_id'])),
));

print_page('usercp_viewdraft.tpl');