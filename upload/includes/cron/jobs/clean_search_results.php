<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$search_results_expire = TIMENOW - ($bb_cfg['user_session_duration'] * 2) - 600;

DB()->query("
	DELETE FROM ". BB_SEARCH ."
	WHERE search_time < $search_results_expire
");