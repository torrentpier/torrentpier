<?php

declare(strict_types=1);

use TorrentPier\Http\Csrf;

describe('Csrf', function () {
    test('exposes the canonical field and header names', function () {
        expect(Csrf::FIELD)->toBe('_token');
        expect(Csrf::HEADER)->toBe('X-CSRF-Token');
    });

    test('lists state-changing HTTP methods as protected', function () {
        expect(Csrf::protectedMethods())->toBe(['POST', 'PUT', 'PATCH', 'DELETE']);
    });

    test('verify() rejects empty / non-string input', function () {
        expect(Csrf::verify(''))->toBeFalse();
        expect(Csrf::verify(null))->toBeFalse();
    });

    test('TOKEN_LENGTH is at least 32 characters', function () {
        expect(Csrf::TOKEN_LENGTH)->toBeGreaterThanOrEqual(32);
    });
});
