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
 * Class HCaptcha
 * @package TorrentPier\Captcha
 */
class HCaptcha implements CaptchaInterface
{
    private string $verifyEndpoint = 'https://hcaptcha.com/siteverify';

    public function get(array $settings): string
    {
        return "";
    }

    public function check(array $settings): bool
    {
        $data = [
            'secret' => $settings['secret_key'],
            'response' => $_POST['h-captcha-response'] ?? null,
        ];

        $verify = curl_init();
        curl_setopt($verify, CURLOPT_URL, $this->verifyEndpoint);
        curl_setopt($verify, CURLOPT_POST, true);
        curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($verify);

        $responseData = json_decode($response);
        return $responseData->success;
    }
}
