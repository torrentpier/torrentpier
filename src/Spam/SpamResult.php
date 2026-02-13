<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Spam;

/**
 * Aggregated result from multiple spam check providers
 */
class SpamResult
{
    /** @var ProviderResult[] */
    private array $providerResults;

    private Decision $decision;

    private ?int $logId = null;

    private function __construct(Decision $decision, array $providerResults = [])
    {
        $this->decision = $decision;
        $this->providerResults = $providerResults;
    }

    /**
     * Create a new empty spam result for aggregation
     */
    public static function create(): self
    {
        return new self(Decision::Allowed);
    }

    /**
     * Create an allowed result with no provider results
     */
    public static function allowed(): self
    {
        return new self(Decision::Allowed);
    }

    /**
     * Add a provider result and recalculate the overall decision
     */
    public function addResult(ProviderResult $result): void
    {
        $this->providerResults[] = $result;
        $this->decision = $this->decision->escalate($result->decision);
    }

    public function getDecision(): Decision
    {
        return $this->decision;
    }

    /**
     * @return ProviderResult[]
     */
    public function getProviderResults(): array
    {
        return $this->providerResults;
    }

    /**
     * Get the provider that caused the highest (most severe) decision
     */
    public function getDecisiveProvider(): ?ProviderResult
    {
        $decisive = null;

        foreach ($this->providerResults as $result) {
            if ($decisive === null || $result->decision->value > $decisive->decision->value) {
                $decisive = $result;
            }
        }

        return $decisive;
    }

    public function isDenied(): bool
    {
        return $this->decision === Decision::Denied;
    }

    public function isModerated(): bool
    {
        return $this->decision === Decision::Moderated;
    }

    public function isAllowed(): bool
    {
        return $this->decision === Decision::Allowed;
    }

    public function setLogId(?int $logId): void
    {
        $this->logId = $logId;
    }

    public function getLogId(): ?int
    {
        return $this->logId;
    }

    /**
     * Sum of all provider response times
     */
    public function getTotalTimeMs(): float
    {
        $total = 0.0;

        foreach ($this->providerResults as $result) {
            $total += $result->responseTimeMs;
        }

        return $total;
    }

    /**
     * Serialize all results for logging/JSON
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'decision' => $this->decision->name,
            'total_time_ms' => $this->getTotalTimeMs(),
            'providers' => array_map(static fn(ProviderResult $r) => [
                'provider' => $r->providerName,
                'decision' => $r->decision->name,
                'reason' => $r->reason,
                'confidence' => $r->confidence,
                'response_time_ms' => $r->responseTimeMs,
            ], $this->providerResults),
        ];
    }
}
