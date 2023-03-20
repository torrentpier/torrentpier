<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
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
     * Determines if the current version of PHP is equal to or greater than the supplied value
     *
     * @param string $version
     * @return bool TRUE if the current version is $version or higher
     */
    public static function isPHP(string $version): bool
    {
        static $_is_php;
        $version = (string)$version;
        if (!isset($_is_php[$version])) {
            $_is_php[$version] = version_compare(PHP_VERSION, $version, '>=');
        }
        return $_is_php[$version];
    }

    /**
     * Return true if ajax request
     *
     * @return bool
     */
    public static function isAJAX(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

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
