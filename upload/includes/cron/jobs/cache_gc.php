<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

if (method_exists(CACHE('tr_cache'), 'gc'))
{
	$changes = CACHE('tr_cache')->gc();
	$cron_runtime_log .= date('Y-m-d H:i:s') ." -- tr -- $changes rows deleted\n";
}
if (method_exists(CACHE('bb_cache'), 'gc'))
{
	$changes = CACHE('bb_cache')->gc();
	$cron_runtime_log .= date('Y-m-d H:i:s') ." -- bb -- $changes rows deleted\n";
}
if (method_exists(CACHE('session_cache'), 'gc'))
{
	$changes = CACHE('session_cache')->gc(TIMENOW + $bb_cfg['session_cache_gc_ttl']);
	$cron_runtime_log .= date('Y-m-d H:i:s') ." -- ss -- $changes rows deleted\n";
}
if (method_exists(CACHE('bb_login_err'), 'gc'))
{
	$changes = CACHE('bb_login_err')->gc();
	$cron_runtime_log .= date('Y-m-d H:i:s') ." -- ss -- $changes rows deleted\n";
}
