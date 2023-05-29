<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'vote');
require __DIR__ . '/common.php';

$user->session_start(array('req_login' => true));

$mode = (string)@$_POST['mode'];
$topic_id = (int)@$_POST['topic_id'];
$forum_id = (int)@$_POST['forum_id'];
$vote_id = (int)@$_POST['vote_id'];

$return_topic_url = TOPIC_URL . $topic_id;
$return_topic_url .= !empty($_POST['start']) ? "&amp;start=" . (int)$_POST['start'] : '';

set_die_append_msg($forum_id, $topic_id);

$poll = new TorrentPier\Legacy\Poll();

// проверка валидности $topic_id
if (!$topic_id) {
    bb_die($lang['INVALID_TOPIC_ID']);
}
if (!$t_data = DB()->fetch_row("SELECT * FROM " . BB_TOPICS . " WHERE topic_id = $topic_id LIMIT 1")) {
    bb_die($lang['INVALID_TOPIC_ID_DB']);
}

// проверка прав
if ($mode != 'poll_vote') {
    if ($t_data['topic_poster'] != $userdata['user_id']) {
        if (!IS_AM) {
            bb_die($lang['NOT_AUTHORISED']);
        }
    }
}

// проверка на возможность вносить изменения
if ($mode == 'poll_delete') {
    if ($t_data['topic_time'] < TIMENOW - $bb_cfg['poll_max_days'] * 86400) {
        bb_die(sprintf($lang['NEW_POLL_DAYS'], $bb_cfg['poll_max_days']));
    }
    if (!IS_ADMIN && ($t_data['topic_vote'] != POLL_FINISHED)) {
        bb_die($lang['CANNOT_DELETE_POLL']);
    }
}

switch ($mode) {
    // голосование
    case 'poll_vote':
        if (!$t_data['topic_vote']) {
            bb_die($lang['POST_HAS_NO_POLL']);
        }
        if ($t_data['topic_status'] == TOPIC_LOCKED) {
            bb_die($lang['TOPIC_LOCKED_SHORT']);
        }
        if (!\TorrentPier\Legacy\Poll::poll_is_active($t_data)) {
            bb_die($lang['NEW_POLL_ENDED']);
        }
        if (!$vote_id) {
            bb_die($lang['NO_VOTE_OPTION']);
        }
        if (DB()->fetch_row("SELECT 1 FROM " . BB_POLL_USERS . " WHERE topic_id = $topic_id AND user_id = {$userdata['user_id']} LIMIT 1")) {
            bb_die($lang['ALREADY_VOTED']);
        }

        DB()->query("
			UPDATE " . BB_POLL_VOTES . " SET
				vote_result = vote_result + 1
			WHERE topic_id = $topic_id
				AND vote_id = $vote_id
			LIMIT 1
		");
        if (DB()->affected_rows() != 1) {
            bb_die($lang['NO_VOTE_OPTION']);
        }

        DB()->query("INSERT IGNORE INTO " . BB_POLL_USERS . " (topic_id, user_id, vote_ip, vote_dt) VALUES ($topic_id, {$userdata['user_id']}, '" . USER_IP . "', " . TIMENOW . ")");

        CACHE('bb_poll_data')->rm("poll_$topic_id");

        bb_die($lang['VOTE_CAST']);
        break;

    // возобновить возможность голосовать
    case 'poll_start':
        if (!$t_data['topic_vote']) {
            bb_die($lang['POST_HAS_NO_POLL']);
        }
        DB()->query("UPDATE " . BB_TOPICS . " SET topic_vote = 1 WHERE topic_id = $topic_id");
        bb_die($lang['NEW_POLL_START']);
        break;

    // завершить опрос
    case 'poll_finish':
        if (!$t_data['topic_vote']) {
            bb_die($lang['POST_HAS_NO_POLL']);
        }
        DB()->query("UPDATE " . BB_TOPICS . " SET topic_vote = " . POLL_FINISHED . " WHERE topic_id = $topic_id");
        bb_die($lang['NEW_POLL_END']);
        break;

    // удаление
    case 'poll_delete':
        if (!$t_data['topic_vote']) {
            bb_die($lang['POST_HAS_NO_POLL']);
        }
        $poll->delete_poll($topic_id);
        bb_die($lang['NEW_POLL_DELETE']);
        break;

    // добавление
    case 'poll_add':
        if ($t_data['topic_vote']) {
            bb_die($lang['NEW_POLL_ALREADY']);
        }
        $poll->build_poll_data($_POST);
        if ($poll->err_msg) {
            bb_die($poll->err_msg);
        }
        $poll->insert_votes_into_db($topic_id);
        bb_die($lang['NEW_POLL_ADDED']);
        break;

    // редактирование
    case 'poll_edit':
        if (!$t_data['topic_vote']) {
            bb_die($lang['POST_HAS_NO_POLL']);
        }
        $poll->build_poll_data($_POST);
        if ($poll->err_msg) {
            bb_die($poll->err_msg);
        }
        $poll->insert_votes_into_db($topic_id);
        CACHE('bb_poll_data')->rm("poll_$topic_id");
        bb_die($lang['NEW_POLL_RESULTS']);
        break;

    default:
        bb_die('Invalid mode: ' . htmlCHR($mode));
}
