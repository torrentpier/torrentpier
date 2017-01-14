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

$users_per_cycle = 1000;

while (true) {
    set_time_limit(600);

    $prune_users = $not_activated_users = $not_active_users = array();

    if ($not_activated_days = intval($di->config->get('user_not_activated_days_keep'))) {
        $sql = DB()->fetch_rowset("SELECT user_id FROM " . BB_USERS . "
			WHERE user_level      = 0
			AND user_lastvisit    = 0
			AND user_session_time = 0
			AND user_regdate      <= " . (TIMENOW - 86400 * $not_activated_days) . "
			AND user_id           NOT IN(" . EXCLUDED_USERS . ")
			LIMIT $users_per_cycle");

        foreach ($sql as $row) {
            $not_activated_users[] = $row['user_id'];
        }
    }

    if ($not_active_days = intval($di->config->get('user_not_active_days_keep'))) {
        $sql = DB()->fetch_rowset("SELECT user_id FROM " . BB_USERS . "
			WHERE user_level   = 0
			AND user_posts     = 0
			AND user_lastvisit <= " . (TIMENOW - 86400 * $not_active_days) . "
			AND user_id        NOT IN(" . EXCLUDED_USERS . ")
			LIMIT $users_per_cycle");

        foreach ($sql as $row) {
            $not_active_users[] = $row['user_id'];
        }
    }

    if ($prune_users = $not_activated_users + $not_active_users) {
        user_delete($prune_users);
    }

    if (count($prune_users) < $users_per_cycle) {
        break;
    }

    sleep(3);
}
