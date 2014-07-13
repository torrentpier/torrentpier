<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

//
// Languages
//
function language_select ($default_lang, $select_name = 'language')
{
	global $bb_cfg;

	$lang_select = '<select name="'. $select_name .'">';
	$x = 0;
	foreach ($bb_cfg['languages'] as $folder => $name)
	{
		$selected = '';
		if ($folder == $default_lang) $selected = ' selected="selected"';
		$lang_select .= '<option value="'. $folder .'"'. $selected .'>'. $name .'</option>';
		$x++;
	}
	$lang_select .= '</select>';
	return ($x > 1) ? $lang_select : reset($bb_cfg['languages']);
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
	$templates_select .= '</select>';
	return ($x > 1) ? $templates_select : reset($bb_cfg['templates']);
}