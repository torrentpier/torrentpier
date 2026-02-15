<?php

use TorrentPier\Auth\TwoFactor\Encryption;
use TorrentPier\Config;

describe('Encryption', function () {
    function createEncryption(string $key = 'test-encryption-key-32-chars-ok!'): Encryption
    {
        $config = Mockery::mock(Config::class);
        $config->shouldReceive('get')
            ->with('auth.two_factor.encryption_key')
            ->andReturn($key);

        return new Encryption($config);
    }

    describe('Encrypt/Decrypt roundtrip', function () {
        it('preserves plaintext through encrypt/decrypt cycle', function () {
            $encryption = createEncryption();
            $plaintext = 'JBSWY3DPEHPK3PXP';

            $encrypted = $encryption->encrypt($plaintext);
            $decrypted = $encryption->decrypt($encrypted);

            expect($decrypted)->toBe($plaintext);
        });

        it('works with various plaintext lengths', function () {
            $encryption = createEncryption();

            foreach (['a', 'short', str_repeat('x', 100), str_repeat('y', 1000)] as $text) {
                $encrypted = $encryption->encrypt($text);
                expect($encryption->decrypt($encrypted))->toBe($text);
            }
        });
    });

    describe('Randomness', function () {
        it('produces different ciphertexts for different plaintexts', function () {
            $encryption = createEncryption();

            $a = $encryption->encrypt('secret_one');
            $b = $encryption->encrypt('secret_two');

            expect($a)->not->toBe($b);
        });

        it('produces different ciphertexts for same plaintext (random nonce)', function () {
            $encryption = createEncryption();
            $plaintext = 'JBSWY3DPEHPK3PXP';

            $first = $encryption->encrypt($plaintext);
            $second = $encryption->encrypt($plaintext);

            expect($first)->not->toBe($second);
        });
    });

    describe('Output format', function () {
        it('produces valid base64 output', function () {
            $encryption = createEncryption();
            $encrypted = $encryption->encrypt('test');

            $decoded = base64_decode($encrypted, true);

            expect($decoded)->not->toBeFalse()
                ->and($encrypted)->toMatch('/^[A-Za-z0-9+\/]+=*$/');
        });
    });

    describe('Decryption failures', function () {
        it('throws RuntimeException with wrong key', function () {
            $enc1 = createEncryption('key-one-that-is-long-enough-here');
            $enc2 = createEncryption('key-two-that-is-long-enough-here');

            $encrypted = $enc1->encrypt('secret');

            expect(fn () => $enc2->decrypt($encrypted))
                ->toThrow(RuntimeException::class);
        });

        it('throws RuntimeException for corrupted data', function () {
            $encryption = createEncryption();
            $encrypted = $encryption->encrypt('secret');

            // Corrupt the base64 data
            $corrupted = base64_encode(str_repeat('x', 50));

            expect(fn () => $encryption->decrypt($corrupted))
                ->toThrow(RuntimeException::class);
        });

        it('throws RuntimeException for empty string', function () {
            $encryption = createEncryption();

            expect(fn () => $encryption->decrypt(''))
                ->toThrow(RuntimeException::class);
        });

        it('throws RuntimeException for truncated data (less than nonce + tag)', function () {
            $encryption = createEncryption();

            // 12 (nonce) + 16 (tag) = 28 minimum bytes
            $tooShort = base64_encode(str_repeat('x', 20));

            expect(fn () => $encryption->decrypt($tooShort))
                ->toThrow(RuntimeException::class);
        });

        it('throws RuntimeException for invalid base64', function () {
            $encryption = createEncryption();

            expect(fn () => $encryption->decrypt('not!valid!base64!!!'))
                ->toThrow(RuntimeException::class);
        });
    });

    describe('Key validation', function () {
        it('throws RuntimeException for missing encryption key', function () {
            $config = Mockery::mock(Config::class);
            $config->shouldReceive('get')
                ->with('auth.two_factor.encryption_key')
                ->andReturn(null);

            $encryption = new Encryption($config);

            expect(fn () => $encryption->encrypt('test'))
                ->toThrow(RuntimeException::class, 'encryption key is not configured');
        });

        it('throws RuntimeException for empty encryption key', function () {
            $config = Mockery::mock(Config::class);
            $config->shouldReceive('get')
                ->with('auth.two_factor.encryption_key')
                ->andReturn('');

            $encryption = new Encryption($config);

            expect(fn () => $encryption->encrypt('test'))
                ->toThrow(RuntimeException::class, 'encryption key is not configured');
        });
    });

    describe('Key derivation', function () {
        it('derives consistent keys from same input', function () {
            // Two instances with same key should be able to cross-decrypt
            $enc1 = createEncryption('shared-key-for-both-instances!!');
            $enc2 = createEncryption('shared-key-for-both-instances!!');

            $encrypted = $enc1->encrypt('cross-test');

            expect($enc2->decrypt($encrypted))->toBe('cross-test');
        });
    });

    afterEach(function () {
        Mockery::close();
    });
});
