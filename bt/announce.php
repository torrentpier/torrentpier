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

define('IN_TRACKER', true);
define('BB_ROOT', './../');
require(BB_ROOT . 'common.php');

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

/** @var \TorrentPier\Cache\Adapter $cache */
$cache = $di->cache;

if (empty($_SERVER['HTTP_USER_AGENT'])) {
    header('Location: http://127.0.0.1', true, 301);
    die;
}

// Ignore 'completed' event
if (isset($_GET['event']) && $_GET['event'] === 'completed') {
    if (DBG_LOG) {
        dbg_log(' ', '!die-event-completed');
    }
    dummy_exit(mt_rand(600, 1200));
}

$announce_interval = $di->config->get('announce_interval');
$passkey_key = $di->config->get('passkey_key');
$max_left_val = 536870912000;   // 500 GB
$max_up_down_val = 5497558138880;  // 5 TB
$max_up_add_val = 85899345920;    // 80 GB
$max_down_add_val = 85899345920;    // 80 GB

// Recover info_hash
if (isset($_GET['?info_hash']) && !isset($_GET['info_hash'])) {
    $_GET['info_hash'] = $_GET['?info_hash'];
}

// Initial request verification
if (strpos($_SERVER['REQUEST_URI'], 'scrape') !== false) {
    msg_die('Please disable SCRAPE!');
}
if (!isset($_GET[$passkey_key]) || !is_string($_GET[$passkey_key]) || strlen($_GET[$passkey_key]) != BT_AUTH_KEY_LENGTH) {
    msg_die('Please LOG IN and REDOWNLOAD this torrent (passkey not found)');
}

// Input var names
// String
$input_vars_str = array(
    'info_hash',
    'peer_id',
    'event',
    $passkey_key,
);
// Numeric
$input_vars_num = array(
    'port',
    'uploaded',
    'downloaded',
    'left',
    'numwant',
    'compact',
);

// Init received data
// String
foreach ($input_vars_str as $var_name) {
    $$var_name = isset($_GET[$var_name]) ? (string)$_GET[$var_name] : null;
}
// Numeric
foreach ($input_vars_num as $var_name) {
    $$var_name = isset($_GET[$var_name]) ? (float)$_GET[$var_name] : null;
}
// Passkey
$passkey = isset($$passkey_key) ? $$passkey_key : null;

// Verify request
// Required params (info_hash, peer_id, port, uploaded, downloaded, left, passkey)
if (!isset($info_hash) || strlen($info_hash) != 20) {
    msg_die('Invalid info_hash');
}
if (!isset($peer_id) || strlen($peer_id) != 20) {
    msg_die('Invalid peer_id');
}
if (!isset($port) || $port < 0 || $port > 0xFFFF) {
    msg_die('Invalid port');
}
if (!isset($uploaded) || $uploaded < 0 || $uploaded > $max_up_down_val || $uploaded == 1844674407370) {
    msg_die('Invalid uploaded value');
}
if (!isset($downloaded) || $downloaded < 0 || $downloaded > $max_up_down_val || $downloaded == 1844674407370) {
    msg_die('Invalid downloaded value');
}
if (!isset($left) || $left < 0 || $left > $max_left_val) {
    msg_die('Invalid left value');
}
if (!verify_id($passkey, BT_AUTH_KEY_LENGTH)) {
    msg_die('Invalid passkey');
}

// IP
$ip = $_SERVER['REMOTE_ADDR'];

if (!$di->config->get('ignore_reported_ip') && isset($_GET['ip']) && $ip !== $_GET['ip']) {
    if (!$di->config->get('verify_reported_ip')) {
        $ip = $_GET['ip'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        foreach ($matches[0] as $x_ip) {
            if ($x_ip === $_GET['ip']) {
                if (!$di->config->get('allow_internal_ip') && preg_match("#^(10|172\.16|192\.168)\.#", $x_ip)) {
                    break;
                }
                $ip = $x_ip;
                break;
            }
        }
    }
}
// Check that IP format is valid
if (!verify_ip($ip)) {
    msg_die("Invalid IP: $ip");
}
// Convert IP to HEX format
$ip_sql = encode_ip($ip);

// Peer unique id
$peer_hash = md5(
    rtrim($info_hash, ' ') . $passkey . $ip . $port
);

// Get cached peer info from previous announce (last peer info)
$lp_info = $cache->get(PEER_HASH_PREFIX . $peer_hash);

if (DBG_LOG) {
    dbg_log(' ', '$lp_info-get_from-CACHE-' . ($lp_info ? 'hit' : 'miss'));
}

// Drop fast announce
if ($lp_info && (!isset($event) || $event !== 'stopped')) {
    drop_fast_announce($lp_info);
}

// Functions
function drop_fast_announce($lp_info)
{
    global $announce_interval;

    if ($lp_info['update_time'] < (TIMENOW - $announce_interval + 60)) {
        return;  // if announce interval correct
    }

    $new_ann_intrv = $lp_info['update_time'] + $announce_interval - TIMENOW;

    dummy_exit($new_ann_intrv);
}

function msg_die($msg)
{
    if (DBG_LOG) {
        dbg_log(' ', '!die-' . clean_filename($msg));
    }

    $output = \Rych\Bencode\Bencode::encode([
#		'interval'        => (int) 1800,
        'min interval' => (int)1800,
#		'peers'           => (string) DUMMY_PEER,
        'failure reason' => (string)$msg,
        'warning message' => (string)$msg,
    ]);

    die($output);
}

// Start announcer
define('TR_ROOT', './');
require(TR_ROOT . 'includes/init_tr.php');

$seeder = ($left == 0) ? 1 : 0;
$stopped = ($event === 'stopped');

// Stopped event
if ($stopped) {
    $cache->delete(PEER_HASH_PREFIX . $peer_hash);
    if (DBG_LOG) {
        dbg_log(' ', 'stopped');
    }
}

// Get last peer info from DB
if (!$lp_info) {
    $lp_info = DB()->fetch_row("
		SELECT * FROM " . BB_BT_TRACKER . " WHERE peer_hash = '$peer_hash' LIMIT 1
	");

    if (DBG_LOG) {
        dbg_log(' ', '$lp_info-get_from-DB-' . ($lp_info ? 'hit' : 'miss'));
    }
}

if ($lp_info) {
    if (!$stopped) {
        drop_fast_announce($lp_info);
    }

    $user_id = $lp_info['user_id'];
    $topic_id = $lp_info['topic_id'];
    $releaser = $lp_info['releaser'];
    $tor_type = $lp_info['tor_type'];
} else {
    // Verify if torrent registered on tracker and user authorized
    $info_hash_sql = rtrim(DB()->escape($info_hash), ' ');
    $passkey_sql = DB()->escape($passkey);

    $sql = "
		SELECT tor.topic_id, tor.poster_id, tor.tor_type, u.*
		FROM " . BB_BT_TORRENTS . " tor
		LEFT JOIN " . BB_BT_USERS . " u ON u.auth_key = '$passkey_sql'
		WHERE tor.info_hash = '$info_hash_sql'
		LIMIT 1
	";

    $row = DB()->fetch_row($sql);

    if (empty($row['topic_id'])) {
        msg_die('Torrent not registered, info_hash = ' . bin2hex($info_hash_sql));
    }
    if (empty($row['user_id'])) {
        msg_die('Please LOG IN and REDOWNLOAD this torrent (user not found)');
    }

    $user_id = $row['user_id'];
    $topic_id = $row['topic_id'];
    $releaser = (int)($user_id == $row['poster_id']);
    $tor_type = $row['tor_type'];

    // Ratio limits
    if ((TR_RATING_LIMITS || $tr_cfg['limit_concurrent_ips']) && !$stopped) {
        $user_ratio = ($row['u_down_total'] && $row['u_down_total'] > MIN_DL_FOR_RATIO) ? ($row['u_up_total'] + $row['u_up_release'] + $row['u_up_bonus']) / $row['u_down_total'] : 1;
        $rating_msg = '';

        if (!$seeder) {
            foreach ($rating_limits as $ratio => $limit) {
                if ($user_ratio < $ratio) {
                    $tr_cfg['limit_active_tor'] = 1;
                    $tr_cfg['limit_leech_count'] = $limit;
                    $rating_msg = " (ratio < $ratio)";
                    break;
                }
            }
        }

        // Limit active torrents
        if (!$di->config->get('unlimited_users.' . $user_id) && $tr_cfg['limit_active_tor'] && (($tr_cfg['limit_seed_count'] && $seeder) || ($tr_cfg['limit_leech_count'] && !$seeder))) {
            $sql = "SELECT COUNT(DISTINCT topic_id) AS active_torrents
				FROM " . BB_BT_TRACKER . "
				WHERE user_id = $user_id
					AND seeder = $seeder
					AND topic_id != $topic_id";

            if (!$seeder && $tr_cfg['leech_expire_factor'] && $user_ratio < 0.5) {
                $sql .= " AND update_time > " . (TIMENOW - 60 * $tr_cfg['leech_expire_factor']);
            }
            $sql .= "	GROUP BY user_id";

            if ($row = DB()->fetch_row($sql)) {
                if ($seeder && $tr_cfg['limit_seed_count'] && $row['active_torrents'] >= $tr_cfg['limit_seed_count']) {
                    msg_die('Only ' . $tr_cfg['limit_seed_count'] . ' torrent(s) allowed for seeding');
                } elseif (!$seeder && $tr_cfg['limit_leech_count'] && $row['active_torrents'] >= $tr_cfg['limit_leech_count']) {
                    msg_die('Only ' . $tr_cfg['limit_leech_count'] . ' torrent(s) allowed for leeching' . $rating_msg);
                }
            }
        }

        // Limit concurrent IPs
        if ($tr_cfg['limit_concurrent_ips'] && (($tr_cfg['limit_seed_ips'] && $seeder) || ($tr_cfg['limit_leech_ips'] && !$seeder))) {
            $sql = "SELECT COUNT(DISTINCT ip) AS ips
				FROM " . BB_BT_TRACKER . "
				WHERE topic_id = $topic_id
					AND user_id = $user_id
					AND seeder = $seeder
					AND ip != '$ip_sql'";

            if (!$seeder && $tr_cfg['leech_expire_factor']) {
                $sql .= " AND update_time > " . (TIMENOW - 60 * $tr_cfg['leech_expire_factor']);
            }
            $sql .= "	GROUP BY topic_id";

            if ($row = DB()->fetch_row($sql)) {
                if ($seeder && $tr_cfg['limit_seed_ips'] && $row['ips'] >= $tr_cfg['limit_seed_ips']) {
                    msg_die('You can seed only from ' . $tr_cfg['limit_seed_ips'] . " IP's");
                } elseif (!$seeder && $tr_cfg['limit_leech_ips'] && $row['ips'] >= $tr_cfg['limit_leech_ips']) {
                    msg_die('You can leech only from ' . $tr_cfg['limit_leech_ips'] . " IP's");
                }
            }
        }
    }
}

// Up/Down speed
$speed_up = $speed_down = 0;

if ($lp_info && $lp_info['update_time'] < TIMENOW) {
    if ($uploaded > $lp_info['uploaded']) {
        $speed_up = ceil(($uploaded - $lp_info['uploaded']) / (TIMENOW - $lp_info['update_time']));
    }
    if ($downloaded > $lp_info['downloaded']) {
        $speed_down = ceil(($downloaded - $lp_info['downloaded']) / (TIMENOW - $lp_info['update_time']));
    }
}

// Up/Down addition
$up_add = ($lp_info && $uploaded > $lp_info['uploaded']) ? $uploaded - $lp_info['uploaded'] : 0;
$down_add = ($lp_info && $downloaded > $lp_info['downloaded']) ? $downloaded - $lp_info['downloaded'] : 0;

// Gold / silver releases
if ($tr_cfg['gold_silver_enabled'] && $down_add) {
    if ($tor_type == TOR_TYPE_GOLD) {
        $down_add = 0;
    } // Silver releases
    elseif ($tor_type == TOR_TYPE_SILVER) {
        $down_add = ceil($down_add / 2);
    }
}

// Freeleech
if ($tr_cfg['freeleech'] && $down_add) {
    $down_add = 0;
}

// Insert / update peer info
$peer_info_updated = false;
$update_time = ($stopped) ? 0 : TIMENOW;

if ($lp_info) {
    $sql = "UPDATE " . BB_BT_TRACKER . " SET update_time = $update_time";

    $sql .= ", seeder = $seeder";
    $sql .= ($releaser != $lp_info['releaser']) ? ", releaser = $releaser" : '';

    $sql .= ($tor_type != $lp_info['tor_type']) ? ", tor_type = $tor_type" : '';

    $sql .= ($uploaded != $lp_info['uploaded']) ? ", uploaded = $uploaded" : '';
    $sql .= ($downloaded != $lp_info['downloaded']) ? ", downloaded = $downloaded" : '';
    $sql .= ", remain = $left";

    $sql .= ($up_add) ? ", up_add = up_add + $up_add" : '';
    $sql .= ($down_add) ? ", down_add = down_add + $down_add" : '';

    $sql .= ", speed_up = $speed_up";
    $sql .= ", speed_down = $speed_down";

    $sql .= " WHERE peer_hash = '$peer_hash'";
    $sql .= " LIMIT 1";

    DB()->query($sql);

    $peer_info_updated = DB()->affected_rows();

    if (DBG_LOG) {
        dbg_log(' ', 'this_peer-update' . ($peer_info_updated ? '' : '-FAIL'));
    }
}

if (!$lp_info || !$peer_info_updated) {
    $columns = 'peer_hash,    topic_id,  user_id,   ip,       port,  seeder,  releaser, tor_type,  uploaded,  downloaded, remain, speed_up,  speed_down,  up_add,  down_add,  update_time';
    $values = "'$peer_hash', $topic_id, $user_id, '$ip_sql', $port, $seeder, $releaser, $tor_type, $uploaded, $downloaded, $left, $speed_up, $speed_down, $up_add, $down_add, $update_time";

    DB()->query("REPLACE INTO " . BB_BT_TRACKER . " ($columns) VALUES ($values)");

    if (DBG_LOG) {
        dbg_log(' ', 'this_peer-insert');
    }
}

// Exit if stopped
if ($stopped) {
    silent_exit();
}

// Store peer info in cache
$lp_info = array(
    'downloaded' => (float)$downloaded,
    'releaser' => (int)$releaser,
    'seeder' => (int)$seeder,
    'topic_id' => (int)$topic_id,
    'update_time' => (int)TIMENOW,
    'uploaded' => (float)$uploaded,
    'user_id' => (int)$user_id,
    'tor_type' => (int)$tor_type,
);

$lp_info_cached = $cache->set(PEER_HASH_PREFIX . $peer_hash, $lp_info, PEER_HASH_EXPIRE);

if (DBG_LOG && !$lp_info_cached) {
    dbg_log(' ', '$lp_info-caching-FAIL');
}

// Get cached output
$output = $cache->get(PEERS_LIST_PREFIX . $topic_id);

if (DBG_LOG) {
    dbg_log(' ', '$output-get_from-CACHE-' . ($output !== false ? 'hit' : 'miss'));
}

if (!$output) {
    // Retrieve peers
    $numwant = (int)$tr_cfg['numwant'];
    $compact_mode = ($tr_cfg['compact_mode'] || !empty($compact));

    $rowset = DB()->fetch_rowset("
		SELECT ip, port
		FROM " . BB_BT_TRACKER . "
		WHERE topic_id = $topic_id
		ORDER BY RAND()
		LIMIT $numwant
	");

    if ($compact_mode) {
        $peers = '';

        foreach ($rowset as $peer) {
            $peers .= pack('Nn', ip2long(decode_ip($peer['ip'])), $peer['port']);
        }
    } else {
        $peers = array();

        foreach ($rowset as $peer) {
            $peers[] = array(
                'ip' => decode_ip($peer['ip']),
                'port' => intval($peer['port']),
            );
        }
    }

    $seeders = 0;
    $leechers = 0;

    if ($tr_cfg['scrape']) {
        $row = DB()->fetch_row("
			SELECT seeders, leechers
			FROM " . BB_BT_TRACKER_SNAP . "
			WHERE topic_id = $topic_id
			LIMIT 1
		");

        $seeders = $row['seeders'];
        $leechers = $row['leechers'];
    }

    $output = array(
        'interval' => (int)$announce_interval,
        'min interval' => (int)$announce_interval,
        'peers' => $peers,
        'complete' => (int)$seeders,
        'incomplete' => (int)$leechers,
    );

    $peers_list_cached = $cache->set(PEERS_LIST_PREFIX . $topic_id, $output, PEERS_LIST_EXPIRE);

    if (DBG_LOG && !$peers_list_cached) {
        dbg_log(' ', '$output-caching-FAIL');
    }
}

// Return data to client
echo \Rych\Bencode\Bencode::encode($output);

tracker_exit();
exit;
