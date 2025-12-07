<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Helpers;

/**
 * Class StringHelper
 * @package TorrentPier\Helpers
 */
class StringHelper
{
    /**
     * Return true if $value contains numbers
     *
     * @param string $value
     * @return bool
     */
    public static function isContainsNums(string $value): bool
    {
        return preg_match('@[[:digit:]]@', $value);
    }

    /**
     * Return true if $value contains letters (Uppercase included)
     *
     * @param string $value
     * @param bool $uppercase
     * @return bool
     */
    public static function isContainsLetters(string $value, bool $uppercase = false): bool
    {
        return $uppercase ? preg_match('@[A-Z]@', $value) : preg_match('@[a-z]@', $value);
    }

    /**
     * Return true if $value contains special symbols
     *
     * @param string $value
     * @return bool
     */
    public static function isContainsSpecSymbols(string $value): bool
    {
        return preg_match('@[[:punct:]]@', $value);
    }
}
