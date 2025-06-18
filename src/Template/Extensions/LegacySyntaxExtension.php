<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentPier/torrentpier/blob/master/LICENSE MIT License
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
        // Convert legacy includes first (simplest)
        $content = $this->convertIncludes($content);

        // Convert legacy IF statements first - they have the most complex structure
        // This ensures nested structures are properly handled
        $content = $this->convertIfStatements($content);

        // Convert legacy blocks (BEGIN/END)
        $content = $this->convertBlocks($content);

        // Convert legacy variables last
        $content = $this->convertVariables($content);

        return $content;
    }

    /**
     * Convert legacy IF statements to Twig syntax
     */
    private function convertIfStatements(string $content): string
    {
        // Use a more precise approach that handles multiple IF statements on the same line
        // and properly handles all types of nested structures
        
        $iterations = 0;
        $maxIterations = 100; // Increase iterations for complex nested structures
        
        do {
            $previousContent = $content;
            
            // Process most specific patterns first (with ELSE/ELSEIF)
            // Use negative lookahead to ensure we don't match across different IF blocks
            
            // <!-- IF condition -->...<!-- ELSEIF condition2 -->...<!-- ELSE -->...<!-- ENDIF -->
            // Handle the complex IF/ELSEIF/ELSE structure first
            $content = preg_replace_callback('/<!-- IF ([^>]+?) -->((?:(?!<!-- (?:IF|ENDIF|ELSEIF|ELSE)).)*?)<!-- ELSEIF ([^>]+?)(?:\s*\/[^>]*)? -->((?:(?!<!-- (?:ENDIF|ELSE)).)*?)<!-- ELSE(?:\s*\/[^>]*)? -->((?:(?!<!-- ENDIF).)*?)<!-- ENDIF(?:\s*\/[^>]*)? -->/s', function($matches) {
                $condition1 = $this->convertCondition($matches[1]);
                $ifBody = $matches[2];
                $condition2 = $this->convertCondition($matches[3]);
                $elseifBody = $matches[4];
                $elseBody = $matches[5];
                
                return "{% if $condition1 %}$ifBody{% elseif $condition2 %}$elseifBody{% else %}$elseBody{% endif %}";
            }, $content);
            
            // <!-- IF condition -->...<!-- ELSEIF condition2 -->...<!-- ENDIF -->
            // Also handle <!-- ELSEIF / COMMENT --> format
            $content = preg_replace_callback('/<!-- IF ([^>]+?) -->((?:(?!<!-- (?:IF|ENDIF|ELSEIF)).)*?)<!-- ELSEIF ([^>]+?)(?:\s*\/[^>]*)? -->((?:(?!<!-- ENDIF).)*?)<!-- ENDIF(?:\s*\/[^>]*)? -->/s', function($matches) {
                $condition1 = $this->convertCondition($matches[1]);
                $ifBody = $matches[2];
                $condition2 = $this->convertCondition($matches[3]);
                $elseifBody = $matches[4];
                
                return "{% if $condition1 %}$ifBody{% elseif $condition2 %}$elseifBody{% endif %}";
            }, $content);
            
            // <!-- IF condition -->...<!-- ELSE -->...<!-- ENDIF -->
            // Also handle <!-- ELSE / COMMENT --> format
            $content = preg_replace_callback('/<!-- IF ([^>]+?) -->((?:(?!<!-- (?:IF|ENDIF|ELSE)).)*?)<!-- ELSE(?:\s*\/[^>]*)? -->((?:(?!<!-- ENDIF).)*?)<!-- ENDIF(?:\s*\/[^>]*)? -->/s', function($matches) {
                $condition = $this->convertCondition($matches[1]);
                $ifBody = $matches[2];
                $elseBody = $matches[3];
                
                return "{% if $condition %}$ifBody{% else %}$elseBody{% endif %}";
            }, $content);
            
            // Simple <!-- IF condition -->...<!-- ENDIF --> (process innermost first)
            // Use a pattern that matches the smallest possible IF...ENDIF pair
            $content = preg_replace_callback('/<!-- IF ([^>]+?) -->((?:(?!<!-- (?:IF|ENDIF)).)*?)<!-- ENDIF(?:\s*\/[^>]*)? -->/s', function($matches) {
                $condition = $this->convertCondition($matches[1]);
                $body = $matches[2];
                
                return "{% if $condition %}$body{% endif %}";
            }, $content);
            
            // Convert any remaining standalone <!-- ELSE --> tags (from nested structures)
            $content = preg_replace('/<!-- ELSE(?:\s*\/[^>]*)? -->/', '{% else %}', $content);
            
            $iterations++;
        } while ($content !== $previousContent && $iterations < $maxIterations);
        
        return $content;
    }

    /**
     * Convert legacy variables to Twig syntax
     */
    private function convertVariables(string $content): string
    {
        // Convert language variables {L_VARIABLE} to {{ L.VARIABLE }} FIRST
        $content = preg_replace_callback('/\{L_([A-Z0-9_]+)\}/', function($matches) {
            $varName = $matches[1];
            return "{{ L.$varName|default('') }}";
        }, $content);

        // Convert constants {#CONSTANT#} to {{ constant('CONSTANT') }}
        $content = preg_replace_callback('/\{#([A-Z0-9_]+)#\}/', function($matches) {
            $constantName = $matches[1];
            return "{{ constant('$constantName')|default('') }}";
        }, $content);

        // Convert PHP variables {$variable} to {{ variable }}
        $content = preg_replace_callback('/\{\$([a-zA-Z_][a-zA-Z0-9_]*(?:\[[^\]]+\])*(?:\.[a-zA-Z_][a-zA-Z0-9_]*)*)\}/', function($matches) {
            $varName = $matches[1];
            return "{{ $varName|default('') }}";
        }, $content);

        // Convert block item variables {blockname_item.VARIABLE} to {{ blockname_item.VARIABLE }}
        $content = preg_replace_callback('/\{([a-zA-Z0-9_]+_item\.[A-Z0-9_.]+)\}/', function($matches) {
            $varPath = $matches[1];
            return "{{ $varPath|default('') }}";
        }, $content);

        // Convert legacy variables {VARIABLE} to {{ V.VARIABLE }} LAST
        $content = preg_replace_callback('/\{([A-Z0-9_]+)\}/', function($matches) {
            $varName = $matches[1];
            return "{{ V.$varName|default('') }}";
        }, $content);

        // Convert nested block variables {block.subblock.VARIABLE} (but not simple block vars handled in convertBlocks)
        // Exclude variables that end with _item. as those are already processed block variables
        $content = preg_replace_callback('/\{(([a-z0-9\-_]+?\.)+)([a-z0-9\-_]+?)\}/i', function($matches) {
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
            $twigVar .= "[loop.index0]['$varName']";

            return "{{ $twigVar|default('') }}";
        }, $content);

        return $content;
    }

    /**
     * Convert legacy condition syntax to Twig
     */
    private function convertCondition(string $condition): string
    {
        $condition = trim($condition);

        // Convert constants #CONSTANT to constant('CONSTANT') FIRST, before other conversions
        $condition = preg_replace('/#([A-Z0-9_]+)\b/', "constant('$1')", $condition);

        // Convert PHP-style array access to Twig array access
        // Handle nested array access like $bb_cfg['key']['subkey']
        do {
            $previousCondition = $condition;
            // Match $variable['key'] patterns
            $condition = preg_replace_callback('/\$([a-zA-Z_][a-zA-Z0-9_]*)\[([\'"][^\'"]*[\'"])\]/', function($matches) {
                $varName = $matches[1];
                $key = trim($matches[2], '\'"');
                return "$varName.$key";
            }, $condition);
            // Match variable.key['subkey'] patterns (for nested access)
            $condition = preg_replace_callback('/([a-zA-Z_][a-zA-Z0-9_.]*)\[([\'"][^\'"]*[\'"])\]/', function($matches) {
                $varName = $matches[1];
                $key = trim($matches[2], '\'"');
                return "$varName.$key";
            }, $condition);
        } while ($condition !== $previousCondition);

        // Convert PHP-style negation ! to Twig 'not' operator
        $condition = preg_replace('/!(?=\s*[a-zA-Z_$])/', 'not ', $condition);

        // Convert block item variables (they should stay as-is, no V. prefix needed)
        $condition = preg_replace('/\b([a-z0-9_]+_item)\.([A-Z0-9_]+)\b/', '$1.$2', $condition);

        // Convert variable references, but not if they're constants or part of object/array access
        $condition = preg_replace('/\b(?<!constant\(\')(?<![a-z0-9_]\.)(?<![a-z0-9_]_item\.)([A-Z0-9_]+)(?!\.[A-Z0-9_])(?!\'\))(?!\])\b/', 'V.$1', $condition);
        $condition = preg_replace('/\$([a-zA-Z_][a-zA-Z0-9_]*)/', '$1', $condition);

                // Convert operators (with word boundaries to avoid partial matches)
        $operators = [
            '/\beq\b/' => '==',
            '/\bne\b/' => '!=',
            '/\bneq\b/' => '!=',
            '/\blt\b/' => '<',
            '/\ble\b/' => '<=',
            '/\blte\b/' => '<=',
            '/\bgt\b/' => '>',
            '/\bge\b/' => '>=',
            '/\bgte\b/' => '>=',
            '/\band\b/' => 'and',
            '/\bor\b/' => 'or',
            '/\bnot\b/' => 'not',
            '/\bmod\b/' => '%',
            // Handle C-style logical operators
            '/&&/' => ' and ',
            '/\|\|/' => ' or ',
        ];

        foreach ($operators as $pattern => $replacement) {
            $condition = preg_replace($pattern, $replacement, $condition);
        }

        return $condition;
    }

            /**
     * Convert legacy blocks to Twig loops
     */
    private function convertBlocks(string $content): string
    {
        return $this->convertBlocksRecursive($content, []);
    }

    /**
     * Convert blocks recursively with proper nesting support
     */
    private function convertBlocksRecursive(string $content, array $parentBlocks = []): string
    {
        // Find all block pairs in this level
        $pattern = '/<!-- BEGIN ([a-zA-Z0-9_]+) -->(.*?)<!-- END \1 -->/s';
        
        return preg_replace_callback($pattern, function($matches) use ($parentBlocks) {
            $blockName = $matches[1];
            $body = $matches[2];
            
            // Recursively process nested blocks first
            $currentBlockStack = array_merge($parentBlocks, [$blockName]);
            $body = $this->convertBlocksRecursive($body, $currentBlockStack);
            
            // Convert variables for this block level
            $body = $this->convertBlockVariables($body, $blockName, $parentBlocks);
            
            // Generate the appropriate loop structure based on how assign_block_vars works
            if (empty($parentBlocks)) {
                // Top-level block: _tpldata['blockname.']
                return "{% for {$blockName}_item in _tpldata['{$blockName}.']|default([]) %}$body{% endfor %}";
            } else {
                // Nested block: When assign_block_vars('parent.child', ...) is used,
                // it creates _tpldata['parent.'][index]['child.'][nested_index]
                // So we need to access parent_item['child.'] (with dot suffix)
                $parentVar = end($parentBlocks) . '_item';
                return "{% for {$blockName}_item in {$parentVar}['{$blockName}.']|default([]) %}$body{% endfor %}";
            }
        }, $content);
    }

    /**
     * Convert block variables with proper nesting context
     */
    private function convertBlockVariables(string $content, string $currentBlock, array $parentBlocks): string
    {
        // Build the full block path for complex nested variables
        $fullBlockPath = array_merge($parentBlocks, [$currentBlock]);
        
        // For nested blocks, we need to handle variables that reference the full path
        // For example: {c.f.VARIABLE} when we're in the 'f' block should become {{ f_item.VARIABLE }}
        // because the 'f' loop is already inside the 'c' loop context
        
        if (!empty($parentBlocks)) {
            // Convert full path variables like {parent.current.VARIABLE} to {{ current_item.VARIABLE }}
            $fullPathPattern = implode('\.', $fullBlockPath);
            $content = preg_replace_callback('/\{' . $fullPathPattern . '\.([A-Z0-9_.]+)\}/', function($matches) use ($currentBlock) {
                $varPath = $matches[1];
                return "{{ {$currentBlock}_item.$varPath|default('') }}";
            }, $content);
            
            // Also handle parent.current.subblock.VARIABLE patterns (like c.f.sf.VARIABLE)
            $content = preg_replace_callback('/\{' . $fullPathPattern . '\.([a-z0-9_]+)\.([A-Z0-9_.]+)\}/', function($matches) use ($currentBlock) {
                $subBlock = $matches[1];
                $varPath = $matches[2];
                return "{{ {$currentBlock}_item.$subBlock.$varPath|default('') }}";
            }, $content);
        }
        
        // Convert simple block variables for current level {blockname.VARIABLE}
        $content = preg_replace_callback('/\{' . preg_quote($currentBlock) . '\.([A-Z0-9_.]+)\}/', function($matches) use ($currentBlock) {
            $varPath = $matches[1];
            return "{{ {$currentBlock}_item.$varPath|default('') }}";
        }, $content);
        
        // Convert block variables in attributes and other contexts (without curly braces)
        $content = preg_replace('/\b' . preg_quote($currentBlock) . '\.([A-Z0-9_.]+)\b/', $currentBlock . '_item.$1', $content);
        
        return $content;
    }

    /**
     * Convert legacy includes to Twig includes
     */
    private function convertIncludes(string $content): string
    {
        // <!-- INCLUDE filename -->
        $content = preg_replace_callback('/<!-- INCLUDE ([a-zA-Z0-9_\.\-\/]+) -->/', function($matches) {
            $filename = $matches[1];
            return "{{ include('$filename') }}";
        }, $content);

        return $content;
    }

    /**
     * Get variable from template data
     */
    public function getVariable(string $varName, mixed $default = ''): mixed
    {
        global $template;
        return $template->vars[$varName] ?? $default;
    }

    /**
     * Get language variable
     */
    public function getLanguageVariable(string $key, mixed $default = ''): mixed
    {
        global $lang;
        return $lang[$key] ?? $default;
    }

    /**
     * Get PHP constant value
     */
    public function getConstant(string $name): mixed
    {
        return defined($name) ? constant($name) : '';
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

        return "V.$varRef";
    }

    /**
     * Convert block reference
     */
    public function convertBlock(string $blockRef): string
    {
        $parts = explode('.', $blockRef);
        $result = '_tpldata';

        foreach ($parts as $part) {
            $result .= "['$part.']";
        }

        return $result;
    }

    /**
     * Legacy include function
     */
    public function legacyInclude(string $template): string
    {
        return "{{ include('$template') }}";
    }

    /**
     * Legacy if function
     */
    public function legacyIf(string $condition, string $then = '', string $else = ''): string
    {
        $convertedCondition = $this->convertCondition($condition);

        if ($else) {
            return "{% if $convertedCondition %}$then{% else %}$else{% endif %}";
        } else {
            return "{% if $convertedCondition %}$then{% endif %}";
        }
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
}