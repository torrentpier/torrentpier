<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use TorrentPier\Infrastructure\DependencyInjection\Bootstrap;
use TorrentPier\Infrastructure\DependencyInjection\Container;
use TorrentPier\Infrastructure\DependencyInjection\ContainerFactory;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Reset container state for each test
        Bootstrap::reset();
    }

    protected function tearDown(): void
    {
        // Clean up container state
        Bootstrap::reset();

        parent::tearDown();
    }

    /**
     * Create a test container with optional custom configuration
     */
    protected function createTestContainer(array $config = []): Container
    {
        $defaultConfig = [
            'environment' => 'testing',
            'autowiring' => true,
            'definitions' => [],
        ];

        return ContainerFactory::create(array_merge($defaultConfig, $config));
    }

    /**
     * Create a container with custom service definitions
     */
    protected function createContainerWithDefinitions(array $definitions): Container
    {
        return $this->createTestContainer([
            'definitions' => $definitions,
        ]);
    }

    /**
     * Create a temporary test root directory
     */
    protected function createTestRootDirectory(): string
    {
        $tempDir = createTempDirectory();

        // Create basic directory structure
        mkdir($tempDir . '/config', 0755, true);
        mkdir($tempDir . '/internal_data/cache', 0755, true);

        return $tempDir;
    }

    /**
     * Create test configuration files
     */
    protected function createTestConfigFiles(string $rootPath, array $configs = []): void
    {
        $configPath = $rootPath . '/config';

        // Create container.php
        if (isset($configs['container'])) {
            file_put_contents(
                $configPath . '/container.php',
                '<?php return ' . var_export($configs['container'], true) . ';'
            );
        }

        // Create services.php - simplified approach for testing
        if (isset($configs['services'])) {
            $servicesContent = "<?php\n\nuse function DI\\factory;\n\nreturn [\n";
            foreach ($configs['services'] as $key => $value) {
                if (is_string($value)) {
                    $servicesContent .= "    '$key' => factory(function () { return '$value'; }),\n";
                }
            }
            $servicesContent .= "];\n";

            file_put_contents($configPath . '/services.php', $servicesContent);
        }

        // Create .env file
        if (isset($configs['env'])) {
            $envContent = '';
            foreach ($configs['env'] as $key => $value) {
                $envContent .= "$key=$value\n";
            }
            file_put_contents($rootPath . '/.env', $envContent);
        }
    }

    /**
     * Assert that a service can be resolved from the container
     */
    protected function assertCanResolve(Container $container, string $serviceId): void
    {
        $this->assertTrue($container->has($serviceId), "Container should have service: $serviceId");
        $this->assertNotNull($container->get($serviceId), "Should be able to resolve service: $serviceId");
    }

    /**
     * Assert that a service cannot be resolved from the container
     */
    protected function assertCannotResolve(Container $container, string $serviceId): void
    {
        $this->assertFalse($container->has($serviceId), "Container should not have service: $serviceId");
    }
}
