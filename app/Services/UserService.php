<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\UserRegistered;

/**
 * User Service
 * 
 * Example service demonstrating event usage
 */
class UserService
{
    /**
     * Register a new user
     * 
     * @param array $userData User registration data
     * @return int User ID
     */
    public function registerUser(array $userData): int
    {
        // TODO: Implement actual user registration logic
        // This is a simplified example
        
        // Simulate user creation
        $userId = random_int(1000, 9999);
        $username = $userData['username'] ?? 'user' . $userId;
        $email = $userData['email'] ?? $username . '@example.com';
        
        // Dispatch the UserRegistered event
        event(new UserRegistered(
            userId: $userId,
            username: $username,
            email: $email,
            registeredAt: new \DateTime()
        ));
        
        return $userId;
    }
}