<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Tracy\Collectors;

use TorrentPier\Database\DatabaseFactory;

/**
 * Collects database query debug information from DatabaseDebugger
 */
class DatabaseCollector
{
    private ?array $cachedData = null;

    /**
     * Collect all database debug data
     */
    public function collect(): array
    {
        if ($this->cachedData !== null) {
            return $this->cachedData;
        }

        $data = [
            'servers' => [],
            'total_queries' => 0,
            'total_time' => 0.0,
            'legacy_count' => 0,
            'slow_count' => 0,
            'nette_count' => 0,
        ];

        $slowThreshold = defined('SQL_SLOW_QUERY_TIME') ? SQL_SLOW_QUERY_TIME : 3.0;

        try {
            $serverNames = DatabaseFactory::getServerNames();

            foreach ($serverNames as $serverName) {
                try {
                    $db = DatabaseFactory::getInstance($serverName);
                    $debugger = $db->debugger;

                    $serverData = [
                        'name' => $serverName,
                        'engine' => $db->engine,
                        'version' => $db->server_version(),
                        'database' => $db->selected_db,
                        'host' => $db->db_server,
                        'num_queries' => $db->num_queries,
                        'total_time' => $db->sql_timetotal,
                        'queries' => [],
                        'legacy_queries' => $debugger->legacy_queries ?? [],
                    ];

                    // Check if EXPLAIN collection is enabled via cookie
                    $collectExplain = (bool) request()->cookies->get('tracy_explain');

                    // Process individual queries
                    foreach ($debugger->dbg ?? [] as $idx => $query) {
                        $queryData = [
                            'id' => $idx,
                            'sql' => $query['sql'] ?? '',
                            'time' => $query['time'] ?? 0,
                            'source' => $query['src'] ?? 'unknown',
                            'file' => $query['file'] ?? '',
                            'line' => $query['line'] ?? '',
                            'info' => $query['info_plain'] ?? $query['info'] ?? '',
                            'is_legacy' => $query['is_legacy_query'] ?? false,
                            'is_nette' => $query['is_nette_explorer'] ?? false,
                            'mem_before' => $query['mem_before'] ?? 0,
                            'mem_after' => $query['mem_after'] ?? 0,
                            'is_slow' => ($query['time'] ?? 0) > $slowThreshold,
                            'explain' => null,
                        ];

                        // Collect EXPLAIN if enabled
                        if ($collectExplain && $this->canExplain($queryData['sql'])) {
                            $queryData['explain'] = $this->explainQuery($queryData['sql'], $serverName);
                        }

                        $serverData['queries'][] = $queryData;

                        if ($queryData['is_legacy']) {
                            $data['legacy_count']++;
                        }
                        if ($queryData['is_slow']) {
                            $data['slow_count']++;
                        }
                        if ($queryData['is_nette']) {
                            $data['nette_count']++;
                        }
                    }

                    $data['servers'][$serverName] = $serverData;
                    $data['total_queries'] += $db->num_queries;
                    $data['total_time'] += $db->sql_timetotal;

                } catch (\Exception $e) {
                    // Server not available, skip
                }
            }
        } catch (\Exception $e) {
            // DatabaseFactory not available
        }

        $this->cachedData = $data;
        return $data;
    }

    /**
     * Get summary statistics for tab display
     */
    public function getStats(): array
    {
        $data = $this->collect();

        return [
            'total_queries' => $data['total_queries'],
            'total_time' => $data['total_time'],
            'legacy_count' => $data['legacy_count'],
            'slow_count' => $data['slow_count'],
            'nette_count' => $data['nette_count'],
            'server_count' => count($data['servers']),
            'explain_enabled' => (bool) request()->cookies->get('tracy_explain'),
        ];
    }

    /**
     * Check if query can be explained
     */
    private function canExplain(string $sql): bool
    {
        $sql = trim(preg_replace('#^(\s*)(/\*)(.*)(\*/)(\s*)#', '', $sql));
        $upper = strtoupper($sql);
        return str_starts_with($upper, 'SELECT')
            || str_starts_with($upper, 'UPDATE')
            || str_starts_with($upper, 'DELETE');
    }

    /**
     * Get EXPLAIN results for a query
     */
    public function explainQuery(string $sql, string $serverName = 'db'): ?array
    {
        try {
            $db = DatabaseFactory::getInstance($serverName);

            // Remove debug comments
            $sql = preg_replace('#^(\s*)(/\*)(.*)(\*/)(\s*)#', '', $sql);
            $sql = trim($sql);

            // Convert UPDATE/DELETE to SELECT for EXPLAIN
            if (preg_match('#UPDATE ([a-z0-9_]+).*?WHERE(.*)/#i', $sql, $m)) {
                $sql = "SELECT * FROM $m[1] WHERE $m[2]";
            } elseif (preg_match('#DELETE FROM ([a-z0-9_]+).*?WHERE(.*)#si', $sql, $m)) {
                $sql = "SELECT * FROM $m[1] WHERE $m[2]";
            }

            // Only EXPLAIN SELECT queries
            if (!str_starts_with(strtoupper($sql), 'SELECT')) {
                return null;
            }

            $result = $db->connection->query("EXPLAIN $sql");
            $rows = [];
            while ($row = $result->fetch()) {
                $rows[] = (array)$row;
            }

            return $rows;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Reset cached data (for testing)
     */
    public function reset(): void
    {
        $this->cachedData = null;
    }
}
