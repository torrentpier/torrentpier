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

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

define('ONLY_NEW_POSTS', 1);
define('ONLY_NEW_TOPICS', 2);

/**
 * @deprecated user_common
 * Dirty class removed from here since 2.2.0
 * To add new opt see at src/Legacy/Common/User.php
 */

//
// userdata cache
//
function ignore_cached_userdata()
{
    return defined('IN_PM') ? true : false;
}

function cache_get_userdata($id)
{
    if (ignore_cached_userdata()) {
        return false;
    }

    return CACHE('session_cache')->get($id);
}

function cache_set_userdata($userdata, $force = false)
{
    global $bb_cfg;

    if (!$userdata || (ignore_cached_userdata() && !$force)) {
        return false;
    }

    $id = ($userdata['user_id'] == GUEST_UID) ? $userdata['session_ip'] : $userdata['session_id'];
    return CACHE('session_cache')->set($id, $userdata, $bb_cfg['session_update_intrv']);
}

function cache_rm_userdata($userdata)
{
    if (!$userdata) {
        return false;
    }

    $id = ($userdata['user_id'] == GUEST_UID) ? $userdata['session_ip'] : $userdata['session_id'];
    return CACHE('session_cache')->rm($id);
}

// $user_id - array(id1,id2,..) or (string) id
function cache_rm_user_sessions($user_id)
{
    $user_id = get_id_csv($user_id);

    $rowset = DB()->fetch_rowset("
		SELECT session_id FROM " . BB_SESSIONS . " WHERE session_user_id IN($user_id)
	");

    foreach ($rowset as $row) {
        CACHE('session_cache')->rm($row['session_id']);
    }
}

function cache_update_userdata($userdata)
{
    return cache_set_userdata($userdata, true);
}

function db_update_userdata($userdata, $sql_ary, $data_already_escaped = true)
{
    if (!$userdata) {
        return false;
    }

    $sql_args = DB()->build_array('UPDATE', $sql_ary, $data_already_escaped);
    DB()->query("UPDATE " . BB_USERS . " SET $sql_args WHERE user_id = {$userdata['user_id']}");

    if (DB()->affected_rows()) {
        cache_rm_userdata($userdata);
    }
}

// $user_id - array(id1,id2,..) or (string) id
function delete_user_sessions($user_id)
{
    cache_rm_user_sessions($user_id);

    $user_id = get_id_csv($user_id);
    DB()->query("DELETE FROM " . BB_SESSIONS . " WHERE session_user_id IN($user_id)");
}

// deprecated
function session_begin($userdata, $page_id = 0, $enable_autologin = false, $auto_created = false)
{
    global $user;

    $user->session_create($userdata, $auto_created);

    return $user->data;
}

// deprecated
function session_pagestart($user_ip = USER_IP, $page_id = 0, $req_login = false)
{
    global $user;

    $user->session_start(array('req_login' => $req_login));

    return $user->data;
}
