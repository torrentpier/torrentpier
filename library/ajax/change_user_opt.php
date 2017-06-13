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

global $bf, $lang;

$user_id = (int)$this->request['user_id'];
$new_opt = json_decode($this->request['user_opt'], true);

if (!$user_id or !$u_data = get_userdata($user_id)) {
    $this->ajax_die('invalid user_id');
}

if (!is_array($new_opt)) {
    $this->ajax_die('invalid new_opt');
}

foreach ($bf['user_opt'] as $opt_name => $opt_bit) {
    if (isset($new_opt[$opt_name])) {
        setbit($u_data['user_opt'], $opt_bit, !empty($new_opt[$opt_name]));
    }
}

DB()->query("UPDATE " . BB_USERS . " SET user_opt = {$u_data['user_opt']} WHERE user_id = $user_id");

// Удаляем данные из кеша
cache_rm_user_sessions($user_id);

$this->response['resp_html'] = $lang['SAVED'];
