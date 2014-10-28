<?php

define('IN_TRACKER', true);
define('BB_ROOT', './../');
require(BB_ROOT .'common.php');

if (!$tr_cfg['scrape']) msg_die('Please disable SCRAPE!');

// Recover info_hash
if (isset($_GET['?info_hash']) && !isset($_GET['info_hash']))
{
	$_GET['info_hash'] = $_GET['?info_hash'];
}

if (!isset($_GET['info_hash']) || strlen($_GET['info_hash']) != 20)
{
	msg_die('Invalid info_hash');
}

$info_hash = $_GET['info_hash'];

function msg_die ($msg)
{
	if (DBG_LOG) dbg_log(' ', '!die-'. clean_filename($msg));

	$output = bencode(array(
		'min interval'    => (int) 1800,
		'failure reason'  => (string) $msg,
		'warning message' => (string) $msg,
	));

	die($output);
}

define('TR_ROOT', './');
require(TR_ROOT . 'includes/init_tr.php');

$info_hash_sql = rtrim(DB()->escape($info_hash), ' ');

$row = DB()->fetch_row("
		SELECT tor.complete_count, snap.seeders, snap.leechers
		FROM ". BB_BT_TORRENTS ." tor
		LEFT JOIN ". BB_BT_TRACKER_SNAP ." snap ON (snap.topic_id = tor.topic_id)
		WHERE tor.info_hash = '$info_hash_sql'
		LIMIT 1
");

$output['files'][$info_hash] = array(
		'complete'    => (int) $row['seeders'],
		'downloaded'  => (int) $row['complete_count'],
		'incomplete'  => (int) $row['leechers'],
);

echo bencode($output);

tracker_exit();
exit;