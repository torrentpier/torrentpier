<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Helpers;

use Carbon\Carbon;

use Exception;
use function config;
use function is_numeric;

/**
 * Time formatting helper using a Carbon library
 */
class TimeHelper
{
    /**
     * Format time difference in human-readable format
     *
     * Uses Carbon with user's language from session (falls back to forum default → 'en').
     *
     * Examples:
     * - TimeHelper::humanTime($timestamp) → "5 minutes ago"
     * - TimeHelper::humanTime($past, $now) → "2 days before"
     *
     * @param int|string $timestamp Timestamp or date string
     * @param int|string|null $reference Reference timestamp (null = now)
     * @return string Human-readable time difference
     */
    public static function humanTime(int|string $timestamp, int|string|null $reference = null): string
    {
        $locale = config()->get('default_lang', 'en');

        try {
            // Parse timestamp - handle both numeric strings and date strings
            $time = is_numeric($timestamp)
                ? Carbon::createFromTimestamp((int)$timestamp)
                : Carbon::parse($timestamp);

            // Parse reference if provided
            $ref = null;
            if ($reference !== null) {
                $ref = is_numeric($reference)
                    ? Carbon::createFromTimestamp((int)$reference)
                    : Carbon::parse($reference);
            }

            return $time->locale($locale)->diffForHumans($ref);
        } catch (Exception) {
            // Fallback for invalid timestamps
            return '';
        }
    }
}
