<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Router;

use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Front Controller for TorrentPier
 *
 * Handles request routing decisions. Does NOT execute includes directly
 * (to preserve global scope for legacy files).
 */
class FrontController
{
    public const string ACTION_REQUIRE = 'require';
    public const string ACTION_REQUIRE_EXIT = 'require_exit';
    public const string ACTION_REDIRECT = 'redirect';
    public const string ACTION_NOT_FOUND = 'not_found';
    public const string ACTION_STATIC = 'static';
    public const string ACTION_ROUTE = 'route';

    private string $appPath;
    private string $publicPath;
    private ?Router $router = null;

    /** @var string[] Paths that bypass the router entirely */
    private array $excludedPrefixes = ['/admin', '/bt'];

    /** @var string[] Static file extensions (let web server handle) */
    private array $staticExtensions = [
        'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg',
        'woff', 'woff2', 'ttf', 'eot', 'map', 'xml',
    ];

    public function __construct(string $appPath, string $publicPath)
    {
        $this->appPath = $appPath;
        $this->publicPath = $publicPath;
    }

    /**
     * Resolve what action to take for the current request
     *
     * @return array{action: string, file?: string, url?: string}
     */
    public function resolve(): array
    {
        $path = $this->normalizePath();

        // Excluded directories - bypass router
        if ($result = $this->resolveExcluded($path)) {
            return $result;
        }

        // PHP file redirects
        if ($result = $this->resolvePhpRedirect($path)) {
            return $result;
        }

        // Static files
        if ($this->isStatic($path)) {
            return ['action' => self::ACTION_STATIC];
        }

        // /index.php -> redirect to /
        if ($path === '/index.php') {
            $query = $_SERVER['QUERY_STRING'] ?? '';

            return [
                'action' => self::ACTION_REDIRECT,
                'url' => '/' . ($query ? '?' . $query : ''),
            ];
        }

        // Route through router
        return ['action' => self::ACTION_ROUTE];
    }

    /**
     * Get the application path
     */
    public function getAppPath(): string
    {
        return $this->appPath;
    }

    /**
     * Get the public path
     */
    public function getPublicPath(): string
    {
        return $this->publicPath;
    }

    /**
     * Get the router instance (if initialized)
     */
    public function getRouter(): ?Router
    {
        return $this->router;
    }

    /**
     * Parse and normalize the request path
     */
    private function normalizePath(): string
    {
        // Note: $_SERVER used here because FrontController runs before common.php bootstrap
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $originalPath = parse_url($requestUri, PHP_URL_PATH) ?? '/';

        // Handle parse_url failure
        if ($originalPath === false) {
            return '/';
        }

        // Normalize a path to prevent directory traversal
        $path = '/' . ltrim($originalPath, '/');

        // Resolve .. and . components
        $segments = explode('/', $path);
        $normalized = [];
        foreach ($segments as $segment) {
            if ($segment === '..') {
                array_pop($normalized);
            } elseif ($segment !== '' && $segment !== '.') {
                $normalized[] = $segment;
            }
        }
        $path = '/' . implode('/', $normalized);

        // Preserve trailing slash for SEO-friendly routes
        if (str_ends_with($originalPath, '/') && $path !== '/') {
            $path .= '/';
        }

        return $path;
    }

    /**
     * Resolve excluded directories
     */
    private function resolveExcluded(string $path): ?array
    {
        foreach ($this->excludedPrefixes as $prefix) {
            if (!str_starts_with($path, $prefix)) {
                continue;
            }

            // Entry points are in publicPath (public/admin/, public/bt/)
            $file = $this->publicPath . $path;

            // Validate path is within publicPath (prevent traversal)
            $realBase = realpath($this->publicPath);
            $realFile = realpath($file);

            if ($realFile !== false && str_starts_with($realFile, $realBase . DIRECTORY_SEPARATOR)) {
                return [
                    'action' => self::ACTION_REQUIRE_EXIT,
                    'file' => $realFile,
                ];
            }

            // Check for index.php in the directory
            $indexFile = $file . '/index.php';
            $realIndex = realpath($indexFile);

            if ($realIndex !== false && str_starts_with($realIndex, $realBase . DIRECTORY_SEPARATOR)) {
                return [
                    'action' => self::ACTION_REQUIRE_EXIT,
                    'file' => $realIndex,
                ];
            }

            return ['action' => self::ACTION_NOT_FOUND];
        }

        return null;
    }

    /**
     * Resolve PHP file redirects
     */
    private function resolvePhpRedirect(string $path): ?array
    {
        if (!str_ends_with($path, '.php') || $path === '/index.php') {
            return null;
        }

        $cleanPath = substr($path, 0, -4);
        $query = $_SERVER['QUERY_STRING'] ?? '';

        // Check if a route exists for the clean path
        if ($this->routeExists($cleanPath)) {
            // For POST requests, rewrite to a clean path and route (redirect would lose POST data)
            // For GET requests, redirect for SEO
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            if ($method !== 'GET') {
                // Rewrite REQUEST_URI to a clean path so the router can match it
                $_SERVER['REQUEST_URI'] = $cleanPath . ($query ? '?' . $query : '');

                return ['action' => self::ACTION_ROUTE];
            }

            return [
                'action' => self::ACTION_REDIRECT,
                'url' => $cleanPath . ($query ? '?' . $query : ''),
            ];
        }

        // No route for clean path - include .php file directly if exists in public/
        $filePath = $this->publicPath . $path;
        $realBase = realpath($this->publicPath);
        $realFile = realpath($filePath);

        if ($realFile !== false && str_starts_with($realFile, $realBase . DIRECTORY_SEPARATOR)) {
            return [
                'action' => self::ACTION_REQUIRE_EXIT,
                'file' => $realFile,
            ];
        }

        // File doesn't exist and no route - 404
        return ['action' => self::ACTION_NOT_FOUND];
    }

    /**
     * Check if a route exists for the given path
     * @throws BindingResolutionException
     */
    private function routeExists(string $path): bool
    {
        if ($this->router === null) {
            $this->router = app(Router::class);

            if (!$this->router->areRoutesLoaded()) {
                $routes = require $this->appPath . '/routes/web.php';
                $routes($this->router);
                $this->router->setRoutesLoaded();
            }
        }

        return $this->router->hasRoute($path);
    }

    /**
     * Check if a path is a static file
     */
    private function isStatic(string $path): bool
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return \in_array(strtolower($extension), $this->staticExtensions, true);
    }
}
