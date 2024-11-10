<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_UPDATER')) {
    die(basename(__FILE__));
}

// Changes schema
return [
    'version' => 'v2.4.5',
    'removed_files' => [
        'install/upgrade/changes.txt',
    ]
];
