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
 * Forum Model
 *
 * @property int $forum_id
 * @property int $cat_id
 * @property string $forum_name
 * @property string $forum_desc
 * @property int $forum_status
 * @property int $forum_order
 * @property int $forum_posts
 * @property int $forum_topics
 * @property int $forum_last_post_id
 * @property int $forum_tpl_id
 * @property int $prune_days
 * @property int $auth_view
 * @property int $auth_read
 * @property int $auth_post
 * @property int $auth_reply
 * @property int $auth_edit
 * @property int $auth_delete
 * @property int $auth_sticky
 * @property int $auth_announce
 * @property int $auth_vote
 * @property int $auth_pollcreate
 * @property int $auth_attachments
 * @property int $auth_download
 * @property bool $allow_reg_tracker
 * @property bool $allow_porno_topic
 * @property bool $self_moderated
 * @property int $forum_parent
 * @property bool $show_on_index
 * @property bool $forum_display_sort
 * @property bool $forum_display_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Forum extends Model
{
    protected $primaryKey = 'forum_id';

    /**
     * The attributes that aren't mass assignable
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'forum_id',
        'forum_posts',
        'forum_topics',
        'forum_last_post_id',
    ];

    /**
     * Get the attributes that should be cast
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'forum_id' => 'integer',
            'cat_id' => 'integer',
            'forum_status' => 'integer',
            'forum_order' => 'integer',
            'forum_posts' => 'integer',
            'forum_topics' => 'integer',
            'forum_last_post_id' => 'integer',
            'forum_tpl_id' => 'integer',
            'prune_days' => 'integer',
            'forum_parent' => 'integer',
            'allow_reg_tracker' => 'boolean',
            'allow_porno_topic' => 'boolean',
            'self_moderated' => 'boolean',
            'show_on_index' => 'boolean',
            'forum_display_sort' => 'boolean',
            'forum_display_order' => 'boolean',
        ];
    }

    /**
     * Category this forum belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'cat_id', 'cat_id');
    }

    /**
     * Parent forum (for subforums)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'forum_parent', 'forum_id');
    }

    /**
     * Child forums (subforums)
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'forum_parent', 'forum_id')
            ->orderBy('forum_order');
    }

    /**
     * Topics in this forum
     */
    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class, 'forum_id', 'forum_id');
    }

    /**
     * Posts in this forum
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'forum_id', 'forum_id');
    }

    /**
     * Check if the forum is locked
     */
    public function isLocked(): bool
    {
        return $this->forum_status === FORUM_LOCKED;
    }

    /**
     * Check if the forum has subforums
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Check if this is a subforum
     */
    public function isSubforum(): bool
    {
        return $this->forum_parent > 0;
    }
}
