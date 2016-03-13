<?php

namespace TorrentPier\ServiceProviders;

use Monolog\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class LogServiceProvider
 * @package TorrentPier\ServiceProviders
 */
class LogServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['logger'] = function ($container) {
            return function ($name) {
                return new Logger(strtoupper($name));
            };
        };

        $container['log'] = function ($container) {
            /** @var Logger $logger */
            $logger = $container['logger']('app');
            foreach ($container['config.log.handlers'] as $logHandler) {
                $logger->pushHandler(call_user_func($logHandler));
            }

            return $logger;
        };
    }
}
