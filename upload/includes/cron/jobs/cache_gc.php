<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

global $bb_cfg;

$gc_cache = array(
	'tr_cache',
	'bb_cache',
	'session_cache',
	'bb_login_err',
	'bb_cap_sid',
);

foreach ($gc_cache as $cache_name)
{
	if (method_exists(CACHE($cache_name), 'gc'))
	{
		$changes = CACHE($cache_name)->gc();
		$cron_runtime_log = date('Y-m-d H:i:s') ." -- tr -- $changes rows deleted\n";
	}
}