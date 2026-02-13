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

class ProjectHoneyPotProvider extends AbstractProvider implements UserProviderInterface
{
    public function getName(): string
    {
        return 'project_honeypot';
    }

    public function checkUser(string $username, string $email, string $ip): ProviderResult
    {
        return $this->safeExecute(function () use ($ip) {
            $start = microtime(true);

            $apiKey = $this->config['api_key'] ?? '';
            if ($apiKey === '') {
                return new ProviderResult(
                    providerName: $this->getName(),
                    decision: Decision::Allowed,
                    reason: 'No API key configured',
                    confidence: 0.0,
                    responseTimeMs: (microtime(true) - $start) * 1000,
                );
            }

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
            $lookup = $this->dnsLookup("{$apiKey}.{$reversed}.dnsbl.httpbl.org");

            if ($lookup === null || !str_starts_with($lookup, '127.')) {
                $result = new ProviderResult(
                    providerName: $this->getName(),
                    decision: Decision::Allowed,
                    reason: 'Not listed in Project Honey Pot',
                    confidence: 0.0,
                    responseTimeMs: (microtime(true) - $start) * 1000,
                );
                $this->setCache($cacheKey, $result);

                return $result;
            }

            // Response format: 127.{days_since_last_activity}.{threat_score}.{visitor_type}
            $parts = explode('.', $lookup);
            $days = (int)($parts[1] ?? 0);
            $threat = (int)($parts[2] ?? 0);
            $type = (int)($parts[3] ?? 0);

            // Type 0 = search engine — always allow
            if ($type === 0) {
                $result = new ProviderResult(
                    providerName: $this->getName(),
                    decision: Decision::Allowed,
                    reason: 'Search engine detected',
                    confidence: 0.0,
                    responseTimeMs: (microtime(true) - $start) * 1000,
                );
                $this->setCache($cacheKey, $result);

                return $result;
            }

            $threatThreshold = (int)($this->config['threat_threshold'] ?? 25);

            if ($threat >= $threatThreshold && $days <= 90) {
                $decision = Decision::Denied;
                $reason = "Honey Pot: threat={$threat}, days={$days}, type={$type}";
            } elseif ($threat >= (int)($threatThreshold / 2)) {
                $decision = Decision::Moderated;
                $reason = "Honey Pot: moderate threat={$threat}, days={$days}, type={$type}";
            } else {
                $decision = Decision::Allowed;
                $reason = "Honey Pot: low threat={$threat}, days={$days}, type={$type}";
            }

            $result = new ProviderResult(
                providerName: $this->getName(),
                decision: $decision,
                reason: $reason,
                confidence: min($threat / 255, 1.0) * 100,
                responseTimeMs: (microtime(true) - $start) * 1000,
            );
            $this->setCache($cacheKey, $result);

            return $result;
        });
    }

    public function submitSpam(string $username, string $email, string $ip): void
    {
    }
}
