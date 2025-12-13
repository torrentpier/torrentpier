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

use TorrentPier\Legacy\Common\User;
use TorrentPier\ReadTracker;
use TorrentPier\ServiceProvider;

/**
 * Session Service Provider
 *
 * Registers user session and read tracking services.
 */
class SessionServiceProvider extends ServiceProvider
{
    /**
     * Register session services
     */
    public function register(): void
    {
        // User session singleton
        $this->app->singleton(User::class, function () {
            return User::getInstance();
        });

        // Read tracker for topic/forum read status
        $this->app->singleton(ReadTracker::class, function () {
            return ReadTracker::getInstance();
        });

        // Register aliases
        $this->app->alias(User::class, 'user');
        $this->app->alias(ReadTracker::class, 'read_tracker');
    }

    /**
     * Get the services provided by this provider
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            User::class,
            ReadTracker::class,
            'user',
            'read_tracker',
        ];
    }
}
