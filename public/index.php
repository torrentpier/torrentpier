<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * Front Controller Entry Point
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('TORRENTPIER_START', microtime(true));
define('FRONT_CONTROLLER', true);

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->handleRequest();
