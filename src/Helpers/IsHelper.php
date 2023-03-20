<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.site)
 * @link      https://github.com/TorrentPeer/TorrentPier for the canonical source repository
 * @license   https://github.com/TorrentPeer/TorrentPier/blob/main/LICENSE MIT License
 */

namespace TorrentPier\Helpers;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;
use const FILTER_VALIDATE_FLOAT;
use const FILTER_VALIDATE_INT;

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
     * Return true if $value is int
     *
     * @param int $value
     * @return bool
     */
    public static function isInt(int $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT);
    }

    /**
     * Return true if $value is boolean
     *
     * @param bool $value
     * @return bool
     */
    public static function isBool(bool $value): bool
    {
        return is_bool(filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
    }

    /**
     * Return true if $value is floating
     *
     * @param float $value
     * @return bool
     */
    public static function isFloat(float $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT);
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
     * @param bool $req_uppercase
     * @return bool
     */
    public static function isContainsLetters(string $value, bool $req_uppercase = false): bool
    {
        return $req_uppercase ? preg_match('@[A-Z]@', $value) : preg_match('@[a-z]@', $value);
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
