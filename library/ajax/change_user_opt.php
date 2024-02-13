<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $bf, $lang;

$user_id = (int)$this->request['user_id'];
$new_opt = json_decode($this->request['user_opt'], true, 512, JSON_THROW_ON_ERROR);

if (!$user_id or !$u_data = get_userdata($user_id)) {
    $this->ajax_die($lang['NO_USER_ID_SPECIFIED']);
}

if (!is_array($new_opt)) {
    $this->ajax_die('invalid new_opt');
}

foreach ($bf['user_opt'] as $opt_name => $opt_bit) {
    if (isset($new_opt[$opt_name])) {
        setbit($u_data['user_opt'], $opt_bit, !empty($new_opt[$opt_name]));
    }
}

DB()->query("UPDATE " . BB_USERS . " SET user_opt = {$u_data['user_opt']} WHERE user_id = $user_id LIMIT 1");

// Удаляем данные из кеша
\TorrentPier\Sessions::cache_rm_user_sessions($user_id);

$this->response['resp_html'] = $lang['SAVED'];
