<?php

require('./pagestart.php');

// check if mod is installed
if(empty($template->xs_version) || $template->xs_version !== 8)
{
	message_die(GENERAL_ERROR, isset($lang['XS_ERROR_NOT_INSTALLED']) ? $lang['XS_ERROR_NOT_INSTALLED'] : 'eXtreme Styles mod is not installed. You forgot to upload includes/template.php');
}

define('IN_XS', true);
define('NO_XS_HEADER', true);
include('xs_include.php');

$action = isset($_GET['action']) ? $_GET['action'] : '';
$get_data = array();
foreach($_GET as $var => $value)
{
	if($var !== 'action' && $var !== 'sid')
	{
		$get_data[] = $var . '=' . urlencode(stripslashes($value));
	}
}

// check for style download command
if(isset($_POST['action']) && $_POST['action'] === 'web')
{
	$action = 'import';
	$get_data[] = 'get_remote=' . urlencode(stripslashes($_POST['source']));
	if(isset($_POST['return']))
	{
		$get_data[] = 'return=' . urlencode(stripslashes($_POST['return']));
	}
}

$get_data = count($get_data) ? 'php?' . implode('&', $get_data) : 'php';

$content_url = array(
	'config'		=> 'xs_config.'.$get_data,
	'install'		=> 'xs_install.'.$get_data,
	'uninstall'		=> 'xs_uninstall.'.$get_data,
	'default'		=> 'xs_styles.'.$get_data,
	'cache'			=> 'xs_cache.'.$get_data,
	'import'		=> 'xs_import.'.$get_data,
	'export'		=> 'xs_export.'.$get_data,
	'clone'			=> 'xs_clone.'.$get_data,
	'download'		=> 'xs_download.'.$get_data,
	'edittpl'		=> 'xs_edit.'.$get_data,
	'editdb'		=> 'xs_edit_data.'.$get_data,
	'exportdb'		=> 'xs_export_data.'.$get_data,
	'updates'		=> 'xs_update.'.$get_data,
	'portal'		=> 'xs_portal.'.$get_data,
	'style_config'	=> 'xs_style_config.'.$get_data,
	);

if(isset($content_url[$action]))
{
	$content = $content_url[$action];
}
else
{
	$content = 'xs_index.'.$get_data;
}

$template->set_filenames(array('body' => XS_TPL_PATH . 'frameset.tpl'));
$template->assign_vars(array(
	'FRAME_TOP'		=> 'xs_frame_top.php',
	'FRAME_MAIN'	=> $content,
	));

$template->pparse('body');

xs_exit();