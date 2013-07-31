<?php

define('IN_FORUM', true);
define('BB_SCRIPT', 'donate');
define('BB_ROOT', './');
require(BB_ROOT ."common.php");

// Start session management
$user->session_start();

$template->assign_vars(array(
	'PAGE_TITLE' => $lang['DONATION'],
));

print_page('donate.tpl');