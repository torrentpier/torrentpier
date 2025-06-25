<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine.
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 *
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 *
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Captcha;

use ReCaptcha\ReCaptcha;

/**
 * Class GoogleCaptchaV2.
 */
class GoogleCaptchaV2 implements CaptchaInterface
{
    /**
     * Captcha service settings.
     *
     * @var array
     */
    private array $settings;

    /**
     * Constructor.
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Returns captcha widget.
     *
     * @return string
     */
    public function get(): string
    {
        return "
        <script type='text/javascript'>
            var onloadCallback = function() {
                grecaptcha.render('tp-captcha', {
                    'sitekey': '{$this->settings['public_key']}',
                    'theme': '".($this->settings['theme'] ?? 'light')."'
                });
            };
		</script>
        <div id='tp-captcha'></div>
        <script src='https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl={$this->settings['language']}' async defer></script>";
    }

    /**
     * Checking captcha answer.
     *
     * @return bool
     */
    public function check(): bool
    {
        $reCaptcha = new ReCaptcha($this->settings['secret_key']);
        $resp = $reCaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

        return $resp->isSuccess();
    }
}
