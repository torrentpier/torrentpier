<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use ReCaptcha\ReCaptcha;

class CaptchaServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['captcha'] = function (Container $container) {
            $captcha = new ReCaptcha($container['config.captcha.secret_key']);

            return $captcha;
        };
    }
}
