<?php

/***************************************************************************
 *                               xs_index.php
 *                               ------------
 *   copyright            : (C) 2003 - 2005 CyberAlien
 *   support              : http://www.phpbbstyles.com
 *
 *   version              : 2.3.1
 *
 *   file revision        : 72
 *   project revision     : 78
 *   last modified        : 05 Dec 2005  13:54:54
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

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
	$msg = str_replace('{URL}', append_sid('xs_index.php'), $lang['XS_MAIN_COMMENT3']);
	xs_message($lang['INFORMATION'], $msg);
}

$template->assign_vars(array(
	'U_CONFIG'				=> append_sid('xs_config.php'),
	'U_DEFAULT_STYLE'		=> append_sid('xs_styles.php'),
	'U_MANAGE_CACHE'		=> append_sid('xs_cache.php'),
	'U_IMPORT_STYLES'		=> append_sid('xs_import.php'),
	'U_EXPORT_STYLES'		=> append_sid('xs_export.php'),
	'U_CLONE_STYLE'			=> append_sid('xs_clone.php'),
	'U_DOWNLOAD_STYLES'		=> append_sid('xs_download.php'),
	'U_INSTALL_STYLES'		=> append_sid('xs_install.php'),
	'U_UNINSTALL_STYLES'	=> append_sid('xs_uninstall.php'),
	'U_EDIT_STYLES'			=> append_sid('xs_edit.php'),
	'U_EDIT_STYLES_DATA'	=> append_sid('xs_edit_data.php'),
	'U_EXPORT_DATA'			=> append_sid('xs_export_data.php'),
	'U_UPDATES'				=> append_sid('xs_update.php'),
	));

$template->set_filenames(array('body' => XS_TPL_PATH . 'index.tpl'));
$template->pparse('body');
xs_exit();