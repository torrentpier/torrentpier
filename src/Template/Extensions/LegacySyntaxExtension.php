<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Template\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Twig extension to handle legacy TorrentPier template syntax
 * Converts old template syntax to Twig equivalents for backward compatibility
 */
class LegacySyntaxExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('legacy_convert', [$this, 'convertLegacySyntax'], ['is_safe' => ['html']]),
            new TwigFilter('legacy_var', [$this, 'convertVariable'], ['is_safe' => ['html']]),
            new TwigFilter('legacy_block', [$this, 'convertBlock'], ['is_safe' => ['html']]),
            new TwigFilter('lang_fallback', [$this, 'langFallback'], ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('legacy_include', [$this, 'legacyInclude'], ['is_safe' => ['html']]),
            new TwigFunction('legacy_if', [$this, 'legacyIf'], ['is_safe' => ['html']]),
            new TwigFunction('get_var', [$this, 'getVariable']),
            new TwigFunction('get_lang', [$this, 'getLanguageVariable']),
            new TwigFunction('get_constant', [$this, 'getConstant']),
            new TwigFunction('include_file', [$this, 'includeFile'], ['is_safe' => ['html']]),
        ];
    }

    public function getTests(): array
    {
        return [
            new TwigTest('legacy_syntax', [$this, 'isLegacySyntax']),
        ];
    }

    /**
     * Convert legacy template syntax to modern Twig syntax
     */
    public function convertLegacySyntax(string $content): string
    {
        // Normalize variations: <!-- ELSE IF --> to <!-- ELSEIF -->
        $content = preg_replace('/<!-- ELSE\s+IF\s+/i', '<!-- ELSEIF ', $content);

        // Convert PHP includes to Twig include_file function
        // Pattern: include($V['VAR']); -> {{ include_file(V.VAR) }}
        $content = preg_replace_callback(
            '/<\?php\s+include\s*\(\s*\$V\[\s*[\'"]([^\'"]+)[\'"]\s*\]\s*\)\s*;\s*\?>/',
            function ($matches) {
                $varName = $matches[1];

                return "{{ include_file(V.{$varName}) }}";
            },
            $content,
        );

        // Convert legacy includes first (simplest)
        $content = $this->convertIncludes($content);

        // Convert legacy blocks (BEGIN/END) - this will also handle IF statements within the block context
        $content = $this->convertBlocks($content);

        // Convert any remaining IF statements that are not within blocks
        $content = $this->convertIfStatements($content);

        // Convert legacy variables last
        $content = $this->convertVariables($content);

        return $content;
    }

    /**
     * Get variable from template data
     */
    public function getVariable(string $varName, mixed $default = ''): mixed
    {
        return template()->getVar($varName, $default);
    }

    /**
     * Get language variable
     */
    public function getLanguageVariable(string $key, mixed $default = ''): mixed
    {
        return lang()->get($key, $default);
    }

    /**
     * Get PHP constant value
     */
    public function getConstant(string $name): mixed
    {
        return \defined($name) ? \constant($name) : '';
    }

    /**
     * Include a file from a path and return its contents
     * Used to safely include HTML/PHP files in Twig templates
     */
    public function includeFile(?string $path): string
    {
        if (empty($path)) {
            return '';
        }

        // Security: ensure a path is within allowed directories
        $realPath = realpath($path);
        if ($realPath === false) {
            return "<!-- include_file: file not found: {$path} -->";
        }

        // Check if a file is within BB_PATH (the application root, not public/)
        // BB_PATH is the actual app root, BB_ROOT is the public web root
        $appRoot = \defined('BB_PATH') ? realpath(BB_PATH) : (\defined('BB_ROOT') ? realpath(BB_ROOT . '/..') : realpath('.'));
        $appRootWithSeparator = rtrim($appRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!str_starts_with($realPath, $appRootWithSeparator) && $realPath !== $appRoot) {
            return "<!-- include_file: access denied: {$path} -->";
        }

        // Read and return file content
        $content = file_get_contents($realPath);
        if ($content === false) {
            return "<!-- include_file: cannot read: {$path} -->";
        }

        return $content;
    }

    /**
     * Filter for language variable fallback with debug highlighting
     * Used as: {{ V.L_VAR|default(L.VAR)|lang_fallback('VAR') }}
     *
     * @param mixed $value The value from the filter chain (could be the translation or null)
     * @param string $key Language key (e.g., 'MEMBERSHIP_IN')
     * @return string The value if not empty, or variable name with optional debug styling
     */
    public function langFallback(mixed $value, string $key): string
    {
        // If we have a valid value, return it
        if ($value !== null && $value !== '') {
            return (string)$value;
        }

        // Fallback: show the variable name, with red highlight in debug mode
        $varName = 'L_' . $key;

        if (app()->isDebug()) {
            return '<span style="background:#ff6b6b;color:#fff;padding:1px 4px;border-radius:2px;font-size:11px;">' . $varName . '</span>';
        }

        return $varName;
    }

    /**
     * Convert a single variable reference
     */
    public function convertVariable(string $varRef): string
    {
        if (preg_match('/^L_(.+)$/', $varRef, $matches)) {
            return "L.{$matches[1]}";
        }

        if (preg_match('/^\$(.+)$/', $varRef, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^#(.+)#$/', $varRef, $matches)) {
            return "constant('{$matches[1]}')";
        }

        return "V.{$varRef}";
    }

    /**
     * Convert block reference
     */
    public function convertBlock(string $blockRef): string
    {
        $parts = explode('.', $blockRef);
        $result = '_tpldata';

        foreach ($parts as $part) {
            $result .= "['{$part}.']";
        }

        return $result;
    }

    /**
     * Legacy include function
     */
    public function legacyInclude(string $template): string
    {
        return "{{ include('{$template}') }}";
    }

    /**
     * Legacy if function
     */
    public function legacyIf(string $condition, string $then = '', string $else = ''): string
    {
        $convertedCondition = $this->convertCondition($condition);

        if ($else) {
            return "{% if {$convertedCondition} %}{$then}{% else %}{$else}{% endif %}";
        }

        return "{% if {$convertedCondition} %}{$then}{% endif %}";
    }

    /**
     * Test if content contains legacy syntax
     */
    public function isLegacySyntax(string $content): bool
    {
        $patterns = [
            '/\{[A-Z0-9_]+\}/',           // {VARIABLE}
            '/\{L_[A-Z0-9_]+\}/',         // {L_VARIABLE}
            '/\{\$[a-zA-Z_][^}]*\}/',     // {$variable}
            '/\{#[A-Z0-9_]+#\}/',         // {#CONSTANT#}
            '/<!-- IF .+ -->/',           // <!-- IF ... -->
            '/<!-- BEGIN .+ -->/',        // <!-- BEGIN ... -->
            '/<!-- INCLUDE .+ -->/',      // <!-- INCLUDE ... -->
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert legacy IF statements to Twig syntax
     * Uses iterative approach processing innermost blocks first
     */
    private function convertIfStatements(string $content): string
    {
        $iterations = 0;
        $maxIterations = 100;

        // Condition pattern: match anything that's not --> (to prevent greedy matching across tags)
        // Using (?:(?!-->).)+ instead of .+? to prevent matching across -->
        $condPattern = '((?:(?!-->).)+)';

        do {
            $previousContent = $content;

            // Process innermost IF blocks first (those without nested IFs)
            // This ensures we handle nested structures correctly from inside out

            // Pattern for simple IF...ENDIF (no nested IF, no ELSE/ELSEIF)
            $content = preg_replace_callback(
                '/<!-- IF\s+' . $condPattern . '\s*-->((?:(?!<!-- (?:IF|ENDIF|ELSE|ELSEIF)\b).)*?)<!-- ENDIF(?:\s*\/[^>]*)?\s*-->/s',
                function ($matches) {
                    $condition = $this->convertCondition(trim($matches[1]));
                    $body = $matches[2];

                    return "{% if {$condition} %}{$body}{% endif %}";
                },
                $content,
            );

            // Pattern for IF...ELSE...ENDIF (no nested IF, no ELSEIF)
            $content = preg_replace_callback(
                '/<!-- IF\s+' . $condPattern . '\s*-->((?:(?!<!-- (?:IF|ENDIF|ELSE|ELSEIF)\b).)*?)<!-- ELSE(?:\s*\/[^>]*)?\s*-->((?:(?!<!-- (?:IF|ENDIF)\b).)*?)<!-- ENDIF(?:\s*\/[^>]*)?\s*-->/s',
                function ($matches) {
                    $condition = $this->convertCondition(trim($matches[1]));
                    $ifBody = $matches[2];
                    $elseBody = $matches[3];

                    return "{% if {$condition} %}{$ifBody}{% else %}{$elseBody}{% endif %}";
                },
                $content,
            );

            // Pattern for IF...ELSEIF...ENDIF (no nested IF)
            $content = preg_replace_callback(
                '/<!-- IF\s+' . $condPattern . '\s*-->((?:(?!<!-- (?:IF|ENDIF|ELSEIF)\b).)*?)<!-- ELSEIF\s+' . $condPattern . '\s*(?:\/[^>]*)?\s*-->((?:(?!<!-- (?:IF|ENDIF)\b).)*?)<!-- ENDIF(?:\s*\/[^>]*)?\s*-->/s',
                function ($matches) {
                    $condition1 = $this->convertCondition(trim($matches[1]));
                    $ifBody = $matches[2];
                    $condition2 = $this->convertCondition(trim($matches[3]));
                    $elseifBody = $matches[4];

                    return "{% if {$condition1} %}{$ifBody}{% elseif {$condition2} %}{$elseifBody}{% endif %}";
                },
                $content,
            );

            // Pattern for IF...ELSEIF...ELSE...ENDIF (no nested IF)
            $content = preg_replace_callback(
                '/<!-- IF\s+' . $condPattern . '\s*-->((?:(?!<!-- (?:IF|ENDIF|ELSEIF|ELSE)\b).)*?)<!-- ELSEIF\s+' . $condPattern . '\s*(?:\/[^>]*)?\s*-->((?:(?!<!-- (?:IF|ENDIF|ELSE)\b).)*?)<!-- ELSE(?:\s*\/[^>]*)?\s*-->((?:(?!<!-- (?:IF|ENDIF)\b).)*?)<!-- ENDIF(?:\s*\/[^>]*)?\s*-->/s',
                function ($matches) {
                    $condition1 = $this->convertCondition(trim($matches[1]));
                    $ifBody = $matches[2];
                    $condition2 = $this->convertCondition(trim($matches[3]));
                    $elseifBody = $matches[4];
                    $elseBody = $matches[5];

                    return "{% if {$condition1} %}{$ifBody}{% elseif {$condition2} %}{$elseifBody}{% else %}{$elseBody}{% endif %}";
                },
                $content,
            );

            // Handle multiple ELSEIF chains (up to 3 ELSEIFs for now)
            $content = preg_replace_callback(
                '/<!-- IF\s+' . $condPattern . '\s*-->((?:(?!<!-- (?:IF|ENDIF|ELSEIF)\b).)*?)<!-- ELSEIF\s+' . $condPattern . '\s*(?:\/[^>]*)?\s*-->((?:(?!<!-- (?:IF|ENDIF|ELSEIF)\b).)*?)<!-- ELSEIF\s+' . $condPattern . '\s*(?:\/[^>]*)?\s*-->((?:(?!<!-- (?:IF|ENDIF)\b).)*?)<!-- ENDIF(?:\s*\/[^>]*)?\s*-->/s',
                function ($matches) {
                    $cond1 = $this->convertCondition(trim($matches[1]));
                    $body1 = $matches[2];
                    $cond2 = $this->convertCondition(trim($matches[3]));
                    $body2 = $matches[4];
                    $cond3 = $this->convertCondition(trim($matches[5]));
                    $body3 = $matches[6];

                    return "{% if {$cond1} %}{$body1}{% elseif {$cond2} %}{$body2}{% elseif {$cond3} %}{$body3}{% endif %}";
                },
                $content,
            );

            $iterations++;
        } while ($content !== $previousContent && $iterations < $maxIterations);

        // Clean up any standalone ELSE tags that might remain (shouldn't happen normally)
        $content = preg_replace('/<!-- ELSE(?:\s*\/[^>]*)?\s*-->/', '{% else %}', $content);

        return $content;
    }

    /**
     * Convert legacy variables to Twig syntax
     */
    private function convertVariables(string $content): string
    {
        // Convert language variables {L_VARIABLE} to use lang_fallback filter
        // Checks V.L_XXX first (template override), then L.XXX (language array)
        // If neither found - shows variable name with debug highlighting in debug mode
        $content = preg_replace_callback('/\{L_([A-Z0-9_]+)\}/', function ($matches) {
            $varName = $matches[1];

            return "{{ V.L_{$varName}|default(L.{$varName})|lang_fallback('{$varName}') }}";
        }, $content);

        // Convert constants {#CONSTANT#} to {{ constant('CONSTANT') }}
        $content = preg_replace_callback('/\{#([A-Z0-9_]+)#\}/', function ($matches) {
            $constantName = $matches[1];

            return "{{ constant('{$constantName}')|default('') }}";
        }, $content);

        // Convert PHP variables {$variable} to {{ variable }}
        $content = preg_replace_callback('/\{\$([a-zA-Z_][a-zA-Z0-9_]*(?:\[[^\]]+\])*(?:\.[a-zA-Z_][a-zA-Z0-9_]*)*)\}/', function ($matches) {
            $varName = $matches[1];

            return "{{ {$varName}|default('') }}";
        }, $content);

        // Convert block item variables {blockname_item.VARIABLE} to {{ blockname_item.VARIABLE }}
        $content = preg_replace_callback('/\{([a-zA-Z0-9_]+_item\.[A-Z0-9_.]+)\}/', function ($matches) {
            $varPath = $matches[1];

            return "{{ {$varPath}|default('') }}";
        }, $content);

        // Convert legacy variables {VARIABLE} to Twig syntax
        // Check global first (ThemeExtension globals), then fall back to V (template variables)
        $content = preg_replace('/\{([A-Z0-9_]+)\}/', "{{ $1|default(V.$1)|default('') }}", $content);

        // Convert nested block variables {block.subblock.VARIABLE} (but not simple block vars handled in convertBlocks)
        // Exclude variables that end with _item. as those are already processed block variables
        $content = preg_replace_callback('/\{(([a-z0-9\-_]+?\.)+)([a-z0-9\-_]+?)\}/i', function ($matches) {
            $namespace = rtrim($matches[1], '.');
            $varName = $matches[3];

            // Skip if this is an already-processed block variable (ending with _item)
            if (str_ends_with($namespace, '_item')) {
                return $matches[0]; // Return unchanged
            }

            $parts = explode('.', $namespace);

            $twigVar = '_tpldata';
            foreach ($parts as $part) {
                $twigVar .= "['" . $part . ".']";
            }
            $twigVar .= "[loop.index0]['{$varName}']";

            return "{{ {$twigVar}|default('') }}";
        }, $content);

        return $content;
    }

    /**
     * Convert legacy condition syntax to Twig
     */
    private function convertCondition(string $condition): string
    {
        $condition = trim($condition);

        // Step 1: Convert constants #CONSTANT# to constant('CONSTANT') FIRST
        // Handle both #CONSTANT# (with closing #) and #CONSTANT (without)
        $condition = preg_replace('/#([A-Z0-9_]+)#?/', "constant('$1')", $condition);

        // Step 2: Convert PHP-style array access to Twig dot notation
        // Handle nested array access like $bb_cfg['key']['subkey']
        $maxIterations = 10;
        $iterations = 0;
        do {
            $previousCondition = $condition;
            // Match $variable['key'] patterns
            $condition = preg_replace_callback('/\$([a-zA-Z_][a-zA-Z0-9_]*)\[([\'"])([^\'"]*)\2\]/', function ($matches) {
                return $matches[1] . '.' . $matches[3];
            }, $condition);
            // Match variable.key['subkey'] patterns (for nested access)
            $condition = preg_replace_callback('/([a-zA-Z_][a-zA-Z0-9_.]+)\[([\'"])([^\'"]*)\2\]/', function ($matches) {
                return $matches[1] . '.' . $matches[3];
            }, $condition);
            $iterations++;
        } while ($condition !== $previousCondition && $iterations < $maxIterations);

        // Step 3: Convert the remaining $ variable to variable (without $)
        $condition = preg_replace('/\$([a-zA-Z_][a-zA-Z0-9_]*)/', '$1', $condition);

        // Step 4: Convert PHP-style negation ! to Twig 'not' operator
        $condition = preg_replace('/!(?=\s*[a-zA-Z_])/', 'not ', $condition);

        // Step 5: Convert operators BEFORE variable conversion (to avoid issues with 'or', 'and' in var names)
        $operators = [
            '/\beq\b/i' => '==',
            '/\bne\b/i' => '!=',
            '/\bneq\b/i' => '!=',
            '/\blt\b/i' => '<',
            '/\ble\b/i' => '<=',
            '/\blte\b/i' => '<=',
            '/\bgt\b/i' => '>',
            '/\bge\b/i' => '>=',
            '/\bgte\b/i' => '>=',
            '/\bmod\b/i' => '%',
            // C-style logical operators
            '/&&/' => ' and ',
            '/\|\|/' => ' or ',
        ];

        foreach ($operators as $pattern => $replacement) {
            $condition = preg_replace($pattern, $replacement, $condition);
        }

        // Step 6: Convert uppercase variables to V.VARIABLE
        // Use negative lookbehinds to skip if preceded by: V., _item., or any word char + dot
        // Use negative lookahead to skip variables followed by dot (they're already object refs)
        $condition = preg_replace_callback(
            '/(?<!V\.)(?<!_item\.)(?<!\w\.)\b([A-Z][A-Z0-9_]*)\b(?!\.)/',
            function ($matches) use ($condition) {
                $var = $matches[0];

                // Skip if inside constant('...')
                if (preg_match('/constant\s*\(\s*[\'"]' . preg_quote($var, '/') . '[\'"]\s*\)/', $condition)) {
                    return $var;
                }

                return 'V.' . $var;
            },
            $condition,
        );

        // Step 7: Convert word-based logical operators (after variable conversion to avoid conflicts)
        $condition = preg_replace('/\band\b/i', 'and', $condition);
        $condition = preg_replace('/\bor\b/i', 'or', $condition);
        $condition = preg_replace('/\bnot\b/i', 'not', $condition);

        return $condition;
    }

    /**
     * Convert legacy blocks to Twig loops
     */
    private function convertBlocks(string $content): string
    {
        return $this->convertBlocksRecursive($content);
    }

    /**
     * Convert blocks recursively with proper nesting support
     */
    private function convertBlocksRecursive(string $content, array $parentBlocks = []): string
    {
        // Find all block pairs in this level
        $pattern = '/<!-- BEGIN ([a-zA-Z0-9_]+) -->(.*?)<!-- END \1 -->/s';

        return preg_replace_callback($pattern, function ($matches) use ($parentBlocks) {
            $blockName = $matches[1];
            $body = $matches[2];

            // Recursively process nested blocks first
            $currentBlockStack = array_merge($parentBlocks, [$blockName]);
            $body = $this->convertBlocksRecursive($body, $currentBlockStack);

            // Convert IF statements with block context BEFORE converting variables
            $body = $this->convertIfStatementsWithContext($body, $currentBlockStack);

            // Convert variables for this block level
            $body = $this->convertBlockVariables($body, $blockName, $parentBlocks);

            // Generate the appropriate loop structure based on how assign_block_vars works
            if (empty($parentBlocks)) {
                // Top-level block: _tpldata['blockname.']
                return "{% for {$blockName}_item in _tpldata['{$blockName}.']|default([]) %}{$body}{% endfor %}";
            }
            // Nested block: When assign_block_vars('parent.child', ...) is used,
            // it creates _tpldata['parent.'][index]['child.'][nested_index]
            // So we need to access parent_item['child.'] (with dot suffix)
            $parentVar = end($parentBlocks) . '_item';

            return "{% for {$blockName}_item in {$parentVar}['{$blockName}.']|default([]) %}{$body}{% endfor %}";
        }, $content);
    }

    /**
     * Convert block variables with a proper nesting context
     */
    private function convertBlockVariables(string $content, string $currentBlock, array $parentBlocks): string
    {
        // Build the full block path for complex nested variables
        $fullBlockPath = array_merge($parentBlocks, [$currentBlock]);

        // Escape block name for regex (and ensure word boundary matching)
        $blockNameEscaped = preg_quote($currentBlock, '/');

        // For nested blocks, we need to handle variables that reference the full path
        // For example: {c.f.VARIABLE} when we're in the 'f' block should become {{ f_item.VARIABLE }}
        // because the 'f' loop is already inside the 'c' loop context

        if (!empty($parentBlocks)) {
            // Convert full path variables like {parent.current.VARIABLE} to {{ current_item.VARIABLE }}
            // Use array_map with preg_quote to properly escape each block name
            $fullPathPattern = implode('\.', array_map(fn ($b) => preg_quote($b, '/'), $fullBlockPath));
            $content = preg_replace_callback('/\{' . $fullPathPattern . '\.([A-Z0-9_.]+)\}/', function ($matches) use ($currentBlock) {
                $varPath = $matches[1];

                return "{{ {$currentBlock}_item.{$varPath}|default('') }}";
            }, $content);

            // Also handle parent.current.subblock.VARIABLE patterns (like c.f.sf.VARIABLE)
            $content = preg_replace_callback('/\{' . $fullPathPattern . '\.([a-z0-9_]+)\.([A-Z0-9_.]+)\}/', function ($matches) use ($currentBlock) {
                $subBlock = $matches[1];
                $varPath = $matches[2];

                return "{{ {$currentBlock}_item.{$subBlock}.{$varPath}|default('') }}";
            }, $content);
        }

        // Convert simple block variables for current level {blockname.VARIABLE}
        // Use negative lookbehind to ensure we match the exact block name, not partial (e.g., don't match 'cf' when looking for 'c')
        $content = preg_replace_callback('/\{(?<![a-z0-9_])' . $blockNameEscaped . '\.([A-Z0-9_.]+)\}/', function ($matches) use ($currentBlock) {
            $varPath = $matches[1];

            return "{{ {$currentBlock}_item.{$varPath}|default('') }}";
        }, $content);

        // Convert block variables in attributes and other contexts (without curly braces)
        // Use word boundary \b to prevent partial matches
        $content = preg_replace('/(?<![a-z0-9_])' . $blockNameEscaped . '\.([A-Z0-9_.]+)(?![a-z0-9_])/', $currentBlock . '_item.$1', $content);

        return $content;
    }

    /**
     * Convert IF statements with block context awareness
     */
    private function convertIfStatementsWithContext(string $content, array $blockStack): string
    {
        $iterations = 0;
        $maxIterations = 50;

        // Condition pattern: match anything that's not -->
        $condPattern = '((?:(?!-->).)+)';

        do {
            $previousContent = $content;

            // Simple IF...ENDIF (innermost first)
            $content = preg_replace_callback(
                '/<!-- IF\s+' . $condPattern . '\s*-->((?:(?!<!-- (?:IF|ENDIF|ELSE|ELSEIF)\b).)*?)<!-- ENDIF(?:\s*\/[^>]*)?\s*-->/s',
                function ($matches) use ($blockStack) {
                    $condition = $this->convertConditionWithContext(trim($matches[1]), $blockStack);
                    $body = $matches[2];

                    return "{% if {$condition} %}{$body}{% endif %}";
                },
                $content,
            );

            // IF...ELSE...ENDIF
            $content = preg_replace_callback(
                '/<!-- IF\s+' . $condPattern . '\s*-->((?:(?!<!-- (?:IF|ENDIF|ELSE|ELSEIF)\b).)*?)<!-- ELSE(?:\s*\/[^>]*)?\s*-->((?:(?!<!-- (?:IF|ENDIF)\b).)*?)<!-- ENDIF(?:\s*\/[^>]*)?\s*-->/s',
                function ($matches) use ($blockStack) {
                    $condition = $this->convertConditionWithContext(trim($matches[1]), $blockStack);
                    $ifBody = $matches[2];
                    $elseBody = $matches[3];

                    return "{% if {$condition} %}{$ifBody}{% else %}{$elseBody}{% endif %}";
                },
                $content,
            );

            // IF...ELSEIF...ENDIF
            $content = preg_replace_callback(
                '/<!-- IF\s+' . $condPattern . '\s*-->((?:(?!<!-- (?:IF|ENDIF|ELSEIF)\b).)*?)<!-- ELSEIF\s+' . $condPattern . '\s*(?:\/[^>]*)?\s*-->((?:(?!<!-- (?:IF|ENDIF)\b).)*?)<!-- ENDIF(?:\s*\/[^>]*)?\s*-->/s',
                function ($matches) use ($blockStack) {
                    $cond1 = $this->convertConditionWithContext(trim($matches[1]), $blockStack);
                    $body1 = $matches[2];
                    $cond2 = $this->convertConditionWithContext(trim($matches[3]), $blockStack);
                    $body2 = $matches[4];

                    return "{% if {$cond1} %}{$body1}{% elseif {$cond2} %}{$body2}{% endif %}";
                },
                $content,
            );

            // IF...ELSEIF...ELSE...ENDIF
            $content = preg_replace_callback(
                '/<!-- IF\s+' . $condPattern . '\s*-->((?:(?!<!-- (?:IF|ENDIF|ELSEIF|ELSE)\b).)*?)<!-- ELSEIF\s+' . $condPattern . '\s*(?:\/[^>]*)?\s*-->((?:(?!<!-- (?:IF|ENDIF|ELSE)\b).)*?)<!-- ELSE(?:\s*\/[^>]*)?\s*-->((?:(?!<!-- (?:IF|ENDIF)\b).)*?)<!-- ENDIF(?:\s*\/[^>]*)?\s*-->/s',
                function ($matches) use ($blockStack) {
                    $cond1 = $this->convertConditionWithContext(trim($matches[1]), $blockStack);
                    $body1 = $matches[2];
                    $cond2 = $this->convertConditionWithContext(trim($matches[3]), $blockStack);
                    $body2 = $matches[4];
                    $elseBody = $matches[5];

                    return "{% if {$cond1} %}{$body1}{% elseif {$cond2} %}{$body2}{% else %}{$elseBody}{% endif %}";
                },
                $content,
            );

            $iterations++;
        } while ($content !== $previousContent && $iterations < $maxIterations);

        return $content;
    }

    /**
     * Convert condition with block context awareness
     */
    private function convertConditionWithContext(string $condition, array $blockStack): string
    {
        $condition = trim($condition);

        // If we're in a block context, convert block variables appropriately
        if (!empty($blockStack)) {
            $currentBlock = end($blockStack);
            $fullBlockPath = implode('.', $blockStack);

            // Convert variables like c.f.POSTS to f_item.POSTS when in f block
            // This handles the pattern where the full path refers to the current block item
            $condition = preg_replace_callback('/\b' . preg_quote($fullBlockPath) . '\.([A-Z0-9_]+)\b/', function ($matches) use ($currentBlock) {
                $varName = $matches[1];

                return "{$currentBlock}_item.{$varName}";
            }, $condition);

            // Don't auto-convert standalone variables - let the main variable conversion handle them
            // This prevents global variables like SHOW_LAST_TOPIC from being incorrectly converted to block variables
        }

        // Apply standard condition conversions
        return $this->convertCondition($condition);
    }

    /**
     * Convert legacy includes to Twig includes
     */
    private function convertIncludes(string $content): string
    {
        // <!-- INCLUDE filename -->
        $content = preg_replace_callback('/<!-- INCLUDE ([a-zA-Z0-9_\.\-\/]+) -->/', function ($matches) {
            $filename = $matches[1];

            return "{{ include('{$filename}') }}";
        }, $content);

        return $content;
    }
}
