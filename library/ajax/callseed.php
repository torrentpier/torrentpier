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

if (!config()->get('callseed')) {
    $this->ajax_die(__('MODULE_OFF'));
}

if (!$topic_id = (int)$this->request['topic_id']) {
    $this->ajax_die(__('INVALID_TOPIC_ID'));
}

if (!$t_data = topic_info($topic_id)) {
    $this->ajax_die(__('INVALID_TOPIC_ID_DB'));
}

$forum_id = $t_data['forum_id'];

if ($t_data['seeders'] >= 3) {
    $this->ajax_die(sprintf(__('CALLSEED_HAVE_SEED'), $t_data['seeders']));
} elseif ($t_data['call_seed_time'] >= (TIMENOW - 86400)) {
    $time_left = humanTime($t_data['call_seed_time'] + 86400, TIMENOW);
    $this->ajax_die(sprintf(__('CALLSEED_MSG_SPAM'), $time_left));
} elseif (isset(config()->get('tor_no_tor_act')[$t_data['tor_status']])) {
    $this->ajax_die(__('NOT_AVAILABLE'));
}

$banned_users = ($get_banned_users = get_banned_users()) ? (', ' . implode(', ', $get_banned_users)) : '';

$user_list = DB()->fetch_rowset('
	SELECT DISTINCT dl.user_id, u.user_opt, tr.user_id as active_dl
	FROM ' . BB_BT_DLSTATUS . ' dl
	LEFT JOIN ' . BB_USERS . ' u  ON(u.user_id = dl.user_id)
	LEFT JOIN ' . BB_BT_TRACKER . " tr ON(tr.user_id = dl.user_id)
	WHERE dl.topic_id = {$topic_id}
		AND dl.user_status IN (" . DL_STATUS_COMPLETE . ', ' . DL_STATUS_DOWN . ')
		AND dl.user_id NOT IN (' . userdata('user_id') . ', ' . EXCLUDED_USERS . $banned_users . ')
		AND u.user_active = 1
	GROUP BY dl.user_id
');

$subject = sprintf(__('CALLSEED_SUBJECT'), $t_data['topic_title']);
$message = sprintf(__('CALLSEED_TEXT'), make_url(TOPIC_URL . $topic_id . '/'), $t_data['topic_title'], make_url(DL_URL . $topic_id . '/'));

if ($user_list) {
    foreach ($user_list as $row) {
        if (!empty($row['active_dl'])) {
            continue;
        }

        if (bf($row['user_opt'], 'user_opt', 'user_callseed')) {
            send_pm($row['user_id'], $subject, $message, BOT_UID);
        }
    }
} else {
    send_pm($t_data['poster_id'], $subject, $message, BOT_UID);
}

DB()->query('UPDATE ' . BB_BT_TORRENTS . ' SET call_seed_time = ' . TIMENOW . " WHERE topic_id = {$topic_id} LIMIT 1");

function topic_info($topic_id)
{
    $sql = '
		SELECT
			tor.poster_id, tor.forum_id, tor.call_seed_time, tor.tor_status,
			t.topic_title, sn.seeders
		FROM      ' . BB_BT_TORRENTS . ' tor
		LEFT JOIN ' . BB_TOPICS . ' t  USING(topic_id)
		LEFT JOIN ' . BB_BT_TRACKER_SNAP . " sn USING(topic_id)
		WHERE tor.topic_id = {$topic_id}
	";

    if (!$torrent = DB()->fetch_row($sql)) {
        bb_die(__('TOPIC_POST_NOT_EXIST'), 404);
    }

    return $torrent;
}

$this->response['response'] = __('CALLSEED_MSG_OK');
