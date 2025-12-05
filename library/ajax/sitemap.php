<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

if (!$mode = (string)$this->request['mode']) {
    $this->ajax_die('invalid mode (empty)');
}

$map = new TorrentPier\Sitemap();

$html = '';
switch ($mode) {
    case 'create':
        $map->createSitemap();
        if (is_file(SITEMAP_DIR . '/sitemap.xml')) {
            $html .= __('SITEMAP_CREATED') . ': <b>' . bb_date(TIMENOW, config()->get('post_date_format')) . '</b> ' . __('SITEMAP_AVAILABLE') . ': <a href="' . make_url('sitemap/sitemap.xml') . '" target="_blank">' . make_url('sitemap/sitemap.xml') . '</a>';
        } else {
            $html .= __('SITEMAP_NOT_CREATED');
        }
        break;

    default:
        $this->ajax_die("Invalid mode: $mode");
}

$this->response['html'] = $html;
$this->response['mode'] = $mode;
