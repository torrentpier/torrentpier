<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

/**
 * Class Censor
 * @package TorrentPier
 */
class Censor
{
    /**
     * Word replacements
     *
     * @var array
     */
    public array $replacements = [];

    /**
     * All censored words (RegEx)
     *
     * @var array
     */
    public array $words = [];

    /**
     * Initialize word censor
     */
    public function __construct()
    {
        global $bb_cfg;

        if (!$bb_cfg['use_word_censor']) {
            return;
        }

        if (!$words = CACHE('bb_cache')->get('censored')) {
            $words = DB()->fetch_rowset("SELECT word, replacement FROM " . BB_WORDS);
            CACHE('bb_cache')->set('censored', $words, 7200);
        }

        foreach ($words as $word) {
            $this->words[] = '#(?<![\p{Nd}\p{L}_])(' . str_replace('\*', '[\p{Nd}\p{L}_]*?', preg_quote($word['word'], '#')) . ')(?![\p{Nd}\p{L}_])#iu';
            $this->replacements[] = $word['replacement'];
        }
    }

    /**
     * Word censor
     *
     * @param string $word
     * @return string
     */
    public function censorString(string $word): string
    {
        return preg_replace($this->words, $this->replacements, $word);
    }
}
