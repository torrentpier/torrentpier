<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use TorrentPier\Cache\DatastoreManager;

/**
 * Word Censoring System
 *
 * Provides word censoring functionality with automatic loading
 * of censored words from the datastore.
 */
class Censor
{
    /**
     * Word replacements
     */
    public array $replacements = [];

    /**
     * All censored words (RegEx)
     */
    public array $words = [];

    public function __construct(
        private readonly Config $config,
        private readonly DatastoreManager $datastore,
    ) {
        $this->loadCensoredWords();
    }

    /**
     * Censor a string by replacing banned words
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
        return $this->config->get('forum.use_word_censor', false);
    }

    /**
     * Add a censored word at runtime
     */
    public function addWord(string $word, string $replacement): void
    {
        $this->words[] = '#(?<![\p{Nd}\p{L}_])(' . str_replace('\*', '[\p{Nd}\p{L}_]*?', preg_quote($word, '#')) . ')(?![\p{Nd}\p{L}_])#iu';
        $this->replacements[] = $replacement;
    }

    /**
     * Get count of censored words
     */
    public function getWordsCount(): int
    {
        return \count($this->words);
    }

    /**
     * Load censored words from datastore
     */
    private function loadCensoredWords(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $censoredWords = $this->datastore->get('censor');

        if (!\is_array($censoredWords)) {
            return;
        }

        foreach ($censoredWords as $word) {
            $this->words[] = '#(?<![\p{Nd}\p{L}_])(' . str_replace('\*', '[\p{Nd}\p{L}_]*?', preg_quote($word['word'], '#')) . ')(?![\p{Nd}\p{L}_])#iu';
            $this->replacements[] = $word['replacement'];
        }
    }
}
