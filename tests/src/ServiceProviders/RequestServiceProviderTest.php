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
use Symfony\Component\HttpFoundation\Request;
use TorrentPier\Di;

/**
 * Class RequestServiceProviderTest
 * @package TorrentPier\ServiceProviders
 */
class RequestServiceProviderTest extends TestCase
{
    /**
     * @covers \TorrentPier\ServiceProviders\RequestServiceProvider::register
     */
    public function testRegisterService(): void
    {
        $di = new Di();

        $di->register(new RequestServiceProvider);

        static::assertInstanceOf(Request::class, $di->request);
    }
}
