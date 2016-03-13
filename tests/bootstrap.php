<?php

date_default_timezone_set('UTC');

$autoLoader = include __DIR__ . '/../vendor/autoload.php';
$autoLoader->addPsr4('TorrentPier\\', __DIR__ . '/src');
