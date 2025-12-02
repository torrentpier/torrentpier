<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

$forum_id = $_REQUEST[POST_FORUM_URL] ?? 0;
$topic_id = $_REQUEST[POST_TOPIC_URL] ?? 0;
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
    $redirect_type = 'search.php';
    $redirect = $full_url ?: "$dl_key=1";
} else {
    $redirect_type = (!$topic_id) ? 'viewforum.php' : 'viewtopic.php';
    $redirect = $full_url ?: ((!$topic_id) ? POST_FORUM_URL . "=$forum_id" : POST_TOPIC_URL . "=$topic_id");
}

set_die_append_msg();

// Check if user logged in
if (IS_GUEST) {
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
        $hidden_fields = [
            POST_TOPIC_URL => $topic_id,
            'mode' => 'dl_delete',
        ];

        print_confirmation([
            'QUESTION' => $lang['DL_LIST_DEL_CONFIRM'],
            'FORM_ACTION' => 'dl_list.php',
            'HIDDEN_FIELDS' => build_hidden_fields($hidden_fields),
        ]);
    }

    clear_dl_list($topic_id);
    redirect("$redirect_type?$redirect");
}

// Update DL status
$req_topics_ary = $topics_ary = [];

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
    $new_dlstatus_ary = [];

    foreach ($topics_ary as $topic_id) {
        $new_dlstatus_ary[] = [
            'user_id' => (int)$user->id,
            'topic_id' => (int)$topic_id,
            'user_status' => (int)$new_dl_status,
        ];
    }
    $new_dlstatus_sql = DB()->build_array('MULTI_INSERT', $new_dlstatus_ary);

    DB()->query("REPLACE INTO " . BB_BT_DLSTATUS . " $new_dlstatus_sql");

    redirect("$redirect_type?$redirect");
}

redirect('index.php');
