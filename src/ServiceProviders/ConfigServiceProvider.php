<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2018 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use TorrentPier\Configure\Config;
use TorrentPier\Configure\Reader\ArrayFileReader;

/**
 * Class ConfigServiceProvider
 * @package TorrentPier\ServiceProviders
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container): void
    {
        $container['config'] = function (Container $container) {
            /** @var array $appConfig Array of application configurations */
            $appConfig = [
                new ArrayFileReader($container['file.system.main'])
            ];

            /** Merge with local config file if exists */
            if (file_exists($container['file.local.main'])) {
                $appConfig[] = new ArrayFileReader($container['file.local.main']);
            }

            return new Config($appConfig);
        };
    }
}
