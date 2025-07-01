<?php

namespace Database\Factories;

use App\Models\Emoji;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmojiAlias>
 */
class EmojiAliasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'emoji_id' => Emoji::factory(),
            'alias' => ':' . $this->faker->unique()->word() . ':',
        ];
    }
}
