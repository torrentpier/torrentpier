<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use TorrentPier\Db\Adapter;
use TorrentPier\Db\Connection;

class DbServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['db'] = function ($container) {
            $adapter = new Adapter($container['config.db']);
            unset($container['config.db']);
            return $adapter;
        };
    }
}
