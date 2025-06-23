<?php

declare(strict_types=1);

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TorrentPier\Infrastructure\DependencyInjection\Bootstrap;
use TorrentPier\Infrastructure\DependencyInjection\Container;

if (!function_exists('container')) {
    /**
     * Get the dependency injection container instance
     *
     * @return Container|null
     */
    function container(): ?Container
    {
        return Bootstrap::getContainer();
    }
}

if (!function_exists('app')) {
    /**
     * Get a service from the container or the container itself
     *
     * @param string|null $id Service identifier
     * @return mixed
     * @throws RuntimeException If container is not initialized or service not found
     */
    function app(?string $id = null): mixed
    {
        $container = container();

        if ($container === null) {
            throw new RuntimeException('Container has not been initialized. Call Bootstrap::init() first.');
        }

        if ($id === null) {
            return $container;
        }

        try {
            return $container->get($id);
        } catch (NotFoundExceptionInterface $e) {
            throw new RuntimeException("Service '$id' not found in container: " . $e->getMessage(), 0, $e);
        } catch (ContainerExceptionInterface $e) {
            throw new RuntimeException("Container error while resolving '$id': " . $e->getMessage(), 0, $e);
        }
    }
}
