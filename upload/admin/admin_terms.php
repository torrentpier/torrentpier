<?php

if (!empty($setmodules))
{
	$module['GENERAL']['TERMS'] = basename(__FILE__);
	return;
}
require('./pagestart.php');

require(INC_DIR .'bbcode.php');

if (isset($_POST['post']) && $bb_cfg['terms'] != $_POST['message'])
{
	bb_update_config(array('terms' => $_POST['message']));
	bb_die($lang['CONFIG_UPDATED']);
}

$template->assign_vars(array(
	'S_ACTION'     => 'admin_terms.php',
	'EXT_LINK_NW'  => $bb_cfg['ext_link_new_win'],
	'MESSAGE'      => ($bb_cfg['terms']) ? $bb_cfg['terms'] : '',
	'PREVIEW_HTML' => (isset($_REQUEST['preview'])) ? bbcode2html($_POST['message']) : '',
));

print_page('admin_terms.tpl', 'admin');