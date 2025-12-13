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

use RuntimeException;
use TorrentPier\ServiceProvider;
use TorrentPier\Template\Template;

/**
 * Template Service Provider
 *
 * Registers template rendering services.
 */
class TemplateServiceProvider extends ServiceProvider
{
    /**
     * Register template services
     *
     * Template requires a root directory that is determined at runtime
     * based on user preferences. We register a protective binding that
     * throws an exception if accessed before setup_style() initializes it.
     */
    public function register(): void
    {
        $this->app->singleton(Template::class, function () {
            throw new RuntimeException(
                'Template not initialized. Ensure setup_style() is called before using template().',
            );
        });

        // Register alias
        $this->app->alias(Template::class, 'template');
    }

    /**
     * Get the services provided by this provider
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            Template::class,
            'template',
        ];
    }
}
