<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

//
// Languages
//
function language_select($default_lang, $select_name = 'language')
{
    global $bb_cfg;

    $lang_default = reset($bb_cfg['lang']);
    $lang_select = '<select name="' . $select_name . '">';
    $x = 0;
    foreach ($bb_cfg['lang'] as $key => $data) {
        $selected = '';
        if ($key == $default_lang) {
            $selected = ' selected="selected"';
        }
        $lang_select .= '<option value="' . $key . '"' . $selected . '>' . $data['name'] . '</option>';
        $x++;
    }
    $lang_select .= '</select>';
    return ($x > 1) ? $lang_select : $lang_default['name'];
}

//
// Pick a timezone
//
function tz_select($default, $select_name = 'timezone')
{
    global $sys_timezone, $lang;

    if (!isset($default)) {
        $default == $sys_timezone;
    }
    $tz_select = '<select name="' . $select_name . '">';

    foreach ($lang['TZ'] as $offset => $zone) {
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
    global $bb_cfg;

    $templates_select = '<select name="' . $select_name . '">';
    $x = 0;
    foreach ($bb_cfg['templates'] as $folder => $name) {
        $selected = '';
        if ($folder == $default_style) {
            $selected = ' selected="selected"';
        }
        $templates_select .= '<option value="' . $folder . '"' . $selected . '>' . $name . '</option>';
        $x++;
    }
    $templates_select .= '</select>';
    return ($x > 1) ? $templates_select : reset($bb_cfg['templates']);
}
