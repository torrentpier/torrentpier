<?php

define('IN_PHPBB', true);
define('BB_SCRIPT', 'donate');
define('BB_ROOT', './');
require(BB_ROOT ."common.php");

// Start session management
$user->session_start();

$template->assign_vars(array(
	'PAGE_TITLE' => 'Помощь трекеру',
));

print_page('donate.tpl');