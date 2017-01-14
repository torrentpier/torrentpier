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

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

require_once(INC_DIR . 'functions_admin.php');

// Синхронизация
sync('topic', 'all');
sync('user_posts', 'all');
sync_all_forums();

// Чистка bb_poll_users
if ($poll_max_days = (int)$di->config->get('poll_max_days')) {
    $per_cycle = 20000;
    $row = DB()->fetch_row("SELECT MIN(topic_id) AS start_id, MAX(topic_id) AS finish_id FROM " . BB_POLL_USERS);
    $start_id = (int)$row['start_id'];
    $finish_id = (int)$row['finish_id'];

    while (true) {
        set_time_limit(600);
        $end_id = $start_id + $per_cycle - 1;

        DB()->query("
			DELETE FROM " . BB_POLL_USERS . "
			WHERE topic_id BETWEEN $start_id AND $end_id
				AND vote_dt < DATE_SUB(NOW(), INTERVAL $poll_max_days DAY)
		");
        if ($end_id > $finish_id) {
            break;
        }
        if (!($start_id % ($per_cycle * 10))) {
            sleep(1);
        }
        $start_id += $per_cycle;
    }
}

// Чистка user_newpasswd
DB()->query("UPDATE " . BB_USERS . " SET user_newpasswd = '' WHERE user_lastvisit < " . (TIMENOW - 7 * 86400));

// Чистка кеша постов
if ($posts_days = intval($di->config->get('posts_cache_days_keep'))) {
    DB()->query("DELETE FROM " . BB_POSTS_HTML . " WHERE post_html_time < DATE_SUB(NOW(), INTERVAL $posts_days DAY)");
}
