<?php

use TorrentPier\Auth\TwoFactor\Encryption;
use TorrentPier\Auth\TwoFactor\RecoveryCodes;
use TorrentPier\Auth\TwoFactor\TotpService;
use TorrentPier\Auth\TwoFactor\TwoFactorAuth;
use TorrentPier\Config;

// Define CACHE stub for tests (TwoFactorAuth calls it for rate limiting)
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

// Define eloquent stub for tests that interact with DB
if (!function_exists('eloquent')) {
    function eloquent()
    {
        return $GLOBALS['__test_eloquent_mock'] ?? throw new RuntimeException('eloquent() mock not set');
    }
}

// Define BB_USERS constant
if (!defined('BB_USERS')) {
    define('BB_USERS', 'bb_users');
}

// Define TIMENOW constant
if (!defined('TIMENOW')) {
    define('TIMENOW', time());
}

describe('TwoFactorAuth', function () {
    function createTwoFactorAuth(
        ?Config $config = null,
        ?TotpService $totp = null,
        ?RecoveryCodes $recoveryCodes = null,
        ?Encryption $encryption = null,
    ): TwoFactorAuth {
        $config ??= Mockery::mock(Config::class);
        $totp ??= Mockery::mock(TotpService::class);
        $recoveryCodes ??= Mockery::mock(RecoveryCodes::class);
        $encryption ??= Mockery::mock(Encryption::class);

        return new TwoFactorAuth($config, $totp, $recoveryCodes, $encryption);
    }

    /**
     * Build an eloquent mock that stubs the fluent chain:
     * eloquent()->table(BB_USERS)->where('user_id', $userId)->first/update(...)
     */
    function mockEloquent(int $userId, string $terminalMethod, mixed $terminalReturn, array $terminalArgs = []): void
    {
        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')
            ->with('user_id', $userId)
            ->andReturnSelf();

        if (!empty($terminalArgs)) {
            $queryMock->shouldReceive($terminalMethod)
                ->with(...$terminalArgs)
                ->andReturn($terminalReturn);
        } else {
            $queryMock->shouldReceive($terminalMethod)
                ->andReturn($terminalReturn);
        }

        $managerMock = Mockery::mock();
        $managerMock->shouldReceive('table')
            ->with('users')
            ->andReturn($queryMock);

        $GLOBALS['__test_eloquent_mock'] = $managerMock;
    }

    /**
     * Build an eloquent mock that expects multiple chained calls (for enableForUser).
     */
    function mockEloquentMultiUpdate(int $userId, array $expectedUpdates): void
    {
        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')
            ->with('user_id', $userId)
            ->andReturnSelf();

        foreach ($expectedUpdates as $update) {
            $queryMock->shouldReceive('update')
                ->with($update)
                ->once()
                ->andReturn(1);
        }

        $managerMock = Mockery::mock();
        $managerMock->shouldReceive('table')
            ->with('users')
            ->andReturn($queryMock);

        $GLOBALS['__test_eloquent_mock'] = $managerMock;
    }

    describe('Feature toggle', function () {
        it('returns true when config enables 2FA', function () {
            $config = Mockery::mock(Config::class);
            $config->shouldReceive('get')
                ->with('auth.two_factor.enabled')
                ->andReturn(true);

            $tfa = createTwoFactorAuth(config: $config);

            expect($tfa->isFeatureEnabled())->toBeTrue();
        });

        it('returns false when config disables 2FA', function () {
            $config = Mockery::mock(Config::class);
            $config->shouldReceive('get')
                ->with('auth.two_factor.enabled')
                ->andReturn(false);

            $tfa = createTwoFactorAuth(config: $config);

            expect($tfa->isFeatureEnabled())->toBeFalse();
        });
    });

    describe('User state', function () {
        it('detects enabled from userdata array', function () {
            $tfa = createTwoFactorAuth();

            expect($tfa->isEnabled(['totp_enabled' => 1]))->toBeTrue()
                ->and($tfa->isEnabled(['totp_enabled' => true]))->toBeTrue();
        });

        it('returns false for empty/missing totp_enabled', function () {
            $tfa = createTwoFactorAuth();

            expect($tfa->isEnabled(['totp_enabled' => 0]))->toBeFalse()
                ->and($tfa->isEnabled(['totp_enabled' => false]))->toBeFalse()
                ->and($tfa->isEnabled(['totp_enabled' => null]))->toBeFalse()
                ->and($tfa->isEnabled([]))->toBeFalse();
        });

        it('detects enabled from string "1"', function () {
            $tfa = createTwoFactorAuth();

            expect($tfa->isEnabled(['totp_enabled' => '1']))->toBeTrue();
        });
    });

    describe('Delegation to TotpService', function () {
        it('delegates generateSecret()', function () {
            $totp = Mockery::mock(TotpService::class);
            $totp->shouldReceive('generateSecret')
                ->once()
                ->andReturn('GENERATED_SECRET');

            $tfa = createTwoFactorAuth(totp: $totp);

            expect($tfa->generateSecret())->toBe('GENERATED_SECRET');
        });

        it('delegates verifyCode()', function () {
            $totp = Mockery::mock(TotpService::class);
            $totp->shouldReceive('verifyCode')
                ->with('secret', '123456', 42)
                ->once()
                ->andReturn(true);

            $tfa = createTwoFactorAuth(totp: $totp);

            expect($tfa->verifyCode('secret', '123456', 42))->toBeTrue();
        });

        it('delegates formatSecretForDisplay()', function () {
            $totp = Mockery::mock(TotpService::class);
            $totp->shouldReceive('formatSecretForDisplay')
                ->with('ABCDEFGH')
                ->once()
                ->andReturn('ABCD EFGH');

            $tfa = createTwoFactorAuth(totp: $totp);

            expect($tfa->formatSecretForDisplay('ABCDEFGH'))->toBe('ABCD EFGH');
        });

        it('delegates generateQrCode()', function () {
            $totp = Mockery::mock(TotpService::class);
            $totp->shouldReceive('generateQrCode')
                ->with('testuser', 'SECRET123')
                ->once()
                ->andReturn('PNG_BINARY_DATA');

            $tfa = createTwoFactorAuth(totp: $totp);

            expect($tfa->generateQrCode('testuser', 'SECRET123'))->toBe('PNG_BINARY_DATA');
        });
    });

    describe('Delegation to Encryption', function () {
        it('delegates encryptSecret()', function () {
            $encryption = Mockery::mock(Encryption::class);
            $encryption->shouldReceive('encrypt')
                ->with('raw_secret')
                ->once()
                ->andReturn('encrypted_blob');

            $tfa = createTwoFactorAuth(encryption: $encryption);

            expect($tfa->encryptSecret('raw_secret'))->toBe('encrypted_blob');
        });

        it('delegates decryptSecret()', function () {
            $encryption = Mockery::mock(Encryption::class);
            $encryption->shouldReceive('decrypt')
                ->with('encrypted_blob')
                ->once()
                ->andReturn('raw_secret');

            $tfa = createTwoFactorAuth(encryption: $encryption);

            expect($tfa->decryptSecret('encrypted_blob'))->toBe('raw_secret');
        });
    });

    describe('Delegation to RecoveryCodes', function () {
        it('delegates verifyRecoveryCode()', function () {
            $rc = Mockery::mock(RecoveryCodes::class);
            $rc->shouldReceive('verify')
                ->with('ABCD-1234', ['hash1', 'hash2'])
                ->once()
                ->andReturn(0);

            $tfa = createTwoFactorAuth(recoveryCodes: $rc);

            expect($tfa->verifyRecoveryCode('ABCD-1234', ['hash1', 'hash2']))->toBe(0);
        });

        it('delegates consumeRecoveryCode()', function () {
            $rc = Mockery::mock(RecoveryCodes::class);
            $rc->shouldReceive('consume')
                ->with(42, 1, ['hash1', 'hash2', 'hash3'])
                ->once()
                ->andReturn(null);

            $tfa = createTwoFactorAuth(recoveryCodes: $rc);

            expect($tfa->consumeRecoveryCode(42, 1, ['hash1', 'hash2', 'hash3']))->toBeNull();
        });

        it('delegates regenerateRecoveryCodes()', function () {
            $rc = Mockery::mock(RecoveryCodes::class);
            $rc->shouldReceive('regenerateForUser')
                ->with(42)
                ->once()
                ->andReturn(['AAAA-1111', 'BBBB-2222']);

            $tfa = createTwoFactorAuth(recoveryCodes: $rc);

            expect($tfa->regenerateRecoveryCodes(42))->toBe(['AAAA-1111', 'BBBB-2222']);
        });
    });

    describe('Rate limiting', function () {
        it('returns true when under limit', function () {
            $config = Mockery::mock(Config::class);
            $config->shouldReceive('get')
                ->with('auth.two_factor.max_attempts')
                ->andReturn(5);

            $cacheMock = Mockery::mock();
            $cacheMock->shouldReceive('get')
                ->with('2fa_attempts_42')
                ->andReturn(3);

            $GLOBALS['__test_cache_mock'] = $cacheMock;

            $tfa = createTwoFactorAuth(config: $config);

            expect($tfa->checkRateLimit(42))->toBeTrue();
        });

        it('returns false when at limit', function () {
            $config = Mockery::mock(Config::class);
            $config->shouldReceive('get')
                ->with('auth.two_factor.max_attempts')
                ->andReturn(5);

            $cacheMock = Mockery::mock();
            $cacheMock->shouldReceive('get')
                ->with('2fa_attempts_99')
                ->andReturn(5);

            $GLOBALS['__test_cache_mock'] = $cacheMock;

            $tfa = createTwoFactorAuth(config: $config);

            expect($tfa->checkRateLimit(99))->toBeFalse();
        });

        it('returns false when over limit', function () {
            $config = Mockery::mock(Config::class);
            $config->shouldReceive('get')
                ->with('auth.two_factor.max_attempts')
                ->andReturn(5);

            $cacheMock = Mockery::mock();
            $cacheMock->shouldReceive('get')
                ->with('2fa_attempts_77')
                ->andReturn(10);

            $GLOBALS['__test_cache_mock'] = $cacheMock;

            $tfa = createTwoFactorAuth(config: $config);

            expect($tfa->checkRateLimit(77))->toBeFalse();
        });

        it('returns true when no attempts recorded', function () {
            $config = Mockery::mock(Config::class);
            $config->shouldReceive('get')
                ->with('auth.two_factor.max_attempts')
                ->andReturn(5);

            $cacheMock = Mockery::mock();
            $cacheMock->shouldReceive('get')
                ->with('2fa_attempts_1')
                ->andReturn(null);

            $GLOBALS['__test_cache_mock'] = $cacheMock;

            $tfa = createTwoFactorAuth(config: $config);

            expect($tfa->checkRateLimit(1))->toBeTrue();
        });

        it('increments attempts with correct key and TTL', function () {
            $config = Mockery::mock(Config::class);
            $config->shouldReceive('get')
                ->with('auth.two_factor.lockout_duration')
                ->andReturn(900);

            $cacheMock = Mockery::mock();
            $cacheMock->shouldReceive('get')
                ->with('2fa_attempts_42')
                ->andReturn(2);
            $cacheMock->shouldReceive('set')
                ->with('2fa_attempts_42', 3, 900)
                ->once()
                ->andReturn(true);

            $GLOBALS['__test_cache_mock'] = $cacheMock;

            $tfa = createTwoFactorAuth(config: $config);
            $tfa->incrementAttempts(42);

            expect(true)->toBeTrue();
        });

        it('increments from zero when no prior attempts', function () {
            $config = Mockery::mock(Config::class);
            $config->shouldReceive('get')
                ->with('auth.two_factor.lockout_duration')
                ->andReturn(600);

            $cacheMock = Mockery::mock();
            $cacheMock->shouldReceive('get')
                ->with('2fa_attempts_7')
                ->andReturn(false);
            $cacheMock->shouldReceive('set')
                ->with('2fa_attempts_7', 1, 600)
                ->once()
                ->andReturn(true);

            $GLOBALS['__test_cache_mock'] = $cacheMock;

            $tfa = createTwoFactorAuth(config: $config);
            $tfa->incrementAttempts(7);

            expect(true)->toBeTrue();
        });

        it('clears attempts from cache', function () {
            $cacheMock = Mockery::mock();
            $cacheMock->shouldReceive('rm')
                ->with('2fa_attempts_42')
                ->once()
                ->andReturn(true);

            $GLOBALS['__test_cache_mock'] = $cacheMock;

            $tfa = createTwoFactorAuth();
            $tfa->clearAttempts(42);

            expect(true)->toBeTrue();
        });
    });

    describe('isEnabledForUser (DB query)', function () {
        it('returns true when user has totp_enabled = 1', function () {
            mockEloquent(42, 'first', (object)['totp_enabled' => 1], [['totp_enabled']]);

            $tfa = createTwoFactorAuth();

            expect($tfa->isEnabledForUser(42))->toBeTrue();
        });

        it('returns false when user has totp_enabled = 0', function () {
            mockEloquent(42, 'first', (object)['totp_enabled' => 0], [['totp_enabled']]);

            $tfa = createTwoFactorAuth();

            expect($tfa->isEnabledForUser(42))->toBeFalse();
        });

        it('returns false when user not found', function () {
            mockEloquent(999, 'first', null, [['totp_enabled']]);

            $tfa = createTwoFactorAuth();

            expect($tfa->isEnabledForUser(999))->toBeFalse();
        });
    });

    describe('verifyUserCode (DB + decrypt + verify)', function () {
        it('returns true for valid code with encrypted secret in DB', function () {
            mockEloquent(42, 'first', (object)['totp_secret' => 'encrypted_secret'], [['totp_secret']]);

            $encryption = Mockery::mock(Encryption::class);
            $encryption->shouldReceive('decrypt')
                ->with('encrypted_secret')
                ->once()
                ->andReturn('raw_secret');

            $totp = Mockery::mock(TotpService::class);
            $totp->shouldReceive('verifyCode')
                ->with('raw_secret', '123456', 42)
                ->once()
                ->andReturn(true);

            $tfa = createTwoFactorAuth(totp: $totp, encryption: $encryption);

            expect($tfa->verifyUserCode(42, '123456'))->toBeTrue();
        });

        it('returns false when user has no totp_secret', function () {
            mockEloquent(42, 'first', (object)['totp_secret' => ''], [['totp_secret']]);

            $tfa = createTwoFactorAuth();

            expect($tfa->verifyUserCode(42, '123456'))->toBeFalse();
        });

        it('returns false when user row is null', function () {
            mockEloquent(42, 'first', null, [['totp_secret']]);

            $tfa = createTwoFactorAuth();

            expect($tfa->verifyUserCode(42, '123456'))->toBeFalse();
        });

        it('returns false when decryption fails', function () {
            mockEloquent(42, 'first', (object)['totp_secret' => 'corrupted_blob'], [['totp_secret']]);

            $encryption = Mockery::mock(Encryption::class);
            $encryption->shouldReceive('decrypt')
                ->with('corrupted_blob')
                ->andThrow(new RuntimeException('Failed to decrypt'));

            $tfa = createTwoFactorAuth(encryption: $encryption);

            expect($tfa->verifyUserCode(42, '123456'))->toBeFalse();
        });

        it('returns false when TOTP code is wrong', function () {
            mockEloquent(42, 'first', (object)['totp_secret' => 'enc_secret'], [['totp_secret']]);

            $encryption = Mockery::mock(Encryption::class);
            $encryption->shouldReceive('decrypt')
                ->with('enc_secret')
                ->andReturn('raw_secret');

            $totp = Mockery::mock(TotpService::class);
            $totp->shouldReceive('verifyCode')
                ->with('raw_secret', '000000', 42)
                ->andReturn(false);

            $tfa = createTwoFactorAuth(totp: $totp, encryption: $encryption);

            expect($tfa->verifyUserCode(42, '000000'))->toBeFalse();
        });
    });

    describe('enableForUser', function () {
        it('encrypts secret, generates codes, updates DB, and returns plain codes', function () {
            $encryption = Mockery::mock(Encryption::class);
            $encryption->shouldReceive('encrypt')
                ->with('MY_SECRET')
                ->once()
                ->andReturn('encrypted_MY_SECRET');

            $rc = Mockery::mock(RecoveryCodes::class);
            $rc->shouldReceive('generate')
                ->once()
                ->andReturn([
                    'plain' => ['AAAA-1111', 'BBBB-2222'],
                    'hashed' => ['$2y$hash1', '$2y$hash2'],
                ]);

            // Single update combining enable + autologin invalidation
            mockEloquent(42, 'update', 1, [[
                'totp_secret' => 'encrypted_MY_SECRET',
                'totp_enabled' => 1,
                'totp_recovery_codes' => json_encode(['$2y$hash1', '$2y$hash2']),
                'totp_enabled_at' => TIMENOW,
                'autologin_id' => '',
            ]]);

            $tfa = createTwoFactorAuth(encryption: $encryption, recoveryCodes: $rc);
            $result = $tfa->enableForUser(42, 'MY_SECRET');

            expect($result)->toBe(['AAAA-1111', 'BBBB-2222']);
        });
    });

    describe('disableForUser', function () {
        it('clears all TOTP data in DB', function () {
            $queryMock = Mockery::mock();
            $queryMock->shouldReceive('where')
                ->with('user_id', 42)
                ->andReturnSelf();
            $queryMock->shouldReceive('update')
                ->with([
                    'totp_secret' => '',
                    'totp_enabled' => 0,
                    'totp_recovery_codes' => null,
                    'totp_enabled_at' => 0,
                ])
                ->once()
                ->andReturn(1);

            $managerMock = Mockery::mock();
            $managerMock->shouldReceive('table')
                ->with('users')
                ->andReturn($queryMock);

            $GLOBALS['__test_eloquent_mock'] = $managerMock;

            $tfa = createTwoFactorAuth();
            $tfa->disableForUser(42);

            // Mockery::close() in afterEach will verify the ->once() expectation
            // Add explicit assertion to avoid "risky test" warning
            expect(true)->toBeTrue();
        });
    });

    afterEach(function () {
        unset($GLOBALS['__test_cache_mock'], $GLOBALS['__test_eloquent_mock']);

        Mockery::close();
    });
});
