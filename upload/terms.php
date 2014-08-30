<?php
/**
 * User: dimka3210
 * Date: 30.08.14
 * Time: 15:15
 */

define('BB_ROOT', './');
require(BB_ROOT . 'common.php');
require(INC_DIR . 'bbcode.php');

$user->session_start();

$message = '';
$sql = "SELECT config_value FROM " . BB_CONFIG . " WHERE config_name='" . TERMS_KEY . "'";

if ($result = DB()->sql_query($sql)) {
    $row = DB()->sql_fetchrow($result);
    $message = $row['config_value'];
}

if (!$message && IS_ADMIN) {
    $message = sprintf($lang['TERMS_ADMIN_EMPTY_TEXT'], $domain_name);
} elseif (!$message) {
    redirect('/');
}

$template->assign_vars(array(
    'TERMS_HTML' => bbcode2html($message)
));

print_page('terms.tpl');