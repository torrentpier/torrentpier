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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * User Model
 *
 * @property int $user_id
 * @property bool $user_active
 * @property string $username
 * @property string $user_password
 * @property int $user_session_time
 * @property int $user_lastvisit
 * @property string $user_last_ip
 * @property int $user_regdate
 * @property string $user_reg_ip
 * @property int $user_level
 * @property int $user_posts
 * @property float $user_timezone
 * @property string $user_lang
 * @property int $user_new_privmsg
 * @property int $user_unread_privmsg
 * @property int $user_last_privmsg
 * @property int $user_opt
 * @property int $user_rank
 * @property int $avatar_ext_id
 * @property bool $user_gender
 * @property Carbon|null $user_birthday
 * @property string $user_email
 * @property string $user_twitter
 * @property string $user_website
 * @property string $user_from
 * @property string $user_sig
 * @property string $user_occ
 * @property string $user_interests
 * @property string $user_actkey
 * @property string $user_newpasswd
 * @property string $autologin_id
 * @property int $user_newest_pm_id
 * @property float $user_points
 * @property string $tpl_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class User extends Model
{
    protected $primaryKey = 'user_id';

    /**
     * The attributes that aren't mass assignable
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'user_id',
        'user_session_time',
        'user_lastvisit',
        'user_last_ip',
        'user_regdate',
        'user_reg_ip',
        'user_posts',
        'user_points',
        'user_new_privmsg',
        'user_unread_privmsg',
        'user_last_privmsg',
        'user_newest_pm_id',
        'user_opt',
        'user_rank',
        'avatar_ext_id',
    ];

    /**
     * The attributes that should be hidden for serialization
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'user_password',
        'user_actkey',
        'user_newpasswd',
        'autologin_id',
    ];

    /**
     * Get the attributes that should be cast
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'user_active' => 'boolean',
            'user_session_time' => 'integer',
            'user_lastvisit' => 'integer',
            'user_regdate' => 'integer',
            'user_level' => 'integer',
            'user_posts' => 'integer',
            'user_timezone' => 'decimal:2',
            'user_new_privmsg' => 'integer',
            'user_unread_privmsg' => 'integer',
            'user_opt' => 'integer',
            'user_rank' => 'integer',
            'user_gender' => 'boolean',
            'user_birthday' => 'date',
            'user_points' => 'decimal:2',
        ];
    }

    /**
     * Topics created by this user
     */
    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class, 'topic_poster', 'user_id');
    }

    /**
     * Posts created by this user
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'poster_id', 'user_id');
    }

    /**
     * Check if the user is admin
     */
    public function isAdmin(): bool
    {
        return $this->user_level === ADMIN;
    }

    /**
     * Check if the user is a moderator
     */
    public function isModerator(): bool
    {
        return $this->user_level === MOD;
    }

    /**
     * Check if the user is a guest
     */
    public function isGuest(): bool
    {
        return $this->user_id === GUEST_UID;
    }

    /**
     * Check if the user is active
     */
    public function isActive(): bool
    {
        return $this->user_active;
    }
}
