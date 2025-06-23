<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;
use TorrentPier\Cache\CacheManager;
use TorrentPier\Database\Database;

/**
 * User Service
 * 
 * Handles business logic for user operations
 */
class UserService
{
    public function __construct(
        private Database $db,
        private CacheManager $cache
    ) {}

    /**
     * Register a new user
     */
    public function register(array $data): User
    {
        // Validate data
        $this->validateRegistrationData($data);

        // Create user
        $user = new User($this->db);
        $user->fill([
            'username' => $data['username'],
            'user_email' => $data['email'],
            'user_password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'user_level' => 0, // Regular user
            'user_active' => 1,
            'user_regdate' => now()->timestamp,
            'user_lastvisit' => now()->timestamp,
            'user_timezone' => 0,
            'user_lang' => 'en',
            'user_dateformat' => 'd M Y H:i',
        ]);
        
        $user->save();

        // Clear user cache
        $this->cache->delete('user_count');
        
        return $user;
    }

    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $data): bool
    {
        $allowedFields = [
            'user_timezone',
            'user_lang',
            'user_dateformat',
        ];

        $updateData = collect($data)
            ->only($allowedFields)
            ->filter()
            ->toArray();

        if (empty($updateData)) {
            return true;
        }

        $user->fill($updateData);
        $result = $user->save();

        // Clear user cache
        $this->cache->delete('user:' . $user->getKey());

        return $result;
    }

    /**
     * Change user password
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!$user->verifyPassword($currentPassword)) {
            throw new \InvalidArgumentException('Current password is incorrect');
        }

        $user->updatePassword($newPassword);
        return $user->save();
    }

    /**
     * Get user statistics
     */
    public function getUserStats(User $user): array
    {
        $cacheKey = 'user_stats:' . $user->getKey();

        return $this->cache->remember($cacheKey, 1800, function() use ($user) {
            $stats = $user->getStats();
            $torrents = $user->getTorrents();
            $posts = $user->getPosts(5);

            return [
                'upload_stats' => [
                    'total_uploaded' => $stats['u_up_total'] ?? 0,
                    'total_downloaded' => $stats['u_down_total'] ?? 0,
                    'ratio' => $user->getRatio(),
                ],
                'activity' => [
                    'torrents_count' => count($torrents),
                    'recent_posts' => count($posts),
                    'last_visit' => now()->createFromTimestamp($user->user_lastvisit)->diffForHumans(),
                ],
                'permissions' => [
                    'level' => $user->user_level,
                    'is_admin' => $user->isAdmin(),
                    'is_moderator' => $user->isModerator(),
                    'is_active' => $user->isActive(),
                ]
            ];
        });
    }

    /**
     * Search users using modern collection methods
     */
    public function searchUsers(string $query, array $filters = []): array
    {
        $cacheKey = 'user_search:' . str($query . serialize($filters))->hash('md5');

        return $this->cache->remember($cacheKey, 600, function() use ($query, $filters) {
            // Get all active users (in a real app, this would be paginated)
            $users = collect(User::all())
                ->where('user_active', 1);

            // Apply search filter
            if (!empty($query)) {
                $searchTerm = str($query)->lower();
                $users = $users->filter(function ($user) use ($searchTerm) {
                    return str($user->username)->lower()->contains($searchTerm) ||
                           str($user->user_email)->lower()->contains($searchTerm);
                });
            }

            // Apply level filter
            if ($level = data_get($filters, 'level')) {
                $users = $users->where('user_level', $level);
            }

            // Apply sorting
            $sortBy = data_get($filters, 'sort', 'username');
            $sortDirection = data_get($filters, 'direction', 'asc');

            $users = $sortDirection === 'desc' 
                ? $users->sortByDesc($sortBy)
                : $users->sortBy($sortBy);

            return $users->values()->toArray();
        });
    }

    /**
     * Validate registration data
     */
    private function validateRegistrationData(array $data): void
    {
        if (empty($data['username'])) {
            throw new \InvalidArgumentException('Username is required');
        }

        if (empty($data['email'])) {
            throw new \InvalidArgumentException('Email is required');
        }

        if (empty($data['password'])) {
            throw new \InvalidArgumentException('Password is required');
        }

        // Check if username already exists
        $existingUser = User::findByUsername($data['username']);
        if ($existingUser) {
            throw new \InvalidArgumentException('Username already exists');
        }

        // Check if email already exists
        $existingEmail = User::findByEmail($data['email']);
        if ($existingEmail) {
            throw new \InvalidArgumentException('Email already exists');
        }
    }
}