<?php

/**
 * Example: How to use the DI Container in TorrentPier 3.0
 * 
 * NOTE: These are examples for future implementation following the hexagonal architecture spec.
 * Most services referenced here don't exist yet and will be implemented in phases.
 */

use TorrentPier\Infrastructure\DependencyInjection\Bootstrap;
use TorrentPier\Infrastructure\DependencyInjection\ContainerFactory;

// ===== PHASE 1: Foundation (CURRENT) =====

// 1. Bootstrap the container (typically done once in index.php or bootstrap file)
$rootPath = __DIR__ . '/../..';
$container = Bootstrap::init($rootPath);

// 2. Basic container usage (works now)
$containerInstance = app(); // Get container itself
$hasService = $container->has('some.service'); // Check if service exists

// ===== PHASE 2: Domain Modeling (FUTURE) =====

// 3. Repository interfaces (when implemented in Domain layer)
// $userRepository = app('TorrentPier\Domain\User\Repository\UserRepositoryInterface');
// $torrentRepository = app('TorrentPier\Domain\Tracker\Repository\TorrentRepositoryInterface');
// $forumRepository = app('TorrentPier\Domain\Forum\Repository\ForumRepositoryInterface');

// ===== PHASE 3: Application Services (FUTURE) =====

// 4. Command/Query handlers (when implemented)
// $registerUserHandler = app('TorrentPier\Application\User\Handler\RegisterUserHandler');
// $announceHandler = app('TorrentPier\Application\Tracker\Handler\ProcessAnnounceHandler');
// $createPostHandler = app('TorrentPier\Application\Forum\Handler\CreatePostHandler');

// 5. Making command instances with parameters
// $command = $container->make('TorrentPier\Application\User\Command\RegisterUserCommand', [
//     'username' => 'john_doe',
//     'email' => 'john@example.com',
//     'password' => 'secure_password'
// ]);

// ===== PHASE 4: Infrastructure (FUTURE) =====

// 6. Database and cache (when infrastructure is implemented)
// $database = app('database.connection.default');
// $cache = app('cache.factory')('forum'); // Get cache instance for 'forum' namespace

// ===== PHASE 5: Presentation (FUTURE) =====

// 7. Controllers (when implemented)
// $userController = app('TorrentPier\Presentation\Http\Controllers\Api\UserController');
// $trackerController = app('TorrentPier\Presentation\Http\Controllers\Web\TrackerController');

// ===== TESTING EXAMPLES =====

// 8. Testing with custom container (works now)
$testContainer = ContainerFactory::create([
    'definitions' => [
        'test.service' => \DI\factory(function () {
            return new class {
                public function test() { return 'test'; }
            };
        }),
    ],
    'environment' => 'testing',
]);

// 9. Safe service resolution (works now)
try {
    $service = app('optional.service');
} catch (RuntimeException $e) {
    // Service not found, handle gracefully
    $service = null;
}

// ===== LEGACY INTEGRATION (CURRENT) =====

// 10. Integration with legacy code
// In legacy files, after including common.php or similar:
if (!Bootstrap::getContainer()) {
    Bootstrap::init(BB_ROOT ?? __DIR__ . '/../..');
}

// 11. Method injection (works now if service exists)
class ExampleService
{
    public function processData(string $data)
    {
        // Container can inject dependencies when calling this method
        return "Processed: $data";
    }
}

$exampleService = new ExampleService();
$result = $container->call([$exampleService, 'processData'], [
    'data' => 'test data'
]);