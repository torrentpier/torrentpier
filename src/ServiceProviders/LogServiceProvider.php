<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2018 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

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
    public function register(Container $container): void
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
                $logger->pushHandler($logHandler());
            }

            return $logger;
        };
    }
}
