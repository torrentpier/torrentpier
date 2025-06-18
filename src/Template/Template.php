<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Template;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use TorrentPier\Template\Extensions\LegacySyntaxExtension;
use TorrentPier\Template\Extensions\BlockExtension;
use TorrentPier\Template\Extensions\LanguageExtension;
use TorrentPier\Template\Loaders\LegacyTemplateLoader;

/**
 * Modern Template class using Twig internally with full backward compatibility
 * Implements singleton pattern while maintaining all existing Template methods
 */
class Template
{
    private static ?Template $instance = null;
    private static array $instances = [];

    private ?Environment $twig = null;
    private ?TwigEnvironmentFactory $factory = null;

    // Backward compatibility properties - mirror original Template class
    public array $_tpldata = [
        '.' => [
            0 => []
        ]
    ];

    public array $vars = [];
    public array $files = [];
    public array $files_cache = [];
    public array $files_cache2 = [];
    public string $root = '';
    public string $cachedir = '';
    public string $tpldir = '';
    public string $tpldef = 'default';
    public int $use_cache = 1;
    public string $tpl = '';
    public string $cur_tpl = '';
    public array $replace = [];
    public string $preparse = '';
    public string $postparse = '';
    public array $style_config = [];
    public array $lang = [];
    public array $uncompiled_code = [];

    /**
     * Private constructor for singleton pattern
     */
    private function __construct(string $root = '.')
    {
        global $lang;

        // Initialize backward compatibility properties
        $this->vars =& $this->_tpldata['.'][0];
        $this->tpldir = TEMPLATES_DIR;
        $this->root = $root;
        $this->tpl = basename($root);
        $this->lang =& $lang;
        $this->use_cache = config()->get('xs_use_cache');
        $this->cachedir = CACHE_DIR . '/';

        // Check template directory exists
        if (!is_dir($this->root)) {
            die("Theme ({$this->tpl}) directory not found");
        }

        // Initialize Twig environment
        $this->initializeTwig();
    }

    /**
     * Get singleton instance for default template
     */
    public static function getInstance(?string $root = null): self
    {
        $root = $root ?: '.';
        $key = md5($root);

        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($root);

            // If this is the first instance, set as default
            if (self::$instance === null) {
                self::$instance = self::$instances[$key];
            }
        }

        return self::$instances[$key];
    }

    /**
     * Get instance for specific template directory
     */
    public static function getDirectoryInstance(string $root): self
    {
        return self::getInstance($root);
    }

    /**
     * Initialize Twig environment with legacy compatibility
     */
    private function initializeTwig(): void
    {
        $this->factory = new TwigEnvironmentFactory();
        $this->twig = $this->factory->create($this->root, $this->cachedir, $this->use_cache);

        // Add template variables to Twig globals
        $this->twig->addGlobal('_tpldata', $this->_tpldata);
        $this->twig->addGlobal('V', $this->vars);
        $this->twig->addGlobal('L', $this->lang);
    }

    /**
     * Get Twig environment (for advanced usage)
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * Assigns template filename for handle (backward compatibility)
     */
    public function set_filename(string $handle, string $filename, bool $xs_include = false, bool $quiet = false): bool
    {
        $can_cache = $this->use_cache;
        $this->files[$handle] = $this->make_filename($filename, $xs_include);
        $this->files_cache[$handle] = '';
        $this->files_cache2[$handle] = '';

        // Check if we have valid filename
        if (!$this->files[$handle]) {
            if ($xs_include || $quiet) {
                return false;
            }
            die("Template->make_filename(): Error - invalid template $filename");
        }

        // Create cache filename
        if ($can_cache) {
            $this->files_cache2[$handle] = $this->make_filename_cache($this->files[$handle]);
            if (@file_exists($this->files_cache2[$handle])) {
                $this->files_cache[$handle] = $this->files_cache2[$handle];
            }
        }

        // Check if tpl file exists
        if (empty($this->files_cache[$handle]) && !@file_exists($this->files[$handle])) {
            if ($quiet) {
                return false;
            }
            die('Template->make_filename(): Error - template file not found: <br /><br />' . hide_bb_path($this->files[$handle]));
        }

        // Check if we should recompile cache
        if (!empty($this->files_cache[$handle])) {
            $cache_time = @filemtime($this->files_cache[$handle]);
            if (@filemtime($this->files[$handle]) > $cache_time) {
                $this->files_cache[$handle] = '';
            }
        }

        return true;
    }

    /**
     * Sets the template filenames for handles (backward compatibility)
     */
    public function set_filenames(array $filenames): void
    {
        foreach ($filenames as $handle => $filename) {
            $this->set_filename($handle, $filename);
        }
    }

    /**
     * Root-level variable assignment (backward compatibility)
     */
    public function assign_vars(array $vararray): void
    {
        foreach ($vararray as $key => $val) {
            $this->vars[$key] = $val;
        }

        // Update Twig globals
        $this->twig->addGlobal('V', $this->vars);
    }

    /**
     * Root-level variable assignment (backward compatibility)
     */
    public function assign_var(string $varname, mixed $varval = true): void
    {
        $this->vars[$varname] = $varval;

        // Update Twig globals
        $this->twig->addGlobal('V', $this->vars);
    }

    /**
     * Block-level variable assignment (backward compatibility)
     */
    public function assign_block_vars(string $blockname, array $vararray): bool
    {
        if (str_contains($blockname, '.')) {
            // Nested block
            $blocks = explode('.', $blockname);
            $blockcount = count($blocks) - 1;

            $str = &$this->_tpldata;
            for ($i = 0; $i < $blockcount; $i++) {
                $str = &$str[$blocks[$i] . '.'];
                $str = &$str[(is_countable($str) ? count($str) : 0) - 1];
            }
            $str[$blocks[$blockcount] . '.'][] = $vararray;
        } else {
            // Top-level block
            $this->_tpldata[$blockname . '.'][] = $vararray;
        }

        // Update Twig globals
        $this->twig->addGlobal('_tpldata', $this->_tpldata);

        return true;
    }

    /**
     * Parse and print template (backward compatibility)
     */
    public function pparse(string $handle): bool
    {
        // Handle preparse and postparse
        if ($this->preparse || $this->postparse) {
            $preparse = $this->preparse;
            $postparse = $this->postparse;
            $this->preparse = '';
            $this->postparse = '';

            if ($preparse) {
                $this->pparse($preparse);
            }
            if ($postparse) {
                $str = $handle;
                $handle = $postparse;
                $this->pparse($str);
            }
        }

        // Check if handle exists
        if (empty($this->files[$handle]) && empty($this->files_cache[$handle])) {
            die("Template->loadfile(): No files found for handle $handle");
        }

        $this->xs_startup();

                // Ensure we have the file path
        if (empty($this->files[$handle])) {
            die("Template->pparse(): No files found for handle $handle. Make sure set_filename() was called first.");
        }

        $template_full_path = $this->files[$handle];

        // Get template name relative to configured template directories
        $template_name = $this->getRelativeTemplateName($template_full_path);

        // Prepare template context
        $context = array_merge(
            $this->vars,
            [
                '_tpldata' => $this->_tpldata,
                'L' => $this->lang,
                'V' => $this->vars
            ]
        );

        try {
            echo $this->twig->render($template_name, $context);
        } catch (\Exception $e) {
            // Instead of falling back to legacy, provide a helpful error message
            die("Template rendering error for '$handle' (template: '$template_name'): " . $e->getMessage() .
                "\nTemplate path: $template_full_path\n" .
                "Available templates in Twig loader: " . implode(', ', $this->getAvailableTemplates()));
        }

        return true;
    }

    /**
     * Generate filename with path (backward compatibility)
     */
    public function make_filename(string $filename, bool $xs_include = false): string
    {
        // Check replacements list
        if (!$xs_include && isset($this->replace[$filename])) {
            $filename = $this->replace[$filename];
        }

        // Handle admin templates specially
        if (str_starts_with($filename, 'admin/')) {
            $adminTemplateDir = dirname($this->root) . '/admin';
            if (is_dir($adminTemplateDir)) {
                // Remove 'admin/' prefix and use admin template directory
                $adminTemplateName = substr($filename, 6); // Remove 'admin/'
                return $adminTemplateDir . '/' . $adminTemplateName;
            }
        }

        // Check if it's an absolute or relative path
        if (($filename[0] !== '/') && (strlen($filename) < 2 || $filename[1] !== ':')) {
            return $this->root . '/' . $filename;
        }

        return $filename;
    }

    /**
     * Convert template filename to cache filename (backward compatibility)
     */
    public function make_filename_cache(string $filename): string
    {
        $filename = clean_filename(str_replace(TEMPLATES_DIR, '', $filename));
        return $this->cachedir . XS_TPL_PREFIX . $filename . '.php';
    }

    /**
     * Initialize startup variables (backward compatibility)
     */
    public function xs_startup(): void
    {
        $this->vars['LANG'] ??= config()->get('default_lang');

        $tpl = $this->root . '/';
        if (str_starts_with($tpl, './')) {
            $tpl = substr($tpl, 2);
        }
        $this->vars['TEMPLATE'] ??= $tpl;
        $this->vars['TEMPLATE_NAME'] ??= $this->tpl;

        // Update Twig globals
        $this->twig->addGlobal('V', $this->vars);
    }



    /**
     * Convert absolute template path to relative template name for Twig
     */
    private function getRelativeTemplateName(string $template_full_path): string
    {
        // Normalize the path
        $template_full_path = realpath($template_full_path) ?: $template_full_path;

        // Check if it's an admin template
        $adminTemplateDir = realpath(dirname($this->root) . '/admin');
        if ($adminTemplateDir && str_starts_with($template_full_path, $adminTemplateDir . '/')) {
            // Admin template - return relative path from admin directory
            return str_replace($adminTemplateDir . '/', '', $template_full_path);
        }

        // Check if it's a regular template in current directory
        $rootDir = realpath($this->root);
        if ($rootDir && str_starts_with($template_full_path, $rootDir . '/')) {
            // Regular template - return relative path from root directory
            return str_replace($rootDir . '/', '', $template_full_path);
        }

        // Check if it's a template in default directory (fallback)
        $defaultTemplateDir = realpath(dirname($this->root) . '/default');
        if ($defaultTemplateDir && str_starts_with($template_full_path, $defaultTemplateDir . '/')) {
            // Default template - return relative path from default directory
            return str_replace($defaultTemplateDir . '/', '', $template_full_path);
        }

        // Fallback - try to extract just the filename
        return basename($template_full_path);
    }

    /**
     * Get list of available templates for debugging
     */
    private function getAvailableTemplates(): array
    {
        try {
            // Try to get available templates from Twig loader
            $loader = $this->twig->getLoader();
            if (method_exists($loader, 'getPaths')) {
                $paths = $loader->getPaths();
                $templates = [];
                foreach ($paths as $path) {
                    if (is_dir($path)) {
                        $files = glob($path . '/*.tpl');
                        foreach ($files as $file) {
                            $templates[] = basename($file);
                        }
                    }
                }
                return $templates;
            }
        } catch (\Exception $e) {
            // If we can't get templates from loader, return what we know
        }

        return array_keys($this->files);
    }

    /**
     * Debug method to dump template data
     */
    public function debugDump(string $title = 'Template Debug', bool $die = false): void
    {
        echo "<h2>$title</h2>";
        echo "<h3>_tpldata structure:</h3>";
        echo "<pre>";
        print_r($this->_tpldata);
        echo "</pre>";
        
        echo "<h3>Root variables (vars):</h3>";
        echo "<pre>";
        print_r($this->vars);
        echo "</pre>";
        
        echo "<h3>Language variables (first 10):</h3>";
        echo "<pre>";
        print_r(array_slice($this->lang, 0, 10, true));
        echo "</pre>";
        
        echo "<h3>Template files:</h3>";
        echo "<pre>";
        print_r($this->files);
        echo "</pre>";
        
        if ($die) {
            die();
        }
    }
    
    /**
     * Debug method to dump specific block data
     */
    public function debugBlock(string $blockName): void
    {
        echo "<h3>Block '$blockName' data:</h3>";
        echo "<pre>";
        $blockKey = $blockName . '.';
        if (isset($this->_tpldata[$blockKey])) {
            print_r($this->_tpldata[$blockKey]);
        } else {
            echo "Block '$blockName' not found in _tpldata\n";
            echo "Available blocks: " . implode(', ', array_keys($this->_tpldata)) . "\n";
        }
        echo "</pre>";
    }

    /**
     * Destroy all instances (for testing)
     */
    public static function destroyInstances(): void
    {
        self::$instance = null;
        self::$instances = [];
    }
}