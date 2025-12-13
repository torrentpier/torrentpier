<?php

declare(strict_types=1);

/**
 * PHP CS Fixer configuration for TorrentPier.
 *
 * Based on @PER-CS (latest) with additional rules for code quality.
 * Risky rules are enabled for modernization purposes.
 *
 * @see https://cs.symfony.com/doc/rules/
 */

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$root = __DIR__;

$finder = new Finder()
    ->in([
        $root . '/app',
        $root . '/config',
        $root . '/library',
        $root . '/public',
        $root . '/routes',
        $root . '/src',
        $root . '/tests',
    ])
    ->name('*.php')
    ->notName('*Stub.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return new Config()
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS' => true, // Latest PER standard (PSR-12 successor)

        // Array
        'no_whitespace_before_comma_in_array' => true, // [1 , 2] → [1, 2]
        'trim_array_spaces' => true, // [ 1, 2 ] → [1, 2]
        'whitespace_after_comma_in_array' => true, // [1,2] → [1, 2]

        // Blank lines
        'blank_line_after_opening_tag' => true, // <?php\necho → <?php\n\necho
        'blank_line_before_statement' => ['statements' => ['return', 'throw', 'try']],
        'no_extra_blank_lines' => ['tokens' => ['curly_brace_block', 'extra', 'parenthesis_brace_block', 'square_brace_block', 'throw', 'use']],

        // Braces
        'no_unneeded_braces' => true, // "{$x}" → "$x"

        // Cast
        'cast_spaces' => ['space' => 'none'], // (int) $x → (int)$x

        // Class notation
        'class_attributes_separation' => ['elements' => ['method' => 'one', 'property' => 'none']],
        'no_blank_lines_after_class_opening' => true,
        'ordered_class_elements' => ['order' => ['use_trait']], // Only traits first, methods stay where you put them
        'self_accessor' => false, // Keep ClassName::method()
        'single_class_element_per_statement' => true, // public $a, $b; → separate lines

        // Comment
        'single_line_comment_style' => ['comment_types' => ['hash']], // # → //

        // Control structure
        'include' => true, // include( → include (
        'no_alternative_syntax' => false, // Keep if/endif for templates
        'no_superfluous_elseif' => true, // elseif after return → if
        'no_useless_else' => true, // else after return → remove
        'simplified_if_return' => true, // if ($x) { return true; } return false; → return $x;
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],

        // Function notation
        'function_declaration' => ['closure_function_spacing' => 'one'],
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'native_function_invocation' => ['include' => ['@compiler_optimized'], 'scope' => 'namespaced'], // \array_map() (risky)
        'no_spaces_after_function_name' => true, // foo () → foo()
        'return_type_declaration' => ['space_before' => 'none'], // ): int not ) : int

        // Import
        'fully_qualified_strict_types' => true,
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => false, 'import_functions' => false],
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha', 'imports_order' => ['const', 'class', 'function']],

        // Language construct
        'combine_consecutive_issets' => true, // isset($a) && isset($b) → isset($a, $b)
        'combine_consecutive_unsets' => true, // unset($a); unset($b); → unset($a, $b);
        'declare_equal_normalize' => ['space' => 'none'],
        'explicit_indirect_variable' => true, // $$foo['bar'] → ${$foo['bar']}

        // Namespace
        'no_leading_namespace_whitespace' => true,

        // Operator
        'new_with_parentheses' => ['named_class' => false, 'anonymous_class' => false], // new Foo not new Foo()
        'binary_operator_spaces' => ['default' => 'single_space'], // $a=1 → $a = 1
        'concat_space' => ['spacing' => 'one'], // 'a'.'b' → 'a' . 'b'
        'increment_style' => ['style' => 'post'], // ++$i → $i++
        'object_operator_without_whitespace' => true, // $obj -> method() → $obj->method()
        'operator_linebreak' => ['only_booleans' => true, 'position' => 'beginning'], // && at line start
        'standardize_not_equals' => true, // <> → !=
        'ternary_to_null_coalescing' => true, // isset($a) ? $a : $b → $a ?? $b
        'ternary_to_elvis_operator' => true, // $x ? $x : $y → $x ?: $y (risky)
        'logical_operators' => true, // and/or → &&/|| (risky)
        'long_to_shorthand_operator' => true, // $x = $x + 1 → $x += 1 (risky)
        'unary_operator_spaces' => ['only_dec_inc' => false], // ! $x → !$x

        // PHPDoc
        'align_multiline_comment' => true,
        'no_blank_lines_after_phpdoc' => false,
        'no_empty_phpdoc' => true,
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true, 'allow_unused_params' => true], // Remove redundant @param/@return
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_indent' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_order' => true, // @param, @return, @throws order
        'phpdoc_scalar' => true, // integer → int
        'phpdoc_separation' => false, // No blank line between link and license
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => false, // The first line doesn't end with a period
        'phpdoc_to_comment' => false,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'phpdoc_types_order' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
        'phpdoc_var_without_name' => true,

        // Return notation
        'no_useless_return' => true,
        'simplified_null_return' => false, // Keep explicit return null;

        // Semicolon
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'no_empty_statement' => true, // Remove ;;
        'no_singleline_whitespace_before_semicolons' => true,
        'semicolon_after_instruction' => true,
        'space_after_semicolon' => true, // for ($i;$j) → for ($i; $j)

        // Strict
        'strict_comparison' => false, // == → === (disabled for legacy)

        // String
        'escape_implicit_backslashes' => true,
        'explicit_string_variable' => true, // "$a$b" → "{$a}{$b}"
        'heredoc_to_nowdoc' => true, // heredoc without vars → nowdoc
        'no_binary_string' => true, // Remove b"string"
        'single_quote' => true, // "string" → 'string'

        // Whitespace
        'blank_line_between_import_groups' => true,
        'heredoc_indentation' => true,
        'no_spaces_around_offset' => true, // $a[ 'key' ] → $a['key']
        'no_trailing_whitespace' => true,
        'no_whitespace_in_blank_line' => true,
        'types_spaces' => ['space' => 'none'], // int | string → int|string

        // Modernization (risky)
        'declare_strict_types' => false, // Disabled for legacy
        'modernize_types_casting' => true, // intval($x) → (int)$x
        'combine_nested_dirname' => true, // dirname(dirname($x)) → dirname($x, 2) (risky)
        'no_alias_functions' => true, // sizeof() → count()
        'no_mixed_echo_print' => ['use' => 'echo'],

        // Performance (risky)
        'is_null' => true, // is_null() → === null
        'modernize_strpos' => true, // strpos() === 0 → str_starts_with()

        // Security (risky)
        'random_api_migration' => true, // rand() → mt_rand()

        // Cleanup
        'no_unneeded_control_parentheses' => ['statements' => ['break', 'clone', 'continue', 'echo_print', 'return', 'switch_case', 'yield']],
        'no_unneeded_curly_braces' => ['namespaces' => true],
        'no_useless_sprintf' => true, // sprintf('text') → 'text' (risky)

        // Type declarations
        'nullable_type_declaration_for_default_null_value' => true, // ($x = null) → (?T $x = null)

        // Code style
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false], // No Yoda
        'list_syntax' => ['syntax' => 'short'], // list($a) → [$a]
        'use_arrow_functions' => false, // fn() => disabled for legacy
        'no_useless_concat_operator' => true, // 'a' . 'b' → 'ab'
        'dir_constant' => true, // dirname(__FILE__) → __DIR__
    ])
    ->setFinder($finder)
    ->setCacheFile($root . '/storage/framework/.php-cs-fixer.cache');
