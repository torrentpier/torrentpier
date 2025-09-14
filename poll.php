<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'vote');

require __DIR__ . '/common.php';

// Start session management
$user->session_start(['req_login' => true]);

$mode = (string)$_POST['mode'];
$topic_id = (int)$_POST['topic_id'];
$forum_id = (int)$_POST['forum_id'];
$vote_id = (int)$_POST['vote_id'];

$return_topic_url = TOPIC_URL . $topic_id;
$return_topic_url .= !empty($_POST['start']) ? "&amp;start=" . (int)$_POST['start'] : '';

set_die_append_msg($forum_id, $topic_id);

$poll = new TorrentPier\Legacy\Poll();

// Checking $topic_id
if (!$topic_id) {
    bb_die(__('INVALID_TOPIC_ID'));
}

// Getting topic data if present
if (!$t_data = DB()->table(BB_TOPICS)->where('topic_id', $topic_id)->fetch()?->toArray()) {
    bb_die(__('INVALID_TOPIC_ID_DB'));
}

// Checking the rights
if ($mode != 'poll_vote') {
    if ($t_data['topic_poster'] != $userdata['user_id']) {
        if (!IS_AM) {
            bb_die(__('NOT_AUTHORISED'));
        }
    }
}

// Checking the ability to make changes
if ($mode == 'poll_delete') {
    if ($t_data['topic_time'] < TIMENOW - config()->get('poll_max_days') * 86400) {
        bb_die(sprintf(__('NEW_POLL_DAYS'), config()->get('poll_max_days')));
    }
    if (!IS_ADMIN && ($t_data['topic_vote'] != POLL_FINISHED)) {
        bb_die(__('CANNOT_DELETE_POLL'));
    }
}

switch ($mode) {
    case 'poll_vote':
        // Checking for poll existence
        if (!$t_data['topic_vote']) {
            bb_die(__('POST_HAS_NO_POLL'));
        }

        // Checking that the topic has not been locked
        if ($t_data['topic_status'] == TOPIC_LOCKED) {
            bb_die(__('TOPIC_LOCKED_SHORT'));
        }

        // Checking that poll has not been finished
        if (!\TorrentPier\Legacy\Poll::pollIsActive($t_data)) {
            bb_die(__('NEW_POLL_ENDED'));
        }

        if (!$vote_id) {
            bb_die(__('NO_VOTE_OPTION'));
        }

        if (\TorrentPier\Legacy\Poll::userIsAlreadyVoted($topic_id, (int)$userdata['user_id'])) {
            bb_die(__('ALREADY_VOTED'));
        }

        $affected_rows = DB()->table(BB_POLL_VOTES)
            ->where('topic_id', $topic_id)
            ->where('vote_id', $vote_id)
            ->update(['vote_result' => new \Nette\Database\SqlLiteral('vote_result + 1')]);

        if ($affected_rows != 1) {
            bb_die(__('NO_VOTE_OPTION'));
        }

        // Voting process
        try {
            DB()->table(BB_POLL_USERS)->insert([
                'topic_id' => $topic_id,
                'user_id' => $userdata['user_id'],
                'vote_ip' => USER_IP,
                'vote_dt' => TIMENOW
            ]);
        } catch (\Nette\Database\UniqueConstraintViolationException $e) {
            // Ignore duplicate entry (equivalent to INSERT IGNORE)
        }
        CACHE('bb_poll_data')->rm("poll_$topic_id");
        bb_die(__('VOTE_CAST'));
        break;
    case 'poll_start':
        // Checking for poll existence
        if (!$t_data['topic_vote']) {
            bb_die(__('POST_HAS_NO_POLL'));
        }

        // Log action
        $log_action->mod('mod_topic_poll_started', [
            'forum_id' => $forum_id,
            'topic_id' => $topic_id,
            'topic_title' => $t_data['topic_title'],
        ]);

        // Starting the poll
        DB()->table(BB_TOPICS)
            ->where('topic_id', $topic_id)
            ->update(['topic_vote' => POLL_STARTED]);
        bb_die(__('NEW_POLL_START'));
        break;
    case 'poll_finish':
        // Checking for poll existence
        if (!$t_data['topic_vote']) {
            bb_die(__('POST_HAS_NO_POLL'));
        }

        // Log action
        $log_action->mod('mod_topic_poll_finished', [
            'forum_id' => $forum_id,
            'topic_id' => $topic_id,
            'topic_title' => $t_data['topic_title'],
        ]);

        // Finishing the poll
        DB()->table(BB_TOPICS)
            ->where('topic_id', $topic_id)
            ->update(['topic_vote' => POLL_FINISHED]);
        bb_die(__('NEW_POLL_END'));
        break;
    case 'poll_delete':
        // Checking for poll existence
        if (!$t_data['topic_vote']) {
            bb_die(__('POST_HAS_NO_POLL'));
        }

        // Log action
        $log_action->mod('mod_topic_poll_deleted', [
            'forum_id' => $forum_id,
            'topic_id' => $topic_id,
            'topic_title' => $t_data['topic_title'],
        ]);

        // Removing poll from database
        $poll->delete_poll($topic_id);
        bb_die(__('NEW_POLL_DELETE'));
        break;
    case 'poll_add':
        // Checking that no other poll exists
        if ($t_data['topic_vote']) {
            bb_die(__('NEW_POLL_ALREADY'));
        }

        // Make a poll from $_POST data
        $poll->build_poll_data($_POST);

        // Showing errors if present
        if ($poll->err_msg) {
            bb_die($poll->err_msg);
        }

        // Log action
        $log_action->mod('mod_topic_poll_added', [
            'forum_id' => $forum_id,
            'topic_id' => $topic_id,
            'topic_title' => $t_data['topic_title'],
        ]);

        // Adding poll info to the database
        $poll->insert_votes_into_db($topic_id);
        bb_die(__('NEW_POLL_ADDED'));
        break;
    case 'poll_edit':
        // Checking for poll existence
        if (!$t_data['topic_vote']) {
            bb_die(__('POST_HAS_NO_POLL'));
        }

        // Make a poll from $_POST data
        $poll->build_poll_data($_POST);

        // Showing errors if present
        if ($poll->err_msg) {
            bb_die($poll->err_msg);
        }

        // Log action
        $log_action->mod('mod_topic_poll_edited', [
            'forum_id' => $forum_id,
            'topic_id' => $topic_id,
            'topic_title' => $t_data['topic_title'],
        ]);

        // Updating poll info to the database
        $poll->insert_votes_into_db($topic_id);
        CACHE('bb_poll_data')->rm("poll_$topic_id");
        bb_die(__('NEW_POLL_RESULTS'));
        break;
    default:
        bb_die('Invalid mode: ' . htmlCHR($mode));
}
