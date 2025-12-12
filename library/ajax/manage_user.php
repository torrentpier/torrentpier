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

if (!$user_id = (int)$this->request['user_id']) {
    $this->ajax_die(__('NO_USER_ID_SPECIFIED'));
}

// Check for demo mode
if (IN_DEMO_MODE) {
    $this->ajax_die(__('CANT_EDIT_IN_DEMO_MODE'));
}

switch ($mode) {
    case 'delete_profile':
        if (userdata('user_id') == $user_id) {
            $this->ajax_die(__('USER_DELETE_ME'));
        }
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm(__('USER_DELETE_CONFIRM'));
        }

        if (!in_array($user_id, explode(',', EXCLUDED_USERS))) {
            \TorrentPier\Sessions::delete_user_sessions($user_id);
            \TorrentPier\Legacy\Admin\Common::user_delete($user_id);

            $user_id = userdata('user_id'); // Store self user_id for redirect after successful deleting
            $this->response['info'] = __('USER_DELETED');
        } else {
            $this->ajax_die(__('USER_DELETE_CSV'));
        }
        break;
    case 'delete_topics':
        if (userdata('user_id') == $user_id) {
            $this->prompt_for_confirm(__('DELETE_USER_POSTS_ME'));
        }
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm(__('DELETE_USER_ALL_POSTS_CONFIRM'));
        }

        $user_topics = DB()->fetch_rowset("SELECT topic_id FROM " . BB_TOPICS . " WHERE topic_poster = $user_id", 'topic_id');
        $deleted_topics = \TorrentPier\Legacy\Admin\Common::topic_delete($user_topics);
        $deleted_posts = \TorrentPier\Legacy\Admin\Common::post_delete('user', $user_id);
        $this->response['info'] = __('USER_DELETED_POSTS');
        break;
    case 'delete_message':
        if (userdata('user_id') == $user_id) {
            $this->prompt_for_confirm(__('DELETE_USER_POSTS_ME'));
        }
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm(__('DELETE_USER_POSTS_CONFIRM'));
        }

        \TorrentPier\Legacy\Admin\Common::post_delete('user', $user_id);
        $this->response['info'] = __('USER_DELETED_POSTS');
        break;
    case 'user_activate':
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm(__('DEACTIVATE_CONFIRM'));
        }

        DB()->query("UPDATE " . BB_USERS . " SET user_active = 1 WHERE user_id = " . $user_id);
        $this->response['info'] = __('USER_ACTIVATE_ON');
        break;
    case 'user_deactivate':
        if (userdata('user_id') == $user_id) {
            $this->ajax_die(__('USER_DEACTIVATE_ME'));
        }
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm(__('ACTIVATE_CONFIRM'));
        }

        DB()->query("UPDATE " . BB_USERS . " SET user_active = 0 WHERE user_id = " . $user_id);
        \TorrentPier\Sessions::delete_user_sessions($user_id);
        $this->response['info'] = __('USER_ACTIVATE_OFF');
        break;
    default:
        $this->ajax_die('Invalid mode');
}

$this->response['mode'] = $mode;
$this->response['url'] = html_entity_decode(make_url(url()->member($user_id, get_username($user_id))));
