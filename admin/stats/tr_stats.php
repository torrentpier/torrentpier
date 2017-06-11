<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

define('IN_ADMIN', true);
define('BB_ROOT', './../../');
require BB_ROOT . 'common.php';

$user->session_start();

if (!IS_ADMIN) {
    bb_die($lang['NOT_AUTHORISED']);
}

$sql[] = 'SELECT count(*) FROM `' . BB_USERS . '` WHERE `user_lastvisit` < UNIX_TIMESTAMP()-2592000';
$sql[] = 'SELECT count(*) FROM `' . BB_USERS . '` WHERE `user_lastvisit` < UNIX_TIMESTAMP()-7776000';
$sql[] = 'SELECT round(avg(size)/1048576) FROM `' . BB_BT_TORRENTS . '`';
$sql[] = 'SELECT count(*) FROM `' . BB_BT_TORRENTS . '`';
$sql[] = 'SELECT count(distinct(topic_id)) FROM `' . BB_BT_TRACKER_SNAP . '` WHERE seeders > 0';
$sql[] = 'SELECT count(distinct(topic_id)) FROM `' . BB_BT_TRACKER_SNAP . '` WHERE seeders > 5';
$sql[] = 'SELECT count(distinct(poster_id)) FROM `' . BB_BT_TORRENTS . '`';
$sql[] = 'SELECT count(distinct(poster_id)) FROM `' . BB_BT_TORRENTS . '` WHERE reg_time >= UNIX_TIMESTAMP()-2592000';

echo '<html><body><head></head>';
echo '<br /><br /><table border="1" cellspacing="0" cellpadding="6" align="center">';

foreach ($sql as $i => $query) {
    $row = mysqli_fetch_row(DB()->query($query));
    echo "<tr><td>{$lang['TR_STATS'][$i]}</td><td><b>{$row[0]}</b></td>";
}

echo '</table>';
echo '<div align="center"><pre>';

if ($l = sys('la')) {
    $l = explode(' ', $l);
    for ($i = 0; $i < 3; $i++) {
        $l[$i] = round($l[$i], 1);
    }
    echo "\n\n<b>loadavg: </b>$l[0] $l[1] $l[2]\n\n";
}

echo 'gen time: <b>' . sprintf('%.3f', array_sum(explode(' ', microtime())) - TIMESTART) . "</b> sec\n";

echo '</pre></div>';
echo '</body></html>';
