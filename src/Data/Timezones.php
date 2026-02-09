<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Data;

/**
 * Timezone offset mappings.
 */
final class Timezones
{
    public const array LIST = [
        '-12' => 'UTC - 12 (International Date Line West)',
        '-11' => 'UTC - 11 (Samoa)',
        '-10' => 'UTC - 10 (Hawaii)',
        '-9' => 'UTC - 9 (Alaska)',
        '-8' => 'UTC - 8 (Pacific Time)',
        '-7' => 'UTC - 7 (Mountain Time)',
        '-6' => 'UTC - 6 (Central Time)',
        '-5' => 'UTC - 5 (Eastern Time)',
        '-4' => 'UTC - 4 (Atlantic Time)',
        '-3.5' => 'UTC - 3:30 (Newfoundland)',
        '-3' => 'UTC - 3 (Buenos Aires, Brasilia)',
        '-2' => 'UTC - 2 (Mid-Atlantic)',
        '-1' => 'UTC - 1 (Azores)',
        '0' => 'UTC (London, Lisbon, Dublin)',
        '1' => 'UTC + 1 (Paris, Berlin, Rome)',
        '2' => 'UTC + 2 (Athens, Cairo, Helsinki)',
        '3' => 'UTC + 3 (Moscow, Istanbul, Baghdad)',
        '3.5' => 'UTC + 3:30 (Tehran)',
        '4' => 'UTC + 4 (Dubai, Baku)',
        '4.5' => 'UTC + 4:30 (Kabul)',
        '5' => 'UTC + 5 (Islamabad, Karachi, Tashkent)',
        '5.5' => 'UTC + 5:30 (Mumbai, New Delhi)',
        '6' => 'UTC + 6 (Almaty, Dhaka)',
        '6.5' => 'UTC + 6:30 (Yangon)',
        '7' => 'UTC + 7 (Bangkok, Jakarta, Hanoi)',
        '8' => 'UTC + 8 (Beijing, Hong Kong, Singapore)',
        '9' => 'UTC + 9 (Tokyo, Seoul)',
        '9.5' => 'UTC + 9:30 (Adelaide)',
        '10' => 'UTC + 10 (Sydney, Melbourne, Brisbane)',
        '11' => 'UTC + 11 (Solomon Islands)',
        '12' => 'UTC + 12 (Auckland, Wellington, Fiji)',
        '13' => 'UTC + 13 (Nuku\'alofa)',
        '14' => 'UTC + 14 (Kiritimati)',
    ];

    /**
     * Get all timezone mappings.
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        return self::LIST;
    }

    /**
     * Get timezone name by offset.
     */
    public static function getName(string $offset): string
    {
        return self::LIST[$offset] ?? 'UTC';
    }

    /**
     * Check if timezone offset exists.
     */
    public static function exists(string $offset): bool
    {
        return isset(self::LIST[$offset]);
    }
}
