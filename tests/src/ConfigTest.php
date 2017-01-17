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
 * Class ConfigTest
 * @package TorrentPier
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    public $config;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $data = [
            'key1' => 'value1',
            'key2' => [
                'key3' => 'value2',
            ]
        ];

        $this->config = new Config($data);
    }

    /**
     * Get value from array by key.
     *
     * @see \TorrentPier\Config::get
     */
    public function testGet()
    {
        static::assertEquals($this->config->get('key1'), 'value1');
        static::assertEquals($this->config->get('key2.key3'), 'value2');

        static::assertEquals($this->config['key1'], 'value1');
        static::assertEquals($this->config['key2.key3'], 'value2');
        static::assertEquals($this->config['key2']['key3'], 'value2');
    }
}
