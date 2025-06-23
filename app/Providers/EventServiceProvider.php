<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\UserRegistered;
use App\Events\TorrentUploaded;
use App\Listeners\SendWelcomeEmail;
use App\Listeners\UpdateUserStatistics;
use Illuminate\Support\ServiceProvider;

/**
 * Event Service Provider
 * 
 * Register event listeners and subscribers
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application
     */
    protected array $listen = [
        UserRegistered::class => [
            SendWelcomeEmail::class,
        ],
        TorrentUploaded::class => [
            UpdateUserStatistics::class,
        ],
    ];

    /**
     * The event subscriber classes to register
     */
    protected array $subscribe = [
        // Add event subscribers here
    ];

    /**
     * Register any events for your application
     */
    public function boot(): void
    {
        $events = $this->app->make('events');

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $listener);
            }
        }

        foreach ($this->subscribe as $subscriber) {
            $events->subscribe($subscriber);
        }
    }

    /**
     * Determine if events and listeners should be automatically discovered
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}