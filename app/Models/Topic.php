<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Topic Model
 *
 * @property int $topic_id
 * @property int $forum_id
 * @property string $topic_title
 * @property int $topic_poster
 * @property int $topic_time
 * @property int $topic_views
 * @property int $topic_replies
 * @property int $topic_status
 * @property bool $topic_vote
 * @property int $topic_type
 * @property int $topic_first_post_id
 * @property int $topic_last_post_id
 * @property int $topic_moved_id
 * @property bool $tracker_status
 * @property int $attach_ext_id
 * @property int $download_count
 * @property int $attach_filesize
 * @property bool $topic_dl_type
 * @property int $topic_last_post_time
 * @property int $topic_show_first_post
 * @property int $topic_allow_robots
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Topic extends Model
{
    protected $primaryKey = 'topic_id';

    /**
     * The attributes that aren't mass assignable
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'topic_id',
        'topic_views',
        'topic_replies',
    ];

    /**
     * Get the attributes that should be cast
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'topic_id' => 'integer',
            'forum_id' => 'integer',
            'topic_poster' => 'integer',
            'topic_time' => 'integer',
            'topic_views' => 'integer',
            'topic_replies' => 'integer',
            'topic_status' => 'integer',
            'topic_vote' => 'boolean',
            'topic_type' => 'integer',
            'topic_first_post_id' => 'integer',
            'topic_last_post_id' => 'integer',
            'topic_moved_id' => 'integer',
            'tracker_status' => 'boolean',
            'attach_ext_id' => 'integer',
            'download_count' => 'integer',
            'attach_filesize' => 'integer',
            'topic_dl_type' => 'boolean',
            'topic_last_post_time' => 'integer',
            'topic_show_first_post' => 'integer',
            'topic_allow_robots' => 'integer',
        ];
    }

    /**
     * Forum this topic belongs to
     */
    public function forum(): BelongsTo
    {
        return $this->belongsTo(Forum::class, 'forum_id', 'forum_id');
    }

    /**
     * User who created this topic
     */
    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'topic_poster', 'user_id');
    }

    /**
     * First post of this topic
     */
    public function firstPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'topic_first_post_id', 'post_id');
    }

    /**
     * Last post of this topic
     */
    public function lastPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'topic_last_post_id', 'post_id');
    }

    /**
     * All posts on this topic
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'topic_id', 'topic_id');
    }

    /**
     * Check if a topic is locked
     */
    public function isLocked(): bool
    {
        return $this->topic_status === TOPIC_LOCKED;
    }

    /**
     * Check if a topic is sticky
     */
    public function isSticky(): bool
    {
        return $this->topic_type === POST_STICKY;
    }

    /**
     * Check if a topic is an announcement
     */
    public function isAnnouncement(): bool
    {
        return $this->topic_type === POST_ANNOUNCE;
    }

    /**
     * Check if a topic has a poll
     */
    public function hasPoll(): bool
    {
        return $this->topic_vote;
    }

    /**
     * Check if a topic has a torrent attached
     */
    public function hasTorrent(): bool
    {
        return $this->tracker_status;
    }

    /**
     * Check if a topic was moved
     */
    public function isMoved(): bool
    {
        return $this->topic_moved_id > 0;
    }
}
