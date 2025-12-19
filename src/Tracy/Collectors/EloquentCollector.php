<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Tracy\Collectors;

/**
 * Collects Eloquent ORM query debug information
 */
class EloquentCollector
{
    /** @var array<int, array{sql: string, bindings: array, time: float, source: string, file: string, line: int}> */
    private static array $queries = [];

    private static float $totalTime = 0.0;

    /**
     * Record a query from Eloquent's query listener
     */
    public static function recordQuery(string $sql, array $bindings, float $time): void
    {
        $source = self::findSource();

        self::$queries[] = [
            'sql' => self::interpolateQuery($sql, $bindings),
            'bindings' => $bindings,
            'time' => $time / 1000, // Convert ms to seconds
            'source' => $source['source'],
            'file' => $source['file'],
            'line' => $source['line'],
        ];

        self::$totalTime += $time / 1000;
    }

    /**
     * Get all collected queries
     *
     * @return array<int, array{sql: string, bindings: array, time: float, source: string, file: string, line: int}>
     */
    public static function getQueries(): array
    {
        return self::$queries;
    }

    /**
     * Get total query count
     */
    public static function getCount(): int
    {
        return \count(self::$queries);
    }

    /**
     * Get total query time in seconds
     */
    public static function getTotalTime(): float
    {
        return self::$totalTime;
    }

    /**
     * Reset collected data
     */
    public static function reset(): void
    {
        self::$queries = [];
        self::$totalTime = 0.0;
    }

    /**
     * Interpolate bindings into an SQL query for display
     */
    private static function interpolateQuery(string $sql, array $bindings): string
    {
        foreach ($bindings as $binding) {
            $value = match (true) {
                $binding === null => 'NULL',
                \is_bool($binding) => $binding ? '1' : '0',
                \is_int($binding), \is_float($binding) => (string)$binding,
                default => "'" . addslashes((string)$binding) . "'",
            };

            $sql = preg_replace('/\?/', $value, $sql, 1);
        }

        return $sql;
    }

    /**
     * Find the source file/line that initiated the query
     */
    private static function findSource(): array
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 20);

        foreach ($trace as $frame) {
            if (!isset($frame['file'])) {
                continue;
            }

            // Skip framework internals
            if (str_contains($frame['file'], '/vendor/illuminate/')
                || str_contains($frame['file'], '/vendor/laravel/')
                || str_contains($frame['file'], 'EloquentCollector.php')
                || str_contains($frame['file'], 'EloquentServiceProvider.php')
            ) {
                continue;
            }

            $file = $frame['file'];
            $line = $frame['line'] ?? 0;

            // Create a short source reference
            $source = basename(\dirname($file)) . '/' . basename($file) . ':' . $line;

            return [
                'source' => $source,
                'file' => $file,
                'line' => $line,
            ];
        }

        return [
            'source' => 'unknown',
            'file' => '',
            'line' => 0,
        ];
    }
}
