<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Torrent Model
 * 
 * Represents a torrent in the database
 */
class Torrent extends Model
{
    protected string $table = 'bb_bt_torrents';
    protected string $primaryKey = 'topic_id';
    
    /**
     * Get active peers for this torrent
     */
    public function getPeers(): array
    {
        return $this->db->table('bb_bt_tracker')
            ->where('topic_id', $this->getKey())
            ->where('complete', 0)
            ->fetchAll();
    }
    
    /**
     * Get completed peers (seeders) for this torrent
     */
    public function getSeeders(): array
    {
        return $this->db->table('bb_bt_tracker')
            ->where('topic_id', $this->getKey())
            ->where('complete', 1)
            ->fetchAll();
    }
    
    /**
     * Get torrent statistics
     */
    public function getStats(): array
    {
        $stats = $this->db->table('bb_bt_tracker_snap')
            ->where('topic_id', $this->getKey())
            ->fetch();
        
        return $stats ? (array) $stats : [
            'seeders' => 0,
            'leechers' => 0,
            'complete' => 0
        ];
    }
    
    /**
     * Get the user who uploaded this torrent
     */
    public function getUploader(): ?User
    {
        return User::find($this->poster_id);
    }
    
    /**
     * Get the forum topic associated with this torrent
     */
    public function getTopic(): array
    {
        $topic = $this->db->table('bb_topics')
            ->where('topic_id', $this->getKey())
            ->fetch();
        
        return $topic ? (array) $topic : [];
    }
    
    /**
     * Check if torrent is active
     */
    public function isActive(): bool
    {
        return (int) $this->tor_status === 0;
    }
    
    /**
     * Find torrent by info hash
     */
    public static function findByInfoHash(string $infoHash): ?self
    {
        return self::findBy('info_hash', $infoHash);
    }
    
    /**
     * Get recent torrents
     */
    public static function getRecent(int $limit = 10): array
    {
        $instance = new static(DB());
        $rows = $instance->db->table($instance->table)
            ->orderBy('reg_time DESC')
            ->limit($limit)
            ->fetchAll();
        
        $torrents = [];
        foreach ($rows as $row) {
            $torrents[] = new static(DB(), (array) $row);
        }
        
        return $torrents;
    }
}