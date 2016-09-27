<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

$log_days_keep = (int) $di->config->get('log_days_keep');

DB()->query("
	DELETE FROM ". BB_LOG ."
	WHERE log_time < ". (TIMENOW - 86400*$log_days_keep) ."
");