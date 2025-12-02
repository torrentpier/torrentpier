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

/**
 * Front Controller for TorrentPier
 *
 * Handles request routing decisions. Does NOT execute includes directly
 * (to preserve global scope for legacy files).
 */
class FrontController
{
    public const ACTION_REQUIRE = 'require';
    public const ACTION_REQUIRE_EXIT = 'require_exit';
    public const ACTION_REDIRECT = 'redirect';
    public const ACTION_NOT_FOUND = 'not_found';
    public const ACTION_STATIC = 'static';
    public const ACTION_ROUTE = 'route';

    private string $basePath;
    private ?Router $router = null;

    /** @var string[] Paths that bypass the router entirely */
    private array $excludedPrefixes = ['/admin', '/bt'];

    /** @var string[] Static file extensions (let web server handle) */
    private array $staticExtensions = [
        'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg',
        'woff', 'woff2', 'ttf', 'eot', 'map'
    ];

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
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

        // Root path - legacy index
        if ($path === '/' || $path === '/index.php') {
            return [
                'action' => self::ACTION_REQUIRE_EXIT,
                'file' => $this->basePath . '/index_legacy.php'
            ];
        }

        // Route through router
        return ['action' => self::ACTION_ROUTE];
    }

    /**
     * Parse and normalize the request path
     */
    private function normalizePath(): string
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($requestUri, PHP_URL_PATH);

        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
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

            $file = $this->basePath . $path;

            if (is_file($file)) {
                return [
                    'action' => self::ACTION_REQUIRE_EXIT,
                    'file' => $file
                ];
            }

            if (is_file($file . '/index.php')) {
                return [
                    'action' => self::ACTION_REQUIRE_EXIT,
                    'file' => $file . '/index.php'
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
            // For POST requests, route directly (redirect would lose POST data)
            // For GET requests, redirect for SEO
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            if ($method !== 'GET') {
                return ['action' => self::ACTION_ROUTE];
            }

            return [
                'action' => self::ACTION_REDIRECT,
                'url' => $cleanPath . ($query ? '?' . $query : '')
            ];
        }

        // No route - include .php file directly if exists
        $filePath = $this->basePath . $path;
        if (is_file($filePath)) {
            return [
                'action' => self::ACTION_REQUIRE_EXIT,
                'file' => $filePath
            ];
        }

        // File doesn't exist - fall through to router (404)
        return null;
    }

    /**
     * Check if a route exists for the given path
     */
    private function routeExists(string $path): bool
    {
        if ($this->router === null) {
            $this->router = Router::getInstance();

            if (!$this->router->areRoutesLoaded()) {
                $routes = require $this->basePath . '/library/routes.php';
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
        return in_array(strtolower($extension), $this->staticExtensions, true);
    }

    /**
     * Get the base path
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Get the router instance (if initialized)
     */
    public function getRouter(): ?Router
    {
        return $this->router;
    }
}
