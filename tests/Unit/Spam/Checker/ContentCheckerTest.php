<?php

use TorrentPier\Spam\Checker\ContentChecker;
use TorrentPier\Spam\Decision;
use TorrentPier\Spam\Provider\ContentProviderInterface;
use TorrentPier\Spam\ProviderResult;

// config() should already be mocked from Pest.php or UserCheckerTest;
// if not, mock it here for SpamLogger suppression
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

describe('ContentChecker', function () {
    describe('Provider Orchestration', function () {
        it('calls enabled providers and aggregates results', function () {
            $provider1 = Mockery::mock(ContentProviderInterface::class);
            $provider1->shouldReceive('isEnabled')->andReturn(true);
            $provider1->shouldReceive('checkContent')
                ->with(42, 'hello world', [])
                ->once()
                ->andReturn(new ProviderResult('p1', Decision::Allowed, 'ok', 0.0, 1.0));

            $provider2 = Mockery::mock(ContentProviderInterface::class);
            $provider2->shouldReceive('isEnabled')->andReturn(true);
            $provider2->shouldReceive('checkContent')
                ->with(42, 'hello world', [])
                ->once()
                ->andReturn(new ProviderResult('p2', Decision::Allowed, 'ok', 0.0, 2.0));

            $checker = new ContentChecker([$provider1, $provider2]);
            $result = $checker->check(42, 'hello world');

            expect($result->isAllowed())->toBeTrue()
                ->and($result->getProviderResults())->toHaveCount(2);
        });

        it('skips disabled providers', function () {
            $disabled = Mockery::mock(ContentProviderInterface::class);
            $disabled->shouldReceive('isEnabled')->andReturn(false);
            $disabled->shouldNotReceive('checkContent');

            $enabled = Mockery::mock(ContentProviderInterface::class);
            $enabled->shouldReceive('isEnabled')->andReturn(true);
            $enabled->shouldReceive('checkContent')
                ->once()
                ->andReturn(new ProviderResult('enabled', Decision::Allowed, 'ok', 0.0, 1.0));

            $checker = new ContentChecker([$disabled, $enabled]);
            $result = $checker->check(1, 'message');

            expect($result->getProviderResults())->toHaveCount(1)
                ->and($result->getProviderResults()[0]->providerName)->toBe('enabled');
        });

        it('passes extra array to providers', function () {
            $provider = Mockery::mock(ContentProviderInterface::class);
            $provider->shouldReceive('isEnabled')->andReturn(true);
            $provider->shouldReceive('checkContent')
                ->with(1, 'msg', ['ip' => '10.0.0.1', 'type' => 'forum-post'])
                ->once()
                ->andReturn(new ProviderResult('p1', Decision::Allowed, 'ok', 0.0, 1.0));

            $checker = new ContentChecker([$provider]);
            $result = $checker->check(1, 'msg', ['ip' => '10.0.0.1', 'type' => 'forum-post']);

            expect($result->isAllowed())->toBeTrue();
        });

        it('returns Allowed when no providers are given', function () {
            $checker = new ContentChecker([]);
            $result = $checker->check(1, 'message');

            expect($result->isAllowed())->toBeTrue()
                ->and($result->getProviderResults())->toBeEmpty();
        });
    });

    describe('Short-Circuit Behavior', function () {
        it('stops after first Denied result when short-circuit is enabled', function () {
            $denier = Mockery::mock(ContentProviderInterface::class);
            $denier->shouldReceive('isEnabled')->andReturn(true);
            $denier->shouldReceive('checkContent')
                ->once()
                ->andReturn(new ProviderResult('denier', Decision::Denied, 'spam', 100.0, 1.0));

            $neverCalled = Mockery::mock(ContentProviderInterface::class);
            $neverCalled->shouldReceive('isEnabled')->andReturn(true);
            $neverCalled->shouldNotReceive('checkContent');

            $checker = new ContentChecker([$denier, $neverCalled], shortCircuit: true);
            $result = $checker->check(1, 'buy cheap pills');

            expect($result->isDenied())->toBeTrue()
                ->and($result->getProviderResults())->toHaveCount(1);
        });

        it('does NOT short-circuit on Moderated', function () {
            $moderator = Mockery::mock(ContentProviderInterface::class);
            $moderator->shouldReceive('isEnabled')->andReturn(true);
            $moderator->shouldReceive('checkContent')
                ->once()
                ->andReturn(new ProviderResult('mod', Decision::Moderated, 'iffy', 60.0, 1.0));

            $second = Mockery::mock(ContentProviderInterface::class);
            $second->shouldReceive('isEnabled')->andReturn(true);
            $second->shouldReceive('checkContent')
                ->once()
                ->andReturn(new ProviderResult('second', Decision::Allowed, 'ok', 0.0, 2.0));

            $checker = new ContentChecker([$moderator, $second], shortCircuit: true);
            $result = $checker->check(1, 'somewhat suspicious');

            expect($result->isModerated())->toBeTrue()
                ->and($result->getProviderResults())->toHaveCount(2);
        });

        it('continues after Denied when short-circuit is disabled', function () {
            $denier = Mockery::mock(ContentProviderInterface::class);
            $denier->shouldReceive('isEnabled')->andReturn(true);
            $denier->shouldReceive('checkContent')
                ->once()
                ->andReturn(new ProviderResult('denier', Decision::Denied, 'spam', 100.0, 1.0));

            $alsoCalled = Mockery::mock(ContentProviderInterface::class);
            $alsoCalled->shouldReceive('isEnabled')->andReturn(true);
            $alsoCalled->shouldReceive('checkContent')
                ->once()
                ->andReturn(new ProviderResult('also', Decision::Allowed, 'ok', 0.0, 2.0));

            $checker = new ContentChecker([$denier, $alsoCalled], shortCircuit: false);
            $result = $checker->check(1, 'spam');

            expect($result->isDenied())->toBeTrue()
                ->and($result->getProviderResults())->toHaveCount(2);
        });
    });

    afterEach(function () {
        Mockery::close();
    });
});
