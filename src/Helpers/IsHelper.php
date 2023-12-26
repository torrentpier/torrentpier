<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Helpers;

/**
 * Class IsHelper
 * @package TorrentPier\Helpers
 */
class IsHelper
{
    /**
     * Return true if server have SSL
     *
     * @return bool
     */
    public static function isHTTPS(): bool
    {
        $is_secure = false;
        if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
            $is_secure = true;
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
            $is_secure = true;
        }
        return $is_secure;
    }

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
