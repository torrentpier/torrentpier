<?php

use TorrentPier\Spam\Decision;

describe('Decision Enum', function () {
    describe('Values', function () {
        it('has correct integer values', function () {
            expect(Decision::Allowed->value)->toBe(1)
                ->and(Decision::Moderated->value)->toBe(2)
                ->and(Decision::Denied->value)->toBe(3);
        });

        it('has exactly three cases', function () {
            expect(Decision::cases())->toHaveCount(3);
        });
    });

    describe('escalate()', function () {
        it('escalates Allowed to Denied', function () {
            $result = Decision::Allowed->escalate(Decision::Denied);

            expect($result)->toBe(Decision::Denied);
        });

        it('keeps Denied when escalating with Allowed', function () {
            $result = Decision::Denied->escalate(Decision::Allowed);

            expect($result)->toBe(Decision::Denied);
        });

        it('escalates Allowed to Moderated', function () {
            $result = Decision::Allowed->escalate(Decision::Moderated);

            expect($result)->toBe(Decision::Moderated);
        });

        it('keeps Moderated when escalating with Moderated', function () {
            $result = Decision::Moderated->escalate(Decision::Moderated);

            expect($result)->toBe(Decision::Moderated);
        });

        it('escalates Moderated to Denied', function () {
            $result = Decision::Moderated->escalate(Decision::Denied);

            expect($result)->toBe(Decision::Denied);
        });

        it('keeps Denied when escalating with Moderated', function () {
            $result = Decision::Denied->escalate(Decision::Moderated);

            expect($result)->toBe(Decision::Denied);
        });

        it('keeps Allowed when escalating with Allowed', function () {
            $result = Decision::Allowed->escalate(Decision::Allowed);

            expect($result)->toBe(Decision::Allowed);
        });

        it('keeps Denied when escalating with Denied', function () {
            $result = Decision::Denied->escalate(Decision::Denied);

            expect($result)->toBe(Decision::Denied);
        });
    });
});
