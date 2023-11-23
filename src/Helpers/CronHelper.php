<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
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
     *
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return env('APP_CRON_ENABLED', true);
    }

    /**
     * Unlock cron (time-dependent)
     *
     * @return void
     */
    public static function releaseDeadlock(): void
    {
        if (file_exists(CRON_RUNNING)) {
            if (TIMENOW - filemtime(CRON_RUNNING) > 2400) {
                self::enableBoard();
                self::releaseLockFile();
            }
        }
    }

    /**
     * Снятие блокировки крона (по файлу)
     *
     * @return void
     */
    public static function releaseLockFile(): void
    {
        if (file_exists(CRON_RUNNING)) {
            rename(CRON_RUNNING, CRON_ALLOWED);
        }
        self::touchLockFile(CRON_ALLOWED);
    }

    /**
     * Создание файла блокировки
     *
     * @param string $lock_file
     *
     * @return void
     */
    public static function touchLockFile(string $lock_file): void
    {
        file_write('', $lock_file, 0, true, true);
    }

    /**
     * Включение форума (при разблокировке крона)
     *
     * @return void
     */
    public static function enableBoard(): void
    {
        if (file_exists(BB_DISABLED)) {
            rename(BB_DISABLED, BB_ENABLED);
        }
    }

    /**
     * Отключение форума (при блокировке крона)
     *
     * @return void
     */
    public static function disableBoard(): void
    {
        if (file_exists(BB_ENABLED)) {
            rename(BB_ENABLED, BB_DISABLED);
        }
    }

    /**
     * Проверка наличия файла блокировки
     *
     * @return bool
     */
    public static function hasFileLock(): bool
    {
        $lock_obtained = false;

        if (file_exists(CRON_ALLOWED)) {
            $lock_obtained = rename(CRON_ALLOWED, CRON_RUNNING);
        } elseif (file_exists(CRON_RUNNING)) {
            self::releaseDeadlock();
        } elseif (!file_exists(CRON_ALLOWED) && !file_exists(CRON_RUNNING)) {
            file_write('', CRON_ALLOWED);
            $lock_obtained = rename(CRON_ALLOWED, CRON_RUNNING);
        }

        return $lock_obtained;
    }

    /**
     * Отслеживание запуска задач
     *
     * @param string $mode
     */
    public static function trackRunning(string $mode)
    {
        if (!defined('START_MARK')) {
            define('START_MARK', TRIGGERS_DIR . '/cron_started_at_' . date('Y-m-d_H-i-s') . '_by_pid_' . getmypid());
        }

        switch ($mode) {
            case 'start':
                self::touchLockFile(CRON_RUNNING);
                file_write('', START_MARK);
                break;
            case 'end':
                if (file_exists(START_MARK)) {
                    unlink(START_MARK);
                }
                break;
            default:
                bb_simple_die('Invalid mode');
        }
    }
}
