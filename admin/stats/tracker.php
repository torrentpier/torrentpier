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
require(BB_ROOT . 'common.php');

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

$user->session_start();

if (!IS_ADMIN) {
    bb_die($lang['NOT_AUTHORISED']);
}

$peers_in_last_minutes = [30, 15, 5, 1];
$peers_in_last_sec_limit = 300;

$announce_interval = intval($di->config->get('announce_interval'));
$stat = array();

define('TMP_TRACKER_TABLE', 'tmp_tracker');

DB()->query("
	CREATE TEMPORARY TABLE " . TMP_TRACKER_TABLE . " (
		`topic_id` mediumint(8) unsigned NOT NULL default '0',
		`user_id` mediumint(9) NOT NULL default '0',
		`ip` char(8) binary NOT NULL default '0',
		`seeder` tinyint(1) NOT NULL default '0',
		`speed_up` mediumint(8) unsigned NOT NULL default '0',
		`speed_down` mediumint(8) unsigned NOT NULL default '0',
		`update_time` int(11) NOT NULL default '0'
	)
	SELECT
		topic_id, user_id, ip, seeder, speed_up, speed_down, update_time
	FROM " . BB_BT_TRACKER . "
");

// Peers within announce interval
$stat += DB()->fetch_row("SELECT COUNT(*) AS p_within_ann FROM " . TMP_TRACKER_TABLE . " WHERE update_time >= " . (TIMENOW - $announce_interval));
// All peers, "max_peer_time"
$stat += DB()->fetch_row("SELECT COUNT(*) AS p_all, SUM(speed_up) as speed_up, SUM(speed_down) as speed_down, UNIX_TIMESTAMP() - MIN(update_time) AS max_peer_time, UNIX_TIMESTAMP() - MAX(update_time) AS last_peer_time FROM " . TMP_TRACKER_TABLE);
// Active users
$stat += DB()->fetch_row("SELECT COUNT(DISTINCT user_id) AS u_bt_active FROM " . TMP_TRACKER_TABLE);
// All bt-users
$stat += DB()->fetch_row("SELECT COUNT(*) AS u_bt_all FROM " . BB_BT_USERS);
// All bb-users
$stat += DB()->fetch_row("SELECT COUNT(*) AS u_bb_all FROM " . BB_USERS);
// Active torrents
$stat += DB()->fetch_row("SELECT COUNT(DISTINCT topic_id) AS tor_active FROM " . TMP_TRACKER_TABLE);
// With seeder
$stat += DB()->fetch_row("SELECT COUNT(DISTINCT topic_id) AS tor_with_seeder FROM " . TMP_TRACKER_TABLE . " WHERE seeder = 1");
// All torrents
$stat += DB()->fetch_row("SELECT COUNT(*) AS tor_all, SUM(size) AS torrents_size FROM " . BB_BT_TORRENTS);

// Last xx minutes
$peers_in_last_min = array();
foreach ($peers_in_last_minutes as $t) {
    $row = DB()->fetch_row("
		SELECT COUNT(*) AS peers FROM " . TMP_TRACKER_TABLE . " WHERE update_time >= " . (TIMENOW - 60 * $t) . "
	");
    $peers_in_last_min[$t] = (int)$row['peers'];
}
// Last xx seconds
$peers_in_last_sec = array();
$rowset = DB()->fetch_rowset("SELECT COUNT(*) AS peers FROM " . TMP_TRACKER_TABLE . " GROUP BY update_time DESC LIMIT $peers_in_last_sec_limit");
foreach ($rowset as $cnt => $row) {
    $peers_in_last_sec[] = sprintf('%3s', $row['peers']) . (($cnt && !(++$cnt % 15)) ? "  \n" : '');
}

function commify_callback($matches)
{
    return commify($matches[0]);
}

function commify_ob($contents)
{
    return preg_replace_callback("#\b\d+\b#", 'commify_callback', $contents);
}

ob_start('commify_ob');

echo '<html><body><head></head>';
echo '<br /><br /><table border="1" cellspacing="0" cellpadding="6" align="center"><col width="40%"><col width="60%">';
echo "\n<tr><td align=center> users: bb-all / bt-all / bt-active </td><td align=center> $stat[u_bb_all] / $stat[u_bt_all] / <b>$stat[u_bt_active]</b> </td></tr>\n";

echo "\n
	<tr><td align=center> torrents:  all / active / with seeder </td>
	<td align=center>
		$stat[tor_all] / <b>$stat[tor_active]</b> / $stat[tor_with_seeder]
		&nbsp;
		[ " . humn_size($stat['torrents_size']) . " ]
	</td></tr>
\n";

echo "\n
	<tr><td align=center> peers: all ($stat[max_peer_time] s) / in ann interval ($announce_interval s) </td>
	<td align=center>
		$stat[p_all] / <b>$stat[p_within_ann]</b>
		&nbsp;
		[ up:   " . humn_size($stat['speed_up']) . "/s,
		  down: " . humn_size($stat['speed_down']) . "/s ]
	</td></tr>
\n";

echo "\n<tr><td align=center> peers: in last " . join(' / ', $peers_in_last_minutes) . " min</td>\n";
echo "\n<td align=center>" . join(' / ', $peers_in_last_min) . "</td></tr>\n";
echo "\n<tr><td align=center> peers in last $peers_in_last_sec_limit sec <br /> [ per second, DESC order --> ] <br /> last peer: $stat[last_peer_time] seconds ago <br /> " . date("j M H:i:s [T O]") . " </td>\n";
echo '<td align=center style="font-size: 13px; font-family: \'Courier New\',Courier,monospace;"><pre> ' . join(' ', $peers_in_last_sec) . "</pre></td></tr>\n";
echo '</table>';
echo '<div align="center"><pre>';

if ($l = sys('la')) {
    $l = explode(' ', $l);
    for ($i = 0; $i < 3; $i++) {
        $l[$i] = round($l[$i], 1);
    }
    echo "\n\n<b>loadavg: </b>$l[0] $l[1] $l[2]\n\n";
}

echo 'gen time: <b>' . sprintf('%.3f', (array_sum(explode(' ', microtime())) - TIMESTART)) . "</b> sec\n";
echo '</pre></div>';
echo '</body></html>';

DB()->query("DROP TEMPORARY TABLE " . TMP_TRACKER_TABLE);

bb_exit();
