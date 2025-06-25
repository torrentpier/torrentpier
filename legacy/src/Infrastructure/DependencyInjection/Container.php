<?php

declare(strict_types=1);

namespace TorrentPier\Infrastructure\DependencyInjection;

use DI\Container as DIContainer;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private DIContainer $container;

    public function __construct(DIContainer $container)
    {
        $this->container = $container;
    }

    public function get(string $id): mixed
    {
        return $this->container->get($id);
    }

    public function has(string $id): bool
    {
        return $this->container->has($id);
    }

    public function make(string $name, array $parameters = []): mixed
    {
        return $this->container->make($name, $parameters);
    }

    public function call(callable $callable, array $parameters = []): mixed
    {
        return $this->container->call($callable, $parameters);
    }

    public function injectOn(object $instance): object
    {
        return $this->container->injectOn($instance);
    }

    public function getWrappedContainer(): DIContainer
    {
        return $this->container;
    }
}
