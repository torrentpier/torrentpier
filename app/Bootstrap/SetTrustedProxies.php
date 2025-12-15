<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Bootstrap;

use TorrentPier\Application;

/**
 * Handle CDN/Proxy IP extraction from headers
 *
 * This bootstrapper extracts the real client IP address from
 * various CDN and proxy headers (Cloudflare, Fastly, Nginx, etc.)
 */
class SetTrustedProxies
{
    /**
     * Trusted proxy IPs (empty = trust all proxies)
     *
     * @var array<string>
     */
    private array $trustedProxies = [];

    /**
     * CDN/Proxy headers to check for client IP
     *
     * @var array<string>
     */
    private array $allowedCDNHeaders = [
        'HTTP_CF_CONNECTING_IP',      // Cloudflare
        'HTTP_FASTLY_CLIENT_IP',      // Fastly
        'HTTP_X_REAL_IP',             // Nginx
        'HTTP_X_FORWARDED_FOR',       // Standard proxy header
        // 'HTTP_TRUE_CLIENT_IP',     // Akamai
        // 'HTTP_X_CLIENT_IP',        // Custom proxy
        // 'HTTP_INCAP_CLIENT_IP',    // Incapsula
    ];

    /**
     * Bootstrap trusted proxies
     */
    public function bootstrap(Application $app): void
    {
        // Set defaults for missing $_SERVER values (CLI or malformed requests)
        $_SERVER['REMOTE_ADDR'] ??= '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] ??= '';
        $_SERVER['HTTP_REFERER'] ??= '';
        $_SERVER['SERVER_NAME'] ??= getenv('SERVER_NAME') ?: '';
        $_SERVER['SERVER_ADDR'] ??= getenv('SERVER_ADDR') ?: '';

        // Skip proxy detection in CLI mode
        if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
            return;
        }

        // Check if we should trust this proxy
        if (!empty($this->trustedProxies) && !\in_array($_SERVER['REMOTE_ADDR'], $this->trustedProxies, true)) {
            return;
        }

        // Extract client IP from CDN/proxy headers
        foreach ($this->allowedCDNHeaders as $header) {
            if (!isset($_SERVER[$header])) {
                continue;
            }

            $clientIP = $this->extractClientIP($_SERVER[$header], $header);

            if ($this->isValidPublicIP($clientIP)) {
                $_SERVER['REMOTE_ADDR'] = $clientIP;
                break;
            }
        }
    }

    /**
     * Extract client IP from header value
     */
    private function extractClientIP(string $headerValue, string $headerName): string
    {
        // X-Forwarded-For may contain multiple IPs: client, proxy1, proxy2, ...
        if ($headerName === 'HTTP_X_FORWARDED_FOR') {
            $ips = explode(',', $headerValue);

            return trim($ips[0]);
        }

        return trim($headerValue);
    }

    /**
     * Validate that IP is a valid public IP address
     */
    private function isValidPublicIP(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        ) !== false;
    }
}
