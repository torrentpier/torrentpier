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

use TorrentPier\Spam\Decision;
use TorrentPier\Spam\ProviderResult;

class DnsBlacklistProvider extends AbstractProvider implements UserProviderInterface
{
    public function getName(): string
    {
        return 'dns_blacklist';
    }

    public function checkUser(string $username, string $email, string $ip): ProviderResult
    {
        return $this->safeExecute(function () use ($ip) {
            $start = microtime(true);

            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return new ProviderResult(
                    providerName: $this->getName(),
                    decision: Decision::Allowed,
                    reason: 'Skipped: not IPv4',
                    confidence: 0.0,
                    responseTimeMs: (microtime(true) - $start) * 1000,
                );
            }

            $cacheKey = md5($ip);
            $cached = $this->getCached($cacheKey);
            if ($cached instanceof ProviderResult) {
                return $cached;
            }

            $reversed = $this->reverseIp($ip);
            $zones = $this->config['zones'] ?? [];

            foreach ($zones as $zone) {
                $lookup = $this->dnsLookup("{$reversed}.{$zone}");

                if ($lookup !== null && str_starts_with($lookup, '127.')) {
                    $result = new ProviderResult(
                        providerName: $this->getName(),
                        decision: Decision::Denied,
                        reason: "Listed in {$zone}",
                        confidence: 100.0,
                        responseTimeMs: (microtime(true) - $start) * 1000,
                    );
                    $this->setCache($cacheKey, $result);

                    return $result;
                }
            }

            $result = new ProviderResult(
                providerName: $this->getName(),
                decision: Decision::Allowed,
                reason: 'Not listed in any DNSBL zone',
                confidence: 0.0,
                responseTimeMs: (microtime(true) - $start) * 1000,
            );
            $this->setCache($cacheKey, $result);

            return $result;
        });
    }

    public function submitSpam(string $username, string $email, string $ip): void {}
}
