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
 * Category Model
 *
 * @property int $cat_id
 * @property string $cat_title
 * @property int $cat_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Category extends Model
{
    protected $primaryKey = 'cat_id';

    /**
     * The attributes that aren't mass assignable
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'cat_id',
    ];

    /**
     * Get the attributes that should be cast
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cat_id' => 'integer',
            'cat_order' => 'integer',
        ];
    }

    /**
     * Forums in this category
     */
    public function forums(): HasMany
    {
        return $this->hasMany(Forum::class, 'cat_id', 'cat_id')
            ->orderBy('forum_order');
    }
}
