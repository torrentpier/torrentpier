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

use ReflectionException;
use TorrentPier\Language;
use TorrentPier\ServiceProvider;
use TorrentPier\Template\Template;

/**
 * Template Service Provider
 *
 * Registers template and language services.
 */
class TemplateServiceProvider extends ServiceProvider
{
    /**
     * Register template services
     * @throws ReflectionException
     */
    public function register(): void
    {
        // Language service singleton
        $this->app->singleton(Language::class, function () {
            return Language::getInstance();
        });

        // Template service - uses keyed instances internally,
        // so we bind a closure that delegates to the singleton pattern
        $this->app->bind(Template::class, function ($app, array $params) {
            $root = $params['root'] ?? null;

            return Template::getInstance($root);
        });

        // Default template instance (without root parameter)
        $this->app->singleton('template.default', function () {
            return Template::getInstance();
        });

        // Register aliases
        $this->app->alias(Language::class, 'lang');
        $this->app->alias('template.default', 'template');
    }

    /**
     * Get the services provided by this provider
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            Language::class,
            Template::class,
            'lang',
            'template',
            'template.default',
        ];
    }
}
