<?php

define('IN_ADMIN', true);
define('BB_ROOT', './../');
require(BB_ROOT .'common.php');

$user->session_start();

if (!IS_ADMIN) bb_die($lang['NOT_AUTHORISED']);

$sql[] = 'SELECT count(*) FROM `'.BB_USERS.'` WHERE `user_lastvisit` < UNIX_TIMESTAMP()-2592000';
$sql[] = 'SELECT count(*) FROM `'.BB_USERS.'` WHERE `user_lastvisit` < UNIX_TIMESTAMP()-7776000';
$sql[] = 'SELECT round(avg(size)/1048576) FROM `'.BB_BT_TORRENTS.'`';
$sql[] = 'SELECT count(*) FROM `'.BB_BT_TORRENTS.'`';
$sql[] = 'SELECT count(distinct(topic_id)) FROM `'.BB_BT_TRACKER_SNAP.'` WHERE seeders > 0';
$sql[] = 'SELECT count(distinct(topic_id)) FROM `'.BB_BT_TRACKER_SNAP.'` WHERE seeders > 5';
$sql[] = 'SELECT count(distinct(poster_id)) FROM `'.BB_BT_TORRENTS.'`';
$sql[] = 'SELECT count(distinct(poster_id)) FROM `'.BB_BT_TORRENTS.'` WHERE reg_time >= UNIX_TIMESTAMP()-2592000';

echo '<html><body><head></head>';
echo '
<br /><br />
<table border="1" cellspacing="0" cellpadding="6" align="center">';

foreach ($sql as $i => $query)
{
	$row = mysql_fetch_row(DB()->query($query));
	echo "<tr><td>{$lang['TR_STATS'][$i]}</td><td><b>{$row[0]}</b></td>";
}

echo '</table>';

echo '<div align="center"><pre>';

if ($l = sys('la'))
{
	$l = explode(' ', $l);
	for ($i=0; $i < 3; $i++)
	{
		$l[$i] = round($l[$i], 1);
	}
	echo "\n\n<b>loadavg: </b>$l[0] $l[1] $l[2]\n\n";
}

echo 'gen time: <b>'. sprintf('%.3f', (array_sum(explode(' ', microtime())) - TIMESTART)) ."</b> sec\n";

echo '</pre></div>';
echo '</body></html>';