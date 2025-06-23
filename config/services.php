<?php

declare(strict_types=1);

/**
 * Service Container Bindings
 * 
 * Define service bindings for the Illuminate Container
 */

return [
    // Config service binding
    \TorrentPier\Config::class => function () {
        return \TorrentPier\Config::getInstance();
    },

    // Future service bindings can be added here:
    
    // Logger service example
    // 'logger' => function () {
    //     $logger = new \Monolog\Logger('torrentpier');
    //     $logger->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/../internal_data/logs/app.log'));
    //     return $logger;
    // },

    // Database service example
    // 'database' => function () {
    //     return \TorrentPier\Database\DB::getInstance();
    // },

    // Cache service example  
    // 'cache' => function () {
    //     return \TorrentPier\Cache\Cache::getInstance();
    // },
];
