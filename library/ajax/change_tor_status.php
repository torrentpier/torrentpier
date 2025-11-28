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

if (!$topic_id = (int)$this->request['topic_id']) {
    $this->ajax_die($lang['EMPTY_TOPIC_ID']);
}

if (!$mode = (string)$this->request['mode']) {
    $this->ajax_die('invalid mode (empty)');
}

$comment = false;
if (config()->get('tor_comment')) {
    $comment = (string)$this->request['comment'];
}

$tor = DB()->fetch_row("
	SELECT
		tor.poster_id, tor.forum_id, tor.topic_id, tor.tor_status, tor.checked_time, tor.checked_user_id, f.cat_id, t.topic_title
	FROM       " . BB_BT_TORRENTS . " tor
	INNER JOIN " . BB_FORUMS . " f ON(f.forum_id = tor.forum_id)
	INNER JOIN " . BB_TOPICS . " t ON(t.topic_id = tor.topic_id)
	WHERE tor.topic_id = $topic_id
	LIMIT 1
");

if (!$tor) {
    $this->ajax_die($lang['TORRENT_FAILED']);
}

switch ($mode) {
    case 'status':
        $new_status = (int)$this->request['status'];

        // Check status validity
        if (!isset($lang['TOR_STATUS_NAME'][$new_status])) {
            $this->ajax_die($lang['TOR_STATUS_FAILED']);
        }
        if (!isset($this->request['status'])) {
            $this->ajax_die($lang['TOR_DONT_CHANGE']);
        }
        if (!IS_AM) {
            $this->ajax_die($lang['NOT_MODERATOR']);
        }

        // Error if same status
        if ($tor['tor_status'] == $new_status) {
            $this->ajax_die($lang['TOR_STATUS_DUB']);
        }

        // Prohibition on changing/assigning CH-status by moderator
        if ($new_status == TOR_CLOSED_CPHOLD && !IS_ADMIN) {
            $this->ajax_die($lang['TOR_DONT_CHANGE']);
        }

        // Check rights to change status
        if ($tor['tor_status'] == TOR_CLOSED_CPHOLD) {
            if (!IS_ADMIN) {
                $this->verify_mod_rights($tor['forum_id']);
            }
            DB()->query("UPDATE " . BB_TOPICS . " SET topic_status = " . TOPIC_UNLOCKED . " WHERE topic_id = {$tor['topic_id']} LIMIT 1");
        } else {
            $this->verify_mod_rights($tor['forum_id']);
        }

        // Confirmation of status change set by another moderator
        if ($tor['tor_status'] != TOR_NOT_APPROVED && $tor['checked_user_id'] != $userdata['user_id'] && $tor['checked_time'] + 2 * 3600 > TIMENOW) {
            if (empty($this->request['confirmed'])) {
                $msg = $lang['TOR_STATUS_OF'] . " {$lang['TOR_STATUS_NAME'][$tor['tor_status']]}\n\n";
                $msg .= ($username = get_username($tor['checked_user_id'])) ? $lang['TOR_STATUS_CHANGED'] . html_entity_decode($username) . ", " . humanTime($tor['checked_time']) . $lang['TOR_BACK'] . "\n\n" : "";
                $msg .= $lang['PROCEED'] . '?';
                $this->prompt_for_confirm($msg);
            }
        }

        \TorrentPier\Torrent\Moderation::changeStatus($topic_id, $new_status);

        // Log action
        $log_msg = sprintf($lang['TOR_STATUS_LOG_ACTION'], config()->get('tor_icons')[$new_status] . ' <b> ' . $lang['TOR_STATUS_NAME'][$new_status] . '</b>', config()->get('tor_icons')[$tor['tor_status']] . ' <b> ' . $lang['TOR_STATUS_NAME'][$tor['tor_status']] . '</b>');
        if ($comment && $comment != $lang['COMMENT']) {
            $log_msg .= "<br/>{$lang['COMMENT']}: <b>$comment</b>.";
        }
        $log_action->mod('mod_topic_change_tor_status', [
            'forum_id' => $tor['forum_id'],
            'topic_id' => $tor['topic_id'],
            'topic_title' => $tor['topic_title'],
            'log_msg' => $log_msg . '<br/>-------------',
        ]);

        $this->response['status'] = config()->get('tor_icons')[$new_status] . ' <b> ' . $lang['TOR_STATUS_NAME'][$new_status] . '</b> &middot; ' . profile_url($userdata) . ' &middot; <i>' . humanTime(TIMENOW) . $lang['TOR_BACK'] . '</i>';

        if (config()->get('tor_comment') && (($comment && $comment != $lang['COMMENT']) || in_array($new_status, config()->get('tor_reply')))) {
            if ($tor['poster_id'] > 0) {
                $subject = sprintf($lang['TOR_MOD_TITLE'], $tor['topic_title']);
                $message = sprintf($lang['TOR_MOD_MSG'], get_username($tor['poster_id']), make_url(TOPIC_URL . $tor['topic_id']), config()->get('tor_icons')[$new_status] . ' ' . $lang['TOR_STATUS_NAME'][$new_status]);

                if ($comment && $comment != $lang['COMMENT']) {
                    $message .= "\n\n[b]" . $lang['COMMENT'] . '[/b]: ' . $comment;
                }

                send_pm($tor['poster_id'], $subject, $message, $userdata['user_id']);
                \TorrentPier\Sessions::cache_rm_user_sessions($tor['poster_id']);
            }
        }
        break;

    case 'status_reply':
        if (!config()->get('tor_comment')) {
            $this->ajax_die($lang['MODULE_OFF']);
        }

        $subject = sprintf($lang['TOR_AUTH_TITLE'], $tor['topic_title']);
        $message = sprintf($lang['TOR_AUTH_MSG'], get_username($tor['checked_user_id']), make_url(TOPIC_URL . $tor['topic_id']), $tor['topic_title']);

        if ($comment && $comment != $lang['COMMENT']) {
            $message .= "\n\n[b]" . $lang['COMMENT'] . '[/b]: ' . $comment;
        }

        send_pm($tor['checked_user_id'], $subject, $message, $userdata['user_id']);
        \TorrentPier\Sessions::cache_rm_user_sessions($tor['checked_user_id']);
        break;

    default:
        $this->ajax_die('Invalid mode: ' . $mode);
}

$this->response['topic_id'] = $topic_id;
