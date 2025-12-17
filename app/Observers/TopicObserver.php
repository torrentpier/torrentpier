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

use App\Models\Topic;
use TorrentPier\ManticoreSearch;

/**
 * Topic Observer
 *
 * Synchronizes topic changes with ManticoreSearch index.
 * Automatically triggered on Eloquent model events (created, updated, deleted).
 */
class TopicObserver
{
    /**
     * Create a new observer instance
     */
    public function __construct(
        protected ManticoreSearch $manticore,
    ) {}

    public function created(Topic $topic): void
    {
        $this->manticore->upsertTopic(
            topic_id: $topic->topic_id,
            topic_title: $topic->topic_title,
            forum_id: $topic->forum_id,
        );
    }

    public function updated(Topic $topic): void
    {
        if (!$topic->wasChanged(['topic_title', 'forum_id'])) {
            return;
        }

        $this->manticore->upsertTopic(
            topic_id: $topic->topic_id,
            topic_title: $topic->wasChanged('topic_title') ? $topic->topic_title : null,
            forum_id: $topic->wasChanged('forum_id') ? $topic->forum_id : null,
        );
    }

    public function deleted(Topic $topic): void
    {
        $this->manticore->deleteTopic($topic->topic_id);
    }
}
