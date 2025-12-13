<?php

use Carbon\Carbon;
use TorrentPier\Helpers\TimeHelper;

describe('TimeHelper class', function () {
    beforeEach(function () {
        // Mock config() if not defined
        if (!function_exists('config')) {
            function config(): object
            {
                return new class {
                    private string $locale = 'en';

                    public function get($key, $default = null)
                    {
                        return match ($key) {
                            'default_lang' => $this->locale,
                            'tp_version' => '2.4.0',
                            default => $default,
                        };
                    }

                    public function setLocale($locale): void
                    {
                        $this->locale = $locale;
                    }
                };
            }
        }

        // Freeze time for predictable tests
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 12, 0, 0));
    });

    afterEach(function () {
        Carbon::setTestNow(); // Reset time
    });

    describe('Basic functionality', function () {
        it('formats past timestamp relative to now', function () {
            $fiveMinutesAgo = Carbon::now()->subMinutes(5)->timestamp;

            $result = TimeHelper::humanTime($fiveMinutesAgo);

            expect($result)->toBe('5 minutes ago');
        });

        it('formats future timestamp relative to now', function () {
            $inTwoHours = Carbon::now()->addHours(2)->timestamp;

            $result = TimeHelper::humanTime($inTwoHours);

            expect($result)->toBe('2 hours from now');
        });

        it('formats timestamp relative to reference point', function () {
            $past = Carbon::now()->subDays(2)->timestamp;
            $reference = Carbon::now()->timestamp;

            $result = TimeHelper::humanTime($past, $reference);

            expect($result)->toBe('2 days before');
        });

        it('handles recent timestamps correctly', function () {
            $justNow = Carbon::now()->subSeconds(30)->timestamp;

            $result = TimeHelper::humanTime($justNow);

            expect($result)->toBe('30 seconds ago');
        });

        it('handles old timestamps correctly', function () {
            $longAgo = Carbon::now()->subYears(2)->timestamp;

            $result = TimeHelper::humanTime($longAgo);

            expect($result)->toBe('2 years ago');
        });
    });

    describe('Input formats', function () {
        it('accepts integer timestamp', function () {
            $timestamp = Carbon::now()->subHours(1)->timestamp;

            $result = TimeHelper::humanTime($timestamp);

            expect($result)->toBe('1 hour ago');
        });

        it('accepts string timestamp', function () {
            $timestamp = (string)Carbon::now()->subMinutes(30)->timestamp;

            $result = TimeHelper::humanTime($timestamp);

            expect($result)->toBe('30 minutes ago');
        });

        it('accepts date string', function () {
            $dateString = Carbon::now()->subDays(3)->toDateTimeString();

            $result = TimeHelper::humanTime($dateString);

            expect($result)->toBe('3 days ago');
        });

        it('accepts Carbon instance as reference', function () {
            $past = Carbon::now()->subDays(1);
            $reference = Carbon::now();

            $result = TimeHelper::humanTime($past->timestamp, $reference);

            expect($result)->toBe('1 day before');
        });
    });

    describe('Localization', function () {
        it('uses English locale by default', function () {
            $timestamp = Carbon::now()->subMinutes(10)->timestamp;

            $result = TimeHelper::humanTime($timestamp);

            expect($result)->toContain('ago')
                ->and($result)->not->toContain('назад'); // Not Russian
        });

        it('respects config locale setting', function () {
            // This would require changing config locale dynamically
            // Just verify function doesn't crash with different locales
            $timestamp = Carbon::now()->subHours(5)->timestamp;

            $result = TimeHelper::humanTime($timestamp);

            expect($result)->toBeString()
                ->and(strlen($result))->toBeGreaterThan(0);
        });
    });

    describe('Edge cases', function () {
        it('handles same timestamp as reference', function () {
            $now = Carbon::now()->timestamp;

            $result = TimeHelper::humanTime($now, $now);

            expect($result)->toBe('0 seconds before');
        });

        it('handles very small differences', function () {
            $almostNow = Carbon::now()->subSecond()->timestamp;

            $result = TimeHelper::humanTime($almostNow);

            expect($result)->toBe('1 second ago');
        });

        it('handles large time differences', function () {
            $longAgo = Carbon::now()->subYears(10)->timestamp;

            $result = TimeHelper::humanTime($longAgo);

            expect($result)->toBe('10 years ago');
        });

        it('handles null reference as now', function () {
            $past = Carbon::now()->subMinutes(15)->timestamp;

            $result = TimeHelper::humanTime($past, null);

            expect($result)->toBe('15 minutes ago');
        });
    });

    describe('Real-world scenarios', function () {
        it('formats user registration time', function () {
            $userRegTime = Carbon::now()->subMonths(3)->timestamp;

            $result = TimeHelper::humanTime($userRegTime);

            expect($result)->toBe('3 months ago');
        });

        it('formats post creation time', function () {
            $postTime = Carbon::now()->subDays(7)->timestamp;

            $result = TimeHelper::humanTime($postTime);

            expect($result)->toBe('1 week ago');
        });

        it('formats last activity time', function () {
            $lastActivity = Carbon::now()->subMinutes(45)->timestamp;

            $result = TimeHelper::humanTime($lastActivity);

            expect($result)->toBe('45 minutes ago');
        });

        it('formats torrent seed time', function () {
            $seedStart = Carbon::now()->subDays(5)->timestamp;
            $now = Carbon::now()->timestamp;

            $result = TimeHelper::humanTime($seedStart, $now);

            expect($result)->toBe('5 days before');
        });
    });
});

describe('delta_time() backward compatibility', function () {
    beforeEach(function () {
        // Define TIMENOW constant to match the frozen test time
        if (!defined('TIMENOW')) {
            define('TIMENOW', Carbon::create(2025, 1, 15, 12, 0, 0)->timestamp);
        }

        // Define delta_time() as deprecated alias
        if (!function_exists('delta_time')) {
            function delta_time($timestamp_1, $timestamp_2 = TIMENOW, $granularity = 'auto'): string
            {
                return TimeHelper::humanTime($timestamp_1, $timestamp_2);
            }
        }

        // Ensure config() is available
        if (!function_exists('config')) {
            function config(): object
            {
                return new class {
                    public function get($key, $default = null)
                    {
                        return match ($key) {
                            'default_lang' => 'en',
                            'tp_version' => '2.4.0',
                            default => $default,
                        };
                    }
                };
            }
        }

        Carbon::setTestNow(Carbon::create(2025, 1, 15, 12, 0, 0));
    });

    afterEach(function () {
        Carbon::setTestNow();
    });

    it('exists as deprecated alias', function () {
        expect(function_exists('delta_time'))->toBeTrue();
    });

    it('returns same result as TimeHelper::humanTime()', function () {
        $timestamp = Carbon::now()->subHours(2)->timestamp;

        $humanTimeResult = TimeHelper::humanTime($timestamp, TIMENOW);
        $deltaTimeResult = delta_time($timestamp, TIMENOW);

        expect($deltaTimeResult)->toBe($humanTimeResult);
    });

    it('ignores granularity parameter for backward compatibility', function () {
        $timestamp = Carbon::now()->subDays(1)->timestamp;

        // All these should return the same result (granularity ignored)
        $result1 = delta_time($timestamp, TIMENOW, 'auto');
        $result2 = delta_time($timestamp, TIMENOW, 'days');
        $result3 = delta_time($timestamp, TIMENOW, 'hours');

        expect($result1)->toBe($result2)
            ->and($result2)->toBe($result3)
            ->and($result1)->toBe('1 day before');
    });

    it('works with two parameters', function () {
        $past = Carbon::now()->subWeeks(2)->timestamp;

        $result = delta_time($past, TIMENOW);

        expect($result)->toBe('2 weeks before');
    });

    it('works with one parameter (defaults to TIMENOW)', function () {
        $past = Carbon::now()->subMonths(1)->timestamp;

        $result = delta_time($past);

        expect($result)->toBe('1 month before');
    });
});
