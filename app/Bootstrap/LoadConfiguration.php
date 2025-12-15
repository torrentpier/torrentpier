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
 * Load application configuration files
 *
 * This bootstrapper handles:
 * - Loading defines.php constants
 * - Loading main config.php
 * - Loading optional config.local.php overrides
 * - Binding config data to container
 */
class LoadConfiguration
{
    /**
     * Bootstrap configuration
     */
    public function bootstrap(Application $app): void
    {
        $basePath = $app->basePath();

        // Load constant definitions
        require_once $basePath . '/library/defines.php';

        // Load main configuration (creates $bb_cfg variable in current scope)
        /** @var array<string, mixed> $bb_cfg */
        require_once $basePath . '/config/config.php';

        // Load local configuration overrides if exists
        $localConfig = $basePath . '/config/config.local.php';
        if (is_file($localConfig)) {
            require_once $localConfig;
        }

        // Bind config data to container
        $app->instance('config.data', $bb_cfg);
    }
}
