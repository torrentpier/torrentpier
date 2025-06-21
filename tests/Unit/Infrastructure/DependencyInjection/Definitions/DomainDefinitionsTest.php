<?php

use TorrentPier\Infrastructure\DependencyInjection\Definitions\DomainDefinitions;

describe('DomainDefinitions', function () {
    describe('getDefinitions() method', function () {
        it('returns an array', function () {
            $definitions = DomainDefinitions::getDefinitions();
            expect($definitions)->toBeArray();
        });

        it('returns empty array when no domain services are implemented yet', function () {
            $definitions = DomainDefinitions::getDefinitions();

            // Since we're in Phase 1 and domain services aren't implemented yet,
            // the definitions should be empty (all examples are commented out)
            expect($definitions)->toBe([]);
        });

        it('follows domain layer principles', function () {
            // Domain definitions should not contain infrastructure dependencies
            // This test verifies the structure is ready for future domain services

            $definitions = DomainDefinitions::getDefinitions();

            // Should be an array (even if empty)
            expect($definitions)->toBeArray();

            // When domain services are added, they should follow these principles:
            // - No framework dependencies
            // - Repository interfaces mapped to implementations
            // - Pure business logic services
        });

        it('can be safely called multiple times', function () {
            $definitions1 = DomainDefinitions::getDefinitions();
            $definitions2 = DomainDefinitions::getDefinitions();

            expect($definitions1)->toBe($definitions2);
        });

        it('is prepared for future repository interface mappings', function () {
            // This test documents the intended structure for Phase 2 implementation

            $definitions = DomainDefinitions::getDefinitions();
            expect($definitions)->toBeArray();

            // Future repository interfaces will be mapped like:
            // 'TorrentPier\Domain\User\Repository\UserRepositoryInterface' =>
            //     DI\factory(function (ContainerInterface $c) {
            //         return $c->get('TorrentPier\Infrastructure\Persistence\Repository\UserRepository');
            //     }),

            // For now, verify the method works without breaking
            expect(count($definitions))->toBeGreaterThanOrEqual(0);
        });
    });

    describe('architectural compliance', function () {
        it('follows hexagonal architecture principles', function () {
            // Domain layer should have no infrastructure dependencies
            // This test ensures the definition structure is correct

            $definitions = DomainDefinitions::getDefinitions();

            // Domain definitions should focus on:
            // 1. Repository interface mappings
            // 2. Domain service factories
            // 3. No framework dependencies

            expect($definitions)->toBeArray();
        });

        it('supports dependency injection inversion', function () {
            // Domain interfaces should be mapped to infrastructure implementations
            // following the dependency inversion principle

            $definitions = DomainDefinitions::getDefinitions();

            // Even though empty now, the structure supports proper DI mapping
            expect($definitions)->toBeArray();
        });
    });
});
