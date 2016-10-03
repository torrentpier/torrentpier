<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use SphinxSearch\Db\Adapter\Driver\Pdo\Statement;
use SphinxSearch\Db\Adapter\Exception\UnsupportedDriverException;
use SphinxSearch\Db\Adapter\Platform\SphinxQL;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\Mysqli\Mysqli;
use Zend\Db\Adapter\Driver\Pdo\Pdo;

/**
 * Class SphinxServiceProvider
 * @package TorrentPier\ServiceProviders
 */
class SphinxServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['sphinx'] = function ($container) {
            $platform = new SphinxQL();
            $adapter = new Adapter($container['config.services.sphinx']);

            $driver = $adapter->getDriver();
            // Check driver
            if ($driver instanceof Pdo && $driver->getDatabasePlatformName(Pdo::NAME_FORMAT_CAMELCASE) == 'Mysql') {
                $driver->registerStatementPrototype(new Statement());
            } elseif (!$driver instanceof Mysqli) {
                $class = get_class($driver);
                throw new UnsupportedDriverException(
                    $class . ' not supported. Use Zend\Db\Adapter\Driver\Pdo\Pdo or Zend\Db\Adapter\Driver\Mysqli\Mysqli'
                );
            }

            $platform->setDriver($adapter->getDriver());
            unset($container['config.services.sphinx']);

            return $adapter;
        };
    }
}
