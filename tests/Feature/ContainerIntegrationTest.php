<?php

use TorrentPier\Infrastructure\DependencyInjection\Bootstrap;
use TorrentPier\Infrastructure\DependencyInjection\Container;

describe('Container Integration', function () {
    afterEach(function () {
        Bootstrap::reset();
    });

    it('can bootstrap the container with real configuration', function () {
        $rootPath = $this->createTestRootDirectory();
        $this->createTestConfigFiles($rootPath, [
            'services' => [
                'integration.test' => 'integration_value',
            ],
        ]);

        $container = Bootstrap::init($rootPath);

        expect($container)->toBeInstanceOf(Container::class);
        expect($container->get('integration.test'))->toBe('integration_value');

        removeTempDirectory($rootPath);
    });

    it('integrates with global helper functions', function () {
        $rootPath = $this->createTestRootDirectory();
        $this->createTestConfigFiles($rootPath, [
            'services' => [
                'helper.test' => 'helper_value',
            ],
        ]);

        Bootstrap::init($rootPath);

        // Test container() helper
        expect(container())->toBeInstanceOf(Container::class);

        // Test app() helper without parameter
        expect(app())->toBeInstanceOf(Container::class);

        // Test app() helper with service ID
        expect(app('helper.test'))->toBe('helper_value');

        removeTempDirectory($rootPath);
    });

    it('handles missing services gracefully in helpers', function () {
        $rootPath = $this->createTestRootDirectory();
        Bootstrap::init($rootPath);

        // Should throw RuntimeException for missing service
        expect(fn() => app('missing.service'))
            ->toThrow(RuntimeException::class)
            ->toThrow('not found in container');

        removeTempDirectory($rootPath);
    });

    it('supports autowiring for simple classes', function () {
        $rootPath = $this->createTestRootDirectory();
        $container = Bootstrap::init($rootPath);

        // Should be able to autowire stdClass
        expect($container->has(stdClass::class))->toBeTrue();
        expect($container->get(stdClass::class))->toBeInstanceOf(stdClass::class);

        removeTempDirectory($rootPath);
    });

    it('loads all architectural layer definitions', function () {
        $rootPath = $this->createTestRootDirectory();
        $container = Bootstrap::init($rootPath);

        // Container should be created successfully with all layer definitions loaded
        // Even though most definitions are commented out, the loading should work
        expect($container)->toBeInstanceOf(Container::class);

        // Container should have itself registered
        expect($container->get(Container::class))->toBe($container);
        expect($container->get('container'))->toBe($container);

        removeTempDirectory($rootPath);
    });

    it('supports environment-based configuration', function () {
        $rootPath = $this->createTestRootDirectory();
        $this->createTestConfigFiles($rootPath, [
            'container' => [
                'environment' => 'production',
                'compilation_dir' => $rootPath . '/internal_data/cache/container',
                'proxies_dir' => $rootPath . '/internal_data/cache/proxies',
            ],
        ]);

        $container = Bootstrap::init($rootPath);

        expect($container)->toBeInstanceOf(Container::class);

        removeTempDirectory($rootPath);
    });

    it('supports service provider registration', function () {
        $testProviderClass = new class implements \TorrentPier\Infrastructure\DependencyInjection\ServiceProvider {
            public static bool $wasRegistered = false;
            public static bool $wasBooted = false;

            public function register(\TorrentPier\Infrastructure\DependencyInjection\Container $container): void
            {
                self::$wasRegistered = true;
                $container->getWrappedContainer()->set('provider.test', 'provider_registered');
            }

            public function boot(\TorrentPier\Infrastructure\DependencyInjection\Container $container): void
            {
                self::$wasBooted = true;
            }
        };

        $rootPath = $this->createTestRootDirectory();
        $this->createTestConfigFiles($rootPath, [
            'container' => [
                'providers' => [get_class($testProviderClass)],
            ],
        ]);

        $container = Bootstrap::init($rootPath);

        expect($testProviderClass::$wasRegistered)->toBeTrue();
        expect($testProviderClass::$wasBooted)->toBeTrue();
        expect($container->get('provider.test'))->toBe('provider_registered');

        removeTempDirectory($rootPath);
    });

    it('handles configuration file loading priority', function () {
        $rootPath = $this->createTestRootDirectory();
        $this->createTestConfigFiles($rootPath, [
            'services' => [
                'priority.test' => \DI\factory(function () {
                    return 'from_services_file';
                }),
            ],
        ]);

        // Initialize with runtime config that should override file config
        $container = Bootstrap::init($rootPath, [
            'definitions' => [
                'priority.test' => \DI\factory(function () {
                    return 'from_runtime_config';
                }),
                'runtime.only' => \DI\factory(function () {
                    return 'runtime_value';
                }),
            ],
        ]);

        // Runtime config should override file config
        expect($container->get('priority.test'))->toBe('from_runtime_config');
        expect($container->get('runtime.only'))->toBe('runtime_value');

        removeTempDirectory($rootPath);
    });

    it('provides meaningful error messages', function () {
        $rootPath = $this->createTestRootDirectory();
        Bootstrap::init($rootPath);

        try {
            app('definitely.missing.service');
            fail('Expected exception to be thrown');
        } catch (RuntimeException $e) {
            expect($e->getMessage())->toContain('definitely.missing.service');
            expect($e->getMessage())->toContain('not found in container');
        }

        removeTempDirectory($rootPath);
    });

    it('supports performance measurement', function () {
        $rootPath = $this->createTestRootDirectory();

        $time = measureExecutionTime(function () use ($rootPath) {
            Bootstrap::init($rootPath);
        });

        // Container initialization should be reasonably fast
        expect($time)->toBeLessThan(1.0); // Should take less than 1 second

        removeTempDirectory($rootPath);
    });
});
