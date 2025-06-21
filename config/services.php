<?php

use function DI\factory;
use function DI\get;
use function DI\autowire;

return [
    // Add custom service definitions here as they are implemented

    // Examples (uncomment and modify when implementing):

    // Logger service example
    // 'logger' => factory(function () {
    //     $logger = new \Monolog\Logger('torrentpier');
    //     $logger->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/../internal_data/logs/app.log'));
    //     return $logger;
    // }),

    // Configuration service example
    // 'config' => factory(function () {
    //     return [
    //         'app' => require __DIR__ . '/app.php',
    //         'database' => require __DIR__ . '/database.php',
    //         'cache' => require __DIR__ . '/cache.php',
    //     ];
    // }),

    // Interface to implementation binding example
    // 'ServiceInterface' => autowire('ConcreteService'),
];
