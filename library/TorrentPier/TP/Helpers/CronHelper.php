<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2017 TorrentPier (https://torrentpier.me)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TP\Helpers;

/**
 * Class CronHelper
 * @package TorrentPier\Helpers
 */
class CronHelper
{
    /**
     * Снятие блокировки крона (по времени)
     *
     * @return void
     */
    public static function releaseDeadlock()
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
    public static function releaseLockFile()
    {
        if (file_exists(CRON_RUNNING)) {
            rename(CRON_RUNNING, CRON_ALLOWED);
        }
        self::touchLockFile(CRON_ALLOWED);
    }

    /**
     * Создание файла блокировки
     *
     * @param $lock_file
     *
     * @return void
     */
    public static function touchLockFile($lock_file)
    {
        file_write(make_rand_str(20), $lock_file, 0, true, true);
    }

    /**
     * Включение форума (при разблокировке крона)
     *
     * @return void
     */
    public static function enableBoard()
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
    public static function disableBoard()
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
    public static function hasFileLock()
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
     * @param $mode
     */
    public static function trackRunning($mode)
    {
        if (!defined('START_MARK')) {
            define('START_MARK', TRIGGERS_DIR . '/cron_started_at_' . date('Y-m-d_H-i-s') . '_by_pid_' . getmypid());
        }

        if ($mode === 'start') {
            self::touchLockFile(CRON_RUNNING);
            file_write('', START_MARK);
        } elseif ($mode === 'end') {
            if (file_exists(START_MARK)) {
                unlink(START_MARK);
            }
        }
    }
}
