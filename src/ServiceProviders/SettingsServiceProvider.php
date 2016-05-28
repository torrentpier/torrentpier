<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use TorrentPier\Settings;

class SettingsServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['settings'] = function (Container $container) {
            return new Settings($container['db'], $container['cache']);
        };
    }
}
