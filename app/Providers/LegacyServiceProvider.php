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

use TorrentPier\Ajax;
use TorrentPier\Censor;
use TorrentPier\Legacy\BBCode;
use TorrentPier\Legacy\Common\Html;
use TorrentPier\Legacy\LogAction;
use TorrentPier\ManticoreSearch;
use TorrentPier\ServiceProvider;

/**
 * Legacy Service Provider
 *
 * Registers legacy services that maintain backward compatibility
 * with older code patterns (BBCode, Ajax, Censor, etc.).
 */
class LegacyServiceProvider extends ServiceProvider
{
    /**
     * Register legacy services
     */
    public function register(): void
    {
        // Censor service for word filtering
        $this->app->singleton(Censor::class);

        // BBCode parser
        $this->app->singleton(BBCode::class, function () {
            return new BBCode;
        });

        // Ajax handler
        $this->app->singleton(Ajax::class, function () {
            return new Ajax;
        });

        // HTML utilities
        $this->app->singleton(Html::class, function () {
            return new Html;
        });

        // Log action service
        $this->app->singleton(LogAction::class, function () {
            return new LogAction;
        });

        // Manticore search (may not be configured)
        $this->app->singleton(ManticoreSearch::class, function () {
            return new ManticoreSearch;
        });

        // Register aliases
        $this->app->alias(Censor::class, 'censor');
        $this->app->alias(BBCode::class, 'bbcode');
        $this->app->alias(Ajax::class, 'ajax');
        $this->app->alias(Html::class, 'html');
        $this->app->alias(LogAction::class, 'log_action');
        $this->app->alias(ManticoreSearch::class, 'manticore');
    }

    /**
     * Get the services provided by this provider
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            Censor::class,
            BBCode::class,
            Ajax::class,
            Html::class,
            LogAction::class,
            ManticoreSearch::class,
            'censor',
            'bbcode',
            'ajax',
            'html',
            'log_action',
            'manticore',
        ];
    }
}
