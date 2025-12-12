<?php

declare(strict_types=1);

/**
 * PHP CS Fixer configuration for TorrentPier.
 *
 * Based on @PER-CS2.0 with additional rules for code quality.
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
        $root . '/src',
        $root . '/tests',
    ])
    ->name('*.php')
    ->notName('*Stub.php')
    ->notName('*.phar')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return new Config()
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        // PER-CS2.0 preset as base
        '@PER-CS2.0' => true,


        // Array

        'no_whitespace_before_comma_in_array' => true,
        'trim_array_spaces' => true,
        'whitespace_after_comma_in_array' => true,

        // Blank lines
        'blank_line_after_opening_tag' => true,
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw', 'try'],
        ],
        'no_extra_blank_lines' => [
            'tokens' => [
                'curly_brace_block',
                'extra',
                'parenthesis_brace_block',
                'square_brace_block',
                'throw',
                'use',
            ],
        ],

        // Braces
        'no_unneeded_braces' => true,

        // Cast
        'cast_spaces' => ['space' => 'none'],

        // Class notation
        'class_attributes_separation' => [
            'elements' => [
                'method' => 'one',
                'property' => 'one',
            ],
        ],
        'no_blank_lines_after_class_opening' => true,
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public_static',
                'property_protected_static',
                'property_private_static',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public_static',
                'method_protected_static',
                'method_private_static',
                'method_public',
                'method_protected',
                'method_private',
            ],
        ],
        'self_accessor' => true,
        'single_class_element_per_statement' => true,

        // Comment
        'single_line_comment_style' => ['comment_types' => ['hash']],

        // Control structure
        'include' => true,
        'no_alternative_syntax' => false, // Allow alternative syntax in templates
        'no_superfluous_elseif' => true,
        'no_useless_else' => true,
        'simplified_if_return' => true,
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters'],
        ],

        // Function notation
        'function_declaration' => ['closure_function_spacing' => 'one'],
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'native_function_invocation' => [
            'include' => ['@compiler_optimized'],
            'scope' => 'namespaced',
        ],
        'no_spaces_after_function_name' => true,
        'return_type_declaration' => ['space_before' => 'none'],

        // Import
        'fully_qualified_strict_types' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => false,
            'import_functions' => false,
        ],
        'no_unused_imports' => true,
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
            'imports_order' => ['class', 'function', 'const'],
        ],

        // Language construct
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'declare_equal_normalize' => ['space' => 'none'],
        'explicit_indirect_variable' => true,

        // Namespace
        'no_leading_namespace_whitespace' => true,

        // Operator
        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],
        'concat_space' => ['spacing' => 'one'],
        'increment_style' => ['style' => 'post'],
        'object_operator_without_whitespace' => true,
        'operator_linebreak' => [
            'only_booleans' => true,
            'position' => 'beginning',
        ],
        'standardize_not_equals' => true,
        'ternary_to_null_coalescing' => true,

        // PHPDoc
        'align_multiline_comment' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_phpdoc' => true,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_indent' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_order' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => true,
        'phpdoc_to_comment' => false,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'phpdoc_types_order' => [
            'null_adjustment' => 'always_last',
            'sort_algorithm' => 'none',
        ],
        'phpdoc_var_without_name' => true,

        // Return notation
        'no_useless_return' => true,
        'simplified_null_return' => false,

        // Semicolon
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'no_multi_line',
        ],
        'no_empty_statement' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'semicolon_after_instruction' => true,
        'space_after_semicolon' => true,

        // Strict
        'strict_comparison' => false, // Disabled for legacy compatibility

        // String
        'escape_implicit_backslashes' => true,
        'explicit_string_variable' => true,
        'heredoc_to_nowdoc' => true,
        'no_binary_string' => true,
        'single_quote' => true,

        // Whitespace
        'blank_line_between_import_groups' => true,
        'heredoc_indentation' => true,
        'no_spaces_around_offset' => true,
        'no_trailing_whitespace' => true,
        'no_whitespace_in_blank_line' => true,
        'types_spaces' => ['space' => 'none'],

        // Modernization (risky)
        'declare_strict_types' => false, // Disabled for legacy compatibility
        'modernize_types_casting' => true,
        'no_alias_functions' => true,
        'no_mixed_echo_print' => ['use' => 'echo'],

        // Performance (risky)
        'is_null' => false, // Disabled - introduces strict comparisons
        'modernize_strpos' => true,

        // Security (risky)
        'random_api_migration' => true,

        // Cleanup
        'no_unneeded_control_parentheses' => [
            'statements' => ['break', 'clone', 'continue', 'echo_print', 'return', 'switch_case', 'yield'],
        ],
        'no_unneeded_curly_braces' => ['namespaces' => true],

        // Type declarations
        'nullable_type_declaration_for_default_null_value' => true,

        // Code style preferences
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        'list_syntax' => ['syntax' => 'short'],
        'use_arrow_functions' => false, // Disabled for legacy compatibility
        'no_useless_concat_operator' => true,
        'dir_constant' => true,
    ])
    ->setFinder($finder)
    ->setCacheFile($root . '/.php-cs-fixer.cache');
