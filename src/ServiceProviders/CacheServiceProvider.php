<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use TorrentPier\Cache\Adapter;

/**
 * Class CacheServiceProvider
 * @package TorrentPier\ServiceProviders
 */
class CacheServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['cache'] = function (Container $container) {
            $setting = $container['config.services.cache'];
            /** @var Adapter $adapter */
            $adapter = new $setting['adapter']();
            $adapter->setOptions($setting['options']);
            return $adapter;
        };
    }
}
