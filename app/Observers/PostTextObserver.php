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

use App\Models\PostText;
use TorrentPier\ManticoreSearch;

/**
 * PostText Observer
 *
 * Synchronizes post text content changes with ManticoreSearch index.
 * This handles updates to the post content separately from post metadata.
 */
class PostTextObserver
{
    /**
     * Create a new observer instance
     */
    public function __construct(
        protected ManticoreSearch $manticore,
    ) {}

    public function created(PostText $postText): void
    {
        $postText->loadMissing(['post.topic']);

        $this->manticore->upsertPost(
            post_id: $postText->post_id,
            post_text: $postText->post_text,
            topic_title: $postText->post?->topic?->topic_title,
            topic_id: $postText->post?->topic_id,
            forum_id: $postText->post?->forum_id,
        );
    }

    public function updated(PostText $postText): void
    {
        if (!$postText->wasChanged('post_text')) {
            return;
        }

        $postText->loadMissing(['post.topic']);

        $this->manticore->upsertPost(
            post_id: $postText->post_id,
            post_text: $postText->post_text,
            topic_title: $postText->post?->topic?->topic_title,
            topic_id: $postText->post?->topic_id,
            forum_id: $postText->post?->forum_id,
        );
    }
}
