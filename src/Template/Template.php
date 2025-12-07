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
    private static array $instances = [];
    private static float $totalRenderTime = 0;

    /** Reserved context keys that should not be overwritten */
    private const array RESERVED_KEYS = ['L', '_tpldata', 'V', 'IMG'];

    /** @var array<array{variable: string, template: string, source: string, time: float}> */
    private static array $variableConflicts = [];

    /** @var array<array{variable: string, old_value: mixed, new_value: mixed, source: string, time: float}> */
    private static array $variableShadowing = [];

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

    private function __construct(string $root = '.')
    {
        $this->variables = &$this->blockData['.'][0];
        $this->rootDir = TwigEnvironmentFactory::normalizePath($root);
        $this->templateName = basename($root);
        $this->cacheDir = TwigEnvironmentFactory::normalizePath(CACHE_DIR . '/');

        if (!is_dir($this->rootDir)) {
            throw new \RuntimeException("Template directory not found: $this->templateName");
        }

        $this->initializeTwig();
    }

    private static ?self $defaultInstance = null;

    public static function getInstance(?string $root = null): self
    {
        if ($root === null && self::$defaultInstance !== null) {
            return self::$defaultInstance;
        }

        $root = $root ?: '.';
        $key = md5($root);

        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($root);
        }

        // When called with a proper templates directory, make it the default
        // This allows setup_style() to override any early initialization
        $normalizedRoot = TwigEnvironmentFactory::normalizePath($root);
        if ($root !== '.' && str_contains($normalizedRoot, 'styles/templates')) {
            self::$defaultInstance = self::$instances[$key];
        } elseif (self::$defaultInstance === null) {
            self::$defaultInstance = self::$instances[$key];
        }

        return self::$instances[$key];
    }

    public function getCacheDir(): string
    {
        return $this->cacheDir;
    }

    /**
     * Get the Twig environment instance
     */
    public function getTwig(): ?Environment
    {
        return $this->twig;
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
    public function assign_vars(array $variables, bool $trackShadowing = false): void
    {
        foreach ($variables as $key => $value) {
            // Track when a variable is being overwritten with a different value
            if ($trackShadowing && array_key_exists($key, $this->variables) && $this->variables[$key] !== $value) {
                $this->logVariableShadowing($key, $this->variables[$key], $value);
            }
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
                $key = $blocks[$i] . '.';
                // Auto-initialize the parent block if missing (prevents PHP 8+ errors)
                if (empty($data[$key])) {
                    $data[$key][] = [];
                }
                $data = &$data[$key];
                $data = &$data[count($data) - 1];
            }
            $data[$blocks[$blockCount] . '.'][] = $variables;
        } else {
            $this->blockData[$block . '.'][] = $variables;
        }

        $this->twig->addGlobal('_tpldata', $this->blockData);
        return true;
    }

    /**
     * Render a native Twig template with variables directly in context
     * For new .twig templates - cleaner API without V. prefix
     */
    public function render(string $template, array $variables = []): void
    {
        $templatePath = $this->buildTemplatePath($template);

        if (!@file_exists($templatePath)) {
            throw new \RuntimeException('Template not found: ' . hide_bb_path($templatePath));
        }

        $templateName = $this->getRelativeTemplateName($templatePath);

        // Variables directly at root level, plus L for language and _tpldata for blocks
        $context = array_merge($variables, [
            '_tpldata' => $this->blockData,
            'L' => lang(),
        ]);

        $renderStart = microtime(true);

        try {
            $output = $this->twig->render($templateName, $context);
        } catch (\Exception $e) {
            throw new \RuntimeException("Template render error '$template': " . $e->getMessage(), 0, $e);
        }

        $renderTime = (microtime(true) - $renderStart) * 1000;
        self::$totalRenderTime += $renderTime;

        echo $output;
    }

    /**
     * Parse and output template (legacy API)
     */
    public function pparse(string $handle): bool
    {
        if (empty($this->files[$handle])) {
            throw new \RuntimeException("Template error: no file for handle '$handle'");
        }

        $this->initStartupVars();

        $templatePath = $this->files[$handle];
        $templateName = $this->getRelativeTemplateName($templatePath);
        $isNativeTwig = str_ends_with($templateName, '.twig');

        $context = [
            '_tpldata' => $this->blockData,
            'L' => lang(),
            'V' => $this->variables,
        ];

        // For native Twig templates, expose V variables at root level for cleaner syntax
        if ($isNativeTwig) {
            $context = $this->exposeVariablesToRoot($context, $templateName);
        }

        // Reset tracking on page_header
        if ($handle === 'page_header') {
            self::$totalRenderTime = 0;
            self::$variableConflicts = [];
            self::$variableShadowing = [];
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
     * Expose V variables to root context for cleaner template syntax
     * Logs conflicts when variables would override reserved keys
     */
    private function exposeVariablesToRoot(array $context, string $templateName): array
    {
        foreach ($this->variables as $key => $value) {
            if (in_array($key, self::RESERVED_KEYS, true)) {
                $this->logVariableConflict($key, $templateName);
                continue;
            }
            $context[$key] = $value;
        }

        return $context;
    }

    /**
     * Log a variable conflict for debugging
     */
    private function logVariableConflict(string $variable, string $template): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        $source = 'unknown';
        foreach ($backtrace as $frame) {
            if (isset($frame['file']) && !str_contains(TwigEnvironmentFactory::normalizePath($frame['file']), 'src/Template/')) {
                $source = basename($frame['file']) . ':' . ($frame['line'] ?? '?');
                break;
            }
        }

        $conflict = [
            'variable' => $variable,
            'template' => $template,
            'source' => $source,
            'time' => microtime(true)
        ];

        self::$variableConflicts[] = $conflict;

        // Log to file
        $msg = str_repeat('=', 60) . LOG_LF;
        $msg .= 'Template Variable Conflict' . LOG_LF;
        $msg .= str_repeat('=', 60) . LOG_LF;
        $msg .= "Variable: $variable" . LOG_LF;
        $msg .= "Template: $template" . LOG_LF;
        $msg .= "Source:   $source" . LOG_LF;
        $msg .= 'Time:     ' . date('Y-m-d H:i:s') . LOG_LF;
        $msg .= 'Note:     This variable conflicts with a reserved key and was not exposed to root context.' . LOG_LF;
        $msg .= '          Use V.' . $variable . ' in the template instead.' . LOG_LF;

        if (function_exists('bb_log')) {
            bb_log($msg, 'template_conflicts', false);
        }
    }

    /**
     * Get variable conflicts logged during this request
     */
    public static function getVariableConflicts(): array
    {
        return self::$variableConflicts;
    }

    /**
     * Reset variable conflicts tracking
     */
    public static function resetVariableConflicts(): void
    {
        self::$variableConflicts = [];
    }

    /**
     * Log when a variable is being overwritten with a different value
     */
    private function logVariableShadowing(string $variable, mixed $oldValue, mixed $newValue): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        $source = 'unknown';
        foreach ($backtrace as $frame) {
            if (isset($frame['file'])) {
                $file = TwigEnvironmentFactory::normalizePath($frame['file']);
                if (!str_contains($file, 'src/Template/') && !str_contains($file, 'library/includes/functions.php')) {
                    $source = basename($frame['file']) . ':' . ($frame['line'] ?? '?');
                    break;
                }
            }
        }

        $shadowing = [
            'variable' => $variable,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'source' => $source,
            'time' => microtime(true)
        ];

        self::$variableShadowing[] = $shadowing;

        // Format values for logging (truncate long values)
        $oldStr = is_scalar($oldValue) ? (string)$oldValue : get_debug_type($oldValue);
        $newStr = is_scalar($newValue) ? (string)$newValue : get_debug_type($newValue);
        if (strlen($oldStr) > 50) {
            $oldStr = substr($oldStr, 0, 47) . '...';
        }
        if (strlen($newStr) > 50) {
            $newStr = substr($newStr, 0, 47) . '...';
        }

        // Log to file
        $msg = str_repeat('=', 60) . LOG_LF;
        $msg .= 'Template Variable Shadowing' . LOG_LF;
        $msg .= str_repeat('=', 60) . LOG_LF;
        $msg .= "Variable: $variable" . LOG_LF;
        $msg .= "Old:      $oldStr" . LOG_LF;
        $msg .= "New:      $newStr" . LOG_LF;
        $msg .= "Source:   $source" . LOG_LF;
        $msg .= 'Time:     ' . date('Y-m-d H:i:s') . LOG_LF;
        $msg .= 'Note:     This variable was overwritten. Check if this is intentional.' . LOG_LF;

        if (function_exists('bb_log')) {
            bb_log($msg, 'template_shadowing', false);
        }
    }

    /**
     * Get variable shadowing logged during this request
     */
    public static function getVariableShadowing(): array
    {
        return self::$variableShadowing;
    }

    /**
     * Reset variable shadowing tracking
     */
    public static function resetVariableShadowing(): void
    {
        self::$variableShadowing = [];
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
        $this->twig->addGlobal('L', lang());
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
        // Normalize filename separators
        $filename = TwigEnvironmentFactory::normalizePath($filename);

        // Handle admin templates
        if (str_starts_with($filename, 'admin/')) {
            $adminDir = TwigEnvironmentFactory::normalizePath(dirname($this->rootDir) . '/admin');
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
        // Normalize all paths to forward slashes for consistent comparison
        $fullPath = TwigEnvironmentFactory::normalizePath(realpath($fullPath) ?: $fullPath);

        // Admin template - use @admin namespace
        $adminDir = realpath(dirname($this->rootDir) . '/admin');
        if ($adminDir) {
            $adminDir = TwigEnvironmentFactory::normalizePath($adminDir);
            if (str_starts_with($fullPath, $adminDir . '/')) {
                return '@admin/' . substr($fullPath, strlen($adminDir) + 1);
            }
        }

        // Current theme directory
        $rootDir = realpath($this->rootDir);
        if ($rootDir) {
            $rootDir = TwigEnvironmentFactory::normalizePath($rootDir);
            if (str_starts_with($fullPath, $rootDir . '/')) {
                return substr($fullPath, strlen($rootDir) + 1);
            }
        }

        // Default theme fallback
        $defaultDir = realpath(dirname($this->rootDir) . '/default');
        if ($defaultDir) {
            $defaultDir = TwigEnvironmentFactory::normalizePath($defaultDir);
            if (str_starts_with($fullPath, $defaultDir . '/')) {
                return substr($fullPath, strlen($defaultDir) + 1);
            }
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
