<?php

use App\Models\Emoji;
use App\Models\EmojiAlias;
use App\Models\EmojiCategory;

describe('Emoji API Endpoints', function () {
    test('can list emojis', function () {
        $category = EmojiCategory::factory()->create();
        $emojis = Emoji::factory()->count(3)->create(['emoji_category_id' => $category->id]);

        $response = $this->getJson('/api/emoji/emojis');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'emoji_text',
                        'emoji_shortcode',
                        'image_url',
                        'sprite_mode',
                        'sprite_params',
                        'display_order',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ])
            ->assertJsonCount(3, 'data');
    });

    test('can filter emojis by category', function () {
        $category1 = EmojiCategory::factory()->create();
        $category2 = EmojiCategory::factory()->create();

        Emoji::factory()->count(2)->create(['emoji_category_id' => $category1->id]);
        Emoji::factory()->count(3)->create(['emoji_category_id' => $category2->id]);

        $response = $this->getJson("/api/emoji/emojis?category_id={$category1->id}");

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    });

    test('can search emojis', function () {
        $category = EmojiCategory::factory()->create();
        Emoji::factory()->create([
            'title' => 'Happy Face',
            'emoji_shortcode' => ':happy:',
            'emoji_category_id' => $category->id,
        ]);
        Emoji::factory()->create([
            'title' => 'Sad Face',
            'emoji_shortcode' => ':sad:',
            'emoji_category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/emoji/emojis?search=happy');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Happy Face');
    });

    test('can include category and aliases with emojis', function () {
        $category = EmojiCategory::factory()->create(['title' => 'Test Category']);
        $emoji = Emoji::factory()->create(['emoji_category_id' => $category->id]);
        EmojiAlias::factory()->count(2)->create(['emoji_id' => $emoji->id]);

        $response = $this->getJson('/api/emoji/emojis?with_category=1&with_aliases=1');

        $response->assertOk()
            ->assertJsonPath('data.0.category.title', 'Test Category')
            ->assertJsonCount(2, 'data.0.aliases');
    });

    test('can get specific emoji', function () {
        $category = EmojiCategory::factory()->create();
        $emoji = Emoji::factory()->create([
            'title' => 'Test Emoji',
            'emoji_shortcode' => ':test:',
            'emoji_category_id' => $category->id,
        ]);

        $response = $this->getJson("/api/emoji/emojis/{$emoji->id}");

        $response->assertOk()
            ->assertJsonPath('data.title', 'Test Emoji')
            ->assertJsonPath('data.emoji_shortcode', ':test:')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'emoji_shortcode',
                    'category',
                    'aliases',
                ],
            ]);
    });

    test('returns 404 for non-existent emoji', function () {
        $response = $this->getJson('/api/emoji/emojis/999');

        $response->assertNotFound();
    });

    test('can create unicode emoji', function () {
        $category = EmojiCategory::factory()->create();

        $data = [
            'title' => 'Happy Face',
            'emoji_text' => '😊',
            'emoji_shortcode' => ':happy_face:',
            'emoji_category_id' => $category->id,
            'display_order' => 1,
        ];

        $response = $this->postJson('/api/emoji/emojis', $data);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Happy Face')
            ->assertJsonPath('data.emoji_text', '😊')
            ->assertJsonPath('data.emoji_shortcode', ':happy_face:');

        $this->assertDatabaseHas('emojis', [
            'title' => 'Happy Face',
            'emoji_text' => '😊',
            'emoji_shortcode' => ':happy_face:',
        ]);
    });

    test('can create custom image emoji', function () {
        $category = EmojiCategory::factory()->create();

        $data = [
            'title' => 'Custom Emoji',
            'emoji_shortcode' => ':custom:',
            'image_url' => '/emojis/custom/custom.png',
            'emoji_category_id' => $category->id,
            'display_order' => 1,
        ];

        $response = $this->postJson('/api/emoji/emojis', $data);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Custom Emoji')
            ->assertJsonPath('data.image_url', '/emojis/custom/custom.png')
            ->assertJsonPath('data.emoji_text', null);
    });

    test('can create sprite emoji', function () {
        $category = EmojiCategory::factory()->create();

        $data = [
            'title' => 'Sprite Emoji',
            'emoji_shortcode' => ':sprite:',
            'sprite_mode' => true,
            'sprite_params' => [
                'x' => 32,
                'y' => 64,
                'width' => 32,
                'height' => 32,
                'sheet' => 'emoji-sheet-1.png',
            ],
            'emoji_category_id' => $category->id,
            'display_order' => 1,
        ];

        $response = $this->postJson('/api/emoji/emojis', $data);

        $response->assertCreated()
            ->assertJsonPath('data.sprite_mode', true)
            ->assertJsonPath('data.sprite_params.x', 32)
            ->assertJsonPath('data.sprite_params.sheet', 'emoji-sheet-1.png');
    });

    test('validates required fields when creating emoji', function () {
        $response = $this->postJson('/api/emoji/emojis', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'emoji_shortcode', 'display_order']);
    });

    test('validates emoji shortcode format', function () {
        $category = EmojiCategory::factory()->create();

        $response = $this->postJson('/api/emoji/emojis', [
            'title' => 'Test',
            'emoji_shortcode' => 'invalid-format',
            'emoji_category_id' => $category->id,
            'display_order' => 1,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['emoji_shortcode']);
    });

    test('validates unique emoji shortcode', function () {
        $category = EmojiCategory::factory()->create();
        Emoji::factory()->create(['emoji_shortcode' => ':existing:']);

        $response = $this->postJson('/api/emoji/emojis', [
            'title' => 'Test',
            'emoji_shortcode' => ':existing:',
            'emoji_category_id' => $category->id,
            'display_order' => 1,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['emoji_shortcode']);
    });

    test('validates sprite params when sprite mode is enabled', function () {
        $category = EmojiCategory::factory()->create();

        $response = $this->postJson('/api/emoji/emojis', [
            'title' => 'Test',
            'emoji_shortcode' => ':test:',
            'sprite_mode' => true,
            'emoji_category_id' => $category->id,
            'display_order' => 1,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'sprite_params.x',
                'sprite_params.y',
                'sprite_params.width',
                'sprite_params.height',
                'sprite_params.sheet',
            ]);
    });

    test('can update emoji', function () {
        $category = EmojiCategory::factory()->create();
        $emoji = Emoji::factory()->create(['emoji_category_id' => $category->id]);

        $data = [
            'title' => 'Updated Title',
            'display_order' => 99,
        ];

        $response = $this->patchJson("/api/emoji/emojis/{$emoji->id}", $data);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.display_order', 99);

        $this->assertDatabaseHas('emojis', [
            'id' => $emoji->id,
            'title' => 'Updated Title',
            'display_order' => 99,
        ]);
    });

    test('can delete emoji', function () {
        $category = EmojiCategory::factory()->create();
        $emoji = Emoji::factory()->create(['emoji_category_id' => $category->id]);
        $alias = EmojiAlias::factory()->create(['emoji_id' => $emoji->id]);

        $response = $this->deleteJson("/api/emoji/emojis/{$emoji->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('emojis', ['id' => $emoji->id]);
        $this->assertDatabaseMissing('emoji_aliases', ['id' => $alias->id]);
    });

    test('supports pagination', function () {
        $category = EmojiCategory::factory()->create();
        Emoji::factory()->count(60)->create(['emoji_category_id' => $category->id]);

        $response = $this->getJson('/api/emoji/emojis?per_page=20&page=2');

        $response->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonCount(20, 'data');
    });
});

describe('Emoji Search API', function () {
    test('can search emojis using scout', function () {
        $category = EmojiCategory::factory()->create();

        // Create emojis with different content
        Emoji::factory()->create([
            'title' => 'Happy Face',
            'emoji_shortcode' => ':happy:',
            'emoji_text' => '😊',
            'emoji_category_id' => $category->id,
        ]);

        Emoji::factory()->create([
            'title' => 'Sad Face',
            'emoji_shortcode' => ':sad:',
            'emoji_text' => '😢',
            'emoji_category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/emoji/emojis/search?q=happy');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'emoji_shortcode',
                        'emoji_text',
                    ],
                ],
            ]);
    });

    test('validates search query parameter', function () {
        $response = $this->getJson('/api/emoji/emojis/search');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['q']);
    });

    test('validates search limit parameter', function () {
        $response = $this->getJson('/api/emoji/emojis/search?q=test&limit=150');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['limit']);
    });
});
