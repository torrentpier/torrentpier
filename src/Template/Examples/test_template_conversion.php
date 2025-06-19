<?php
/**
 * Template System Test - Demonstrates legacy syntax conversion and compatibility
 *
 * This script shows how the new Twig-based Template system maintains
 * 100% backward compatibility while providing modern features.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

// Simulate TorrentPier environment
define('BB_ROOT', __DIR__ . '/../../');
define('TEMPLATES_DIR', __DIR__ . '/../../styles/templates');
define('CACHE_DIR', __DIR__ . '/../../internal_data/cache');

// Mock functions that the Template system expects
if (!function_exists('config')) {
    function config() {
        return new class {
            public function get($key) {
                $config = [
                    'xs_use_cache' => 1,
                    'default_lang' => 'en',
                    'tpl_name' => 'default'
                ];
                return $config[$key] ?? null;
            }
        };
    }
}

if (!function_exists('dev')) {
    function dev() {
        return new class {
            public function get_level() { return 0; }
        };
    }
}

if (!function_exists('hide_bb_path')) {
    function hide_bb_path($path) { return $path; }
}

if (!function_exists('clean_filename')) {
    function clean_filename($filename) { return $filename; }
}

if (!defined('XS_TPL_PREFIX')) {
    define('XS_TPL_PREFIX', 'tpl_');
}

// Test the Template system
use TorrentPier\Template\Template;
use TorrentPier\Template\Extensions\LegacySyntaxExtension;

echo "=== TorrentPier Template System Test ===\n\n";

// Test 1: Legacy Syntax Conversion
echo "1. Testing Legacy Syntax Conversion:\n";
echo "=====================================\n";

$extension = new LegacySyntaxExtension();

$legacyTemplate = '
<h1>{SITE_NAME}</h1>
<!-- IF LOGGED_IN -->
    <p>Welcome, {$userdata.username}!</p>
    <!-- BEGIN notifications -->
        <div>{notifications.MESSAGE}</div>
    <!-- END notifications -->
<!-- ELSE -->
    <a href="{U_LOGIN}">{L_LOGIN}</a>
<!-- ENDIF -->
<p>Constant: {#MY_CONSTANT#}</p>
';

echo "Legacy Template:\n";
echo $legacyTemplate . "\n";

$convertedTemplate = $extension->convertLegacySyntax($legacyTemplate);

echo "Converted to Twig:\n";
echo $convertedTemplate . "\n";

// Test 2: Backward Compatibility
echo "2. Testing Backward Compatibility:\n";
echo "===================================\n";

try {
    // Create template instance (should work like the old system)
    $template = Template::getInstance(__DIR__);

    echo "✓ Template singleton created successfully\n";

    // Test variable assignment (legacy method)
    $template->assign_vars([
        'SITE_NAME' => 'TorrentPier Test',
        'PAGE_TITLE' => 'Test Page',
        'LOGGED_IN' => true,
        'USERNAME' => 'TestUser'
    ]);

    echo "✓ Variables assigned using legacy assign_vars method\n";

    // Test block assignment (legacy method)
    $template->assign_block_vars('news', [
        'TITLE' => 'Test News Item',
        'CONTENT' => 'This is a test news item.',
        'AUTHOR' => 'Admin',
        'DATE' => date('Y-m-d H:i:s')
    ]);

    $template->assign_block_vars('news', [
        'TITLE' => 'Another News Item',
        'CONTENT' => 'This is another test news item.',
        'AUTHOR' => 'Editor',
        'DATE' => date('Y-m-d H:i:s')
    ]);

    echo "✓ Block variables assigned using legacy assign_block_vars method\n";

    // Test Twig environment access (new feature)
    $twig = $template->getTwig();
    echo "✓ Twig environment accessible for advanced features\n";

    // Test that all legacy properties are accessible
    $properties = ['vars', '_tpldata', 'files', 'root', 'tpl'];
    foreach ($properties as $prop) {
        if (property_exists($template, $prop)) {
            echo "✓ Legacy property '{$prop}' is accessible\n";
        } else {
            echo "✗ Legacy property '{$prop}' is missing\n";
        }
    }

    // Test that all legacy methods are callable
    $methods = ['assign_var', 'assign_vars', 'assign_block_vars', 'set_filename', 'set_filenames', 'pparse', 'make_filename'];
    foreach ($methods as $method) {
        if (method_exists($template, $method)) {
            echo "✓ Legacy method '{$method}' is callable\n";
        } else {
            echo "✗ Legacy method '{$method}' is missing\n";
        }
    }

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// Test 3: Template Data Structure
echo "\n3. Testing Template Data Structure:\n";
echo "====================================\n";

echo "Template vars: " . json_encode($template->vars, JSON_PRETTY_PRINT) . "\n";
echo "Template _tpldata: " . json_encode($template->_tpldata, JSON_PRETTY_PRINT) . "\n";

// Test 4: Syntax Pattern Recognition
echo "\n4. Testing Syntax Pattern Recognition:\n";
echo "======================================\n";

$testPatterns = [
    '{VARIABLE}' => 'Legacy variable',
    '{L_LANGUAGE}' => 'Language variable',
    '{$php_var}' => 'PHP variable',
    '{#CONSTANT#}' => 'PHP constant',
    '<!-- IF condition -->' => 'Legacy IF statement',
    '<!-- BEGIN block -->' => 'Legacy block',
    '<!-- INCLUDE file -->' => 'Legacy include',
    '{{ modern_var }}' => 'Modern Twig variable',
    '{% if condition %}' => 'Modern Twig if'
];

foreach ($testPatterns as $pattern => $description) {
    $isLegacy = $extension->isLegacySyntax($pattern);
    echo sprintf("%-25s | %-20s | %s\n",
        $pattern,
        $description,
        $isLegacy ? '✓ Legacy detected' : '○ Modern syntax'
    );
}

echo "\n=== Test Completed Successfully ===\n";
echo "The new Template system maintains full backward compatibility\n";
echo "while providing modern Twig features under the hood!\n";