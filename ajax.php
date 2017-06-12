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

define('BB_SCRIPT', 'ajax');
define('IN_AJAX', true);

require __DIR__ . '/common.php';

$ajax = new TorrentPier\Legacy\Ajax();

$ajax->init();

// Init userdata
$user->session_start();

// Exit if board is disabled via ON/OFF trigger or by admin
if ($ajax->action != 'manage_admin') {
    if ($bb_cfg['board_disable']) {
        $ajax->ajax_die($lang['BOARD_DISABLE']);
    } elseif (file_exists(BB_DISABLED)) {
        $ajax->ajax_die($lang['BOARD_DISABLE_CRON']);
    }
}

// Load actions required modules
switch ($ajax->action) {
    case 'view_post':
        require INC_DIR . '/bbcode.php';
        break;

    case 'posts':
    case 'post_mod_comment':
        require INC_DIR . '/bbcode.php';
        require INC_DIR . '/functions_post.php';
        require INC_DIR . '/functions_admin.php';
        break;

    case 'view_torrent':
    case 'mod_action':
    case 'change_tor_status':
    case 'change_torrent':
    case 'gen_passkey':
        require ATTACH_DIR . '/attachment_mod.php';
        require INC_DIR . '/functions_torrent.php';
        break;

    case 'user_register':
        require INC_DIR . '/functions_validate.php';
        break;

    case 'manage_user':
    case 'manage_admin':
        require INC_DIR . '/functions_admin.php';
        break;

    case 'group_membership':
    case 'manage_group':
        require INC_DIR . '/functions_group.php';
        break;
}

// Position in $ajax->valid_actions['xxx']
define('AJAX_AUTH', 0); // 'guest', 'user', 'mod', 'admin', 'super_admin'

$ajax->exec();

/**
 * @deprecated ajax_common
 * Dirty class removed from here since 2.2.0
 * To add new actions see at src/Legacy/Ajax.php
 */
