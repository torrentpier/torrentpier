<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Http;

/**
 * Middleware configuration builder
 *
 * Provides a fluent API for configuring middleware in bootstrap/app.php.
 * This class is passed to the withMiddleware() callback during application
 * configuration.
 *
 * Usage:
 *   ->withMiddleware(function (Middleware $middleware): void {
 *       // Global middleware (runs on every request)
 *       $middleware->prepend(CorsMiddleware::class);
 *       $middleware->append(CompressionMiddleware::class);
 *
 *       // Middleware groups
 *       $middleware->web(append: [SessionMiddleware::class]);
 *       $middleware->api(append: [ThrottleMiddleware::class]);
 *
 *       // Custom groups
 *       $middleware->group('admin', [AdminAuthMiddleware::class]);
 *
 *       // Aliases for route-specific middleware
 *       $middleware->alias('auth', AuthMiddleware::class);
 *       $middleware->alias('admin', AdminMiddleware::class);
 *   })
 */
class Middleware
{
    /**
     * Global middleware stack
     *
     * @var string[]
     */
    private array $_global = [];

    /**
     * Middleware groups
     *
     * @var array<string, string[]>
     */
    private array $_groups = [
        'web' => [],
        'api' => [],
    ];

    /**
     * Middleware aliases
     *
     * @var array<string, string>
     */
    private array $_aliases = [];

    /**
     * Global middleware stack (read-only)
     *
     * @var string[]
     */
    public array $global {
        get => $this->_global;
    }

    /**
     * Middleware groups (read-only)
     *
     * @var array<string, string[]>
     */
    public array $groups {
        get => $this->_groups;
    }

    /**
     * Middleware aliases (read-only)
     *
     * @var array<string, string>
     */
    public array $aliases {
        get => $this->_aliases;
    }

    /**
     * Prepend middleware to the global stack
     *
     * Prepended middleware runs before all other middleware.
     *
     * @param string|string[] $middleware Middleware class name(s)
     */
    public function prepend(string|array $middleware): static
    {
        $middleware = \is_array($middleware) ? $middleware : [$middleware];
        $this->_global = array_merge($middleware, $this->_global);

        return $this;
    }

    /**
     * Append middleware to the global stack
     *
     * Appended middleware runs after other middleware.
     *
     * @param string|string[] $middleware Middleware class name(s)
     */
    public function append(string|array $middleware): static
    {
        $middleware = \is_array($middleware) ? $middleware : [$middleware];
        $this->_global = array_merge($this->_global, $middleware);

        return $this;
    }

    /**
     * Configure the 'web' middleware group
     *
     * @param string[] $append Middleware to append to the group
     * @param string[] $prepend Middleware to prepend to the group
     */
    public function web(array $append = [], array $prepend = []): static
    {
        return $this->configureGroup('web', $append, $prepend);
    }

    /**
     * Configure the 'api' middleware group
     *
     * @param string[] $append Middleware to append to the group
     * @param string[] $prepend Middleware to prepend to the group
     */
    public function api(array $append = [], array $prepend = []): static
    {
        return $this->configureGroup('api', $append, $prepend);
    }

    /**
     * Configure a custom middleware group
     *
     * @param string $name Group name
     * @param string[] $middleware Middleware stack for the group
     */
    public function group(string $name, array $middleware): static
    {
        $this->_groups[$name] = $middleware;

        return $this;
    }

    /**
     * Register a middleware alias
     *
     * Aliases allow using short names in route definitions.
     * Example: $middleware->alias('auth', AuthMiddleware::class);
     * Then in routes: ->middleware('auth')
     *
     * @param string $name Alias name
     * @param string $class Full middleware class name
     */
    public function alias(string $name, string $class): static
    {
        $this->_aliases[$name] = $class;

        return $this;
    }

    /**
     * Configure a middleware group with append and prepend
     *
     * @param string $name Group name
     * @param string[] $append Middleware to append
     * @param string[] $prepend Middleware to prepend
     */
    protected function configureGroup(string $name, array $append, array $prepend): static
    {
        if (!isset($this->_groups[$name])) {
            $this->_groups[$name] = [];
        }

        if (!empty($prepend)) {
            $this->_groups[$name] = array_merge($prepend, $this->_groups[$name]);
        }

        if (!empty($append)) {
            $this->_groups[$name] = array_merge($this->_groups[$name], $append);
        }

        return $this;
    }

    /**
     * Get a specific middleware group
     *
     * @param string $name Group name
     * @return string[]
     */
    public function getGroup(string $name): array
    {
        return $this->_groups[$name] ?? [];
    }

    /**
     * Resolve a middleware alias to its class name
     *
     * @param string $name Alias name or class name
     * @return string Full class name
     */
    public function resolveAlias(string $name): string
    {
        return $this->_aliases[$name] ?? $name;
    }
}
