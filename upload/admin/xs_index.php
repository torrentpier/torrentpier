<?php

require('./pagestart.php');

// check if mod is installed
if(empty($template->xs_version) || $template->xs_version !== 8)
{
	message_die(GENERAL_ERROR, isset($lang['XS_ERROR_NOT_INSTALLED']) ? $lang['XS_ERROR_NOT_INSTALLED'] : 'eXtreme Styles mod is not installed. You forgot to upload includes/template.php');
}

define('IN_XS', true);
include('xs_include.php');

if(isset($_GET['showwarning']))
{
	$msg = str_replace('{URL}', 'xs_index.php', $lang['XS_MAIN_COMMENT3']);
	xs_message($lang['INFORMATION'], $msg);
}

$template->assign_vars(array(
	'U_CONFIG'				=> 'xs_config.php',
	'U_DEFAULT_STYLE'		=> 'xs_styles.php',
	'U_MANAGE_CACHE'		=> 'xs_cache.php',
	'U_IMPORT_STYLES'		=> 'xs_import.php',
	'U_EXPORT_STYLES'		=> 'xs_export.php',
	'U_CLONE_STYLE'			=> 'xs_clone.php',
	'U_DOWNLOAD_STYLES'		=> 'xs_download.php',
	'U_INSTALL_STYLES'		=> 'xs_install.php',
	'U_UNINSTALL_STYLES'	=> 'xs_uninstall.php',
	'U_EDIT_STYLES'			=> 'xs_edit.php',
	'U_EDIT_STYLES_DATA'	=> 'xs_edit_data.php',
	'U_EXPORT_DATA'			=> 'xs_export_data.php',
	'U_UPDATES'				=> 'xs_update.php',
	));

$template->set_filenames(array('body' => XS_TPL_PATH . 'index.tpl'));
$template->pparse('body');

xs_exit();