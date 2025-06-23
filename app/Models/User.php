<?php

declare(strict_types=1);

namespace App\Models;

/**
 * User Model
 * 
 * Represents a user in the system
 */
class User extends Model
{
    protected string $table = 'bb_users';
    protected string $primaryKey = 'user_id';
    
    /**
     * Find user by username
     */
    public static function findByUsername(string $username): ?self
    {
        return self::findBy('username', $username);
    }
    
    /**
     * Find user by email
     */
    public static function findByEmail(string $email): ?self
    {
        return self::findBy('user_email', $email);
    }
    
    /**
     * Get user's torrents
     */
    public function getTorrents(): array
    {
        return $this->db->table('bb_bt_torrents')
            ->where('poster_id', $this->getKey())
            ->orderBy('reg_time DESC')
            ->fetchAll();
    }
    
    /**
     * Get user's posts
     */
    public function getPosts(int $limit = 10): array
    {
        return $this->db->table('bb_posts')
            ->where('poster_id', $this->getKey())
            ->orderBy('post_time DESC')
            ->limit($limit)
            ->fetchAll();
    }
    
    /**
     * Get user's groups
     */
    public function getGroups(): array
    {
        return $this->db->table('bb_user_group')
            ->where('user_id', $this->getKey())
            ->where('user_pending', 0)
            ->fetchAll();
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return (int) $this->user_level === 1;
    }
    
    /**
     * Check if user is moderator
     */
    public function isModerator(): bool
    {
        return (int) $this->user_level === 2;
    }
    
    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return (int) $this->user_active === 1;
    }
    
    /**
     * Get user's upload/download statistics
     */
    public function getStats(): array
    {
        $stats = $this->db->table('bb_bt_users')
            ->where('user_id', $this->getKey())
            ->fetch();
        
        return $stats ? (array) $stats : [
            'u_up_total' => 0,
            'u_down_total' => 0,
            'u_up_release' => 0,
            'u_up_bonus' => 0
        ];
    }
    
    /**
     * Get user's ratio
     */
    public function getRatio(): float
    {
        $stats = $this->getStats();
        $downloaded = (int) $stats['u_down_total'];
        $uploaded = (int) $stats['u_up_total'];
        
        if ($downloaded === 0) {
            return 0.0;
        }
        
        return round($uploaded / $downloaded, 2);
    }
    
    /**
     * Verify password
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->user_password);
    }
    
    /**
     * Update password
     */
    public function updatePassword(string $newPassword): void
    {
        $this->user_password = password_hash($newPassword, PASSWORD_BCRYPT);
    }
}