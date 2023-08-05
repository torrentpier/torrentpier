<?php

if (!is_file($autoloader = dirname(__DIR__) . '/vendor/autoload.php')) {
    throw new \RuntimeException('Run "composer install --dev" to create the autoloader.');
}

$loader = require $autoloader;
$loader->add('Sphinx\\Tests', __DIR__);
