<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Captcha;

use ReCaptcha\ReCaptcha;

/**
 * Class GoogleCaptchaV3
 * @package TorrentPier\Captcha
 */
class GoogleCaptchaV3 implements CaptchaInterface
{
    /**
     * Captcha service settings
     *
     * @var array
     */
    private array $settings;

    /**
     * Constructor
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Returns captcha widget
     *
     * @return string
     */
    public function get(): string
    {
        global $lang;

        return "{$lang['CAPTCHA_OCCURS_BACKGROUND']}
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
     *
     * @return bool
     */
    public function check(): bool
    {
        $reCaptcha = new ReCaptcha($this->settings['secret_key']);
        $resp = $reCaptcha
            ->setScoreThreshold(0.5)
            ->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

        return $resp->isSuccess();
    }
}
