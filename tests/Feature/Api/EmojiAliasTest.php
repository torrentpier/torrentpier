<?php

use App\Models\Emoji;
use App\Models\EmojiAlias;
use App\Models\EmojiCategory;

describe('Emoji Alias API Endpoints', function () {
    test('can list aliases', function () {
        $category = EmojiCategory::factory()->create();
        $emoji = Emoji::factory()->create(['emoji_category_id' => $category->id]);
        EmojiAlias::factory()->count(3)->create(['emoji_id' => $emoji->id]);

        $response = $this->getJson('/api/emoji/aliases');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'alias',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ])
            ->assertJsonCount(3, 'data');
    });

    test('can filter aliases by emoji', function () {
        $category = EmojiCategory::factory()->create();
        $emoji1 = Emoji::factory()->create(['emoji_category_id' => $category->id]);
        $emoji2 = Emoji::factory()->create(['emoji_category_id' => $category->id]);

        EmojiAlias::factory()->count(2)->create(['emoji_id' => $emoji1->id]);
        EmojiAlias::factory()->count(3)->create(['emoji_id' => $emoji2->id]);

        $response = $this->getJson("/api/emoji/aliases?emoji_id={$emoji1->id}");

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    });

    test('can search aliases', function () {
        $category = EmojiCategory::factory()->create();
        $emoji = Emoji::factory()->create(['emoji_category_id' => $category->id]);
        EmojiAlias::factory()->create([
            'emoji_id' => $emoji->id,
            'alias' => ':happy:',
        ]);
        EmojiAlias::factory()->create([
            'emoji_id' => $emoji->id,
            'alias' => ':sad:',
        ]);

        $response = $this->getJson('/api/emoji/aliases?search=happy');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.alias', ':happy:');
    });

    test('can include emoji and category with aliases', function () {
        $category = EmojiCategory::factory()->create(['title' => 'Test Category']);
        $emoji = Emoji::factory()->create([
            'emoji_category_id' => $category->id,
            'title' => 'Test Emoji',
        ]);
        EmojiAlias::factory()->create(['emoji_id' => $emoji->id]);

        $response = $this->getJson('/api/emoji/aliases?with_emoji=1');

        $response->assertOk()
            ->assertJsonPath('data.0.emoji.title', 'Test Emoji')
            ->assertJsonPath('data.0.emoji.category.title', 'Test Category');
    });

    test('can get specific alias', function () {
        $category = EmojiCategory::factory()->create();
        $emoji = Emoji::factory()->create(['emoji_category_id' => $category->id]);
        $alias = EmojiAlias::factory()->create([
            'emoji_id' => $emoji->id,
            'alias' => ':test_alias:',
        ]);

        $response = $this->getJson("/api/emoji/aliases/{$alias->id}");

        $response->assertOk()
            ->assertJsonPath('data.alias', ':test_alias:')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'alias',
                    'emoji',
                    'created_at',
                    'updated_at',
                ],
            ]);
    });

    test('returns 404 for non-existent alias', function () {
        $response = $this->getJson('/api/emoji/aliases/999');

        $response->assertNotFound();
    });

    test('can create alias', function () {
        $category = EmojiCategory::factory()->create();
        $emoji = Emoji::factory()->create(['emoji_category_id' => $category->id]);

        $data = [
            'emoji_id' => $emoji->id,
            'alias' => ':new_alias:',
        ];

        $response = $this->postJson('/api/emoji/aliases', $data);

        $response->assertCreated()
            ->assertJsonPath('data.alias', ':new_alias:')
            ->assertJsonPath('data.emoji', null); // Not loaded by default

        $this->assertDatabaseHas('emoji_aliases', [
            'emoji_id' => $emoji->id,
            'alias' => ':new_alias:',
        ]);
    });

    test('validates required fields when creating alias', function () {
        $response = $this->postJson('/api/emoji/aliases', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['emoji_id', 'alias']);
    });

    test('validates alias format', function () {
        $category = EmojiCategory::factory()->create();
        $emoji = Emoji::factory()->create(['emoji_category_id' => $category->id]);

        $response = $this->postJson('/api/emoji/aliases', [
            'emoji_id' => $emoji->id,
            'alias' => 'invalid-format',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['alias']);
    });

    test('validates unique alias', function () {
        $category = EmojiCategory::factory()->create();
        $emoji = Emoji::factory()->create(['emoji_category_id' => $category->id]);
        EmojiAlias::factory()->create([
            'emoji_id' => $emoji->id,
            'alias' => ':existing:',
        ]);

        $response = $this->postJson('/api/emoji/aliases', [
            'emoji_id' => $emoji->id,
            'alias' => ':existing:',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['alias']);
    });

    test('validates emoji exists', function () {
        $response = $this->postJson('/api/emoji/aliases', [
            'emoji_id' => 999,
            'alias' => ':valid_alias:',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['emoji_id']);
    });

    test('prevents alias from conflicting with emoji shortcode', function () {
        $category = EmojiCategory::factory()->create();
        $emoji1 = Emoji::factory()->create([
            'emoji_category_id' => $category->id,
            'emoji_shortcode' => ':existing_emoji:',
        ]);
        $emoji2 = Emoji::factory()->create(['emoji_category_id' => $category->id]);

        $response = $this->postJson('/api/emoji/aliases', [
            'emoji_id' => $emoji2->id,
            'alias' => ':existing_emoji:',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['alias']);
    });

    test('can update alias', function () {
        $category = EmojiCategory::factory()->create();
        $emoji1 = Emoji::factory()->create(['emoji_category_id' => $category->id]);
        $emoji2 = Emoji::factory()->create(['emoji_category_id' => $category->id]);
        $alias = EmojiAlias::factory()->create(['emoji_id' => $emoji1->id]);

        $data = [
            'emoji_id' => $emoji2->id,
            'alias' => ':updated_alias:',
        ];

        $response = $this->patchJson("/api/emoji/aliases/{$alias->id}", $data);

        $response->assertOk()
            ->assertJsonPath('data.alias', ':updated_alias:');

        $this->assertDatabaseHas('emoji_aliases', [
            'id' => $alias->id,
            'emoji_id' => $emoji2->id,
            'alias' => ':updated_alias:',
        ]);
    });

    test('can partially update alias', function () {
        $category = EmojiCategory::factory()->create();
        $emoji = Emoji::factory()->create(['emoji_category_id' => $category->id]);
        $alias = EmojiAlias::factory()->create([
            'emoji_id' => $emoji->id,
            'alias' => ':original:',
        ]);

        $response = $this->patchJson("/api/emoji/aliases/{$alias->id}", [
            'alias' => ':updated:',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.alias', ':updated:');

        // emoji_id should remain unchanged
        $this->assertDatabaseHas('emoji_aliases', [
            'id' => $alias->id,
            'emoji_id' => $emoji->id,
            'alias' => ':updated:',
        ]);
    });

    test('can delete alias', function () {
        $category = EmojiCategory::factory()->create();
        $emoji = Emoji::factory()->create(['emoji_category_id' => $category->id]);
        $alias = EmojiAlias::factory()->create(['emoji_id' => $emoji->id]);

        $response = $this->deleteJson("/api/emoji/aliases/{$alias->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('emoji_aliases', ['id' => $alias->id]);

        // Emoji should still exist
        $this->assertDatabaseHas('emojis', ['id' => $emoji->id]);
    });

    test('supports pagination', function () {
        $category = EmojiCategory::factory()->create();
        $emoji = Emoji::factory()->create(['emoji_category_id' => $category->id]);
        EmojiAlias::factory()->count(60)->create(['emoji_id' => $emoji->id]);

        $response = $this->getJson('/api/emoji/aliases?per_page=20&page=2');

        $response->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonCount(20, 'data');
    });

    test('aliases are ordered alphabetically', function () {
        $category = EmojiCategory::factory()->create();
        $emoji = Emoji::factory()->create(['emoji_category_id' => $category->id]);

        EmojiAlias::factory()->create(['emoji_id' => $emoji->id, 'alias' => ':zebra:']);
        EmojiAlias::factory()->create(['emoji_id' => $emoji->id, 'alias' => ':apple:']);
        EmojiAlias::factory()->create(['emoji_id' => $emoji->id, 'alias' => ':banana:']);

        $response = $this->getJson('/api/emoji/aliases');

        $response->assertOk()
            ->assertJsonPath('data.0.alias', ':apple:')
            ->assertJsonPath('data.1.alias', ':banana:')
            ->assertJsonPath('data.2.alias', ':zebra:');
    });
});

describe('Emoji Alias Search API', function () {
    test('can search aliases using scout', function () {
        $category = EmojiCategory::factory()->create();
        $emoji = Emoji::factory()->create(['emoji_category_id' => $category->id]);

        EmojiAlias::factory()->create([
            'emoji_id' => $emoji->id,
            'alias' => ':happy:',
        ]);

        EmojiAlias::factory()->create([
            'emoji_id' => $emoji->id,
            'alias' => ':sad:',
        ]);

        $response = $this->getJson('/api/emoji/aliases/search?q=happy&with_emoji=1');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'alias',
                        'emoji',
                    ],
                ],
            ]);
    });

    test('validates search query parameter', function () {
        $response = $this->getJson('/api/emoji/aliases/search');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['q']);
    });

    test('validates search limit parameter', function () {
        $response = $this->getJson('/api/emoji/aliases/search?q=test&limit=150');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['limit']);
    });

    test('can include emoji data in search results', function () {
        $category = EmojiCategory::factory()->create();
        $emoji = Emoji::factory()->create(['emoji_category_id' => $category->id]);
        $alias = EmojiAlias::factory()->create(['emoji_id' => $emoji->id]);

        $response = $this->getJson('/api/emoji/aliases/search?q=test&with_emoji=1');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'emoji' => [
                            'id',
                            'title',
                            'emoji_shortcode',
                        ],
                    ],
                ],
            ]);
    });
});
