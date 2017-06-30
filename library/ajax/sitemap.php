<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2017 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

$mode = (string)$this->request['mode'];
$map = new TorrentPier\Legacy\Sitemap();
$html = '';

switch ($mode) {
    case 'create':
        $map->createSitemap();
        if (file_exists(SITEMAP_DIR . '/sitemap.xml')) {
            $html .= trans('messages.SITEMAP_CREATED') . ': <b>' . bb_date(TIMENOW, config('tp.post_date_format')) . '</b> ' . trans('messages.SITEMAP_AVAILABLE') . ': <a href="' . make_url('sitemap/sitemap.xml') . '" target="_blank">' . make_url('sitemap/sitemap.xml') . '</a>';
        } else {
            $html .= trans('messages.SITEMAP_NOT_CREATED');
        }
        break;

    case 'search_update':
        if (!file_exists(SITEMAP_DIR . '/sitemap.xml')) {
            $map->createSitemap();
        }

        $map_link = make_url('sitemap/sitemap.xml');

        if ($map->sendSitemap('http://google.com/webmasters/sitemaps/ping?sitemap=', $map_link)) {
            $html .= '<br />' . trans('messages.SITEMAP_NOTIFY_SEARCH') . ' Google: <span style="color: green;">' . trans('messages.SITEMAP_SENT') . '</span>';
        } else {
            $html .= '<br />' . trans('messages.SITEMAP_NOTIFY_SEARCH') . ' Google: <span style="color: red;">' . trans('messages.SITEMAP_ERROR') . '</span> URL: <a href="http://google.com/webmasters/sitemaps/ping?sitemap=' . urlencode($map_link) . '" target="_blank">http://google.com/webmasters/sitemaps/ping?sitemap=' . $map_link . '</a>';
        }

        if ($map->sendSitemap('http://ping.blogs.yandex.ru/ping?sitemap=', $map_link)) {
            $html .= '<br />' . trans('messages.SITEMAP_NOTIFY_SEARCH') . ' Yandex: <span style="color: green;">' . trans('messages.SITEMAP_SENT') . '</span>';
        } else {
            $html .= '<br />' . trans('messages.SITEMAP_NOTIFY_SEARCH') . ' Yandex: <span style="color: red;">' . trans('messages.SITEMAP_ERROR') . '</span> URL: <a href="http://ping.blogs.yandex.ru/ping?sitemap=' . urlencode($map_link) . '" target="_blank">http://ping.blogs.yandex.ru/ping?sitemap=' . $map_link . '</a>';
        }

        if ($map->sendSitemap('http://www.bing.com/ping?sitemap=', $map_link)) {
            $html .= '<br />' . trans('messages.SITEMAP_NOTIFY_SEARCH') . ' Bing: <span style="color: green;">' . trans('messages.SITEMAP_SENT') . '</span>';
        } else {
            $html .= '<br />' . trans('messages.SITEMAP_NOTIFY_SEARCH') . ' Bing: <span style="color: red;">' . trans('messages.SITEMAP_ERROR') . '</span> URL: <a href="http://www.bing.com/ping?sitemap=' . urlencode($map_link) . '" target="_blank">http://www.bing.com/ping?sitemap=' . $map_link . '</a>';
        }
}

$this->response['html'] = $html;
$this->response['mode'] = $mode;
