<?php

declare(strict_types=1);

namespace App\Events;

use DateTimeInterface;

/**
 * Event fired when a new torrent is uploaded
 */
readonly class TorrentUploaded
{
    /**
     * Create a new event instance
     */
    public function __construct(
        public int               $torrentId,
        public int               $uploaderId,
        public string            $torrentName,
        public int               $size,
        public DateTimeInterface $uploadedAt
    )
    {
    }

    /**
     * Get the torrent ID
     */
    public function getTorrentId(): int
    {
        return $this->torrentId;
    }

    /**
     * Get the uploader user ID
     */
    public function getUploaderId(): int
    {
        return $this->uploaderId;
    }

    /**
     * Get the torrent name
     */
    public function getTorrentName(): string
    {
        return $this->torrentName;
    }

    /**
     * Get the torrent size in bytes
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Get the upload timestamp
     */
    public function getUploadedAt(): DateTimeInterface
    {
        return $this->uploadedAt;
    }
}
