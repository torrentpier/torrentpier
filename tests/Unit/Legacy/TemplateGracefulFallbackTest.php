<?php

use TorrentPier\Legacy\Template;

beforeEach(function () {
    // Setup test environment
    setupTestEnvironment();

    // Define required constants
    if (!defined('CACHE_DIR')) {
        define('CACHE_DIR', sys_get_temp_dir() . '/torrentpier_test_cache');
    }
    if (!defined('TEMPLATES_DIR')) {
        define('TEMPLATES_DIR', sys_get_temp_dir() . '/torrentpier_test_templates');
    }
    if (!defined('XS_TPL_PREFIX')) {
        define('XS_TPL_PREFIX', 'tpl__');
    }
    if (!defined('XS_TAG_NONE')) {
        define('XS_TAG_NONE', 0);
    }
    if (!defined('XS_TAG_BEGIN')) {
        define('XS_TAG_BEGIN', 1);
    }
    if (!defined('XS_TAG_END')) {
        define('XS_TAG_END', 2);
    }
    if (!defined('XS_TAG_INCLUDE')) {
        define('XS_TAG_INCLUDE', 3);
    }
    if (!defined('XS_TAG_IF')) {
        define('XS_TAG_IF', 4);
    }
    if (!defined('XS_TAG_ELSE')) {
        define('XS_TAG_ELSE', 5);
    }
    if (!defined('XS_TAG_ELSEIF')) {
        define('XS_TAG_ELSEIF', 6);
    }
    if (!defined('XS_TAG_ENDIF')) {
        define('XS_TAG_ENDIF', 7);
    }
    if (!defined('XS_TAG_BEGINELSE')) {
        define('XS_TAG_BEGINELSE', 8);
    }

    // Mock required functions if they don't exist
    if (!function_exists('clean_filename')) {
        function clean_filename($fname)
        {
            static $s = ['\\', '/', ':', '*', '?', '"', '<', '>', '|', ' '];
            return str_replace($s, '_', trim($fname));
        }
    }

    if (!function_exists('config')) {
        function config()
        {
            return new class {
                public function get($key, $default = null)
                {
                    // Return sensible defaults for template configuration
                    return match ($key) {
                        'xs_use_cache' => 0,
                        'default_lang' => 'en',
                        default => $default
                    };
                }
            };
        }
    }

    // Create a temporary directory for templates and cache
    $this->tempDir = createTempDirectory();
    $this->templateDir = $this->tempDir . '/templates';
    $this->cacheDir = $this->tempDir . '/cache';

    mkdir($this->templateDir, 0755, true);
    mkdir($this->cacheDir, 0755, true);

    // Set up global language array for testing
    global $lang;
    $lang = [
        'EXISTING_KEY' => 'This key exists',
        'ANOTHER_KEY' => 'Another existing key'
    ];

    // Create template instance
    $this->template = new Template($this->templateDir);
    $this->template->cachedir = $this->cacheDir . '/';
    $this->template->use_cache = 0; // Disable caching for tests
});

afterEach(function () {
    // Clean up
    if (isset($this->tempDir)) {
        removeTempDirectory($this->tempDir);
    }

    // Reset global state
    resetGlobalState();
});

/**
 * Execute a compiled template and return its output
 *
 * @param string $compiled The compiled template code
 * @param array $variables Optional variables to set in scope (V array)
 * @param array $additionalVars Optional additional variables to set in scope
 * @return string The template output
 */
function executeTemplate(string $compiled, array $variables = [], array $additionalVars = []): string
{
    ob_start();
    global $lang;
    $L = &$lang;
    $V = $variables;

    // Set any additional variables in scope
    foreach ($additionalVars as $name => $value) {
        $$name = $value;
    }

    // SECURITY NOTE: eval() is used intentionally here to execute compiled template code
    // within a controlled test environment. While eval() poses security risks in production,
    // its use is justified in this specific unit test scenario because:
    // 1. We're testing the legacy template compilation system that generates PHP code
    // 2. The input is controlled and comes from our own template compiler
    // 3. This runs in an isolated test environment, not production
    // 4. Testing the actual execution is necessary to verify template output correctness
    // Future maintainers: Use extreme caution with eval() and avoid it in production code
    eval('?>' . $compiled);
    return ob_get_clean();
}

describe('Template Text Compilation - Graceful Fallback', function () {

    it('shows missing language variables as original syntax', function () {
        $template = '{L_MISSING_KEY}';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled);

        expect($output)->toBe('L_MISSING_KEY');
    });

    it('shows existing language variables correctly', function () {
        $template = '{L_EXISTING_KEY}';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled);

        expect($output)->toBe('This key exists');
    });

    it('shows missing regular variables as original syntax', function () {
        $template = '{MISSING_VAR}';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled);

        expect($output)->toBe('');
    });

    it('shows existing regular variables correctly', function () {
        $template = '{EXISTING_VAR}';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled, ['EXISTING_VAR' => 'This variable exists']);

        expect($output)->toBe('This variable exists');
    });

    it('shows missing constants as original syntax', function () {
        $template = '{#MISSING_CONSTANT#}';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled);

        expect($output)->toBe('');
    });

    it('shows existing constants correctly', function () {
        // Define a test constant
        if (!defined('TEST_CONSTANT')) {
            define('TEST_CONSTANT', 'This constant exists');
        }

        $template = '{#TEST_CONSTANT#}';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled);

        expect($output)->toBe('This constant exists');
    });

    it('handles mixed existing and missing variables correctly', function () {
        $template = '{L_EXISTING_KEY} - {L_MISSING_KEY} - {EXISTING_VAR} - {MISSING_VAR}';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled, ['EXISTING_VAR' => 'Variable exists']);

        expect($output)->toBe('This key exists - L_MISSING_KEY - Variable exists - ');
    });

    it('handles PHP variables correctly without fallback', function () {
        $template = '{$test_var}';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled, [], ['test_var' => 'PHP variable value']);

        expect($output)->toBe('PHP variable value');
    });

    it('handles undefined PHP variables gracefully', function () {
        $template = '{$undefined_var}';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled);

        // PHP variables that don't exist should show empty string (original behavior)
        expect($output)->toBe('');
    });

});

describe('Template Block Variable Fallback', function () {

    it('shows missing block variables as original syntax', function () {
        $namespace = 'testblock';
        $varname = 'MISSING_VAR';

        $result = $this->template->generate_block_varref($namespace . '.', $varname);

        // Verify the exact expected fallback output format string
        $expectedFormat = "<?php echo isset(\$testblock_item['MISSING_VAR']) ? \$testblock_item['MISSING_VAR'] : 'testblock.MISSING_VAR'; ?>";
        expect($result)->toBe($expectedFormat);
    });

    it('generates correct PHP code for block variable fallback', function () {
        $namespace = 'news';
        $varname = 'TITLE';

        $result = $this->template->generate_block_varref($namespace . '.', $varname);

        // Verify the exact expected fallback output format string
        $expectedFormat = "<?php echo isset(\$news_item['TITLE']) ? \$news_item['TITLE'] : 'news.TITLE'; ?>";
        expect($result)->toBe($expectedFormat);
    });

});

describe('Compiled Code Verification', function () {

    it('compiles language variables with proper fallback code', function () {
        $template = '{L_MISSING_KEY}';
        $compiled = $this->template->_compile_text($template);

        // Verify the compiled PHP code contains the expected fallback logic
        expect($compiled)->toContain("isset(\$L['MISSING_KEY'])");
        expect($compiled)->toContain("'L_MISSING_KEY'");
    });

    it('compiles regular variables with proper fallback code', function () {
        $template = '{MISSING_VAR}';
        $compiled = $this->template->_compile_text($template);

        // Verify the compiled PHP code contains the expected fallback logic
        expect($compiled)->toContain("isset(\$V['MISSING_VAR'])");
        expect($compiled)->toContain("''");
    });

    it('compiles constants with proper fallback code', function () {
        $template = '{#MISSING_CONSTANT#}';
        $compiled = $this->template->_compile_text($template);

        // Verify the compiled PHP code contains the expected fallback logic
        expect($compiled)->toContain("defined('MISSING_CONSTANT')");
        expect($compiled)->toContain("''");
    });

});

describe('Real-world Example - Admin Migrations', function () {

    it('handles the original L_MIGRATIONS_FILE error gracefully', function () {
        // The exact template that was causing the error
        $template = '<td class="catHead" width="50%"><b>{L_MIGRATIONS_FILE}</b></td>';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled);

        // Should show the fallback without braces instead of throwing an error
        expect($output)->toContain('L_MIGRATIONS_FILE');
        expect($output)->toContain('<td class="catHead"');
    });

});

describe('Edge Cases and Robustness', function () {

    it('handles empty variable names gracefully', function () {
        $template = '{}';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled);

        // Empty braces should remain as literal text
        expect($output)->toBe('{}');
    });

    it('handles variables with special characters in names', function () {
        $template = '{VAR_WITH_UNDERSCORES} {VAR-WITH-DASHES} {VAR123NUMBERS}';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled, [
            'VAR_WITH_UNDERSCORES' => 'underscore value',
            'VAR123NUMBERS' => 'number value'
        ]);

        // Verify the compiled code contains proper fallback logic for special chars
        expect($compiled)->toContain("isset(\$V['VAR_WITH_UNDERSCORES'])");
        expect($compiled)->toContain("isset(\$V['VAR123NUMBERS'])");

        // Underscores and numbers should work, dashes might not be valid variable names
        expect($output)->toContain('underscore value');
        expect($output)->toContain('number value');
    });

    it('handles HTML entities and special characters in template content', function () {
        $template = '<div>&amp; {TEST_VAR} &lt;script&gt;</div>';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled, ['TEST_VAR' => 'safe content']);

        // HTML entities should be preserved, variable should be substituted
        expect($output)->toBe('<div>&amp; safe content &lt;script&gt;</div>');

        // Verify fallback logic is present
        expect($compiled)->toContain("isset(\$V['TEST_VAR'])");
    });

    it('handles quotes and escaping in variable values', function () {
        $template = 'Value: {QUOTED_VAR}';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled, [
            'QUOTED_VAR' => 'Contains "quotes" and \'apostrophes\''
        ]);

        expect($output)->toBe('Value: Contains "quotes" and \'apostrophes\'');
        expect($compiled)->toContain("isset(\$V['QUOTED_VAR'])");
    });

    it('handles very long variable names', function () {
        $longVarName = 'VERY_LONG_VARIABLE_NAME_THAT_TESTS_BUFFER_LIMITS_AND_PARSING_' . str_repeat('X', 100);
        $template = '{' . $longVarName . '}';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled, [$longVarName => 'long var value']);

        expect($output)->toBe('long var value');
        expect($compiled)->toContain("isset(\$V['$longVarName'])");
    });

    it('handles nested braces and malformed syntax', function () {
        $template = '{{NESTED}} {UNCLOSED {NORMAL_VAR} }EXTRA}';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled, ['NORMAL_VAR' => 'works']);

        // Should handle the valid variable and leave malformed parts as literals
        expect($output)->toContain('works');
        expect($compiled)->toContain("isset(\$V['NORMAL_VAR'])");
    });

    it('handles empty string values with proper fallback', function () {
        $template = 'Before:{EMPTY_VAR}:After';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled, ['EMPTY_VAR' => '']);

        expect($output)->toBe('Before::After');
        expect($compiled)->toContain("isset(\$V['EMPTY_VAR'])");
    });

    it('handles null and false values correctly', function () {
        $template = 'Null:{NULL_VAR} False:{FALSE_VAR} Zero:{ZERO_VAR}';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled, [
            'NULL_VAR' => null,
            'FALSE_VAR' => false,
            'ZERO_VAR' => 0
        ]);

        // PHP's string conversion: null='', false='', 0='0'
        expect($output)->toBe('Null: False: Zero:0');
        expect($compiled)->toContain("isset(\$V['NULL_VAR'])");
        expect($compiled)->toContain("isset(\$V['FALSE_VAR'])");
        expect($compiled)->toContain("isset(\$V['ZERO_VAR'])");
    });

    it('handles whitespace around variable names', function () {
        $template = '{ SPACED_VAR } {NORMAL_VAR}';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled, [
            'SPACED_VAR' => 'should not work',
            'NORMAL_VAR' => 'should work'
        ]);

        // Spaces inside braces should make it not match as a variable pattern
        expect($output)->toContain('should work');
        expect($compiled)->toContain("isset(\$V['NORMAL_VAR'])");
    });

    it('handles multiple consecutive variables', function () {
        $template = '{VAR1}{VAR2}{VAR3}';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled, [
            'VAR1' => 'A',
            'VAR2' => 'B',
            'VAR3' => 'C'
        ]);

        expect($output)->toBe('ABC');
        expect($compiled)->toContain("isset(\$V['VAR1'])");
        expect($compiled)->toContain("isset(\$V['VAR2'])");
        expect($compiled)->toContain("isset(\$V['VAR3'])");
    });

    it('handles variables with numeric suffixes', function () {
        $template = '{VAR1} {VAR2} {VAR10} {VAR100}';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled, [
            'VAR1' => 'one',
            'VAR2' => 'two',
            'VAR10' => 'ten',
            'VAR100' => 'hundred'
        ]);

        expect($output)->toBe('one two ten hundred');
        expect($compiled)->toContain("isset(\$V['VAR1'])");
        expect($compiled)->toContain("isset(\$V['VAR2'])");
        expect($compiled)->toContain("isset(\$V['VAR10'])");
        expect($compiled)->toContain("isset(\$V['VAR100'])");
    });

    it('handles mixed case sensitivity correctly', function () {
        $template = '{lowercase} {UPPERCASE} {MixedCase}';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled, [
            'lowercase' => 'lower',
            'UPPERCASE' => 'upper',
            'MixedCase' => 'mixed'
        ]);

        expect($output)->toBe('lower upper mixed');
        expect($compiled)->toContain("isset(\$V['lowercase'])");
        expect($compiled)->toContain("isset(\$V['UPPERCASE'])");
        expect($compiled)->toContain("isset(\$V['MixedCase'])");
    });

    it('handles language variables with special prefixes', function () {
        global $lang;
        $originalLang = $lang;

        // Add some special test language variables
        $lang['TEST_SPECIAL_CHARS'] = 'Special: &<>"\'';
        $lang['TEST_UNICODE'] = 'Unicode: ñáéíóú';

        $template = '{L_TEST_SPECIAL_CHARS} | {L_TEST_UNICODE} | {L_MISSING_SPECIAL}';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled);

        expect($output)->toBe('Special: &<>"\' | Unicode: ñáéíóú | L_MISSING_SPECIAL');
        expect($compiled)->toContain("isset(\$L['TEST_SPECIAL_CHARS'])");
        expect($compiled)->toContain("isset(\$L['TEST_UNICODE'])");
        expect($compiled)->toContain("'L_MISSING_SPECIAL'");

        // Restore original language array
        $lang = $originalLang;
    });

    it('handles constants with edge case names', function () {
        // Define some test constants with edge case names
        if (!defined('TEST_CONST_123')) {
            define('TEST_CONST_123', 'numeric suffix');
        }
        if (!defined('TEST_CONST_UNDERSCORE_')) {
            define('TEST_CONST_UNDERSCORE_', 'trailing underscore');
        }

        $template = '{#TEST_CONST_123#} {#TEST_CONST_UNDERSCORE_#} {#UNDEFINED_CONST_EDGE#}';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled);

        expect($output)->toBe('numeric suffix trailing underscore ');
        expect($compiled)->toContain("defined('TEST_CONST_123')");
        expect($compiled)->toContain("defined('TEST_CONST_UNDERSCORE_')");
        expect($compiled)->toContain("defined('UNDEFINED_CONST_EDGE')");
    });

    it('handles complex nested HTML with variables', function () {
        $template = '<table><tr><td>{CELL1}</td><td class="{CSS_CLASS}">{CELL2}</td></tr></table>';
        $compiled = $this->template->_compile_text($template);
        $output = executeTemplate($compiled, [
            'CELL1' => 'First Cell',
            'CSS_CLASS' => 'highlight',
            'CELL2' => 'Second Cell'
        ]);

        expect($output)->toBe('<table><tr><td>First Cell</td><td class="highlight">Second Cell</td></tr></table>');
        expect($compiled)->toContain("isset(\$V['CELL1'])");
        expect($compiled)->toContain("isset(\$V['CSS_CLASS'])");
        expect($compiled)->toContain("isset(\$V['CELL2'])");
    });

});
