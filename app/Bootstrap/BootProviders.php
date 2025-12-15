<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Bootstrap;

use TorrentPier\Application;

/**
 * Boot service providers
 *
 * This bootstrapper calls boot() on all registered service providers.
 * It should run after RegisterProviders bootstrapper.
 */
class BootProviders
{
    /**
     * Bootstrap service provider booting
     */
    public function bootstrap(Application $app): void
    {
        $app->boot();
    }
}
