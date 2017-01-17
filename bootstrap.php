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

require_once __DIR__ . '/vendor/autoload.php';

use TorrentPier\Di;
use TorrentPier\ServiceProviders;

$di = new Di();

$di->register(new ServiceProviders\ConfigServiceProvider, [
    'file.system.main' => __DIR__ . '/configs/main.php',
    'file.local.main' => __DIR__ . '/configs/local.php',
    'config.dbQuery' => "SELECT config_name, config_value FROM bb_config"
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
