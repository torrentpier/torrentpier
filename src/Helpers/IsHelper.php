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
     * Determines if the current version of PHP is equal to or greater than the supplied value
     *
     * @param string $version
     * @return bool TRUE if the current version is $version or higher
     */
    public static function isPHP(string $version): bool
    {
        return version_compare(PHP_VERSION, $version, '>=');
    }

    /**
     * Return true if server have SSL
     *
     * @return bool
     */
    public static function isHTTPS(): bool
    {
        if (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
            || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
            || (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == 443)
            || (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https')
        ) {
            return true;
        }

        return false;
    }
}
