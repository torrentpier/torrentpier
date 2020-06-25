<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2018 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

// Composer
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die('Please <a href="https://getcomposer.org/download/" target="_blank" rel="noreferrer" style="color:#0a25bb;">install composer</a> and run <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">composer install</code>');
}

require_once __DIR__ . '/vendor/autoload.php';

use TorrentPier\Di;
use TorrentPier\ServiceProviders;

if (!getenv('APP_DEBUG') && file_exists(__DIR__ . '/.env')) {
    (new Symfony\Component\Dotenv\Dotenv())->load(__DIR__ . '/.env');
}

$di = new Di;

$di->register(new ServiceProviders\ConfigServiceProvider, [
    'file.system.main' => __DIR__ . '/src/config.php',
    'file.local.main' => __DIR__ . '/src/config.local.php',
]);

$di->register(new ServiceProviders\CaptchaServiceProvider());
$di->register(new ServiceProviders\LogServiceProvider());
$di->register(new ServiceProviders\RequestServiceProvider());
$di->register(new ServiceProviders\TemplateServiceContainer());
$di->register(new ServiceProviders\ResponseServiceProvider());
