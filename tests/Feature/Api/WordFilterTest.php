<?php

use App\Models\User;
use App\Models\WordFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Word Filter API', function () {
    describe('Index', function () {
        test('can list word filters', function () {
            WordFilter::factory()->count(3)->create();

            $response = $this->getJson('/api/word-filters');

            $response->assertOk()
                ->assertJsonCount(3, 'data')
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'pattern',
                            'replacement',
                            'filter_type',
                            'pattern_type',
                            'severity',
                            'is_active',
                            'case_sensitive',
                            'applies_to',
                            'notes',
                            'creator',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'links',
                    'meta',
                ]);
        });

        test('can filter by filter type', function () {
            WordFilter::factory()->create(['filter_type' => 'replace']);
            WordFilter::factory()->create(['filter_type' => 'block']);
            WordFilter::factory()->create(['filter_type' => 'moderate']);

            $response = $this->getJson('/api/word-filters?filter_type=block');

            $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.filter_type', 'block');
        });

        test('can filter by pattern type', function () {
            WordFilter::factory()->create(['pattern_type' => 'exact']);
            WordFilter::factory()->create(['pattern_type' => 'wildcard']);
            WordFilter::factory()->create(['pattern_type' => 'regex']);

            $response = $this->getJson('/api/word-filters?pattern_type=wildcard');

            $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.pattern_type', 'wildcard');
        });

        test('can filter by severity', function () {
            WordFilter::factory()->create(['severity' => 'low']);
            WordFilter::factory()->create(['severity' => 'medium']);
            WordFilter::factory()->create(['severity' => 'high']);

            $response = $this->getJson('/api/word-filters?severity=high');

            $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.severity', 'high');
        });

        test('can filter by active status', function () {
            WordFilter::factory()->create(['is_active' => true]);
            WordFilter::factory()->create(['is_active' => false]);

            $response = $this->getJson('/api/word-filters?is_active=0');

            $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.is_active', false);
        });

        test('can filter by applies to', function () {
            WordFilter::factory()->create(['applies_to' => ['posts', 'private_messages']]);
            WordFilter::factory()->create(['applies_to' => ['usernames']]);

            $response = $this->getJson('/api/word-filters?applies_to=usernames');

            $response->assertOk()
                ->assertJsonCount(1, 'data');
        });

        test('can search by pattern and notes', function () {
            WordFilter::factory()->create(['pattern' => 'badword', 'notes' => 'common profanity']);
            WordFilter::factory()->create(['pattern' => 'spam', 'notes' => 'commercial spam']);

            $response = $this->getJson('/api/word-filters?search=spam');

            $response->assertOk()
                ->assertJsonCount(1, 'data');
        });

        test('can include creator information', function () {
            $user = User::factory()->create();
            WordFilter::factory()->create(['created_by' => $user->id]);

            $response = $this->getJson('/api/word-filters?with_creator=1');

            $response->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'creator' => ['id', 'name', 'email'],
                        ],
                    ],
                ]);
        });

        test('can sort results', function () {
            WordFilter::factory()->create(['pattern' => 'aaa']);
            WordFilter::factory()->create(['pattern' => 'zzz']);

            $response = $this->getJson('/api/word-filters?sort_by=pattern&sort_order=asc');

            $response->assertOk()
                ->assertJsonPath('data.0.pattern', 'aaa')
                ->assertJsonPath('data.1.pattern', 'zzz');
        });
    });

    describe('Search', function () {
        test('can search word filters', function () {
            WordFilter::factory()->create(['pattern' => 'badword']);
            WordFilter::factory()->create(['pattern' => 'goodword']);

            $response = $this->getJson('/api/word-filters/search?q=bad');

            $response->assertOk()
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.pattern', 'badword');
        });

        test('search requires query parameter', function () {
            $response = $this->getJson('/api/word-filters/search');

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['q']);
        });

        test('can limit search results', function () {
            WordFilter::factory()->count(10)->create(['pattern' => 'test']);

            $response = $this->getJson('/api/word-filters/search?q=test&limit=5');

            $response->assertOk()
                ->assertJsonCount(5, 'data');
        });
    });

    describe('Show', function () {
        test('can get a single word filter', function () {
            $filter = WordFilter::factory()->create();

            $response = $this->getJson("/api/word-filters/{$filter->id}");

            $response->assertOk()
                ->assertJsonPath('data.id', $filter->id)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'pattern',
                        'replacement',
                        'filter_type',
                        'pattern_type',
                        'severity',
                        'is_active',
                        'case_sensitive',
                        'applies_to',
                        'notes',
                        'creator',
                        'created_at',
                        'updated_at',
                    ],
                ]);
        });

        test('returns 404 for non-existent filter', function () {
            $response = $this->getJson('/api/word-filters/999');

            $response->assertNotFound();
        });
    });

    describe('Store', function () {
        test('can create a replace filter', function () {
            $data = [
                'pattern' => 'badword',
                'replacement' => '******',
                'filter_type' => 'replace',
                'pattern_type' => 'exact',
                'severity' => 'high',
                'applies_to' => ['posts', 'private_messages'],
                'notes' => 'Common profanity',
            ];

            $response = $this->postJson('/api/word-filters', $data);

            $response->assertCreated()
                ->assertJsonPath('data.pattern', 'badword')
                ->assertJsonPath('data.replacement', '******');

            $this->assertDatabaseHas('word_filters', [
                'pattern' => 'badword',
                'filter_type' => 'replace',
            ]);
        });

        test('can create a block filter', function () {
            $data = [
                'pattern' => '*spam*',
                'filter_type' => 'block',
                'pattern_type' => 'wildcard',
                'severity' => 'medium',
                'applies_to' => ['posts'],
            ];

            $response = $this->postJson('/api/word-filters', $data);

            $response->assertCreated()
                ->assertJsonPath('data.filter_type', 'block')
                ->assertJsonPath('data.replacement', null);
        });

        test('can create a regex filter', function () {
            $data = [
                'pattern' => '/\\b\\d{3}-\\d{4}\\b/',
                'filter_type' => 'moderate',
                'pattern_type' => 'regex',
                'severity' => 'low',
                'applies_to' => ['posts', 'signatures'],
            ];

            $response = $this->postJson('/api/word-filters', $data);

            $response->assertCreated()
                ->assertJsonPath('data.pattern_type', 'regex');
        });

        test('validates required fields', function () {
            $response = $this->postJson('/api/word-filters', []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'pattern',
                    'filter_type',
                    'pattern_type',
                    'severity',
                    'applies_to',
                ]);
        });

        test('requires replacement for replace filter type', function () {
            $data = [
                'pattern' => 'test',
                'filter_type' => 'replace',
                'pattern_type' => 'exact',
                'severity' => 'low',
                'applies_to' => ['posts'],
            ];

            $response = $this->postJson('/api/word-filters', $data);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['replacement']);
        });

        test('validates regex patterns', function () {
            $data = [
                'pattern' => '[invalid regex',
                'filter_type' => 'moderate',
                'pattern_type' => 'regex',
                'severity' => 'low',
                'applies_to' => ['posts'],
            ];

            $response = $this->postJson('/api/word-filters', $data);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['pattern']);
        });

        test('validates enum values', function () {
            $data = [
                'pattern' => 'test',
                'replacement' => '****',
                'filter_type' => 'invalid',
                'pattern_type' => 'invalid',
                'severity' => 'invalid',
                'applies_to' => ['invalid'],
            ];

            $response = $this->postJson('/api/word-filters', $data);

            $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'filter_type',
                    'pattern_type',
                    'severity',
                    'applies_to.0',
                ]);
        });
    });

    describe('Update', function () {
        test('can update a word filter', function () {
            $filter = WordFilter::factory()->create([
                'pattern' => 'oldword',
                'pattern_type' => 'exact',
                'severity' => 'low',
            ]);

            $response = $this->patchJson("/api/word-filters/{$filter->id}", [
                'pattern' => 'newword',
                'severity' => 'high',
            ]);

            $response->assertOk()
                ->assertJsonPath('data.pattern', 'newword')
                ->assertJsonPath('data.severity', 'high');

            $this->assertDatabaseHas('word_filters', [
                'id' => $filter->id,
                'pattern' => 'newword',
                'severity' => 'high',
            ]);
        });

        test('changing to replace type requires replacement', function () {
            $filter = WordFilter::factory()->create([
                'filter_type' => 'block',
                'replacement' => null,
            ]);

            $response = $this->patchJson("/api/word-filters/{$filter->id}", [
                'filter_type' => 'replace',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['replacement']);
        });

        test('validates regex pattern on update', function () {
            $filter = WordFilter::factory()->create([
                'pattern_type' => 'exact',
            ]);

            $response = $this->patchJson("/api/word-filters/{$filter->id}", [
                'pattern_type' => 'regex',
                'pattern' => '[invalid',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['pattern']);
        });
    });

    describe('Destroy', function () {
        test('can delete a word filter', function () {
            $filter = WordFilter::factory()->create();

            $response = $this->deleteJson("/api/word-filters/{$filter->id}");

            $response->assertNoContent();
            $this->assertDatabaseMissing('word_filters', ['id' => $filter->id]);
        });

        test('returns 404 when deleting non-existent filter', function () {
            $response = $this->deleteJson('/api/word-filters/999');

            $response->assertNotFound();
        });
    });
});
