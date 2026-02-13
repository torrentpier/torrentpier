<?php

use TorrentPier\Spam\Decision;
use TorrentPier\Spam\Provider\AbstractProvider;
use TorrentPier\Spam\ProviderResult;

/**
 * Concrete implementation of AbstractProvider for testing.
 *
 * Exposes protected methods as public so they can be tested directly.
 */
class ConcreteTestProvider extends AbstractProvider
{
    public function getName(): string
    {
        return 'test_provider';
    }

    /**
     * Expose safeExecute for testing
     */
    public function callSafeExecute(callable $fn): ProviderResult
    {
        return $this->safeExecute($fn);
    }

    /**
     * Expose reverseIp for testing
     */
    public function callReverseIp(string $ip): string
    {
        return $this->reverseIp($ip);
    }
}

describe('AbstractProvider', function () {
    describe('isEnabled()', function () {
        it('returns false by default (no config)', function () {
            $provider = new ConcreteTestProvider();

            expect($provider->isEnabled())->toBeFalse();
        });

        it('returns false when enabled is explicitly false', function () {
            $provider = new ConcreteTestProvider(['enabled' => false]);

            expect($provider->isEnabled())->toBeFalse();
        });

        it('returns true when enabled is true', function () {
            $provider = new ConcreteTestProvider(['enabled' => true]);

            expect($provider->isEnabled())->toBeTrue();
        });

        it('returns true when enabled is truthy (1)', function () {
            $provider = new ConcreteTestProvider(['enabled' => 1]);

            expect($provider->isEnabled())->toBeTrue();
        });

        it('returns false when enabled is falsy (0)', function () {
            $provider = new ConcreteTestProvider(['enabled' => 0]);

            expect($provider->isEnabled())->toBeFalse();
        });

        it('returns false when enabled is empty string', function () {
            $provider = new ConcreteTestProvider(['enabled' => '']);

            expect($provider->isEnabled())->toBeFalse();
        });
    });

    describe('safeExecute()', function () {
        it('returns the result from the callable on success', function () {
            $provider = new ConcreteTestProvider();

            $expected = new ProviderResult('test', Decision::Denied, 'reason', 99.0, 1.0);

            $result = $provider->callSafeExecute(fn() => $expected);

            expect($result)->toBe($expected);
        });

        it('returns Allowed on exception (fail-open)', function () {
            $provider = new ConcreteTestProvider();

            $result = $provider->callSafeExecute(function () {
                throw new RuntimeException('API connection failed');
            });

            expect($result->decision)->toBe(Decision::Allowed)
                ->and($result->providerName)->toBe('test_provider')
                ->and($result->reason)->toContain('Provider error')
                ->and($result->reason)->toContain('API connection failed')
                ->and($result->confidence)->toBe(0.0);
        });

        it('returns Allowed on TypeError', function () {
            $provider = new ConcreteTestProvider();

            $result = $provider->callSafeExecute(function () {
                throw new TypeError('Unexpected type');
            });

            expect($result->decision)->toBe(Decision::Allowed)
                ->and($result->reason)->toContain('Unexpected type');
        });

        it('includes response time in error result', function () {
            $provider = new ConcreteTestProvider();

            $result = $provider->callSafeExecute(function () {
                throw new RuntimeException('fail');
            });

            expect($result->responseTimeMs)->toBeGreaterThanOrEqual(0.0);
        });
    });

    describe('reverseIp()', function () {
        it('reverses a standard IPv4 address', function () {
            $provider = new ConcreteTestProvider();

            expect($provider->callReverseIp('1.2.3.4'))->toBe('4.3.2.1');
        });

        it('reverses a loopback address', function () {
            $provider = new ConcreteTestProvider();

            expect($provider->callReverseIp('127.0.0.1'))->toBe('1.0.0.127');
        });

        it('reverses all-zeros', function () {
            $provider = new ConcreteTestProvider();

            expect($provider->callReverseIp('0.0.0.0'))->toBe('0.0.0.0');
        });

        it('reverses 255.255.255.255', function () {
            $provider = new ConcreteTestProvider();

            expect($provider->callReverseIp('255.255.255.255'))->toBe('255.255.255.255');
        });

        it('reverses a typical public IP', function () {
            $provider = new ConcreteTestProvider();

            expect($provider->callReverseIp('192.168.1.100'))->toBe('100.1.168.192');
        });
    });

    describe('getName()', function () {
        it('returns the concrete provider name', function () {
            $provider = new ConcreteTestProvider();

            expect($provider->getName())->toBe('test_provider');
        });
    });
});
