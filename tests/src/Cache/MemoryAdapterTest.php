<?php

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
