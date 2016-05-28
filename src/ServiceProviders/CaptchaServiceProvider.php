<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use ReCaptcha\ReCaptcha;

/**
 * Class CaptchaServiceProvider
 * @package TorrentPier\ServiceProviders
 */
class CaptchaServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['captcha'] = function (Container $container) {
            $captcha = new ReCaptcha($container['config.services.captcha.secret_key']);
            return $captcha;
        };
    }
}
