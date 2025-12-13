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
 * Class CloudflareTurnstileCaptcha
 * @package TorrentPier\Captcha
 */
class CloudflareTurnstileCaptcha implements CaptchaInterface
{
    /**
     * Captcha service settings
     */
    private array $settings;

    /**
     * Service verification endpoint
     */
    private string $verifyEndpoint = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

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
        <script src='https://challenges.cloudflare.com/turnstile/v0/api.js' async defer></script>
        <div class='cf-turnstile' data-sitekey='{$this->settings['public_key']}' data-language='{$this->settings['language']}' data-theme='" . ($this->settings['theme'] ?? 'light') . "'></div>
        ";
    }

    /**
     * Checking captcha answer
     */
    #[Override]
    public function check(): bool
    {
        // Require token present - fail closed
        $turnstileResponse = request()->post->get('cf-turnstile-response', '');
        if (empty($turnstileResponse)) {
            return false;
        }

        try {
            $response = httpClient()->post($this->verifyEndpoint, [
                'form_params' => [
                    'secret' => $this->settings['secret_key'],
                    'response' => $turnstileResponse,
                    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
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
                bb_log('Cloudflare Turnstile verification failed: Invalid JSON response' . LOG_LF);

                return false;
            }
            $responseData = json_decode($responseBody, false);

            // Validate that the response contains the expected 'success' field
            if (!isset($responseData->success)) {
                bb_log("Cloudflare Turnstile verification failed: Missing 'success' field in response" . LOG_LF);

                return false;
            }

            return $responseData->success === true;
        } catch (Throwable $e) {
            bb_log("Cloudflare Turnstile verification failed: {$e->getMessage()}" . LOG_LF);

            return false;
        }
    }
}
