<?php
/**
 * Twig Legacy Syntax Conversion Test
 *
 * Run: php test_twig_conversion.php
 * This file tests the LegacySyntaxExtension conversion without rendering
 */

require_once __DIR__ . '/vendor/autoload.php';

use TorrentPier\Template\Extensions\LegacySyntaxExtension;

$converter = new LegacySyntaxExtension();

// Test cases: [description, input, expected_output]
$testCases = [
    // === VARIABLES ===
    ['Simple variable', '{VARIABLE}', "{{ V.VARIABLE|default('') }}"],
    ['Language variable', '{L_HELLO}', "{{ L.HELLO|default('') }}"],
    ['Constant', '{#CONSTANT#}', "{{ constant('CONSTANT')|default('') }}"],
    ['PHP variable', '{$myvar}', "{{ myvar|default('') }}"],

    // === CONDITIONS - Simple ===
    ['Simple IF', '<!-- IF SHOW -->yes<!-- ENDIF -->', "{% if V.SHOW %}yes{% endif %}"],
    ['IF with ELSE', '<!-- IF SHOW -->yes<!-- ELSE -->no<!-- ENDIF -->', "{% if V.SHOW %}yes{% else %}no{% endif %}"],
    ['IF ELSEIF', '<!-- IF A -->a<!-- ELSEIF B -->b<!-- ENDIF -->', "{% if V.A %}a{% elseif V.B %}b{% endif %}"],
    ['IF ELSEIF ELSE', '<!-- IF A -->a<!-- ELSEIF B -->b<!-- ELSE -->c<!-- ENDIF -->', "{% if V.A %}a{% elseif V.B %}b{% else %}c{% endif %}"],

    // === CONDITIONS - Operators ===
    ['Negation !', '<!-- IF !SHOW -->hidden<!-- ENDIF -->', "{% if not V.SHOW %}hidden{% endif %}"],
    ['Equality eq', '<!-- IF VAR eq 1 -->one<!-- ENDIF -->', "{% if V.VAR == 1 %}one{% endif %}"],
    ['Not equal neq', '<!-- IF VAR neq 0 -->ok<!-- ENDIF -->', "{% if V.VAR != 0 %}ok{% endif %}"],
    ['Greater than gt', '<!-- IF COUNT gt 5 -->many<!-- ENDIF -->', "{% if V.COUNT > 5 %}many{% endif %}"],
    ['Less than lt', '<!-- IF COUNT lt 10 -->few<!-- ENDIF -->', "{% if V.COUNT < 10 %}few{% endif %}"],
    ['AND operator', '<!-- IF A and B -->both<!-- ENDIF -->', "{% if V.A and V.B %}both{% endif %}"],
    ['OR operator', '<!-- IF A or B -->either<!-- ENDIF -->', "{% if V.A or V.B %}either{% endif %}"],
    ['C-style AND', '<!-- IF A && B -->both<!-- ENDIF -->', "{% if V.A  and  V.B %}both{% endif %}"],
    ['C-style OR', '<!-- IF A || B -->either<!-- ENDIF -->', "{% if V.A  or  V.B %}either{% endif %}"],

    // === CONDITIONS - Constants ===
    ['Constant in condition', '<!-- IF #DEBUG# -->debug<!-- ENDIF -->', "{% if constant('DEBUG') %}debug{% endif %}"],

    // === BLOCKS ===
    ['Simple block', '<!-- BEGIN items -->{items.NAME}<!-- END items -->', "{% for items_item in _tpldata['items.']|default([]) %}{{ items_item.NAME|default('') }}{% endfor %}"],

    // === INCLUDES ===
    ['Include', '<!-- INCLUDE header.tpl -->', "{{ include('header.tpl') }}"],

    // === NESTED STRUCTURES ===
    ['Nested IF', '<!-- IF A --><!-- IF B -->inner<!-- ENDIF --><!-- ENDIF -->', "{% if V.A %}{% if V.B %}inner{% endif %}{% endif %}"],

    // === PHP ARRAY ACCESS ===
    ['PHP array in condition', '<!-- IF $config[\'enabled\'] -->on<!-- ENDIF -->', "{% if config.enabled %}on{% endif %}"],
    ['Nested array access', '<!-- IF $bb_cfg[\'a\'][\'b\'] -->ok<!-- ENDIF -->', "{% if bb_cfg.a.b %}ok{% endif %}"],

    // === BLOCK VARIABLES IN CONDITIONS ===
    // Note: These should work within BEGIN/END context, here we test raw conversion
];

echo "=== Twig Legacy Syntax Conversion Tests ===\n\n";

$passed = 0;
$failed = 0;
$errors = [];

foreach ($testCases as $i => [$description, $input, $expected]) {
    $result = $converter->convertLegacySyntax($input);

    // Normalize whitespace for comparison
    $resultNorm = preg_replace('/\s+/', ' ', trim($result));
    $expectedNorm = preg_replace('/\s+/', ' ', trim($expected));

    if ($resultNorm === $expectedNorm) {
        echo "✓ $description\n";
        $passed++;
    } else {
        echo "✗ $description\n";
        echo "  Input:    $input\n";
        echo "  Expected: $expected\n";
        echo "  Got:      $result\n";
        $errors[] = [
            'description' => $description,
            'input' => $input,
            'expected' => $expected,
            'got' => $result,
        ];
        $failed++;
    }
}

echo "\n=== Results ===\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Total:  " . ($passed + $failed) . "\n";

if ($failed > 0) {
    echo "\n=== Failed Tests Details ===\n";
    foreach ($errors as $error) {
        echo "\n{$error['description']}:\n";
        echo "  Input:    {$error['input']}\n";
        echo "  Expected: {$error['expected']}\n";
        echo "  Got:      {$error['got']}\n";
    }
}

// Also test convertCondition directly for edge cases
echo "\n\n=== Direct Condition Conversion Tests ===\n\n";

$conditionTests = [
    ['Simple var', 'SHOW', 'V.SHOW'],
    ['Negation', '!SHOW', 'not V.SHOW'],
    ['Equality', 'VAR eq 1', 'V.VAR == 1'],
    ['PHP var', '$myvar', 'myvar'],
    ['Array access', "\$cfg['key']", 'cfg.key'],
    ['Nested array', "\$cfg['a']['b']", 'cfg.a.b'],
    ['Constant', '#DEBUG#', "constant('DEBUG')"],
    ['Block item var', 'row_item.VALUE', 'row_item.VALUE'],  // Should NOT add V. prefix
    ['Mixed', 'SHOW and !HIDE', 'V.SHOW and not V.HIDE'],
    ['Comparison', 'COUNT gt 10', 'V.COUNT > 10'],
];

$condPassed = 0;
$condFailed = 0;

// Use reflection to access private method
$reflection = new ReflectionClass($converter);
$method = $reflection->getMethod('convertCondition');
$method->setAccessible(true);

foreach ($conditionTests as [$desc, $input, $expected]) {
    $result = $method->invoke($converter, $input);
    $resultNorm = preg_replace('/\s+/', ' ', trim($result));
    $expectedNorm = preg_replace('/\s+/', ' ', trim($expected));

    if ($resultNorm === $expectedNorm) {
        echo "✓ $desc: '$input' => '$result'\n";
        $condPassed++;
    } else {
        echo "✗ $desc\n";
        echo "  Input:    $input\n";
        echo "  Expected: $expected\n";
        echo "  Got:      $result\n";
        $condFailed++;
    }
}

echo "\n=== Condition Results ===\n";
echo "Passed: $condPassed\n";
echo "Failed: $condFailed\n";

// Exit with error code if any tests failed
exit($failed + $condFailed > 0 ? 1 : 0);
