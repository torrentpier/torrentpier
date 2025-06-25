<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine.
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 *
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 *
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Captcha;

/**
 * Class HCaptcha.
 */
class HCaptcha implements CaptchaInterface
{
    /**
     * Captcha service settings.
     *
     * @var array
     */
    private array $settings;

    /**
     * Service verification endpoint.
     *
     * @var string
     */
    private string $verifyEndpoint = 'https://hcaptcha.com/siteverify';

    /**
     * Constructor.
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Returns captcha widget.
     *
     * @return string
     */
    public function get(): string
    {
        return "
        <div class='h-captcha' data-sitekey='{$this->settings['public_key']}' data-theme='".($this->settings['theme'] ?? 'light')."'></div>
        <script src='https://www.hCaptcha.com/1/api.js?hl={$this->settings['language']}' async defer></script>";
    }

    /**
     * Checking captcha answer.
     *
     * @return bool
     */
    public function check(): bool
    {
        $data = [
            'secret'   => $this->settings['secret_key'],
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
