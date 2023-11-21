<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $bb_cfg, $lang, $userdata;

if (!$bb_cfg['tor_thank']) {
    $this->ajax_die($lang['MODULE_OFF']);
}

$mode = (string)$this->request['mode'];
$topic_id = (int)$this->request['topic_id'];

switch ($mode) {
    case 'add':
        if (DB()->fetch_row('SELECT poster_id FROM ' . BB_BT_TORRENTS . " WHERE topic_id = $topic_id AND poster_id = " . $userdata['user_id'])) {
            $this->ajax_die($lang['LIKE_OWN_POST']);
        }

        if (DB()->fetch_row('SELECT topic_id FROM ' . BB_THX . " WHERE topic_id = $topic_id  AND user_id = " . $userdata['user_id'])) {
            $this->ajax_die($lang['LIKE_ALREADY']);
        }

        $columns = 'topic_id, user_id, time';
        $values = "$topic_id, {$userdata['user_id']}, " . TIMENOW;
        DB()->query('REPLACE INTO ' . BB_THX . " ($columns) VALUES ($values)");

        $this->response['html'] = '<b>' . profile_url($userdata) . ' <i>(' . bb_date(TIMENOW) . ')</i></b>';
        break;

    case 'get':
        $sql = DB()->fetch_rowset('SELECT u.username, u.user_rank, u.user_id, t.* FROM ' . BB_THX . ' t, ' . BB_USERS . " u WHERE t.topic_id = $topic_id AND t.user_id = u.user_id");

        $user_list = [];
        foreach ($sql as $row) {
            $user_list[] = '<b>' . profile_url($row) . ' <i>(' . bb_date($row['time']) . ')</i></b>';
        }

        $this->response['html'] = join(', ', $user_list) ?: $lang['NO_LIKES'];
        break;

    default:
        $this->ajax_die('Invalid mode');
}

$this->response['mode'] = $mode;
