<?php

namespace Tests\Unit\Spam\Provider;

use TorrentPier\Spam\Provider\DnsBlacklistProvider;

/**
 * Testable subclass that overrides dnsLookup() to avoid real DNS queries.
 */
class TestableDnsBlacklistProvider extends DnsBlacklistProvider
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
