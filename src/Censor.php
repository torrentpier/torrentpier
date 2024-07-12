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

        if (!$censoredWords = CACHE('bb_cache')->get('censored')) {
            $censoredWords = DB()->fetch_rowset("SELECT word, replacement FROM " . BB_WORDS);
            $censoredWords = empty($censoredWords) ? ['no_words' => true] : $censoredWords;
            CACHE('bb_cache')->set('censored', $censoredWords, 7200);
        }

        if (isset($censoredWords['no_words'])) {
            return;
        }

        foreach ($censoredWords as $word) {
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
