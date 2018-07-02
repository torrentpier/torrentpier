<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2018 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\ServiceProviders;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use TorrentPier\Di;

/**
 * Class LogServiceProviderTest
 * @package TorrentPier\ServiceProviders
 */
class LogServiceProviderTest extends TestCase
{
    /**
     * @covers \TorrentPier\ServiceProviders\LogServiceProvider::register
     */
    public function testRegisterService(): void
    {
        $di = new Di();

        $di->register(new LogServiceProvider, [
            'config.log.handlers' => [],
        ]);

        static::assertInstanceOf(Logger::class, $di->log);
    }
}
