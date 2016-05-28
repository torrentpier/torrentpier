<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use TorrentPier\Visitor;

class VisitorServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['visitor'] = function (Container $container) {
            return new Visitor();
        };
    }
}
