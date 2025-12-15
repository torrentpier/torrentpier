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

    /**
     * Format timestamp with timezone and locale support
     *
     * @param int $timestamp Unix timestamp (UTC)
     * @param string|false $format Date format string (false = use default)
     * @param bool $friendlyDate Show "Today"/"Yesterday" instead of date
     * @param float $timezoneOffset Timezone offset in hours (e.g., 3, -5, 5.5)
     * @param string|null $locale Locale for translations (null = use default)
     * @param array $labels Custom labels for "today" and "yesterday"
     * @return string Formatted date string
     */
    public static function formatDate(
        int          $timestamp,
        string|false $format = false,
        bool         $friendlyDate = true,
        float        $timezoneOffset = 0,
        ?string      $locale = null,
        array        $labels = [],
    ): string {
        if (!$format) {
            $format = \function_exists('config')
                ? config()->get('default_dateformat', 'd-M-Y H:i')
                : 'd-M-Y H:i';
        }

        $locale = $locale ?? (\function_exists('config') ? config()->get('default_lang', 'en') : 'en');
        $translateDates = \function_exists('config') ? config()->get('translate_dates', true) : true;

        try {
            // Create Carbon instance with timezone offset
            $carbon = Carbon::createFromTimestamp($timestamp, 'UTC')->addHours($timezoneOffset);
            $now = Carbon::createFromTimestamp(time(), 'UTC')->addHours($timezoneOffset);

            if ($friendlyDate) {
                // Check if the date is today
                if ($carbon->isSameDay($now)) {
                    $todayLabel = $labels['today'] ?? 'Today';

                    return $todayLabel . ' ' . $carbon->format('H:i');
                }
                // Check if the date is yesterday
                if ($carbon->isSameDay($now->copy()->subDay())) {
                    $yesterdayLabel = $labels['yesterday'] ?? 'Yesterday';

                    return $yesterdayLabel . ' ' . $carbon->format('H:i');
                }
            }

            // Use instance-based locale to avoid global state modification
            return $translateDates
                ? $carbon->locale($locale)->translatedFormat($format)
                : $carbon->format($format);
        } catch (Exception $e) {
            error_log('TimeHelper::formatDate error: ' . $e->getMessage());

            return '';
        }
    }

    /**
     * Calculate age from a birthday and return a human-readable format
     *
     * @param string|null $date Birthday date string
     * @param float|null $timezoneOffset Timezone offset in hours (null = use board default)
     * @return string Human-readable age or empty string
     */
    public static function birthdayAge(?string $date, ?float $timezoneOffset = null): string
    {
        if (empty($date)) {
            return '';
        }

        $timezoneOffset ??= (float)config()->get('board_timezone', 0);
        $now = \defined('TIMENOW') ? TIMENOW : time();

        return self::humanTime(strtotime($date, $now + (3600 * $timezoneOffset)));
    }
}
