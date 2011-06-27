<?php

define('IN_ADMIN', true);
define('BB_ROOT', './../');
require(BB_ROOT .'common.php');

$user->session_start();

if (!IS_ADMIN) die('Unauthorized');

$titles[] = 'неактивные пользователи в течение 30 дней';
$titles[] = 'неактивные пользователи в течение 90 дней';
$titles[] = 'средний размер раздачи на трекере (сколько мегабайт)';
$titles[] = 'сколько у нас всего раздач на трекере';
$titles[] = 'сколько живых раздач (есть хотя бы 1 сид)';
$titles[] = 'сколько раздач где которые сидируются больше 5 сидами';
$titles[] = 'сколько у нас аплоадеров (те, кто залили хотя бы 1 раздачу)';
$titles[] = 'сколько аплоадеров за последние 30 дней';

$sql[] = 'SELECT count(*) FROM `'.BB_USERS.'` WHERE `user_lastvisit` < UNIX_TIMESTAMP()-2592000';
$sql[] = 'SELECT count(*) FROM `'.BB_USERS.'` WHERE `user_lastvisit` < UNIX_TIMESTAMP()-7776000';
$sql[] = 'SELECT round(avg(size)/1048576) FROM `'.BB_BT_TORRENTS.'`';
$sql[] = 'SELECT count(*) FROM `'.BB_BT_TORRENTS.'`';
$sql[] = 'SELECT count(distinct(topic_id)) FROM `'.BB_BT_TRACKER_SNAP.'` WHERE seeders > 0';
$sql[] = 'SELECT count(distinct(topic_id)) FROM `'.BB_BT_TRACKER_SNAP.'` WHERE seeders > 5';
$sql[] = 'SELECT count(distinct(poster_id)) FROM `'.BB_BT_TORRENTS.'`';
$sql[] = 'SELECT count(distinct(poster_id)) FROM `'.BB_BT_TORRENTS.'` WHERE reg_time >= UNIX_TIMESTAMP()-2592000';

foreach($sql as $i => $query) {
	$res = DB()->query($query) or die('Oh shit!');
	$row = mysql_fetch_row($res);
	echo "<li>{$titles[$i]} - <b>{$row[0]}</b>";
}

?>