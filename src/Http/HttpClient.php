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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use LogicException;

use const PHP_ROUND_HALF_UP;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use TorrentPier\Http\Exception\HttpClientException;

/**
 * HTTP Client (singleton)
 * Centralized HTTP client based on Guzzle with retry logic, error handling, and logging
 */
final class HttpClient
{
    private static ?self $instance = null;
    private Client $client;

    /**
     * Default timeout for HTTP requests (in seconds)
     */
    private const int DEFAULT_TIMEOUT = 10;

    /**
     * Default connection timeout (in seconds)
     */
    private const int DEFAULT_CONNECT_TIMEOUT = 5;

    /**
     * Default number of retry attempts
     */
    private const int DEFAULT_MAX_RETRIES = 3;


    /**
     * Private constructor to prevent direct instantiation
     *
     * @param array $config Additional Guzzle configuration options
     */
    private function __construct(array $config = [])
    {
        $handler = HandlerStack::create();

        // Add retry middleware
        $handler->push(Middleware::retry(
            $this->retryDecider(),
            $this->retryDelay()
        ));

        // Add logging middleware
        $handler->push($this->loggingMiddleware());

        // Merge default config with custom config
        $defaultConfig = [
            'handler' => $handler,
            'timeout' => self::DEFAULT_TIMEOUT,
            'connect_timeout' => self::DEFAULT_CONNECT_TIMEOUT,
            'headers' => [
                'User-Agent' => APP_NAME . '/' . config()->get('tp_version', 'Unknown'),
            ],
            'http_errors' => false, // Don't throw exceptions on 4xx/5xx responses
            'verify' => true, // Verify SSL certificates
        ];

        $this->client = new Client(array_merge($defaultConfig, $config));
    }

    /**
     * Get a singleton instance
     *
     * @param array $config Additional Guzzle configuration (only used on the first call)
     * @return self
     */
    public static function getInstance(array $config = []): self
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * Reset singleton instance (useful for testing)
     *
     * @return void
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }

    /**
     * Create a simple Guzzle client without retry middleware
     * Use this for services that need fast fail without retries
     *
     * @param array $config Guzzle configuration options
     * @return Client
     */
    public static function createSimpleClient(array $config = []): Client
    {
        $defaultConfig = [
            'timeout' => self::DEFAULT_TIMEOUT,
            'connect_timeout' => self::DEFAULT_CONNECT_TIMEOUT,
            'http_errors' => false,
            'verify' => true,
        ];

        return new Client(array_merge($defaultConfig, $config));
    }

    /**
     * Get the underlying Guzzle client
     * Use this for advanced Guzzle features
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Perform a GET request
     *
     * @param string $uri URI to request
     * @param array $options Request options (headers, query, etc.)
     * @return ResponseInterface
     * @throws HttpClientException
     */
    public function get(string $uri, array $options = []): ResponseInterface
    {
        return $this->request('GET', $uri, $options);
    }

    /**
     * Perform a POST request
     *
     * @param string $uri URI to request
     * @param array $options Request options (headers, form_params, json, multipart, etc.)
     * @return ResponseInterface
     * @throws HttpClientException
     */
    public function post(string $uri, array $options = []): ResponseInterface
    {
        return $this->request('POST', $uri, $options);
    }

    /**
     * Perform a PUT request
     *
     * @param string $uri URI to request
     * @param array $options Request options
     * @return ResponseInterface
     * @throws HttpClientException
     */
    public function put(string $uri, array $options = []): ResponseInterface
    {
        return $this->request('PUT', $uri, $options);
    }

    /**
     * Perform a PATCH request
     *
     * @param string $uri URI to request
     * @param array $options Request options
     * @return ResponseInterface
     * @throws HttpClientException
     */
    public function patch(string $uri, array $options = []): ResponseInterface
    {
        return $this->request('PATCH', $uri, $options);
    }

    /**
     * Perform a DELETE request
     *
     * @param string $uri URI to request
     * @param array $options Request options
     * @return ResponseInterface
     * @throws HttpClientException
     */
    public function delete(string $uri, array $options = []): ResponseInterface
    {
        return $this->request('DELETE', $uri, $options);
    }

    /**
     * Perform a HEAD request
     *
     * @param string $uri URI to request
     * @param array $options Request options
     * @return ResponseInterface
     * @throws HttpClientException
     */
    public function head(string $uri, array $options = []): ResponseInterface
    {
        return $this->request('HEAD', $uri, $options);
    }

    /**
     * Perform an HTTP request
     *
     * @param string $method HTTP method
     * @param string $uri URI to request
     * @param array $options Request options
     * @return ResponseInterface
     * @throws HttpClientException
     */
    public function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        try {
            return $this->client->request($method, $uri, $options);
        } catch (GuzzleException $e) {
            throw new HttpClientException(
                "HTTP request failed: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        } catch (Throwable $e) {
            throw new HttpClientException(
                "Unexpected error during HTTP request: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Download a file to a local path with streaming support
     * Useful for large files to avoid memory issues
     *
     * @param string $uri URI to download from
     * @param string $savePath Local path to save the file
     * @param array $options Additional request options
     * @return bool True on success
     * @throws HttpClientException
     */
    public function download(string $uri, string $savePath, array $options = []): bool
    {
        try {
            // Use streaming to avoid loading an entire file into memory
            $options['sink'] = $savePath;

            $response = $this->get($uri, $options);

            // Check if the download was successful
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                return file_exists($savePath);
            }

            // Remove the failed download file if it exists
            if (file_exists($savePath)) {
                @unlink($savePath);
            }

            throw new HttpClientException(
                "Failed to download file: HTTP {$response->getStatusCode()}"
            );
        } catch (HttpClientException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new HttpClientException(
                "Failed to download file: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Download a file with progress callback support
     * Useful for providing user feedback during large downloads
     *
     * @param string $uri URI to download from
     * @param string $savePath Local path to save the file
     * @param callable|null $progressCallback Callback function (float $percent, int $downloaded, int $total)
     * @param array $options Additional request options
     * @return bool True on success
     * @throws HttpClientException
     */
    public function downloadWithProgress(
        string    $uri,
        string    $savePath,
        ?callable $progressCallback = null,
        array     $options = []
    ): bool {
        // Add a progress callback if provided
        if ($progressCallback !== null) {
            $options['progress'] = function (
                int|float $downloadTotal,
                int|float $downloadedBytes,
                int|float $uploadTotal,
                int|float $uploadedBytes
            ) use ($progressCallback): void {
                if ($downloadTotal > 0) {
                    $percent = round(($downloadedBytes / $downloadTotal) * 100, 2, PHP_ROUND_HALF_UP);
                    $progressCallback($percent, (int) $downloadedBytes, (int) $downloadTotal);
                }
            };
        }

        // Use the regular download method with progress options
        return $this->download($uri, $savePath, $options);
    }

    /**
     * Retry decider function
     * Determines whether a request should be retried based on the response
     *
     * @return callable
     */
    private function retryDecider(): callable
    {
        return function (
            int                $retries,
            RequestInterface   $request,
            ?ResponseInterface $response = null,
            ?Throwable         $exception = null
        ) {
            // Don't retry if we've exceeded max retries
            if ($retries >= self::DEFAULT_MAX_RETRIES) {
                return false;
            }

            // Retry on connection exceptions
            if ($exception !== null) {
                return true;
            }

            // Retry on server errors (5xx)
            if ($response && $response->getStatusCode() >= 500) {
                return true;
            }

            // Retry on 429 Too Many Requests
            if ($response && $response->getStatusCode() === 429) {
                return true;
            }

            return false;
        };
    }

    /**
     * Retry delay function
     * Calculates exponential backoff delay for retries
     *
     * @return callable
     */
    private function retryDelay(): callable
    {
        return function (int $retries, ?ResponseInterface $response = null) {
            // Check for Retry-After header
            if ($response && $response->hasHeader('Retry-After')) {
                $retryAfter = $response->getHeaderLine('Retry-After');
                if (is_numeric($retryAfter)) {
                    return (int) $retryAfter * 1000; // seconds -> ms
                }
                // Support http-date format
                $ts = strtotime($retryAfter);
                if ($ts !== false) {
                    $delay = max(0, $ts - time());
                    return (int) ($delay * 1000);
                }
            }

            // Exponential backoff: 2s, 4s, 8s, etc.
            return (int) (1000 * (2 ** $retries));
        };
    }

    /**
     * Logging middleware
     * Logs HTTP requests and responses for debugging
     *
     * @return callable
     */
    private function loggingMiddleware(): callable
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                // Log request if debug logging is enabled
                if (defined('IN_DEBUG_MODE') && IN_DEBUG_MODE && function_exists('bb_log')) {
                    $this->logRequest($request);
                }

                return $handler($request, $options)->then(
                    function (ResponseInterface $response) use ($request) {
                        // Log response if debug logging is enabled
                        if (defined('IN_DEBUG_MODE') && IN_DEBUG_MODE && function_exists('bb_log')) {
                            $this->logResponse($request, $response);
                        }
                        return $response;
                    }
                );
            };
        };
    }

    /**
     * Log HTTP request
     *
     * @param RequestInterface $request
     * @return void
     */
    private function logRequest(RequestInterface $request): void
    {
        $message = sprintf(
            "[HTTP] Request: %s %s",
            $request->getMethod(),
            $request->getUri()
        );

        bb_log($message . LOG_LF);
    }

    /**
     * Log HTTP response
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return void
     */
    private function logResponse(RequestInterface $request, ResponseInterface $response): void
    {
        $message = sprintf(
            "[HTTP] Response: %s %s - Status: %d - Size: %d bytes",
            $request->getMethod(),
            $request->getUri(),
            $response->getStatusCode(),
            $response->getBody()->getSize() ?? 0
        );

        bb_log($message . LOG_LF);
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent serialization of the singleton instance
     */
    public function __serialize(): array
    {
        throw new LogicException('Cannot serialize singleton');
    }

    /**
     * Prevent unserialization of the singleton instance
     */
    public function __unserialize(array $data): void
    {
        throw new LogicException('Cannot unserialize singleton');
    }
}
