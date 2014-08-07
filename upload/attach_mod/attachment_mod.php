<?php

if (!defined('IN_FORUM')) die("Hacking attempt");

require(ATTACH_DIR .'includes/functions_includes.php');
require(ATTACH_DIR .'includes/functions_attach.php');
require(ATTACH_DIR .'includes/functions_delete.php');
require(ATTACH_DIR .'includes/functions_thumbs.php');
require(ATTACH_DIR .'includes/functions_filetypes.php');

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
	if (!file_exists(LANG_ROOT_DIR ."$language/$language_file.php"))
	{
		$language = $attach_config['board_lang'];

		if (!file_exists(LANG_ROOT_DIR ."$language/$language_file.php"))
		{
			bb_die('Attachment mod language file does not exist: language/' . $language . '/' . $language_file . '.php');
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

	if (!($result = DB()->sql_query($sql)))
	{
		bb_die('Could not query attachment information');
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

if (!$attach_config = CACHE('bb_cache')->get('attach_config'))
{
	$attach_config = get_config();
	CACHE('bb_cache')->set('attach_config', $attach_config, 86400);
}

include(ATTACH_DIR .'displaying.php');
include(ATTACH_DIR .'posting_attachments.php');

$upload_dir = $attach_config['upload_dir'];