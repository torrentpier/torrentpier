<?php

namespace TorrentPier\ServiceProviders;

use Symfony\Component\HttpFoundation\Request;
use TorrentPier\Di;

class RequestServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \TorrentPier\ServiceProviders\RequestServiceProvider::register
     */
    public function testRegisterService()
    {
        $di = new Di();
        $di->register(new RequestServiceProvider);

        static::assertInstanceOf(Request::class, $di->request);
    }
}
