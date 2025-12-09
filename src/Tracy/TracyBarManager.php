<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Tracy;

use Tracy\Debugger;

/**
 * Tracy Debug Bar Manager
 *
 * Manages the integration of Tracy debug bar with TorrentPier.
 * Provides a modern debug interface while keeping Whoops for error handling.
 */
class TracyBarManager
{
    private static ?self $instance = null;
    private bool $initialized = false;

    /** @var float|null Captured execution time from page_footer */
    private ?float $capturedExecTime = null;

    /** @var float|null Captured SQL time from page_footer */
    private ?float $capturedSqlTime = null;

    private function __construct()
    {
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize Tracy debug bar
     *
     * IMPORTANT: This method enables Tracy bar ONLY.
     * Error handling remains with Whoops (configured in Dev.php).
     */
    public function init(): void
    {
        if ($this->initialized || !$this->isEnabled()) {
            return;
        }

        // Store current error handlers (set by Whoops)
        $errorHandler = set_error_handler(fn() => false);
        $exceptionHandler = set_exception_handler(fn() => null);
        restore_error_handler();
        restore_exception_handler();

        // Enable Tracy in development mode
        // This registers Tracy's bar and output buffer hooks
        Debugger::enable(Debugger::Development);

        // Immediately restore Whoops error handlers
        // Tracy's enable() registers its own handlers, but we want Whoops to handle errors
        if ($errorHandler) {
            set_error_handler($errorHandler);
        }
        if ($exceptionHandler) {
            set_exception_handler($exceptionHandler);
        }

        // Configure Tracy bar appearance
        Debugger::$showBar = true;
        Debugger::$maxDepth = 5;
        Debugger::$maxLength = 300;

        // Register custom panels
        $this->registerPanels();

        $this->initialized = true;
    }

    /**
     * Check if Tracy debug bar should be enabled
     */
    public function isEnabled(): bool
    {
        // Only enable for debug users, in web context, with tracy panel selected
        $debugPanel = config()->get('debug.panel');
        return defined('DBG_USER')
            && DBG_USER
            && php_sapi_name() !== 'cli'
            && in_array($debugPanel, ['tracy', 'both'], true);
    }

    /**
     * Register all custom TorrentPier panels
     */
    private function registerPanels(): void
    {
        $bar = Debugger::getBar();
        $panelConfig = config()->get('debug.tracy.panels', []);

        // Performance panel - always first (shows timing)
        if ($panelConfig['performance'] ?? true) {
            $bar->addPanel(new Panels\PerformancePanel(), 'tp-performance');
        }

        // Database panel - SQL queries
        if ($panelConfig['database'] ?? true) {
            $bar->addPanel(new Panels\DatabasePanel(), 'tp-database');
        }

        // Template panel - Twig debugging
        if ($panelConfig['template'] ?? true) {
            $bar->addPanel(new Panels\TemplatePanel(), 'tp-template');
        }

        // Cache panel - Cache/Datastore operations
        if ($panelConfig['cache'] ?? true) {
            $bar->addPanel(new Panels\CachePanel(), 'tp-cache');
        }
    }

    /**
     * Check if Tracy is currently initialized
     */
    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    /**
     * Capture performance data at the correct measurement point (page_footer)
     * This ensures Tracy shows the same timing as legacy debug bar
     */
    public function capturePerformanceData(float $execTime, float $sqlTime): void
    {
        $this->capturedExecTime = $execTime;
        $this->capturedSqlTime = $sqlTime;
    }

    /**
     * Get captured execution time (in seconds)
     */
    public function getCapturedExecTime(): ?float
    {
        return $this->capturedExecTime;
    }

    /**
     * Get captured SQL time (in seconds)
     */
    public function getCapturedSqlTime(): ?float
    {
        return $this->capturedSqlTime;
    }

    /**
     * Prevent cloning
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new \LogicException('Cannot unserialize a singleton.');
    }
}
