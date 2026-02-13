<?php

use TorrentPier\Spam\Checker\UserChecker;
use TorrentPier\Spam\Decision;
use TorrentPier\Spam\ProviderResult;
use TorrentPier\Spam\Provider\UserProviderInterface;

// Mock config() for SpamLogger — logging disabled so DB() is never called
beforeAll(function () {
    if (!function_exists('config')) {
        function config(): object
        {
            return new class {
                public function get($key, $default = null)
                {
                    return match ($key) {
                        'spam.logging.enabled' => false,
                        default => $default,
                    };
                }
            };
        }
    }
});

describe('UserChecker', function () {
    describe('Provider Orchestration', function () {
        it('calls enabled providers and aggregates results', function () {
            $provider1 = Mockery::mock(UserProviderInterface::class);
            $provider1->shouldReceive('isEnabled')->andReturn(true);
            $provider1->shouldReceive('checkUser')
                ->with('user', 'user@test.com', '1.2.3.4')
                ->once()
                ->andReturn(new ProviderResult('p1', Decision::Allowed, 'ok', 0.0, 1.0));

            $provider2 = Mockery::mock(UserProviderInterface::class);
            $provider2->shouldReceive('isEnabled')->andReturn(true);
            $provider2->shouldReceive('checkUser')
                ->with('user', 'user@test.com', '1.2.3.4')
                ->once()
                ->andReturn(new ProviderResult('p2', Decision::Allowed, 'ok', 0.0, 2.0));

            $checker = new UserChecker([$provider1, $provider2]);
            $result = $checker->check('user', 'user@test.com', '1.2.3.4');

            expect($result->isAllowed())->toBeTrue()
                ->and($result->getProviderResults())->toHaveCount(2);
        });

        it('skips disabled providers', function () {
            $disabled = Mockery::mock(UserProviderInterface::class);
            $disabled->shouldReceive('isEnabled')->andReturn(false);
            $disabled->shouldNotReceive('checkUser');

            $enabled = Mockery::mock(UserProviderInterface::class);
            $enabled->shouldReceive('isEnabled')->andReturn(true);
            $enabled->shouldReceive('checkUser')
                ->once()
                ->andReturn(new ProviderResult('enabled', Decision::Allowed, 'ok', 0.0, 1.0));

            $checker = new UserChecker([$disabled, $enabled]);
            $result = $checker->check('user', 'user@test.com', '1.2.3.4');

            expect($result->getProviderResults())->toHaveCount(1)
                ->and($result->getProviderResults()[0]->providerName)->toBe('enabled');
        });

        it('returns Allowed when no providers are given', function () {
            $checker = new UserChecker([]);
            $result = $checker->check('user', 'user@test.com', '1.2.3.4');

            expect($result->isAllowed())->toBeTrue()
                ->and($result->getProviderResults())->toBeEmpty();
        });

        it('returns Allowed when all providers are disabled', function () {
            $disabled1 = Mockery::mock(UserProviderInterface::class);
            $disabled1->shouldReceive('isEnabled')->andReturn(false);

            $disabled2 = Mockery::mock(UserProviderInterface::class);
            $disabled2->shouldReceive('isEnabled')->andReturn(false);

            $checker = new UserChecker([$disabled1, $disabled2]);
            $result = $checker->check('user', 'user@test.com', '1.2.3.4');

            expect($result->isAllowed())->toBeTrue()
                ->and($result->getProviderResults())->toBeEmpty();
        });
    });

    describe('Short-Circuit Behavior', function () {
        it('stops after first Denied result when short-circuit is enabled', function () {
            $denier = Mockery::mock(UserProviderInterface::class);
            $denier->shouldReceive('isEnabled')->andReturn(true);
            $denier->shouldReceive('checkUser')
                ->once()
                ->andReturn(new ProviderResult('denier', Decision::Denied, 'spam', 100.0, 1.0));

            $neverCalled = Mockery::mock(UserProviderInterface::class);
            $neverCalled->shouldReceive('isEnabled')->andReturn(true);
            $neverCalled->shouldNotReceive('checkUser');

            $checker = new UserChecker([$denier, $neverCalled], shortCircuit: true);
            $result = $checker->check('user', 'user@test.com', '1.2.3.4');

            expect($result->isDenied())->toBeTrue()
                ->and($result->getProviderResults())->toHaveCount(1);
        });

        it('does NOT short-circuit on Moderated', function () {
            $moderator = Mockery::mock(UserProviderInterface::class);
            $moderator->shouldReceive('isEnabled')->andReturn(true);
            $moderator->shouldReceive('checkUser')
                ->once()
                ->andReturn(new ProviderResult('mod', Decision::Moderated, 'iffy', 60.0, 1.0));

            $secondProvider = Mockery::mock(UserProviderInterface::class);
            $secondProvider->shouldReceive('isEnabled')->andReturn(true);
            $secondProvider->shouldReceive('checkUser')
                ->once()
                ->andReturn(new ProviderResult('second', Decision::Allowed, 'ok', 0.0, 2.0));

            $checker = new UserChecker([$moderator, $secondProvider], shortCircuit: true);
            $result = $checker->check('user', 'user@test.com', '1.2.3.4');

            expect($result->isModerated())->toBeTrue()
                ->and($result->getProviderResults())->toHaveCount(2);
        });

        it('continues after Denied when short-circuit is disabled', function () {
            $denier = Mockery::mock(UserProviderInterface::class);
            $denier->shouldReceive('isEnabled')->andReturn(true);
            $denier->shouldReceive('checkUser')
                ->once()
                ->andReturn(new ProviderResult('denier', Decision::Denied, 'spam', 100.0, 1.0));

            $alsoCalled = Mockery::mock(UserProviderInterface::class);
            $alsoCalled->shouldReceive('isEnabled')->andReturn(true);
            $alsoCalled->shouldReceive('checkUser')
                ->once()
                ->andReturn(new ProviderResult('also', Decision::Allowed, 'ok', 0.0, 2.0));

            $checker = new UserChecker([$denier, $alsoCalled], shortCircuit: false);
            $result = $checker->check('user', 'user@test.com', '1.2.3.4');

            expect($result->isDenied())->toBeTrue()
                ->and($result->getProviderResults())->toHaveCount(2);
        });
    });

    describe('Result Aggregation', function () {
        it('aggregates multiple results with escalation', function () {
            $clean = Mockery::mock(UserProviderInterface::class);
            $clean->shouldReceive('isEnabled')->andReturn(true);
            $clean->shouldReceive('checkUser')
                ->andReturn(new ProviderResult('clean', Decision::Allowed, 'ok', 0.0, 5.0));

            $suspicious = Mockery::mock(UserProviderInterface::class);
            $suspicious->shouldReceive('isEnabled')->andReturn(true);
            $suspicious->shouldReceive('checkUser')
                ->andReturn(new ProviderResult('sus', Decision::Moderated, 'iffy', 70.0, 10.0));

            $checker = new UserChecker([$clean, $suspicious], shortCircuit: false);
            $result = $checker->check('user', 'user@test.com', '1.2.3.4');

            expect($result->isModerated())->toBeTrue()
                ->and($result->getTotalTimeMs())->toBe(15.0)
                ->and($result->getDecisiveProvider()->providerName)->toBe('sus');
        });
    });

    afterEach(function () {
        Mockery::close();
    });
});
