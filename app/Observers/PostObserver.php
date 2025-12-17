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

use App\Models\Post;
use TorrentPier\ManticoreSearch;

/**
 * Post Observer
 *
 * Synchronizes post-changes with ManticoreSearch index.
 * Note: Post text changes are handled by PostTextObserver.
 */
class PostObserver
{
    /**
     * Create a new observer instance
     */
    public function __construct(
        protected ManticoreSearch $manticore,
    ) {}

    public function created(Post $post): void
    {
        $post->loadMissing(['topic', 'text']);

        $this->manticore->upsertPost(
            post_id: $post->post_id,
            post_text: $post->text?->post_text,
            topic_title: $post->topic?->topic_title,
            topic_id: $post->topic_id,
            forum_id: $post->forum_id,
        );
    }

    public function updated(Post $post): void
    {
        if (!$post->wasChanged(['topic_id', 'forum_id'])) {
            return;
        }

        $post->loadMissing(['topic', 'text']);

        $this->manticore->upsertPost(
            post_id: $post->post_id,
            post_text: null, // text changes handled by PostTextObserver
            topic_title: $post->topic?->topic_title,
            topic_id: $post->wasChanged('topic_id') ? $post->topic_id : null,
            forum_id: $post->wasChanged('forum_id') ? $post->forum_id : null,
        );
    }

    public function deleted(Post $post): void
    {
        $this->manticore->deletePost($post->post_id);
    }
}
