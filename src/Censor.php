<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

/**
 * Word Censoring System
 *
 * Singleton class that provides word censoring functionality
 * with automatic loading of censored words from the datastore.
 */
class Censor
{
    private static ?Censor $instance = null;

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
    private function __construct()
    {
        $this->loadCensoredWords();
    }

    /**
     * Get the singleton instance of Censor
     */
    public static function getInstance(): Censor
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the censor system (for compatibility)
     */
    public static function init(): Censor
    {
        return self::getInstance();
    }

    /**
     * Load censored words from datastore
     */
    private function loadCensoredWords(): void
    {
        global $datastore;

        if (!$this->isEnabled()) {
            return;
        }

        // Get censored words
        $censoredWords = $datastore->get('censor');

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
        if (!$this->isEnabled()) {
            return $word;
        }

        return preg_replace($this->words, $this->replacements, $word);
    }

    /**
     * Reload censored words from datastore
     * Useful when words are updated in admin panel
     */
    public function reload(): void
    {
        $this->words = [];
        $this->replacements = [];
        $this->loadCensoredWords();
    }

    /**
     * Check if censoring is enabled
     */
    public function isEnabled(): bool
    {
        return tp_config()->get('use_word_censor', false);
    }

    /**
     * Add a censored word (runtime only)
     *
     * @param string $word
     * @param string $replacement
     */
    public function addWord(string $word, string $replacement): void
    {
        $this->words[] = '#(?<![\p{Nd}\p{L}_])(' . str_replace('\*', '[\p{Nd}\p{L}_]*?', preg_quote($word, '#')) . ')(?![\p{Nd}\p{L}_])#iu';
        $this->replacements[] = $replacement;
    }

    /**
     * Get all censored words count
     */
    public function getWordsCount(): int
    {
        return count($this->words);
    }

    /**
     * Prevent cloning of the singleton instance
     */
    private function __clone() {}

    /**
     * Prevent unserialization of the singleton instance
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}
