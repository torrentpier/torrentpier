<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TorrentUploaded;

/**
 * Update user statistics when a torrent is uploaded
 */
class UpdateUserStatistics
{
    /**
     * Handle the event
     */
    public function handle(TorrentUploaded $event): void
    {
        // TODO: Implement statistics update logic
        // This would typically update the user's upload count, ratio, etc.
        
        if (function_exists('bb_log')) {
            bb_log(sprintf(
                'User statistics update triggered for user %d after uploading torrent: %s (ID: %d, Size: %d bytes)',
                $event->getUploaderId(),
                $event->getTorrentName(),
                $event->getTorrentId(),
                $event->getSize()
            ));
        }
    }
}