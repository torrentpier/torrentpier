<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Helpers;

/**
 * Class CronHelper
 * @package TorrentPier\Helpers
 */
class CronHelper
{
    /**
     * Checking whether cron scripts execution is enabled
     */
    public static function isEnabled(): bool
    {
        return env('APP_CRON_ENABLED', true);
    }

    /**
     * Unlock cron (time-dependent)
     */
    public static function releaseDeadlock(): void
    {
        if (is_file(CRON_RUNNING)) {
            if (TIMENOW - filemtime(CRON_RUNNING) > 2400) {
                self::enableBoard();
                self::releaseLockFile();
            }
        }
    }

    /**
     * Снятие блокировки крона (по файлу)
     */
    public static function releaseLockFile(): void
    {
        if (is_file(CRON_RUNNING)) {
            rename(CRON_RUNNING, CRON_ALLOWED);
        }
        self::touchLockFile(CRON_ALLOWED);
    }

    /**
     * Создание файла блокировки
     */
    public static function touchLockFile(string $lock_file): void
    {
        file_write('', $lock_file, replace_content: true);
    }

    /**
     * Включение форума (при разблокировке крона)
     */
    public static function enableBoard(): void
    {
        if (is_file(BB_DISABLED)) {
            rename(BB_DISABLED, BB_ENABLED);
        }
    }

    /**
     * Отключение форума (при блокировке крона)
     */
    public static function disableBoard(): void
    {
        if (is_file(BB_ENABLED)) {
            rename(BB_ENABLED, BB_DISABLED);
        }
    }

    /**
     * Проверка наличия файла блокировки
     */
    public static function hasFileLock(): bool
    {
        $lock_obtained = false;

        if (is_file(CRON_ALLOWED)) {
            $lock_obtained = rename(CRON_ALLOWED, CRON_RUNNING);
        } elseif (is_file(CRON_RUNNING)) {
            self::releaseDeadlock();
        } elseif (!is_file(CRON_ALLOWED) && !is_file(CRON_RUNNING)) {
            file_write('', CRON_ALLOWED);
            $lock_obtained = rename(CRON_ALLOWED, CRON_RUNNING);
        }

        return $lock_obtained;
    }

    /**
     * Отслеживание запуска задач
     */
    public static function trackRunning(string $mode): void
    {
        if (!\defined('START_MARK')) {
            \define('START_MARK', TRIGGERS_DIR . '/cron_started_at_' . date('Y-m-d_H-i-s') . '_by_pid_' . getmypid());
        }

        switch ($mode) {
            case 'start':
                self::touchLockFile(CRON_RUNNING);
                file_write('', START_MARK);
                break;
            case 'end':
                if (is_file(START_MARK)) {
                    unlink(START_MARK);
                }
                break;
            default:
                bb_simple_die("Invalid cron track mode: {$mode}");
        }
    }

    /**
     * Run cron jobs
     *
     * @param bool $force Force run even if an interval not passed
     * @return bool Whether cron was executed
     */
    public static function run(bool $force = false): bool
    {
        // Check conditions
        if (!empty($_POST)) {
            return false;
        }

        if (is_file(CRON_RUNNING)) {
            return false;
        }

        if (!self::isEnabled() && !$force) {
            return false;
        }

        // Check interval
        if (!$force && (TIMENOW - config()->get('cron_last_check') <= config()->get('cron_check_interval'))) {
            return false;
        }

        // Update cron_last_check
        bb_update_config(['cron_last_check' => TIMENOW + 10]);
        bb_log(date('H:i:s - ') . getmypid() . ' -x-- DB-LOCK try' . LOG_LF, CRON_LOG_DIR . '/cron_check');

        if (!DB()->get_lock('cron', 1)) {
            return false;
        }

        bb_log(date('H:i:s - ') . getmypid() . ' --x- DB-LOCK OBTAINED !!!!!!!!!!!!!!!!!' . LOG_LF, CRON_LOG_DIR . '/cron_check');

        // Run cron
        if (self::hasFileLock()) {
            // Release file lock on shutdown
            register_shutdown_function(function () {
                self::releaseLockFile();
            });

            // Enable board on shutdown
            register_shutdown_function(function () {
                self::enableBoard();
            });

            self::trackRunning('start');

            require CRON_DIR . 'cron_check.php';

            self::trackRunning('end');
        }

        if (\defined('IN_CRON')) {
            bb_log(date('H:i:s - ') . getmypid() . ' --x- ALL jobs FINISHED *************************************************' . LOG_LF, CRON_LOG_DIR . '/cron_check');
        }

        DB()->release_lock('cron');

        return true;
    }
}
