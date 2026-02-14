<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Spam\Provider;

use GuzzleHttp\Client;
use Throwable;
use TorrentPier\Http\HttpClient;
use TorrentPier\Spam\Decision;
use TorrentPier\Spam\ProviderResult;

/**
 * Base class for all spam check providers
 *
 * Provides shared functionality: HTTP client, caching, safe execution, DNS lookups
 */
abstract class AbstractProvider implements ProviderInterface
{
    private ?Client $httpClient = null;

    public function __construct(
        protected readonly array $config = [],
    ) {}

    public function isEnabled(): bool
    {
        return (bool)($this->config['enabled'] ?? false);
    }

    /**
     * Wrap a provider check in try/catch — failures always result in Allowed
     *
     * This ensures that external API failures never block user registration or posting.
     */
    protected function safeExecute(callable $fn): ProviderResult
    {
        $start = microtime(true);

        try {
            return $fn();
        } catch (Throwable $e) {
            $elapsed = (microtime(true) - $start) * 1000;

            if (\function_exists('bb_log')) {
                bb_log("[Spam] {$this->getName()} failed: {$e->getMessage()}" . LOG_LF);
            }

            return new ProviderResult(
                providerName: $this->getName(),
                decision: Decision::Allowed,
                reason: "Provider error: {$e->getMessage()}",
                confidence: 0.0,
                responseTimeMs: $elapsed,
            );
        }
    }

    /**
     * Get a simple HTTP client (no retry middleware, fast-fail)
     */
    protected function getHttpClient(): Client
    {
        if ($this->httpClient === null) {
            $this->httpClient = HttpClient::createSimpleClient([
                'timeout' => $this->config['timeout'] ?? 5,
                'connect_timeout' => 3,
            ]);
        }

        return $this->httpClient;
    }

    /**
     * Get a cached value
     */
    protected function getCached(string $key): mixed
    {
        $ttl = $this->config['cache_ttl'] ?? 0;
        if ($ttl <= 0) {
            return null;
        }

        try {
            return CACHE('bb_spam')->get($this->cacheKey($key));
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Store a value in cache
     */
    protected function setCache(string $key, mixed $value, ?int $ttl = null): void
    {
        $ttl ??= $this->config['cache_ttl'] ?? 0;
        if ($ttl <= 0) {
            return;
        }

        try {
            CACHE('bb_spam')->set($this->cacheKey($key), $value, $ttl);
        } catch (Throwable) {
            // Cache failure is non-critical
        }
    }

    /**
     * Perform a DNS A record lookup
     */
    protected function dnsLookup(string $query): ?string
    {
        $records = @dns_get_record($query, DNS_A);

        if ($records === false || $records === []) {
            return null;
        }

        return $records[0]['ip'] ?? null;
    }

    /**
     * Reverse IP octets for DNSBL queries (e.g. 1.2.3.4 → 4.3.2.1)
     */
    protected function reverseIp(string $ip): string
    {
        return implode('.', array_reverse(explode('.', $ip)));
    }

    private function cacheKey(string $key): string
    {
        return "spam:{$this->getName()}:{$key}";
    }
}
