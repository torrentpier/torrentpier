<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Captcha;

use Throwable;

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
        // Require token present
        $token = $_POST['smart-token'] ?? null;
        if (!$token) {
            return false;
        }

        try {
            $response = httpClient()->post($this->verifyEndpoint, [
                'form_params' => [
                    'secret' => $this->settings['secret_key'],
                    'token' => $token,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                ],
                'timeout' => 3,
                'connect_timeout' => 2,
            ]);

            if ($response->getStatusCode() !== 200) {
                return false;
            }

            // Safely decode JSON with error checking
            $responseData = json_decode((string)$response->getBody(), false);
            if ($responseData === null || json_last_error() !== JSON_ERROR_NONE) {
                bb_log("Yandex SmartCaptcha verification failed: Invalid JSON response" . LOG_LF);
                return false;
            }

            // Validate that the response contains the expected 'status' field
            if (!isset($responseData->status)) {
                bb_log("Yandex SmartCaptcha verification failed: Missing 'status' field in response" . LOG_LF);
                return false;
            }

            return $responseData->status === 'ok';
        } catch (Throwable $e) {
            bb_log("Yandex SmartCaptcha verification failed: {$e->getMessage()}" . LOG_LF);
            return false;
        }
    }
}
