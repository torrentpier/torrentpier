<?php

if (!empty($setmodules))
{
	$module['GENERAL']['TERMS'] = basename(__FILE__);
	return;
}
require('./pagestart.php');

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

require(INC_DIR .'bbcode.php');

if (isset($_POST['post']) && $di->config->get('terms') != $_POST['message'])
{
	bb_update_config(array('terms' => $_POST['message']));
	bb_die($lang['CONFIG_UPDATED']);
}

$template->assign_vars(array(
	'S_ACTION'     => 'admin_terms.php',
	'EXT_LINK_NW'  => $di->config->get('ext_link_new_win'),
	'MESSAGE'      => ($di->config->get('terms')) ? $di->config->get('terms') : '',
	'PREVIEW_HTML' => (isset($_REQUEST['preview'])) ? bbcode2html($_POST['message']) : '',
));

print_page('admin_terms.tpl', 'admin');