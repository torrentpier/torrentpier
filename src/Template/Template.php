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

/**
 * Template engine based on Twig with legacy API compatibility
 */
class Template
{
    private static ?Template $instance = null;
    private static array $instances = [];
    private static float $totalRenderTime = 0;

    private ?Environment $twig = null;

    /** @var array Block data for template loops */
    public array $_tpldata = ['.' => [0 => []]];

    /** @var array Template variables (reference to _tpldata['.'][0]) */
    public array $vars = [];

    /** @var array Registered template files by handle */
    public array $files = [];

    /** @var string Template root directory */
    public string $root = '';

    /** @var string Cache directory */
    public string $cachedir = '';

    /** @var string Current template name */
    public string $tpl = '';

    /** @var string Pre-parse template handle */
    public string $preparse = '';

    /** @var string Post-parse template handle */
    public string $postparse = '';

    /** @var array Language variables reference */
    public array $lang = [];

    private function __construct(string $root = '.')
    {
        global $lang;

        $this->vars = &$this->_tpldata['.'][0];
        $this->root = $root;
        $this->tpl = basename($root);
        $this->lang = &$lang;
        $this->cachedir = CACHE_DIR . '/';

        if (!is_dir($this->root)) {
            die("Template directory not found: $this->tpl");
        }

        $this->initializeTwig();
    }

    public static function getInstance(?string $root = null): self
    {
        $root = $root ?: '.';
        $key = md5($root);

        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($root);

            if (self::$instance === null) {
                self::$instance = self::$instances[$key];
            }
        }

        return self::$instances[$key];
    }

    private function initializeTwig(): void
    {
        $useCache = (bool)config()->get('twig.cache_enabled', true);
        $factory = new TwigEnvironmentFactory();
        $this->twig = $factory->create($this->root, $this->cachedir, $useCache);

        $this->twig->addGlobal('_tpldata', $this->_tpldata);
        $this->twig->addGlobal('V', $this->vars);
        $this->twig->addGlobal('L', $this->lang);
    }

    public function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * Register template file for a handle
     */
    public function set_filename(string $handle, string $filename, bool $quiet = false): bool
    {
        $this->files[$handle] = $this->make_filename($filename);

        if (!$this->files[$handle]) {
            if ($quiet) {
                return false;
            }
            die("Template error: invalid template $filename");
        }

        if (!@file_exists($this->files[$handle])) {
            if ($quiet) {
                return false;
            }
            die('Template not found: ' . hide_bb_path($this->files[$handle]));
        }

        return true;
    }

    /**
     * Register multiple template files
     */
    public function set_filenames(array $filenames): void
    {
        foreach ($filenames as $handle => $filename) {
            $this->set_filename($handle, $filename);
        }
    }

    /**
     * Assign template variables
     */
    public function assign_vars(array $vararray): void
    {
        foreach ($vararray as $key => $val) {
            $this->vars[$key] = $val;
        }
        $this->twig->addGlobal('V', $this->vars);
    }

    /**
     * Assign single template variable
     */
    public function assign_var(string $varname, mixed $varval = true): void
    {
        $this->vars[$varname] = $varval;
        $this->twig->addGlobal('V', $this->vars);
    }

    /**
     * Assign block variables for loops
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
            $this->_tpldata[$blockname . '.'][] = $vararray;
        }

        $this->twig->addGlobal('_tpldata', $this->_tpldata);
        return true;
    }

    /**
     * Parse and output template
     */
    public function pparse(string $handle): bool
    {
        // Handle pre- / post-parse
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

        if (empty($this->files[$handle])) {
            die("Template error: no file for handle '$handle'");
        }

        $this->initStartupVars();

        $templatePath = $this->files[$handle];
        $templateName = $this->getRelativeTemplateName($templatePath);

        $context = [
            '_tpldata' => $this->_tpldata,
            'L' => $this->lang,
            'V' => $this->vars
        ];

        // Reset tracking on page_header
        if ($handle === 'page_header') {
            self::$totalRenderTime = 0;
            Loaders\LegacyTemplateLoader::resetLoadedTemplates();
        }

        $renderStart = microtime(true);

        try {
            $output = $this->twig->render($templateName, $context);
        } catch (\Exception $e) {
            die("Template render error '$handle': " . $e->getMessage());
        }

        $renderTime = (microtime(true) - $renderStart) * 1000;
        self::$totalRenderTime += $renderTime;

        // Debug bar (if enabled in config)
        $showDebugBar = config()->get('twig.debug_bar', false);

        if ($showDebugBar && $handle === 'page_header' && stripos($output, '<body') !== false) {
            $debugBar = sprintf(
                '<div id="twig-debug-bar" style="position:fixed;top:0;left:0;right:0;z-index:99999;' .
                'background:linear-gradient(90deg,#1a472a,#2d5a3d);color:#90EE90;font-family:monospace;' .
                'font-size:11px;padding:4px 12px;display:flex;gap:20px;align-items:center;box-shadow:0 2px 4px rgba(0,0,0,0.3);">' .
                '<span style="font-weight:bold;">🌿 TWIG v%s</span>' .
                '<span>Theme: <b>%s</b></span>' .
                '<span id="twig-templates">Templates: <b>loading...</b></span>' .
                '<span id="twig-render-time">Render: <b>%.2fms</b></span>' .
                '<span style="margin-left:auto;opacity:0.7;">TorrentPier Twig Engine</span>' .
                '</div><style>body{padding-top:28px !important;}</style>',
                Environment::VERSION,
                $this->tpl,
                $renderTime
            );
            $output = preg_replace('/(<body[^>]*>)/i', '$1' . $debugBar, $output, 1);
        }

        if ($showDebugBar && $handle === 'page_footer') {
            $loadedTemplates = Loaders\LegacyTemplateLoader::getLoadedTemplates();
            $count = count($loadedTemplates);
            $list = $count > 0 ? implode(', ', $loadedTemplates) : '(from cache)';
            $label = $count > 0 ? "Templates ($count)" : "Templates";

            $output .= sprintf(
                '<script>document.getElementById("twig-templates").innerHTML = "%s: <b>%s</b>";' .
                'document.getElementById("twig-render-time").innerHTML = "Total render: <b>%.2fms</b>";</script>',
                $label,
                addslashes($list),
                self::$totalRenderTime
            );
        }

        echo $output;
        return true;
    }

    /**
     * Build a full template path
     */
    public function make_filename(string $filename): string
    {
        // Handle admin templates
        if (str_starts_with($filename, 'admin/')) {
            $adminDir = dirname($this->root) . '/admin';
            if (is_dir($adminDir)) {
                return $adminDir . '/' . substr($filename, 6);
            }
        }

        // Relative path
        if ($filename[0] !== '/' && (strlen($filename) < 2 || $filename[1] !== ':')) {
            return $this->root . '/' . $filename;
        }

        return $filename;
    }

    /**
     * Initialize standard template variables
     */
    private function initStartupVars(): void
    {
        $this->vars['LANG'] ??= config()->get('default_lang');

        $tpl = $this->root . '/';
        if (str_starts_with($tpl, './')) {
            $tpl = substr($tpl, 2);
        }
        $this->vars['TEMPLATE'] ??= $tpl;
        $this->vars['TEMPLATE_NAME'] ??= $this->tpl;

        $this->twig->addGlobal('V', $this->vars);
    }

    /**
     * Convert full path to Twig template name
     */
    private function getRelativeTemplateName(string $fullPath): string
    {
        $fullPath = realpath($fullPath) ?: $fullPath;

        // Admin template - use @admin namespace
        $adminDir = realpath(dirname($this->root) . '/admin');
        if ($adminDir && str_starts_with($fullPath, $adminDir . '/')) {
            return '@admin/' . str_replace($adminDir . '/', '', $fullPath);
        }

        // Current theme directory
        $rootDir = realpath($this->root);
        if ($rootDir && str_starts_with($fullPath, $rootDir . '/')) {
            return str_replace($rootDir . '/', '', $fullPath);
        }

        // Default theme fallback
        $defaultDir = realpath(dirname($this->root) . '/default');
        if ($defaultDir && str_starts_with($fullPath, $defaultDir . '/')) {
            return str_replace($defaultDir . '/', '', $fullPath);
        }

        return basename($fullPath);
    }

    /**
     * Reset instances (for testing)
     */
    public static function destroyInstances(): void
    {
        self::$instance = null;
        self::$instances = [];
    }
}
