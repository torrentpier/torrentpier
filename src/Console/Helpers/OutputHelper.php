<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Console\Helpers;

/**
 * Helper class for console output formatting
 */
class OutputHelper
{
    /**
     * Keywords that indicate sensitive values
     */
    private const array SENSITIVE_KEYWORDS = ['PASSWORD', 'SECRET', 'KEY', 'TOKEN'];

    /**
     * Mask sensitive value for display
     *
     * @param string $name Variable name to check for sensitive keywords
     * @param string $value Value to mask
     * @return string Masked or original value
     */
    public static function maskSensitive(string $name, string $value): string
    {
        foreach (self::SENSITIVE_KEYWORDS as $keyword) {
            if (stripos($name, $keyword) !== false) {
                if (strlen($value) <= 4) {
                    return '****';
                }
                return substr($value, 0, 2) . str_repeat('*', strlen($value) - 4) . substr($value, -2);
            }
        }

        return $value;
    }
}
