<?php

namespace Database\Factories;

use App\Models\EmojiCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Emoji>
 */
class EmojiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $emojis = ['😊', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃', '😉', '😇', '🥰', '😍', '🤩', '😘', '😗', '😚', '😙', '🥲', '😋', '😛', '😜', '🤪', '😝', '🤑', '🤗', '🤭', '🤫', '🤔'];
        $hasEmoji = $this->faker->boolean(80); // 80% chance of having Unicode emoji

        return [
            'title' => $this->faker->words(2, true),
            'emoji_text' => $hasEmoji ? $this->faker->randomElement($emojis) : null,
            'emoji_shortcode' => ':' . $this->faker->unique()->word() . ':',
            'image_url' => !$hasEmoji ? '/emojis/custom/' . $this->faker->unique()->word() . '.png' : null,
            'sprite_mode' => false,
            'sprite_params' => null,
            'emoji_category_id' => EmojiCategory::factory(),
            'display_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    /**
     * Indicate that the emoji uses sprite mode.
     */
    public function withSprite(): static
    {
        return $this->state(fn (array $attributes) => [
            'sprite_mode' => true,
            'sprite_params' => [
                'x' => $this->faker->numberBetween(0, 500),
                'y' => $this->faker->numberBetween(0, 500),
                'width' => 32,
                'height' => 32,
                'sheet' => 'emoji-sheet-' . $this->faker->numberBetween(1, 5) . '.png',
            ],
            'image_url' => null,
            'emoji_text' => null,
        ]);
    }

    /**
     * Indicate that the emoji is a custom image.
     */
    public function customImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'emoji_text' => null,
            'image_url' => '/emojis/custom/' . $this->faker->unique()->word() . '.png',
        ]);
    }
}
