<?php

namespace Database\Seeders;

use App\Models\Emoji;
use App\Models\EmojiAlias;
use App\Models\EmojiCategory;
use Illuminate\Database\Seeder;

class EmojiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create categories
        $categories = [
            'Smileys' => ['title' => 'Smileys', 'display_order' => 1],
            'People' => ['title' => 'People', 'display_order' => 2],
            'Nature' => ['title' => 'Nature', 'display_order' => 3],
            'Food' => ['title' => 'Food', 'display_order' => 4],
            'Objects' => ['title' => 'Objects', 'display_order' => 5],
        ];

        $categoryModels = [];
        foreach ($categories as $key => $data) {
            $categoryModels[$key] = EmojiCategory::create($data);
        }

        // Define emojis with their categories and aliases
        $emojis = [
            // Smileys
            [
                'category' => 'Smileys',
                'emojis' => [
                    ['title' => 'Grinning', 'emoji_text' => '😀', 'emoji_shortcode' => ':grinning:', 'aliases' => [':grin:']],
                    ['title' => 'Smile', 'emoji_text' => '😊', 'emoji_shortcode' => ':smile:', 'aliases' => [':blush:', ':happy:']],
                    ['title' => 'Laughing', 'emoji_text' => '😂', 'emoji_shortcode' => ':joy:', 'aliases' => [':laughing:', ':lol:']],
                    ['title' => 'Wink', 'emoji_text' => '😉', 'emoji_shortcode' => ':wink:', 'aliases' => [';)', ':winking:']],
                    ['title' => 'Heart Eyes', 'emoji_text' => '😍', 'emoji_shortcode' => ':heart_eyes:', 'aliases' => [':love:']],
                    ['title' => 'Thinking', 'emoji_text' => '🤔', 'emoji_shortcode' => ':thinking:', 'aliases' => [':think:', ':hmm:']],
                    ['title' => 'Sad', 'emoji_text' => '😢', 'emoji_shortcode' => ':cry:', 'aliases' => [':sad:', ':tear:']],
                    ['title' => 'Angry', 'emoji_text' => '😠', 'emoji_shortcode' => ':angry:', 'aliases' => [':mad:', ':rage:']],
                    ['title' => 'Thumbs Up', 'emoji_text' => '👍', 'emoji_shortcode' => ':thumbsup:', 'aliases' => [':+1:', ':like:']],
                    ['title' => 'Thumbs Down', 'emoji_text' => '👎', 'emoji_shortcode' => ':thumbsdown:', 'aliases' => [':-1:', ':dislike:']],
                ],
            ],
            // People
            [
                'category' => 'People',
                'emojis' => [
                    ['title' => 'Clap', 'emoji_text' => '👏', 'emoji_shortcode' => ':clap:', 'aliases' => [':applause:']],
                    ['title' => 'Wave', 'emoji_text' => '👋', 'emoji_shortcode' => ':wave:', 'aliases' => [':hello:', ':bye:']],
                    ['title' => 'OK Hand', 'emoji_text' => '👌', 'emoji_shortcode' => ':ok_hand:', 'aliases' => [':ok:', ':perfect:']],
                    ['title' => 'Victory', 'emoji_text' => '✌️', 'emoji_shortcode' => ':v:', 'aliases' => [':victory:', ':peace:']],
                    ['title' => 'Muscle', 'emoji_text' => '💪', 'emoji_shortcode' => ':muscle:', 'aliases' => [':strong:', ':flex:']],
                ],
            ],
            // Nature
            [
                'category' => 'Nature',
                'emojis' => [
                    ['title' => 'Sun', 'emoji_text' => '☀️', 'emoji_shortcode' => ':sunny:', 'aliases' => [':sun:']],
                    ['title' => 'Fire', 'emoji_text' => '🔥', 'emoji_shortcode' => ':fire:', 'aliases' => [':flame:', ':hot:']],
                    ['title' => 'Star', 'emoji_text' => '⭐', 'emoji_shortcode' => ':star:', 'aliases' => []],
                    ['title' => 'Rainbow', 'emoji_text' => '🌈', 'emoji_shortcode' => ':rainbow:', 'aliases' => []],
                    ['title' => 'Plant', 'emoji_text' => '🌱', 'emoji_shortcode' => ':seedling:', 'aliases' => [':plant:', ':sprout:']],
                ],
            ],
            // Food
            [
                'category' => 'Food',
                'emojis' => [
                    ['title' => 'Pizza', 'emoji_text' => '🍕', 'emoji_shortcode' => ':pizza:', 'aliases' => []],
                    ['title' => 'Coffee', 'emoji_text' => '☕', 'emoji_shortcode' => ':coffee:', 'aliases' => [':cafe:']],
                    ['title' => 'Beer', 'emoji_text' => '🍺', 'emoji_shortcode' => ':beer:', 'aliases' => [':beers:']],
                    ['title' => 'Cake', 'emoji_text' => '🎂', 'emoji_shortcode' => ':birthday:', 'aliases' => [':cake:']],
                    ['title' => 'Apple', 'emoji_text' => '🍎', 'emoji_shortcode' => ':apple:', 'aliases' => []],
                ],
            ],
            // Objects
            [
                'category' => 'Objects',
                'emojis' => [
                    ['title' => 'Heart', 'emoji_text' => '❤️', 'emoji_shortcode' => ':heart:', 'aliases' => [':love_heart:', ':red_heart:']],
                    ['title' => 'Broken Heart', 'emoji_text' => '💔', 'emoji_shortcode' => ':broken_heart:', 'aliases' => []],
                    ['title' => 'Alarm Clock', 'emoji_text' => '⏰', 'emoji_shortcode' => ':alarm_clock:', 'aliases' => [':alarm:']],
                    ['title' => 'Check Mark', 'emoji_text' => '✅', 'emoji_shortcode' => ':white_check_mark:', 'aliases' => [':check:', ':done:']],
                    ['title' => 'X Mark', 'emoji_text' => '❌', 'emoji_shortcode' => ':x:', 'aliases' => [':cross:', ':no:']],
                ],
            ],
        ];

        // Insert emojis
        foreach ($emojis as $categoryData) {
            $category = $categoryModels[$categoryData['category']];
            $displayOrder = 0;

            foreach ($categoryData['emojis'] as $emojiData) {
                $displayOrder++;

                $emoji = Emoji::create([
                    'title' => $emojiData['title'],
                    'emoji_text' => $emojiData['emoji_text'],
                    'emoji_shortcode' => $emojiData['emoji_shortcode'],
                    'image_url' => null,
                    'sprite_mode' => false,
                    'sprite_params' => null,
                    'emoji_category_id' => $category->id,
                    'display_order' => $displayOrder,
                ]);

                // Create aliases
                foreach ($emojiData['aliases'] as $alias) {
                    EmojiAlias::create([
                        'emoji_id' => $emoji->id,
                        'alias' => $alias,
                    ]);
                }
            }
        }
    }
}
