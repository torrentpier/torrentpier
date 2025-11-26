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
                    return match ($key) {
                        'xs_use_cache' => 0,
                        'default_lang' => 'en',
                        default => $default
                    };
                }
            };
        }
    }

    if (!function_exists('file_write')) {
        function file_write($content, $filename, $max_size = null, $replace_content = false)
        {
            return file_put_contents($filename, $content);
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
        'TEST_KEY' => 'Test Value',
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

describe('Template Hook Registration', function () {

    it('allows registering hooks', function () {
        $called = false;
        $this->template->registerHook('template_before_assign_vars', function ($data) use (&$called) {
            $called = true;
            return $data;
        });

        $this->template->assign_vars(['TEST_VAR' => 'value']);

        expect($called)->toBeTrue();
    });

    it('allows multiple hooks on same event with priority', function () {
        $calls = [];

        $this->template->registerHook('template_before_assign_vars', function ($data) use (&$calls) {
            $calls[] = 'second';
            return $data;
        }, 20);

        $this->template->registerHook('template_before_assign_vars', function ($data) use (&$calls) {
            $calls[] = 'first';
            return $data;
        }, 10);

        $this->template->assign_vars(['TEST_VAR' => 'value']);

        expect($calls)->toBe(['first', 'second']);
    });

});

describe('template_before_assign_vars Hook', function () {

    it('executes before variables are assigned', function () {
        $this->template->registerHook('template_before_assign_vars', function ($vars) {
            $vars['INJECTED_VAR'] = 'injected value';
            return $vars;
        });

        $this->template->assign_vars(['ORIGINAL_VAR' => 'original value']);

        expect($this->template->vars['ORIGINAL_VAR'])->toBe('original value');
        expect($this->template->vars['INJECTED_VAR'])->toBe('injected value');
    });

    it('can modify existing variables', function () {
        $this->template->registerHook('template_before_assign_vars', function ($vars) {
            if (isset($vars['MOD_VAR'])) {
                $vars['MOD_VAR'] = strtoupper($vars['MOD_VAR']);
            }
            return $vars;
        });

        $this->template->assign_vars(['MOD_VAR' => 'lowercase']);

        expect($this->template->vars['MOD_VAR'])->toBe('LOWERCASE');
    });

});

describe('template_before_compile Hook', function () {

    it('executes before template compilation', function () {
        $this->template->registerHook('template_before_compile', function ($code) {
            return str_replace('{CUSTOM_TAG}', '{REPLACED_TAG}', $code);
        });

        $code = '<div>{CUSTOM_TAG}</div>';
        $compiled = $this->template->compile_code('', $code);

        expect($compiled)->toContain('REPLACED_TAG');
        expect($compiled)->not->toContain('CUSTOM_TAG');
    });

    it('can inject additional template code', function () {
        $this->template->registerHook('template_before_compile', function ($code) {
            return '<!-- MOD HEADER -->' . $code . '<!-- MOD FOOTER -->';
        });

        $code = '<div>Content</div>';
        $compiled = $this->template->compile_code('', $code);

        expect($compiled)->toContain('MOD HEADER');
        expect($compiled)->toContain('MOD FOOTER');
    });

});

describe('template_after_compile Hook', function () {

    it('executes after template compilation', function () {
        $this->template->registerHook('template_after_compile', function ($compiled) {
            return $compiled . '<!-- Compiled by ModSystem -->';
        });

        $code = '<div>Test</div>';
        $compiled = $this->template->compile_code('', $code);

        expect($compiled)->toContain('Compiled by ModSystem');
    });

    it('can modify compiled PHP code', function () {
        $this->template->registerHook('template_after_compile', function ($compiled) {
            // Inject additional PHP code
            return "<?php /* Modified */ ?>\n" . $compiled;
        });

        $code = '<div>Test</div>';
        $compiled = $this->template->compile_code('', $code);

        expect($compiled)->toContain('Modified');
    });

});

describe('template_before_render Hook', function () {

    it('executes before template rendering', function () {
        $called = false;
        $this->template->registerHook('template_before_render', function ($data) use (&$called) {
            $called = true;
            return $data;
        });

        // Create a simple template file
        $templateFile = $this->templateDir . '/test.html';
        file_put_contents($templateFile, '<div>Test</div>');

        $this->template->set_filename('test', 'test.html');
        ob_start();
        $this->template->pparse('test');
        ob_end_clean();

        expect($called)->toBeTrue();
    });

    it('receives handle and template instance', function () {
        $receivedHandle = null;
        $receivedTemplate = null;

        $this->template->registerHook('template_before_render', function ($data) use (&$receivedHandle, &$receivedTemplate) {
            $receivedHandle = $data['handle'] ?? null;
            $receivedTemplate = $data['template'] ?? null;
            return $data;
        });

        $templateFile = $this->templateDir . '/test.html';
        file_put_contents($templateFile, '<div>Test</div>');

        $this->template->set_filename('test', 'test.html');
        ob_start();
        $this->template->pparse('test');
        ob_end_clean();

        expect($receivedHandle)->toBe('test');
        expect($receivedTemplate)->toBeInstanceOf(Template::class);
    });

});

describe('template_after_render Hook', function () {

    it('executes after template rendering', function () {
        $called = false;
        $this->template->registerHook('template_after_render', function ($output) use (&$called) {
            $called = true;
            return $output;
        });

        $templateFile = $this->templateDir . '/test.html';
        file_put_contents($templateFile, '<div>Test</div>');

        $this->template->set_filename('test', 'test.html');
        ob_start();
        $this->template->pparse('test');
        ob_end_clean();

        expect($called)->toBeTrue();
    });

    it('can modify final output', function () {
        $this->template->registerHook('template_after_render', function ($output) {
            return str_replace('</div>', '<!-- Modified --></div>', $output);
        });

        $templateFile = $this->templateDir . '/test.html';
        file_put_contents($templateFile, '<div>Test</div>');

        $this->template->set_filename('test', 'test.html');
        ob_start();
        $this->template->pparse('test');
        $output = ob_get_clean();

        expect($output)->toContain('Modified');
    });

    it('can inject analytics or tracking code', function () {
        $this->template->registerHook('template_after_render', function ($output) {
            return $output . "\n<!-- Analytics Code -->";
        });

        $templateFile = $this->templateDir . '/test.html';
        file_put_contents($templateFile, '<html><body>Content</body></html>');

        $this->template->set_filename('test', 'test.html');
        ob_start();
        $this->template->pparse('test');
        $output = ob_get_clean();

        expect($output)->toContain('Analytics Code');
    });

});

describe('Hook Error Handling', function () {

    it('continues rendering when hook throws exception', function () {
        $this->template->registerHook('template_before_render', function ($data) {
            throw new \Exception('Hook error');
        });

        $templateFile = $this->templateDir . '/test.html';
        file_put_contents($templateFile, '<div>Test</div>');

        $this->template->set_filename('test', 'test.html');
        ob_start();
        $this->template->pparse('test');
        $output = ob_get_clean();

        // Template should still render despite hook error
        expect($output)->toContain('Test');
    });

    it('allows subsequent hooks to run after one fails', function () {
        $secondHookCalled = false;

        $this->template->registerHook('template_before_render', function ($data) {
            throw new \Exception('First hook error');
        }, 10);

        $this->template->registerHook('template_before_render', function ($data) use (&$secondHookCalled) {
            $secondHookCalled = true;
            return $data;
        }, 20);

        $templateFile = $this->templateDir . '/test.html';
        file_put_contents($templateFile, '<div>Test</div>');

        $this->template->set_filename('test', 'test.html');
        ob_start();
        $this->template->pparse('test');
        ob_end_clean();

        expect($secondHookCalled)->toBeTrue();
    });

});

describe('Real-world Mod Integration Scenarios', function () {

    it('karma mod can inject user karma display', function () {
        // Simulate karma mod adding karma display to templates
        $this->template->registerHook('template_before_assign_vars', function ($vars) {
            // Inject karma data for user
            $vars['USER_KARMA'] = 150;
            $vars['USER_KARMA_LEVEL'] = 'Trusted';
            return $vars;
        });

        $this->template->assign_vars(['USERNAME' => 'TestUser']);

        expect($this->template->vars['USERNAME'])->toBe('TestUser');
        expect($this->template->vars['USER_KARMA'])->toBe(150);
        expect($this->template->vars['USER_KARMA_LEVEL'])->toBe('Trusted');
    });

    it('analytics mod can inject tracking code', function () {
        $this->template->registerHook('template_after_render', function ($output) {
            $tracking = '<script>/* Tracking Code */</script>';
            // Insert before closing body tag
            return str_replace('</body>', $tracking . '</body>', $output);
        });

        $templateFile = $this->templateDir . '/page.html';
        file_put_contents($templateFile, '<html><body>Content</body></html>');

        $this->template->set_filename('page', 'page.html');
        ob_start();
        $this->template->pparse('page');
        $output = ob_get_clean();

        expect($output)->toContain('Tracking Code');
        expect($output)->toContain('</body>');
    });

    it('notification mod can add notification bar', function () {
        $this->template->registerHook('template_after_render', function ($output) {
            $notification = '<div class="notification">You have 3 new messages</div>';
            // Insert after body tag
            return str_replace('<body>', '<body>' . $notification, $output);
        });

        $templateFile = $this->templateDir . '/page.html';
        file_put_contents($templateFile, '<html><body><div>Main Content</div></body></html>');

        $this->template->set_filename('page', 'page.html');
        ob_start();
        $this->template->pparse('page');
        $output = ob_get_clean();

        expect($output)->toContain('You have 3 new messages');
        $notificationPos = strpos($output, 'notification');
        $mainContentPos = strpos($output, 'Main Content');
        expect($notificationPos)->toBeLessThan($mainContentPos);
    });

});
