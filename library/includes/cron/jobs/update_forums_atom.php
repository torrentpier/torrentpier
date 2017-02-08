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

global $bb_cfg;

require_once INC_DIR . '/functions_atom.php';

$timecheck = TIMENOW - 600;
$forums_data = DB()->fetch_rowset("SELECT forum_id, allow_reg_tracker, forum_name FROM " . BB_FORUMS);

if (file_exists($bb_cfg['atom']['path'] . '/f/0.atom')) {
    if (filemtime($bb_cfg['atom']['path'] . '/f/0.atom') <= $timecheck) {
        update_forum_feed(0, $forums_data);
    }
} else {
    update_forum_feed(0, $forums_data);
}

foreach ($forums_data as $forum_data) {
    if (file_exists($bb_cfg['atom']['path'] . '/f/' . $forum_data['forum_id'] . '.atom')) {
        if (filemtime($bb_cfg['atom']['path'] . '/f/' . $forum_data['forum_id'] . '.atom') <= $timecheck) {
            update_forum_feed($forum_data['forum_id'], $forum_data);
        }
    } else {
        update_forum_feed($forum_data['forum_id'], $forum_data);
    }
}
