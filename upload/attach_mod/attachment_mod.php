<?php

if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
	exit;
}

require(BB_ROOT .'attach_mod/includes/functions_includes.php');
require(BB_ROOT .'attach_mod/includes/functions_attach.php');
require(BB_ROOT .'attach_mod/includes/functions_delete.php');
require(BB_ROOT .'attach_mod/includes/functions_thumbs.php');
require(BB_ROOT .'attach_mod/includes/functions_filetypes.php');

if (defined('ATTACH_INSTALL'))
{
	return;
}

/**
* wrapper function for determining the correct language directory
*/
function attach_mod_get_lang($language_file)
{
	global $attach_config, $bb_cfg;

	$language = $bb_cfg['default_lang'];
	if (!file_exists(LANG_ROOT_DIR ."lang_$language/$language_file.php"))
	{
		$language = $attach_config['board_lang'];

		if (!file_exists(LANG_ROOT_DIR ."lang_$language/$language_file.php"))
		{
			message_die(GENERAL_MESSAGE, 'Attachment Mod language file does not exist: language/lang_' . $language . '/' . $language_file . '.php');
		}
		else
		{
			return $language;
		}
	}
	else
	{
		return $language;
	}
}

/**
* Get attachment mod configuration
*/
function get_config()
{
	global $bb_cfg;

	$attach_config = array();

	$sql = 'SELECT * FROM ' . BB_ATTACH_CONFIG;

	if ( !($result = DB()->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, 'Could not query attachment information', '', __LINE__, __FILE__, $sql);
	}

	while ($row = DB()->sql_fetchrow($result))
	{
		$attach_config[$row['config_name']] = trim($row['config_value']);
	}

	// We assign the original default board language here, because it gets overwritten later with the users default language
	$attach_config['board_lang'] = trim($bb_cfg['default_lang']);

	return $attach_config;
}

// Get Attachment Config
$attach_config = array();

if (!($attach_config = CACHE('bb_cache')->get('attach_config')))
{
	$attach_config = get_config();
	CACHE('bb_cache')->set('attach_config', $attach_config, 86400);
}

include(BB_ROOT .'attach_mod/displaying.php');
include(BB_ROOT .'attach_mod/posting_attachments.php');

if (!intval($attach_config['allow_ftp_upload']))
{
	$upload_dir = $attach_config['upload_dir'];
}
else
{
	$upload_dir = $attach_config['download_path'];
}