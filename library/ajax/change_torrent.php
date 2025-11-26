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

global $userdata, $lang, $log_action;

if (!isset($this->request['topic_id'])) {
    $this->ajax_die($lang['EMPTY_TOPIC_ID']);
}
if (!isset($this->request['type'])) {
    $this->ajax_die('empty type');
}

$topic_id = (int)$this->request['topic_id'];
$type = (string)$this->request['type'];

if (!$torrent = \TorrentPier\Legacy\Torrent::get_torrent_info($topic_id)) {
    $this->ajax_die($lang['INVALID_TOPIC_ID']);
}

if ($torrent['topic_poster'] == $userdata['user_id'] && !IS_AM) {
    switch ($type) {
        case 'del_torrent':
        case 'reg':
        case 'unreg':
            break;
        default:
            $this->ajax_die($lang['ONLY_FOR_MOD']);
            break;
    }
} elseif (!IS_AM) {
    $this->ajax_die($lang['ONLY_FOR_MOD']);
}

$title = $url = '';
switch ($type) {
    case 'set_gold':
    case 'set_silver':
    case 'unset_silver_gold':
        if ($type == 'set_silver') {
            $tor_type = TOR_TYPE_SILVER;
            $tor_type_lang = $lang['SILVER'];
        } elseif ($type == 'set_gold') {
            $tor_type = TOR_TYPE_GOLD;
            $tor_type_lang = $lang['GOLD'];
        } else {
            $tor_type = TOR_TYPE_DEFAULT;
            $tor_type_lang = "{$lang['UNSET_GOLD_TORRENT']} / {$lang['UNSET_SILVER_TORRENT']}";
        }

        \TorrentPier\Legacy\Torrent::change_tor_type($topic_id, $tor_type);

        // Log action
        $log_action->mod('mod_topic_change_tor_type', [
            'forum_id' => $torrent['forum_id'],
            'topic_id' => $topic_id,
            'topic_title' => $torrent['topic_title'],
            'log_msg' => sprintf($lang['TOR_TYPE_LOG_ACTION'], $tor_type_lang),
        ]);

        $title = $lang['CHANGE_TOR_TYPE'];
        $url = make_url(TOPIC_URL . $topic_id);
        break;

    case 'reg':
        \TorrentPier\Legacy\Torrent::tracker_register($topic_id);
        // Log action
        $log_action->mod('mod_topic_tor_register', [
            'forum_id' => $torrent['forum_id'],
            'topic_id' => $topic_id,
            'topic_title' => $torrent['topic_title'],
        ]);
        $url = (TOPIC_URL . $topic_id);
        break;

    case 'unreg':
        \TorrentPier\Legacy\Torrent::tracker_unregister($topic_id);
        // Log action
        $log_action->mod('mod_topic_tor_unregister', [
            'forum_id' => $torrent['forum_id'],
            'topic_id' => $topic_id,
            'topic_title' => $torrent['topic_title'],
        ]);
        $url = (TOPIC_URL . $topic_id);
        break;

    case 'del_torrent':
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm($lang['DEL_TORRENT']);
        }
        \TorrentPier\Legacy\Torrent::delete_torrent($topic_id);
        $url = make_url(TOPIC_URL . $topic_id);
        break;

    case 'del_torrent_move_topic':
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm($lang['DEL_MOVE_TORRENT']);
        }
        \TorrentPier\Legacy\Torrent::delete_torrent($topic_id);
        $url = make_url("modcp.php?" . POST_TOPIC_URL . "={$topic_id}&mode=move&sid={$userdata['session_id']}");
        break;
}

$this->response['url'] = $url;
$this->response['title'] = $title;
