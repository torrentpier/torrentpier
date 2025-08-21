<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_ROOT', './../../');
define('IN_ADMIN', true);

require BB_ROOT . 'common.php';

$user->session_start();

if (!IS_ADMIN) {
    bb_die($lang['NOT_AUTHORISED']);
}

$sql[] = 'SELECT count(*) FROM `' . BB_USERS . '` WHERE `user_lastvisit` < UNIX_TIMESTAMP()-2592000 AND user_id NOT IN (' . EXCLUDED_USERS . ')';
$sql[] = 'SELECT count(*) FROM `' . BB_USERS . '` WHERE `user_lastvisit` < UNIX_TIMESTAMP()-7776000 AND user_id NOT IN (' . EXCLUDED_USERS . ')';
$sql[] = 'SELECT round(avg(size)) FROM `' . BB_BT_TORRENTS . '`';
$sql[] = 'SELECT count(*) FROM `' . BB_BT_TORRENTS . '`';
$sql[] = 'SELECT count(distinct(topic_id)) FROM `' . BB_BT_TRACKER_SNAP . '` WHERE seeders > 0';
$sql[] = 'SELECT count(distinct(topic_id)) FROM `' . BB_BT_TRACKER_SNAP . '` WHERE seeders > 5';
$sql[] = 'SELECT count(distinct(poster_id)) FROM `' . BB_BT_TORRENTS . '`';
$sql[] = 'SELECT count(distinct(poster_id)) FROM `' . BB_BT_TORRENTS . '` WHERE reg_time >= UNIX_TIMESTAMP()-2592000';

echo '<html><body><head></head>';
echo '<br /><br /><table border="1" cellspacing="0" cellpadding="6" align="center">';

foreach ($sql as $i => $query) {
    $result = DB()->fetch_row($query);
    $row = array_values($result)[0]; // Get first column value
    $row = ($i == 2) ? humn_size($row) : $row;
    echo "<tr><td>{$lang['TR_STATS'][$i]}</td><td><b>$row</b></td>";
}

echo '</table>';
echo '<div align="center"><pre>';

echo 'gen time: <b>' . sprintf('%.3f', array_sum(explode(' ', microtime())) - TIMESTART) . "</b> sec\n";

echo '</pre></div>';
echo '</body></html>';
