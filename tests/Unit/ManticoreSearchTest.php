<?php

use TorrentPier\ManticoreSearch;

describe('ManticoreSearch', function () {
    // Note: ManticoreSearch class connects to Manticore database in constructor
    // which requires running Manticore server. This test only verifies
    // the class is autoloadable.

    it('class exists and is autoloadable', function () {
        expect(class_exists(ManticoreSearch::class))->toBeTrue();
    });

    it('has expected public methods defined', function () {
        $reflection = new ReflectionClass(ManticoreSearch::class);

        expect($reflection->hasMethod('search'))->toBeTrue()
            ->and($reflection->hasMethod('upsertTopic'))->toBeTrue()
            ->and($reflection->hasMethod('deleteTopic'))->toBeTrue()
            ->and($reflection->hasMethod('createIndexes'))->toBeTrue()
            ->and($reflection->hasMethod('escapeMatch'))->toBeTrue()
            ->and($reflection->hasMethod('getCharsetTable'))->toBeTrue();
    });

    describe('escapeMatch (via reflection)', function () {
        /**
         * Helper to call escapeMatch without connecting to Manticore.
         * Uses reflection to bypass the constructor which requires a running server.
         */
        function callEscapeMatch(string $query): string
        {
            $reflection = new ReflectionClass(ManticoreSearch::class);
            $instance = $reflection->newInstanceWithoutConstructor();
            $method = $reflection->getMethod('escapeMatch');

            return $method->invoke($instance, $query);
        }

        it('preserves Ukrainian-specific Cyrillic characters', function () {
            // These are the 4 Ukrainian letters NOT in the Russian Cyrillic range (U+0410-U+044F)
            // Є (U+0404) / є (U+0454)
            // І (U+0406) / і (U+0456)
            // Ї (U+0407) / ї (U+0457)
            // Ґ (U+0490) / ґ (U+0491)

            $result = callEscapeMatch('Єдність');
            expect($result)->toBe('Єдність');

            $result = callEscapeMatch('Іван');
            expect($result)->toBe('Іван');

            $result = callEscapeMatch('Їжак');
            expect($result)->toBe('Їжак');

            $result = callEscapeMatch('Ґрунт');
            expect($result)->toBe('Ґрунт');
        });

        it('preserves lowercase Ukrainian characters', function () {
            $result = callEscapeMatch('єдність');
            expect($result)->toBe('єдність');

            $result = callEscapeMatch('іграшка');
            expect($result)->toBe('іграшка');

            $result = callEscapeMatch('їжак');
            expect($result)->toBe('їжак');

            $result = callEscapeMatch('ґанок');
            expect($result)->toBe('ґанок');
        });

        it('preserves Ukrainian words that mix Russian and Ukrainian chars', function () {
            // "гвардія" contains "і" (U+0456) which is Ukrainian-specific
            $result = callEscapeMatch('гвардія');
            expect($result)->toBe('гвардія');

            // "історія" contains "і" (U+0456) twice
            $result = callEscapeMatch('історія');
            expect($result)->toBe('історія');

            // "відеоігри" - mixed chars
            $result = callEscapeMatch('відеоігри');
            expect($result)->toBe('відеоігри');
        });

        it('preserves full Ukrainian search phrases', function () {
            $phrase = 'Шукаю гарну українську музику';
            $result = callEscapeMatch($phrase);
            expect($result)->toBe($phrase);
        });

        it('escapes special characters while preserving Ukrainian text', function () {
            // Query with both special Manticore chars and Ukrainian text
            $result = callEscapeMatch('гвардія (2024)');
            expect($result)->toBe('гвардія \\(2024\\)');

            $result = callEscapeMatch('Україна - новини');
            expect($result)->toBe('Україна \\- новини');

            $result = callEscapeMatch('фільм "Їжак"');
            expect($result)->toBe('фільм \\"Їжак\\"');
        });

        it('preserves standard Russian Cyrillic characters', function () {
            $result = callEscapeMatch('Привет мир');
            expect($result)->toBe('Привет мир');

            $result = callEscapeMatch('гвардия');
            expect($result)->toBe('гвардия');
        });
    });

    describe('getCharsetTable', function () {
        function callGetCharsetTable(): string
        {
            $reflection = new ReflectionClass(ManticoreSearch::class);
            $instance = $reflection->newInstanceWithoutConstructor();
            $method = $reflection->getMethod('getCharsetTable');

            return $method->invoke($instance);
        }

        it('includes Ukrainian letter Є/є mapping (U+0404->U+0454)', function () {
            $table = callGetCharsetTable();
            expect($table)->toContain('U+0404->U+0454')
                ->and($table)->toContain('U+0454');
        });

        it('includes Ukrainian letter І/і mapping (U+0406->U+0456)', function () {
            $table = callGetCharsetTable();
            expect($table)->toContain('U+0406->U+0456')
                ->and($table)->toContain('U+0456');
        });

        it('includes Ukrainian letter Ї/ї mapping (U+0407->U+0457)', function () {
            $table = callGetCharsetTable();
            expect($table)->toContain('U+0407->U+0457')
                ->and($table)->toContain('U+0457');
        });

        it('includes Ukrainian letter Ґ/ґ mapping (U+0490->U+0491)', function () {
            $table = callGetCharsetTable();
            expect($table)->toContain('U+0490->U+0491')
                ->and($table)->toContain('U+0491');
        });

        it('starts with non_cjk base charset', function () {
            $table = callGetCharsetTable();
            expect($table)->toStartWith('non_cjk');
        });

        it('includes Belarusian/Ukrainian shared characters', function () {
            $table = callGetCharsetTable();
            // Ў/ў (U+040E/U+045E) - Belarusian, but good to have for broader Cyrillic support
            expect($table)->toContain('U+040E->U+045E');
        });
    });

    describe('createIndexes SQL includes charset_table', function () {
        it('generates CREATE TABLE with charset_table parameter', function () {
            $reflection = new ReflectionClass(ManticoreSearch::class);
            $instance = $reflection->newInstanceWithoutConstructor();

            // Access the private getIndexDefinitions method to verify SQL
            $method = $reflection->getMethod('getIndexDefinitions');
            $definitions = $method->invoke($instance);

            foreach ($definitions as $name => $sql) {
                expect($sql)->toContain('charset_table')
                    ->and($sql)->toContain('U+0454');
            }
        });
    });
});
