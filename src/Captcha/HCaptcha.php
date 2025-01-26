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
        return false;
    }
}
