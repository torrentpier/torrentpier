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

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

use jacklul\MonologTelegramHandler\TelegramHandler;
use jacklul\MonologTelegramHandler\TelegramFormatter;

use Exception;

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
        $this->whoops = new Run;

        if (DBG_USER) {
            $this->getWhoopsOnPage();
        } else {
            $this->getWhoopsPlaceholder();
        }
        $this->getWhoopsLogger();

        if (APP_ENV !== 'local') {
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
        $telegramSender->setLogger((new Logger(
            APP_NAME,
            [(new TelegramHandler(config()->get('telegram_sender.token'), (int)config()->get('telegram_sender.chat_id'), timeout: (int)config()->get('telegram_sender.timeout')))
                ->setFormatter(new TelegramFormatter())]
        )));
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
         * Show errors on page
         */
        $prettyPageHandler = new PrettyPageHandler();
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
        $loggingInConsole->setLogger((new Logger(
            APP_NAME,
            [(new BrowserConsoleHandler())
                ->setFormatter((new LineFormatter(null, null, true)))]
        )));
        $this->whoops->pushHandler($loggingInConsole);
    }

    /**
     * [Whoops] Logger handler
     *
     * @return void
     */
    private function getWhoopsLogger(): void
    {
        if ((int)ini_get('log_errors') !== 1) {
            return;
        }

        $loggingInFile = new PlainTextHandler();
        $loggingInFile->loggerOnly(true);
        $loggingInFile->setLogger((new Logger(
            APP_NAME,
            [(new StreamHandler(WHOOPS_LOG_FILE))
                ->setFormatter((new LineFormatter(null, null, true)))]
        )));
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
            echo "<hr/>Error: {$e->getMessage()}.";
        });
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

        // Get debug information from new database system
        $server_names = \TorrentPier\Database\DatabaseFactory::getServerNames();
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

            $log .= '<div onclick="$(this).toggleClass(\'sqlHighlight\');" class="sqlLogRow" title="' . htmlspecialchars($info_plain) . '">'
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
     * Check if application is in local environment
     *
     * @return bool
     */
    public function isLocalEnvironment(): bool
    {
        return APP_ENV === 'local';
    }

    /**
     * Prevent cloning of the singleton instance
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization of the singleton instance
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}
