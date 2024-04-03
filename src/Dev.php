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
    public static string $envType;

    public static $whoops;

    /**
     * Base debug functionality init
     *
     * @return void
     */
    public static function initDebug(): void
    {
        global $bb_cfg;

        self::$envType = (string)env('APP_ENV', 'local');
        self::$whoops = new Run;

        if (self::$envType === 'production') {
            if ($bb_cfg['error_reporting']['enabled']) {
                if ($bb_cfg['error_reporting']['method'] == 'both' || $bb_cfg['error_reporting']['method'] == 'bugsnag') {
                    self::getBugsnag($bb_cfg['error_reporting']['bugsnag']);
                }
                if ($bb_cfg['error_reporting']['method'] == 'both' || $bb_cfg['error_reporting']['method'] == 'telegram') {
                    self::getTelegramSender($bb_cfg['error_reporting']['telegram']);
                }
            }
        } else {
            if (APP_DEBUG) {
                self::getWhoops();
            }
        }

        self::$whoops->register();
    }

    /**
     * Bugsnag debug driver
     *
     * @param array $config
     * @return void
     */
    private static function getBugsnag(array $config): void
    {
        Handler::register(Client::make($config['api_key']));
    }

    /**
     * Send debug via Telegram
     *
     * @param array $config
     * @return void
     */
    private static function getTelegramSender(array $config): void
    {
        $telegramSender = new PlainTextHandler();
        $telegramSender->loggerOnly(true);
        $telegramSender->setLogger((new Logger(
            APP_NAME,
            [(new TelegramHandler($config['token'], (int)$config['chat_id'], timeout: $config['timeout']))
                ->setFormatter(new TelegramFormatter())]
        )));
        self::$whoops->pushHandler($telegramSender);
    }

    /**
     * Whoops debug driver
     *
     * @return void
     */
    private static function getWhoops(): void
    {
        /**
         * Show errors on page
         */
        self::$whoops->pushHandler(new PrettyPageHandler);

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
        self::$whoops->pushHandler($loggingInConsole);

        /**
         * Log errors in file
         */
        if (ini_get('log_errors') == 1) {
            $loggingInFile = new PlainTextHandler();
            $loggingInFile->loggerOnly(true);
            $loggingInFile->setLogger((new Logger(
                APP_NAME,
                [(new StreamHandler(WHOOPS_LOG_FILE))
                    ->setFormatter((new LineFormatter(null, null, true)))]
            )));
            self::$whoops->pushHandler($loggingInFile);
        }
    }

    /**
     * Get SQL debug log
     *
     * @return string
     * @throws Exception
     */
    public static function get_sql_log(): string
    {
        global $DBS, $CACHES, $datastore;

        $log = '';

        foreach ($DBS->srv as $srv_name => $db_obj) {
            $log .= !empty($db_obj->dbg) ? self::get_sql_log_html($db_obj, "database: $srv_name [{$db_obj->engine}]") : '';
        }

        foreach ($CACHES->obj as $cache_name => $cache_obj) {
            if (!empty($cache_obj->db->dbg)) {
                $log .= self::get_sql_log_html($cache_obj->db, "cache: $cache_name [{$cache_obj->db->engine}]");
            } elseif (!empty($cache_obj->dbg)) {
                $log .= self::get_sql_log_html($cache_obj, "cache: $cache_name [{$cache_obj->engine}]");
            }
        }

        if (!empty($datastore->db->dbg)) {
            $log .= self::get_sql_log_html($datastore->db, "cache: datastore [{$datastore->db->engine}]");
        } elseif (!empty($datastore->dbg)) {
            $log .= self::get_sql_log_html($datastore, "cache: datastore [{$datastore->engine}]");
        }

        return $log;
    }

    /**
     * Sql debug status
     *
     * @return bool
     */
    public static function sql_dbg_enabled(): bool
    {
        return (SQL_DEBUG && APP_DEBUG && !empty($_COOKIE['sql_log']));
    }

    /**
     * Short query
     *
     * @param string $sql
     * @param bool $esc_html
     * @return string
     */
    public static function short_query(string $sql, bool $esc_html = false): string
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

    /**
     * Get SQL query html log
     *
     * @param object $db_obj
     * @param string $log_name
     *
     * @return string
     * @throws Exception
     */
    private static function get_sql_log_html(object $db_obj, string $log_name): string
    {
        $log = '';

        foreach ($db_obj->dbg as $i => $dbg) {
            $id = "sql_{$i}_" . random_int(0, mt_getrandmax());
            $sql = self::short_query($dbg['sql'], true);
            $time = sprintf('%.4f', $dbg['time']);
            $perc = '[' . round($dbg['time'] * 100 / $db_obj->sql_timetotal) . '%]';
            $info = !empty($dbg['info']) ? $dbg['info'] . ' [' . $dbg['src'] . ']' : $dbg['src'];

            $log .= ''
                . '<div onmouseout="$(this).removeClass(\'sqlHover\');" onmouseover="$(this).addClass(\'sqlHover\');" onclick="$(this).toggleClass(\'sqlHighlight\');" class="sqlLogRow" title="' . $info . '">'
                . '<span style="letter-spacing: -1px;">' . $time . ' </span>'
                . '<span class="copyElement" data-clipboard-target="#' . $id . '" title="Copy to clipboard" style="color: gray; letter-spacing: -1px;">' . $perc . '</span>'
                . ' '
                . '<span style="letter-spacing: 0;" id="' . $id . '">' . $sql . '</span>'
                . '<span style="color: gray"> # ' . $info . ' </span>'
                . '</div>'
                . "\n";
        }
        return '
		<div class="sqlLogTitle">' . $log_name . '</div>
		' . $log . '
	';
    }
}
