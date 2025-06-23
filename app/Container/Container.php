<?php

declare(strict_types=1);

namespace App\Container;

use Illuminate\Container\Container as IlluminateContainer;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\CircularDependencyException;

/**
 * Application Container
 *
 * Extends Illuminate Container with application-specific functionality
 */
class Container extends IlluminateContainer
{
    /**
     * Get a service from the container
     *
     * @throws BindingResolutionException When the service cannot be resolved
     * @throws CircularDependencyException
     */
    public function get(string $id): mixed
    {
        return $this->resolve($id);
    }

    /**
     * Check if a service exists in the container
     */
    public function has(string $id): bool
    {
        return $this->bound($id);
    }
}
