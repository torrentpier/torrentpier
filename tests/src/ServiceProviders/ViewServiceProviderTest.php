<?php

namespace TorrentPier\ServiceProviders;

use TorrentPier\Di;
use TorrentPier\View;

class ViewServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \TorrentPier\ServiceProviders\ViewServiceProvider::register
     */
    public function testRegisterService()
    {
        $di = new Di();
        $di->register(new ViewServiceProvider, [
            'twig' => new \Twig_Environment()
        ]);

        static::assertInstanceOf(View::class, $di->view);
    }
}
