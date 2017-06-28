<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2017 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
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
    if (config('tp.board_disable')) {
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
