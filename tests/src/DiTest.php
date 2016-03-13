<?php

namespace TorrentPier;

/**
 * Class DiTest
 * @package TorrentPier
 */
class DiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage The container has not been initialized
     */
    public function testGetInstanceIsNotInitialized()
    {
        Di::getInstance();
    }

    /**
     * @covers                   \TorrentPier\Di::getInstance
     * @depends                  testGetInstanceIsNotInitialized
     */
    public function testGetInstance()
    {
        $di = new Di();
        static::assertEquals($di, Di::getInstance());
    }

    /**
     * @depends                  testGetInstanceIsNotInitialized
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Service 'test' is not registered in the container
     */
    public function testGetByPropertyIsNotExists()
    {
        $di = new Di();
        $di->test;
    }

    /**
     * @see                      \TorrentPier\Di::__get
     * @depends                  testGetInstanceIsNotInitialized
     */
    public function testGetByProperty()
    {
        $di = new Di([
            'test' => function () {
                return 'test string';
            }
        ]);

        static::assertEquals('test string', $di->test);
    }
}
