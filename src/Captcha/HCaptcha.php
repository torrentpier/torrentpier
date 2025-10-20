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
    private string $verifyEndpoint = 'https://hcaptcha.com/siteverify';

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
        <div class='h-captcha' data-sitekey='{$this->settings['public_key']}' data-theme='" . ($this->settings['theme'] ?? 'light') . "'></div>
        <script src='https://www.hCaptcha.com/1/api.js?hl={$this->settings['language']}' async defer></script>";
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
                    'response' => $_POST['h-captcha-response'] ?? null,
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                return false;
            }

            $responseData = json_decode((string) $response->getBody(), false);
            return $responseData->success ?? false;
        } catch (\Throwable $e) {
            // Log error but don't expose to user
            if (function_exists('bb_log')) {
                bb_log("HCaptcha verification failed: {$e->getMessage()}" . LOG_LF);
            }
            return false;
        }
    }
}
