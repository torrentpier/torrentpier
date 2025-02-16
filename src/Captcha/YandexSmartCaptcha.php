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
 * Class YandexSmartCaptcha
 * @package TorrentPier\Captcha
 */
class YandexSmartCaptcha implements CaptchaInterface
{
    /**
     * Captcha service settings
     *
     * @var array
     */
    private array $settings;

    /**
     * Service verification endpoint
     *
     * @var string
     */
    private string $verifyEndpoint = 'https://smartcaptcha.yandexcloud.net/validate';

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
        return "
        <script src='https://smartcaptcha.yandexcloud.net/captcha.js' defer></script>
        <div id='captcha-container' style='width: 402px;' class='smart-captcha' data-sitekey='{$this->settings['public_key']}' data-hl='{$this->settings['language']}'></div>";
    }

    /**
     * Checking captcha answer
     *
     * @return bool
     */
    public function check(): bool
    {
        $ch = curl_init($this->verifyEndpoint);
        $args = [
            'secret' => $this->settings['secret_key'],
            'token' => $_POST['smart-token'] ?? null,
            'ip' => $_SERVER['REMOTE_ADDR'],
        ];

        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($args));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $serverOutput = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return true;
        }

        $resp = json_decode($serverOutput);
        return ($resp->status === 'ok');
    }
}
