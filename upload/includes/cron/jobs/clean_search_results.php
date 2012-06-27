<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$search_results_expire = TIMENOW - 3*3600;

DB()->query("
	DELETE FROM ". BB_SEARCH ."
	WHERE search_time < $search_results_expire
");