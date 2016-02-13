<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use TorrentPier\Config;
use Zend\Db\Adapter\Adapter;

class DbServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['db'] = function ($container) {
            $config = $container['config.db'];

            if ($config instanceof Config) {
                $config = $config->toArray();
            }

            $adapter = new Adapter($config);
            unset($container['config.db']);

            return $adapter;
        };
    }
}
