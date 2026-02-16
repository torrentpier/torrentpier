<?php

use TorrentPier\Auth\TwoFactor\RecoveryCodes;
use TorrentPier\Config;

// Define eloquent stub for tests that interact with DB
if (!function_exists('eloquent')) {
    function eloquent()
    {
        return $GLOBALS['__test_eloquent_mock'] ?? throw new RuntimeException('eloquent() mock not set');
    }
}

describe('RecoveryCodes', function () {
    function createRecoveryCodes(int $count = 8): RecoveryCodes
    {
        $config = Mockery::mock(Config::class);
        $config->shouldReceive('get')
            ->with('auth.two_factor.recovery_codes_count')
            ->andReturn($count);

        return new RecoveryCodes($config);
    }

    describe('Generation', function () {
        it('returns correct count from config default', function () {
            $rc = createRecoveryCodes(8);
            $result = $rc->generate();

            expect($result['plain'])->toHaveCount(8)
                ->and($result['hashed'])->toHaveCount(8);
        });

        it('returns correct count with explicit parameter', function () {
            $rc = createRecoveryCodes(8);
            $result = $rc->generate(5);

            expect($result['plain'])->toHaveCount(5)
                ->and($result['hashed'])->toHaveCount(5);
        });

        it('generates plain codes in XXXX-XXXX hex format', function () {
            $rc = createRecoveryCodes(8);
            $result = $rc->generate();

            foreach ($result['plain'] as $code) {
                expect($code)->toMatch('/^[A-F0-9]{4}-[A-F0-9]{4}$/');
            }
        });

        it('generates valid bcrypt hashes', function () {
            $rc = createRecoveryCodes(8);
            $result = $rc->generate();

            foreach ($result['hashed'] as $hash) {
                // bcrypt hashes start with $2y$
                expect($hash)->toStartWith('$2y$')
                    ->and(strlen($hash))->toBe(60);
            }
        });

        it('generates unique codes within a set', function () {
            $rc = createRecoveryCodes(8);
            $result = $rc->generate();

            $unique = array_unique($result['plain']);

            expect($unique)->toHaveCount(count($result['plain']));
        });

        it('hashed codes verify against their corresponding plain codes', function () {
            $rc = createRecoveryCodes(8);
            $result = $rc->generate(4);

            for ($i = 0; $i < 4; $i++) {
                expect($rc->verify($result['plain'][$i], $result['hashed']))->toBe($i);
            }
        });

        it('uses config count of 0 to default to config value', function () {
            $rc = createRecoveryCodes(6);
            $result = $rc->generate(0);

            expect($result['plain'])->toHaveCount(6);
        });

        it('uses config count for negative parameter', function () {
            $rc = createRecoveryCodes(4);
            $result = $rc->generate(-1);

            expect($result['plain'])->toHaveCount(4);
        });
    });

    describe('Verification', function () {
        it('returns index for valid code', function () {
            $rc = createRecoveryCodes(8);
            $result = $rc->generate(3);

            $index = $rc->verify($result['plain'][1], $result['hashed']);

            expect($index)->toBe(1);
        });

        it('returns false for invalid code', function () {
            $rc = createRecoveryCodes(8);
            $result = $rc->generate(3);

            $index = $rc->verify('DEAD-BEEF', $result['hashed']);

            expect($index)->toBeFalse();
        });

        it('is case-insensitive', function () {
            $rc = createRecoveryCodes(8);
            $result = $rc->generate(3);

            $lowered = strtolower($result['plain'][0]);
            $index = $rc->verify($lowered, $result['hashed']);

            expect($index)->toBe(0);
        });

        it('accepts code without dash', function () {
            $rc = createRecoveryCodes(8);
            $result = $rc->generate(3);

            $noDash = str_replace('-', '', $result['plain'][2]);
            $index = $rc->verify($noDash, $result['hashed']);

            expect($index)->toBe(2);
        });

        it('accepts code with spaces instead of dash', function () {
            $rc = createRecoveryCodes(8);
            $result = $rc->generate(3);

            $spaced = str_replace('-', ' ', $result['plain'][0]);
            $index = $rc->verify($spaced, $result['hashed']);

            expect($index)->toBe(0);
        });

        it('handles leading/trailing whitespace', function () {
            $rc = createRecoveryCodes(8);
            $result = $rc->generate(3);

            $padded = '  ' . $result['plain'][1] . '  ';
            $index = $rc->verify($padded, $result['hashed']);

            expect($index)->toBe(1);
        });

        it('returns false for empty code', function () {
            $rc = createRecoveryCodes(8);
            $result = $rc->generate(3);

            expect($rc->verify('', $result['hashed']))->toBeFalse();
        });

        it('returns false against empty hash array', function () {
            $rc = createRecoveryCodes(8);

            expect($rc->verify('ABCD-1234', []))->toBeFalse();
        });

        it('accepts mixed case code with spaces and no dash', function () {
            $rc = createRecoveryCodes(8);
            $result = $rc->generate(3);

            // Take a code like "A1B2-C3D4", make it "a1b2 c3d4"
            $mangled = strtolower(str_replace('-', ' ', $result['plain'][0]));
            $index = $rc->verify($mangled, $result['hashed']);

            expect($index)->toBe(0);
        });
    });

    describe('Consume (DB interaction)', function () {
        it('removes used code and updates DB with remaining codes', function () {
            $rc = createRecoveryCodes(8);

            $currentCodes = ['hash0', 'hash1', 'hash2'];
            $expectedRemaining = ['hash0', 'hash2'];

            $queryMock = Mockery::mock();
            $queryMock->shouldReceive('where')
                ->with('user_id', 42)
                ->andReturnSelf();
            $queryMock->shouldReceive('update')
                ->with(['totp_recovery_codes' => json_encode($expectedRemaining)])
                ->once()
                ->andReturn(1);

            $managerMock = Mockery::mock();
            $managerMock->shouldReceive('table')
                ->with('users')
                ->andReturn($queryMock);

            $GLOBALS['__test_eloquent_mock'] = $managerMock;

            $result = $rc->consume(42, 1, $currentCodes);

            expect($result)->toBeNull();
        });

        it('auto-regenerates when last code is consumed', function () {
            $rc = createRecoveryCodes(8);

            $currentCodes = ['only_hash'];

            // After consuming index 0, the array is empty, triggering regenerateForUser
            // regenerateForUser calls generate() then updates DB
            $queryMock = Mockery::mock();
            $queryMock->shouldReceive('where')
                ->with('user_id', 42)
                ->andReturnSelf();
            $queryMock->shouldReceive('update')
                ->once()
                ->andReturn(1);

            $managerMock = Mockery::mock();
            $managerMock->shouldReceive('table')
                ->with('users')
                ->andReturn($queryMock);

            $GLOBALS['__test_eloquent_mock'] = $managerMock;

            $result = $rc->consume(42, 0, $currentCodes);

            // Should return new plain codes (auto-regenerated)
            expect($result)->toBeArray()
                ->and($result)->toHaveCount(8)
                ->and($result[0])->toMatch('/^[A-F0-9]{4}-[A-F0-9]{4}$/');
        });

        it('removes first code correctly', function () {
            $rc = createRecoveryCodes(8);

            $currentCodes = ['hash0', 'hash1', 'hash2'];
            $expectedRemaining = ['hash1', 'hash2'];

            $queryMock = Mockery::mock();
            $queryMock->shouldReceive('where')
                ->with('user_id', 10)
                ->andReturnSelf();
            $queryMock->shouldReceive('update')
                ->with(['totp_recovery_codes' => json_encode($expectedRemaining)])
                ->once()
                ->andReturn(1);

            $managerMock = Mockery::mock();
            $managerMock->shouldReceive('table')
                ->with('users')
                ->andReturn($queryMock);

            $GLOBALS['__test_eloquent_mock'] = $managerMock;

            $result = $rc->consume(10, 0, $currentCodes);

            expect($result)->toBeNull();
        });

        it('removes last code correctly', function () {
            $rc = createRecoveryCodes(8);

            $currentCodes = ['hash0', 'hash1', 'hash2'];
            $expectedRemaining = ['hash0', 'hash1'];

            $queryMock = Mockery::mock();
            $queryMock->shouldReceive('where')
                ->with('user_id', 10)
                ->andReturnSelf();
            $queryMock->shouldReceive('update')
                ->with(['totp_recovery_codes' => json_encode($expectedRemaining)])
                ->once()
                ->andReturn(1);

            $managerMock = Mockery::mock();
            $managerMock->shouldReceive('table')
                ->with('users')
                ->andReturn($queryMock);

            $GLOBALS['__test_eloquent_mock'] = $managerMock;

            $result = $rc->consume(10, 2, $currentCodes);

            expect($result)->toBeNull();
        });
    });

    describe('RegenerateForUser (DB interaction)', function () {
        it('generates new codes and stores hashes in DB', function () {
            $rc = createRecoveryCodes(4);

            $queryMock = Mockery::mock();
            $queryMock->shouldReceive('where')
                ->with('user_id', 42)
                ->andReturnSelf();
            $queryMock->shouldReceive('update')
                ->once()
                ->andReturn(1);

            $managerMock = Mockery::mock();
            $managerMock->shouldReceive('table')
                ->with('users')
                ->andReturn($queryMock);

            $GLOBALS['__test_eloquent_mock'] = $managerMock;

            $result = $rc->regenerateForUser(42);

            expect($result)->toBeArray()
                ->and($result)->toHaveCount(4)
                ->and($result[0])->toMatch('/^[A-F0-9]{4}-[A-F0-9]{4}$/');
        });
    });

    afterEach(function () {
        unset($GLOBALS['__test_eloquent_mock']);
        Mockery::close();
    });
});
