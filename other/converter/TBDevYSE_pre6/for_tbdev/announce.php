<?php

// TorrentPier bridge
define ('TP_ROOT', './../ptv/'); // Absolete or related local path to your TorrentPier installation
define ('USER_ID_DIFF', 1); //	User_id difference between TP and TBDev (tp_user_id - tb_user_id)

// TorrentPier Database
$dbhost = 'localhost';
$dbname = 'dbase';
$dbuser = 'user';
$dbpasswd  = 'pass';
$dbcharset = 'utf8';

// Start announce
define ('IN_ANNOUNCE', true);
require_once('./include/core_announce.php');

$passkey = @$_GET['passkey'];

if (!$passkey)
{
	err('Passkey required');
}

dbconn();

$res = mysql_query("SELECT id FROM users WHERE passkey = " . sqlesc($passkey)) or err(mysql_error());

if (mysql_affected_rows() == 0)
{
	err('Invalid passkey! Re-download the .torrent from '.$DEFAULTBASEURL);
}

$user = mysql_fetch_array($res);
$user_id = $user['id'];

mysql_close();

// Init connection to TP database for get passkey
@mysql_connect($dbhost, $dbuser, $dbpasswd);
@mysql_select_db($dbname);
mysql_query("SET NAMES $dbcharset");

// Get passkey for TorrentPier
$user_id += USER_ID_DIFF;
$res = mysql_query("SELECT auth_key FROM bb_bt_users WHERE user_id = $user_id") or err(mysql_error());

if (mysql_affected_rows() == 0)
{
	err('Passkey doesn\'t created on new tracker or user doesn\'t exist');
}

$user = mysql_fetch_array($res);
mysql_close();

$_GET['uk'] = $user['auth_key'];

unset($res, $user, $dbpasswd, $passkey, $user_id);

// Execute TP's announce
chdir(TP_ROOT .'bt/');
require ('announce.php');
