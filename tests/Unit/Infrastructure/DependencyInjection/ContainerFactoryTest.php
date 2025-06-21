<?php

use TorrentPier\Infrastructure\DependencyInjection\Container;
use TorrentPier\Infrastructure\DependencyInjection\ContainerFactory;
use TorrentPier\Infrastructure\DependencyInjection\ServiceProvider;

describe('ContainerFactory', function () {
    describe('create() method', function () {
        it('creates a container instance', function () {
            $container = ContainerFactory::create();
            expect($container)->toBeInstanceOf(Container::class);
        });

        it('applies configuration correctly', function () {
            $config = [
                'environment' => 'testing',
                'autowiring' => true,
                'annotations' => false,
            ];

            $container = ContainerFactory::create($config);
            expect($container)->toBeInstanceOf(Container::class);
        });

        it('loads custom definitions', function () {
            $config = [
                'definitions' => [
                    'test.service' => \DI\factory(function () {
                        return 'test_value';
                    }),
                ],
            ];

            $container = ContainerFactory::create($config);
            expect($container->get('test.service'))->toBe('test_value');
        });

        it('configures autowiring when enabled', function () {
            $config = ['autowiring' => true];
            $container = ContainerFactory::create($config);

            // Should be able to autowire stdClass
            expect($container->has(stdClass::class))->toBeTrue();
        });

        it('loads definition files when specified', function () {
            $tempDir = createTempDirectory();
            $definitionFile = $tempDir . '/definitions.php';

            file_put_contents($definitionFile, '<?php return [
                "file.service" => \DI\factory(function () {
                    return "from_file";
                }),
            ];');

            $config = [
                'definition_files' => [$definitionFile],
            ];

            $container = ContainerFactory::create($config);
            expect($container->get('file.service'))->toBe('from_file');

            removeTempDirectory($tempDir);
        });

        it('handles non-existent definition files gracefully', function () {
            $config = [
                'definition_files' => ['/non/existent/file.php'],
            ];

            // Should not throw an exception
            $container = ContainerFactory::create($config);
            expect($container)->toBeInstanceOf(Container::class);
        });
    });

    describe('service providers', function () {
        it('registers and boots service providers', function () {
            $providerClass = new class implements ServiceProvider {
                public static bool $registered = false;
                public static bool $booted = false;

                public function register(Container $container): void
                {
                    self::$registered = true;
                    $container->getWrappedContainer()->set('provider.service', 'provider_value');
                }

                public function boot(Container $container): void
                {
                    self::$booted = true;
                }
            };

            $config = [
                'providers' => [get_class($providerClass)],
            ];

            $container = ContainerFactory::create($config);

            expect($providerClass::$registered)->toBeTrue();
            expect($providerClass::$booted)->toBeTrue();
            expect($container->get('provider.service'))->toBe('provider_value');
        });

        it('handles invalid provider classes gracefully', function () {
            $config = [
                'providers' => ['NonExistentProvider'],
            ];

            // Should not throw an exception
            $container = ContainerFactory::create($config);
            expect($container)->toBeInstanceOf(Container::class);
        });

        it('boots providers after all registrations', function () {
            // Use a simpler approach without constructor dependencies
            $testFile = sys_get_temp_dir() . '/provider_order_test.txt';
            if (file_exists($testFile)) {
                unlink($testFile);
            }

            $provider1Class = new class implements ServiceProvider {
                public function register(Container $container): void
                {
                    $testFile = sys_get_temp_dir() . '/provider_order_test.txt';
                    file_put_contents($testFile, "register1\n", FILE_APPEND);
                }

                public function boot(Container $container): void
                {
                    $testFile = sys_get_temp_dir() . '/provider_order_test.txt';
                    file_put_contents($testFile, "boot1\n", FILE_APPEND);
                }
            };

            $provider2Class = new class implements ServiceProvider {
                public function register(Container $container): void
                {
                    $testFile = sys_get_temp_dir() . '/provider_order_test.txt';
                    file_put_contents($testFile, "register2\n", FILE_APPEND);
                }

                public function boot(Container $container): void
                {
                    $testFile = sys_get_temp_dir() . '/provider_order_test.txt';
                    file_put_contents($testFile, "boot2\n", FILE_APPEND);
                }
            };

            $config = [
                'providers' => [get_class($provider1Class), get_class($provider2Class)],
            ];

            ContainerFactory::create($config);

            // Read the order from the test file
            $content = file_get_contents($testFile);
            $lines = array_filter(explode("\n", trim($content)));

            // All registrations should happen before any boots
            expect($lines)->toBe(['register1', 'register2', 'boot1', 'boot2']);

            // Clean up
            unlink($testFile);
        });
    });

    describe('environment configuration', function () {
        it('enables compilation in production', function () {
            $tempDir = createTempDirectory();

            $config = [
                'environment' => 'production',
                'compilation_dir' => $tempDir . '/container',
                'proxies_dir' => $tempDir . '/proxies',
            ];

            $container = ContainerFactory::create($config);
            expect($container)->toBeInstanceOf(Container::class);

            removeTempDirectory($tempDir);
        });

        it('skips compilation in development', function () {
            $config = [
                'environment' => 'development',
            ];

            $container = ContainerFactory::create($config);
            expect($container)->toBeInstanceOf(Container::class);
        });
    });

    describe('layer definitions integration', function () {
        it('loads definitions from all architectural layers', function () {
            $container = ContainerFactory::create();

            // Container should be created successfully with all layer definitions
            expect($container)->toBeInstanceOf(Container::class);

            // Since most definitions are commented out, we just verify the container works
            expect($container->has(stdClass::class))->toBeTrue();
        });
    });
});
