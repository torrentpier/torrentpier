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

use TorrentPier\Legacy\BBCode;
use TorrentPier\Legacy\Common\Html;
use TorrentPier\Legacy\Common\User;
use TorrentPier\Legacy\LogAction;
use TorrentPier\ServiceProvider;

/**
 * Legacy Service Provider
 *
 * Registers legacy services from TorrentPier\Legacy namespace
 * that maintain backward compatibility with older code patterns.
 */
class LegacyServiceProvider extends ServiceProvider
{
    /**
     * Register legacy services
     */
    public function register(): void
    {
        // BBCode parser
        $this->app->singleton(BBCode::class, function () {
            return new BBCode;
        });

        // HTML utilities
        $this->app->singleton(Html::class, function () {
            return new Html;
        });

        // Log action service
        $this->app->singleton(LogAction::class, function () {
            return new LogAction;
        });

        // User session (legacy)
        $this->app->singleton(User::class, function () {
            return User::getInstance();
        });

        // Register aliases
        $this->app->alias(BBCode::class, 'bbcode');
        $this->app->alias(Html::class, 'html');
        $this->app->alias(LogAction::class, 'log_action');
        $this->app->alias(User::class, 'user');
    }

    /**
     * Get the services provided by this provider
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            BBCode::class,
            Html::class,
            LogAction::class,
            User::class,
            'bbcode',
            'html',
            'log_action',
            'user',
        ];
    }
}
