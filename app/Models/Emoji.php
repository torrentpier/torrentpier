<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Emoji extends Model
{
    /** @use HasFactory<\Database\Factories\EmojiFactory> */
    use HasFactory, Searchable;

    /**
     * The table associated with the model.
     *
     * Note: Explicitly set because Laravel's pluralization doesn't handle "emoji" correctly
     * (it's a Japanese loanword where singular and plural forms are the same).
     *
     * @var string
     */
    protected $table = 'emojis';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'emoji_text',
        'emoji_shortcode',
        'image_url',
        'sprite_mode',
        'sprite_params',
        'emoji_category_id',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sprite_mode' => 'boolean',
        'sprite_params' => 'array',
    ];

    /**
     * Get the category that owns the emoji.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<EmojiCategory>
     */
    public function category()
    {
        return $this->belongsTo(EmojiCategory::class, 'emoji_category_id');
    }

    /**
     * Get the aliases for the emoji.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<EmojiAlias>
     */
    public function aliases()
    {
        return $this->hasMany(EmojiAlias::class);
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
            'emoji_shortcode' => $this->emoji_shortcode,
            'emoji_text' => $this->emoji_text,
        ];
    }
}
