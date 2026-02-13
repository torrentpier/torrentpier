<?php

use TorrentPier\Spam\Decision;
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

describe('DnsBlacklistProvider', function () {
    describe('getName()', function () {
        it('returns dns_blacklist', function () {
            $provider = new DnsBlacklistProvider();

            expect($provider->getName())->toBe('dns_blacklist');
        });
    });

    describe('isEnabled()', function () {
        it('is disabled by default', function () {
            $provider = new DnsBlacklistProvider();

            expect($provider->isEnabled())->toBeFalse();
        });

        it('is enabled when config says so', function () {
            $provider = new DnsBlacklistProvider(['enabled' => true]);

            expect($provider->isEnabled())->toBeTrue();
        });
    });

    describe('checkUser() - IPv4 Handling', function () {
        it('returns Denied when IP is listed in a DNSBL zone', function () {
            $provider = new TestableDnsBlacklistProvider([
                'enabled' => true,
                'zones' => ['zen.spamhaus.org'],
            ]);

            // 1.2.3.4 reversed = 4.3.2.1
            $provider->setDnsResponse('4.3.2.1.zen.spamhaus.org', '127.0.0.2');

            $result = $provider->checkUser('user', 'user@test.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Denied)
                ->and($result->reason)->toContain('zen.spamhaus.org')
                ->and($result->confidence)->toBe(100.0);
        });

        it('returns Allowed when IP is NOT listed', function () {
            $provider = new TestableDnsBlacklistProvider([
                'enabled' => true,
                'zones' => ['zen.spamhaus.org'],
            ]);

            // No DNS response set = not listed
            $result = $provider->checkUser('user', 'user@test.com', '8.8.8.8');

            expect($result->decision)->toBe(Decision::Allowed)
                ->and($result->reason)->toContain('Not listed');
        });

        it('checks multiple zones and returns Denied on first hit', function () {
            $provider = new TestableDnsBlacklistProvider([
                'enabled' => true,
                'zones' => ['zone1.test', 'zone2.test', 'zone3.test'],
            ]);

            // Only listed in zone2
            $provider->setDnsResponse('4.3.2.1.zone2.test', '127.0.0.3');

            $result = $provider->checkUser('user', 'user@test.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Denied)
                ->and($result->reason)->toContain('zone2.test');
        });

        it('returns Allowed when no zones are configured', function () {
            $provider = new TestableDnsBlacklistProvider([
                'enabled' => true,
                'zones' => [],
            ]);

            $result = $provider->checkUser('user', 'user@test.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Allowed);
        });

        it('ignores DNS responses that do not start with 127.', function () {
            $provider = new TestableDnsBlacklistProvider([
                'enabled' => true,
                'zones' => ['test.zone'],
            ]);

            // Response is not 127.x.x.x, should be treated as not listed
            $provider->setDnsResponse('4.3.2.1.test.zone', '192.168.1.1');

            $result = $provider->checkUser('user', 'user@test.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Allowed);
        });
    });

    describe('checkUser() - IPv6 Handling', function () {
        it('skips IPv6 addresses and returns Allowed', function () {
            $provider = new TestableDnsBlacklistProvider([
                'enabled' => true,
                'zones' => ['zen.spamhaus.org'],
            ]);

            $result = $provider->checkUser('user', 'user@test.com', '2001:db8::1');

            expect($result->decision)->toBe(Decision::Allowed)
                ->and($result->reason)->toContain('not IPv4');
        });

        it('skips invalid IP addresses', function () {
            $provider = new TestableDnsBlacklistProvider([
                'enabled' => true,
                'zones' => ['zen.spamhaus.org'],
            ]);

            $result = $provider->checkUser('user', 'user@test.com', 'not-an-ip');

            expect($result->decision)->toBe(Decision::Allowed)
                ->and($result->reason)->toContain('not IPv4');
        });
    });

    describe('Response Timing', function () {
        it('includes response time', function () {
            $provider = new TestableDnsBlacklistProvider([
                'enabled' => true,
                'zones' => [],
            ]);

            $result = $provider->checkUser('user', 'user@test.com', '1.2.3.4');

            expect($result->responseTimeMs)->toBeGreaterThanOrEqual(0.0);
        });
    });
});
