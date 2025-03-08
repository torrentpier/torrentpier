<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Captcha;

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;

/**
 * Class TextCaptcha
 * @package TorrentPier\Captcha
 */
class TextCaptcha implements CaptchaInterface
{
    /**
     * Captcha service settings
     *
     * @var array
     */
    private array $settings;

    private $captcha;

    /**
     * Constructor
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        session_start();

        $this->settings = $settings;
        $this->captcha = new CaptchaBuilder;
    }

    /**
     * Returns captcha widget
     *
     * @return string
     */
    public function get(): string
    {
        $this->captcha->build();
        return "
            <img src=" . $this->captcha->inline() . " /><br />
            <input type='text' name='captcha_phrase' />
        ";
    }

    /**
     * Checking captcha answer
     *
     * @return bool
     */
    public function check(): bool
    {
        return (isset($_SESSION['phrase']) && PhraseBuilder::comparePhrases($_SESSION['phrase'], $_POST['captcha_phrase']));
    }
}
