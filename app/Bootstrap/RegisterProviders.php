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

use ReflectionException;
use TorrentPier\Application;

/**
 * Register service providers
 *
 * This bootstrapper loads and registers all service providers
 * from the bootstrap/providers.php file.
 *
 * Note: Providers are only registered here, not booted.
 * The boot() method is called later by BootProviders bootstrapper.
 */
class RegisterProviders
{
    /**
     * Bootstrap service provider registration
     * @throws ReflectionException
     */
    public function bootstrap(Application $app): void
    {
        $providersFile = $app->basePath('bootstrap/providers.php');

        if (!is_file($providersFile)) {
            return;
        }

        $providers = require $providersFile;

        foreach ($providers as $provider) {
            $app->register($provider);
        }
    }
}
