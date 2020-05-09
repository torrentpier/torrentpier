<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2018 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use PHPUnit\Framework\TestCase;

/**
 * Class DiTest
 * @package TorrentPier
 */
class DiTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The container has not been initialized
     */
    public function testGetInstanceIsNotInitialized(): void
    {
        Di::getInstance();
    }

    /**
     * @covers \TorrentPier\Di::getInstance
     * @depends testGetInstanceIsNotInitialized
     */
    public function testGetInstance(): void
    {
        $di = new Di();
        static::assertEquals($di, Di::getInstance());
    }

    /**
     * @depends testGetInstanceIsNotInitialized
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Identifier "test" is not defined.
     */
    public function testGetByPropertyIsNotExists(): void
    {
        $di = new Di();
        $di->test;
    }

    /**
     * @see \TorrentPier\Di::__get
     * @depends testGetInstanceIsNotInitialized
     */
    public function testGetByProperty(): void
    {
        $di = new Di([
            'test' => function () {
                return 'test string';
            }
        ]);

        static::assertEquals('test string', $di->test);
    }
}
