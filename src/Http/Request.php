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

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\ServerBag;
use UnexpectedValueException;

/**
 * HTTP Request (singleton)
 * Centralized request handling based on Symfony HttpFoundation
 *
 * Provides typed access to request parameters with fallback defaults.
 * Use instead of direct $_GET, $_POST, $_REQUEST, $_COOKIE, $_SERVER, $_FILES access.
 *
 * @property-read InputBag $query       GET parameters ($_GET equivalent)
 * @property-read InputBag $post        POST parameters ($_POST equivalent)
 * @property-read InputBag $cookies     Cookies ($_COOKIE equivalent)
 * @property-read ServerBag $server     Server parameters ($_SERVER equivalent)
 * @property-read FileBag $files        Uploaded files ($_FILES equivalent)
 * @property-read HeaderBag $headers    HTTP headers
 * @property-read ParameterBag $attributes Custom request attributes (for routing data)
 */
final class Request
{
    private static ?self $instance = null;
    private SymfonyRequest $request;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        $this->request = SymfonyRequest::createFromGlobals();
    }

    /**
     * Magic getter for direct access to Symfony Request bags
     *
     * @param string $name Property name (query, post, cookies, server, files, headers, attributes)
     * @return InputBag|ServerBag|FileBag|HeaderBag|ParameterBag|null
     */
    public function __get(string $name): InputBag|ServerBag|FileBag|HeaderBag|ParameterBag|null
    {
        return match ($name) {
            'query' => $this->request->query,
            'post' => $this->request->request,
            'cookies' => $this->request->cookies,
            'server' => $this->request->server,
            'files' => $this->request->files,
            'headers' => $this->request->headers,
            'attributes' => $this->request->attributes,
            default => null,
        };
    }

    /**
     * Get a singleton instance
     */
    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * Reset singleton instance (useful for testing)
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }

    /**
     * Set trusted proxies for IP detection behind load balancers
     *
     * @param array<string> $proxies List of trusted proxy IPs
     * @param int $trustedHeaderSet Bitmask of trusted headers
     */
    public static function setTrustedProxies(array $proxies, int $trustedHeaderSet = SymfonyRequest::HEADER_X_FORWARDED_FOR | SymfonyRequest::HEADER_X_FORWARDED_HOST | SymfonyRequest::HEADER_X_FORWARDED_PORT | SymfonyRequest::HEADER_X_FORWARDED_PROTO): void
    {
        SymfonyRequest::setTrustedProxies($proxies, $trustedHeaderSet);
    }

    // ========================================
    // Generic parameter access (POST > GET priority)
    // ========================================

    /**
     * Get a parameter from POST or GET (POST takes priority)
     * Handles both scalar and array values safely
     *
     * @param string $key Parameter name
     * @param mixed $default Default value if parameter not found (must be scalar for InputBag compatibility)
     */
    public function get(string $key, mixed $default = null): mixed
    {
        // Ensure default is scalar (InputBag requirement)
        $scalarDefault = \is_scalar($default) || $default === null ? $default : null;

        // POST takes priority over GET
        if ($this->request->request->has($key)) {
            try {
                return $this->request->request->get($key, $scalarDefault);
            } catch (BadRequestException|InvalidArgumentException) {
                // Value is an array, use all() to get it
                return $this->request->request->all($key) ?: $default;
            }
        }

        try {
            return $this->request->query->get($key, $scalarDefault);
        } catch (BadRequestException|InvalidArgumentException) {
            // Value is an array, use all() to get it
            return $this->request->query->all($key) ?: $default;
        }
    }

    /**
     * Check if a parameter exists in POST or GET
     */
    public function has(string $key): bool
    {
        return $this->request->request->has($key) || $this->request->query->has($key);
    }

    /**
     * Get all parameters merged (query and post)
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge(
            $this->request->query->all(),
            $this->request->request->all(),
        );
    }

    // ========================================
    // Typed getters (POST > GET priority)
    // ========================================

    /**
     * Get an integer parameter
     * Returns default if value is not a valid integer
     */
    public function getInt(string $key, int $default = 0): int
    {
        try {
            // Check POST first, then GET
            if ($this->request->request->has($key)) {
                return $this->request->request->getInt($key, $default);
            }

            return $this->request->query->getInt($key, $default);
        } catch (UnexpectedValueException) {
            // Value is not a valid integer (e.g., "all", arrays, etc.)
            return $default;
        }
    }

    /**
     * Get a string parameter
     */
    public function getString(string $key, string $default = ''): string
    {
        $value = $this->get($key, $default);

        return \is_string($value) ? $value : $default;
    }

    /**
     * Get a boolean parameter
     * Returns default if value is not valid
     */
    public function getBool(string $key, bool $default = false): bool
    {
        try {
            // Check POST first, then GET
            if ($this->request->request->has($key)) {
                return $this->request->request->getBoolean($key, $default);
            }

            return $this->request->query->getBoolean($key, $default);
        } catch (UnexpectedValueException) {
            return $default;
        }
    }

    /**
     * Get a float parameter
     */
    public function getFloat(string $key, float $default = 0.0): float
    {
        $value = $this->get($key, $default);

        return is_numeric($value) ? (float)$value : $default;
    }

    /**
     * Get an array parameter
     */
    public function getArray(string $key, array $default = []): array
    {
        // Get all parameters and extract the key - more reliable than all($key)
        $postData = $this->request->request->all();
        if (isset($postData[$key]) && \is_array($postData[$key])) {
            return $postData[$key];
        }

        $queryData = $this->request->query->all();
        if (isset($queryData[$key]) && \is_array($queryData[$key])) {
            return $queryData[$key];
        }

        return $default;
    }

    // ========================================
    // Request metadata
    // ========================================

    /**
     * Get HTTP method (GET, POST, etc.)
     */
    public function getMethod(): string
    {
        return $this->request->getMethod();
    }

    /**
     * Check if the request method is POST
     */
    public function isPost(): bool
    {
        return $this->request->isMethod('POST');
    }

    /**
     * Check if the request method is GET
     */
    public function isGet(): bool
    {
        return $this->request->isMethod('GET');
    }

    /**
     * Check if this is an AJAX request
     */
    public function isAjax(): bool
    {
        return $this->request->isXmlHttpRequest();
    }

    /**
     * Check if the request is secure (HTTPS)
     */
    public function isSecure(): bool
    {
        return $this->request->isSecure();
    }

    /**
     * Get client IP address
     * Handles proxies if configured with setTrustedProxies()
     */
    public function getClientIp(): ?string
    {
        return $this->request->getClientIp();
    }

    /**
     * Get request URI (path + query string)
     */
    public function getRequestUri(): string
    {
        return $this->request->getRequestUri();
    }

    /**
     * Get a request path (without query string)
     */
    public function getPathInfo(): string
    {
        return $this->request->getPathInfo();
    }

    /**
     * Get query string
     */
    public function getQueryString(): ?string
    {
        return $this->request->getQueryString();
    }

    /**
     * Get host name
     */
    public function getHost(): string
    {
        return $this->request->getHost();
    }

    /**
     * Get scheme (http or https)
     */
    public function getScheme(): string
    {
        return $this->request->getScheme();
    }

    /**
     * Get user agent string
     */
    public function getUserAgent(): ?string
    {
        return $this->request->headers->get('User-Agent');
    }

    /**
     * Get referer URL
     */
    public function getReferer(): ?string
    {
        return $this->request->headers->get('Referer');
    }

    /**
     * Get content type
     */
    public function getContentType(): ?string
    {
        return $this->request->getContentTypeFormat();
    }

    /**
     * Get raw request content (body)
     */
    public function getContent(): string
    {
        return $this->request->getContent();
    }

    // ========================================
    // File uploads
    // ========================================

    /**
     * Get uploaded file as legacy array format (like $_FILES)
     *
     * Returns an array with keys: name, type, tmp_name, error, size
     * Returns null if a file was not uploaded
     *
     * @param string $key The form field name
     * @return array<string, mixed>|null Legacy file array or null
     */
    public function getFileAsArray(string $key): ?array
    {
        $file = $this->request->files->get($key);

        if (!$file instanceof UploadedFile) {
            return null;
        }

        return [
            'name' => $file->getClientOriginalName(),
            'type' => $file->getClientMimeType(),
            'tmp_name' => $file->getPathname(),
            'error' => $file->getError(),
            'size' => $file->getSize(),
        ];
    }

    // ========================================
    // Backward compatibility
    // ========================================

    /**
     * Get the underlying Symfony Request object
     * Use for advanced operations not covered by this wrapper
     */
    public function getSymfonyRequest(): SymfonyRequest
    {
        return $this->request;
    }
}
