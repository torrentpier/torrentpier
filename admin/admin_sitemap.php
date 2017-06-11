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

if (!empty($setmodules)) {
    $module['MODS']['SITEMAP'] = basename(__FILE__);
    return;
}
require __DIR__ . '/pagestart.php';
require INC_DIR . '/functions_selects.php';

$sql = 'SELECT * FROM ' . BB_CONFIG;

if (!$result = DB()->sql_query($sql)) {
    bb_die('Could not query config information in admin_sitemap');
} else {
    $new_params = array();

    while ($row = DB()->sql_fetchrow($result)) {
        $config_name = $row['config_name'];
        $config_value = $row['config_value'];
        $default_config[$config_name] = $config_value;
        $new[$config_name] = isset($_POST[$config_name]) ? $_POST[$config_name] : $default_config[$config_name];

        if (isset($_POST['submit']) && $row['config_value'] != $new[$config_name]) {
            $new_params[$config_name] = $new[$config_name];
        }
    }

    if (isset($_POST['submit'])) {
        if (!empty($new_params)) {
            bb_update_config($new_params);
        }
    }
}

$s_mess = $lang['SITEMAP_CREATED'] . ': <b>' . bb_date($new['sitemap_time'], $bb_cfg['post_date_format']) . '</b> ' . $lang['SITEMAP_AVAILABLE'] . ': <a href="' . make_url('sitemap/sitemap.xml') . '" target="_blank">' . make_url('sitemap/sitemap.xml') . '</a>';
$message = file_exists(SITEMAP_DIR . '/sitemap.xml') ? $s_mess : $lang['SITEMAP_NOT_CREATED'];

$template->assign_vars(array(
    'STATIC_SITEMAP' => $new['static_sitemap'],
    'MESSAGE' => $message,
));

print_page('admin_sitemap.tpl', 'admin');
