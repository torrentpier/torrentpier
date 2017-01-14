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

namespace TorrentPier\Cache;

use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\MemcachedCache;

/**
 * Class FileAdapterTest
 * @package TorrentPier\Cache
 */
class MemoryAdapterTest extends \PHPUnit_Framework_TestCase
{
    /** @var Adapter */
    private $adapterMemcache;

    /** @var Adapter */
    private $adapterMemcached;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        // memcache
        $this->adapterMemcache = $this->getMockBuilder(MemoryAdapter::class)
            ->setMethods(['getMemcache'])
            ->disableOriginalConstructor()
            ->getMock();

        $propMemcache = new \ReflectionProperty(MemoryAdapter::class, 'isMemcached');
        $propMemcache->setAccessible(true);
        $propMemcache->setValue($this->adapterMemcache, false);

        $memcacheMock = $this->getMock('\Memcache', ['getStats']);
        $memcacheMock->method('getStats')->willReturn([]);

        $this->adapterMemcache
            ->method('getMemcache')
            ->willReturn($memcacheMock);

        // memcached
        $this->adapterMemcached = $this->getMockBuilder(MemoryAdapter::class)
            ->setMethods(['getMemcached'])
            ->disableOriginalConstructor()
            ->getMock();

        $propMemcached = new \ReflectionProperty(MemoryAdapter::class, 'isMemcached');
        $propMemcached->setAccessible(true);
        $propMemcached->setValue($this->adapterMemcached, true);

        $memcachedMock = $this->getMock('\Memcached', ['getStats', 'getServerList']);
        $memcachedMock->method('getStats')->willReturn([]);
        $memcachedMock->method('getServerList')->willReturn([['host' => '127.0.0.1', 'port' => 11211]]);

        $this->adapterMemcached
            ->method('getMemcache')
            ->willReturn($memcachedMock);
    }

    /**
     * Check provider for memcache.
     */
    public function testAdapterCacheMemcache()
    {
        self::assertInstanceOf(MemcacheCache::class, $this->adapterMemcache->getProvider());
    }

    /**
     * Check provider for memcached.
     */
    public function testAdapterCacheMemcached()
    {
        self::assertInstanceOf(MemcachedCache::class, $this->adapterMemcached->getProvider());
    }
}
