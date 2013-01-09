<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

if (empty($_GET[POST_USERS_URL]) || $_GET[POST_USERS_URL] == ANONYMOUS)
{
	bb_die($lang['NO_USER_ID_SPECIFIED']);
}
if (!$profiledata = get_userdata($_GET[POST_USERS_URL]))
{
	bb_die($lang['NO_USER_ID_SPECIFIED']);
}

if (!$userdata['session_logged_in'])
{
	redirect("login.php?redirect={$_SERVER['REQUEST_URI']}");
}

if($profiledata["user_id"] != $userdata["user_id"] && !IS_ADMIN) {
	bb_die("Нельзя смотреть чужие черновики");
}

$sql = "SELECT * FROM ". BB_TOPICS." WHERE topic_poster = ".$profiledata["user_id"]." AND is_draft = 1";

$rows = DB()->fetch_rowset($sql);

$i = 0;
foreach ($rows as $row) {
	$template->assign_block_vars("DRAFT", array(
		"ROW_CLASS"     => ($i % 2) ? 2 : 1,
		"TITLE"         => $row["topic_title"],
		"T_ID"          => $row["topic_id"],
		"DT_CREATE"     => date("d-m-Y (H:i:s)", $row["topic_time"]),
		"POST_FIRST_ID" => $row["topic_first_post_id"]
	));

	$i++;
}

$template->assign_vars(array(
	"USERNAME" => $profiledata["username"]
));

print_page('usercp_viewdraft.tpl');
