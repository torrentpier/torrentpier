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

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $bb_cfg, $lang;

$mode = (string)$this->request['mode'];
$map = new TorrentPier\Legacy\Sitemap();
$html = '';

switch ($mode) {
    case 'create':
        $map->createSitemap();
        if (file_exists(SITEMAP_DIR . '/sitemap.xml')) {
            $html .= $lang['SITEMAP_CREATED'] . ': <b>' . bb_date(TIMENOW, $bb_cfg['post_date_format']) . '</b> ' . $lang['SITEMAP_AVAILABLE'] . ': <a href="' . make_url('sitemap/sitemap.xml') . '" target="_blank">' . make_url('sitemap/sitemap.xml') . '</a>';
        } else {
            $html .= $lang['SITEMAP_NOT_CREATED'];
        }
        break;

    case 'search_update':
        if (!file_exists(SITEMAP_DIR . '/sitemap.xml')) {
            $map->createSitemap();
        }

        $map_link = make_url('sitemap/sitemap.xml');

        if ($map->sendSitemap('http://google.com/webmasters/sitemaps/ping?sitemap=', $map_link)) {
            $html .= '<br />' . $lang['SITEMAP_NOTIFY_SEARCH'] . ' Google: <span style="color: green;">' . $lang['SITEMAP_SENT'] . '</span>';
        } else {
            $html .= '<br />' . $lang['SITEMAP_NOTIFY_SEARCH'] . ' Google: <span style="color: red;">' . $lang['SITEMAP_ERROR'] . '</span> URL: <a href="http://google.com/webmasters/sitemaps/ping?sitemap=' . urlencode($map_link) . '" target="_blank">http://google.com/webmasters/sitemaps/ping?sitemap=' . $map_link . '</a>';
        }

        if ($map->sendSitemap('http://ping.blogs.yandex.ru/ping?sitemap=', $map_link)) {
            $html .= '<br />' . $lang['SITEMAP_NOTIFY_SEARCH'] . ' Yandex: <span style="color: green;">' . $lang['SITEMAP_SENT'] . '</span>';
        } else {
            $html .= '<br />' . $lang['SITEMAP_NOTIFY_SEARCH'] . ' Yandex: <span style="color: red;">' . $lang['SITEMAP_ERROR'] . '</span> URL: <a href="http://ping.blogs.yandex.ru/ping?sitemap=' . urlencode($map_link) . '" target="_blank">http://ping.blogs.yandex.ru/ping?sitemap=' . $map_link . '</a>';
        }

        if ($map->sendSitemap('http://www.bing.com/ping?sitemap=', $map_link)) {
            $html .= '<br />' . $lang['SITEMAP_NOTIFY_SEARCH'] . ' Bing: <span style="color: green;">' . $lang['SITEMAP_SENT'] . '</span>';
        } else {
            $html .= '<br />' . $lang['SITEMAP_NOTIFY_SEARCH'] . ' Bing: <span style="color: red;">' . $lang['SITEMAP_ERROR'] . '</span> URL: <a href="http://www.bing.com/ping?sitemap=' . urlencode($map_link) . '" target="_blank">http://www.bing.com/ping?sitemap=' . $map_link . '</a>';
        }
}

$this->response['html'] = $html;
$this->response['mode'] = $mode;
