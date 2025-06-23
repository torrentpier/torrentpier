<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

/**
 * Send welcome email to newly registered users
 */
class SendWelcomeEmail implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the event
     */
    public function handle(UserRegistered $event): void
    {
        // TODO: Implement email sending logic
        // This is where you would queue an email to be sent
        // For now, just log the event
        
        if (function_exists('bb_log')) {
            bb_log(sprintf(
                'Welcome email queued for user: %s (ID: %d, Email: %s)',
                $event->getUsername(),
                $event->getUserId(),
                $event->getEmail()
            ));
        }
    }

    /**
     * Determine whether the listener should be queued
     */
    public function shouldQueue(UserRegistered $event): bool
    {
        return true;
    }
}