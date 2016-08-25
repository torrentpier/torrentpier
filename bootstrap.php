<?php

require_once __DIR__ . '/vendor/autoload.php';

use TorrentPier\Di;
use TorrentPier\ServiceProviders;

$di = new Di();

$di->register(new ServiceProviders\ConfigServiceProvider, [
    'file.system.main' => __DIR__ . '/configs/main.php',
    'file.local.main' => __DIR__ . '/configs/local.php',
]);

//// Application
$di->register(new ServiceProviders\LogServiceProvider());
$di->register(new ServiceProviders\CacheServiceProvider());
$di->register(new ServiceProviders\DbServiceProvider());
$di->register(new ServiceProviders\SettingsServiceProvider());
$di->register(new ServiceProviders\VisitorServiceProvider());
$di->register(new ServiceProviders\RequestServiceProvider());

// Services
//$di->register(new \TorrentPier\ServiceProviders\SphinxServiceProvider());

// View and Templates
$di->register(new ServiceProviders\TranslationServiceProvider());
$di->register(new ServiceProviders\CaptchaServiceProvider());
$di->register(new ServiceProviders\TwigServiceProvider());
$di->register(new ServiceProviders\ViewServiceProvider());
