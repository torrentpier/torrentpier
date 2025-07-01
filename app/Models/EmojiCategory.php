<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmojiCategory extends Model
{
    /** @use HasFactory<\Database\Factories\EmojiCategoryFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'display_order',
    ];

    /**
     * Get the emojis for the category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Emoji>
     */
    public function emojis()
    {
        return $this->hasMany(Emoji::class);
    }
}
