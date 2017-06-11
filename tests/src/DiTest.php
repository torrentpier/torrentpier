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
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Identifier "test" is not defined.
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
