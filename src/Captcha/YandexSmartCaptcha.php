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
        try {
            $response = httpClient()->post($this->verifyEndpoint, [
                'form_params' => [
                    'secret' => $this->settings['secret_key'],
                    'token' => $_POST['smart-token'] ?? null,
                    'ip' => $_SERVER['REMOTE_ADDR'],
                ],
                'timeout' => 1,
            ]);

            $httpCode = $response->getStatusCode();

            // Yandex SmartCaptcha returns true on non-200 responses
            // This is original behavior preserved from legacy code
            if ($httpCode !== 200) {
                return true;
            }

            $responseData = json_decode((string) $response->getBody(), false);
            return ($responseData->status ?? '') === 'ok';
        } catch (\Throwable $e) {
            // Log error but don't expose to user
            if (function_exists('bb_log')) {
                bb_log("Yandex SmartCaptcha verification failed: {$e->getMessage()}" . LOG_LF);
            }
            // Return true on timeout/error (preserving original behavior)
            return true;
        }
    }
}
