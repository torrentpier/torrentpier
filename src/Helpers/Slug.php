<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Helpers;

use Symfony\Component\String\Slugger\AsciiSlugger;
use TorrentPier\Data\Romanization;
use Transliterator;

/**
 * URL slug generator with multi-script transliteration support
 *
 * Converts arbitrary strings (including Cyrillic, Japanese, Arabic, etc.)
 * to URL-safe slugs. Uses PHP intl extension when available, falls back
 * to comprehensive character mapping for systems without intl.
 *
 * Supported scripts: Cyrillic (Russian, Ukrainian), Georgian, Sanskrit,
 * Hebrew, Arabic, Japanese (Hiragana, Katakana), Greek, Thai, Korean.
 */
class Slug
{
    private static ?Transliterator $transliterator = null;
    private static ?AsciiSlugger $slugger = null;

    /**
     * Generate a URL-safe slug from a string
     *
     * @param string $title The string to convert
     * @param int $maxLength Maximum length of the resulting slug (0 = unlimited)
     * @return string URL-safe slug (lowercase, hyphens instead of spaces)
     */
    public static function generate(string $title, int $maxLength = 50): string
    {
        // Decode HTML entities (e.g., &amp; → &, &quot; → ")
        // This handles cases where HTML-escaped strings are passed
        $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $title = trim($title);

        if ($title === '') {
            return '';
        }

        // Fast path: if already ASCII, just use slugger directly
        if (self::isAscii($title)) {
            return self::finalizeSlug($title, $maxLength);
        }

        // Transliterate non-ASCII characters
        $title = self::transliterate($title);

        return self::finalizeSlug($title, $maxLength);
    }

    /**
     * Reset cached instances (useful for testing)
     */
    public static function reset(): void
    {
        self::$transliterator = null;
        self::$slugger = null;
        Romanization::reset();
    }

    /**
     * Check if a string contains only ASCII characters
     */
    private static function isAscii(string $string): bool
    {
        return mb_check_encoding($string, 'ASCII');
    }

    /**
     * Transliterate non-ASCII text to Latin characters
     *
     * Uses ICU transliterator when available (best quality),
     * falls back to a hardcoded romanization map otherwise.
     */
    private static function transliterate(string $string): string
    {
        // If intl extension is available, use ICU (best quality)
        if (\function_exists('transliterator_transliterate')) {
            $transliterator = self::getTransliterator();
            if ($transliterator !== null) {
                $result = $transliterator->transliterate($string);
                if ($result !== false) {
                    return $result;
                }
            }
        }

        // Fallback: use our comprehensive romanization map
        // This handles Cyrillic, Japanese, Arabic, etc. without intl
        return strtr($string, Romanization::getMap());
    }

    /**
     * Get or create the ICU transliterator
     *
     * Uses a pipeline: Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC
     */
    private static function getTransliterator(): ?Transliterator
    {
        if (self::$transliterator === null) {
            // Transliteration pipeline:
            // 1. Any-Latin: Convert any script to Latin
            // 2. Latin-ASCII: Convert Latin with diacritics to ASCII
            // 3. NFD: Normalize (decompose)
            // 4. [:Nonspacing Mark:] Remove: Remove combining marks
            // 5. NFC: Normalize (compose)
            self::$transliterator = Transliterator::create(
                'Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC',
            );
        }

        return self::$transliterator;
    }

    /**
     * Finalize slug: convert to lowercase, replace special chars with hyphens
     */
    private static function finalizeSlug(string $title, int $maxLength): string
    {
        $slugger = self::getSlugger();

        // Generate slug (lowercase, hyphens)
        $slug = $slugger->slug($title)->lower()->toString();

        // Truncate if needed (at word boundary)
        if ($maxLength > 0 && mb_strlen($slug) > $maxLength) {
            $slug = self::truncateAtWordBoundary($slug, $maxLength);
        }

        return $slug;
    }

    /**
     * Get or create the ASCII slugger
     */
    private static function getSlugger(): AsciiSlugger
    {
        if (self::$slugger === null) {
            self::$slugger = new AsciiSlugger;
        }

        return self::$slugger;
    }

    /**
     * Truncate a slug at a word boundary (hyphen)
     */
    private static function truncateAtWordBoundary(string $slug, int $maxLength): string
    {
        if (mb_strlen($slug) <= $maxLength) {
            return $slug;
        }

        $truncated = mb_substr($slug, 0, $maxLength);

        // Find the last hyphen and cut there to avoid partial words
        $lastHyphen = mb_strrpos($truncated, '-');
        if ($lastHyphen !== false && $lastHyphen > $maxLength / 2) {
            $truncated = mb_substr($truncated, 0, $lastHyphen);
        }

        // Remove trailing hyphens
        return rtrim($truncated, '-');
    }
}
