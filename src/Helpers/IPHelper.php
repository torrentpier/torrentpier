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
