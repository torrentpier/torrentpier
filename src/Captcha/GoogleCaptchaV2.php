<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Captcha;

/**
 * Class GoogleCaptchaV2
 * @package TorrentPier\Captcha
 */
class GoogleCaptchaV2 implements CaptchaInterface
{
    public function get(array $settings): string
    {
        return "
        <script type='text/javascript'>
            let onloadCallback = function() {
                grecaptcha.render('tp-captcha', {
                    'sitekey': '{$settings['public_key']}',
                    'theme': '" . ($settings['theme'] ?? 'light') . "'
                });
            };
		</script>
        <div id='tp-captcha'></div>
        <script src='https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit' async defer></script>";
    }

    public function check(array $settings): bool
    {
        $reCaptcha = new \ReCaptcha\ReCaptcha($settings['secret_key']);
        $resp = $reCaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
        if ($resp->isSuccess()) {
            return true;
        }

        return false;
    }
}
