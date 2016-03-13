<?php

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

    public function setUp()
    {
        $data = [
            'key1' => 'value1',
            'key2' => [
                'key3' => 'value2',
                'key4' => '{self.key1}',
                'key5' => '{self.key2.key4}',
            ]
        ];

        $this->config = new Config($data);
    }

    /**
     * @covers \TorrentPier\Config::get
     */
    public function testGet()
    {
        $this->assertEquals($this->config->get('key1'), 'value1');
        $this->assertEquals($this->config->get('key2.key3'), 'value2');
        $this->assertEquals($this->config->get('key2.key4'), 'value1');
        $this->assertEquals($this->config->get('key2.key5'), 'value1');
        $this->assertEquals($this->config->get('key2')->get('key3'), 'value2');

        $this->assertEquals($this->config['key1'], 'value1');
        $this->assertEquals($this->config['key2.key3'], 'value2');
        $this->assertEquals($this->config['key2.key4'], 'value1');
        $this->assertEquals($this->config['key2.key5'], 'value1');
        $this->assertEquals($this->config['key2']['key3'], 'value2');

        $this->assertEquals($this->config['key2.key6'], null);
    }

    /**
     * @covers \TorrentPier\Config::toArray
     */
    public function testToArray()
    {
        $this->assertEquals($this->config->toArray(), [
            'key1' => 'value1',
            'key2' => [
                'key3' => 'value2',
                'key4' => 'value1',
                'key5' => 'value1',
            ]
        ]);
    }
}
