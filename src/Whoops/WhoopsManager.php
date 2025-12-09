<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Whoops;

use Bugsnag\Client;
use jacklul\MonologTelegramHandler\TelegramFormatter;
use jacklul\MonologTelegramHandler\TelegramHandler;
use LogicException;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run;

/**
 * Whoops Error Handler Manager
 *
 * Singleton that initializes Whoops for error display.
 * Integrates with Monolog for logging and external services (Telegram, Bugsnag).
 */
class WhoopsManager
{
    private static ?self $instance = null;
    private(set) Run $whoops;
    private bool $initialized = false;

    private function __construct()
    {
        $this->whoops = new Run();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize and register Whoops handlers
     */
    public function init(): self
    {
        if ($this->initialized) {
            return $this;
        }

        if ($this->isDebugEnabled()) {
            $this->registerPrettyPageHandler();
            $this->registerBrowserConsoleHandler();
        } else {
            $this->registerPlaceholderHandler();
        }

        $this->registerFileLogger();

        if (!$this->isDevelopmentEnvironment()) {
            $this->registerTelegramHandler();
            $this->registerBugsnagHandler();
        }

        $this->whoops->register();
        $this->initialized = true;

        return $this;
    }

    /**
     * Pretty error page for debug mode
     */
    private function registerPrettyPageHandler(): void
    {
        $handler = new EnhancedPrettyPageHandler();

        foreach (config()->get('whoops.blacklist', []) as $key => $secrets) {
            foreach ($secrets as $secret) {
                $handler->blacklist($key, $secret);
            }
        }

        $this->whoops->pushHandler($handler);
    }

    /**
     * Browser console logging for debug mode
     */
    private function registerBrowserConsoleHandler(): void
    {
        $handler = new PlainTextHandler();
        $handler->loggerOnly(true);
        $handler->setLogger(new Logger(
            APP_NAME,
            [new BrowserConsoleHandler()
                ->setFormatter(new LineFormatter(null, null, true))]
        ));
        $this->whoops->pushHandler($handler);
    }

    /**
     * Simple error placeholder for production
     */
    private function registerPlaceholderHandler(): void
    {
        $this->whoops->pushHandler(function ($e) {
            echo config()->get('whoops.error_message');
            if (config()->get('whoops.show_error_details')) {
                echo "<hr/>Error: " . $this->sanitizeErrorMessage($e->getMessage()) . ".";
            }
        });
    }

    /**
     * File logging
     */
    private function registerFileLogger(): void
    {
        if ((int) ini_get('log_errors') !== 1) {
            return;
        }

        $handler = new PlainTextHandler();
        $handler->loggerOnly(true);
        $handler->setLogger(new Logger(
            APP_NAME,
            [new StreamHandler(WHOOPS_LOG_FILE)
                ->setFormatter(new LineFormatter(null, null, true))]
        ));
        $this->whoops->pushHandler($handler);
    }

    /**
     * Telegram notifications for production errors
     */
    private function registerTelegramHandler(): void
    {
        if (!config()->get('telegram_sender.enabled')) {
            return;
        }

        $handler = new PlainTextHandler();
        $handler->loggerOnly(true);
        $handler->setLogger(new Logger(
            APP_NAME,
            [new TelegramHandler(
                config()->get('telegram_sender.token'),
                (int) config()->get('telegram_sender.chat_id'),
                timeout: (int) config()->get('telegram_sender.timeout')
            )->setFormatter(new TelegramFormatter())]
        ));
        $this->whoops->pushHandler($handler);
    }

    /**
     * Bugsnag error tracking for production
     */
    private function registerBugsnagHandler(): void
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
     * Sanitize an error message to hide sensitive information
     */
    private function sanitizeErrorMessage(string $message): string
    {
        $patterns = [
            '/password[\s=:\'\"]+[^\s\'\"]+/i' => 'password=***',
            '/passwd[\s=:\'\"]+[^\s\'\"]+/i' => 'passwd=***',
            '/pwd[\s=:\'\"]+[^\s\'\"]+/i' => 'pwd=***',
            '/[a-z]:\\\\[^\s]+/i' => '***\\***',
            '/\/[a-z0-9_\-\/]+\/(public|src|library|vendor)\/[^\s]+/i' => '***/$1/***',
            '/api[_-]?key[\s=:\'\"]+[^\s\'\"]+/i' => 'api_key=***',
            '/token[\s=:\'\"]+[^\s\'\"]+/i' => 'token=***',
            '/mysql:\/\/[^@]+@[^\s]+/' => 'mysql://***:***@***',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $message = preg_replace($pattern, $replacement, $message);
        }

        return htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    }

    public function isDebugEnabled(): bool
    {
        return defined('DBG_USER') && DBG_USER;
    }

    private function isDevelopmentEnvironment(): bool
    {
        return defined('APP_ENV') && APP_ENV === 'development';
    }

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new LogicException('Cannot unserialize a singleton.');
    }
}
