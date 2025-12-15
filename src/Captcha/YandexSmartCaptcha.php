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
use Throwable;

/**
 * Class YandexSmartCaptcha
 * @package TorrentPier\Captcha
 */
class YandexSmartCaptcha implements CaptchaInterface
{
    /**
     * Captcha service settings
     */
    private array $settings;

    /**
     * Service verification endpoint
     */
    private string $verifyEndpoint = 'https://smartcaptcha.yandexcloud.net/validate';

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
        return "
        <script src='https://smartcaptcha.yandexcloud.net/captcha.js' defer></script>
        <div id='captcha-container' style='width: 402px;' class='smart-captcha' data-sitekey='{$this->settings['public_key']}' data-hl='{$this->settings['language']}'></div>";
    }

    /**
     * Checking captcha answer
     */
    #[Override]
    public function check(): bool
    {
        // Require token present
        $token = request()->post->get('smart-token');
        if (!$token) {
            return false;
        }

        try {
            $response = httpClient()->post($this->verifyEndpoint, [
                'form_params' => [
                    'secret' => $this->settings['secret_key'],
                    'token' => $token,
                    'ip' => request()->getClientIp() ?? '',
                ],
                'timeout' => 3,
                'connect_timeout' => 2,
            ]);

            if ($response->getStatusCode() !== 200) {
                return false;
            }

            // Safely decode JSON with error checking
            $responseBody = (string)$response->getBody();
            if (!json_validate($responseBody)) {
                bb_log('Yandex SmartCaptcha verification failed: Invalid JSON response' . LOG_LF);

                return false;
            }
            $responseData = json_decode($responseBody, false);

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
