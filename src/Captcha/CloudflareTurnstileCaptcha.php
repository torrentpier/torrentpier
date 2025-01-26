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
 * Class CloudflareTurnstileCaptcha
 * @package TorrentPier\Captcha
 */
class CloudflareTurnstileCaptcha implements CaptchaInterface
{
    private string $verifyEndpoint = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function get(array $settings): string
    {
        return "
        <script src='https://challenges.cloudflare.com/turnstile/v0/api.js' async defer></script>
        <div class='cf-turnstile' data-sitekey='{$settings['public_key']}' data-theme='" . ($settings['theme'] ?? 'light') . "'></div>
        ";
    }

    public function check(array $settings): bool
    {
        $turnstileResponse = $_POST['cf-turnstile-response'] ?? '';
        $postFields = "secret={$settings['secret_key']}&response=$turnstileResponse";

        $ch = curl_init($this->verifyEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        $response = curl_exec($ch);
        curl_close($ch);

        $responseData = json_decode($response);
        return $responseData->success;
    }
}
