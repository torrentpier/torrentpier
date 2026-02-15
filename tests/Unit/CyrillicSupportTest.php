<?php

use TorrentPier\Legacy\BBCode;
use TorrentPier\Legacy\WordsRate;
use TorrentPier\Validate;

/**
 * Tests for full Cyrillic character support across the codebase.
 *
 * Ukrainian, Belarusian, and Serbian Cyrillic characters fall outside
 * the standard Russian range (U+0410-U+044F). These tests verify that
 * all regex patterns and validation rules support the full Cyrillic Unicode block.
 *
 * Key Ukrainian characters:
 *   Є/є (U+0404/U+0454), І/і (U+0406/U+0456),
 *   Ї/ї (U+0407/U+0457), Ґ/ґ (U+0490/U+0491)
 */
describe('Cyrillic Support', function () {
    describe('Validate::username() - character acceptance', function () {
        /**
         * Rebuild the username validation regex matching Validate::username().
         * The actual name_chars from Validate.php uses \p{Cyrillic} for full Unicode Cyrillic support.
         */
        function getUsernamePattern(): string
        {
            // This must mirror the $name_chars in Validate::username()
            // Using \p{Cyrillic} covers all Cyrillic scripts (Russian, Ukrainian, Belarusian, Serbian, etc.)
            return '#^[a-z0-9\p{Cyrillic}_@$%^&;(){}\#\-\'.:+ ]+$#iu';
        }

        it('accepts Russian Cyrillic usernames', function () {
            $pattern = getUsernamePattern();

            expect(preg_match($pattern, 'Привет'))->toBe(1)
                ->and(preg_match($pattern, 'пользователь'))->toBe(1)
                ->and(preg_match($pattern, 'Ёжик'))->toBe(1);
        });

        it('accepts Ukrainian Cyrillic usernames', function () {
            $pattern = getUsernamePattern();

            // Ukrainian-specific chars
            expect(preg_match($pattern, 'Їжак'))->toBe(1)
                ->and(preg_match($pattern, 'Єва'))->toBe(1)
                ->and(preg_match($pattern, 'Іван'))->toBe(1)
                ->and(preg_match($pattern, 'Ґанок'))->toBe(1);
        });

        it('accepts mixed Ukrainian-Russian usernames', function () {
            $pattern = getUsernamePattern();

            expect(preg_match($pattern, 'Українець'))->toBe(1)
                ->and(preg_match($pattern, 'гвардія'))->toBe(1)
                ->and(preg_match($pattern, 'історія'))->toBe(1);
        });

        it('accepts Belarusian Cyrillic usernames', function () {
            $pattern = getUsernamePattern();

            // Ў/ў is Belarusian-specific
            expect(preg_match($pattern, 'Беларўс'))->toBe(1);
        });

        it('accepts Serbian Cyrillic usernames', function () {
            $pattern = getUsernamePattern();

            // Serbian-specific chars: Ђ, Ј, Љ, Њ, Ћ, Џ
            expect(preg_match($pattern, 'Ђорђе'))->toBe(1)
                ->and(preg_match($pattern, 'Љубав'))->toBe(1);
        });

        it('still rejects invalid characters', function () {
            $pattern = getUsernamePattern();

            // These should fail (pipe, backtick not in allowed set)
            expect(preg_match($pattern, 'user|name'))->toBe(0)
                ->and(preg_match($pattern, 'user`name'))->toBe(0);
        });

        it('verifies Validate.php source uses \\p{Cyrillic}', function () {
            // Ensure the actual source file uses \p{Cyrillic} and not а-я
            $source = file_get_contents((new ReflectionClass(Validate::class))->getFileName());

            expect($source)->toContain('\p{Cyrillic}')
                ->and($source)->not->toMatch('/\$name_chars.*а-я/');
        });
    });

    describe('WordsRate - Cyrillic word counting', function () {
        it('counts Ukrainian words with 4+ characters', function () {
            $pattern = (new ReflectionClass(WordsRate::class))
                ->getProperty('words_cnt_exp')
                ->getDefaultValue();

            // Ukrainian words that should be counted (4+ chars)
            expect(preg_match_all($pattern, 'Їжачок'))->toBe(1)    // 6 chars
                ->and(preg_match_all($pattern, 'Єдність'))->toBe(1) // 7 chars
                ->and(preg_match_all($pattern, 'Іграшка'))->toBe(1) // 7 chars
                ->and(preg_match_all($pattern, 'Ґрунтовий'))->toBe(1); // 9 chars
        });

        it('counts mixed Ukrainian text correctly', function () {
            $pattern = (new ReflectionClass(WordsRate::class))
                ->getProperty('words_cnt_exp')
                ->getDefaultValue();

            // Sentence with multiple Ukrainian words
            $text = 'Шукаю гарну українську музику';
            $count = preg_match_all($pattern, $text);

            // "Шукаю"(5) "гарну"(5) "українську"(10) "музику"(6) = 4 words
            expect($count)->toBe(4);
        });

        it('does not count short Ukrainian words', function () {
            $pattern = (new ReflectionClass(WordsRate::class))
                ->getProperty('words_cnt_exp')
                ->getDefaultValue();

            // Short words (< 4 chars)
            expect(preg_match_all($pattern, 'їм'))->toBe(0)   // 2 chars
                ->and(preg_match_all($pattern, 'він'))->toBe(0); // 3 chars
        });
    });

    describe('BBCode URL patterns', function () {
        it('url_exp pattern matches URLs with Ukrainian characters', function () {
            // Replicate the url_exp from BBCode.php
            $url_exp = '[\w\#!$%&~/.\-;:=,?@\p{Cyrillic}()\[\]+]+?';
            $pattern = "#^{$url_exp}$#isu";

            // URLs with Ukrainian chars
            expect(preg_match($pattern, 'сайт.укр/їжак'))->toBe(1)
                ->and(preg_match($pattern, 'example.com/новини-україни'))->toBe(1)
                ->and(preg_match($pattern, 'example.com/історія'))->toBe(1);
        });

        it('make_clickable pattern matches URLs with Ukrainian path segments', function () {
            $url_regexp = "#
                (?<![\"'=])
                \\b
                (
                    https?://[\\w\\#!$%&~/.\\-;:=?@\\p{Cyrillic}()\\[\\]+]+
                )
                (?![\"']|\\[/url|\\[/img|</a)
                (?=[,!]?\\s|[\\)<!])
            #xiu";

            $text = ' https://example.com/їжак-та-ґрунт ';
            expect(preg_match($url_regexp, $text))->toBe(1);

            $text = ' https://example.com/Єдність ';
            expect(preg_match($url_regexp, $text))->toBe(1);
        });
    });

    describe('URL extraction in search word indexing', function () {
        it('URL removal pattern matches URLs with Ukrainian characters', function () {
            // Pattern from library/includes/bbcode.php:265
            $pattern = '#\b[a-z0-9]+://[\w\#!$%&~/.\-;:=,?@\p{Cyrillic}\[\]+]+(/[0-9a-z\?\.%_\-\+=&/]+)?#u';

            // Should match (and thus be removed during search indexing)
            $text = 'текст https://example.com/новини-їжак текст';
            $cleaned = preg_replace($pattern, ' ', $text);

            expect($cleaned)->toBe('текст   текст');
        });
    });

    describe('Website URL validation', function () {
        it('accepts websites with Ukrainian domain paths', function () {
            // Pattern from register.php and EditUserProfileController.php
            $pattern = '#^https?://[\w\#!$%&~/.\-;:=,?@\p{Cyrillic}\[\]+]+$#iu';

            expect(preg_match($pattern, 'https://example.com/їжак'))->toBe(1)
                ->and(preg_match($pattern, 'https://сайт.укр'))->toBe(1)
                ->and(preg_match($pattern, 'http://example.com/Єдність'))->toBe(1)
                ->and(preg_match($pattern, 'https://example.com/Ґрунт'))->toBe(1);
        });

        it('rejects invalid URLs', function () {
            $pattern = '#^https?://[\w\#!$%&~/.\-;:=,?@\p{Cyrillic}\[\]+]+$#iu';

            expect(preg_match($pattern, 'not-a-url'))->toBe(0)
                ->and(preg_match($pattern, 'ftp://example.com'))->toBe(0);
        });
    });

    describe('Consistency check: \\p{Cyrillic} covers all needed characters', function () {
        it('matches all Ukrainian-specific characters', function () {
            $pattern = '#^\p{Cyrillic}+$#u';

            // Ukrainian
            expect(preg_match($pattern, 'єіїґЄІЇҐ'))->toBe(1);
        });

        it('matches Belarusian-specific characters', function () {
            $pattern = '#^\p{Cyrillic}+$#u';

            expect(preg_match($pattern, 'ЎўІі'))->toBe(1);
        });

        it('matches Serbian-specific characters', function () {
            $pattern = '#^\p{Cyrillic}+$#u';

            expect(preg_match($pattern, 'ЂЃЅЈЉЊЋЌЏђѓѕјљњћќџ'))->toBe(1);
        });

        it('matches standard Russian characters', function () {
            $pattern = '#^\p{Cyrillic}+$#u';

            expect(preg_match($pattern, 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдеёжзийклмнопрстуфхцчшщъыьэюя'))->toBe(1);
        });

        it('does NOT match Latin or special characters', function () {
            $pattern = '#^\p{Cyrillic}+$#u';

            expect(preg_match($pattern, 'abc'))->toBe(0)
                ->and(preg_match($pattern, '123'))->toBe(0)
                ->and(preg_match($pattern, '@#$'))->toBe(0);
        });
    });
});
