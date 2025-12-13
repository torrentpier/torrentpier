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
 * Class GoogleCaptchaV3
 * @package TorrentPier\Captcha
 */
class GoogleCaptchaV3 implements CaptchaInterface
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
        return __('CAPTCHA_OCCURS_BACKGROUND') . "
        <script src='https://www.google.com/recaptcha/api.js?render={$this->settings['public_key']}&lang={$this->settings['language']}'></script>
        <script>
        grecaptcha.ready(function() {
            grecaptcha.execute('{$this->settings['public_key']}', { action:'validate_captcha' }).then(function(token) {
                document.getElementById('g-recaptcha-response').value = token;
            });
        });
	    </script>
	    <input type='hidden' id='g-recaptcha-response' name='g-recaptcha-response'>";
    }

    /**
     * Checking captcha answer
     */
    #[Override]
    public function check(): bool
    {
        $reCaptcha = new ReCaptcha($this->settings['secret_key']);
        $resp = $reCaptcha
            ->setScoreThreshold(0.5)
            ->verify(request()->post->get('g-recaptcha-response', ''), request()->getClientIp());

        return $resp->isSuccess();
    }
}
