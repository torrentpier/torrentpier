<?php

namespace Tests\Unit\Spam\Provider;

use TorrentPier\Spam\Provider\ProjectHoneyPotProvider;

/**
 * Testable subclass that overrides dnsLookup() to avoid real DNS queries.
 */
class TestableProjectHoneyPotProvider extends ProjectHoneyPotProvider
{
    private array $dnsResponses = [];

    public function setDnsResponse(string $query, ?string $response): void
    {
        $this->dnsResponses[$query] = $response;
    }

    protected function dnsLookup(string $query): ?string
    {
        return $this->dnsResponses[$query] ?? null;
    }
}
