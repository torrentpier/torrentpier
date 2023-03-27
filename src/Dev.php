<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
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

use Exception;

/**
 * Class Dev
 * @package TorrentPier
 */
class Dev
{
    /**
     * Base debug functionality init
     *
     * @return void
     */
    public static function debug_init(): void
    {
        global $bb_cfg;

        if ($bb_cfg['bugsnag']['enabled']) {
            if (env('APP_ENV', 'production') !== 'local') {
                $bugsnag = Client::make($bb_cfg['bugsnag']['api_key']);
                Handler::register($bugsnag);
            }
        } else {
            if (DBG_USER) {
                $whoops = new Run;

                /**
                 * Show errors on page
                 */
                $whoops->pushHandler(new PrettyPageHandler);

                /**
                 * Show log in browser console
                 */
                $loggingInConsole = new PlainTextHandler();
                $loggingInConsole->loggerOnly(true);
                $loggingInConsole->setLogger((new Logger(env('APP_NAME', 'TorrentPier'), [(new BrowserConsoleHandler())->setFormatter((new LineFormatter(null, null, true)))])));
                $whoops->pushHandler($loggingInConsole);

                /**
                 * Log errors in file
                 */
                if (ini_get('log_errors') == 1) {
                    $loggingInFile = new PlainTextHandler();
                    $loggingInFile->loggerOnly(true);
                    $loggingInFile->setLogger((new Logger(env('APP_NAME', 'TorrentPier'), [(new StreamHandler(WHOOPS_LOG_FILE))->setFormatter((new LineFormatter(null, null, true)))])));
                    $whoops->pushHandler($loggingInFile);
                }

                $whoops->register();
            }
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
            $log .= !empty($db_obj) ? self::get_sql_log_html($db_obj, "$srv_name [MySQL]") : '';
        }

        foreach ($CACHES->obj as $cache_name => $cache_obj) {
            if (!empty($cache_obj->db)) {
                $log .= self::get_sql_log_html($cache_obj->db, "cache: $cache_name [{$cache_obj->db->engine}]");
            } elseif (!empty($cache_obj->engine)) {
                $log .= self::get_sql_log_html($cache_obj, "cache: $cache_name [{$cache_obj->engine}]");
            }
        }

        if (!empty($datastore->db->dbg)) {
            $log .= self::get_sql_log_html($datastore->db, 'cache: datastore [' . $datastore->engine . ']');
        } elseif (!empty($datastore->dbg)) {
            $log .= self::get_sql_log_html($datastore, 'cache: datastore [' . $datastore->engine . ']');
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
        return (SQL_DEBUG && DBG_USER && !empty($_COOKIE['sql_log']));
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
            $perc = @sprintf('[%2d]', $dbg['time'] * 100 / $db_obj->sql_timetotal);
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
