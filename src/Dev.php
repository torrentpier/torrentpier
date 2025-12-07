<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use Bugsnag\Client;
use Exception;
use jacklul\MonologTelegramHandler\TelegramFormatter;
use jacklul\MonologTelegramHandler\TelegramHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run;

/**
 * Development and Debugging System
 *
 * Singleton class that provides development and debugging functionality
 * including error handling, SQL logging, and debugging utilities.
 */
class Dev
{
    private static ?Dev $instance = null;

    /**
     * Whoops instance
     *
     * @var Run
     */
    private Run $whoops;

    /**
     * Initialize debugging system
     */
    private function __construct()
    {
        $this->whoops = new Run();

        if ($this->isDebugEnabled()) {
            $this->getWhoopsOnPage();
        } else {
            $this->getWhoopsPlaceholder();
        }
        $this->getWhoopsLogger();

        if (!$this->isDevelopmentEnvironment()) {
            $this->getTelegramSender();
            $this->getBugsnag();
        }

        $this->whoops->register();
    }

    /**
     * Get the singleton instance of Dev
     */
    public static function getInstance(): Dev
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the dev system (for compatibility)
     */
    public static function init(): Dev
    {
        return self::getInstance();
    }

    /**
     * [Whoops] Bugsnag handler
     *
     * @return void
     */
    private function getBugsnag(): void
    {
        if (!config()->get('bugsnag.enabled')) {
            return;
        }

        $bugsnag = Client::make(config()->get('bugsnag.api_key'));
        $this->whoops->pushHandler(function ($e) use ($bugsnag) {
            $bugsnag->notifyException($e);
        });
    }

    /**
     * [Whoops] Telegram handler
     *
     * @return void
     */
    private function getTelegramSender(): void
    {
        if (!config()->get('telegram_sender.enabled')) {
            return;
        }

        $telegramSender = new PlainTextHandler();
        $telegramSender->loggerOnly(true);
        $telegramSender->setLogger(new Logger(
            APP_NAME,
            [new TelegramHandler(config()->get('telegram_sender.token'), (int) config()->get('telegram_sender.chat_id'), timeout: (int) config()->get('telegram_sender.timeout'))
                ->setFormatter(new TelegramFormatter())]
        ));
        $this->whoops->pushHandler($telegramSender);
    }

    /**
     * [Whoops] On page handler (in debug)
     *
     * @return void
     */
    private function getWhoopsOnPage(): void
    {
        /**
         * Show errors on page with enhanced database information
         */
        $prettyPageHandler = new \TorrentPier\Whoops\EnhancedPrettyPageHandler();
        foreach (config()->get('whoops.blacklist', []) as $key => $secrets) {
            foreach ($secrets as $secret) {
                $prettyPageHandler->blacklist($key, $secret);
            }
        }
        $this->whoops->pushHandler($prettyPageHandler);

        /**
         * Show log in browser console
         */
        $loggingInConsole = new PlainTextHandler();
        $loggingInConsole->loggerOnly(true);
        $loggingInConsole->setLogger(new Logger(
            APP_NAME,
            [new BrowserConsoleHandler()
                ->setFormatter(new LineFormatter(null, null, true))]
        ));
        $this->whoops->pushHandler($loggingInConsole);
    }

    /**
     * [Whoops] Logger handler
     *
     * @return void
     */
    private function getWhoopsLogger(): void
    {
        if ((int) ini_get('log_errors') !== 1) {
            return;
        }

        $loggingInFile = new PlainTextHandler();
        $loggingInFile->loggerOnly(true);
        $loggingInFile->setLogger(new Logger(
            APP_NAME,
            [new StreamHandler(WHOOPS_LOG_FILE)
                ->setFormatter(new LineFormatter(null, null, true))]
        ));
        $this->whoops->pushHandler($loggingInFile);
    }

    /**
     * [Whoops] Placeholder handler (non debug)
     *
     * @return void
     */
    private function getWhoopsPlaceholder(): void
    {
        $this->whoops->pushHandler(function ($e) {
            echo config()->get('whoops.error_message');
            if (config()->get('whoops.show_error_details')) {
                echo "<hr/>Error: " . $this->sanitizeErrorMessage($e->getMessage()) . ".";
            }
        });
    }

    /**
     * Sanitize error message to hide sensitive information
     *
     * @param string $message
     * @return string
     */
    private function sanitizeErrorMessage(string $message): string
    {
        // Patterns to sanitize
        $patterns = [
            // Database credentials
            '/password[\s=:\'"]+[^\s\'"]+/i' => 'password=***',
            '/passwd[\s=:\'"]+[^\s\'"]+/i' => 'passwd=***',
            '/pwd[\s=:\'"]+[^\s\'"]+/i' => 'pwd=***',
            // File paths
            '/[a-z]:\\\\[^\s]+/i' => '***\\***',
            '/\/[a-z0-9_\-\/]+\/(public|src|library|vendor)\/[^\s]+/i' => '***/$1/***',
            // API keys and tokens
            '/api[_-]?key[\s=:\'"]+[^\s\'"]+/i' => 'api_key=***',
            '/token[\s=:\'"]+[^\s\'"]+/i' => 'token=***',
            // Database connection strings
            '/mysql:\/\/[^@]+@[^\s]+/' => 'mysql://***:***@***',
            // IP addresses (optional, uncomment if needed)
            // '/\b(?:[0-9]{1,3}\.){3}[0-9]{1,3}\b/' => '***.***.***.***',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $message = preg_replace($pattern, $replacement, $message);
        }

        return htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get SQL debug log (instance method)
     *
     * @return string
     * @throws Exception
     */
    public function getSqlLogInstance(): string
    {
        $log = '';
        $totalLegacyQueries = 0;

        // Check for legacy queries across all database instances
        $server_names = \TorrentPier\Database\DatabaseFactory::getServerNames();
        foreach ($server_names as $srv_name) {
            try {
                $db_obj = \TorrentPier\Database\DatabaseFactory::getInstance($srv_name);
                if (!empty($db_obj->debugger->legacy_queries)) {
                    $totalLegacyQueries += count($db_obj->debugger->legacy_queries);
                }
            } catch (\Exception $e) {
                // Skip if server not available
            }
        }

        // Add a warning banner if legacy queries were detected
        if ($totalLegacyQueries > 0) {
            $log .= '<div style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin-bottom: 10px; border-radius: 4px;">'
                . '<strong>⚠️ Legacy Query Warning:</strong> '
                . $totalLegacyQueries . ' quer' . ($totalLegacyQueries > 1 ? 'ies' : 'y') . ' with duplicate columns detected and automatically fixed. '
                . 'These queries should be updated to explicitly select columns. '
                . 'Check the legacy_queries.log file for details.'
                . '</div>';
        }

        // Check for template variable conflicts
        $templateConflicts = \TorrentPier\Template\Template::getVariableConflicts();
        if (!empty($templateConflicts)) {
            $conflictCount = count($templateConflicts);
            $conflictList = array_map(fn($c) => $c['variable'] . ' in ' . $c['template'], $templateConflicts);
            $log .= '<div style="background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 10px; margin-bottom: 10px; border-radius: 4px;">'
                . '<strong>⚠️ Template Variable Conflict:</strong> '
                . $conflictCount . ' variable' . ($conflictCount > 1 ? 's' : '') . ' conflict with reserved keys. '
                . 'Details: ' . implode(', ', $conflictList) . '. '
                . 'Check the template_conflicts.log file for details.'
                . '</div>';
        }

        // Check for template variable shadowing (variables overwritten with different values)
        $templateShadowing = \TorrentPier\Template\Template::getVariableShadowing();
        if (!empty($templateShadowing)) {
            $shadowCount = count($templateShadowing);
            $shadowList = array_map(function ($s) {
                $old = is_scalar($s['old_value']) ? (string) $s['old_value'] : get_debug_type($s['old_value']);
                $new = is_scalar($s['new_value']) ? (string) $s['new_value'] : get_debug_type($s['new_value']);
                if (strlen($old) > 20) {
                    $old = substr($old, 0, 17) . '...';
                }
                if (strlen($new) > 20) {
                    $new = substr($new, 0, 17) . '...';
                }
                return $s['variable'] . " ({$old} → {$new})";
            }, $templateShadowing);
            $log .= '<div style="background-color: #fff0e6; border: 1px solid #ffcc99; color: #cc5500; padding: 10px; margin-bottom: 10px; border-radius: 4px;">'
                . '<strong>🔄 Template Variable Shadowing:</strong> '
                . $shadowCount . ' variable' . ($shadowCount > 1 ? 's were' : ' was') . ' overwritten during render. '
                . 'Details: ' . implode(', ', $shadowList) . '. '
                . 'Check the template_shadowing.log file for details.'
                . '</div>';
        }

        // Get debug information from a new database system
        foreach ($server_names as $srv_name) {
            try {
                $db_obj = \TorrentPier\Database\DatabaseFactory::getInstance($srv_name);
                $log .= !empty($db_obj->dbg) ? $this->getSqlLogHtml($db_obj, "database: $srv_name [{$db_obj->engine}]") : '';
            } catch (\Exception $e) {
                // Skip if server not available
            }
        }

        // Get cache system debug information
        $cacheSystem = \TorrentPier\Cache\UnifiedCacheSystem::getInstance();
        $cacheObjects = $cacheSystem->obj; // Uses magic __get method for backward compatibility

        foreach ($cacheObjects as $cache_name => $cache_obj) {
            if (!empty($cache_obj->db->dbg)) {
                $log .= $this->getSqlLogHtml($cache_obj->db, "cache: $cache_name [{$cache_obj->db->engine}]");
            } elseif (!empty($cache_obj->dbg)) {
                $log .= $this->getSqlLogHtml($cache_obj, "cache: $cache_name [{$cache_obj->engine}]");
            }
        }

        // Get datastore debug information
        $datastore = datastore();
        if (!empty($datastore->db->dbg)) {
            $log .= $this->getSqlLogHtml($datastore->db, "cache: datastore [{$datastore->db->engine}]");
        } elseif (!empty($datastore->dbg)) {
            $log .= $this->getSqlLogHtml($datastore, "cache: datastore [{$datastore->engine}]");
        }

        return $log;
    }

    /**
     * Sql debug status (instance method)
     *
     * @return bool
     */
    public function sqlDebugAllowedInstance(): bool
    {
        return (SQL_DEBUG && DBG_USER && !empty($_COOKIE['sql_log']));
    }

    /**
     * Get SQL query html log
     *
     * @param object $db_obj
     * @param string $log_name
     *
     * @return string
     * @throws Exception
     */
    private function getSqlLogHtml(object $db_obj, string $log_name): string
    {
        $log = '';

        foreach ($db_obj->dbg as $i => $dbg) {
            $id = "sql_{$i}_" . random_int(0, mt_getrandmax());
            $sql = $this->shortQueryInstance($dbg['sql'], true);
            $time = sprintf('%.3f', $dbg['time']);
            $perc = '[' . round($dbg['time'] * 100 / $db_obj->sql_timetotal) . '%]';
            // Use plain text version for title attribute to avoid HTML issues
            $info_plain = !empty($dbg['info_plain']) ? $dbg['info_plain'] . ' [' . $dbg['src'] . ']' : $dbg['src'];
            $info = !empty($dbg['info']) ? $dbg['info'] . ' [' . $dbg['src'] . ']' : $dbg['src'];

            // Check if this is a legacy query that needed compatibility fix
            $isLegacyQuery = !empty($dbg['is_legacy_query']);
            $rowClass = $isLegacyQuery ? 'sqlLogRow sqlLegacyRow' : 'sqlLogRow';
            $rowStyle = $isLegacyQuery ? ' style="background-color: #ffe6e6; border-left: 4px solid #dc3545; color: #721c24;"' : '';
            $legacyWarning = $isLegacyQuery ? '<span style="color: #dc3545; font-weight: bold; margin-right: 8px;">[LEGACY]</span>' : '';

            $log .= '<div onclick="$(this).toggleClass(\'sqlHighlight\');" class="' . $rowClass . '" title="' . htmlspecialchars($info_plain) . '"' . $rowStyle . '>'
                . $legacyWarning
                . '<span style="letter-spacing: -1px;">' . $time . ' </span>'
                . '<span class="copyElement" data-clipboard-target="#' . $id . '" title="Copy to clipboard" style="color: rgb(128,128,128); letter-spacing: -1px;">' . $perc . '</span>&nbsp;'
                . '<span style="letter-spacing: 0;" id="' . $id . '">' . $sql . '</span>'
                . '<span style="color: rgb(128,128,128);"> # ' . $info . ' </span>'
                . '</div>';
        }

        return '<div class="sqlLogTitle">' . $log_name . '</div>' . $log;
    }

    /**
     * Short query (instance method)
     *
     * @param string $sql
     * @param bool $esc_html
     * @return string
     */
    public function shortQueryInstance(string $sql, bool $esc_html = false): string
    {
        $max_len = 100;
        $sql = str_compact($sql);

        if (!empty($_COOKIE['sql_log_full'])) {
            if (mb_strlen($sql, DEFAULT_CHARSET) > $max_len) {
                $sql = mb_substr($sql, 0, 50) . ' [...cut...] ' . mb_substr($sql, -50);
            }
        }

        return $esc_html ? htmlCHR($sql, true) : $sql;
    }

    // Static methods for backward compatibility (proxy to instance methods)

    /**
     * Get SQL debug log (static)
     *
     * @return string
     * @throws Exception
     * @deprecated Use dev()->getSqlLog() instead
     */
    public static function getSqlLog(): string
    {
        return self::getInstance()->getSqlLogInstance();
    }

    /**
     * Sql debug status (static)
     *
     * @return bool
     * @deprecated Use dev()->sqlDebugAllowed() instead
     */
    public static function sqlDebugAllowed(): bool
    {
        return self::getInstance()->sqlDebugAllowedInstance();
    }

    /**
     * Short query (static)
     *
     * @param string $sql
     * @param bool $esc_html
     * @return string
     * @deprecated Use dev()->shortQuery() instead
     */
    public static function shortQuery(string $sql, bool $esc_html = false): string
    {
        return self::getInstance()->shortQueryInstance($sql, $esc_html);
    }

    /**
     * Get SQL debug log (for dev() singleton usage)
     *
     * @return string
     * @throws Exception
     */
    public function getSqlDebugLog(): string
    {
        return $this->getSqlLogInstance();
    }

    /**
     * Check if SQL debugging is allowed (for dev() singleton usage)
     *
     * @return bool
     */
    public function checkSqlDebugAllowed(): bool
    {
        return $this->sqlDebugAllowedInstance();
    }

    /**
     * Format SQL query for display (for dev() singleton usage)
     *
     * @param string $sql
     * @param bool $esc_html
     * @return string
     */
    public function formatShortQuery(string $sql, bool $esc_html = false): string
    {
        return $this->shortQueryInstance($sql, $esc_html);
    }

    /**
     * Get Whoops instance
     *
     * @return Run
     */
    public function getWhoops(): Run
    {
        return $this->whoops;
    }

    /**
     * Check if debug mode is enabled
     *
     * @return bool
     */
    public function isDebugEnabled(): bool
    {
        return DBG_USER;
    }

    /**
     * Check if application is in development environment
     *
     * @return bool
     */
    public function isDevelopmentEnvironment(): bool
    {
        return APP_ENV === 'development';
    }

    /**
     * Prevent cloning of the singleton instance
     */
    private function __clone() {}

    /**
     * Prevent serialization of the singleton instance
     */
    public function __serialize(): array
    {
        throw new \LogicException("Cannot serialize a singleton.");
    }

    /**
     * Prevent unserialization of the singleton instance
     */
    public function __unserialize(array $data): void
    {
        throw new \LogicException("Cannot unserialize a singleton.");
    }
}
