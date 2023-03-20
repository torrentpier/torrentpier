<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Helpers;

use Longman\IPTools\Ip;

use function strlen;

/**
 * Class IPHelper
 * @package TorrentPier\Helpers
 */
class IPHelper
{
    /**
     * Decode original IP
     *
     * @param $ip
     *
     * @return string
     */
    public static function encodeIP($ip): string
    {
        return Ip::ip2long($ip);
    }

    /**
     * Recovery of decoded IP
     *
     * @param $ip
     *
     * @return string
     */
    public static function decodeIP($ip): string
    {
        return Ip::long2ip($ip);
    }

    /**
     * Checking IP for validity
     *
     * @param $ip
     *
     * @return bool
     */
    public static function isValid($ip): bool
    {
        return Ip::isValid($ip);
    }

    /**
     * Checking if it is a local IP
     *
     * @param $ip
     *
     * @return bool
     */
    public static function isLocal($ip): bool
    {
        return IP::isLocal($ip);
    }

    /**
     * Checking if it is a remote IP
     *
     * @param $ip
     *
     * @return bool
     */
    public static function isRemote($ip): bool
    {
        return IP::isRemote($ip);
    }

    /**
     * Compare IP
     *
     * @param $ip
     * @param $range
     *
     * @return bool
     */
    public static function compareIP($ip, $range): bool
    {
        return Ip::compare($ip, $range);
    }

    /**
     * Anonymizes an IP/IPv6.
     *
     * Removes the last byte for v4 and the last 8 bytes for v6 IPs
     *
     * -------------------------------------------------------------
     * From Symfony
     */
    public static function anonymizeIP(string $ip): string
    {
        $wrappedIPv6 = false;
        if ('[' === substr($ip, 0, 1) && ']' === substr($ip, -1, 1)) {
            $wrappedIPv6 = true;
            $ip = substr($ip, 1, -1);
        }

        $packedAddress = inet_pton($ip);
        if (4 === strlen($packedAddress)) {
            $mask = '255.255.255.0';
        } elseif ($ip === inet_ntop($packedAddress & inet_pton('::ffff:ffff:ffff'))) {
            $mask = '::ffff:ffff:ff00';
        } elseif ($ip === inet_ntop($packedAddress & inet_pton('::ffff:ffff'))) {
            $mask = '::ffff:ff00';
        } else {
            $mask = 'ffff:ffff:ffff:ffff:0000:0000:0000:0000';
        }
        $ip = inet_ntop($packedAddress & inet_pton($mask));

        if ($wrappedIPv6) {
            $ip = '[' . $ip . ']';
        }

        return $ip;
    }
}
