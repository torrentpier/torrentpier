<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2017 TorrentPier (https://torrentpier.me)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

$announce_urls = array();

// Here you can define additional allowed announce urls
// For example, if you want to add http://demo.torrentpier.me
// add this line: $announce_urls[] = 'http://demo.torrentpier.me/bt/announce.php';

// $announce_urls[] = 'http://demo.torrentpier.me/bt/announce.php';
