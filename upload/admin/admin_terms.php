<?php

if (!empty($setmodules)) {
    $module['GENERAL']['TERMS'] = basename(__FILE__);
    return;
}

require('./pagestart.php');
require(INC_DIR . 'bbcode.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    bb_update_config(array(TERMS_KEY => $_POST['message']));
}

$message = '';
$sql = "SELECT config_value FROM " . BB_CONFIG . " WHERE config_name='" . TERMS_KEY . "'";

if ($result = DB()->sql_query($sql)) {
    $row = DB()->sql_fetchrow($result);
    $message = $row['config_value'];
}

$template->assign_vars(array(
    'S_CONFIG_ACTION' => 'admin_terms.php',
    'CONFIG' => true,
    'MESSAGE' => $message,
    'EXT_LINK_NEW_WIN' => $bb_cfg['ext_link_new_win'],
    'PREVIEW_HTML' => (isset($_REQUEST['preview'])) ? bbcode2html($message) : ''

));

print_page('admin_terms.tpl', 'admin');