<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Tracy\Collectors;

use Throwable;
use TorrentPier\Cache\UnifiedCacheSystem;

/**
 * Collects cache and datastore debug information
 */
class CacheCollector
{
    private ?array $cachedData = null;

    /**
     * Collect all cache debug data
     */
    public function collect(): array
    {
        if ($this->cachedData !== null) {
            return $this->cachedData;
        }

        $data = [
            'caches' => [],
            'datastore' => null,
            'total_queries' => 0,
            'total_time' => 0.0,
        ];

        try {
            $cacheSystem = app(UnifiedCacheSystem::class);
            $cacheObjects = $cacheSystem->obj; // Uses magic __get method

            foreach ($cacheObjects as $cacheName => $cacheObj) {
                // Skip internal stub cache
                if ($cacheName === '__stub') {
                    continue;
                }

                $cacheData = [
                    'name' => $cacheName,
                    'engine' => $cacheObj->engine ?? 'unknown',
                    'queries' => [],
                    'num_queries' => 0,
                    'total_time' => 0.0,
                ];

                // Extract debug info from a cache object
                $debugInfo = $this->extractDebugInfo($cacheObj);
                $cacheData['queries'] = $debugInfo['queries'];
                $cacheData['num_queries'] = $debugInfo['num_queries'];
                $cacheData['total_time'] = $debugInfo['total_time'];

                // Skip caches with no operations
                if ($cacheData['num_queries'] === 0) {
                    continue;
                }

                $data['caches'][$cacheName] = $cacheData;
                $data['total_queries'] += $cacheData['num_queries'];
                $data['total_time'] += $cacheData['total_time'];
            }
        } catch (Throwable) {
            // Cache system not available
        }

        // Collect datastore info
        try {
            $datastore = datastore();
            $datastoreData = [
                'engine' => $datastore->engine ?? 'unknown',
                'queries' => [],
                'num_queries' => 0,
                'total_time' => 0.0,
            ];

            $debugInfo = $this->extractDebugInfo($datastore);
            $datastoreData['queries'] = $debugInfo['queries'];
            $datastoreData['num_queries'] = $debugInfo['num_queries'];
            $datastoreData['total_time'] = $debugInfo['total_time'];

            $data['datastore'] = $datastoreData;
            $data['total_queries'] += $datastoreData['num_queries'];
            $data['total_time'] += $datastoreData['total_time'];
        } catch (Throwable) {
            // Datastore not available
        }

        $this->cachedData = $data;

        return $data;
    }

    /**
     * Get summary statistics for the tab display
     */
    public function getStats(): array
    {
        $data = $this->collect();

        // Count cache engines: caches + datastore (if it has operations)
        $engineCount = \count($data['caches']);
        if ($data['datastore'] !== null && $data['datastore']['num_queries'] > 0) {
            $engineCount++;
        }

        return [
            'total_queries' => $data['total_queries'],
            'total_time' => $data['total_time'],
            'cache_count' => $engineCount,
            'has_datastore' => $data['datastore'] !== null,
        ];
    }

    /**
     * Reset cached data
     */
    public function reset(): void
    {
        $this->cachedData = null;
    }

    /**
     * Extract debug info from a cache/datastore object
     */
    private function extractDebugInfo(object $obj): array
    {
        if (!empty($obj->db->dbg)) {
            return [
                'queries' => $this->processQueries($obj->db->dbg),
                'num_queries' => \count($obj->db->dbg),
                'total_time' => $obj->db->sql_timetotal ?? 0,
            ];
        }

        if (!empty($obj->dbg)) {
            return [
                'queries' => $this->processQueries($obj->dbg),
                'num_queries' => \count($obj->dbg),
                'total_time' => $obj->sql_timetotal ?? 0,
            ];
        }

        return ['queries' => [], 'num_queries' => 0, 'total_time' => 0.0];
    }

    /**
     * Process raw query debug array into standardized format
     */
    private function processQueries(array $dbg): array
    {
        $queries = [];

        foreach ($dbg as $idx => $query) {
            $queries[] = [
                'id' => $idx,
                'sql' => $query['sql'] ?? '',
                'time' => $query['time'] ?? 0,
                'source' => $query['src'] ?? 'unknown',
                'info' => $query['info_plain'] ?? $query['info'] ?? '',
            ];
        }

        return $queries;
    }
}
