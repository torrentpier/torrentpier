<?php

declare(strict_types=1);

namespace Database\Seeders;

/**
 * Database Seeder
 * 
 * Run all seeders for the application
 */
class DatabaseSeeder
{
    /**
     * Seed the application's database
     */
    public function run(): void
    {
        // Call individual seeders here
        // $this->call(UserSeeder::class);
        // $this->call(ForumSeeder::class);
        // $this->call(TorrentSeeder::class);
    }
    
    /**
     * Call a seeder class
     */
    protected function call(string $seederClass): void
    {
        echo "Seeding: {$seederClass}\n";
        $seeder = new $seederClass();
        $seeder->run();
    }
}