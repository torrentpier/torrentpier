<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2017 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

//
// Languages
//
function language_select($default_lang, $select_name = 'language')
{
    $lang_default = config('language.lang');
    $lang_select = '<select name="' . $select_name . '">';
    $x = 0;
    foreach (config('language.lang') as $key => $data) {
        $selected = '';
        if ($key == $default_lang) {
            $selected = ' selected="selected"';
        }
        $lang_select .= '<option value="' . $key . '"' . $selected . '>' . $data['name'] . '</option>';
        $x++;
    }
    $lang_select .= '</select>';
    return ($x > 1) ? $lang_select : reset($lang_default)['name'];
}

//
// Pick a timezone
//
function tz_select($default, $select_name = 'timezone')
{
    global $sys_timezone;

    if (!isset($default)) {
        $default = $sys_timezone;
    }
    $tz_select = '<select name="' . $select_name . '">';

    foreach (trans('messages.TZ') as $offset => $zone) {
        $selected = ($offset == $default) ? ' selected="selected"' : '';
        $tz_select .= '<option value="' . $offset . '"' . $selected . '>' . $zone . '</option>';
    }
    $tz_select .= '</select>';

    return $tz_select;
}

//
// Templates
//
function templates_select($default_style, $select_name = 'tpl_name')
{
    $templates_select = '<select name="' . $select_name . '">';
    $x = 0;
    foreach (config('tp.templates') as $folder => $name) {
        $selected = '';
        if ($folder == $default_style) {
            $selected = ' selected="selected"';
        }
        $templates_select .= '<option value="' . $folder . '"' . $selected . '>' . $name . '</option>';
        $x++;
    }
    $templates_select .= '</select>';
    $templates = config('tp.templates');
    return ($x > 1) ? $templates_select : reset($templates);
}
