<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

//
// Pick a language, any language
//
function language_select ($default, $select_name = "language", $dirname="language")
{
	global $bb_cfg;
	if(!$default) $default = $bb_cfg['default_lang'];

	$dir = opendir(BB_ROOT . $dirname);

	$lang = array();
	while ( $file = readdir($dir) )
	{
		if (preg_match('#^lang_#i', $file) && !is_file(@bb_realpath(BB_ROOT . $dirname . '/' . $file)) && !is_link(@bb_realpath(BB_ROOT . $dirname . '/' . $file)))
		{
			$filename = trim(str_replace("lang_", "", $file));
			$displayname = preg_replace("/^(.*?)_(.*)$/", "\\1 [ \\2 ]", $filename);
			$displayname = preg_replace("/\[(.*?)_(.*)\]/", "[ \\1 - \\2 ]", $displayname);
			$lang[$displayname] = $filename;
		}
	}

	closedir($dir);

	@asort($lang);
	@reset($lang);

	$lang_select = '<select name="' . $select_name . '">';
	while ( list($displayname, $filename) = @each($lang) )
	{
		$selected = ( strtolower($default) == strtolower($filename) ) ? ' selected="selected"' : '';
		$lang_select .= '<option value="' . $filename . '"' . $selected . '>' . ucwords($displayname) . '</option>';
	}
	$lang_select .= '</select>';

	return $lang_select;
}

//
// Pick a timezone
//
function tz_select ($default, $select_name = 'timezone')
{
	global $sys_timezone, $lang;

	if (!isset($default))
	{
		$default == $sys_timezone;
	}
	$tz_select = '<select name="' . $select_name . '">';

	while( list($offset, $zone) = @each($lang['TZ']) )
	{
		$selected = ( $offset == $default ) ? ' selected="selected"' : '';
		$tz_select .= '<option value="' . $offset . '"' . $selected . '>' . $zone . '</option>';
	}
	$tz_select .= '</select>';

	return $tz_select;
}

//
// Templates
//
function templates_select ($default_style, $select_name = 'tpl_name')
{
	global $bb_cfg;

	$templates_select = '<select name="'. $select_name .'">';
	$x = 0;
	foreach ($bb_cfg['templates'] as $folder => $name)
	{
		$selected = '';
		if ($folder == $default_style) $selected = ' selected="selected"';
		$templates_select .= '<option value="'. $folder .'"'. $selected .'>'. $name .'</option>';
		$x++;
	}
	$templates_select .= '</select>&nbsp;';
	return ($x > 1) ? $templates_select : '';
}