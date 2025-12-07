<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * CLI entry point for cron jobs.
 * Usage: php cron.php
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (PHP_SAPI !== 'cli') {
    die('This script must be run from the command line.');
}

define('BB_ROOT', __DIR__ . '/');

require __DIR__ . '/common.php';

// Force run cron jobs
TorrentPier\Helpers\CronHelper::run(force: true);
