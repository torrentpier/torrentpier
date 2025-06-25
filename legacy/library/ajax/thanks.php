<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine.
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 *
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 *
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */
if (!defined('IN_AJAX')) {
    exit(basename(__FILE__));
}

global $lang, $userdata;

if (!config()->get('tor_thank')) {
    $this->ajax_die($lang['MODULE_OFF']);
}

if (!$mode = (string) $this->request['mode']) {
    $this->ajax_die('invalid mode (empty)');
}

if (!$topic_id = (int) $this->request['topic_id']) {
    $this->ajax_die($lang['INVALID_TOPIC_ID']);
}

if (!$poster_id = (int) $this->request['poster_id']) {
    $this->ajax_die($lang['NO_USER_ID_SPECIFIED']);
}

switch ($mode) {
    case 'add':
        if (IS_GUEST) {
            $this->ajax_die($lang['NEED_TO_LOGIN_FIRST']);
        }

        if ($poster_id == $userdata['user_id']) {
            $this->ajax_die($lang['LIKE_OWN_POST']);
        }

        if (DB()->fetch_row('SELECT topic_id FROM '.BB_THX." WHERE topic_id = $topic_id  AND user_id = ".$userdata['user_id'])) {
            $this->ajax_die($lang['LIKE_ALREADY']);
        }

        $columns = 'topic_id, user_id, time';
        $values = "$topic_id, {$userdata['user_id']}, ".TIMENOW;
        DB()->query('INSERT IGNORE INTO '.BB_THX." ($columns) VALUES ($values)");

        // Limit voters per topic
        $thanks_count = DB()->fetch_row('SELECT COUNT(*) as thx FROM '.BB_THX." WHERE topic_id = $topic_id")['thx'];
        if ($thanks_count > (int) config()->get('tor_thank_limit_per_topic')) {
            DB()->query('DELETE FROM '.BB_THX." WHERE topic_id = $topic_id ORDER BY time ASC LIMIT 1");
        }
        break;
    case 'get':
        if (IS_GUEST && !config()->get('tor_thanks_list_guests')) {
            $this->ajax_die($lang['NEED_TO_LOGIN_FIRST']);
        }

        $user_list = [];
        $sql = DB()->fetch_rowset('SELECT u.username, u.user_rank, u.user_id, t.* FROM '.BB_THX.' t, '.BB_USERS." u WHERE t.topic_id = $topic_id AND t.user_id = u.user_id");
        foreach ($sql as $row) {
            $user_list[] = '<b>'.profile_url($row).' <i>('.bb_date($row['time']).')</i></b>';
        }

        $this->response['html'] = implode(', ', $user_list) ?: $lang['NO_LIKES'];
        break;
    default:
        $this->ajax_die('Invalid mode: '.$mode);
}

$this->response['mode'] = $mode;
