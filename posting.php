<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'posting');

require __DIR__ . '/common.php';
require INC_DIR . '/bbcode.php';
require ATTACH_DIR . '/attachment_mod.php';

$page_cfg['load_tpl_vars'] = [
    'post_icons'
];

$submit = (bool)@$_REQUEST['post'];
$refresh = $preview = (bool)@$_REQUEST['preview'];
$delete = (bool)@$_REQUEST['delete'];
$mode = (string)@$_REQUEST['mode'];
$confirm = isset($_POST['confirm']);

$forum_id = (int)@$_REQUEST[POST_FORUM_URL];
$topic_id = (int)@$_REQUEST[POST_TOPIC_URL];
$post_id = (int)@$_REQUEST[POST_POST_URL];

// Set topic type
$topic_type = (@$_POST['topictype']) ? (int)$_POST['topictype'] : POST_NORMAL;
$topic_type = in_array($topic_type, [POST_NORMAL, POST_STICKY, POST_ANNOUNCE]) ? $topic_type : POST_NORMAL;

$selected_rg = 0;
$switch_rg_sig = 0;
$switch_poster_rg_sig = 0;

if ($mode == 'smilies') {
    generate_smilies('window');
    exit;
}

$tracking_topics = get_tracks('topic');
$tracking_forums = get_tracks('forum');

// Start session management
$user->session_start();

set_die_append_msg($forum_id, $topic_id);

// What auth type do we need to check?
$is_auth = [];
switch ($mode) {
    case 'newtopic':
    case 'new_rel':
        if (bf($userdata['user_opt'], 'user_opt', 'dis_topic')) {
            bb_die($lang['RULES_POST_CANNOT']);
        }
        if ($topic_type == POST_ANNOUNCE) {
            $is_auth_type = 'auth_announce';
        } elseif ($topic_type == POST_STICKY) {
            $is_auth_type = 'auth_sticky';
        } else {
            $is_auth_type = 'auth_post';
        }
        break;

    case 'reply':
    case 'quote':
        if (bf($userdata['user_opt'], 'user_opt', 'dis_post')) {
            bb_die($lang['RULES_REPLY_CANNOT']);
        }
        $is_auth_type = 'auth_reply';
        break;

    case 'editpost':
        if (bf($userdata['user_opt'], 'user_opt', 'dis_post_edit')) {
            bb_die($lang['RULES_EDIT_CANNOT']);
        }
        $is_auth_type = 'auth_edit';
        break;

    case 'delete':
        $is_auth_type = 'auth_delete';
        break;

    default:
        bb_simple_die($lang['NO_POST_MODE']);
        break;
}

// Various lookups to find topic_id, forum_id, post_id etc
$error_msg = '';
$post_data = [];
switch ($mode) {
    case 'newtopic':
    case 'new_rel':
        if (!$forum_id) {
            bb_simple_die($lang['FORUM_NOT_EXIST']);
        }
        $sql = "SELECT * FROM " . BB_FORUMS . " WHERE forum_id = $forum_id LIMIT 1";
        break;

    case 'reply':
        if (!$topic_id) {
            bb_die($lang['NO_TOPIC_ID']);
        }
        $sql = "SELECT f.*, t.*
			FROM " . BB_FORUMS . " f, " . BB_TOPICS . " t
			WHERE t.topic_id = $topic_id
				AND f.forum_id = t.forum_id
			LIMIT 1";
        break;

    case 'quote':
    case 'editpost':
    case 'delete':
        if (!$post_id) {
            bb_simple_die($lang['NO_POST_ID']);
        }

        $select_sql = 'SELECT f.*, t.*, p.*';
        $select_sql .= !$submit ? ', pt.*, u.username, u.user_id' : '';

        $from_sql = "FROM " . BB_POSTS . " p, " . BB_TOPICS . " t, " . BB_FORUMS . " f";
        $from_sql .= !$submit ? ", " . BB_POSTS_TEXT . " pt, " . BB_USERS . " u" : '';

        $where_sql = "
			WHERE p.post_id = $post_id
			AND t.topic_id = p.topic_id
			AND f.forum_id = p.forum_id
		";
        $where_sql .= (!$submit) ? "
			AND pt.post_id = p.post_id
			AND u.user_id = p.poster_id
		" : '';

        $sql = "$select_sql $from_sql $where_sql LIMIT 1";
        break;

    default:
        bb_simple_die($lang['NO_VALID_MODE']);
}

if ($post_info = DB()->fetch_row($sql)) {
    $forum_id = $post_info['forum_id'];
    $forum_name = $post_info['forum_name'];

    set_die_append_msg($forum_id);

    $is_auth = auth(AUTH_ALL, $forum_id, $userdata, $post_info);

    if ($post_info['forum_status'] == FORUM_LOCKED && !$is_auth['auth_mod']) {
        bb_die($lang['FORUM_LOCKED']);
    } elseif ($mode != 'newtopic' && $mode != 'new_rel' && $post_info['topic_status'] == TOPIC_LOCKED && !$is_auth['auth_mod']) {
        bb_die($lang['TOPIC_LOCKED']);
    }

    if ($mode == 'editpost' || $mode == 'delete') {
        $topic_id = $post_info['topic_id'];

        $post_data['poster_post'] = ($post_info['poster_id'] == $userdata['user_id']);
        $post_data['first_post'] = ($post_info['topic_first_post_id'] == $post_id);
        $post_data['last_post'] = ($post_info['topic_last_post_id'] == $post_id);
        $post_data['last_topic'] = ($post_info['forum_last_post_id'] == $post_id);
        $post_data['topic_type'] = $post_info['topic_type'];
        $post_data['poster_id'] = $post_info['poster_id'];

        $selected_rg = $post_info['poster_rg_id'];
        $switch_rg_sig = (bool)$post_info['attach_rg_sig'];

        // Can this user edit/delete the post?
        if ($post_info['poster_id'] != $userdata['user_id'] && !$is_auth['auth_mod']) {
            $auth_err = ($delete || $mode == 'delete') ? $lang['DELETE_OWN_POSTS'] : $lang['EDIT_OWN_POSTS'];
        } elseif (!$post_data['last_post'] && !$is_auth['auth_mod'] && ($mode == 'delete' || $delete)) {
            $auth_err = $lang['CANNOT_DELETE_REPLIED'];
        }

        if (isset($auth_err)) {
            bb_die($auth_err);
        }
    } else {
        if ($mode == 'quote') {
            $topic_id = $post_info['topic_id'];
        }
        if ($mode == 'newtopic') {
            $post_data['topic_type'] = POST_NORMAL;
        }
        $post_data['first_post'] = ($mode == 'newtopic');
        $post_data['last_post'] = false;
    }
} else {
    bb_die($lang['NO_SUCH_POST']);
}

// The user is not authed, if they're not logged in then redirect
// them, else show them an error message
if (!$is_auth[$is_auth_type]) {
    if (!IS_GUEST) {
        bb_die(sprintf($lang['SORRY_' . strtoupper($is_auth_type)], $is_auth[$is_auth_type . '_type']));
    }

    switch ($mode) {
        case 'newtopic':
            $redirect = "mode=newtopic&" . POST_FORUM_URL . "=$forum_id";
            break;
        case 'new_rel':
            $redirect = "mode=new_rel&" . POST_FORUM_URL . "=$forum_id";
            break;
        case 'reply':
            $redirect = "mode=reply&" . POST_TOPIC_URL . "=$topic_id";
            break;
        case 'quote':
        case 'editpost':
            $redirect = "mode=quote&" . POST_POST_URL . "=$post_id";
            break;
        default:
            $redirect = '';
    }
    redirect(LOGIN_URL . "?redirect=/" . POSTING_URL . "?$redirect");
}

if ($mode == 'new_rel') {
    if ($tor_status = implode(',', config()->get('tor_cannot_new'))) {
        $sql = DB()->fetch_rowset("SELECT t.topic_title, t.topic_id, tor.tor_status
			FROM " . BB_BT_TORRENTS . " tor, " . BB_TOPICS . " t
			WHERE poster_id = {$userdata['user_id']}
				AND tor.topic_id = t.topic_id
				AND tor.tor_status IN ($tor_status)
			ORDER BY tor.reg_time
		");

        $topics = '';
        foreach ($sql as $row) {
            $topics .= config()->get('tor_icons')[$row['tor_status']] . '<a href="' . TOPIC_URL . $row['topic_id'] . '">' . $row['topic_title'] . '</a><div class="spacer_12"></div>';
        }
        if ($topics && !(IS_SUPER_ADMIN && !empty($_REQUEST['edit_tpl']))) {
            bb_die($topics . $lang['UNEXECUTED_RELEASE']);
        }
    }
    require INC_DIR . '/posting_tpl.php';
    exit;
}

// Disallowed release editing with a certain status
if (!empty(config()->get('tor_cannot_edit')) && $post_info['allow_reg_tracker'] && $post_data['first_post'] && !IS_AM) {
    if ($tor_status = DB()->fetch_row("SELECT tor_status FROM " . BB_BT_TORRENTS . " WHERE topic_id = $topic_id AND forum_id = $forum_id AND tor_status IN(" . implode(',', config()->get('tor_cannot_edit')) . ") LIMIT 1")) {
        bb_die($lang['NOT_EDIT_TOR_STATUS'] . ':&nbsp;<span title="' . $lang['TOR_STATUS_NAME'][$tor_status['tor_status']] . '">' . config()->get('tor_icons')[$tor_status['tor_status']] . '&nbsp;' . $lang['TOR_STATUS_NAME'][$tor_status['tor_status']] . '</span>.');
    }
}

// Notify & Allow robots indexing
$robots_indexing = $post_info['topic_allow_robots'] ?? true;
if ($submit || $refresh) {
    if (IS_AM) {
        $robots_indexing = !empty($_POST['robots']);
    }
    $notify_user = (int)!empty($_POST['notify']);
} else {
    $notify_user = bf($userdata['user_opt'], 'user_opt', 'user_notify');

    if (!IS_GUEST && $mode != 'newtopic' && !$notify_user) {
        $notify_user = (int)DB()->fetch_row("SELECT topic_id FROM " . BB_TOPICS_WATCH . " WHERE topic_id = $topic_id AND user_id = " . $userdata['user_id']);
    }
}

$update_post_time = !empty($_POST['update_post_time']);

execute_posting_attachment_handling();

// If while you were writing a response, new messages appeared in the topic,
// before your message is sent, a warning is displayed with an overview of these messages
$topic_has_new_posts = false;

if (!IS_GUEST && $mode != 'newtopic' && ($submit || $preview || $mode == 'quote' || $mode == 'reply') && isset($_COOKIE[COOKIE_TOPIC])) {
    if ($topic_last_read = max((int)(@$tracking_topics[$topic_id]), (int)(@$tracking_forums[$forum_id]))) {
        $sql = "SELECT p.*, pt.post_text, u.username, u.user_rank
			FROM " . BB_POSTS . " p, " . BB_POSTS_TEXT . " pt, " . BB_USERS . " u
			WHERE p.topic_id = " . (int)$topic_id . "
				AND u.user_id = p.poster_id
				AND pt.post_id = p.post_id
				AND p.post_time > $topic_last_read
			ORDER BY p.post_time
			LIMIT " . config()->get('posts_per_page');

        if ($rowset = DB()->fetch_rowset($sql)) {
            $topic_has_new_posts = true;

            foreach ($rowset as $i => $row) {
                $template->assign_block_vars('new_posts', [
                    'ROW_CLASS' => !($i % 2) ? 'row1' : 'row2',
                    'POSTER' => profile_url($row),
                    'POSTER_NAME_JS' => addslashes($row['username']),
                    'POST_DATE' => '<a class="small" href="' . POST_URL . $row['post_id'] . '#' . $row['post_id'] . '" title="' . $lang['POST_LINK'] . '">' . bb_date($row['post_time'], config()->get('post_date_format')) . '</a>',
                    'MESSAGE' => get_parsed_post($row)
                ]);
            }
            $template->assign_vars(['TPL_SHOW_NEW_POSTS' => true]);

            set_tracks(COOKIE_TOPIC, $tracking_topics, $topic_id);
            unset($rowset);
        }
    }
}

// Confirm deletion
if (($delete || $mode == 'delete') && !$confirm) {
    if (isset($_POST['cancel'])) {
        redirect(POST_URL . "$post_id#$post_id");
    }
    $hidden_fields = [
        POST_POST_URL => $post_id,
        'mode' => 'delete'
    ];
    print_confirmation([
        'QUESTION' => $lang['CONFIRM_DELETE'],
        'FORM_ACTION' => POSTING_URL,
        'HIDDEN_FIELDS' => build_hidden_fields($hidden_fields)
    ]);
} elseif (($submit || $confirm) && !$topic_has_new_posts) {
    //
    // Submit post (newtopic, edit, reply, etc.)
    //
    $return_message = '';
    $return_meta = '';

    switch ($mode) {
        case 'editpost':
        case 'newtopic':
        case 'reply':
            $username = (!empty($_POST['username'])) ? clean_username($_POST['username']) : '';
            $subject = (!empty($_POST['subject'])) ? clean_title($_POST['subject']) : '';
            $message = (!empty($_POST['message'])) ? prepare_message($_POST['message']) : '';
            $attach_rg_sig = (isset($_POST['attach_rg_sig'], $_POST['poster_rg']) && $_POST['poster_rg'] != -1) ? 1 : 0;
            $poster_rg_id = (isset($_POST['poster_rg']) && $_POST['poster_rg'] != -1) ? (int)$_POST['poster_rg'] : 0;

            \TorrentPier\Legacy\Post::prepare_post($mode, $post_data, $error_msg, $username, $subject, $message);

            if (!$error_msg) {
                $topic_type = (isset($post_data['topic_type']) && $topic_type != $post_data['topic_type'] && !$is_auth['auth_sticky'] && !$is_auth['auth_announce']) ? $post_data['topic_type'] : $topic_type;

                \TorrentPier\Legacy\Post::submit_post($mode, $post_data, $return_message, $return_meta, $forum_id, $topic_id, $post_id, $topic_type, DB()->escape($username), DB()->escape($subject), DB()->escape($message), $update_post_time, $poster_rg_id, $attach_rg_sig, (int)$robots_indexing);

                $post_url = POST_URL . "$post_id#$post_id";
                $post_msg = ($mode == 'editpost') ? $lang['EDITED'] : $lang['STORED'];
                $onclick = ($mode == 'editpost') ? 'onclick="return post2url(this.href);"' : '';
                $return_message .= $post_msg . '<br /><br />
					<a ' . $onclick . ' href="' . $post_url . '" >' . $lang['POST_RETURN'] . '</a>
				';
            }
            break;

        case 'delete':
            if (!$post_data['first_post']) {
                \TorrentPier\Legacy\Post::delete_post($mode, $post_data, $return_message, $return_meta, $forum_id, $topic_id, $post_id);
            } else {
                redirect('modcp.php?' . POST_TOPIC_URL . "=$topic_id&mode=delete&sid=" . $userdata['session_id']);
            }
            break;
    }

    if (!$error_msg) {
        if (!in_array($mode, ['editpost', 'delete'])) {
            $user_id = ($mode == 'reply' || $mode == 'newtopic') ? $userdata['user_id'] : $post_data['poster_id'];
            \TorrentPier\Legacy\Post::update_post_stats($mode, $post_data, $forum_id, $topic_id, $post_id, $user_id);
        }
        $attachment_mod['posting']->insert_attachment($post_id);

        if (!$error_msg) {
            \TorrentPier\Legacy\Post::user_notification($mode, $post_data, $post_info['topic_title'], $forum_id, $topic_id, $notify_user);
        }

        if ($mode == 'newtopic' || $mode == 'reply') {
            set_tracks(COOKIE_TOPIC, $tracking_topics, $topic_id);
        }

        if (defined('TORRENT_ATTACH_ID') && config()->get('bt_newtopic_auto_reg') && !$error_msg) {
            if (!DB()->fetch_row("SELECT attach_id FROM " . BB_BT_TORRENTS . " WHERE attach_id = " . TORRENT_ATTACH_ID)) {
                if (config()->get('premod')) {
                    // Getting a list of forum ids starting with "parent"
                    $forum_parent = $forum_id;
                    if ($post_info['forum_parent']) {
                        $forum_parent = $post_info['forum_parent'];
                    }
                    $count_rowset = DB()->fetch_rowset("SELECT forum_id FROM " . BB_FORUMS . " WHERE forum_parent = $forum_parent");
                    $sub_forums = [];
                    foreach ($count_rowset as $count_row) {
                        if ($count_row['forum_id'] != $forum_id) {
                            $sub_forums[] = $count_row['forum_id'];
                        }
                    }
                    $sub_forums[] = $forum_id;
                    $sub_forums = implode(',', $sub_forums);
                    // Counting verified releases in section forums
                    $count_checked_releases = DB()->fetch_row("
						SELECT COUNT(*) AS checked_releases
						FROM " . BB_BT_TORRENTS . "
						WHERE poster_id  = " . $userdata['user_id'] . "
						  AND forum_id   IN($sub_forums)
						  AND tor_status IN(" . TOR_APPROVED . "," . TOR_DOUBTFUL . "," . TOR_TMP . ")
						LIMIT 1
					", 'checked_releases');
                    if ($count_checked_releases || IS_AM) {
                        \TorrentPier\Legacy\Torrent::tracker_register(TORRENT_ATTACH_ID, 'newtopic', TOR_NOT_APPROVED);
                    } else {
                        \TorrentPier\Legacy\Torrent::tracker_register(TORRENT_ATTACH_ID, 'newtopic', TOR_PREMOD);
                    }
                } else {
                    \TorrentPier\Legacy\Torrent::tracker_register(TORRENT_ATTACH_ID, 'newtopic', TOR_NOT_APPROVED);
                }
            }
        }

        // Update atom feed
        update_atom('topic', $topic_id);

        if ($mode == 'reply' && $post_info['topic_status'] == TOPIC_LOCKED) {
            $locked_warn = '
				<div class="warnColor1">
					<b>' . $lang['LOCKED_WARN'] . '</b>
				</div>
				<br /><hr/><br />
			';
            $return_message = $locked_warn . $return_message;
        }

        bb_die($return_message);
    }
}

if ($refresh || $error_msg || ($submit && $topic_has_new_posts)) {
    $username = (!empty($_POST['username'])) ? clean_username($_POST['username']) : '';
    $subject = (!empty($_POST['subject'])) ? clean_title($_POST['subject']) : '';
    $message = (!empty($_POST['message'])) ? prepare_message($_POST['message']) : '';

    if ($preview) {
        $preview_subject = $subject;
        $preview_username = $username;
        $preview_message = htmlCHR($message, false, ENT_NOQUOTES);
        $preview_message = bbcode2html($preview_message);

        $template->assign_vars([
            'TPL_PREVIEW_POST' => true,
            'TOPIC_TITLE' => $preview_subject,
            'POST_SUBJECT' => $preview_subject,
            'POSTER_NAME' => $preview_username,
            'POST_DATE' => bb_date(TIMENOW),
            'PREVIEW_MSG' => $preview_message
        ]);
    }
} else {
    // User default entry point
    if ($mode == 'newtopic') {
        $username = !IS_GUEST ? $userdata['username'] : '';
        $subject = $message = '';
    } elseif ($mode == 'reply') {
        $username = !IS_GUEST ? $userdata['username'] : '';
        $subject = $message = '';
    } elseif ($mode == 'quote' || $mode == 'editpost') {
        $subject = ($post_data['first_post']) ? $post_info['topic_title'] : '';
        $message = $post_info['post_text'];

        if ($mode == 'quote') {
            if ($post_info['post_attachment'] && !IS_AM) {
                $message = $post_info['topic_title'];
            }
            $quote_username = ($post_info['post_username'] != '') ? $post_info['post_username'] : $post_info['username'];
            $message = '[quote="' . $quote_username . '"][qpost=' . $post_info['post_id'] . ']' . $message . '[/quote]';

            // hide user passkey
            $message = preg_replace('#(?<=[\?&;]' . config()->get('passkey_key') . '=)[a-zA-Z0-9]#', 'passkey', $message);
            // hide sid
            $message = preg_replace('#(?<=[\?&;]sid=)[a-zA-Z0-9]#', 'sid', $message);

            $subject = censor()->censorString($subject);
            $message = censor()->censorString($message);

            if (!preg_match('/^Re:/', $subject) && !empty($subject)) {
                $subject = 'Re: ' . $subject;
            }

            $mode = 'reply';
        } else {
            $username = ($post_info['user_id'] == GUEST_UID && !empty($post_info['post_username'])) ? $post_info['post_username'] : '';
        }
    }
}

if ($error_msg) {
    $template->assign_vars(['ERROR_MESSAGE' => $error_msg]);
}

if (IS_GUEST || ($mode == 'editpost' && $post_info['poster_id'] == GUEST_UID)) {
    $template->assign_var('POSTING_USERNAME');
}

// Notify checkbox
if (!IS_GUEST) {
    if ($mode != 'editpost' || ($mode == 'editpost' && $post_info['poster_id'] != GUEST_UID)) {
        $template->assign_var('SHOW_NOTIFY_CHECKBOX');
    }
}

$topic_type_toggle = '';
if ($mode == 'newtopic' || ($mode == 'editpost' && $post_data['first_post'])) {
    // Allow robots indexing
    if (IS_AM) {
        $template->assign_var('SHOW_ROBOTS_CHECKBOX');
    }

    // Topic type selection
    $template->assign_block_vars('switch_type_toggle', []);

    if ($is_auth['auth_sticky']) {
        $topic_type_toggle .= '<label><input type="radio" name="topictype" value="' . POST_STICKY . '"';
        if (isset($post_data['topic_type']) && ($post_data['topic_type'] == POST_STICKY || $topic_type == POST_STICKY)) {
            $topic_type_toggle .= ' checked';
        }
        $topic_type_toggle .= ' /> ' . $lang['POST_STICKY'] . '</label>&nbsp;&nbsp;';
    }

    if ($is_auth['auth_announce']) {
        $topic_type_toggle .= '<label><input type="radio" name="topictype" value="' . POST_ANNOUNCE . '"';
        if (isset($post_data['topic_type']) && ($post_data['topic_type'] == POST_ANNOUNCE || $topic_type == POST_ANNOUNCE)) {
            $topic_type_toggle .= ' checked';
        }
        $topic_type_toggle .= ' /> ' . $lang['POST_ANNOUNCEMENT'] . '</label>&nbsp;&nbsp;';
    }

    if ($topic_type_toggle != '') {
        $topic_type_toggle = $lang['POST_TOPIC_AS'] . ': <label><input type="radio" name="topictype" value="' . POST_NORMAL . '"' . ((!isset($post_data['topic_type']) || $post_data['topic_type'] == POST_NORMAL || $topic_type == POST_NORMAL) ? ' checked' : '') . ' /> ' . $lang['POST_NORMAL'] . '</label>&nbsp;&nbsp;' . $topic_type_toggle;
    }
}
//bt
$topic_dl_type = $post_info['topic_dl_type'] ?? 0;

if ($post_info['allow_reg_tracker'] && $post_data['first_post'] && ($topic_dl_type || $is_auth['auth_mod'])) {
    $sql = "
		SELECT tor.attach_id
		FROM " . BB_POSTS . " p
		LEFT JOIN " . BB_BT_TORRENTS . " tor ON (p.post_id = tor.post_id)
		WHERE p.post_id = $post_id
	";
    $result = DB()->fetch_row($sql);
    if (!empty($result['attach_id'])) {
        if (!$topic_type_toggle) {
            $topic_type_toggle = $lang['POST_TOPIC_AS'] . ': ';
        }

        $dl_ds = $dl_ch = $dl_hid = '';
        $dl_type_name = 'topic_dl_type';
        $dl_type_val = $topic_dl_type ? 1 : 0;

        if (!$post_info['allow_reg_tracker'] && !$is_auth['auth_mod']) {
            $dl_ds = ' disabled ';
            $dl_hid = '<input type="hidden" name="topic_dl_type" value="' . $dl_type_val . '" />';
            $dl_type_name = '';
        }

        $dl_ch = ($mode == 'editpost' && $post_data['first_post'] && $topic_dl_type) ? ' checked ' : '';

        $topic_type_toggle .= '<nobr><input type="checkbox" name="' . $dl_type_name . '" id="topic_dl_type_id" ' . $dl_ds . $dl_ch . ' /><label for="topic_dl_type_id"> ' . $lang['POST_DOWNLOAD'] . '</label></nobr>';
        $topic_type_toggle .= $dl_hid;
    }
}
//bt end

// Get poster release group data
if ($userdata['user_level'] == GROUP_MEMBER || IS_AM) {
    $poster_rgroups = '';

    $sql = "SELECT ug.group_id, g.group_name, g.release_group
		FROM " . BB_USER_GROUP . " ug
		INNER JOIN " . BB_GROUPS . " g ON(g.group_id = ug.group_id)
		WHERE ug.user_id = {$userdata['user_id']}
			AND g.release_group = 1
		ORDER BY g.group_name";

    foreach (DB()->fetch_rowset($sql) as $row) {
        $selected_opt = ($row['group_id'] == $selected_rg) ? 'selected' : '';
        $poster_rgroups .= '<option value="' . $row['group_id'] . '" ' . $selected_opt . '>' . $row['group_name'] . '</option>';
    }
}

// Assign posting title & hidden fields
$page_title = '';
$hidden_form_fields = '<input type="hidden" name="mode" value="' . $mode . '" />';

switch ($mode) {
    case 'newtopic':
        $page_title = $lang['POST_A_NEW_TOPIC'];
        $hidden_form_fields .= '<input type="hidden" name="' . POST_FORUM_URL . '" value="' . $forum_id . '" />';
        break;

    case 'reply':
        $page_title = $lang['POST_A_REPLY'];
        $hidden_form_fields .= '<input type="hidden" name="' . POST_TOPIC_URL . '" value="' . $topic_id . '" />';
        break;

    case 'editpost':
        $page_title = $lang['EDIT_POST'];
        $hidden_form_fields .= '<input type="hidden" name="' . POST_POST_URL . '" value="' . $post_id . '" />';
        break;
}

// Generate smilies listing for page output
generate_smilies('inline');

$template->set_filenames(['body' => 'posting.tpl']);

// Output the data to the template
$template->assign_vars([
    'FORUM_NAME' => htmlCHR($forum_name),
    'PAGE_TITLE' => $page_title,
    'POSTING_TYPE_TITLE' => $page_title,
    'POSTING_TOPIC_ID' => ($mode != 'newtopic') ? $topic_id : '',
    'POSTING_TOPIC_TITLE' => ($mode != 'newtopic') ? $post_info['topic_title'] : '',
    'U_VIEW_FORUM' => FORUM_URL . $forum_id,

    'USERNAME' => @$username,
    'CAPTCHA_HTML' => (IS_GUEST && !config()->get('captcha.disabled')) ? bb_captcha('get') : '',
    'SUBJECT' => $subject,
    'MESSAGE' => $message,

    'POSTER_RGROUPS' => !empty($poster_rgroups) ? $poster_rgroups : '',
    'ATTACH_RG_SIG' => $switch_rg_sig ?: false,

    'S_NOTIFY_CHECKED' => $notify_user ? 'checked' : '',
    'S_ROBOTS_CHECKED' => $robots_indexing ? 'checked' : '',
    'S_TYPE_TOGGLE' => $topic_type_toggle,
    'S_TOPIC_ID' => $topic_id,
    'S_POST_ACTION' => POSTING_URL,
    'S_HIDDEN_FORM_FIELDS' => $hidden_form_fields,
]);

if ($mode == 'newtopic' || $post_data['first_post']) {
    $template->assign_var('POSTING_SUBJECT');
}

// Update post time
if ($mode == 'editpost' && $post_data['last_post'] && !$post_data['first_post']) {
    $template->assign_vars([
        'SHOW_UPDATE_POST_TIME' => ($is_auth['auth_mod'] || ($post_data['poster_post'] && $post_info['post_time'] + 3600 * 3 > TIMENOW)),
        'UPDATE_POST_TIME_CHECKED' => ($post_data['poster_post'] && ($post_info['post_time'] + 3600 * 2 > TIMENOW)),
    ]);
}

// Topic review
if ($mode == 'reply' && $is_auth['auth_read']) {
    \TorrentPier\Legacy\Post::topic_review($topic_id);
}

require(PAGE_HEADER);

$template->pparse('body');

require(PAGE_FOOTER);
