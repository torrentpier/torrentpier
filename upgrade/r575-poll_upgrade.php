<?php

define('IN_FORUM', true);
define('BB_ROOT', './');
require(BB_ROOT . 'common.php');

$user->session_start();

set_die_append_msg();
if (!IS_SUPER_ADMIN) bb_die($lang['ONLY_FOR_SUPER_ADMIN']);

$confirm = request_var('confirm', '');

if ($confirm) {
	DB()->query("
		CREATE TABLE IF NOT EXISTS `bb_poll_users` (
		  `topic_id` int(10) unsigned NOT NULL,
		  `user_id` int(11) NOT NULL,
		  `vote_ip` varchar(32) NOT NULL,
		  `vote_dt` int(11) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`topic_id`,`user_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8
	");

	DB()->query("
		CREATE TABLE IF NOT EXISTS `bb_poll_votes` (
		  `topic_id` int(10) unsigned NOT NULL,
		  `vote_id` tinyint(4) unsigned NOT NULL,
		  `vote_text` varchar(255) NOT NULL,
		  `vote_result` mediumint(8) unsigned NOT NULL,
		  PRIMARY KEY (`topic_id`,`vote_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8
	");

	DB()->query("
		INSERT IGNORE INTO bb_poll_votes
			(topic_id, vote_id, vote_text, vote_result)
		SELECT
			topic_id, 0, vote_text, 0
		FROM bb_vote_desc;
	");

	DB()->query("
		INSERT IGNORE INTO bb_poll_votes
			(topic_id, vote_id, vote_text, vote_result)
		SELECT
			d.topic_id, r.vote_option_id, r.vote_option_text, r.vote_result
		FROM bb_vote_desc d, bb_vote_results r
		WHERE
			d.vote_id = r.vote_id;
	");

	DB()->query("
		INSERT IGNORE INTO bb_poll_users
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
} else {
	$msg = '<form method="POST">';
	$msg .= '<h1 style="color: red">!!! Перед тем как нажать на кнопку, сделайте бекап базы данных !!!</h1><br />';
	$msg .= '<input type="submit" name="confirm" value="Начать обновление Базы Данных (R575)" style="height: 30px; font:bold 14px Arial, Helvetica, sans-serif;" />';
	$msg .= '</form>';

	bb_die($msg);
}