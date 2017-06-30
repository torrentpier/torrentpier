<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2017 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Events\StatementPrepared;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Symfony\Component\Finder\Finder;

try {
    (new Dotenv(__DIR__))->load();
} catch (InvalidPathException $e) {
    throw $e;
}

/**
 * Service Container
 * @var Container $container
 */
$container = new Container;
Container::setInstance($container);

/**
 * Events
 */
$container->instance('events', new Dispatcher);

/**
 * Database
 */
$container->singleton('db', function ($container) {
    /** @var Manager $capsule */
    $capsule = new Manager;

    $capsule->addConnection([
        'driver' => 'mysql',
        'host' => env('DB_HOST', 'localhost'),
        'database' => env('DB_DATABASE', 'torrentpier'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', 'pass'),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
    ]);

    $capsule->setEventDispatcher($container['events']);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
});
$container->instance(DB::class, $container->make('db'));

/**
 * Database setFetchMode
 * @var Dispatcher $dispatcher
 */
$dispatcher = $container->make('events');
$dispatcher->listen(StatementPrepared::class, function ($event) {
    $event->statement->setFetchMode(PDO::FETCH_ASSOC);
});

/**
 * Request
 */
$container->instance('request', Illuminate\Http\Request::capture());

/**
 * Filesystem
 */
$container->instance('files', new Filesystem);

/**
 * Config
 */
$container->singleton('config', function () {
    /** @var Repository $config */
    $config = new Repository;

    $files = [];

    $configPath = __DIR__ . '/config';

    /** @noinspection ForeachSourceInspection */
    foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
        /** @var \SplFileInfo $file */
        $configFile = $file->getRealPath();
        $files[basename($configFile, '.php')] = $configFile;
    }

    foreach ($files as $key => $path) {
        if ($key === 'tp') {
            // if (!$cfg = OLD_CACHE('bb_config')->get('config_bb_config')) {
            $cfg = [];
            foreach (DB::table('bb_config')->get()->toArray() as $row) {
                $cfg[$row['config_name']] = $row['config_value'];
            }
            // }
            /** @noinspection PhpIncludeInspection */
            $config->set($key, array_merge(require $path, $cfg));
        } else {
            /** @noinspection PhpIncludeInspection */
            $config->set($key, require $path);
        }
    }

    $config->set('cache', [
        'default' => 'file',
        'stores' => [
            'file' => [
                'driver' => 'file',
                'path' => $config->get('tp.cache.db_dir'),
            ],
        ],
    ]);

    $config->set('app.locale', 'ru');
    $config->set('app.fallback_locale', 'source');

    return $config;
});

/**
 * Cache
 */
$container->singleton('cache', function ($container) {
    /** @var CacheManager $cache */
    $cache = new CacheManager($container);

    return $cache->driver();
});

/**
 * Localization
 */
$container->singleton('translator', function ($app) {
    $loader = $app['translation.loader'];
    $locale = $app['config']['app.locale'];

    $trans = new Translator($loader, $locale);

    $trans->setFallback($app['config']['app.fallback_locale']);

    return $trans;
});

$container->singleton('translation.loader', function ($app) {
    return new FileLoader($app['files'], __DIR__ . '/resources/lang');
});
