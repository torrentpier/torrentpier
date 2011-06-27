<?php

define('IN_TRACKER', true);
define('BB_ROOT', './../');
require(BB_ROOT .'common.php');

if (!$tr_cfg['scrape'])
{
	msg_die('Please disable SCRAPE!');
}

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
require(TR_ROOT .'includes/init_tr.php');
require(TR_ROOT .'includes/tr_scraper.php');
exit;