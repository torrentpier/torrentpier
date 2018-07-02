<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2018 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $userdata, $bb_cfg, $lang;

if (!isset($this->request['t'])) {
    $this->ajax_die('Invalid AJAX topic');
}
if (!isset($this->request['type'])) {
    $this->ajax_die('Invalid AJAX type');
}

$topic_id = (int)$this->request['t'];
$type = (string)$this->request['type'];
$title = $url = '';

switch ($type) {
    case 'set_gold':
    case 'set_silver':
    case 'unset_silver_gold':
        if ($type == 'set_silver') {
            $tor_type = TOR_TYPE_SILVER;
        } elseif ($type == 'set_gold') {
            $tor_type = TOR_TYPE_GOLD;
        } else {
            $tor_type = 0;
        }
        \TorrentPier\Legacy\Torrent::change_tor_type($topic_id, $tor_type);
        $title = $lang['CHANGE_TOR_TYPE'];
        $url = make_url(TOPIC_URL . $topic_id);
        break;

    case 'reg':
        \TorrentPier\Legacy\Torrent::tracker_register($topic_id);
        $url = (TOPIC_URL . $topic_id);
        break;

    case 'unreg':
        \TorrentPier\Legacy\Torrent::tracker_unregister($topic_id);
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
        $url = make_url("modcp.php?t=$topic_id&mode=move");
        break;
}

$this->response['url'] = $url;
$this->response['title'] = $title;
