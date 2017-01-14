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

use Doctrine\Common\Cache\FilesystemCache;

/**
 * Class FileAdapterTest
 * @package TorrentPier\Cache
 */
class FileAdapterTest extends \PHPUnit_Framework_TestCase
{
    /** @var Adapter */
    private $adapter;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->adapter = new FileAdapter();
        $this->adapter->setOptions([
            'directory' => __DIR__ . '/../../../internal_data/cache',
            'extension' => '.torrentpier.php',
            'umask' => 0644,
        ]);
    }

    /**
     * Check provider.
     */
    public function testAdapterCache()
    {
        self::assertInstanceOf(FilesystemCache::class, $this->adapter->getProvider());
    }

    /**
     * Test type cache adapter.
     */
    public function testStatsType()
    {
        self::assertEquals('Filesystem Cache', $this->adapter->stats()['type']);
    }

    /**
     * Check handler error for incorrect error.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Method setIncorrectKey is undefined.
     */
    public function testIncorrectSetting()
    {
        $this->adapter->setOptions(['incorrectKey' => 'value']);
    }
}
