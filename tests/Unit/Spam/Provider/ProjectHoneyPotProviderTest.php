<?php

use Tests\Unit\Spam\Provider\TestableProjectHoneyPotProvider;
use TorrentPier\Spam\Decision;
use TorrentPier\Spam\Provider\ProjectHoneyPotProvider;

describe('ProjectHoneyPotProvider', function () {
    describe('getName()', function () {
        it('returns project_honeypot', function () {
            $provider = new ProjectHoneyPotProvider;

            expect($provider->getName())->toBe('project_honeypot');
        });
    });

    describe('isEnabled()', function () {
        it('is disabled by default', function () {
            $provider = new ProjectHoneyPotProvider;

            expect($provider->isEnabled())->toBeFalse();
        });

        it('is enabled when config says so', function () {
            $provider = new ProjectHoneyPotProvider(['enabled' => true]);

            expect($provider->isEnabled())->toBeTrue();
        });
    });

    describe('checkUser() - No API Key', function () {
        it('returns Allowed when no API key is configured', function () {
            $provider = new TestableProjectHoneyPotProvider([
                'enabled' => true,
                // no api_key
            ]);

            $result = $provider->checkUser('user', 'user@test.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Allowed)
                ->and($result->reason)->toContain('No API key');
        });

        it('returns Allowed when API key is empty string', function () {
            $provider = new TestableProjectHoneyPotProvider([
                'enabled' => true,
                'api_key' => '',
            ]);

            $result = $provider->checkUser('user', 'user@test.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Allowed)
                ->and($result->reason)->toContain('No API key');
        });
    });

    describe('checkUser() - IPv6 Handling', function () {
        it('skips IPv6 addresses', function () {
            $provider = new TestableProjectHoneyPotProvider([
                'enabled' => true,
                'api_key' => 'testkey',
            ]);

            $result = $provider->checkUser('user', 'user@test.com', '::1');

            expect($result->decision)->toBe(Decision::Allowed)
                ->and($result->reason)->toContain('not IPv4');
        });
    });

    describe('checkUser() - Not Listed', function () {
        it('returns Allowed when IP is not listed', function () {
            $provider = new TestableProjectHoneyPotProvider([
                'enabled' => true,
                'api_key' => 'testkey',
            ]);

            // No DNS response set = not listed
            $result = $provider->checkUser('user', 'user@test.com', '8.8.8.8');

            expect($result->decision)->toBe(Decision::Allowed)
                ->and($result->reason)->toContain('Not listed');
        });

        it('returns Allowed when DNS returns non-127 response', function () {
            $provider = new TestableProjectHoneyPotProvider([
                'enabled' => true,
                'api_key' => 'testkey',
            ]);

            // 1.2.3.4 reversed = 4.3.2.1
            $provider->setDnsResponse('testkey.4.3.2.1.dnsbl.httpbl.org', '192.168.1.1');

            $result = $provider->checkUser('user', 'user@test.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Allowed);
        });
    });

    describe('checkUser() - Search Engine Detection', function () {
        it('returns Allowed for search engine (type=0)', function () {
            $provider = new TestableProjectHoneyPotProvider([
                'enabled' => true,
                'api_key' => 'testkey',
            ]);

            // Response: 127.{days}.{threat}.{type} — type 0 = search engine
            $provider->setDnsResponse('testkey.4.3.2.1.dnsbl.httpbl.org', '127.5.50.0');

            $result = $provider->checkUser('user', 'user@test.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Allowed)
                ->and($result->reason)->toContain('Search engine');
        });
    });

    describe('checkUser() - Threat Levels', function () {
        it('returns Denied for high threat + recent activity', function () {
            $provider = new TestableProjectHoneyPotProvider([
                'enabled' => true,
                'api_key' => 'testkey',
                'threat_threshold' => 25,
            ]);

            // 127.{days=10}.{threat=50}.{type=1} — high threat, recent, type 1 (suspicious)
            $provider->setDnsResponse('testkey.4.3.2.1.dnsbl.httpbl.org', '127.10.50.1');

            $result = $provider->checkUser('user', 'user@test.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Denied)
                ->and($result->reason)->toContain('threat=50')
                ->and($result->reason)->toContain('days=10');
        });

        it('returns Denied for threat at exact threshold with days <= 90', function () {
            $provider = new TestableProjectHoneyPotProvider([
                'enabled' => true,
                'api_key' => 'testkey',
                'threat_threshold' => 25,
            ]);

            // threat=25 >= threshold=25 and days=90 <= 90
            $provider->setDnsResponse('testkey.4.3.2.1.dnsbl.httpbl.org', '127.90.25.1');

            $result = $provider->checkUser('user', 'user@test.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Denied);
        });

        it('returns Moderated for moderate threat level', function () {
            $provider = new TestableProjectHoneyPotProvider([
                'enabled' => true,
                'api_key' => 'testkey',
                'threat_threshold' => 25,
            ]);

            // threat=15 >= threshold/2=12.5 but < threshold=25 -> Moderated
            $provider->setDnsResponse('testkey.4.3.2.1.dnsbl.httpbl.org', '127.10.15.1');

            $result = $provider->checkUser('user', 'user@test.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Moderated)
                ->and($result->reason)->toContain('moderate threat');
        });

        it('returns Allowed for low threat level', function () {
            $provider = new TestableProjectHoneyPotProvider([
                'enabled' => true,
                'api_key' => 'testkey',
                'threat_threshold' => 25,
            ]);

            // threat=5 < threshold/2=12.5 -> Allowed
            $provider->setDnsResponse('testkey.4.3.2.1.dnsbl.httpbl.org', '127.10.5.1');

            $result = $provider->checkUser('user', 'user@test.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Allowed)
                ->and($result->reason)->toContain('low threat');
        });

        it('does NOT deny when days > 90 even with high threat', function () {
            $provider = new TestableProjectHoneyPotProvider([
                'enabled' => true,
                'api_key' => 'testkey',
                'threat_threshold' => 25,
            ]);

            // threat=50 >= threshold but days=91 > 90 -> NOT Denied
            // But threat=50 >= threshold/2=12 -> Moderated
            $provider->setDnsResponse('testkey.4.3.2.1.dnsbl.httpbl.org', '127.91.50.1');

            $result = $provider->checkUser('user', 'user@test.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Moderated);
        });
    });

    describe('checkUser() - Confidence Calculation', function () {
        it('calculates confidence as threat/255 * 100', function () {
            $provider = new TestableProjectHoneyPotProvider([
                'enabled' => true,
                'api_key' => 'testkey',
                'threat_threshold' => 25,
            ]);

            // threat=127
            $provider->setDnsResponse('testkey.4.3.2.1.dnsbl.httpbl.org', '127.10.127.1');

            $result = $provider->checkUser('user', 'user@test.com', '1.2.3.4');

            // min(127/255, 1.0) * 100 = ~49.8
            $expectedConfidence = (127 / 255) * 100;
            expect($result->confidence)->toBe($expectedConfidence);
        });

        it('caps confidence at 100 for max threat', function () {
            $provider = new TestableProjectHoneyPotProvider([
                'enabled' => true,
                'api_key' => 'testkey',
                'threat_threshold' => 25,
            ]);

            // threat=255 -> min(255/255, 1.0) * 100 = 100.0
            $provider->setDnsResponse('testkey.4.3.2.1.dnsbl.httpbl.org', '127.10.255.1');

            $result = $provider->checkUser('user', 'user@test.com', '1.2.3.4');

            expect($result->confidence)->toBe(100.0);
        });
    });

    describe('Default Threshold', function () {
        it('uses default threat_threshold of 25 when not configured', function () {
            $provider = new TestableProjectHoneyPotProvider([
                'enabled' => true,
                'api_key' => 'testkey',
                // no threat_threshold -> defaults to 25
            ]);

            // threat=30 > 25, days=5 <= 90 -> Denied
            $provider->setDnsResponse('testkey.4.3.2.1.dnsbl.httpbl.org', '127.5.30.1');

            $result = $provider->checkUser('user', 'user@test.com', '1.2.3.4');

            expect($result->decision)->toBe(Decision::Denied);
        });
    });
});
