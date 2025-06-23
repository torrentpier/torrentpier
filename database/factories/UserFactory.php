<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;

/**
 * User Factory
 * 
 * Create fake user data for testing
 */
class UserFactory
{
    /**
     * Define the model's default state using Laravel-style helpers
     */
    public function definition(): array
    {
        return [
            'username' => str('user_')->append(str()->random(8))->lower(),
            'user_email' => fake()->email(),
            'user_password' => password_hash('password', PASSWORD_BCRYPT),
            'user_level' => 0, // Regular user
            'user_active' => 1,
            'user_regdate' => now()->timestamp,
            'user_lastvisit' => now()->timestamp,
            'user_timezone' => 0,
            'user_lang' => 'en',
            'user_dateformat' => 'd M Y H:i',
        ];
    }
    
    /**
     * Create a user instance
     */
    public function make(array $attributes = []): User
    {
        $data = array_merge($this->definition(), $attributes);
        return new User(DB(), $data);
    }
    
    /**
     * Create and save a user instance
     */
    public function create(array $attributes = []): User
    {
        $user = $this->make($attributes);
        $user->save();
        return $user;
    }
    
    /**
     * Create an admin user
     */
    public function admin(): self
    {
        return $this->state(['user_level' => 1]);
    }
    
    /**
     * Create a moderator user
     */
    public function moderator(): self
    {
        return $this->state(['user_level' => 2]);
    }
    
    /**
     * Apply state to the factory
     */
    public function state(array $state): self
    {
        $clone = clone $this;
        $clone->states[] = $state;
        return $clone;
    }
    
    private array $states = [];
}