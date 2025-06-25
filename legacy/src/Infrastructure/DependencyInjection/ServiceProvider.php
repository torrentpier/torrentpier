<?php

declare(strict_types=1);

namespace TorrentPier\Infrastructure\DependencyInjection;

interface ServiceProvider
{
    /**
     * Register services in the container.
     *
     * @param Container $container
     *
     * @return void
     */
    public function register(Container $container): void;

    /**
     * Bootstrap services after all providers have been registered.
     *
     * @param Container $container
     *
     * @return void
     */
    public function boot(Container $container): void;
}
