<?php

// Comment the following line to enable
die('Please REMOVE THIS FILE from your production environment!<br /><br />'. basename(__FILE__));

define('IN_FORUM', true);
define('BB_ROOT', './../');

require('./dbg_config.php');
require('./functions_debug.php');

$file = @$_GET['file'];
$line = @$_GET['line'];
$prev = @$_GET['prev'] ? $_GET['prev'] : 15;
$next = @$_GET['next'] ? $_GET['next'] : 15;

require('./dbg_header.php');
echo showSource($file, $line, $prev, $next);