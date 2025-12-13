<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier;

/**
 * Base Service Provider
 *
 * Abstract base class for all service providers.
 * Service providers are the central place to configure your application.
 */
abstract class ServiceProvider
{
    /**
     * The application instance
     */
    protected Application $app;

    /**
     * All the registered bindings
     *
     * @var array<string, string|callable>
     */
    public array $bindings = [];

    /**
     * All the singletons that should be registered
     *
     * @var array<string, string|callable>
     */
    public array $singletons = [];

    /**
     * Create a new service provider instance
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Register any application services
     *
     * This method is called immediately when the provider is registered.
     * Only bind things into the container here - don't attempt to use
     * any other services as they may not be loaded yet.
     */
    abstract public function register(): void;

    /**
     * Bootstrap any application services
     *
     * This method is called after all other service providers have
     * been registered, meaning you have access to all other services
     * that have been registered by the framework.
     */
    public function boot(): void
    {
        // Override in subclass if needed
    }

    /**
     * Get the services provided by the provider
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [];
    }

    /**
     * Determine if the provider is deferred
     */
    public function isDeferred(): bool
    {
        return false;
    }

    /**
     * Get the default providers for a TorrentPier application
     *
     * @return class-string<ServiceProvider>[]
     */
    public static function defaultProviders(): array
    {
        return [
            \App\Providers\ConfigServiceProvider::class,
            \App\Providers\ErrorHandlerServiceProvider::class,
            \App\Providers\DatabaseServiceProvider::class,
            \App\Providers\CacheServiceProvider::class,
            \App\Providers\SessionServiceProvider::class,
            \App\Providers\HttpServiceProvider::class,
            \App\Providers\TemplateServiceProvider::class,
            \App\Providers\LegacyServiceProvider::class,
            \App\Providers\AppServiceProvider::class,
        ];
    }
}
