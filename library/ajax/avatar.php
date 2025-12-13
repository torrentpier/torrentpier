<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

if (!$mode = (string)$this->request['mode']) {
    $this->ajax_die('invalid mode (empty)');
}

$user_id = (int)$this->request['user_id'];
if (!$user_id || !$u_data = get_userdata($user_id)) {
    $this->ajax_die(__('NO_USER_ID_SPECIFIED'));
}

if (!IS_ADMIN && $user_id != user()->id) {
    $this->ajax_die(__('NOT_AUTHORISED'));
}

$new_ext_id = 0;
$response = '';

switch ($mode) {
    case 'delete':
        delete_avatar($user_id, $u_data['avatar_ext_id']);
        $response = get_avatar($user_id, $new_ext_id);
        break;
    default:
        $this->ajax_die('Invalid mode: ' . $mode);
}

DB()->query('UPDATE ' . BB_USERS . " SET avatar_ext_id = {$new_ext_id} WHERE user_id = {$user_id} LIMIT 1");

TorrentPier\Sessions::cache_rm_user_sessions($user_id);

$this->response['avatar_html'] = $response;
