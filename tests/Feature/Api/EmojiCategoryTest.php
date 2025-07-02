<?php

use App\Models\Emoji;
use App\Models\EmojiCategory;

describe('Emoji Category API Endpoints', function () {
    test('can list categories', function () {
        EmojiCategory::factory()->count(3)->create();

        $response = $this->getJson('/api/emoji/categories');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'display_order',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJsonCount(3, 'data');
    });

    test('can list categories with emoji counts', function () {
        // Ensure we start with clean slate
        EmojiCategory::query()->delete();
        Emoji::query()->delete();

        $category1 = EmojiCategory::factory()->create([
            'title' => 'Category 1',
            'display_order' => 1,
        ]);
        $category2 = EmojiCategory::factory()->create([
            'title' => 'Category 2',
            'display_order' => 2,
        ]);

        Emoji::factory()->count(5)->create(['emoji_category_id' => $category1->id]);
        Emoji::factory()->count(3)->create(['emoji_category_id' => $category2->id]);

        $response = $this->getJson('/api/emoji/categories?with_emojis=1');

        $response->assertOk()
            ->assertJsonPath('data.0.emojis_count', 5)
            ->assertJsonPath('data.1.emojis_count', 3);
    });

    test('can list categories with full emoji data', function () {
        $category = EmojiCategory::factory()->create();
        Emoji::factory()->count(2)->create(['emoji_category_id' => $category->id]);

        $response = $this->getJson('/api/emoji/categories?include_emojis=1');

        $response->assertOk()
            ->assertJsonCount(2, 'data.0.emojis')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'emojis' => [
                            '*' => [
                                'id',
                                'title',
                                'emoji_shortcode',
                                'emoji_text',
                            ],
                        ],
                    ],
                ],
            ]);
    });

    test('can get specific category', function () {
        $category = EmojiCategory::factory()->create([
            'title' => 'Test Category',
            'display_order' => 5,
        ]);

        $response = $this->getJson("/api/emoji/categories/{$category->id}");

        $response->assertOk()
            ->assertJsonPath('data.title', 'Test Category')
            ->assertJsonPath('data.display_order', 5)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'display_order',
                    'emojis',
                    'created_at',
                    'updated_at',
                ],
            ]);
    });

    test('can get category with emoji aliases', function () {
        $category = EmojiCategory::factory()->create();
        $emoji = Emoji::factory()->create(['emoji_category_id' => $category->id]);

        $response = $this->getJson("/api/emoji/categories/{$category->id}?with_aliases=1");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'emojis' => [
                        '*' => [
                            'aliases',
                        ],
                    ],
                ],
            ]);
    });

    test('returns 404 for non-existent category', function () {
        $response = $this->getJson('/api/emoji/categories/999');

        $response->assertNotFound();
    });

    test('can create category', function () {
        $data = [
            'title' => 'New Category',
            'display_order' => 10,
        ];

        $response = $this->postJson('/api/emoji/categories', $data);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'New Category')
            ->assertJsonPath('data.display_order', 10);

        $this->assertDatabaseHas('emoji_categories', [
            'title' => 'New Category',
            'display_order' => 10,
        ]);
    });

    test('validates required fields when creating category', function () {
        $response = $this->postJson('/api/emoji/categories', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'display_order']);
    });

    test('validates display order is non-negative', function () {
        $response = $this->postJson('/api/emoji/categories', [
            'title' => 'Test Category',
            'display_order' => -1,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['display_order']);
    });

    test('validates title length', function () {
        $response = $this->postJson('/api/emoji/categories', [
            'title' => str_repeat('a', 256),
            'display_order' => 1,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    });

    test('can update category', function () {
        $category = EmojiCategory::factory()->create();

        $data = [
            'title' => 'Updated Title',
            'display_order' => 99,
        ];

        $response = $this->patchJson("/api/emoji/categories/{$category->id}", $data);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.display_order', 99);

        $this->assertDatabaseHas('emoji_categories', [
            'id' => $category->id,
            'title' => 'Updated Title',
            'display_order' => 99,
        ]);
    });

    test('can partially update category', function () {
        $category = EmojiCategory::factory()->create([
            'title' => 'Original Title',
            'display_order' => 5,
        ]);

        $response = $this->patchJson("/api/emoji/categories/{$category->id}", [
            'title' => 'New Title',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'New Title')
            ->assertJsonPath('data.display_order', 5); // Should remain unchanged
    });

    test('can delete category', function () {
        $category = EmojiCategory::factory()->create();
        $emoji = Emoji::factory()->create(['emoji_category_id' => $category->id]);

        $response = $this->deleteJson("/api/emoji/categories/{$category->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('emoji_categories', ['id' => $category->id]);

        // Emoji should still exist but with null category_id
        $this->assertDatabaseHas('emojis', [
            'id' => $emoji->id,
            'emoji_category_id' => null,
        ]);
    });

    test('categories are ordered by display_order', function () {
        EmojiCategory::factory()->create(['title' => 'Third', 'display_order' => 3]);
        EmojiCategory::factory()->create(['title' => 'First', 'display_order' => 1]);
        EmojiCategory::factory()->create(['title' => 'Second', 'display_order' => 2]);

        $response = $this->getJson('/api/emoji/categories');

        $response->assertOk()
            ->assertJsonPath('data.0.title', 'First')
            ->assertJsonPath('data.1.title', 'Second')
            ->assertJsonPath('data.2.title', 'Third');
    });
});
