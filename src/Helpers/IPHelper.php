<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Helpers;

use Longman\IPTools\Ip;

/**
 * Class IPHelper
 * @package TorrentPier\Helpers
 */
class IPHelper extends Ip
{
    /**
     * Anonymize an IP/IPv6.
     * Removes the last byte for v4 and the last 8 bytes for v6 IPs
     *
     * @param string $ip
     * @return string
     */
    public static function anonymizeIP(string $ip): string
    {
        $wrappedIPv6 = false;
        if (str_starts_with($ip, '[') && str_ends_with($ip, ']')) {
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

    /**
     * Converts IP from any format (long or string) to readable string format.
     * Accepts both numeric long format (2130706433) and string IP (127.0.0.1).
     *
     * @param string|int $ip IP in any format
     * @return string IP in readable format (e.g., "127.0.0.1")
     */
    public static function decode(string|int $ip): string
    {
        // If it's a valid string IP, return as is
        if (self::isValid($ip)) {
            return (string) $ip;
        }

        // If it's numeric (long format), convert to string IP
        if (is_numeric($ip)) {
            $ipLong = (string) $ip;
            $isIPv6 = bccomp($ipLong, '4294967295') === 1;
            return self::long2ip($ipLong, $isIPv6);
        }

        // Fallback - return as is
        return (string) $ip;
    }
}
