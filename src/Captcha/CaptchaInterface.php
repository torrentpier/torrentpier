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
 * Interface CaptchaInterface
 * @package TorrentPier\Captcha
 */
interface CaptchaInterface
{
    /**
     * Constructor
     *
     * @param array $settings
     */
    public function __construct(array $settings);

    /**
     * Returns captcha widget
     *
     * @return string
     */
    public function get(): string;

    /**
     * Checking captcha answer
     *
     * @return bool
     */
    public function check(): bool;
}
