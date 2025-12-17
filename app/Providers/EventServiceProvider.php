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

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Events\Dispatcher;
use TorrentPier\ServiceProvider;

/**
 * Event Service Provider
 *
 * Registers the event dispatcher for Eloquent model events and application-wide event handling.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * Register event services
     */
    public function register(): void
    {
        $this->app->singleton(Dispatcher::class, fn ($app) => new Dispatcher($app));

        $this->app->alias(Dispatcher::class, DispatcherContract::class);
        $this->app->alias(Dispatcher::class, 'events');
    }

    /**
     * Get the services provided by this provider
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            Dispatcher::class,
            DispatcherContract::class,
            'events',
        ];
    }
}
