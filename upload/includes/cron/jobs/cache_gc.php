<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$gc_cache = array(
	'bb_cache',
	'tr_cache',
	'session_cache',
	'bb_cap_sid',
	'bb_login_err',
	'bb_poll_data',
);

foreach ($gc_cache as $cache_name)
{
	if (method_exists(CACHE($cache_name), 'gc'))
	{
		$changes = CACHE($cache_name)->gc();
		$cron_runtime_log = date('Y-m-d H:i:s') ." -- ". str_pad("$cache_name ", 25, '-', STR_PAD_RIGHT) ." del: $changes\n";
	}
}