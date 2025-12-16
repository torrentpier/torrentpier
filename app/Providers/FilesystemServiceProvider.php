<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Filesystem\Filesystem as IlluminateFilesystem;
use TorrentPier\Filesystem\Filesystem;
use TorrentPier\ServiceProvider;

/**
 * Filesystem Service Provider
 *
 * Registers filesystem services including the TorrentPier wrapper
 * around Illuminate\Filesystem with log rotation and umask handling.
 */
class FilesystemServiceProvider extends ServiceProvider
{
    /**
     * Register filesystem services
     */
    public function register(): void
    {
        // Base Illuminate Filesystem
        $this->app->singleton(IlluminateFilesystem::class);

        // TorrentPier wrapper with custom features
        $this->app->singleton(
            Filesystem::class,
            fn ($app) =>
            new Filesystem($app->make(IlluminateFilesystem::class)),
        );

        // Register alias for convenience
        $this->app->alias(Filesystem::class, 'files');
    }

    /**
     * Get the services provided by this provider
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            IlluminateFilesystem::class,
            Filesystem::class,
            'files',
        ];
    }
}
