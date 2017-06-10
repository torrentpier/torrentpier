<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace TorrentPier\Helpers;

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
        rename(CRON_RUNNING, CRON_ALLOWED);
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
            unlink(START_MARK);
        }
    }
}
