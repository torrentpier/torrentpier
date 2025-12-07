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
     * CaptchaBuilder object
     *
     * @var CaptchaBuilder
     */
    private CaptchaBuilder $captcha;

    /**
     * Constructor
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->captcha = new CaptchaBuilder;
        $this->captcha->setScatterEffect(false);
    }

    /**
     * Returns captcha widget
     *
     * @return string
     */
    #[\Override]
    public function get(): string
    {
        $_SESSION['phrase'] = $this->captcha->getPhrase();
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
    #[\Override]
    public function check(): bool
    {
        if (!request()->post->has('captcha_phrase') || !isset($_SESSION['phrase'])) {
            return false;
        }

        return PhraseBuilder::comparePhrases($_SESSION['phrase'], request()->post->get('captcha_phrase'));
    }
}
