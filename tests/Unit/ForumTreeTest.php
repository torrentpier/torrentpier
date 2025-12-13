<?php

use TorrentPier\Cache\DatastoreManager;
use TorrentPier\Forum\ForumTree;

describe('ForumTree', function () {
    it('can be instantiated with mocked datastore', function () {
        $datastore = Mockery::mock(DatastoreManager::class);
        $datastore->shouldReceive('get')->andReturn([]);

        $instance = new ForumTree($datastore);

        expect($instance)->toBeInstanceOf(ForumTree::class);
    });

    it('returns empty array when datastore is empty', function () {
        $datastore = Mockery::mock(DatastoreManager::class);
        $datastore->shouldReceive('get')
            ->with('cat_forums')
            ->andReturn([]);

        $instance = new ForumTree($datastore);

        expect($instance->get())->toBe([]);
    });

    it('returns forum data from datastore', function () {
        $forumData = [
            'cat' => ['id' => 1, 'name' => 'Test Category'],
            'forum' => ['id' => 10, 'name' => 'Test Forum'],
        ];

        $datastore = Mockery::mock(DatastoreManager::class);
        $datastore->shouldReceive('get')
            ->with('cat_forums')
            ->andReturn($forumData);

        $instance = new ForumTree($datastore);

        expect($instance->get())->toBe($forumData);
    });

    it('updates datastore when data is false', function () {
        $datastore = Mockery::mock(DatastoreManager::class);
        $datastore->shouldReceive('get')
            ->with('cat_forums')
            ->once()
            ->andReturn(false);
        $datastore->shouldReceive('update')
            ->with('cat_forums')
            ->once();
        $datastore->shouldReceive('get')
            ->with('cat_forums')
            ->once()
            ->andReturn(['refreshed' => true]);

        $instance = new ForumTree($datastore);

        expect($instance->get())->toBe(['refreshed' => true]);
    });

    it('caches data after first call', function () {
        $datastore = Mockery::mock(DatastoreManager::class);
        $datastore->shouldReceive('get')
            ->with('cat_forums')
            ->once() // Should only be called once
            ->andReturn(['cached' => true]);

        $instance = new ForumTree($datastore);

        // Call get() twice
        $result1 = $instance->get();
        $result2 = $instance->get();

        expect($result1)->toBe(['cached' => true])
            ->and($result2)->toBe(['cached' => true]);
    });

    it('refresh clears cache and updates datastore', function () {
        $datastore = Mockery::mock(DatastoreManager::class);
        $datastore->shouldReceive('update')
            ->with('cat_forums')
            ->once();

        $instance = new ForumTree($datastore);
        $instance->refresh();

        // Just verify no exception - refresh() returns void
        expect(true)->toBeTrue();
    });

    afterEach(function () {
        Mockery::close();
    });
});
