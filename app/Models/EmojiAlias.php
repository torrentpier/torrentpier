<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class EmojiAlias extends Model
{
    /** @use HasFactory<\Database\Factories\EmojiAliasFactory> */
    use HasFactory, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'emoji_id',
        'alias',
    ];

    /**
     * Get the emoji that owns the alias.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Emoji>
     */
    public function emoji()
    {
        return $this->belongsTo(Emoji::class);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'alias' => $this->alias,
            'emoji_id' => $this->emoji_id,
        ];
    }
}
