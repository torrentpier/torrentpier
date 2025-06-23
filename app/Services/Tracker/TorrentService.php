<?php

declare(strict_types=1);

namespace App\Services\Tracker;

use App\Models\Torrent;
use App\Models\User;
use TorrentPier\Cache\CacheManager;
use TorrentPier\Database\Database;

/**
 * Torrent Service
 * 
 * Handles business logic for torrent operations
 */
class TorrentService
{
    public function __construct(
        private Database $db,
        private CacheManager $cache
    ) {}
    
    /**
     * Register a new torrent
     */
    public function register(array $data, User $user): Torrent
    {
        // Validate torrent data
        $this->validateTorrentData($data);
        
        // Create torrent record
        $torrent = new Torrent($this->db);
        $torrent->fill([
            'info_hash' => $data['info_hash'],
            'poster_id' => $user->getKey(),
            'size' => $data['size'],
            'reg_time' => time(),
            'tor_status' => 0,
            'checked_user_id' => 0,
            'checked_time' => 0,
            'tor_type' => 0,
            'speed_up' => 0,
            'speed_down' => 0,
        ]);
        $torrent->save();
        
        // Clear cache
        $this->cache->delete('recent_torrents');
        
        return $torrent;
    }
    
    /**
     * Update torrent information
     */
    public function update(Torrent $torrent, array $data): bool
    {
        $torrent->fill($data);
        $result = $torrent->save();
        
        // Clear relevant caches
        $this->cache->delete('torrent:' . $torrent->info_hash);
        $this->cache->delete('recent_torrents');
        
        return $result;
    }
    
    /**
     * Delete a torrent
     */
    public function delete(Torrent $torrent): bool
    {
        // Delete related data
        $this->db->table('bb_bt_tracker')
            ->where('topic_id', $torrent->getKey())
            ->delete();
        
        $this->db->table('bb_bt_tracker_snap')
            ->where('topic_id', $torrent->getKey())
            ->delete();
        
        // Delete torrent
        $result = $torrent->delete();
        
        // Clear cache
        $this->cache->delete('torrent:' . $torrent->info_hash);
        $this->cache->delete('recent_torrents');
        
        return $result;
    }
    
    /**
     * Get paginated torrents using Laravel-style collections
     */
    public function paginate(int $page = 1, ?string $category = null, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        $query = $this->db->table('bb_bt_torrents as t')
            ->select('t.*, ts.seeders, ts.leechers, ts.complete')
            ->leftJoin('bb_bt_tracker_snap as ts', 'ts.topic_id = t.topic_id')
            ->where('t.tor_status', 0)
            ->orderBy('t.reg_time DESC')
            ->limit($perPage)
            ->offset($offset);
        
        if ($category !== null) {
            $query->where('t.tor_type', $category);
        }
        
        $rows = $query->fetchAll();
        
        // Use Laravel-style collection for better data manipulation
        $torrents = collect($rows)
            ->map(fn($row) => new Torrent($this->db, (array) $row))
            ->values()
            ->toArray();
        
        return [
            'data' => $torrents,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $this->countTorrents($category),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $this->countTorrents($category)),
            'last_page' => ceil($this->countTorrents($category) / $perPage)
        ];
    }
    
    /**
     * Search torrents using collections and modern string helpers
     */
    public function search(string $query, array $filters = []): array
    {
        $cacheKey = 'search:' . str($query . serialize($filters))->hash('md5');
        
        return $this->cache->remember($cacheKey, 300, function() use ($query, $filters) {
            $qb = $this->db->table('bb_bt_torrents as t')
                ->select('t.*, ts.seeders, ts.leechers')
                ->leftJoin('bb_bt_tracker_snap as ts', 'ts.topic_id = t.topic_id')
                ->leftJoin('bb_topics as top', 'top.topic_id = t.topic_id')
                ->where('t.tor_status', 0);
            
            // Search in topic title (cleaned query)
            if (!empty($query)) {
                $cleanQuery = str($query)->trim()->lower()->limit(100);
                $qb->where('LOWER(top.topic_title) LIKE ?', '%' . $cleanQuery . '%');
            }
            
            // Apply filters using data_get helper
            if ($category = data_get($filters, 'category')) {
                $qb->where('t.tor_type', $category);
            }
            
            if ($minSeeders = data_get($filters, 'min_seeders')) {
                $qb->where('ts.seeders >= ?', $minSeeders);
            }
            
            $rows = $qb->limit(100)->fetchAll();
            
            // Use collection to transform and filter results
            $torrents = collect($rows)
                ->map(fn($row) => new Torrent($this->db, (array) $row))
                ->when(data_get($filters, 'sort') === 'popular', function ($collection) {
                    return $collection->sortByDesc(fn($torrent) => $torrent->seeders ?? 0);
                })
                ->when(data_get($filters, 'sort') === 'recent', function ($collection) {
                    return $collection->sortByDesc('reg_time');
                })
                ->values()
                ->toArray();
            
            return $torrents;
        });
    }
    
    /**
     * Get torrent statistics
     */
    public function getStatistics(): array
    {
        return $this->cache->remember('torrent_stats', 3600, function() {
            $stats = [];
            
            // Total torrents
            $stats['total_torrents'] = $this->db->table('bb_bt_torrents')
                ->where('tor_status', 0)
                ->count('*');
            
            // Total size
            $totalSize = $this->db->table('bb_bt_torrents')
                ->where('tor_status', 0)
                ->sum('size');
            $stats['total_size'] = $totalSize ?: 0;
            
            // Active peers
            $stats['active_peers'] = $this->db->table('bb_bt_tracker')
                ->count('*');
            
            // Completed downloads
            $stats['total_completed'] = $this->db->table('bb_bt_torrents')
                ->where('tor_status', 0)
                ->sum('complete_count');
            
            return $stats;
        });
    }
    
    /**
     * Validate torrent data
     */
    private function validateTorrentData(array $data): void
    {
        if (empty($data['info_hash']) || strlen($data['info_hash']) !== 40) {
            throw new \InvalidArgumentException('Invalid info hash');
        }
        
        if (empty($data['size']) || $data['size'] <= 0) {
            throw new \InvalidArgumentException('Invalid torrent size');
        }
        
        // Check if torrent already exists
        $existing = Torrent::findByInfoHash($data['info_hash']);
        if ($existing !== null) {
            throw new \InvalidArgumentException('Torrent already exists');
        }
    }
    
    /**
     * Count torrents
     */
    private function countTorrents(?string $category = null): int
    {
        $query = $this->db->table('bb_bt_torrents')
            ->where('tor_status', 0);
        
        if ($category !== null) {
            $query->where('tor_type', $category);
        }
        
        return $query->count('*');
    }
}