<?php

/**
 * Mock config() for BBCode tests (same shim as BBCodeUrlTest).
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
     * Regression tests for stored XSS via [acronym="..."] title attribute.
     */
    describe('BBCode [acronym=...] title escaping', function () {
        function parseAcronym(string $text): string
        {
            $bbcode = new BBCode;
            $reflection = new ReflectionMethod($bbcode, 'parse');

            return $reflection->invoke($bbcode, $text);
        }

        function acronymAttributes(string $rendered): array
        {
            $doc = new DOMDocument;
            // wrap to make the fragment a valid HTML doc
            $ok = @$doc->loadHTML('<!doctype html><html><body>' . $rendered . '</body></html>', LIBXML_NOERROR | LIBXML_NOWARNING);
            expect($ok)->toBeTrue();

            $span = $doc->getElementsByTagName('span')->item(0);
            expect($span)->not->toBeNull();

            $attrs = [];
            foreach ($span->attributes as $a) {
                $attrs[$a->nodeName] = $a->nodeValue;
            }

            return $attrs;
        }

        it('parses benign acronym correctly', function () {
            $result = parseAcronym('[acronym="HTML"]Hyper Text Markup Language[/acronym]');
            $attrs = acronymAttributes($result);

            expect($attrs)->toHaveKey('class', 'post-acronym')
                ->and($attrs)->toHaveKey('title', 'HTML')
                ->and(array_keys($attrs))->toEqualCanonicalizing(['class', 'title']);
        });

        it('rejects onmouseover attribute injection', function () {
            $result = parseAcronym('[acronym="x" onmouseover="alert(1)"]hover[/acronym]');
            $attrs = acronymAttributes($result);

            expect(array_keys($attrs))->toEqualCanonicalizing(['class', 'title'])
                ->and($attrs)->not->toHaveKey('onmouseover');
        });

        it('rejects autofocus + onfocus attribute injection', function () {
            $result = parseAcronym('[acronym="" autofocus onfocus="alert(1)" "]y[/acronym]');
            $attrs = acronymAttributes($result);

            expect(array_keys($attrs))->toEqualCanonicalizing(['class', 'title'])
                ->and($attrs)->not->toHaveKey('autofocus')
                ->and($attrs)->not->toHaveKey('onfocus');
        });

        it('rejects apostrophe-based injection', function () {
            $result = parseAcronym("[acronym=\"' onmouseover='alert(1)\"]y[/acronym]");
            $attrs = acronymAttributes($result);

            expect(array_keys($attrs))->toEqualCanonicalizing(['class', 'title']);
        });

        it('rejects inline style injection', function () {
            $result = parseAcronym('[acronym="x" style="position:fixed;inset:0;background:red" "]click[/acronym]');
            $attrs = acronymAttributes($result);

            expect(array_keys($attrs))->toEqualCanonicalizing(['class', 'title']);
        });
    });
}
