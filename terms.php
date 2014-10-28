<?php

define('BB_SCRIPT', 'terms');
define('BB_ROOT', './');
require(BB_ROOT .'common.php');
require(INC_DIR .'bbcode.php');

// Start session management
$user->session_start();

if (!$bb_cfg['terms'] && !IS_ADMIN) redirect('index.php');

$template->assign_vars(array(
	'TERMS_EDIT' => bbcode2html(sprintf($lang['TERMS_EMPTY_TEXT'], $domain_name)),
	'TERMS_HTML' => bbcode2html($bb_cfg['terms']),
));

print_page('terms.tpl');