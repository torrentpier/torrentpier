<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use Bugsnag\Client;
use Bugsnag\Handler;

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
 * Class Dev
 * @package TorrentPier
 */
class Dev
{
    /**
     * Environment type
     *
     * @var string
     */
    private string $envType;

    /**
     * In production mode
     *
     * @var bool
     */
    public bool $isProduction = false;

    /**
     * Whoops instance
     *
     * @var Run
     */
    private Run $whoops;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->envType = strtolower(env('APP_ENV', 'production'));
        $this->whoops = new Run;

        switch ($this->envType) {
            case 'prod':
            case 'production':
                ini_set('display_errors', 0);
                ini_set('display_startup_errors', 0);
                $this->getWhoopsProduction();
                $this->isProduction = true;
                break;
            case 'dev':
            case 'local':
            case 'development':
                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);
                $this->getWhoops();
                break;
        }
        $this->getBugsnag();
        $this->getTelegramSender();

        $this->whoops->register();
    }

    /**
     * Bugsnag debug driver
     *
     * @return void
     */
    private function getBugsnag(): void
    {
        global $bb_cfg;

        if (!$bb_cfg['bugsnag']['enabled']) {
            return;
        }

        Handler::register(Client::make($bb_cfg['bugsnag']['api_key']));
    }

    /**
     * Telegram debug driver
     *
     * @return void
     */
    private function getTelegramSender(): void
    {
        global $bb_cfg;

        if ($bb_cfg['telegram_sender']['enabled']) {
            $telegramSender = new PlainTextHandler();
            $telegramSender->loggerOnly(true);
            $telegramSender->setLogger((new Logger(
                APP_NAME,
                [(new TelegramHandler($bb_cfg['telegram_sender']['token'], (int)$bb_cfg['telegram_sender']['chat_id'], timeout: (int)$bb_cfg['telegram_sender']['timeout']))
                    ->setFormatter(new TelegramFormatter())]
            )));
            $this->whoops->pushHandler($telegramSender);
        }
    }

    /**
     * Whoops production debug driver
     *
     * @return void
     */
    private function getWhoopsProduction(): void
    {
        $this->whoops->pushHandler(function () {
            global $bb_cfg;
            echo $bb_cfg['whoops']['error_message'];
        });
    }

    /**
     * Whoops debug driver
     *
     * @return void
     */
    private function getWhoops(): void
    {
        global $bb_cfg;

        /**
         * Show errors on page
         */
        $prettyPageHandler = new PrettyPageHandler();
        foreach ($bb_cfg['whoops']['blacklist'] as $key => $secrets) {
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

        /**
         * Log errors in file
         */
        if ((int)ini_get('log_errors') === 1) {
            $loggingInFile = new PlainTextHandler();
            $loggingInFile->loggerOnly(true);
            $loggingInFile->setLogger((new Logger(
                APP_NAME,
                [(new StreamHandler(WHOOPS_LOG_FILE))
                    ->setFormatter((new LineFormatter(null, null, true)))]
            )));
            $this->whoops->pushHandler($loggingInFile);
        }
    }

    /**
     * Get SQL debug log
     *
     * @return string
     * @throws Exception
     */
    public function getSqlLog(): string
    {
        global $DBS, $CACHES, $datastore;

        $log = '';

        foreach ($DBS->srv as $srv_name => $db_obj) {
            $log .= !empty($db_obj->dbg) ? $this->getSqlLogHtml($db_obj, "database: $srv_name [{$db_obj->engine}]") : '';
        }

        foreach ($CACHES->obj as $cache_name => $cache_obj) {
            if (!empty($cache_obj->db->dbg)) {
                $log .= $this->getSqlLogHtml($cache_obj->db, "cache: $cache_name [{$cache_obj->db->engine}]");
            } elseif (!empty($cache_obj->dbg)) {
                $log .= $this->getSqlLogHtml($cache_obj, "cache: $cache_name [{$cache_obj->engine}]");
            }
        }

        if (!empty($datastore->db->dbg)) {
            $log .= $this->getSqlLogHtml($datastore->db, "cache: datastore [{$datastore->db->engine}]");
        } elseif (!empty($datastore->dbg)) {
            $log .= $this->getSqlLogHtml($datastore, "cache: datastore [{$datastore->engine}]");
        }

        return $log;
    }

    /**
     * Sql debug status
     *
     * @return bool
     */
    public function sqlDebugAllowed(): bool
    {
        return (SQL_DEBUG && !$this->isProduction && !empty($_COOKIE['sql_log']));
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
            $sql = $this->shortQuery($dbg['sql'], true);
            $time = sprintf('%.4f', $dbg['time']);
            $perc = '[' . round($dbg['time'] * 100 / $db_obj->sql_timetotal) . '%]';
            $info = !empty($dbg['info']) ? $dbg['info'] . ' [' . $dbg['src'] . ']' : $dbg['src'];

            $log .= '<div onclick="$(this).toggleClass(\'sqlHighlight\');" class="sqlLogRow" title="' . $info . '">'
                . '<span style="letter-spacing: -1px;">' . $time . ' </span>'
                . '<span class="copyElement" data-clipboard-target="#' . $id . '" title="Copy to clipboard" style="color: rgb(128,128,128); letter-spacing: -1px;">' . $perc . '</span>&nbsp;'
                . '<span style="letter-spacing: 0;" id="' . $id . '">' . $sql . '</span>'
                . '<span style="color: rgb(128,128,128);"> # ' . $info . ' </span>'
                . '</div>';
        }

        return '<div class="sqlLogTitle">' . $log_name . '</div>' . $log;
    }

    /**
     * Short query
     *
     * @param string $sql
     * @param bool $esc_html
     * @return string
     */
    public function shortQuery(string $sql, bool $esc_html = false): string
    {
        $max_len = 100;
        $sql = str_compact($sql);

        if (!empty($_COOKIE['sql_log_full'])) {
            if (mb_strlen($sql, 'UTF-8') > $max_len) {
                $sql = mb_substr($sql, 0, 50) . ' [...cut...] ' . mb_substr($sql, -50);
            }
        }

        return $esc_html ? htmlCHR($sql, true) : $sql;
    }
}
