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
use Symfony\Component\HttpFoundation\Response;
use TorrentPier\Di;

/**
 * Class ResponseServiceProviderTest
 * @package TorrentPier\ServiceProviders
 */
class ResponseServiceProviderTest extends TestCase
{
    /**
     * @covers \TorrentPier\ServiceProviders\ResponseServiceProvider::register
     */
    public function testRegisterService(): void
    {
        $di = new Di();

        $di->register(new ResponseServiceProvider, [
            'request' => new Request,
        ]);

        static::assertInstanceOf(Response::class, $di->response);
    }
}
