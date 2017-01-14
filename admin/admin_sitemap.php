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
require('./pagestart.php');

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

/** @var \TorrentPier\Cache\Adapter $cache */
$cache = $di->cache;

if (request_var('submit', '')) {
    if (bb_update_config(array('static_sitemap' => request_var('static_sitemap', '')))) {
        // TODO: delete only necessary cache
        $cache->flush();
    }
    bb_die('<a href="admin_sitemap.php">' . $lang['GO_BACK'] . '</a>');
}

$s_mess = $lang['SITEMAP_CREATED'] . ': <b>' . bb_date($di->config->get('sitemap_time'), $di->config->get('post_date_format')) . '</b> ' . $lang['SITEMAP_AVAILABLE'] . ': <a href="' . make_url('sitemap.xml') . '" target="_blank">' . make_url('sitemap.xml') . '</a>';

$template->assign_vars(array(
    'STATIC_SITEMAP' => $di->config->get('static_sitemap'),
    'MESSAGE' => (file_exists(INT_DATA_DIR . "sitemap/sitemap.xml")) ? $s_mess : $lang['SITEMAP_NOT_CREATED'],
));

print_page('admin_sitemap.tpl', 'admin');
