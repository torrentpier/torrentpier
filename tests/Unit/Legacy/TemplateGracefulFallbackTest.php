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

describe('Template Text Compilation - Graceful Fallback', function () {

    it('debugs compiled output for troubleshooting', function () {
        $template = '{L_MISSING_KEY}';
        $compiled = $this->template->_compile_text($template);

        // Show the actual compiled output for debugging
        expect($compiled)->toBeString();

        // Print the compiled code to understand the issue
        error_log("Compiled template: " . $compiled);
    });

    it('shows missing language variables as original syntax', function () {
        $template = '{L_MISSING_KEY}';
        $compiled = $this->template->_compile_text($template);

        // Extract and execute the PHP code
        ob_start();
        global $lang;
        $L = &$lang;
        $V = [];

        // Execute the compiled template properly
        eval('?>' . $compiled);
        $output = ob_get_clean();

        expect($output)->toBe('L_MISSING_KEY');
    });

    it('shows existing language variables correctly', function () {
        $template = '{L_EXISTING_KEY}';
        $compiled = $this->template->_compile_text($template);

        // Extract and execute the PHP code
        ob_start();
        global $lang;
        $L = &$lang;
        $V = [];

        // Execute the compiled template properly
        eval('?>' . $compiled);
        $output = ob_get_clean();

        expect($output)->toBe('This key exists');
    });

    it('shows missing regular variables as original syntax', function () {
        $template = '{MISSING_VAR}';
        $compiled = $this->template->_compile_text($template);

        // Extract and execute the PHP code
        ob_start();
        global $lang;
        $L = &$lang;
        $V = [];
        eval('?>' . $compiled);
        $output = ob_get_clean();

        expect($output)->toBe('');
    });

    it('shows existing regular variables correctly', function () {
        $template = '{EXISTING_VAR}';
        $compiled = $this->template->_compile_text($template);

        // Extract and execute the PHP code
        ob_start();
        global $lang;
        $L = &$lang;
        $V = ['EXISTING_VAR' => 'This variable exists'];
        eval('?>' . $compiled);
        $output = ob_get_clean();

        expect($output)->toBe('This variable exists');
    });

    it('shows missing constants as original syntax', function () {
        $template = '{#MISSING_CONSTANT#}';
        $compiled = $this->template->_compile_text($template);

        // Extract and execute the PHP code
        ob_start();
        global $lang;
        $L = &$lang;
        $V = [];
        eval('?>' . $compiled);
        $output = ob_get_clean();

        expect($output)->toBe('');
    });

    it('shows existing constants correctly', function () {
        // Define a test constant
        if (!defined('TEST_CONSTANT')) {
            define('TEST_CONSTANT', 'This constant exists');
        }

        $template = '{#TEST_CONSTANT#}';
        $compiled = $this->template->_compile_text($template);

        // Extract and execute the PHP code
        ob_start();
        global $lang;
        $L = &$lang;
        $V = [];
        eval('?>' . $compiled);
        $output = ob_get_clean();

        expect($output)->toBe('This constant exists');
    });

    it('handles mixed existing and missing variables correctly', function () {
        $template = '{L_EXISTING_KEY} - {L_MISSING_KEY} - {EXISTING_VAR} - {MISSING_VAR}';
        $compiled = $this->template->_compile_text($template);

        // Extract and execute the PHP code
        ob_start();
        global $lang;
        $L = &$lang;
        $V = ['EXISTING_VAR' => 'Variable exists'];

        // Execute the compiled template properly
        eval('?>' . $compiled);
        $output = ob_get_clean();

        expect($output)->toBe('This key exists - L_MISSING_KEY - Variable exists - ');
    });

    it('handles PHP variables correctly without fallback', function () {
        $template = '{$test_var}';
        $compiled = $this->template->_compile_text($template);

        // Extract and execute the PHP code
        ob_start();
        global $lang;
        $L = &$lang;
        $V = [];
        $test_var = 'PHP variable value';
        eval('?>' . $compiled);
        $output = ob_get_clean();

        expect($output)->toBe('PHP variable value');
    });

    it('handles undefined PHP variables gracefully', function () {
        $template = '{$undefined_var}';
        $compiled = $this->template->_compile_text($template);

        // Extract and execute the PHP code
        ob_start();
        global $lang;
        $L = &$lang;
        $V = [];
        // Note: $undefined_var is not defined
        eval('?>' . $compiled);
        $output = ob_get_clean();

        // PHP variables that don't exist should show empty string (original behavior)
        expect($output)->toBe('');
    });

});

describe('Template Block Variable Fallback', function () {

    it('shows missing block variables as original syntax', function () {
        $namespace = 'testblock';
        $varname = 'MISSING_VAR';

        $result = $this->template->generate_block_varref($namespace . '.', $varname);

        // The result should be PHP code that shows the missing variable syntax without braces
        expect($result)->toContain('testblock.MISSING_VAR');
    });

    it('generates correct PHP code for block variable fallback', function () {
        $namespace = 'news';
        $varname = 'TITLE';

        $result = $this->template->generate_block_varref($namespace . '.', $varname);

        // Should contain the fallback syntax without braces
        expect($result)->toContain('news.TITLE');
        expect($result)->toContain('<?php echo isset(');
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

        // Execute the compiled template
        ob_start();
        global $lang;
        $L = &$lang;
        $V = [];

        // Execute the compiled template properly
        eval('?>' . $compiled);
        $output = ob_get_clean();

        // Should show the fallback without braces instead of throwing an error
        expect($output)->toContain('L_MIGRATIONS_FILE');
        expect($output)->toContain('<td class="catHead"');
    });

});
