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
 * Class CloudflareTurnstileCaptcha
 * @package TorrentPier\Captcha
 */
class CloudflareTurnstileCaptcha implements CaptchaInterface
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
    private string $verifyEndpoint = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

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
        <script src='https://challenges.cloudflare.com/turnstile/v0/api.js' async defer></script>
        <div class='cf-turnstile' data-sitekey='{$this->settings['public_key']}' data-language='{$this->settings['language']}' data-theme='" . ($this->settings['theme'] ?? 'light') . "'></div>
        ";
    }

    /**
     * Checking captcha answer
     *
     * @return bool
     */
    public function check(): bool
    {
        $turnstileResponse = $_POST['cf-turnstile-response'] ?? '';

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

            $responseData = json_decode((string)$response->getBody(), false);
            return $responseData->success ?? false;
        } catch (Throwable $e) {
            bb_log("Cloudflare Turnstile verification failed: {$e->getMessage()}" . LOG_LF);
            return false;
        }
    }
}
