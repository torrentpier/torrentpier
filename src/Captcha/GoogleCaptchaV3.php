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
 * Class GoogleCaptchaV3
 * @package TorrentPier\Captcha
 */
class GoogleCaptchaV3 implements CaptchaInterface
{
    private string $verifyEndpoint = 'https://www.google.com/recaptcha/api/siteverify';

    public function get(array $settings): string
    {
        return "
        <script src='https://www.google.com/recaptcha/api.js?render={$settings['site_key']}'></script>
        <script>
        grecaptcha.ready(function() {
            grecaptcha.execute('{$settings['site_key']}', { action:'validate_captcha' }).then(function(token) {
                document.getElementById('g-recaptcha-response').value = token;
            });
        });
	    </script>
	    <input type='hidden' id='g-recaptcha-response' name='g-recaptcha-response'>";
    }

    public function check(array $settings): bool
    {
        $captcha = $_POST['g-recaptcha-response'] ?? false;
        if (!$captcha) {
            return false;
        }

        $postdata = http_build_query(
            array(
                'secret' => $settings['secret_key'],
                'response' => $captcha,
                'remoteip' => $_SERVER["REMOTE_ADDR"]
            )
        );
        $opts = array(
            'http' =>
                array(
                    'method' => 'POST',
                    'header' => 'Content-Type: application/x-www-form-urlencoded',
                    'content' => $postdata
                )
        );
        $context = stream_context_create($opts);

        $googleApiResponse = file_get_contents($this->verifyEndpoint, false, $context);
        if ($googleApiResponse === false) {
            return false;
        }

        $googleApiResponseObject = json_decode($googleApiResponse);
        return $googleApiResponseObject->success;
    }
}
