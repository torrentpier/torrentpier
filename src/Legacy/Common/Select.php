<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy\Common;

/**
 * Class Select
 * @package TorrentPier\Legacy\Common
 */
class Select
{
    /**
     * Select forum language
     *
     * @param string $default_lang
     * @param string $select_name
     *
     * @return mixed
     */
    public static function language(string $default_lang, string $select_name = 'language'): mixed
    {
        $lang_select = '<select name="' . $select_name . '">';
        $x = 0;
        foreach (config()->get('lang') as $key => $data) {
            $selected = '';
            if ($key == $default_lang) {
                $selected = ' selected';
            }
            $lang_select .= '<option value="' . $key . '"' . $selected . '>' . $data['name'] . '</option>';
            $x++;
        }
        $lang_select .= '</select>';
        $languages = config()->get('lang');
        return ($x > 1) ? $lang_select : reset($languages);
    }

    /**
     * Select forum timezone
     *
     * @param string $default
     * @param string $select_name
     *
     * @return string
     */
    public static function timezone(string $default = '', string $select_name = 'timezone'): string
    {
        if ($default === '') {
            $default = (string)config()->get('board_timezone');
        }
        $tz_select = '<select name="' . $select_name . '">';

        foreach (config()->get('timezones') as $offset => $zone) {
            $selected = ($offset == $default) ? ' selected' : '';
            $tz_select .= '<option value="' . $offset . '"' . $selected . '>' . $zone . '</option>';
        }
        $tz_select .= '</select>';

        return $tz_select;
    }

    /**
     * Select forum template
     *
     * @param string $default_style
     * @param string $select_name
     *
     * @return mixed
     */
    public static function template(string $default_style, string $select_name = 'tpl_name'): mixed
    {
        $templates_select = '<select name="' . $select_name . '">';
        $x = 0;
        foreach (config()->get('templates') as $folder => $name) {
            $selected = '';
            if ($folder == $default_style) {
                $selected = ' selected';
            }
            $templates_select .= '<option value="' . $folder . '"' . $selected . '>' . $name . '</option>';
            $x++;
        }
        $templates_select .= '</select>';
        $templates = config()->get('templates');
        return ($x > 1) ? $templates_select : reset($templates);
    }
}
