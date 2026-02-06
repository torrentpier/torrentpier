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

use App\Models\Post;
use App\Models\PostText;
use App\Models\Topic;
use App\Models\User;
use App\Observers\PostObserver;
use App\Observers\PostTextObserver;
use App\Observers\TopicObserver;
use App\Observers\UserObserver;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use TorrentPier\ServiceProvider;
use TorrentPier\Tracy\Collectors\EloquentCollector;

/**
 * Eloquent Service Provider
 *
 * Registers Eloquent ORM with Capsule Manager for standalone usage.
 * Works alongside Nette Database for gradual migration to Laravel 12.
 */
class EloquentServiceProvider extends ServiceProvider
{
    /**
     * Register Eloquent services
     */
    public function register(): void
    {
        $this->app->singleton(Capsule::class, function ($app) {
            $capsule = new Capsule;

            // Get default connection config from config/database.php
            $default = config()->get('database.default');
            $connectionConfig = config()->get("database.connections.$default");

            if (!$connectionConfig || !\is_array($connectionConfig)) {
                throw new RuntimeException("Database connection '{$default}' is not configured or invalid.");
            }

            $capsule->addConnection($connectionConfig);

            // Set event dispatcher for model events (required for Observers)
            if ($app->bound(Dispatcher::class)) {
                $capsule->setEventDispatcher($app->make(Dispatcher::class));
            }

            // Make Capsule available globally via static methods
            $capsule->setAsGlobal();
            $capsule->bootEloquent();

            return $capsule;
        });

        $this->app->alias(Capsule::class, 'eloquent');
    }

    /**
     * Bootstrap Eloquent services
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        // Ensure Capsule is initialized
        $capsule = $this->app->make(Capsule::class);

        // Configure Eloquent strict mode for development
        $debugEnabled = (bool)config()->get('debug.enable', false);
        Model::preventLazyLoading($debugEnabled);
        Model::preventSilentlyDiscardingAttributes($debugEnabled);

        // Register query listener for Tracy debug bar
        if ($debugEnabled) {
            $capsule->getConnection()->listen(function ($query) {
                EloquentCollector::recordQuery($query->sql, $query->bindings, $query->time);
            });
        }

        // Register observers for ManticoreSearch synchronization
        $this->registerObservers();
    }

    /**
     * Register model observers for search index synchronization
     * @throws BindingResolutionException
     */
    protected function registerObservers(): void
    {
        // Only register observers if ManticoreSearch is enabled
        if (config()->get('forum.search_engine_type') !== 'manticore') {
            return;
        }

        Topic::observe(TopicObserver::class);
        Post::observe(PostObserver::class);
        PostText::observe(PostTextObserver::class);
        User::observe(UserObserver::class);
    }

    /**
     * Get the services provided by this provider
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            Capsule::class,
            'eloquent',
        ];
    }
}
