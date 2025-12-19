<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use TorrentPier\ManticoreSearch;

/**
 * User Observer
 *
 * Synchronizes user changes with ManticoreSearch index.
 * Only username is indexed for user search functionality.
 */
class UserObserver
{
    /**
     * Create a new observer instance
     */
    public function __construct(
        protected ManticoreSearch $manticore,
    ) {}

    public function created(User $user): void
    {
        if ($user->isSystemUser()) {
            return;
        }

        $this->manticore->upsertUser(
            user_id: $user->user_id,
            username: $user->username,
        );
    }

    public function updated(User $user): void
    {
        if ($user->isSystemUser() || !$user->wasChanged('username')) {
            return;
        }

        $this->manticore->upsertUser(
            user_id: $user->user_id,
            username: $user->username,
        );
    }

    public function deleted(User $user): void
    {
        if ($user->isSystemUser()) {
            return;
        }

        $this->manticore->deleteUser($user->user_id);
    }
}
