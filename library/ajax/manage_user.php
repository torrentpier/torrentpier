<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $userdata, $lang;

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

$mode = (string)$this->request['mode'];
$user_id = $this->request['user_id'];

switch ($mode) {
    case 'delete_profile':

        if ($userdata['user_id'] == $user_id) {
            $this->ajax_die($lang['USER_DELETE_ME']);
        }
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm($lang['USER_DELETE_CONFIRM']);
        }

        if ($user_id != BOT_UID) {
            delete_user_sessions($user_id);
            user_delete($user_id);

            $this->response['info'] = $lang['USER_DELETED'];
        } else {
            $this->ajax_die($lang['USER_DELETE_CSV']);
        }

        break;

    case 'delete_topics':

        if (empty($this->request['confirmed']) && $userdata['user_id'] == $user_id) {
            $this->prompt_for_confirm($lang['DELETE_USER_POSTS_ME']);
        }
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm($lang['DELETE_USER_ALL_POSTS_CONFIRM']);
        }

        if (IS_ADMIN) {
            $user_topics = DB()->fetch_rowset("SELECT topic_id FROM " . BB_TOPICS . " WHERE topic_poster = $user_id", 'topic_id');
            $deleted_topics = topic_delete($user_topics);
            $deleted_posts = post_delete('user', $user_id);

            $this->response['info'] = $lang['USER_DELETED_POSTS'];
        } else {
            $this->ajax_die($lang['NOT_ADMIN']);
        }

        break;

    case 'delete_message':

        if (empty($this->request['confirmed']) && $userdata['user_id'] == $user_id) {
            $this->prompt_for_confirm($lang['DELETE_USER_POSTS_ME']);
        }
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm($lang['DELETE_USER_POSTS_CONFIRM']);
        }

        if (IS_ADMIN) {
            post_delete('user', $user_id);

            $this->response['info'] = $lang['USER_DELETED_POSTS'];
        } else {
            $this->ajax_die($lang['NOT_ADMIN']);
        }

        break;

    case 'user_activate':

        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm($lang['DEACTIVATE_CONFIRM']);
        }

        DB()->query("UPDATE " . BB_USERS . " SET user_active = '1' WHERE user_id = " . $user_id);

        $this->response['info'] = $lang['USER_ACTIVATE_ON'];

        break;

    case 'user_deactivate':

        if ($userdata['user_id'] == $user_id) {
            $this->ajax_die($lang['USER_DEACTIVATE_ME']);
        }
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm($lang['ACTIVATE_CONFIRM']);
        }

        DB()->query("UPDATE " . BB_USERS . " SET user_active = '0' WHERE user_id = " . $user_id);
        delete_user_sessions($user_id);

        $this->response['info'] = $lang['USER_ACTIVATE_OFF'];

        break;
}

$this->response['mode'] = $mode;
$this->response['url'] = html_entity_decode(make_url('/') . PROFILE_URL . $user_id);
