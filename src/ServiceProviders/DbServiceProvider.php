<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use TorrentPier\Db\Adapter;
use TorrentPier\Db\Connection;

/**
 * Class DbServiceProvider
 * @package TorrentPier\ServiceProviders
 */
class DbServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['db'] = function ($container) {
            $adapter = new Adapter($container['config.services.db']);
            unset($container['config.services.db']);
            return $adapter;
        };
    }
}
