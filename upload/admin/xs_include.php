<?php

/***************************************************************************
 *                               xs_include.php
 *                               --------------
 *   copyright            : (C) 2003 - 2005 CyberAlien
 *   support              : http://www.phpbbstyles.com
 *
 *   version              : 2.3.1
 *
 *   file revision        : 77
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

if (!defined('IN_PHPBB') || !defined('IN_XS'))
{
	die(basename(__FILE__));
}

if(defined('XS_INCLUDED'))
{
	return;
}
define('XS_INCLUDED', true);


//
// include language file
//

if(!defined('XS_LANG_INCLUDED'))
{
	global $bb_cfg, $lang;
	$xs_lang_file = LANG_ROOT_DIR ."lang_{$bb_cfg['default_lang']}/lang_xs.php";
	if( !@file_exists($xs_lang_file) )
	{	// load english version if there is no translation to current language
		$xs_lang_file = LANG_ROOT_DIR ."lang_english/lang_xs.php";
	}
	include($xs_lang_file);
	define('XS_LANG_INCLUDED', true);
}


define('XS_SHOWNAV_CONFIG', 0);
define('XS_SHOWNAV_INSTALL', 1);
define('XS_SHOWNAV_UNINSTALL', 2);
define('XS_SHOWNAV_DEFAULT', 3);
define('XS_SHOWNAV_CACHE', 4);
define('XS_SHOWNAV_IMPORT', 5);
define('XS_SHOWNAV_EXPORT', 6);
define('XS_SHOWNAV_CLONE', 7);
define('XS_SHOWNAV_DOWNLOAD', 8);
define('XS_SHOWNAV_EDITTPL', 9);
define('XS_SHOWNAV_EDITDB', 10);
define('XS_SHOWNAV_EXPORTDB', 11);
define('XS_SHOWNAV_UPDATES', 12);
define('XS_SHOWNAV_MAX', 13);

global $xs_shownav_action;
$xs_shownav_action = array(
	'config',
#	'install',
#	'uninstall',
#	'default',
	'cache',
#	'import',
#	'export',
#	'clone',
#	'download',
#	'edittpl',
#	'editdb',
#	'exportdb',
#	'updates',
	);


// override styles management in admin navigation
function xs_admin_override($modded = false)
{
	if(defined('XS_ADMIN_OVERRIDE_FINISHED'))
	{
		return;
	}
	define('XS_ADMIN_OVERRIDE_FINISHED', true);
	global $module, $xs_shownav_action, $bb_cfg, $lang;
	// remove default phpBB styles management
	if(isset($module['Styles']))
	{
		$unset = array('Add_new', 'Create_new', 'Manage', 'Export');
		for($i=0; $i<count($unset); $i++)
		{
			if(isset($module['Styles'][$unset[$i]]))
			{
				unset($module['Styles'][$unset[$i]]);
			}
		}
		$module['Styles']['Menu'] = 'xs_frameset.php'.'?action=menu&showwarning=1';
	}
	// add new menu
	$module_name = 'Extreme_Styles';
	$module[$module_name]['Styles_Management'] = 'xs_frameset.php'.'?action=menu';
	for($i=0; $i<count($lang['XS_CONFIG_SHOWNAV']); $i++)
	{
		$num = pow(2, $i);
		if($i != XS_SHOWNAV_DOWNLOAD && ($bb_cfg['xs_shownav'] & $num) > 0 && isset($xs_shownav_action[$i]))
		{
			$module[$module_name][$lang['XS_CONFIG_SHOWNAV'][$i]] = 'xs_frameset.php'.'?action=' . $xs_shownav_action[$i];
		}
	}
	// add menu for style configuration
	foreach($bb_cfg as $var => $value)
	{
		if(substr($var, 0, 9) === 'xs_style_')
		{
			$str = substr($var, 9);
			$module['Template_Config'][$str] = 'xs_frameset.php'.'?action=style_config&tpl='.urlencode($str);
		}
	}
}


if(!empty($setmodules))
{
	if(@function_exists('jr_admin_get_module_list'))
	{
		$tmp_mod = $module;
		global $module;
		$module = $tmp_mod;
		xs_admin_override(true);
	}
	return;
}

//
// Global defines for eXtreme Styles mod administration panel
//
define('STYLE_HEADER_START', 'xs_style_01<xs>');
define('STYLE_HEADER_END', '</xs>');
define('STYLE_HEADER_VERSION', '1');
define('STYLE_EXTENSION', '.style');
define('XS_MAX_ITEMS_PER_STYLE', 32);
define('XS_FTP_LOCAL', 'no_ftp');
define('XS_UPDATE_STYLE', 1);
define('XS_UPDATE_MOD', 2);
define('XS_UPDATE_PHPBB', 3);
define('XS_TPL_PATH', TEMPLATES_DIR .'xs_mod/tpl/');
define('XS_BACKUP_PREFIX', 'backup.');
define('XS_BACKUP_EXT', '.backup');
define('XS_MAX_TIMEOUT', 600); // maximum timeout for downloads/import/installation

$xs_row_class = array('row1', 'row2');

$template_dir = 'templates/';

$template->assign_vars(array(
	'XS_PATH'	=> TEMPLATES_DIR .'xs_mod/',
	'XS_UL'		=> '<table width="100%" cellspacing="0" cellpadding="2" border="0">',
	'XS_UL2'	=> '</table>',
	'XS_LI'		=> '<tr><td width="20" align="center" valign="middle"><img src="../xs_mod/images/dot.gif" border="0" alt="" /></td><td align="left" valign="middle" width="100%"><span class="gen">',
	'XS_LI2'	=> '</span></td></tr>',
	'S_HIDDEN_FIELDS'	=> '<input type="hidden" name="sid" value="' . $userdata['session_id'] . '" />',
	));

if(!defined('NO_XS_HEADER'))
{
	$template->set_filenames(array(
		'xs_header' => XS_TPL_PATH . 'xs_header.tpl',
		'xs_footer' => XS_TPL_PATH . 'xs_footer.tpl',
	));

	$template->preparse = 'xs_header';
	$template->postparse = 'xs_footer';
	$template->assign_block_vars('nav_left',array('ITEM' => '<a href="' . append_sid('xs_index.php') . '">' . $lang['XS_MENU'] . '</a>'));
}

// check if cache is writable
function xs_check_cache($filename)
{
	// check if filename is valid
	global $str, $template, $lang;
	if(substr($filename, 0, strlen($template->cachedir)) !== $template->cachedir)
	{
		$str .= $lang['XS_CHECK_FILENAME'] . "<br />\n";
		return false;
	}
	else
	{
		// try to open file
		$file = @fopen($filename, 'w');
		if(!$file)
		{
			$str .= sprintf($lang['XS_CHECK_OPENFILE1'], $filename) . "<br />\n";
			// try to create directories
			$dir = substr($filename, strlen($template->cachedir), strlen($filename));
			$dirs = explode('/', $dir);
			$path = $template->cachedir;
			@umask(0);
			if(!@is_dir($path))
			{
				$str .= sprintf($lang['XS_CHECK_NODIR'], $path) . "<br />\n";
				if(!@mkdir($path))
				{
					$str .= sprintf($lang['XS_CHECK_NODIR2'], $path) . "<br />\n";
					return false;
				}
				else
				{
					$str .= sprintf($lang['XS_CHECK_CREATEDDIR'], $path) . "<br />\n";
					@chmod($path, 0777);
				}
			}
			else
			{
				$str .= sprintf($lang['XS_CHECK_DIR'] , $path) . "<br />\n";
			}
			if(count($dirs) > 0)
			for($i=0; $i<count($dirs)-1; $i++)
			{
				if($i>0)
				{
					$path .= '/';
				}
				$path .= $dirs[$i];
				if(!@is_dir($path))
				{
					$str .= sprintf($lang['XS_CHECK_NODIR'], $path) . "<br />\n";
					if(!@mkdir($path))
					{
						$str .= sprintf($lang['XS_CHECK_NODIR2'], $path) . "<br />\n";
						return false;
					}
					else
					{
						$str .= sprintf($lang['XS_CHECK_CREATEDDIR'], $path) . "<br />\n";
						@chmod($path, 0777);
					}
				}
				else
				{
					$str .= sprintf($lang['XS_CHECK_DIR'] , $path) . "<br />\n";
				}
			}
			// try to open file again after directories were created
			$file = @fopen($filename, 'w');
		}
		if(!$file)
		{
			$str .= sprintf($lang['XS_CHECK_OPENFILE2'], $filename) . "<br />\n";
			return false;
		}
		$str .= sprintf($lang['XS_CHECK_OK'], $filename) . "<br />\n";
		fputs($file, '&nbsp;');
		fclose($file);
		@chmod($filename, 0777);
		return true;
	}
}

// show error and exit
function xs_error($error, $line = 0, $file = '')
{
	global $template, $lang;
	if($line || $file)
	{
		$error = basename($file) . '(' . $line . '): ' . $error;
	}
	$template->set_filenames(array('errormsg' => XS_TPL_PATH . 'message.tpl'));
	$template->assign_vars(array(
			'MESSAGE_TITLE'	=> $lang['ERROR'],
			'MESSAGE_TEXT'	=> $error
		));
	$template->pparse('errormsg');
	xs_exit();
}

// show message and exit
function xs_message($title, $message)
{
	global $template;
	$template->set_filenames(array('msg' => XS_TPL_PATH . 'message.tpl'));
	$template->assign_vars(array(
			'MESSAGE_TITLE'	=> $title,
			'MESSAGE_TEXT'	=> $message
		));
	$template->pparse('msg');
	xs_exit();
}

// strip slashes for sql
function xs_sql($sql, $strip = false)
{
	if($strip)
	{
		$sql = stripslashes($sql);
	}
	return str_replace('\\\'', '\'\'', addslashes($sql));
}

// clean template name
function xs_tpl_name($name)
{
	return str_replace(array('\\', '/', "'", '"'), array('','','',''), $name);
}

// close database and maybe do some other stuff
function xs_exit()
{
	require(PAGE_FOOTER);
}

// check directory name/filename
function xs_fix_dir($dir)
{
	$dir = str_replace('\\', '/', $dir);
	$dir = str_replace('../', './', $dir);
	while(strlen($dir > 1) && substr($dir, strlen($dir) - 2) === '..')
	{
		$dir = substr($dir, 0, strlen($dir) - 1);
	}
	return $dir;
}