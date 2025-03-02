<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'ajax');
define('IN_AJAX', true);

require __DIR__ . '/common.php';

// Init Ajax class
$ajax = new TorrentPier\Ajax();
$ajax->init();

// Init userdata
$user->session_start();

// Load actions required modules
switch ($ajax->action) {
    case 'view_post':
    case 'posts':
    case 'post_mod_comment':
        require INC_DIR . '/bbcode.php';
        break;

    case 'view_torrent':
    case 'mod_action':
    case 'change_tor_status':
    case 'change_torrent':
    case 'passkey':
        require ATTACH_DIR . '/attachment_mod.php';
        break;
}

$ajax->exec();

/**
 * @deprecated ajax_common
 * Dirty class removed from here since 2.2.0
 * To add new actions see at src/Ajax.php
 */
