<?php

namespace Tests;

use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create a fresh container for each test
        $container = new Container();
        Container::setInstance($container);
    }

    protected function tearDown(): void
    {
        // Clean up container state
        Container::setInstance(null);

        parent::tearDown();
    }

    /**
     * Create a test container with optional custom configuration
     */
    protected function createTestContainer(array $bindings = []): Container
    {
        $container = new Container();
        
        // Add test-specific bindings
        foreach ($bindings as $abstract => $concrete) {
            $container->bind($abstract, $concrete);
        }

        return $container;
    }

    /**
     * Get the app container instance
     */
    protected function app(?string $abstract = null): mixed
    {
        $container = Container::getInstance();
        
        if (is_null($abstract)) {
            return $container;
        }
        
        return $container->make($abstract);
    }

    /**
     * Assert that a service can be resolved from the container
     */
    protected function assertCanResolve(Container $container, string $serviceId): void
    {
        $this->assertTrue($container->bound($serviceId), "Container should have service: $serviceId");
        $this->assertNotNull($container->make($serviceId), "Should be able to resolve service: $serviceId");
    }

    /**
     * Assert that a service cannot be resolved from the container
     */
    protected function assertCannotResolve(Container $container, string $serviceId): void
    {
        $this->assertFalse($container->bound($serviceId), "Container should not have service: $serviceId");
    }
}
