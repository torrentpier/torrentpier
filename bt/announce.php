<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('IN_TRACKER', true);
define('BB_ROOT', './../');
require dirname(__DIR__) . '/common.php';

global $bb_cfg;

if (empty($_SERVER['HTTP_USER_AGENT'])) {
    header('Location: http://127.0.0.1', true, 301);
    die;
}

$announce_interval = $bb_cfg['announce_interval'];
$passkey_key = $bb_cfg['passkey_key'];

// Recover info_hash
if (isset($_GET['?info_hash']) && !isset($_GET['info_hash'])) {
    $_GET['info_hash'] = $_GET['?info_hash'];
}

// Initial request verification
if (strpos($_SERVER['REQUEST_URI'], 'scrape') !== false) {
    msg_die('Please disable SCRAPE!');
}
if (!isset($_GET[$passkey_key]) || !is_string($_GET[$passkey_key]) || strlen($_GET[$passkey_key]) !== BT_AUTH_KEY_LENGTH) {
    msg_die('Please LOG IN and RE-DOWNLOAD this torrent (passkey not found)');
}

// Input var names
// String
$input_vars_str = ['info_hash', 'peer_id', 'event', $passkey_key];
// Numeric
$input_vars_num = ['port', 'uploaded', 'downloaded', 'left', 'numwant', 'compact'];

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
$passkey = $$passkey_key ?? null;

// Verify request
// Required params (info_hash, peer_id, port, uploaded, downloaded, left, passkey)
if (!isset($peer_id)) {
    msg_die('peer_id was not provided');
}
if (strlen($peer_id) !== 20) {
    msg_die('Invalid peer_id: ' . $peer_id);
}

// Verify info_hash
if (!isset($info_hash)) {
    msg_die('info_hash was not provided');
}

// Store info hash in hex format
$info_hash_hex = bin2hex($info_hash);
// Store peer id
$peer_id_sql = rtrim(DB()->escape(substr($peer_id, 0, 10)), ' ');
// Check info_hash version
if (strlen($info_hash) === 32) {
    $is_bt_v2 = true;
} elseif (strlen($info_hash) === 20) {
    $is_bt_v2 = false;
} else {
    msg_die('Invalid info_hash: ' . $info_hash_hex);
}

if (!isset($port) || $port < 0 || $port > 0xFFFF) {
    msg_die('Invalid port: ' . $port);
}
if (!isset($uploaded) || $uploaded < 0) {
    msg_die('Invalid uploaded value: ' . $uploaded);
}
if (!isset($downloaded) || $downloaded < 0) {
    msg_die('Invalid downloaded value: ' . $downloaded);
}
if (!isset($left) || $left < 0) {
    msg_die('Invalid left value: ' . $left);
}
if (!verify_id($passkey, BT_AUTH_KEY_LENGTH)) {
    msg_die('Invalid passkey: ' . $passkey);
}

// IP
$ip = $_SERVER['REMOTE_ADDR'];

if (!$bb_cfg['ignore_reported_ip'] && isset($_GET['ip']) && $ip !== $_GET['ip']) {
    if (!$bb_cfg['verify_reported_ip']) {
        $ip = $_GET['ip'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        foreach ($matches[0] as $x_ip) {
            if ($x_ip === $_GET['ip']) {
                if (!$bb_cfg['allow_internal_ip'] && preg_match("#(127\.([0-9]{1,3}\.){2}[0-9]{1,3}|10\.([0-9]{1,3}\.){2}[0-9]{1,3}|172\.[123][0-9]\.[0-9]{1,3}\.[0-9]{1,3}|192\.168\.[0-9]{1,3}\.[0-9]{1,3})#", $x_ip)) {
                    break;
                }
                $ip = $x_ip;
                break;
            }
        }
    }
}

// Check that IP format is valid
if (!\TorrentPier\Helpers\IPHelper::isValid($ip)) {
    msg_die("Invalid IP: $ip");
}

// Convert IP to long format
$ip_sql = \TorrentPier\Helpers\IPHelper::ip2long($ip);

// Peer unique id
$peer_hash = hash('xxh128', rtrim($info_hash, ' ') . $passkey . $ip . $port);
// Events
$stopped = ($event === 'stopped');

// Set seeder & complete
$complete = $seeder = ($left == 0) ? 1 : 0;

// Get cached peer info from previous announce (last peer info)
$lp_info = CACHE('tr_cache')->get(PEER_HASH_PREFIX . $peer_hash);

// Stopped event, slice peer's cache life to 30 seconds
if ($stopped && $lp_info) {
    CACHE('tr_cache')->set(PEER_HASH_PREFIX . $peer_hash, $lp_info, 30);
}

// Drop fast announce
if ($lp_info && (!isset($event) || !$stopped)) {
    if ($lp_cached_peers = CACHE('tr_cache')->get(PEERS_LIST_PREFIX . $lp_info['topic_id'])) {
        drop_fast_announce($lp_info, $lp_cached_peers); // Use cache but with new calculated interval and seed, peer count set
    }
}

// Get last peer info from DB
if (!CACHE('tr_cache')->used && !$lp_info) {
    $lp_info = DB()->fetch_row("
		SELECT * FROM " . BB_BT_TRACKER . " WHERE peer_hash = '$peer_hash' LIMIT 1
	");
}

if ($lp_info) {
    $user_id = $lp_info['user_id'];
    $topic_id = $lp_info['topic_id'];
    $releaser = $lp_info['releaser'];
    $tor_type = $lp_info['tor_type'];
} else {
    /**
     * Поскольку торрент-клиенты в настоящее время обрезают инфо-хэш до 20 символов (независимо от его типа, как известно v1 = 20 символов, а v2 = 32 символа),
     * то результатов $is_bt_v2 (исходя из длины строки определяем тип инфо-хэша) проверки нам будет мало, именно поэтому происходит поиск v2 хэша, если торрент является v1 (по длине) и если в tor.info_hash столбце нету v1 хэша.
     */
    $info_hash_sql = rtrim(DB()->escape($info_hash), ' ');
    $info_hash_where = $is_bt_v2 ? "WHERE tor.info_hash_v2 = '$info_hash_sql'" : "WHERE tor.info_hash = '$info_hash_sql' OR tor.info_hash_v2 LIKE '$info_hash_sql%'";
    $passkey_sql = DB()->escape($passkey);

    $sql = "
		SELECT tor.topic_id, tor.poster_id, tor.tor_type, tor.info_hash, tor.info_hash_v2, u.*
		FROM " . BB_BT_TORRENTS . " tor
		LEFT JOIN " . BB_BT_USERS . " u ON u.auth_key = '$passkey_sql'
		$info_hash_where
		LIMIT 1
	";
    $row = DB()->fetch_row($sql);

    // Verify if torrent registered on tracker and user authorized
    if (empty($row['topic_id'])) {
        msg_die('Torrent not registered, info_hash = ' . $info_hash_hex);
    }
    if (empty($row['user_id'])) {
        msg_die('Please LOG IN and RE-DOWNLOAD this torrent (user not found)');
    }

    // Assign variables
    $user_id = $row['user_id'];
    $topic_id = $row['topic_id'];
    $releaser = (int)($user_id == $row['poster_id']);
    $tor_type = $row['tor_type'];

    // Check hybrid torrents
    if (!empty($row['info_hash'], $row['info_hash_v2'])) {
        // Helpful dev variables
        $is_hybrid = true;
        $hybrid_v1_hash = &$row['info_hash'];
        $hybrid_v2_hash = &$row['info_hash_v2'];
        if ($info_hash === $hybrid_v1_hash) {
            $hybrid_tor_update = true;
        }
    }

    // Ratio limits
    if ((TR_RATING_LIMITS || $bb_cfg['tracker']['limit_concurrent_ips']) && !$stopped) {
        $user_ratio = ($row['u_down_total'] && $row['u_down_total'] > MIN_DL_FOR_RATIO) ? ($row['u_up_total'] + $row['u_up_release'] + $row['u_up_bonus']) / $row['u_down_total'] : 1;
        $rating_msg = '';

        if (!$seeder) {
            foreach ($bb_cfg['rating'] as $ratio => $limit) {
                if ($user_ratio < $ratio) {
                    $bb_cfg['tracker']['limit_active_tor'] = 1;
                    $bb_cfg['tracker']['limit_leech_count'] = $limit;
                    $rating_msg = " (ratio < $ratio)";
                    break;
                }
            }
        }

        // Limit active torrents
        if (!isset($bb_cfg['unlimited_users'][$user_id]) && $bb_cfg['tracker']['limit_active_tor'] && (($bb_cfg['tracker']['limit_seed_count'] && $seeder) || ($bb_cfg['tracker']['limit_leech_count'] && !$seeder))) {
            $sql = "SELECT COUNT(DISTINCT topic_id) AS active_torrents
				FROM " . BB_BT_TRACKER . "
				WHERE user_id = $user_id
					AND seeder = $seeder
					AND topic_id != $topic_id";

            if (!$seeder && $bb_cfg['tracker']['leech_expire_factor'] && $user_ratio < 0.5) {
                $sql .= " AND update_time > " . (TIMENOW - 60 * $bb_cfg['tracker']['leech_expire_factor']);
            }
            $sql .= " GROUP BY user_id";

            if ($row = DB()->fetch_row($sql)) {
                if ($seeder && $bb_cfg['tracker']['limit_seed_count'] && $row['active_torrents'] >= $bb_cfg['tracker']['limit_seed_count']) {
                    msg_die('Only ' . $bb_cfg['tracker']['limit_seed_count'] . ' torrent(s) allowed for seeding');
                } elseif (!$seeder && $bb_cfg['tracker']['limit_leech_count'] && $row['active_torrents'] >= $bb_cfg['tracker']['limit_leech_count']) {
                    msg_die('Only ' . $bb_cfg['tracker']['limit_leech_count'] . ' torrent(s) allowed for leeching' . $rating_msg);
                }
            }
        }

        // Limit concurrent IPs
        if ($bb_cfg['tracker']['limit_concurrent_ips'] && (($bb_cfg['tracker']['limit_seed_ips'] && $seeder) || ($bb_cfg['tracker']['limit_leech_ips'] && !$seeder))) {
            $sql = "SELECT COUNT(DISTINCT ip) AS ips
				FROM " . BB_BT_TRACKER . "
				WHERE topic_id = $topic_id
					AND user_id = $user_id
					AND seeder = $seeder
					AND ip != '$ip_sql'";

            if (!$seeder && $bb_cfg['tracker']['leech_expire_factor']) {
                $sql .= " AND update_time > " . (TIMENOW - 60 * $bb_cfg['tracker']['leech_expire_factor']);
            }
            $sql .= " GROUP BY topic_id";

            if ($row = DB()->fetch_row($sql)) {
                if ($seeder && $bb_cfg['tracker']['limit_seed_ips'] && $row['ips'] >= $bb_cfg['tracker']['limit_seed_ips']) {
                    msg_die('You can seed only from ' . $bb_cfg['tracker']['limit_seed_ips'] . " IP's");
                } elseif (!$seeder && $bb_cfg['tracker']['limit_leech_ips'] && $row['ips'] >= $bb_cfg['tracker']['limit_leech_ips']) {
                    msg_die('You can leech only from ' . $bb_cfg['tracker']['limit_leech_ips'] . " IP's");
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

// Gold/Silver releases
if ($bb_cfg['tracker']['gold_silver_enabled'] && $down_add) {
    if ($tor_type == TOR_TYPE_GOLD) {
        $down_add = 0;
    } // Silver releases
    elseif ($tor_type == TOR_TYPE_SILVER) {
        $down_add = ceil($down_add / 2);
    }
}

// Freeleech
if ($bb_cfg['tracker']['freeleech'] && $down_add) {
    $down_add = 0;
}

// Insert / update peer info
$peer_info_updated = false;
$update_time = ($stopped) ? 0 : TIMENOW;
if (isset($hybrid_tor_update) || !isset($is_hybrid)) { // Update statistics only for one topic
    if ($lp_info) {
        $sql = "UPDATE " . BB_BT_TRACKER . " SET update_time = $update_time";

        $sql .= ", seeder = $seeder";
        $sql .= ($releaser != $lp_info['releaser']) ? ", releaser = $releaser" : '';

        $sql .= ($tor_type != $lp_info['tor_type']) ? ", tor_type = $tor_type" : '';

        $sql .= ($uploaded != $lp_info['uploaded']) ? ", uploaded = $uploaded" : '';
        $sql .= ($downloaded != $lp_info['downloaded']) ? ", downloaded = $downloaded" : '';
        $sql .= ", remain = $left";

        $sql .= $up_add ? ", up_add = up_add + $up_add" : '';
        $sql .= $down_add ? ", down_add = down_add + $down_add" : '';

        $sql .= ", speed_up = $speed_up";
        $sql .= ", speed_down = $speed_down";

        $sql .= ", complete = $complete";
        $sql .= ", peer_id = '$peer_id_sql'";

        $sql .= " WHERE peer_hash = '$peer_hash'";
        $sql .= " LIMIT 1";

        DB()->query($sql);

        $peer_info_updated = DB()->affected_rows();
    }

    if (!$lp_info || !$peer_info_updated) {
        $columns = 'peer_hash, topic_id, user_id, ip, port, seeder, releaser, tor_type, uploaded, downloaded, remain, speed_up, speed_down, up_add, down_add, update_time, complete, peer_id';
        $values = "'$peer_hash', $topic_id, $user_id, '$ip_sql', $port, $seeder, $releaser, $tor_type, $uploaded, $downloaded, $left, $speed_up, $speed_down, $up_add, $down_add, $update_time, $complete, '$peer_id_sql'";

        DB()->query("REPLACE INTO " . BB_BT_TRACKER . " ($columns) VALUES ($values)");
    }
}

// Exit if stopped
if ($stopped) {
    silent_exit('Cache will be reset within 30 seconds');
}

// Store peer info in cache
$lp_info = [
    'downloaded' => (float)$downloaded,
    'releaser' => (int)$releaser,
    'seeder' => (int)$seeder,
    'topic_id' => (int)$topic_id,
    'update_time' => (int)TIMENOW,
    'uploaded' => (float)$uploaded,
    'user_id' => (int)$user_id,
    'tor_type' => (int)$tor_type,
    'complete' => (int)$complete,
];

$lp_info_cached = CACHE('tr_cache')->set(PEER_HASH_PREFIX . $peer_hash, $lp_info, PEER_HASH_EXPIRE);

// Get cached output
$output = CACHE('tr_cache')->get(PEERS_LIST_PREFIX . $topic_id);

if (!$output) {
    // Retrieve peers
    $numwant = (int)$bb_cfg['tracker']['numwant'];
    $compact_mode = ($bb_cfg['tracker']['compact_mode'] || !empty($compact));

    $rowset = DB()->fetch_rowset("
        SELECT ip, port
        FROM " . BB_BT_TRACKER . "
        WHERE topic_id = $topic_id
        ORDER BY seeder ASC, RAND()
        LIMIT $numwant
    ");

    if (empty($rowset)) {
        $rowset[] = ['ip' => \TorrentPier\Helpers\IPHelper::ip2long($ip), 'port' => (int)$port];
    }

    if ($compact_mode) {

    $peers = '';
    $peers6 = '';

        foreach ($rowset as $peer) {
            $ip = \TorrentPier\Helpers\IPHelper::long2ip_extended($peer['ip']);
            $endian = inet_pton($ip) . pack('n', $peer['port']);

            if (\TorrentPier\Helpers\IPHelper::isValidv6($ip)) {
                $peers6 .= $endian;
            }
            else{
                $peers .= $endian;
            }
        }
    } else {
        $peers = [];

        foreach ($rowset as $peer) {
            $peers[] = ['ip' => \TorrentPier\Helpers\IPHelper::long2ip_extended($peer['ip']), 'port' => (int)$peer['port']];
        }
    }

    $seeders = $leechers = $client_completed = 0;

    if ($bb_cfg['tracker']['scrape']) {
        $row = DB()->fetch_row("
			SELECT seeders, leechers, completed
			FROM " . BB_BT_TRACKER_SNAP . "
			WHERE topic_id = $topic_id
			LIMIT 1
		");

        $seeders = $row['seeders'] ?? ($seeder ? 1 : 0);
        $leechers = $row['leechers'] ?? (!$seeder ? 1 : 0);
        $client_completed = $row['completed'] ?? 0;
    }

    $output = [
        'interval' => (int)$announce_interval,
        'min interval' => (int)$announce_interval,
        'complete' => (int)$seeders,
        'incomplete' => (int)$leechers,
        'downloaded' => (int)$client_completed,
        'peers' => $peers,
    ];

    if (!empty($peers6)) {
        $output['peers6'] = $peers6;
    }

    $peers_list_cached = CACHE('tr_cache')->set(PEERS_LIST_PREFIX . $topic_id, $output, PEERS_LIST_EXPIRE);
    $output['external ip'] = inet_pton($ip);
    $output['warning message'] = 'Statistics were updated';
}

// Return data to client
echo \Arokettu\Bencode\Bencode::encode($output);

exit;
