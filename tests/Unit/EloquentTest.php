<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

use App\Models\Category;
use App\Models\Forum;
use App\Models\Post;
use App\Models\PostText;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection;

/**
 * Initialize Eloquent for standalone testing (without a full app bootstrap)
 */
function bootEloquentForTest(): Capsule
{
    static $capsule = null;

    if ($capsule !== null) {
        return $capsule;
    }

    // Load .env if available
    $envFile = __DIR__ . '/../../.env';
    if (file_exists($envFile)) {
        $dotenv = Dotenv\Dotenv::createImmutable(dirname($envFile));
        $dotenv->safeLoad();
    }

    $capsule = new Capsule;

    $capsule->addConnection([
        'driver' => 'mysql',
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => (int)($_ENV['DB_PORT'] ?? 3306),
        'database' => $_ENV['DB_DATABASE'] ?? 'tp',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => 'bb_',  // TorrentPier table prefix
    ]);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
}

beforeEach(function () {
    // Ensure Eloquent is booted for tests
    bootEloquentForTest();
});

test('can connect to database via Eloquent', function () {
    $connection = Capsule::connection();

    expect($connection)->toBeInstanceOf(Connection::class)
        ->and($connection->getDatabaseName())->toBe(env('DB_DATABASE', 'tp'));
});

test('can query users via Eloquent', function () {
    $users = User::limit(5)->get();

    expect($users)->toBeInstanceOf(Collection::class);
});

test('user model has correct table', function () {
    $user = new User;

    // Table name without prefix - Eloquent adds 'bb_' automatically via connection config
    expect($user->getTable())->toBe('users')
        ->and($user->getKeyName())->toBe('user_id');
});

test('topic model has correct table', function () {
    $topic = new Topic;

    expect($topic->getTable())->toBe('topics')
        ->and($topic->getKeyName())->toBe('topic_id');
});

test('post model has correct table', function () {
    $post = new Post;

    expect($post->getTable())->toBe('posts')
        ->and($post->getKeyName())->toBe('post_id');
});

test('category model has correct table', function () {
    $category = new Category;

    expect($category->getTable())->toBe('categories')
        ->and($category->getKeyName())->toBe('cat_id');
});

test('forum model has correct table', function () {
    $forum = new Forum;

    expect($forum->getTable())->toBe('forums')
        ->and($forum->getKeyName())->toBe('forum_id');
});

test('can query real users from database', function () {
    $user = User::first();

    if ($user) {
        expect($user)->toBeInstanceOf(User::class)
            ->and($user->user_id)->toBeInt()
            ->and($user->username)->toBeString();
    } else {
        // No users in the database - this is OK for empty test databases
        expect(true)->toBeTrue();
    }
});

test('can query topics with relationships', function () {
    $topic = Topic::with(['forum', 'poster'])->first();

    if ($topic) {
        expect($topic)->toBeInstanceOf(Topic::class)
            ->and($topic->relationLoaded('forum'))->toBeTrue()
            ->and($topic->relationLoaded('poster'))->toBeTrue();
    } else {
        expect(true)->toBeTrue();
    }
});

test('can query posts with text relationship', function () {
    $post = Post::with('text')->first();

    if ($post) {
        expect($post)->toBeInstanceOf(Post::class)
            ->and($post->relationLoaded('text'))->toBeTrue();

        if ($post->text) {
            expect($post->text)->toBeInstanceOf(PostText::class);
        }
    } else {
        expect(true)->toBeTrue();
    }
});

test('eloquent capsule is globally accessible', function () {
    // In tests, we use bootEloquentForTest() which sets capsule as global
    $capsule = Capsule::connection();

    expect($capsule)->toBeInstanceOf(Connection::class);
});
