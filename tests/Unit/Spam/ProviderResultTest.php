<?php

use TorrentPier\Spam\Decision;
use TorrentPier\Spam\ProviderResult;

describe('ProviderResult', function () {
    it('stores all properties correctly', function () {
        $result = new ProviderResult(
            providerName: 'test_provider',
            decision: Decision::Denied,
            reason: 'Testing denial',
            confidence: 95.5,
            responseTimeMs: 42.3,
        );

        expect($result->providerName)->toBe('test_provider')
            ->and($result->decision)->toBe(Decision::Denied)
            ->and($result->reason)->toBe('Testing denial')
            ->and($result->confidence)->toBe(95.5)
            ->and($result->responseTimeMs)->toBe(42.3);
    });

    it('can be constructed with Allowed decision', function () {
        $result = new ProviderResult(
            providerName: 'clean_provider',
            decision: Decision::Allowed,
            reason: 'All clear',
            confidence: 0.0,
            responseTimeMs: 1.0,
        );

        expect($result->decision)->toBe(Decision::Allowed)
            ->and($result->confidence)->toBe(0.0);
    });

    it('can be constructed with Moderated decision', function () {
        $result = new ProviderResult(
            providerName: 'moderate_provider',
            decision: Decision::Moderated,
            reason: 'Suspicious activity',
            confidence: 65.0,
            responseTimeMs: 100.0,
        );

        expect($result->decision)->toBe(Decision::Moderated)
            ->and($result->confidence)->toBe(65.0);
    });

    it('is readonly and cannot be modified', function () {
        $result = new ProviderResult(
            providerName: 'readonly_test',
            decision: Decision::Allowed,
            reason: 'test',
            confidence: 0.0,
            responseTimeMs: 0.0,
        );

        $reflection = new ReflectionClass($result);

        expect($reflection->isReadOnly())->toBeTrue();
    });
});
