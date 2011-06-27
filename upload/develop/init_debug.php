<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

require(DEV_DIR .'dbg_config.php');
require(DEV_DIR .'functions_debug.php');

//
// Timer
//
require(DEV_DIR .'benchmark/timer.php');
$timer_markers = 0;
$timer = new Benchmark_Timer();
$GLOBALS['timer']->start();
#	$GLOBALS['timer']->setMarker();                // empty setMarker() will point to "source(line)"
#	$GLOBALS['timer']->setMarker('Marker 1');
#	$GLOBALS['timer']->setMarker('Marker 1 End');
#	$GLOBALS['timer']->stop();
#	$GLOBALS['timer']->display(); die;

//
// Error handler
//
require(DEV_DIR .'error_handler.php');

//
// OB conveyer
//
function prepend_debug_info ($contents)
{
	global $errHandler;

	if ($errors = $errHandler->get_clean_errors())
	{
		$contents = file_get_contents(DEV_DIR .'dbg_header.php') . $errors . $contents;
	}

	return $contents;
}

ob_start('prepend_debug_info');
