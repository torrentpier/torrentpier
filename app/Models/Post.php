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
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Post Model
 *
 * @property int $post_id
 * @property int $topic_id
 * @property int $forum_id
 * @property int $poster_id
 * @property int $post_time
 * @property string $poster_ip
 * @property int $poster_rg_id
 * @property int $attach_rg_sig
 * @property string $post_username
 * @property int $post_edit_time
 * @property int $post_edit_count
 * @property bool $user_post
 * @property string $mc_comment
 * @property bool $mc_type
 * @property int $mc_user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Post extends Model
{
    protected $primaryKey = 'post_id';

    /**
     * The attributes that aren't mass assignable
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'post_id',
    ];

    /**
     * Get the attributes that should be cast
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'post_id' => 'integer',
            'topic_id' => 'integer',
            'forum_id' => 'integer',
            'poster_id' => 'integer',
            'post_time' => 'integer',
            'poster_rg_id' => 'integer',
            'attach_rg_sig' => 'integer',
            'post_edit_time' => 'integer',
            'post_edit_count' => 'integer',
            'user_post' => 'boolean',
            'mc_type' => 'boolean',
            'mc_user_id' => 'integer',
        ];
    }

    /**
     * Topic this post belongs to
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topic_id', 'topic_id');
    }

    /**
     * Forum this post belongs to
     */
    public function forum(): BelongsTo
    {
        return $this->belongsTo(Forum::class, 'forum_id', 'forum_id');
    }

    /**
     * User who created this post
     */
    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'poster_id', 'user_id');
    }

    /**
     * Post text content (stored in a separate table)
     */
    public function text(): HasOne
    {
        return $this->hasOne(PostText::class, 'post_id', 'post_id');
    }

    /**
     * Get full post text content
     */
    public function getTextContent(): ?string
    {
        return $this->text?->post_text;
    }

    /**
     * Check if the post was edited
     */
    public function isEdited(): bool
    {
        return $this->post_edit_count > 0;
    }

    /**
     * Check if post has moderator comment
     */
    public function hasModeratorComment(): bool
    {
        return !empty($this->mc_comment);
    }
}
