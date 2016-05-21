<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use TorrentPier\Config;
use Zend\Config\Factory;

/**
 * Class ConfigServiceProvider
 * @package TorrentPier\ServiceProviders
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['config'] = function ($container) {
            $config = new Config(Factory::fromFile($container['config.file.system.main']));

            if (isset($container['config.file.local.main']) && file_exists($container['config.file.local.main'])) {
                $config->merge(new Config(Factory::fromFile($container['config.file.local.main'])));
            }

            return $config;
        };
    }
}
