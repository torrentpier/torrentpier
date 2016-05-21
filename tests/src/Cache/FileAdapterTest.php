<?php

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
            'umask'     => 0644,
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
