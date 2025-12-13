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

use TorrentPier\ManticoreSearch;
use TorrentPier\ServiceProvider;

/**
 * Search Service Provider
 *
 * Registers search-related services (Manticore, Sphinx, etc.).
 */
class SearchServiceProvider extends ServiceProvider
{
    /**
     * Register search services
     */
    public function register(): void
    {
        // Manticore search engine
        $this->app->singleton(ManticoreSearch::class, function () {
            return new ManticoreSearch;
        });

        // Register aliases
        $this->app->alias(ManticoreSearch::class, 'manticore');
        $this->app->alias(ManticoreSearch::class, 'search');
    }

    /**
     * Get the services provided by this provider
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            ManticoreSearch::class,
            'manticore',
            'search',
        ];
    }
}
