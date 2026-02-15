<?php

/**
 * Mock config() for BBCode tests.
 * The nofollow_url() method calls config() which isn't bootstrapped in unit tests.
 * We define it in the TorrentPier\Legacy namespace so it takes priority.
 */

namespace TorrentPier\Legacy {
    if (!\function_exists('TorrentPier\Legacy\config')) {
        function config()
        {
            return new class {
                public function get(string $key)
                {
                    return match ($key) {
                        'forum.nofollow.allowed_url' => [],
                        'forum.nofollow.disabled' => true,
                        'forum.tidy_post' => false,
                        default => null,
                    };
                }
            };
        }
    }
}

namespace {
    use TorrentPier\Legacy\BBCode;

    /**
     * Tests for BBCode URL tag processing, specifically bracket handling.
     *
     * Bug: [url=link_[on]_photo]text[/url] gets truncated because the lazy
     * regex quantifier stops at the first ] inside the URL parameter.
     *
     * @see https://torrentpier.com/threads/raznica-obrabotki-bb-tegov-img-i-url.42308/
     */
    describe('BBCode URL bracket handling', function () {
        /**
         * Helper to call the private parse() method via reflection,
         * bypassing the need for full application bootstrap.
         */
        function parseBBCode(string $text): string
        {
            $bbcode = new BBCode;
            $reflection = new ReflectionMethod($bbcode, 'parse');

            return $reflection->invoke($bbcode, $text);
        }

        // ---------------------------------------------------------------
        // Regression tests — existing functionality that must keep working
        // ---------------------------------------------------------------

        it('parses [url]http://...[/url] correctly', function () {
            $result = parseBBCode('[url]http://example.com/path[/url]');

            expect($result)->toContain('href="http://example.com/path"')
                ->and($result)->toContain('class="postLink"');
        });

        it('parses [url]www...[/url] correctly', function () {
            $result = parseBBCode('[url]www.example.com/path[/url]');

            expect($result)->toContain('href="http://www.example.com/path"')
                ->and($result)->toContain('class="postLink"');
        });

        it('parses [url=http://...]text[/url] correctly', function () {
            $result = parseBBCode('[url=http://example.com/path]click here[/url]');

            expect($result)->toContain('href="http://example.com/path"')
                ->and($result)->toContain('>click here</a>');
        });

        it('parses [url=www...]text[/url] correctly', function () {
            $result = parseBBCode('[url=www.example.com/path]click here[/url]');

            expect($result)->toContain('href="http://www.example.com/path"')
                ->and($result)->toContain('>click here</a>');
        });

        it('parses [url=#anchor]text[/url] correctly', function () {
            $result = parseBBCode('[url=#section1]go to section[/url]');

            expect($result)->toContain('href="#section1"')
                ->and($result)->toContain('>go to section</a>');
        });

        it('parses [url=http://...] with text inside [/url] correctly', function () {
            $result = parseBBCode('[url=http://example.com]some link text[/url]');

            expect($result)->toContain('href="http://example.com"')
                ->and($result)->toContain('>some link text</a>');
        });

        // ---------------------------------------------------------------
        // Bug reproduction — brackets in [url=PARAM]
        // ---------------------------------------------------------------

        it('handles brackets in [url=PARAM] — the core bug', function () {
            $result = parseBBCode('[url=http://example.com/page_[on]_photo]click here[/url]');

            expect($result)->toContain('href="http://example.com/page_[on]_photo"')
                ->and($result)->toContain('>click here</a>');
        });

        it('handles brackets in [url=PARAM] with link text', function () {
            $result = parseBBCode('[url=http://example.com/link_[on]_photo]some text[/url]');

            expect($result)->toContain('href="http://example.com/link_[on]_photo"')
                ->and($result)->toContain('>some text</a>');
        });

        it('handles multiple bracket pairs in URL parameter', function () {
            $result = parseBBCode('[url=http://example.com/[a1]_[a2]_[a3]]text[/url]');

            expect($result)->toContain('href="http://example.com/[a1]_[a2]_[a3]"')
                ->and($result)->toContain('>text</a>');
        });

        it('handles brackets in www URL parameter', function () {
            $result = parseBBCode('[url=www.example.com/page_[1]]text[/url]');

            expect($result)->toContain('href="http://www.example.com/page_[1]"')
                ->and($result)->toContain('>text</a>');
        });

        // ---------------------------------------------------------------
        // Quoted URL support — new feature for explicit bracket handling
        // ---------------------------------------------------------------

        it('parses [url="..."]text[/url] with double quotes', function () {
            $result = parseBBCode('[url="http://example.com/path"]click here[/url]');

            expect($result)->toContain('href="http://example.com/path"')
                ->and($result)->toContain('>click here</a>');
        });

        it('handles brackets in double-quoted URLs', function () {
            $result = parseBBCode('[url="http://example.com/page_[on]_photo"]click here[/url]');

            expect($result)->toContain('href="http://example.com/page_[on]_photo"')
                ->and($result)->toContain('>click here</a>');
        });

        it('handles complex brackets in double-quoted URLs', function () {
            $result = parseBBCode('[url="http://example.com/wiki/Test_(computing)_[1]"]article[/url]');

            expect($result)->toContain('href="http://example.com/wiki/Test_(computing)_[1]"')
                ->and($result)->toContain('>article</a>');
        });

        it('handles quoted www URL', function () {
            $result = parseBBCode('[url="www.example.com/[test]"]text[/url]');

            expect($result)->toContain('href="http://www.example.com/[test]"')
                ->and($result)->toContain('>text</a>');
        });

        // ---------------------------------------------------------------
        // Edge cases
        // ---------------------------------------------------------------

        it('handles URL with Cyrillic characters', function () {
            $result = parseBBCode('[url=http://example.com/путь]текст[/url]');

            expect($result)->toContain('href="http://example.com/путь"')
                ->and($result)->toContain('>текст</a>');
        });

        it('handles URL with query parameters', function () {
            $result = parseBBCode('[url=http://example.com/path?foo=bar&baz=1]click[/url]');

            expect($result)->toContain('href="http://example.com/path?foo=bar&baz=1"');
        });

        it('handles empty brackets in URL parameter', function () {
            $result = parseBBCode('[url=http://example.com/page_[]_test]click[/url]');

            expect($result)->toContain('href="http://example.com/page_[]_test"')
                ->and($result)->toContain('>click</a>');
        });

        it('does not parse text without bbcode tags', function () {
            $text = 'Just a regular text without any tags';
            $result = parseBBCode($text);

            expect($result)->toBe($text);
        });

        it('handles multiple URL tags in same text', function () {
            $text = '[url=http://a.com]first[/url] middle [url=http://b.com]second[/url]';
            $result = parseBBCode($text);

            expect($result)->toContain('href="http://a.com"')
                ->and($result)->toContain('>first</a>')
                ->and($result)->toContain('href="http://b.com"')
                ->and($result)->toContain('>second</a>');
        });

        it('handles URL with brackets alongside regular URL', function () {
            $text = '[url=http://example.com/[test]]linked[/url] and [url=http://normal.com]plain[/url]';
            $result = parseBBCode($text);

            expect($result)->toContain('href="http://example.com/[test]"')
                ->and($result)->toContain('>linked</a>')
                ->and($result)->toContain('href="http://normal.com"')
                ->and($result)->toContain('>plain</a>');
        });
    });
}
