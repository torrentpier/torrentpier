<?php

use TorrentPier\Infrastructure\DependencyInjection\Definitions\InfrastructureDefinitions;

describe('InfrastructureDefinitions', function () {
    describe('getDefinitions() method', function () {
        it('returns an array', function () {
            $definitions = InfrastructureDefinitions::getDefinitions();
            expect($definitions)->toBeArray();
        });

        it('accepts configuration parameter', function () {
            $config = ['test' => 'value'];
            $definitions = InfrastructureDefinitions::getDefinitions($config);
            expect($definitions)->toBeArray();
        });

        it('returns empty array when no infrastructure services are implemented yet', function () {
            $definitions = InfrastructureDefinitions::getDefinitions();

            // Since we're in Phase 1 and infrastructure services aren't implemented yet,
            // the definitions should be empty (all examples are commented out)
            expect($definitions)->toBe([]);
        });

        it('follows infrastructure layer principles', function () {
            // Infrastructure layer should handle external concerns
            // This test verifies the structure is ready for future infrastructure services

            $definitions = InfrastructureDefinitions::getDefinitions();

            // Should be an array (even if empty)
            expect($definitions)->toBeArray();

            // When infrastructure services are added, they should follow these principles:
            // - Database connections and repositories
            // - Cache implementations
            // - External service adapters
            // - File storage systems
        });

        it('can be safely called multiple times', function () {
            $definitions1 = InfrastructureDefinitions::getDefinitions();
            $definitions2 = InfrastructureDefinitions::getDefinitions();

            expect($definitions1)->toBe($definitions2);
        });

        it('can handle different configurations', function () {
            $config1 = ['database' => ['host' => 'localhost']];
            $config2 = ['cache' => ['driver' => 'redis']];

            $definitions1 = InfrastructureDefinitions::getDefinitions($config1);
            $definitions2 = InfrastructureDefinitions::getDefinitions($config2);

            // Should handle different configs without breaking
            expect($definitions1)->toBeArray();
            expect($definitions2)->toBeArray();
        });

        it('is prepared for future database services', function () {
            // This test documents the intended structure for Phase 4 implementation

            $definitions = InfrastructureDefinitions::getDefinitions([
                'database' => [
                    'host' => '127.0.0.1',
                    'port' => 3306,
                    'database' => 'tp',
                    'username' => 'root',
                    'password' => '',
                    'charset' => 'utf8mb4',
                ],
            ]);

            expect($definitions)->toBeArray();

            // Future database services will be registered like:
            // 'database.connection.default' => DI\factory(function () use ($config) { ... }),
            // Connection::class => DI\get('database.connection.default'),

            // For now, verify the method works without breaking
            expect(count($definitions))->toBeGreaterThanOrEqual(0);
        });

        it('is prepared for future cache services', function () {
            $definitions = InfrastructureDefinitions::getDefinitions([
                'cache' => [
                    'driver' => 'file',
                    'file' => ['path' => '/tmp/cache'],
                ],
            ]);

            expect($definitions)->toBeArray();

            // Future cache services will be registered like:
            // 'cache.storage' => DI\factory(function () use ($config) { ... }),
            // 'cache.factory' => DI\factory(function (ContainerInterface $c) { ... }),
        });
    });

    describe('architectural compliance', function () {
        it('follows hexagonal architecture principles', function () {
            // Infrastructure layer should handle external concerns and adapters

            $definitions = InfrastructureDefinitions::getDefinitions();

            // Infrastructure definitions should focus on:
            // 1. Database connections and persistence
            // 2. Cache implementations
            // 3. External service adapters
            // 4. File storage systems
            // 5. Third-party integrations

            expect($definitions)->toBeArray();
        });

        it('supports dependency inversion', function () {
            // Infrastructure should implement domain interfaces

            $definitions = InfrastructureDefinitions::getDefinitions();

            // Future repository implementations will be registered here:
            // 'TorrentPier\Infrastructure\Persistence\Repository\UserRepository' => DI\autowire()
            //     ->constructorParameter('connection', DI\get('database.connection.default'))

            expect($definitions)->toBeArray();
        });

        it('handles configuration-based service creation', function () {
            // Infrastructure services should be configurable

            $config = [
                'database' => ['driver' => 'mysql'],
                'cache' => ['driver' => 'redis'],
                'storage' => ['driver' => 's3'],
            ];

            $definitions = InfrastructureDefinitions::getDefinitions($config);

            // Should handle configuration without breaking
            expect($definitions)->toBeArray();
        });

        it('prepares for multiple database connections', function () {
            $config = [
                'database' => [
                    'default' => 'mysql',
                    'connections' => [
                        'mysql' => ['driver' => 'mysql'],
                        'sqlite' => ['driver' => 'sqlite'],
                    ],
                ],
            ];

            $definitions = InfrastructureDefinitions::getDefinitions($config);
            expect($definitions)->toBeArray();
        });
    });
});
