<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Zend\Authentication\AuthenticationService;

/**
 * Class AuthServiceProvider
 * @package TorrentPier\ServiceProviders
 */
class AuthServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['auth'] = function ($container) {
            return new AuthenticationService(isset($container['config.service.auth.storage']) ?: null);
        };
    }
}
