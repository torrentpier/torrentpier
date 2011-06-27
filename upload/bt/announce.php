<?php

define('IN_TRACKER', true);
define('BB_ROOT', './../');
require(BB_ROOT .'common.php');

if (empty($_SERVER['HTTP_USER_AGENT']))
{
	header('Location: http://127.0.0.1', true, 301);
	die;
}

// Ignore 'completed' event
if (isset($_GET['event']) && $_GET['event'] === 'completed')
{
	if (DBG_LOG) dbg_log(' ', '!die-event-completed');
	dummy_exit(mt_rand(600, 1200));
}

$announce_interval = $bb_cfg['announce_interval'];
$passkey_key = $bb_cfg['passkey_key'];
$max_left_val      = 536870912000;   // 500 GB
$max_up_down_val   = 5497558138880;  // 5 TB
$max_up_add_val    = 85899345920;    // 80 GB
$max_down_add_val  = 85899345920;    // 80 GB

// Recover info_hash
if (isset($_GET['?info_hash']) && !isset($_GET['info_hash']))
{
	$_GET['info_hash'] = $_GET['?info_hash'];
}

// Initial request verification
if (strpos($_SERVER['REQUEST_URI'], 'scrape') !== false)
{
	msg_die('Please disable SCRAPE!');
}
if (!isset($_GET[$passkey_key]) || !is_string($_GET[$passkey_key]) || strlen($_GET[$passkey_key]) != BT_AUTH_KEY_LENGTH)
{
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
foreach ($input_vars_str as $var_name)
{
	$$var_name = isset($_GET[$var_name]) ? (string) $_GET[$var_name] : null;
}
// Numeric
foreach ($input_vars_num as $var_name)
{
	$$var_name = isset($_GET[$var_name]) ? (float) $_GET[$var_name] : null;
}
// Passkey
$passkey = isset($$passkey_key) ? $$passkey_key : null;

// Verify request
// Required params (info_hash, peer_id, port, uploaded, downloaded, left, passkey)
if (!isset($info_hash) || strlen($info_hash) != 20)
{
	msg_die('Invalid info_hash');
}
if (!isset($peer_id) || strlen($peer_id) != 20)
{
	msg_die('Invalid peer_id');
}
if (!isset($port) || $port < 0 || $port > 0xFFFF)
{
	msg_die('Invalid port');
}
if (!isset($uploaded) || $uploaded < 0 || $uploaded > $max_up_down_val || $uploaded == 1844674407370)
{
	msg_die('Invalid uploaded value');
}
if (!isset($downloaded) || $downloaded < 0 || $downloaded > $max_up_down_val || $downloaded == 1844674407370)
{
	msg_die('Invalid downloaded value');
}
if (!isset($left) || $left < 0 || $left > $max_left_val)
{
	msg_die('Invalid left value');
}
if (!verify_id($passkey, BT_AUTH_KEY_LENGTH))
{
	msg_die('Invalid passkey');
}

// IP
$ip = $_SERVER['REMOTE_ADDR'];

if (!$bb_cfg['ignore_reported_ip'] && isset($_GET['ip']) && $ip !== $_GET['ip'])
{
	if (!$bb_cfg['verify_reported_ip'])
	{
		$ip = $_GET['ip'];
	}
	else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches))
	{
		foreach ($matches[0] as $x_ip)
		{
			if ($x_ip === $_GET['ip'])
			{
				if (!$bb_cfg['allow_internal_ip'] && preg_match("#^(10|172\.16|192\.168)\.#", $x_ip))
				{
					break;
				}
				$ip = $x_ip;
				break;
			}
		}
	}
}
// Check that IP format is valid
if (!verify_ip($ip))
{
	msg_die("Invalid IP: $ip");
}
// Convert IP to HEX format
$ip_sql = encode_ip($ip);

// Peer unique id
$peer_hash = md5(
	rtrim($info_hash, ' ') . $passkey . $ip . $port
);

// Get cached peer info from previous announce (last peer info)
$lp_info = CACHE('tr_cache')->get(PEER_HASH_PREFIX . $peer_hash);

if (DBG_LOG) dbg_log(' ', '$lp_info-get_from-CACHE-'. ($lp_info ? 'hit' : 'miss'));

// Drop fast announce
if ($lp_info && (!isset($event) || $event !== 'stopped'))
{
	drop_fast_announce($lp_info);
}

// Functions
function drop_fast_announce ($lp_info)
{
	global $announce_interval;

	if ($lp_info['update_time'] < (TIMENOW - $announce_interval + 60))
	{
		return;  // if announce interval correct
	}

	if (DBG_LOG) dbg_log(' ', 'drop_fast_announce-'. (!empty(DB()) ? 'DB' : 'CACHE'));

	$new_ann_intrv = $lp_info['update_time'] + $announce_interval - TIMENOW;

	dummy_exit($new_ann_intrv);
}

function msg_die ($msg)
{
	if (DBG_LOG) dbg_log(' ', '!die-'. clean_filename($msg));

	$output = bencode(array(
#		'interval'        => (int) 1800,
		'min interval'    => (int) 1800,
#		'peers'           => (string) DUMMY_PEER,
		'failure reason'  => (string) $msg,
		'warning message' => (string) $msg,
	));

	die($output);
}

# $agent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '-';
# bb_log("$agent  |  ". str_compact($peer_id) ."\n", 'agent');

// Start announcer
define('TR_ROOT', './');
require(TR_ROOT .'includes/init_tr.php');
require(TR_ROOT .'includes/tr_announcer.php');
exit;