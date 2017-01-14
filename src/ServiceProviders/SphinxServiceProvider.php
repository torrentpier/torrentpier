<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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
