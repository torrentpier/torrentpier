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

        $callback = function ($cache, $key) {
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
            ->with('namespaceTest[keyTest][1]', [1, 2, 3, 4], 10)->willReturn(true);

        self::assertEquals(true, $this->adapter->set('namespaceTest::keyTest', [1, 2, 3, 4], 10));
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
            ->with('namespaceTest[keyTest][1]', [1, 2, 3, 4], 10)->willReturn(true);

        self::assertEquals(true, $this->adapter->sets(['keyTest' => [1, 2, 3, 4]], 'namespaceTest', 10));
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
