<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Zend\Config\Factory;

class ConfigServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['config'] = function ($container) {
            $config = Factory::fromFile($container['config.file.system.main'], true);

            if (isset($container['config.file.local.main']) && file_exists($container['config.file.local.main'])) {
                $config->merge(Factory::fromFile($container['config.file.local.main'], true));
            }

            return $config;
        };
    }
}
