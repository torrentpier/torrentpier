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

global $datastore, $lang;

$ranks = $datastore->get('ranks');
$rank_id = (int)$this->request['rank_id'];

if (!$user_id = (int)$this->request['user_id'] or !$profiledata = get_userdata($user_id)) {
    $this->ajax_die("invalid user_id: $user_id");
}

if ($rank_id != 0 && !isset($ranks[$rank_id])) {
    $this->ajax_die("invalid rank_id: $rank_id");
}

DB()->query("UPDATE " . BB_USERS . " SET user_rank = $rank_id WHERE user_id = $user_id");

cache_rm_user_sessions($user_id);

$user_rank = ($rank_id) ? '<span class="' . $ranks[$rank_id]['rank_style'] . '">' . $ranks[$rank_id]['rank_title'] . '</span>' : '';

$this->response['html'] = ($rank_id) ? $lang['AWARDED_RANK'] . "<b> $user_rank </b>" : $lang['SHOT_RANK'];
$this->response['rank_name'] = ($rank_id) ? $user_rank : $lang['USER'];
