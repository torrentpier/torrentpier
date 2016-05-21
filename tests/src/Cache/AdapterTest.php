<?php

namespace TorrentPier\Cache;

use Doctrine\Common\Cache\CacheProvider;

class AdapterTest extends \PHPUnit_Framework_TestCase
{
    /** @var Adapter|\PHPUnit_Framework_MockObject_MockObject */
    private $adapter;

    /** @var CacheProvider|\PHPUnit_Framework_MockObject_MockObject */
    private $cacheProvider;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->cacheProvider = $this->getMock(CacheProvider::class,
            ['doFetch', 'doContains', 'doSave', 'doDelete', 'doFlush', 'doGetStats']);

        $this->adapter = $this->getMock(Adapter::class, ['getProvider', 'getType']);
        $this->adapter->method('getProvider')->willReturn($this->cacheProvider);
        $this->adapter->method('getType')->willReturn('Void Cache for tests');
    }

    /**
     * @covers TorrentPier\Cache\Adapter::has
     */
    public function testHas()
    {
        $this->cacheProvider->expects(self::once())->method('doContains')
            ->with('namespaceTest[keyTest][1]')->willReturn(false);

        self::assertEquals(false, $this->adapter->has('namespaceTest::keyTest'));
    }

    /**
     * @covers TorrentPier\Cache\Adapter::get
     */
    public function testGet()
    {
        $this->cacheProvider->expects(self::at(0))->method('doFetch')
            ->with('DoctrineNamespaceCacheKey[namespaceTest]')->willReturn(false);

        $this->cacheProvider->expects(self::at(1))->method('doFetch')
            ->with('namespaceTest[keyTest][1]')->willReturn(false);

        self::assertEquals(false, $this->adapter->get('namespaceTest::keyTest'));
    }

    /**
     * @covers TorrentPier\Cache\Adapter::get
     */
    public function testGetCallback()
    {
        $this->cacheProvider->expects(self::at(0))->method('doFetch')
            ->with('DoctrineNamespaceCacheKey[namespaceTest]')->willReturn(false);

        $this->cacheProvider->expects(self::at(1))->method('doFetch')
            ->with('namespaceTest[keyTest][1]')->willReturn(false);

        $callback = function($cache, $key) {
            return [$cache instanceof Adapter, $key];
        };

        self::assertEquals([true, 'namespaceTest::keyTest'], $this->adapter->get('namespaceTest::keyTest', $callback));
    }

    /**
     * @covers TorrentPier\Cache\Adapter::set
     */
    public function testSet()
    {
        $this->cacheProvider->expects(self::at(0))->method('doFetch')
            ->with('DoctrineNamespaceCacheKey[namespaceTest]')->willReturn(false);

        $this->cacheProvider->expects(self::at(1))->method('doSave')
            ->with('namespaceTest[keyTest][1]', [1,2,3,4], 10)->willReturn(true);

        self::assertEquals(true, $this->adapter->set('namespaceTest::keyTest', [1,2,3,4], 10));
    }

    public function testGets()
    {
        $this->cacheProvider->expects(self::at(0))->method('doFetch')
            ->with('DoctrineNamespaceCacheKey[namespaceTest]')->willReturn(false);

        $this->cacheProvider->expects(self::at(1))->method('doFetch')
            ->with('namespaceTest[keyTest][1]')->willReturn('testValue');

        self::assertEquals(['keyTest' => 'testValue'], $this->adapter->gets(['keyTest'], 'namespaceTest'));
    }

    public function testSets()
    {
        $this->cacheProvider->expects(self::at(0))->method('doFetch')
            ->with('DoctrineNamespaceCacheKey[namespaceTest]')->willReturn(false);

        $this->cacheProvider->expects(self::at(1))->method('doSave')
            ->with('namespaceTest[keyTest][1]', [1,2,3,4], 10)->willReturn(true);

        self::assertEquals(true, $this->adapter->sets(['keyTest' => [1,2,3,4]], 'namespaceTest', 10));
    }

    /**
     * @covers TorrentPier\Cache\Adapter::delete
     */
    public function testDelete()
    {
        $this->cacheProvider->expects(self::at(0))->method('doFetch')
            ->with('DoctrineNamespaceCacheKey[namespaceTest]')->willReturn(false);

        $this->cacheProvider->expects(self::at(1))->method('doDelete')
            ->with('namespaceTest[keyTest][1]')->willReturn(true);

        self::assertEquals(true, $this->adapter->delete('namespaceTest::keyTest'));
    }

    /**
     * @covers TorrentPier\Cache\Adapter::deleteAll
     */
    public function testDeleteAll()
    {
        $this->cacheProvider->expects(self::at(0))->method('doFetch')
            ->with('DoctrineNamespaceCacheKey[namespaceTest]')->willReturn(false);

        $this->cacheProvider->expects(self::at(1))->method('doSave')
            ->with('DoctrineNamespaceCacheKey[namespaceTest]', 2)->willReturn(true);

        self::assertEquals(true, $this->adapter->deleteAll('namespaceTest'));
    }

    /**
     * @covers TorrentPier\Cache\Adapter::flush
     */
    public function testFlush()
    {
        $this->cacheProvider->expects(self::at(0))->method('doFlush')->willReturn(true);

        self::assertEquals(true, $this->adapter->flush());
    }

    /**
     * @covers TorrentPier\Cache\Adapter::stats
     */
    public function testStats()
    {
        $this->cacheProvider->expects(self::at(0))->method('doGetStats')->willReturn(null);

        self::assertEquals(['type' => 'Void Cache for tests'], $this->adapter->stats());
    }
}
