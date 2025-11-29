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

    /** Block data for template loops */
    private array $blockData = ['.' => [0 => []]];

    /** Template variables (reference to blockData['.'][0]) */
    private array $variables = [];

    /** Registered template files by handle */
    private array $files = [];

    /** Template root directory */
    private string $rootDir;

    /** Cache directory */
    private string $cacheDir;

    /** Current template name */
    private string $templateName;

    /** Language variables reference */
    private array $lang = [];

    private function __construct(string $root = '.')
    {
        global $lang;

        $this->variables = &$this->blockData['.'][0];
        $this->rootDir = $root;
        $this->templateName = basename($root);
        $this->lang = &$lang;
        $this->cacheDir = CACHE_DIR . '/';

        if (!is_dir($this->rootDir)) {
            throw new \RuntimeException("Template directory not found: $this->templateName");
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

    public function getCacheDir(): string
    {
        return $this->cacheDir;
    }

    public function getVar(string $name, mixed $default = null): mixed
    {
        return $this->variables[$name] ?? $default;
    }

    /**
     * Register multiple template files
     */
    public function set_filenames(array $filenames): void
    {
        foreach ($filenames as $handle => $filename) {
            $this->registerTemplate($handle, $filename);
        }
    }

    /**
     * Assign template variables
     */
    public function assign_vars(array $variables): void
    {
        foreach ($variables as $key => $value) {
            $this->variables[$key] = $value;
        }
        $this->twig->addGlobal('V', $this->variables);
    }

    /**
     * Assign single template variable
     */
    public function assign_var(string $name, mixed $value = true): void
    {
        $this->variables[$name] = $value;
        $this->twig->addGlobal('V', $this->variables);
    }

    /**
     * Assign block variables for loops
     */
    public function assign_block_vars(string $block, array $variables): bool
    {
        if (str_contains($block, '.')) {
            $blocks = explode('.', $block);
            $blockCount = count($blocks) - 1;

            $data = &$this->blockData;
            for ($i = 0; $i < $blockCount; $i++) {
                $data = &$data[$blocks[$i] . '.'];
                $data = &$data[(is_countable($data) ? count($data) : 0) - 1];
            }
            $data[$blocks[$blockCount] . '.'][] = $variables;
        } else {
            $this->blockData[$block . '.'][] = $variables;
        }

        $this->twig->addGlobal('_tpldata', $this->blockData);
        return true;
    }

    /**
     * Parse and output template
     */
    public function pparse(string $handle): bool
    {
        if (empty($this->files[$handle])) {
            throw new \RuntimeException("Template error: no file for handle '$handle'");
        }

        $this->initStartupVars();

        $templatePath = $this->files[$handle];
        $templateName = $this->getRelativeTemplateName($templatePath);

        $context = [
            '_tpldata' => $this->blockData,
            'L' => $this->lang,
            'V' => $this->variables
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
            throw new \RuntimeException("Template render error '$handle': " . $e->getMessage(), 0, $e);
        }

        $renderTime = (microtime(true) - $renderStart) * 1000;
        self::$totalRenderTime += $renderTime;

        $output = $this->injectDebugBar($handle, $output, $renderTime);

        echo $output;
        return true;
    }

    /**
     * Initialize Twig environment
     */
    private function initializeTwig(): void
    {
        $useCache = (bool)config()->get('twig.cache_enabled', true);
        $factory = new TwigEnvironmentFactory();
        $this->twig = $factory->create($this->rootDir, $this->cacheDir, $useCache);

        $this->twig->addGlobal('_tpldata', $this->blockData);
        $this->twig->addGlobal('V', $this->variables);
        $this->twig->addGlobal('L', $this->lang);
    }

    /**
     * Register template file
     */
    private function registerTemplate(string $handle, string $filename): void
    {
        $this->files[$handle] = $this->buildTemplatePath($filename);

        if (!$this->files[$handle]) {
            throw new \RuntimeException("Template error: invalid template $filename");
        }

        if (!@file_exists($this->files[$handle])) {
            throw new \RuntimeException('Template not found: ' . hide_bb_path($this->files[$handle]));
        }
    }

    /**
     * Build a full template path
     */
    private function buildTemplatePath(string $filename): string
    {
        // Handle admin templates
        if (str_starts_with($filename, 'admin/')) {
            $adminDir = dirname($this->rootDir) . '/admin';
            if (is_dir($adminDir)) {
                return $adminDir . '/' . substr($filename, 6);
            }
        }

        // Relative path
        if ($filename[0] !== '/' && (strlen($filename) < 2 || $filename[1] !== ':')) {
            return $this->rootDir . '/' . $filename;
        }

        return $filename;
    }

    /**
     * Initialize standard template variables
     */
    private function initStartupVars(): void
    {
        $this->variables['LANG'] ??= config()->get('default_lang');

        $tpl = $this->rootDir . '/';
        if (str_starts_with($tpl, './')) {
            $tpl = substr($tpl, 2);
        }
        $this->variables['TEMPLATE'] ??= $tpl;
        $this->variables['TEMPLATE_NAME'] ??= $this->templateName;

        $this->twig->addGlobal('V', $this->variables);
    }

    /**
     * Convert full path to Twig template name
     */
    private function getRelativeTemplateName(string $fullPath): string
    {
        $fullPath = realpath($fullPath) ?: $fullPath;

        // Admin template - use @admin namespace
        $adminDir = realpath(dirname($this->rootDir) . '/admin');
        if ($adminDir && str_starts_with($fullPath, $adminDir . '/')) {
            return '@admin/' . str_replace($adminDir . '/', '', $fullPath);
        }

        // Current theme directory
        $rootDir = realpath($this->rootDir);
        if ($rootDir && str_starts_with($fullPath, $rootDir . '/')) {
            return str_replace($rootDir . '/', '', $fullPath);
        }

        // Default theme fallback
        $defaultDir = realpath(dirname($this->rootDir) . '/default');
        if ($defaultDir && str_starts_with($fullPath, $defaultDir . '/')) {
            return str_replace($defaultDir . '/', '', $fullPath);
        }

        return basename($fullPath);
    }

    /**
     * Injects the debug bar into the given output for debugging purposes.
     *
     * @param string $handle The handle representing the section of the page being rendered (e.g., 'page_header', 'page_footer').
     * @param string $output The current HTML output of the section being rendered.
     * @param float $renderTime The time taken to render the section, in milliseconds.
     *
     * @return string The modified HTML output with the debug bar included, if enabled, or the original output if the debug bar is disabled.
     */
    private function injectDebugBar(string $handle, string $output, float $renderTime): string
    {
        $showDebugBar = config()->get('twig.debug_bar', false);

        if (!$showDebugBar) {
            return $output;
        }

        if ($handle === 'page_header' && stripos($output, '<body') !== false) {
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
                $this->templateName,
                $renderTime
            );
            $output = preg_replace('/(<body[^>]*>)/i', '$1' . $debugBar, $output, 1);
        }

        if ($handle === 'page_footer') {
            $legacyTemplates = Loaders\LegacyTemplateLoader::getLegacyTemplates();
            $nativeTemplates = Loaders\LegacyTemplateLoader::getNativeTemplates();
            $legacyCount = count($legacyTemplates);
            $nativeCount = count($nativeTemplates);
            $totalCount = $legacyCount + $nativeCount;

            if ($totalCount > 0) {
                $legacyList = $legacyCount > 0 ? implode(', ', $legacyTemplates) : '';
                $nativeList = $nativeCount > 0 ? implode(', ', $nativeTemplates) : '';
                $label = "Legacy: $legacyCount, Native: $nativeCount";
                $list = $legacyList . ($legacyList && $nativeList ? ' | ' : '') . $nativeList;
            } else {
                $label = 'Templates';
                $list = '(from cache)';
            }

            $output .= sprintf(
                '<script>document.getElementById("twig-templates").innerHTML = "%s: <b>%s</b>";' .
                'document.getElementById("twig-render-time").innerHTML = "Total render: <b>%.2fms</b>";</script>',
                $label,
                addslashes($list),
                self::$totalRenderTime
            );
        }

        return $output;
    }
}
