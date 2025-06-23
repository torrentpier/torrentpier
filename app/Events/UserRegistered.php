<?php

declare(strict_types=1);

namespace App\Events;

use DateTimeInterface;

/**
 * Event fired when a new user is registered
 */
readonly class UserRegistered
{
    /**
     * Create a new event instance
     */
    public function __construct(
        public int               $userId,
        public string            $username,
        public string            $email,
        public DateTimeInterface $registeredAt
    )
    {
    }

    /**
     * Get the user ID
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Get the username
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Get the email
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Get the registration timestamp
     */
    public function getRegisteredAt(): DateTimeInterface
    {
        return $this->registeredAt;
    }
}
