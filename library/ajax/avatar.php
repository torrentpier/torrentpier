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

global $lang, $user;

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

$mode = (string)$this->request['mode'];
$user_id = (int)$this->request['user_id'];

if (!$user_id || !($u_data = get_userdata($user_id))) {
    $this->ajax_die('Invalid user_id');
}

if (!IS_ADMIN && $user_id != $user->id) {
    $this->ajax_die($lang['NOT_ADMIN']);
}

switch ($mode) {
    case 'delete':
        delete_avatar($user_id, $u_data['avatar_ext_id']);
        $new_ext_id = 0;
        $response = '<img src="' . $di->config->get('avatars.upload_path') . $di->config->get('avatars.no_avatar') . '" alt="' . $user_id . '" />';
        break;
    default:
        $this->ajax_die('Invalid mode');
}

DB()->query("UPDATE " . BB_USERS . " SET avatar_ext_id = $new_ext_id WHERE user_id = $user_id LIMIT 1");

cache_rm_user_sessions($user_id);

$this->response['avatar_html'] = $response;
