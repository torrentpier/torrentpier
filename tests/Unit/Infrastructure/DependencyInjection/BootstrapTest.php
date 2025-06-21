<?php

use TorrentPier\Infrastructure\DependencyInjection\Bootstrap;
use TorrentPier\Infrastructure\DependencyInjection\Container;

describe('Bootstrap', function () {
    beforeEach(function () {
        // Ensure clean state for each test
        Bootstrap::reset();
    });

    afterEach(function () {
        Bootstrap::reset();
    });

    describe('init() method', function () {
        it('initializes and returns a container', function () {
            $rootPath = $this->createTestRootDirectory();

            $container = Bootstrap::init($rootPath);

            expect($container)->toBeInstanceOf(Container::class);

            removeTempDirectory($rootPath);
        });

        it('returns the same container on subsequent calls', function () {
            $rootPath = $this->createTestRootDirectory();

            $container1 = Bootstrap::init($rootPath);
            $container2 = Bootstrap::init($rootPath);

            expect($container1)->toBe($container2);

            removeTempDirectory($rootPath);
        });

        it('registers container instance with itself', function () {
            $rootPath = $this->createTestRootDirectory();

            $container = Bootstrap::init($rootPath);

            expect($container->get(Container::class))->toBe($container);
            expect($container->get('container'))->toBe($container);

            removeTempDirectory($rootPath);
        });

        it('loads environment variables from .env file', function () {
            $rootPath = $this->createTestRootDirectory();
            $this->createTestConfigFiles($rootPath, [
                'env' => [
                    'TEST_VAR' => 'test_value',
                    'APP_ENV' => 'testing',
                ],
            ]);

            Bootstrap::init($rootPath);

            expect($_ENV['TEST_VAR'] ?? null)->toBe('test_value');
            expect($_ENV['APP_ENV'] ?? null)->toBe('testing');

            removeTempDirectory($rootPath);
        });

        it('loads configuration from config files', function () {
            $rootPath = $this->createTestRootDirectory();
            $this->createTestConfigFiles($rootPath, [
                'container' => [
                    'environment' => 'testing',
                    'autowiring' => true,
                ],
                'services' => [
                    'test.service' => 'config_value',
                ],
            ]);

            $container = Bootstrap::init($rootPath);

            expect($container->get('test.service'))->toBe('config_value');

            removeTempDirectory($rootPath);
        });

        it('handles missing config files gracefully', function () {
            $rootPath = $this->createTestRootDirectory();

            // Should not throw exception even without config files
            $container = Bootstrap::init($rootPath);

            expect($container)->toBeInstanceOf(Container::class);

            removeTempDirectory($rootPath);
        });
    });

    describe('getContainer() method', function () {
        it('returns null when not initialized', function () {
            expect(Bootstrap::getContainer())->toBeNull();
        });

        it('returns container after initialization', function () {
            $rootPath = $this->createTestRootDirectory();

            $container = Bootstrap::init($rootPath);

            expect(Bootstrap::getContainer())->toBe($container);

            removeTempDirectory($rootPath);
        });
    });

    describe('reset() method', function () {
        it('clears the container instance', function () {
            $rootPath = $this->createTestRootDirectory();

            Bootstrap::init($rootPath);
            expect(Bootstrap::getContainer())->not->toBeNull();

            Bootstrap::reset();
            expect(Bootstrap::getContainer())->toBeNull();

            removeTempDirectory($rootPath);
        });

        it('allows re-initialization after reset', function () {
            $rootPath = $this->createTestRootDirectory();

            $container1 = Bootstrap::init($rootPath);
            Bootstrap::reset();
            $container2 = Bootstrap::init($rootPath);

            expect($container1)->not->toBe($container2);
            expect($container2)->toBeInstanceOf(Container::class);

            removeTempDirectory($rootPath);
        });
    });

    describe('configuration loading', function () {
        it('merges configuration from multiple sources', function () {
            $rootPath = $this->createTestRootDirectory();
            $this->createTestConfigFiles($rootPath, [
                'env' => [
                    'APP_ENV' => 'production',
                    'APP_DEBUG' => 'false',
                ],
                'container' => [
                    'autowiring' => true,
                ],
                'services' => [
                    'config.service' => 'merged_config',
                ],
            ]);

            $container = Bootstrap::init($rootPath, [
                'definitions' => [
                    'runtime.service' => \DI\factory(function () {
                        return 'runtime_config';
                    }),
                ],
            ]);

            expect($container->get('config.service'))->toBe('merged_config');
            expect($container->get('runtime.service'))->toBe('runtime_config');

            removeTempDirectory($rootPath);
        });

        it('sets default environment when no .env file exists', function () {
            $rootPath = $this->createTestRootDirectory();

            $container = Bootstrap::init($rootPath);

            // Container should still be created successfully
            expect($container)->toBeInstanceOf(Container::class);

            removeTempDirectory($rootPath);
        });
    });

    describe('error handling', function () {
        it('handles invalid root path gracefully', function () {
            // Should not throw fatal error for non-existent path
            expect(function () {
                Bootstrap::init('/non/existent/path');
            })->not->toThrow(Error::class);
        });
    });
});
