<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Tracy\Collectors;

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
            $cacheSystem = UnifiedCacheSystem::getInstance();
            $cacheObjects = $cacheSystem->obj; // Uses magic __get method

            foreach ($cacheObjects as $cacheName => $cacheObj) {
                $cacheData = [
                    'name' => $cacheName,
                    'engine' => $cacheObj->engine ?? 'unknown',
                    'queries' => [],
                    'num_queries' => 0,
                    'total_time' => 0.0,
                ];

                // Check if cache has database backend with debug info
                if (!empty($cacheObj->db->dbg)) {
                    $cacheData['queries'] = $this->processQueries($cacheObj->db->dbg);
                    $cacheData['num_queries'] = count($cacheObj->db->dbg);
                    $cacheData['total_time'] = $cacheObj->db->sql_timetotal ?? 0;
                } elseif (!empty($cacheObj->dbg)) {
                    $cacheData['queries'] = $this->processQueries($cacheObj->dbg);
                    $cacheData['num_queries'] = count($cacheObj->dbg);
                    $cacheData['total_time'] = $cacheObj->sql_timetotal ?? 0;
                }

                $data['caches'][$cacheName] = $cacheData;
                $data['total_queries'] += $cacheData['num_queries'];
                $data['total_time'] += $cacheData['total_time'];
            }

        } catch (\Exception $e) {
            // Cache system not available
        }

        // Collect datastore info
        try {
            $datastore = datastore();

            if ($datastore) {
                $datastoreData = [
                    'engine' => $datastore->engine ?? 'unknown',
                    'queries' => [],
                    'num_queries' => 0,
                    'total_time' => 0.0,
                ];

                if (!empty($datastore->db->dbg)) {
                    $datastoreData['queries'] = $this->processQueries($datastore->db->dbg);
                    $datastoreData['num_queries'] = count($datastore->db->dbg);
                    $datastoreData['total_time'] = $datastore->db->sql_timetotal ?? 0;
                } elseif (!empty($datastore->dbg)) {
                    $datastoreData['queries'] = $this->processQueries($datastore->dbg);
                    $datastoreData['num_queries'] = count($datastore->dbg);
                    $datastoreData['total_time'] = $datastore->sql_timetotal ?? 0;
                }

                $data['datastore'] = $datastoreData;
                $data['total_queries'] += $datastoreData['num_queries'];
                $data['total_time'] += $datastoreData['total_time'];
            }

        } catch (\Exception $e) {
            // Datastore not available
        }

        $this->cachedData = $data;
        return $data;
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

    /**
     * Get summary statistics for tab display
     */
    public function getStats(): array
    {
        $data = $this->collect();

        return [
            'total_queries' => $data['total_queries'],
            'total_time' => $data['total_time'],
            'cache_count' => count($data['caches']),
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
}
