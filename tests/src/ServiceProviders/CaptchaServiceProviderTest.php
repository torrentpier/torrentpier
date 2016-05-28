<?php

namespace TorrentPier\ServiceProviders;

use ReCaptcha\ReCaptcha;
use TorrentPier\Di;

class CaptchaServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \TorrentPier\ServiceProviders\CaptchaServiceProvider::register
     */
    public function testRegisterService()
    {
        $di = new Di();
        $di->register(new CaptchaServiceProvider, [
            'config.services.captcha.secret_key' => 'secret key'
        ]);

        static::assertInstanceOf(ReCaptcha::class, $di->captcha);
    }
}
