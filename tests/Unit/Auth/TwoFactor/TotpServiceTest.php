<?php

use OTPHP\TOTP;
use Symfony\Component\Clock\NativeClock;
use TorrentPier\Auth\TwoFactor\TotpService;
use TorrentPier\Config;

// Define CACHE stub for tests (TotpService calls it for replay protection)
// Returns a safe no-op mock when __test_cache_mock is not set
if (!function_exists('CACHE')) {
    function CACHE()
    {
        return $GLOBALS['__test_cache_mock'] ?? new class {
            public function get($key)
            {
                return null;
            }

            public function set($key, $value, $ttl = 0)
            {
                return true;
            }

            public function rm($key)
            {
                return true;
            }
        };
    }
}

describe('TotpService', function () {
    function createTotpService(): TotpService
    {
        $config = Mockery::mock(Config::class);
        $config->shouldReceive('get')->with('auth.two_factor.digits')->andReturn(6);
        $config->shouldReceive('get')->with('auth.two_factor.period')->andReturn(30);
        $config->shouldReceive('get')->with('auth.two_factor.algorithm')->andReturn('sha1');
        $config->shouldReceive('get')->with('auth.two_factor.window')->andReturn(1);
        $config->shouldReceive('get')->with('auth.two_factor.issuer')->andReturn('TorrentPier');

        return new TotpService($config, new NativeClock);
    }

    function mockCache(): void
    {
        $cacheMock = Mockery::mock();
        $cacheMock->shouldReceive('get')->andReturn(false);
        $cacheMock->shouldReceive('set')->andReturn(true);
        $GLOBALS['__test_cache_mock'] = $cacheMock;
    }

    describe('Secret generation', function () {
        it('generates a 32-char Base32 string', function () {
            $service = createTotpService();
            $secret = $service->generateSecret();

            expect($secret)->toHaveLength(32)
                ->and($secret)->toMatch('/^[A-Z2-7]+$/');
        });

        it('generates different values each call', function () {
            $service = createTotpService();

            $a = $service->generateSecret();
            $b = $service->generateSecret();

            expect($a)->not->toBe($b);
        });
    });

    describe('Secret formatting', function () {
        it('groups in 4-char blocks with spaces', function () {
            $service = createTotpService();
            $formatted = $service->formatSecretForDisplay('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP');

            $parts = explode(' ', $formatted);

            expect($parts)->each->toHaveLength(4);
        });
    });

    describe('Code verification', function () {
        it('accepts valid TOTP code', function () {
            mockCache();
            $service = createTotpService();
            $secret = $service->generateSecret();

            // Generate a real code using OTPHP directly
            $totp = TOTP::createFromSecret($secret, new NativeClock);
            $totp->setDigits(6);
            $totp->setPeriod(30);
            $totp->setDigest('sha1');
            $validCode = $totp->now();

            expect($service->verifyCode($secret, $validCode, 0))->toBeTrue();
        });

        it('rejects wrong code', function () {
            $service = createTotpService();
            $secret = $service->generateSecret();

            expect($service->verifyCode($secret, '000000', 0))->toBeFalse();
        });

        it('rejects empty code', function () {
            $service = createTotpService();
            $secret = $service->generateSecret();

            expect($service->verifyCode($secret, '', 0))->toBeFalse();
        });

        it('rejects non-numeric code', function () {
            $service = createTotpService();
            $secret = $service->generateSecret();

            expect($service->verifyCode($secret, 'abcdef', 0))->toBeFalse();
        });

        it('rejects code with wrong length (5 digits)', function () {
            $service = createTotpService();
            $secret = $service->generateSecret();

            expect($service->verifyCode($secret, '12345', 0))->toBeFalse();
        });

        it('rejects code with wrong length (7 digits)', function () {
            $service = createTotpService();
            $secret = $service->generateSecret();

            expect($service->verifyCode($secret, '1234567', 0))->toBeFalse();
        });

        it('skips replay protection when userId is 0', function () {
            // With userId=0, no cache interaction should happen for replay
            // The default no-op CACHE is enough
            $service = createTotpService();
            $secret = $service->generateSecret();

            $totp = TOTP::createFromSecret($secret, new NativeClock);
            $totp->setDigits(6);
            $totp->setPeriod(30);
            $totp->setDigest('sha1');
            $validCode = $totp->now();

            // Both should pass since userId=0 skips replay
            $first = $service->verifyCode($secret, $validCode, 0);
            $second = $service->verifyCode($secret, $validCode, 0);

            expect($first)->toBeTrue()
                ->and($second)->toBeTrue();
        });

        it('uses replay protection with userId', function () {
            $cacheMock = Mockery::mock();
            // First call: no prior use, allow it
            $cacheMock->shouldReceive('get')->andReturn(false, true);
            $cacheMock->shouldReceive('set')->andReturn(true);
            $GLOBALS['__test_cache_mock'] = $cacheMock;

            $service = createTotpService();
            $secret = $service->generateSecret();

            $totp = TOTP::createFromSecret($secret, new NativeClock);
            $totp->setDigits(6);
            $totp->setPeriod(30);
            $totp->setDigest('sha1');
            $code = $totp->now();

            // First verification should pass
            $first = $service->verifyCode($secret, $code, 42);

            // Second verification with same code should fail (replay)
            $second = $service->verifyCode($secret, $code, 42);

            expect($first)->toBeTrue()
                ->and($second)->toBeFalse();
        });
    });

    describe('Provisioning URI', function () {
        it('contains otpauth:// scheme', function () {
            $service = createTotpService();
            $secret = $service->generateSecret();

            $uri = $service->getProvisioningUri($secret, 'testuser');

            expect($uri)->toStartWith('otpauth://totp/');
        });

        it('contains issuer and label', function () {
            $service = createTotpService();
            $secret = $service->generateSecret();

            $uri = $service->getProvisioningUri($secret, 'myuser');

            expect($uri)->toContain('TorrentPier')
                ->and($uri)->toContain('myuser');
        });

        it('contains the secret parameter', function () {
            $service = createTotpService();
            $secret = $service->generateSecret();

            $uri = $service->getProvisioningUri($secret, 'testuser');

            expect($uri)->toContain('secret=' . $secret);
        });

        it('handles username with special characters', function () {
            $service = createTotpService();
            $secret = $service->generateSecret();

            // Usernames with special chars should be URL-encoded in the URI
            $uri = $service->getProvisioningUri($secret, 'user@example.com');

            expect($uri)->toStartWith('otpauth://totp/')
                ->and($uri)->toContain('secret=');
        });
    });

    describe('QR code generation', function () {
        it('returns non-empty binary PNG data', function () {
            $service = createTotpService();
            $secret = $service->generateSecret();

            $png = $service->generateQrCode('testuser', $secret);

            expect($png)->not->toBeEmpty();
        });

        it('starts with PNG header bytes', function () {
            $service = createTotpService();
            $secret = $service->generateSecret();

            $png = $service->generateQrCode('testuser', $secret);

            // PNG magic bytes: 0x89 0x50 0x4E 0x47
            expect(substr($png, 0, 4))->toBe("\x89PNG");
        });
    });

    afterEach(function () {
        unset($GLOBALS['__test_cache_mock']);
        Mockery::close();
    });
});
