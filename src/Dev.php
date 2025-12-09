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
     * Check if SQL debugging is allowed
     * Used by DatabaseDebugger, CacheManager, DatastoreManager to enable debug data collection
     */
    public function checkSqlDebugAllowed(): bool
    {
        return SQL_DEBUG && DBG_USER;
    }

    /**
     * Format SQL query for display
     */
    public function formatShortQuery(string $sql, bool $esc_html = false): string
    {
        $sql = str_compact($sql);
        return $esc_html ? htmlCHR($sql, true) : $sql;
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
