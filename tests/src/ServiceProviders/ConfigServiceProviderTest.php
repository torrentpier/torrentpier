<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2018 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\ServiceProviders;

use PHPUnit\Framework\TestCase;
use TorrentPier\Configure\Config;
use TorrentPier\Di;

/**
 * Class ConfigServiceProviderTest
 * @package TorrentPier\ServiceProviders
 */
class ConfigServiceProviderTest extends TestCase
{
    /**
     * @covers \TorrentPier\ServiceProviders\ConfigServiceProvider::register
     */
    public function testRegisterService(): void
    {
        $di = new Di();

        $di->register(new ConfigServiceProvider, [
            'file.system.main' => __DIR__ . '/../../../src/config.php',
            'file.local.main' => __DIR__ . '/../../../src/config.local.php',
        ]);

        static::assertInstanceOf(Config::class, $di->config);
    }
}
