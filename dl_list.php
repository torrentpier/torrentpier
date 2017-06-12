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

define('BB_SCRIPT', 'dl_list');
define('BB_ROOT', './');
require __DIR__ . '/common.php';

$forum_id = isset($_REQUEST[POST_FORUM_URL]) ? (int)$_REQUEST[POST_FORUM_URL] : 0;
$topic_id = isset($_REQUEST[POST_TOPIC_URL]) ? (int)$_REQUEST[POST_TOPIC_URL] : 0;
$mode = isset($_REQUEST['mode']) ? (string)$_REQUEST['mode'] : '';
$confirmed = isset($_POST['confirm']);

// Get new DL-status
if ($mode == 'set_dl_status' || $mode == 'set_topics_dl_status') {
    if (isset($_POST['dl_set_will'])) {
        $new_dl_status = DL_STATUS_WILL;
        $dl_key = 'dlw';
    } elseif (isset($_POST['dl_set_down'])) {
        $new_dl_status = DL_STATUS_DOWN;
        $dl_key = 'dld';
    } elseif (isset($_POST['dl_set_complete'])) {
        $new_dl_status = DL_STATUS_COMPLETE;
        $dl_key = 'dlc';
    } elseif (isset($_POST['dl_set_cancel'])) {
        $new_dl_status = DL_STATUS_CANCEL;
        $dl_key = 'dla';
    } else {
        bb_die('Invalid download status');
    }
}

// Define redirect URL
$full_url = isset($_POST['full_url']) ? str_replace('&amp;', '&', htmlspecialchars($_POST['full_url'])) : '';

if (isset($_POST['redirect_type']) && $_POST['redirect_type'] == 'search') {
    $redirect_type = "search.php";
    $redirect = $full_url ?: "$dl_key=1";
} else {
    $redirect_type = (!$topic_id) ? "viewforum.php" : "viewtopic.php";
    $redirect = $full_url ?: ((!$topic_id) ? POST_FORUM_URL . "=$forum_id" : POST_TOPIC_URL . "=$topic_id");
}

// Start session management
$user->session_start();

set_die_append_msg();

// Check if user logged in
if (!$userdata['session_logged_in']) {
    redirect(LOGIN_URL . "?redirect=$redirect_type&$redirect");
}

// Check if user did not confirm
if (isset($_POST['cancel']) && $_POST['cancel']) {
    redirect("$redirect_type?$redirect");
}

// Delete DL-list
if ($mode == 'dl_delete' && $topic_id) {
    if (!IS_ADMIN) {
        $sql = "SELECT forum_id FROM " . BB_TOPICS . " WHERE topic_id = $topic_id LIMIT 1";

        if (!$row = DB()->sql_fetchrow(DB()->sql_query($sql))) {
            bb_die('Could not obtain forum_id for this topic');
        }

        $is_auth = auth(AUTH_ALL, $row['forum_id'], $userdata);

        if (!$is_auth['auth_mod']) {
            bb_die($lang['NOT_MODERATOR']);
        }
    }

    if (!$confirmed) {
        $hidden_fields = array(
            't' => $topic_id,
            'mode' => 'dl_delete',
        );

        print_confirmation(array(
            'QUESTION' => $lang['DL_LIST_DEL_CONFIRM'],
            'FORM_ACTION' => 'dl_list.php',
            'HIDDEN_FIELDS' => build_hidden_fields($hidden_fields),
        ));
    }

    clear_dl_list($topic_id);
    redirect("$redirect_type?$redirect");
}

// Update DL status
$req_topics_ary = $topics_ary = array();

// Get topics selected by user
if ($mode == 'set_topics_dl_status') {
    if (!isset($_POST['dl_topics_id_list']) || !is_array($_POST['dl_topics_id_list'])) {
        bb_die($lang['NONE_SELECTED']);
    }

    foreach ($_POST['dl_topics_id_list'] as $topic_id) {
        $req_topics_ary[] = (int)$topic_id;
    }
} elseif ($mode == 'set_dl_status') {
    $req_topics_ary[] = (int)$topic_id;
}

// Get existing topics
if ($req_topics_sql = implode(',', $req_topics_ary)) {
    $sql = "SELECT topic_id FROM " . BB_TOPICS . " WHERE topic_id IN($req_topics_sql)";

    foreach (DB()->fetch_rowset($sql) as $row) {
        $topics_ary[] = $row['topic_id'];
    }
}

if ($topics_ary && ($mode == 'set_dl_status' || $mode == 'set_topics_dl_status')) {
    $new_dlstatus_ary = array();

    foreach ($topics_ary as $topic_id) {
        $new_dlstatus_ary[] = array(
            'user_id' => (int)$user->id,
            'topic_id' => (int)$topic_id,
            'user_status' => (int)$new_dl_status,
        );
    }
    $new_dlstatus_sql = DB()->build_array('MULTI_INSERT', $new_dlstatus_ary);

    DB()->query("REPLACE INTO " . BB_BT_DLSTATUS . " $new_dlstatus_sql");

    redirect("$redirect_type?$redirect");
}

redirect("index.php");
