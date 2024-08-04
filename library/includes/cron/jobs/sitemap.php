<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

$map = new TorrentPier\Sitemap();
$map->createSitemap();

if (is_file(SITEMAP_DIR . '/sitemap.xml')) {
    $map_link = make_url(hide_bb_path(SITEMAP_DIR . '/sitemap.xml'));

    foreach ($bb_cfg['sitemap_sending'] as $source_name => $source_link) {
        $map->sendSitemap($source_link, $map_link);
    }
}

// Cron completed
$cronjob_completed = true;
