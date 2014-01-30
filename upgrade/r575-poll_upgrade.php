<?php

define('IN_FORUM', true);
define('BB_ROOT', './');
require(BB_ROOT .'common.php');

$user->session_start();

set_die_append_msg();
if (!IS_SUPER_ADMIN) bb_die($lang['ONLY_FOR_SUPER_ADMIN']);

$confirm = request_var('confirm', '');

if ($confirm)
{
	DB()->query("
		INSERT INTO bb_poll_votes
			(topic_id, vote_id, vote_text, vote_result)
		SELECT
			topic_id, 0, vote_text, 0
		FROM bb_vote_desc;
	");

	DB()->query("
		INSERT INTO bb_poll_votes
			(topic_id, vote_id, vote_text, vote_result)
		SELECT
			d.topic_id, r.vote_option_id, r.vote_option_text, r.vote_result
		FROM bb_vote_desc d, bb_vote_results r
		WHERE
			d.vote_id = r.vote_id;
	");

	DB()->query("
		INSERT INTO bb_poll_users
			(topic_id, user_id, vote_ip)
		SELECT
			d.topic_id, v.vote_user_id, v.vote_user_ip
		FROM bb_vote_desc d, bb_vote_voters v
		WHERE
			d.vote_id = v.vote_id
			AND v.vote_user_id > 0;
	");

	DB()->query("DROP TABLE IF EXISTS bb_vote_desc");
	DB()->query("DROP TABLE IF EXISTS bb_vote_results");
	DB()->query("DROP TABLE IF EXISTS bb_vote_voters");

	bb_die('<h1 style="color: green">База данных обновлена</h1>');
}
else
{
	$msg = '<form method="POST">';
	$msg .= '<h1 style="color: red">!!! Перед тем как нажать на кнопку, сделайте бекап базы данных !!!</h1><br />';
	$msg .= '<input type="submit" name="confirm" value="Начать обновление Базы Данных (R575)" style="height: 30px; font:bold 14px Arial, Helvetica, sans-serif;" />';
	$msg .= '</form>';

	bb_die($msg);
}