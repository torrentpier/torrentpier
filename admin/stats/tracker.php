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

$peers_in_last_minutes = [30, 15, 5, 1];
$peers_in_last_sec_limit = 300;

$announce_interval = (int)$bb_cfg['announce_interval'];
$stat = [];

define('TMP_TRACKER_TABLE', 'tmp_tracker');

DB()->query('
	CREATE TEMPORARY TABLE ' . TMP_TRACKER_TABLE . " (
		`topic_id` mediumint(8) unsigned NOT NULL default '0',
		`user_id` mediumint(9) NOT NULL default '0',
		`ip` char(42) binary default '0',
		`ipv6` char(42) binary default '0',
		`peer_id` char(20) binary default '0',
		`seeder` tinyint(1) NOT NULL default '0',
		`speed_up` mediumint(8) unsigned NOT NULL default '0',
		`speed_down` mediumint(8) unsigned NOT NULL default '0',
		`update_time` int(11) NOT NULL default '0'
	)
	SELECT
		topic_id, user_id, ip, ipv6, peer_id, seeder, speed_up, speed_down, update_time
	FROM " . BB_BT_TRACKER . '
');

// Peers within announce interval
$stat += DB()->fetch_row('SELECT COUNT(*) AS p_within_ann FROM ' . TMP_TRACKER_TABLE . ' WHERE update_time >= ' . (TIMENOW - $announce_interval));
// All peers, "max_peer_time"
$stat += DB()->fetch_row('SELECT COUNT(*) AS p_all, SUM(speed_up) as speed_up, SUM(speed_down) as speed_down, UNIX_TIMESTAMP() - MIN(update_time) AS max_peer_time, UNIX_TIMESTAMP() - MAX(update_time) AS last_peer_time FROM ' . TMP_TRACKER_TABLE);
// Active users
$stat += DB()->fetch_row('SELECT COUNT(DISTINCT user_id) AS u_bt_active FROM ' . TMP_TRACKER_TABLE);
// All bt-users
$stat += DB()->fetch_row('SELECT COUNT(*) AS u_bt_all FROM ' . BB_BT_USERS);
// All bb-users
$stat += DB()->fetch_row('SELECT COUNT(*) AS u_bb_all FROM ' . BB_USERS . ' WHERE user_id != ' . BOT_UID);
// Active torrents
$stat += DB()->fetch_row('SELECT COUNT(DISTINCT topic_id) AS tor_active FROM ' . TMP_TRACKER_TABLE);
// With seeder
$stat += DB()->fetch_row('SELECT COUNT(DISTINCT topic_id) AS tor_with_seeder FROM ' . TMP_TRACKER_TABLE . ' WHERE seeder = 1');
// All torrents
$stat += DB()->fetch_row('SELECT COUNT(*) AS tor_all, SUM(size) AS torrents_size FROM ' . BB_BT_TORRENTS);

// Last xx minutes
$peers_in_last_min = [];
foreach ($peers_in_last_minutes as $t) {
    $row = DB()->fetch_row('
		SELECT COUNT(*) AS peers FROM ' . TMP_TRACKER_TABLE . ' WHERE update_time >= ' . (TIMENOW - 60 * $t) . '
	');
    $peers_in_last_min[$t] = (int)$row['peers'];
}
// Last xx seconds
$peers_in_last_sec = [];
$rowset = DB()->fetch_rowset('SELECT COUNT(*) AS peers FROM ' . TMP_TRACKER_TABLE . ' ORDER BY update_time DESC LIMIT ' . $peers_in_last_sec_limit);
foreach ($rowset as $cnt => $row) {
    $peers_in_last_sec[] = sprintf('%3s', $row['peers']) . (($cnt && !(++$cnt % 15)) ? "  \n" : '');
}

// Detailed statistics for peer clients

$client_list = '';
$clients_percentage = [];
$numwant = !empty($_GET['client_numwant']) ? (int)$_GET['client_numwant'] : 100;
$client_full = !empty($_GET['client_length']) ? (int)$_GET['client_length'] : false;

if ($client_full || !$stats_cache = CACHE('tr_cache')->get('tracker_clients_stats')) {

    $rowset = DB()->fetch_rowset('SELECT peer_id AS client FROM ' . TMP_TRACKER_TABLE);

    if (!empty($rowset)) {

        $client_count = 0;

        foreach ($rowset as $cnt => $row) {
            $clientString = $client_full ? substr($row['client'], 0, $client_full) : substr($row['client'], 0, 3);
            if (!isset($clients[$clientString])) {
                $clients[$clientString] = 1;
            } else {
                $clients[$clientString]++;
            }
            $client_count++;
        }

        arsort($clients, SORT_NUMERIC);
        foreach ($clients as $client => $count) {
            $percentage = number_format(($count / $client_count) * 100, 2);
            $clients_percentage[$client] = "[$count] => $percentage%";
        }

        if (!$client_full) {
            CACHE('tr_cache')->set('tracker_clients_stats', $clients_percentage, 3600);
        }
    }
} else {
    $clients_percentage = $stats_cache;
}

$n = 1;
foreach (array_slice($clients_percentage, 0, $numwant) as $client => $value) {
    $client_list .= ($client_full) ? ("$client => $value<br/>") : "$n. " . get_user_torrent_client($client) . " $value<br/>";
    $n++;
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
		[ up:   " . humn_size($stat['speed_up']) . '/s,
		  down: ' . humn_size($stat['speed_down']) . "/s ]
	</td></tr>
\n";

echo "\n<tr><td align=center> peers: in last " . implode(' / ', $peers_in_last_minutes) . " min</td>\n";
echo "\n<td align=center>" . implode(' / ', $peers_in_last_min) . "</td></tr>\n";
echo "\n<tr><td align=center> peers in last $peers_in_last_sec_limit sec <br /> [ per second, DESC order --> ] <br /> last peer: $stat[last_peer_time] seconds ago <br /> " . date('j M H:i:s [T O]') . " </td>\n";
echo '<td align=center style="font-size: 13px; font-family: \'Courier New\',Courier,monospace;"><pre> ' . implode(' ', $peers_in_last_sec) . "</pre></td></tr>\n";
echo "\n
	<tr><td align=center> clients: </td>
	<td align=center>

    $client_list
<br/>
\n";
echo (count($clients_percentage) > $numwant) ? ('<a href="' . 'tracker.php?client_numwant=' . ($numwant + 100) . '">' . 'Show more' . '</a><br/>') : '';
echo $client_full ? '<br/><b>Get more length and numbers via modifying the parameters in the url<b>' : (!empty($client_list) ? '<a href="tracker.php?client_length=6&client_numwant=10">Peer_ids with more length (version debugging)</a>' : '');
echo '</td></tr>';
echo '</table>';
echo !$client_full ? '<p style = "text-align:right;">Simple stats for clients are being cached for one hour.</p>' : '';
echo '<div align="center"><pre>';

echo 'gen time: <b>' . sprintf('%.3f', array_sum(explode(' ', microtime())) - TIMESTART) . "</b> sec\n";
echo '</pre></div>';
echo '</body></html>';

DB()->query('DROP TEMPORARY TABLE ' . TMP_TRACKER_TABLE);

bb_exit();
