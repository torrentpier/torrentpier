<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Captcha;

use Override;
use ReCaptcha\ReCaptcha;

/**
 * Class GoogleCaptchaV2
 * @package TorrentPier\Captcha
 */
class GoogleCaptchaV2 implements CaptchaInterface
{
    /**
     * Captcha service settings
     */
    private array $settings;

    /**
     * Constructor
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Returns captcha widget
     */
    #[Override]
    public function get(): string
    {
        return "
        <script type='text/javascript'>
            var onloadCallback = function() {
                grecaptcha.render('tp-captcha', {
                    'sitekey': '{$this->settings['public_key']}',
                    'theme': '" . ($this->settings['theme'] ?? 'light') . "'
                });
            };
		</script>
        <div id='tp-captcha'></div>
        <script src='https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl={$this->settings['language']}' async defer></script>";
    }

    /**
     * Checking captcha answer
     */
    #[Override]
    public function check(): bool
    {
        $reCaptcha = new ReCaptcha($this->settings['secret_key']);
        $resp = $reCaptcha->verify(request()->post->get('g-recaptcha-response', ''), request()->getClientIp());

        return $resp->isSuccess();
    }
}
