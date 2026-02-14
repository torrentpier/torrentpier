<?php

use TorrentPier\Spam\Decision;
use TorrentPier\Spam\ProviderResult;
use TorrentPier\Spam\SpamResult;

describe('SpamResult', function () {
    describe('Factory Methods', function () {
        it('creates an allowed result via allowed()', function () {
            $result = SpamResult::allowed();

            expect($result->isAllowed())->toBeTrue()
                ->and($result->isModerated())->toBeFalse()
                ->and($result->isDenied())->toBeFalse()
                ->and($result->getDecision())->toBe(Decision::Allowed)
                ->and($result->getProviderResults())->toBeEmpty();
        });

        it('creates an allowed result via create()', function () {
            $result = SpamResult::create();

            expect($result->isAllowed())->toBeTrue()
                ->and($result->getDecision())->toBe(Decision::Allowed)
                ->and($result->getProviderResults())->toBeEmpty();
        });
    });

    describe('addResult() and Decision Escalation', function () {
        it('stays Allowed when adding Allowed result', function () {
            $result = SpamResult::create();
            $result->addResult(new ProviderResult('p1', Decision::Allowed, 'ok', 0.0, 1.0));

            expect($result->isAllowed())->toBeTrue()
                ->and($result->getProviderResults())->toHaveCount(1);
        });

        it('escalates to Moderated when adding Moderated result', function () {
            $result = SpamResult::create();
            $result->addResult(new ProviderResult('p1', Decision::Allowed, 'ok', 0.0, 1.0));
            $result->addResult(new ProviderResult('p2', Decision::Moderated, 'suspicious', 50.0, 2.0));

            expect($result->isModerated())->toBeTrue()
                ->and($result->getProviderResults())->toHaveCount(2);
        });

        it('escalates to Denied when adding Denied result', function () {
            $result = SpamResult::create();
            $result->addResult(new ProviderResult('p1', Decision::Allowed, 'ok', 0.0, 1.0));
            $result->addResult(new ProviderResult('p2', Decision::Denied, 'spam', 100.0, 3.0));

            expect($result->isDenied())->toBeTrue();
        });

        it('stays Denied after escalation even if Allowed is added later', function () {
            $result = SpamResult::create();
            $result->addResult(new ProviderResult('p1', Decision::Denied, 'spam', 100.0, 1.0));
            $result->addResult(new ProviderResult('p2', Decision::Allowed, 'ok', 0.0, 2.0));

            expect($result->isDenied())->toBeTrue()
                ->and($result->getProviderResults())->toHaveCount(2);
        });

        it('escalates from Moderated to Denied', function () {
            $result = SpamResult::create();
            $result->addResult(new ProviderResult('p1', Decision::Moderated, 'iffy', 60.0, 1.0));
            $result->addResult(new ProviderResult('p2', Decision::Denied, 'spam', 99.0, 2.0));

            expect($result->isDenied())->toBeTrue();
        });
    });

    describe('getDecisiveProvider()', function () {
        it('returns null when no providers have been added', function () {
            $result = SpamResult::create();

            expect($result->getDecisiveProvider())->toBeNull();
        });

        it('returns the single provider when only one result exists', function () {
            $result = SpamResult::create();
            $provider = new ProviderResult('only_one', Decision::Denied, 'spam', 100.0, 5.0);
            $result->addResult($provider);

            expect($result->getDecisiveProvider())->toBe($provider);
        });

        it('returns the provider with the most severe decision', function () {
            $result = SpamResult::create();
            $allowed = new ProviderResult('clean', Decision::Allowed, 'ok', 0.0, 1.0);
            $moderated = new ProviderResult('iffy', Decision::Moderated, 'suspect', 60.0, 2.0);
            $denied = new ProviderResult('bad', Decision::Denied, 'spam', 100.0, 3.0);

            $result->addResult($allowed);
            $result->addResult($moderated);
            $result->addResult($denied);

            expect($result->getDecisiveProvider()->providerName)->toBe('bad')
                ->and($result->getDecisiveProvider()->decision)->toBe(Decision::Denied);
        });

        it('returns the first provider with the highest severity when tied', function () {
            $result = SpamResult::create();
            $first = new ProviderResult('first_deny', Decision::Denied, 'first', 100.0, 1.0);
            $second = new ProviderResult('second_deny', Decision::Denied, 'second', 100.0, 2.0);

            $result->addResult($first);
            $result->addResult($second);

            // The implementation iterates and uses strict >, so the first Denied stays
            expect($result->getDecisiveProvider()->providerName)->toBe('first_deny');
        });
    });

    describe('getTotalTimeMs()', function () {
        it('returns 0 when no providers have been added', function () {
            $result = SpamResult::create();

            expect($result->getTotalTimeMs())->toBe(0.0);
        });

        it('sums all provider response times', function () {
            $result = SpamResult::create();
            $result->addResult(new ProviderResult('p1', Decision::Allowed, 'ok', 0.0, 10.5));
            $result->addResult(new ProviderResult('p2', Decision::Allowed, 'ok', 0.0, 20.3));
            $result->addResult(new ProviderResult('p3', Decision::Moderated, 'iffy', 50.0, 100.0));

            expect($result->getTotalTimeMs())->toBe(130.8);
        });
    });

    describe('toArray()', function () {
        it('produces correct structure with no providers', function () {
            $result = SpamResult::create();
            $array = $result->toArray();

            expect($array)->toHaveKeys(['decision', 'total_time_ms', 'providers'])
                ->and($array['decision'])->toBe('Allowed')
                ->and($array['total_time_ms'])->toBe(0.0)
                ->and($array['providers'])->toBeEmpty();
        });

        it('produces correct structure with providers', function () {
            $result = SpamResult::create();
            $result->addResult(new ProviderResult('sfs', Decision::Moderated, 'SFS: suspicious', 75.0, 150.0));
            $result->addResult(new ProviderResult('akismet', Decision::Allowed, 'not spam', 0.0, 200.0));

            $array = $result->toArray();

            expect($array['decision'])->toBe('Moderated')
                ->and($array['total_time_ms'])->toBe(350.0)
                ->and($array['providers'])->toHaveCount(2);

            $firstProvider = $array['providers'][0];
            expect($firstProvider)->toHaveKeys(['provider', 'decision', 'reason', 'confidence', 'response_time_ms'])
                ->and($firstProvider['provider'])->toBe('sfs')
                ->and($firstProvider['decision'])->toBe('Moderated')
                ->and($firstProvider['reason'])->toBe('SFS: suspicious')
                ->and($firstProvider['confidence'])->toBe(75.0)
                ->and($firstProvider['response_time_ms'])->toBe(150.0);
        });
    });

    describe('Boolean Helpers', function () {
        it('isDenied returns true only for Denied', function () {
            $result = SpamResult::create();
            expect($result->isDenied())->toBeFalse();

            $result->addResult(new ProviderResult('p', Decision::Moderated, 'x', 0.0, 0.0));
            expect($result->isDenied())->toBeFalse();

            $result->addResult(new ProviderResult('p', Decision::Denied, 'x', 0.0, 0.0));
            expect($result->isDenied())->toBeTrue();
        });

        it('isModerated returns true only for Moderated', function () {
            $result = SpamResult::create();
            expect($result->isModerated())->toBeFalse();

            $result->addResult(new ProviderResult('p', Decision::Moderated, 'x', 0.0, 0.0));
            expect($result->isModerated())->toBeTrue();

            $result->addResult(new ProviderResult('p', Decision::Denied, 'x', 0.0, 0.0));
            expect($result->isModerated())->toBeFalse(); // now Denied, not Moderated
        });

        it('isAllowed returns true only for Allowed', function () {
            $result = SpamResult::create();
            expect($result->isAllowed())->toBeTrue();

            $result->addResult(new ProviderResult('p', Decision::Moderated, 'x', 0.0, 0.0));
            expect($result->isAllowed())->toBeFalse();
        });
    });
});
