<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('IN_PM', true);

require INC_DIR . '/bbcode.php';

$privmsg_sent_id = $l_box_name = $to_username = $privmsg_subject = $privmsg_message = $error_msg = '';

// Page config
page_cfg('use_tablesorter', true);
page_cfg('load_tpl_vars', [
    'pm_icons'
]);

//
// Is PM disabled?
//
if (config()->get('privmsg_disable')) {
    bb_die('PM_DISABLED');
}

//
// Parameters
//
$submit = (bool)request_var('post', false);
$submit_search = (isset($_POST['usersubmit'])) ? true : 0;
$submit_msgdays = (isset($_POST['submit_msgdays'])) ? true : 0;
$cancel = (isset($_POST['cancel'])) ? true : 0;
$preview = (isset($_POST['preview'])) ? true : 0;
$confirmed = (isset($_POST['confirm'])) ? true : 0;
$delete = (isset($_POST['delete'])) ? true : 0;
$delete_all = (isset($_POST['deleteall'])) ? true : 0;
$save = (isset($_POST['save'])) ? true : 0;
$mode = isset($_REQUEST['mode']) ? htmlCHR($_REQUEST['mode']) : '';

$refresh = $preview || $submit_search;

$mark_list = (!empty($_POST['mark'])) ? $_POST['mark'] : 0;

if ($folder =& $_REQUEST['folder']) {
    if ($folder != 'inbox' && $folder != 'outbox' && $folder != 'sentbox' && $folder != 'savebox') {
        $folder = 'inbox';
    }
} else {
    $folder = 'inbox';
}

// Start session management
user()->session_start(['req_login' => true]);

template()->assign_vars([
    'IN_PM' => true,
    'QUICK_REPLY' => config()->get('show_quick_reply') && $folder == 'inbox' && $mode == 'read',
]);

//
// Set mode for quick reply
//
if (empty($mode) && config()->get('show_quick_reply') && $folder == 'inbox' && $preview) {
    $mode = 'reply';
}

//
// Cancel
//
if ($cancel) {
    redirect(PM_URL . "?folder=$folder");
}

//
// Var definitions
//
$start = isset($_REQUEST['start']) ? abs((int)$_REQUEST['start']) : 0;

if (isset($_POST[POST_POST_URL]) || isset($_GET[POST_POST_URL])) {
    $privmsg_id = isset($_POST[POST_POST_URL]) ? (int)$_POST[POST_POST_URL] : (int)$_GET[POST_POST_URL];
} else {
    $privmsg_id = '';
}

$error = false;

//
// Define the box image links
//
$inbox_url = ($folder != 'inbox' || $mode != '') ? '<a href="' . PM_URL . "?folder=inbox" . '">' . __('INBOX') . '</a>' : __('INBOX');
$outbox_url = ($folder != 'outbox' || $mode != '') ? '<a href="' . PM_URL . "?folder=outbox" . '">' . __('OUTBOX') . '</a>' : __('OUTBOX');
$sentbox_url = ($folder != 'sentbox' || $mode != '') ? '<a href="' . PM_URL . "?folder=sentbox" . '">' . __('SENTBOX') . '</a>' : __('SENTBOX');
$savebox_url = ($folder != 'savebox' || $mode != '') ? '<a href="' . PM_URL . "?folder=savebox" . '">' . __('SAVEBOX') . '</a>' : __('SAVEBOX');

//
// Start main
//
template()->assign_var('POSTING_SUBJECT');

if ($mode == 'read') {
    if (!empty($_GET[POST_POST_URL])) {
        $privmsgs_id = (int)$_GET[POST_POST_URL];
    } else {
        bb_die(__('NO_PM_ID'));
    }

    switch ($folder) {
        case 'inbox':
            $l_box_name = __('INBOX');
            $pm_sql_user = "AND pm.privmsgs_to_userid = " . userdata('user_id') . "
				AND ( pm.privmsgs_type = " . PRIVMSGS_READ_MAIL . "
					OR pm.privmsgs_type = " . PRIVMSGS_NEW_MAIL . "
					OR pm.privmsgs_type = " . PRIVMSGS_UNREAD_MAIL . " )";
            break;
        case 'outbox':
            $l_box_name = __('OUTBOX');
            $pm_sql_user = "AND pm.privmsgs_from_userid =  " . userdata('user_id') . "
				AND ( pm.privmsgs_type = " . PRIVMSGS_NEW_MAIL . "
					OR pm.privmsgs_type = " . PRIVMSGS_UNREAD_MAIL . " ) ";
            break;
        case 'sentbox':
            $l_box_name = __('SENTBOX');
            $pm_sql_user = "AND pm.privmsgs_from_userid =  " . userdata('user_id') . "
				AND pm.privmsgs_type = " . PRIVMSGS_SENT_MAIL;
            break;
        case 'savebox':
            $l_box_name = __('SAVEBOX');
            $pm_sql_user = "AND ( ( pm.privmsgs_to_userid = " . userdata('user_id') . "
					AND pm.privmsgs_type = " . PRIVMSGS_SAVED_IN_MAIL . " )
				OR ( pm.privmsgs_from_userid = " . userdata('user_id') . "
					AND pm.privmsgs_type = " . PRIVMSGS_SAVED_OUT_MAIL . " )
				)";
            break;
        default:
            bb_die(__('NO_SUCH_FOLDER'));
            break;
    }

    //
    // Major query obtains the message ...
    //
    $sql = "SELECT u.username, u.user_id, u.user_posts, u.user_from, u.user_email, u.user_regdate, u.user_rank,
			u2.username AS to_username, u2.user_id AS to_user_id, u2.user_rank as to_user_rank,
			pm.*, pmt.privmsgs_text
		FROM " . BB_PRIVMSGS . " pm, " . BB_PRIVMSGS_TEXT . " pmt, " . BB_USERS . " u, " . BB_USERS . " u2
		WHERE pm.privmsgs_id = $privmsgs_id
			AND pmt.privmsgs_text_id = pm.privmsgs_id
			$pm_sql_user
			AND u.user_id = pm.privmsgs_from_userid
			AND u2.user_id = pm.privmsgs_to_userid";
    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not query private message post information');
    }

    //
    // Did the query return any data?
    //
    if (!($privmsg = DB()->sql_fetchrow($result))) {
        redirect(PM_URL . "?folder=$folder");
    }

    $privmsg_id = $privmsg['privmsgs_id'];

    //
    // Is this a new message in the inbox? If it is then save
    // a copy in the posters sent box
    //
    if (($privmsg['privmsgs_type'] == PRIVMSGS_NEW_MAIL || $privmsg['privmsgs_type'] == PRIVMSGS_UNREAD_MAIL) && $folder == 'inbox') {
        // Update appropriate counter
        switch ($privmsg['privmsgs_type']) {
            case PRIVMSGS_NEW_MAIL:
                $sql = "user_new_privmsg = IF(user_new_privmsg, user_new_privmsg - 1, 0)";
                break;
            case PRIVMSGS_UNREAD_MAIL:
                $sql = "user_unread_privmsg = IF(user_unread_privmsg, user_unread_privmsg - 1, 0)";
                break;
        }

        $sql = "UPDATE " . BB_USERS . " SET $sql WHERE user_id = " . userdata('user_id');
        if (!DB()->sql_query($sql)) {
            bb_die('Could not update private message read status for user');
        }
        if (DB()->affected_rows()) {
            \TorrentPier\Sessions::cache_rm_userdata(userdata());
        }

        $sql = "UPDATE " . BB_PRIVMSGS . "
			SET privmsgs_type = " . PRIVMSGS_READ_MAIL . "
			WHERE privmsgs_id = " . $privmsg['privmsgs_id'];
        if (!DB()->sql_query($sql)) {
            bb_die('Could not update private message read status');
        }

        // Check to see if the poster has a 'full' sent box
        $sql = "SELECT COUNT(privmsgs_id) AS sent_items, MIN(privmsgs_date) AS oldest_post_time
			FROM " . BB_PRIVMSGS . "
			WHERE privmsgs_type = " . PRIVMSGS_SENT_MAIL . "
				AND privmsgs_from_userid = " . $privmsg['privmsgs_from_userid'];
        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not obtain sent message info for sender');
        }

        if ($sent_info = DB()->sql_fetchrow($result)) {
            if (config()->get('max_sentbox_privmsgs') && $sent_info['sent_items'] >= config()->get('max_sentbox_privmsgs')) {
                $sql = "SELECT privmsgs_id FROM " . BB_PRIVMSGS . "
					WHERE privmsgs_type = " . PRIVMSGS_SENT_MAIL . "
						AND privmsgs_date = " . $sent_info['oldest_post_time'] . "
						AND privmsgs_from_userid = " . $privmsg['privmsgs_from_userid'];
                if (!$result = DB()->sql_query($sql)) {
                    bb_die('Could not find oldest privmsgs');
                }
                $old_privmsgs_id = DB()->sql_fetchrow($result);
                $old_privmsgs_id = (int)$old_privmsgs_id['privmsgs_id'];

                $sql = "DELETE FROM " . BB_PRIVMSGS . " WHERE privmsgs_id = $old_privmsgs_id";
                if (!DB()->sql_query($sql)) {
                    bb_die('Could not delete oldest privmsgs (sent)');
                }

                $sql = "DELETE FROM " . BB_PRIVMSGS_TEXT . " WHERE privmsgs_text_id = $old_privmsgs_id";
                if (!DB()->sql_query($sql)) {
                    bb_die('Could not delete oldest privmsgs text (sent)');
                }
            }
        }

        //
        // This makes a copy of the post and stores it as a sent message from the sender. Perhaps
        // not the most DB friendly way but a lot easier to manage, besides the admin will be able to
        // set limits on numbers of storable posts for users ... hopefully!
        //
        $sql = "INSERT INTO " . BB_PRIVMSGS . " (privmsgs_type, privmsgs_subject, privmsgs_from_userid, privmsgs_to_userid, privmsgs_date, privmsgs_ip)
			VALUES (" . PRIVMSGS_SENT_MAIL . ", '" . DB()->escape($privmsg['privmsgs_subject']) . "', " . $privmsg['privmsgs_from_userid'] . ", " . $privmsg['privmsgs_to_userid'] . ", " . $privmsg['privmsgs_date'] . ", '" . $privmsg['privmsgs_ip'] . "')";
        if (!DB()->sql_query($sql)) {
            bb_die('Could not insert private message sent info');
        }

        $privmsg_sent_id = DB()->sql_nextid();

        $sql = "INSERT INTO " . BB_PRIVMSGS_TEXT . " (privmsgs_text_id, privmsgs_text)
			VALUES ($privmsg_sent_id, '" . DB()->escape($privmsg['privmsgs_text']) . "')";
        if (!DB()->sql_query($sql)) {
            bb_die('Could not insert private message sent text');
        }
    }

    //
    // Pick a folder, any folder, so long as it's one below ...
    //
    $post_urls = [
        'post' => PM_URL . '?mode=post',
        'reply' => PM_URL . '?mode=reply&amp;' . POST_POST_URL . "=$privmsg_id",
        'quote' => PM_URL . '?mode=quote&amp;' . POST_POST_URL . "=$privmsg_id",
        'edit' => PM_URL . '?mode=edit&amp;' . POST_POST_URL . "=$privmsg_id"
    ];
    $post_icons = [
        'post_img' => '<a href="' . $post_urls['post'] . '" class="btn-post-topic">' . __('POST_NEW_PM') . '</a>',
        'post' => '<a href="' . $post_urls['post'] . '">' . __('POST_NEW_PM') . '</a>',
        'reply_img' => '<a href="' . $post_urls['reply'] . '" class="btn-post-topic">' . __('POST_REPLY_PM') . '</a>',
        'reply' => '<a href="' . $post_urls['reply'] . '">' . __('POST_REPLY_PM') . '</a>',
        'quote_img' => '<a href="' . $post_urls['quote'] . '" class="btn-post-topic">' . __('POST_QUOTE_PM') . '</a>',
        'quote' => '<a href="' . $post_urls['quote'] . '">' . __('POST_QUOTE_PM') . '</a>',
        'edit_img' => '<a href="' . $post_urls['edit'] . '" class="btn-post-topic">' . __('EDIT_PM') . '</a>',
        'edit' => '<a href="' . $post_urls['edit'] . '">' . __('EDIT_PM') . '</a>'
    ];

    if ($folder == 'inbox') {
        $post_img = $post_icons['post_img'];
        $reply_img = $post_icons['reply_img'];
        $quote_img = $post_icons['quote_img'];
        $edit_img = '';
        $post = $post_icons['post'];
        $reply = $post_icons['reply'];
        $quote = $post_icons['quote'];
        $edit = '';
        $l_box_name = __('INBOX');
    } elseif ($folder == 'outbox') {
        $post_img = $post_icons['post_img'];
        $reply_img = '';
        $quote_img = '';
        $edit_img = $post_icons['edit_img'];
        $post = $post_icons['post'];
        $reply = '';
        $quote = '';
        $edit = $post_icons['edit'];
        $l_box_name = __('OUTBOX');
    } elseif ($folder == 'savebox') {
        if ($privmsg['privmsgs_type'] == PRIVMSGS_SAVED_IN_MAIL) {
            $post_img = $post_icons['post_img'];
            $reply_img = $post_icons['reply_img'];
            $quote_img = $post_icons['quote_img'];
            $edit_img = '';
            $post = $post_icons['post'];
            $reply = $post_icons['reply'];
            $quote = $post_icons['quote'];
            $edit = '';
        } else {
            $post_img = $post_icons['post_img'];
            $reply_img = '';
            $quote_img = '';
            $edit_img = '';
            $post = $post_icons['post'];
            $reply = '';
            $quote = '';
            $edit = '';
        }
        $l_box_name = __('SAVED');
    } elseif ($folder == 'sentbox') {
        $post_img = $post_icons['post_img'];
        $reply_img = '';
        $quote_img = '';
        $edit_img = '';
        $post = $post_icons['post'];
        $reply = '';
        $quote = '';
        $edit = '';
        $l_box_name = __('SENT');
    }

    $s_hidden_fields = '<input type="hidden" name="mark[]" value="' . $privmsgs_id . '" />';

    $page_title = __('READ_PM');

    //
    // Load templates
    //
    template()->set_filenames(['body' => 'privmsgs_read.tpl']);

    template()->assign_vars([
        'INBOX' => $inbox_url,

        'POST_PM_IMG' => $post_img,
        'REPLY_PM_IMG' => $reply_img,
        'EDIT_PM_IMG' => $edit_img,
        'QUOTE_PM_IMG' => $quote_img,
        'POST_PM' => $post,
        'REPLY_PM' => $reply,
        'EDIT_PM' => $edit,
        'QUOTE_PM' => $quote,

        'SENTBOX' => $sentbox_url,
        'OUTBOX' => $outbox_url,
        'SAVEBOX' => $savebox_url,
        'BOX_NAME' => $l_box_name,

        'S_PRIVMSGS_ACTION' => PM_URL . "?folder=$folder",
        'S_HIDDEN_FIELDS' => $s_hidden_fields
    ]);

    $username_from = $privmsg['username'];
    $user_id_from = $privmsg['user_id'];
    $username_to = $privmsg['to_username'];
    $user_id_to = $privmsg['to_user_id'];

    $post_date = bb_date($privmsg['privmsgs_date']);

    $temp_url = "profile?mode=viewprofile&amp;" . POST_USERS_URL . '=' . $user_id_from;
    $profile = '<a href="' . $temp_url . '">' . __('READ_PROFILE') . '</a>';

    $temp_url = "search?search_author=1&amp;uid=$user_id_from";
    $search = '<a href="' . $temp_url . '">' . sprintf(__('SEARCH_USER_POSTS'), $username_from) . '</a>';

    //
    // Processing of post
    //
    $post_subject = htmlCHR($privmsg['privmsgs_subject']);
    $private_message = $privmsg['privmsgs_text'];
    $post_subject = censor()->censorString($post_subject);
    $private_message = censor()->censorString($private_message);
    $private_message = bbcode2html($private_message);

    //
    // Dump it to the templating engine
    //
    template()->assign_vars([
        'TO_USER' => profile_url(['username' => $username_to, 'user_id' => $user_id_to, 'user_rank' => $privmsg['to_user_rank']]),
        'FROM_USER' => profile_url($privmsg),

        'QR_SUBJECT' => (!preg_match('/^Re:/', $post_subject) ? 'Re: ' : '') . $post_subject,
        'MESSAGE_TO' => $username_to,
        'MESSAGE_FROM' => $username_from,
        'RANK_IMAGE' => (@$rank_image) ? $rank_image : '',
        'POSTER_JOINED' => (@$poster_joined) ? $poster_joined : '',
        'POSTER_POSTS' => (@$poster_posts) ? $poster_posts : '',
        'POSTER_FROM' => (@$poster_from) ? $poster_from : '',
        'POST_SUBJECT' => $post_subject,
        'POST_DATE' => $post_date,
        'PM_MESSAGE' => $private_message,
        'PROFILE' => $profile,
        'SEARCH' => $search
    ]);
} elseif (($delete && $mark_list) || $delete_all) {
    if (isset($mark_list) && !is_array($mark_list)) {
        // Set to empty array instead of '0' if nothing is selected.
        $mark_list = [];
    }

    if (!$confirmed) {
        $delete = isset($_POST['delete']) ? 'delete' : 'deleteall';

        $hidden_fields = [
            'mode' => $mode,
            $delete => 1
        ];
        foreach ($mark_list as $pm_id) {
            $hidden_fields['mark'][] = (int)$pm_id;
        }

        print_confirmation([
            'QUESTION' => (count($mark_list) == 1) ? __('CONFIRM_DELETE_PM') : __('CONFIRM_DELETE_PMS'),
            'FORM_ACTION' => PM_URL . "?folder=$folder",
            'HIDDEN_FIELDS' => build_hidden_fields($hidden_fields)
        ]);
    } elseif ($confirmed) {
        $delete_sql_id = '';

        if (!$delete_all) {
            for ($i = 0, $iMax = count($mark_list); $i < $iMax; $i++) {
                $delete_sql_id .= (($delete_sql_id != '') ? ', ' : '') . (int)$mark_list[$i];
            }
            $delete_sql_id = "AND privmsgs_id IN ($delete_sql_id)";
        }

        switch ($folder) {
            case 'inbox':
                $delete_type = "privmsgs_to_userid = " . userdata('user_id') . " AND (
				privmsgs_type = " . PRIVMSGS_READ_MAIL . " OR privmsgs_type = " . PRIVMSGS_NEW_MAIL . " OR privmsgs_type = " . PRIVMSGS_UNREAD_MAIL . " )";
                break;

            case 'outbox':
                $delete_type = "privmsgs_from_userid = " . userdata('user_id') . " AND ( privmsgs_type = " . PRIVMSGS_NEW_MAIL . " OR privmsgs_type = " . PRIVMSGS_UNREAD_MAIL . " )";
                break;

            case 'sentbox':
                $delete_type = "privmsgs_from_userid = " . userdata('user_id') . " AND privmsgs_type = " . PRIVMSGS_SENT_MAIL;
                break;

            case 'savebox':
                $delete_type = "( ( privmsgs_from_userid = " . userdata('user_id') . "
					AND privmsgs_type = " . PRIVMSGS_SAVED_OUT_MAIL . " )
				OR ( privmsgs_to_userid = " . userdata('user_id') . "
					AND privmsgs_type = " . PRIVMSGS_SAVED_IN_MAIL . " ) )";
                break;
        }

        $sql = "SELECT privmsgs_id FROM " . BB_PRIVMSGS . " WHERE $delete_type $delete_sql_id";
        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not obtain id list to delete messages');
        }

        $mark_list = [];
        while ($row = DB()->sql_fetchrow($result)) {
            $mark_list[] = $row['privmsgs_id'];
        }

        unset($delete_type);

        if (count($mark_list)) {
            $delete_sql_id = '';
            for ($i = 0, $iMax = count($mark_list); $i < $iMax; $i++) {
                $delete_sql_id .= (($delete_sql_id != '') ? ', ' : '') . (int)$mark_list[$i];
            }

            if ($folder == 'inbox' || $folder == 'outbox') {
                switch ($folder) {
                    case 'inbox':
                        $sql = "privmsgs_to_userid = " . userdata('user_id');
                        break;
                    case 'outbox':
                        $sql = "privmsgs_from_userid = " . userdata('user_id');
                        break;
                }

                // Get information relevant to new or unread mail
                // so we can adjust users counters appropriately
                $sql = "SELECT privmsgs_to_userid, privmsgs_type
					FROM " . BB_PRIVMSGS . "
					WHERE privmsgs_id IN ($delete_sql_id)
						AND $sql
						AND privmsgs_type IN (" . PRIVMSGS_NEW_MAIL . ", " . PRIVMSGS_UNREAD_MAIL . ")";
                if (!($result = DB()->sql_query($sql))) {
                    bb_die('Could not obtain user id list for outbox messages');
                }

                if ($row = DB()->sql_fetchrow($result)) {
                    $update_users = $update_list = [];

                    do {
                        switch ($row['privmsgs_type']) {
                            case PRIVMSGS_NEW_MAIL:
                                @$update_users['new'][$row['privmsgs_to_userid']]++;
                                break;

                            case PRIVMSGS_UNREAD_MAIL:
                                @$update_users['unread'][$row['privmsgs_to_userid']]++;
                                break;
                        }
                    } while ($row = DB()->sql_fetchrow($result));

                    if (count($update_users)) {
                        foreach ($update_users as $type => $users) {
                            foreach ($users as $user_id => $dec) {
                                $update_list[$type][$dec][] = $user_id;
                            }
                        }
                        unset($update_users);

                        foreach ($update_list as $type => $dec_ary) {
                            switch ($type) {
                                case 'new':
                                    $type = "user_new_privmsg";
                                    break;

                                case 'unread':
                                    $type = "user_unread_privmsg";
                                    break;
                            }

                            foreach ($dec_ary as $dec => $user_ary) {
                                $user_ids = implode(', ', $user_ary);

                                $sql = "UPDATE " . BB_USERS . "
									SET $type = $type - $dec
									WHERE user_id IN ($user_ids)";
                                if (!DB()->sql_query($sql)) {
                                    bb_die('Could not update user pm counters');
                                }
                            }
                        }
                        unset($update_list);
                    }
                }
                DB()->sql_freeresult($result);
            }

            // Delete the messages
            $delete_text_sql = "DELETE FROM " . BB_PRIVMSGS_TEXT . "
				WHERE privmsgs_text_id IN ($delete_sql_id)";
            $delete_sql = "DELETE FROM " . BB_PRIVMSGS . "
				WHERE privmsgs_id IN ($delete_sql_id)
					AND ";

            switch ($folder) {
                case 'inbox':
                    $delete_sql .= "privmsgs_to_userid = " . userdata('user_id') . " AND (
						privmsgs_type = " . PRIVMSGS_READ_MAIL . " OR privmsgs_type = " . PRIVMSGS_NEW_MAIL . " OR privmsgs_type = " . PRIVMSGS_UNREAD_MAIL . " )";
                    break;

                case 'outbox':
                    $delete_sql .= "privmsgs_from_userid = " . userdata('user_id') . " AND (
						privmsgs_type = " . PRIVMSGS_NEW_MAIL . " OR privmsgs_type = " . PRIVMSGS_UNREAD_MAIL . " )";
                    break;

                case 'sentbox':
                    $delete_sql .= "privmsgs_from_userid = " . userdata('user_id') . " AND privmsgs_type = " . PRIVMSGS_SENT_MAIL;
                    break;

                case 'savebox':
                    $delete_sql .= "( ( privmsgs_from_userid = " . userdata('user_id') . "
						AND privmsgs_type = " . PRIVMSGS_SAVED_OUT_MAIL . " )
					OR ( privmsgs_to_userid = " . userdata('user_id') . "
						AND privmsgs_type = " . PRIVMSGS_SAVED_IN_MAIL . " ) )";
                    break;
            }

            if (!DB()->sql_query($delete_sql)) {
                bb_die('Could not delete private message info');
            }

            if (!DB()->sql_query($delete_text_sql)) {
                bb_die('Could not delete private message text');
            }

            pm_die(__('DELETE_POSTS_SUCCESFULLY'));
        } else {
            pm_die(__('NONE_SELECTED'));
        }
    }
} elseif ($save && $mark_list && $folder != 'savebox' && $folder != 'outbox') {
    if (count($mark_list)) {
        // See if recipient is at their savebox limit
        $sql = "SELECT COUNT(privmsgs_id) AS savebox_items, MIN(privmsgs_date) AS oldest_post_time
			FROM " . BB_PRIVMSGS . "
			WHERE ( ( privmsgs_to_userid = " . userdata('user_id') . "
					AND privmsgs_type = " . PRIVMSGS_SAVED_IN_MAIL . " )
				OR ( privmsgs_from_userid = " . userdata('user_id') . "
					AND privmsgs_type = " . PRIVMSGS_SAVED_OUT_MAIL . ") )";
        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not obtain sent message info for sender');
        }

        if ($saved_info = DB()->sql_fetchrow($result)) {
            if (config()->get('max_savebox_privmsgs') && $saved_info['savebox_items'] >= config()->get('max_savebox_privmsgs')) {
                $sql = "SELECT privmsgs_id FROM " . BB_PRIVMSGS . "
					WHERE ( ( privmsgs_to_userid = " . userdata('user_id') . "
								AND privmsgs_type = " . PRIVMSGS_SAVED_IN_MAIL . " )
							OR ( privmsgs_from_userid = " . userdata('user_id') . "
								AND privmsgs_type = " . PRIVMSGS_SAVED_OUT_MAIL . ") )
						AND privmsgs_date = " . $saved_info['oldest_post_time'];
                if (!$result = DB()->sql_query($sql)) {
                    bb_die('Could not find oldest privmsgs (save)');
                }
                $old_privmsgs_id = DB()->sql_fetchrow($result);
                $old_privmsgs_id = (int)$old_privmsgs_id['privmsgs_id'];

                $sql = "DELETE FROM " . BB_PRIVMSGS . " WHERE privmsgs_id = $old_privmsgs_id";
                if (!DB()->sql_query($sql)) {
                    bb_die('Could not delete oldest privmsgs (save)');
                }

                $sql = "DELETE FROM " . BB_PRIVMSGS_TEXT . " WHERE privmsgs_text_id = $old_privmsgs_id";
                if (!DB()->sql_query($sql)) {
                    bb_die('Could not delete oldest privmsgs text (save)');
                }
            }
        }

        $saved_sql_id = '';
        for ($i = 0, $iMax = count($mark_list); $i < $iMax; $i++) {
            $saved_sql_id .= (($saved_sql_id != '') ? ', ' : '') . (int)$mark_list[$i];
        }

        // Process request
        $saved_sql = "UPDATE " . BB_PRIVMSGS;

        // Decrement read/new counters if appropriate
        if ($folder == 'inbox' || $folder == 'outbox') {
            switch ($folder) {
                case 'inbox':
                    $sql = "privmsgs_to_userid = " . userdata('user_id');
                    break;
                case 'outbox':
                    $sql = "privmsgs_from_userid = " . userdata('user_id');
                    break;
            }

            // Get information relevant to new or unread mail
            // so we can adjust users counters appropriately
            $sql = "SELECT privmsgs_to_userid, privmsgs_type
				FROM " . BB_PRIVMSGS . "
				WHERE privmsgs_id IN ($saved_sql_id)
					AND $sql
					AND privmsgs_type IN (" . PRIVMSGS_NEW_MAIL . ", " . PRIVMSGS_UNREAD_MAIL . ")";
            if (!($result = DB()->sql_query($sql))) {
                bb_die('Could not obtain user id list for outbox messages');
            }

            if ($row = DB()->sql_fetchrow($result)) {
                $update_users = $update_list = [];

                do {
                    switch ($row['privmsgs_type']) {
                        case PRIVMSGS_NEW_MAIL:
                            ($update_users['new'][$row['privmsgs_to_userid']] ??= 0) + 1;
                            break;

                        case PRIVMSGS_UNREAD_MAIL:
                            ($update_users['unread'][$row['privmsgs_to_userid']] ??= 0) + 1;
                            break;
                    }
                } while ($row = DB()->sql_fetchrow($result));

                if (count($update_users)) {
                    foreach ($update_users as $type => $users) {
                        foreach ($users as $user_id => $dec) {
                            $update_list[$type][$dec][] = $user_id;
                        }
                    }
                    unset($update_users);

                    foreach ($update_list as $type => $dec_ary) {
                        switch ($type) {
                            case 'new':
                                $type = "user_new_privmsg";
                                break;

                            case 'unread':
                                $type = "user_unread_privmsg";
                                break;
                        }

                        foreach ($dec_ary as $dec => $user_ary) {
                            $user_ids = implode(', ', $user_ary);

                            $sql = "UPDATE " . BB_USERS . " SET $type = $type - $dec WHERE user_id IN ($user_ids)";
                            if (!DB()->sql_query($sql)) {
                                bb_die('Could not update user pm counters');
                            }
                        }
                    }
                    unset($update_list);
                }
            }
            DB()->sql_freeresult($result);
        }

        switch ($folder) {
            case 'inbox':
                $saved_sql .= " SET privmsgs_type = " . PRIVMSGS_SAVED_IN_MAIL . "
					WHERE privmsgs_to_userid = " . userdata('user_id') . "
						AND ( privmsgs_type = " . PRIVMSGS_READ_MAIL . "
							OR privmsgs_type = " . PRIVMSGS_NEW_MAIL . "
							OR privmsgs_type = " . PRIVMSGS_UNREAD_MAIL . ")";
                break;

            case 'outbox':
                $saved_sql .= " SET privmsgs_type = " . PRIVMSGS_SAVED_OUT_MAIL . "
					WHERE privmsgs_from_userid = " . userdata('user_id') . "
						AND ( privmsgs_type = " . PRIVMSGS_NEW_MAIL . "
							OR privmsgs_type = " . PRIVMSGS_UNREAD_MAIL . " ) ";
                break;

            case 'sentbox':
                $saved_sql .= " SET privmsgs_type = " . PRIVMSGS_SAVED_OUT_MAIL . "
					WHERE privmsgs_from_userid = " . userdata('user_id') . "
						AND privmsgs_type = " . PRIVMSGS_SENT_MAIL;
                break;
        }

        $saved_sql .= " AND privmsgs_id IN ($saved_sql_id)";

        if (!DB()->sql_query($saved_sql)) {
            bb_die('Could not save private messages');
        }

        redirect(PM_URL . "?folder=savebox");
    }
} elseif ($submit || $refresh || $mode != '') {
    if (IS_USER && $submit && $mode != 'edit') {
        // Flood control
        $sql = "SELECT MAX(privmsgs_date) AS last_post_time FROM " . BB_PRIVMSGS . " WHERE privmsgs_from_userid = " . userdata('user_id');
        if ($result = DB()->sql_query($sql)) {
            $db_row = DB()->sql_fetchrow($result);

            $last_post_time = $db_row['last_post_time'];
            $current_time = TIMENOW;

            if (($current_time - $last_post_time) < config()->get('flood_interval')) {
                bb_die(__('FLOOD_ERROR'));
            }
        }
    }

    if ($submit && $mode == 'edit') {
        $sql = 'SELECT privmsgs_from_userid
			FROM ' . BB_PRIVMSGS . '
			WHERE privmsgs_id = ' . (int)$privmsg_id . '
				AND privmsgs_from_userid = ' . userdata('user_id');

        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not obtain message details');
        }

        if (!($row = DB()->sql_fetchrow($result))) {
            bb_die(__('NO_SUCH_POST'));
        }
        DB()->sql_freeresult($result);

        unset($row);
    }

    if ($submit) {
        if (!empty($_POST['username'])) {
            $to_userdata = get_userdata($_POST['username'], true);

            if (!$to_userdata || $to_userdata['user_id'] == GUEST_UID) {
                $error = true;
                $error_msg = __('NO_SUCH_USER');
            }
        } else {
            $error = true;
            $error_msg .= ((!empty($error_msg)) ? '<br />' : '') . __('NO_TO_USER');
        }

        $privmsg_subject = htmlCHR($_POST['subject']);
        if (empty($privmsg_subject)) {
            $error = true;
            $error_msg .= ((!empty($error_msg)) ? '<br />' : '') . __('EMPTY_SUBJECT');
        }

        if (!empty($_POST['message'])) {
            if (!$error) {
                $privmsg_message = prepare_message($_POST['message']);
            }
        } else {
            $error = true;
            $error_msg .= ((!empty($error_msg)) ? '<br />' : '') . __('EMPTY_MESSAGE');
        }

        // Check smilies limit
        if (config()->get('max_smilies_pm')) {
            $count_smilies = substr_count(bbcode2html($privmsg_message), '<img class="smile" src="' . config()->get('smilies_path'));
            if ($count_smilies > config()->get('max_smilies_pm')) {
                $error = true;
                $error_msg .= ((!empty($error_msg)) ? '<br />' : '') . sprintf(__('MAX_SMILIES_PER_POST'), config()->get('max_smilies_pm'));
            }
        }
    }

    if ($submit && !$error) {
        //
        // Has admin prevented user from sending PM's?
        //
        if (bf(userdata('user_opt'), 'user_opt', 'dis_pm')) {
            bb_die(__('CANNOT_SEND_PRIVMSG'));
        }

        $msg_time = TIMENOW;

        if ($mode != 'edit') {
            // See if recipient is at their inbox limit
            $sql = "SELECT COUNT(privmsgs_id) AS inbox_items, MIN(privmsgs_date) AS oldest_post_time
				FROM " . BB_PRIVMSGS . "
				WHERE ( privmsgs_type = " . PRIVMSGS_NEW_MAIL . "
						OR privmsgs_type = " . PRIVMSGS_READ_MAIL . "
						OR privmsgs_type = " . PRIVMSGS_UNREAD_MAIL . " )
					AND privmsgs_to_userid = " . $to_userdata['user_id'];
            if (!($result = DB()->sql_query($sql))) {
                bb_die(__('NO_SUCH_USER'));
            }

            if ($inbox_info = DB()->sql_fetchrow($result)) {
                if (config()->get('max_inbox_privmsgs') && $inbox_info['inbox_items'] >= config()->get('max_inbox_privmsgs')) {
                    $sql = "SELECT privmsgs_id FROM " . BB_PRIVMSGS . "
						WHERE ( privmsgs_type = " . PRIVMSGS_NEW_MAIL . "
								OR privmsgs_type = " . PRIVMSGS_READ_MAIL . "
								OR privmsgs_type = " . PRIVMSGS_UNREAD_MAIL . "  )
							AND privmsgs_date = " . $inbox_info['oldest_post_time'] . "
							AND privmsgs_to_userid = " . $to_userdata['user_id'];
                    if (!$result = DB()->sql_query($sql)) {
                        bb_die('Could not find oldest privmsgs (inbox)');
                    }
                    $old_privmsgs_id = DB()->sql_fetchrow($result);
                    $old_privmsgs_id = (int)$old_privmsgs_id['privmsgs_id'];

                    $sql = "DELETE FROM " . BB_PRIVMSGS . " WHERE privmsgs_id = $old_privmsgs_id";
                    if (!DB()->sql_query($sql)) {
                        bb_die('Could not delete oldest privmsgs (inbox)');
                    }

                    $sql = "DELETE FROM " . BB_PRIVMSGS_TEXT . " WHERE privmsgs_text_id = $old_privmsgs_id";
                    if (!DB()->sql_query($sql)) {
                        bb_die('Could not delete oldest privmsgs text (inbox)');
                    }
                }
            }

            $sql_info = "INSERT INTO " . BB_PRIVMSGS . " (privmsgs_type, privmsgs_subject, privmsgs_from_userid, privmsgs_to_userid, privmsgs_date, privmsgs_ip)
				VALUES (" . PRIVMSGS_NEW_MAIL . ", '" . DB()->escape($privmsg_subject) . "', " . userdata('user_id') . ", " . $to_userdata['user_id'] . ", $msg_time, '" . USER_IP . "')";
        } else {
            $sql_info = "UPDATE " . BB_PRIVMSGS . "
				SET privmsgs_type = " . PRIVMSGS_NEW_MAIL . ", privmsgs_subject = '" . DB()->escape($privmsg_subject) . "', privmsgs_from_userid = " . userdata('user_id') . ", privmsgs_to_userid = " . $to_userdata['user_id'] . ", privmsgs_date = $msg_time, privmsgs_ip = '" . USER_IP . "'
				WHERE privmsgs_id = $privmsg_id";
        }

        if (!($result = DB()->sql_query($sql_info))) {
            bb_die('Could not insert / update private message sent info');
        }

        if ($mode != 'edit') {
            $privmsg_sent_id = DB()->sql_nextid();

            $sql = "INSERT INTO " . BB_PRIVMSGS_TEXT . " (privmsgs_text_id, privmsgs_text)
				VALUES ($privmsg_sent_id, '" . DB()->escape($privmsg_message) . "')";
        } else {
            $sql = "UPDATE " . BB_PRIVMSGS_TEXT . "
				SET privmsgs_text = '" . DB()->escape($privmsg_message) . "'
				WHERE privmsgs_text_id = $privmsg_id";
        }

        if (!DB()->sql_query($sql)) {
            bb_die('Could not insert / update private message sent text');
        }

        if ($mode != 'edit') {
            $timenow = TIMENOW;
            // Add to the users new pm counter
            $sql = "UPDATE " . BB_USERS . " SET
					user_new_privmsg = user_new_privmsg + 1,
					user_last_privmsg = $timenow,
					user_newest_pm_id = $privmsg_sent_id
				WHERE user_id = {$to_userdata['user_id']}
				LIMIT 1";

            if (!$status = DB()->sql_query($sql)) {
                bb_die('Could not update private message new / read status for user');
            }

            \TorrentPier\Sessions::cache_rm_user_sessions($to_userdata['user_id']);

            if (bf($to_userdata['user_opt'], 'user_opt', 'user_notify_pm') && $to_userdata['user_active'] && config()->get('pm_notify_enabled')) {
                // Sending email
                $emailer = new TorrentPier\Emailer();

                $emailer->set_to($to_userdata['user_email'], $to_userdata['username']);
                $emailer->set_subject(__('EMAILER_SUBJECT')['PRIVMSG_NOTIFY']);

                $emailer->set_template('privmsg_notify', $to_userdata['user_lang']);
                $emailer->assign_vars([
                    'USERNAME' => html_entity_decode($to_username),
                    'NAME_FROM' => userdata('username'),
                    'MSG_SUBJECT' => html_entity_decode($privmsg_subject),
                    'U_INBOX' => make_url(PM_URL . "?folder=inbox&mode=read&" . POST_POST_URL . "=$privmsg_sent_id"),
                ]);

                $emailer->send();
            }
        }

        pm_die(__('MESSAGE_SENT'));
    } elseif ($preview || $refresh || $error) {
        //
        // If we're previewing or refreshing then obtain the data
        // passed to the script, process it a little, do some checks
        // where neccessary, etc.
        //
        $to_username = (isset($_POST['username'])) ? clean_username($_POST['username']) : '';

        $privmsg_subject = (isset($_POST['subject'])) ? clean_title($_POST['subject']) : '';
        $privmsg_message = (isset($_POST['message'])) ? prepare_message($_POST['message']) : '';

        //
        // Do mode specific things
        //
        if ($mode == 'edit') {
            $page_title = __('EDIT_PM');

            $sql = "SELECT u.user_id
				FROM " . BB_PRIVMSGS . " pm, " . BB_USERS . " u
				WHERE pm.privmsgs_id = $privmsg_id
					AND u.user_id = pm.privmsgs_from_userid";
            if (!($result = DB()->sql_query($sql))) {
                bb_die('Could not obtain post and post text');
            }

            if ($postrow = DB()->sql_fetchrow($result)) {
                if (userdata('user_id') != $postrow['user_id']) {
                    bb_die(__('EDIT_OWN_POSTS'));
                }
            }
        }
    } else {
        if (!$privmsg_id && ($mode == 'reply' || $mode == 'edit' || $mode == 'quote')) {
            bb_die(__('NO_POST_ID'));
        }

        if (!empty($_GET[POST_USERS_URL])) {
            $user_id = (int)$_GET[POST_USERS_URL];

            $sql = "SELECT username FROM " . BB_USERS . " WHERE user_id = $user_id AND user_id <> " . GUEST_UID;
            if (!($result = DB()->sql_query($sql))) {
                $error = true;
                $error_msg = __('NO_SUCH_USER');
            }

            if ($row = DB()->sql_fetchrow($result)) {
                $to_username = $row['username'];
            }
        } elseif ($mode == 'edit') {
            $sql = "SELECT pm.*, pmt.privmsgs_text, u.username, u.user_id
				FROM " . BB_PRIVMSGS . " pm, " . BB_PRIVMSGS_TEXT . " pmt, " . BB_USERS . " u
				WHERE pm.privmsgs_id = $privmsg_id
					AND pmt.privmsgs_text_id = pm.privmsgs_id
					AND pm.privmsgs_from_userid = " . userdata('user_id') . "
					AND ( pm.privmsgs_type = " . PRIVMSGS_NEW_MAIL . "
						OR pm.privmsgs_type = " . PRIVMSGS_UNREAD_MAIL . " )
					AND u.user_id = pm.privmsgs_to_userid";
            if (!($result = DB()->sql_query($sql))) {
                bb_die('Could not obtain private message for editing #1');
            }

            if (!($privmsg = DB()->sql_fetchrow($result))) {
                redirect(PM_URL . "?folder=$folder");
            }

            $privmsg_subject = $privmsg['privmsgs_subject'];
            $privmsg_message = $privmsg['privmsgs_text'];

            $to_username = $privmsg['username'];
            $to_userid = $privmsg['user_id'];
        } elseif ($mode == 'reply' || $mode == 'quote') {
            $sql = "SELECT pm.privmsgs_subject, pm.privmsgs_date, pmt.privmsgs_text, u.username, u.user_id
				FROM " . BB_PRIVMSGS . " pm, " . BB_PRIVMSGS_TEXT . " pmt, " . BB_USERS . " u
				WHERE pm.privmsgs_id = $privmsg_id
					AND pmt.privmsgs_text_id = pm.privmsgs_id
					AND pm.privmsgs_to_userid = " . userdata('user_id') . "
					AND u.user_id = pm.privmsgs_from_userid";
            if (!($result = DB()->sql_query($sql))) {
                bb_die('Could not obtain private message for editing #2');
            }

            if (!($privmsg = DB()->sql_fetchrow($result))) {
                redirect(PM_URL . "?folder=$folder");
            }

            $privmsg_subject = ((!preg_match('/^Re:/', $privmsg['privmsgs_subject'])) ? 'Re: ' : '') . $privmsg['privmsgs_subject'];

            $to_username = $privmsg['username'];
            $to_userid = $privmsg['user_id'];

            if ($mode == 'quote') {
                $privmsg_message = $privmsg['privmsgs_text'];
                $msg_date = bb_date($privmsg['privmsgs_date']);
                $privmsg_message = '[quote="' . $to_username . '"]' . $privmsg_message . '[/quote]';
            }
        } else {
            $privmsg_subject = $privmsg_message = $to_username = '';
        }
    }

    //
    // Has admin prevented user from sending PM's?
    //
    if (bf(userdata('user_opt'), 'user_opt', 'dis_pm') && $mode != 'edit') {
        $message = (__('CANNOT_SEND_PRIVMSG'));
    }

    //
    // Start output, first preview, then errors then post form
    //
    if ($mode == 'post') {
        $page_title = __('POST_NEW_PM');
    } elseif ($mode == 'reply') {
        $page_title = __('POST_REPLY_PM');
    } elseif ($mode == 'quote') {
        $page_title = __('POST_QUOTE_PM');
    } elseif ($mode == 'edit') {
        $page_title = __('EDIT_PM');
    }

    if ($preview && !$error) {
        $preview_message = bbcode2html($privmsg_message);
        $preview_subject = censor()->censorString($privmsg_subject);
        $preview_message = censor()->censorString($preview_message);

        $s_hidden_fields = '<input type="hidden" name="folder" value="' . $folder . '" />';
        $s_hidden_fields .= '<input type="hidden" name="mode" value="' . $mode . '" />';

        if (isset($privmsg_id)) {
            $s_hidden_fields .= '<input type="hidden" name="' . POST_POST_URL . '" value="' . $privmsg_id . '" />';
        }

        template()->assign_vars([
            'TPL_PREVIEW_POST' => true,
            'TOPIC_TITLE' => $preview_subject,
            'POST_SUBJECT' => $preview_subject,
            'MESSAGE_TO' => $to_username,
            'MESSAGE_FROM' => userdata('username'),
            'POST_DATE' => bb_date(TIMENOW),
            'PREVIEW_MSG' => $preview_message,

            'S_HIDDEN_FIELDS' => $s_hidden_fields
        ]);
    }

    //
    // Start error handling
    //
    if ($error) {
        template()->assign_vars(['ERROR_MESSAGE' => $error_msg]);
    }

    //
    // Load templates
    //
    template()->set_filenames(['body' => 'posting.tpl']);

    //
    // Enable extensions in posting_body
    //
    template()->assign_block_vars('switch_privmsg', []);
    template()->assign_var('POSTING_USERNAME');

    //
    // Assign posting title & hidden fields
    //
    $post_a = false;
    switch ($mode) {
        case 'post':
            $post_a = __('SEND_A_NEW_MESSAGE');
            break;
        case 'quote':
        case 'reply':
            $post_a = __('SEND_A_REPLY');
            break;
        case 'edit':
            $post_a = __('EDIT_MESSAGE');
            break;
    }

    $s_hidden_fields = '<input type="hidden" name="folder" value="' . $folder . '" />';
    $s_hidden_fields .= '<input type="hidden" name="mode" value="' . $mode . '" />';
    if ($mode == 'edit') {
        $s_hidden_fields .= '<input type="hidden" name="' . POST_POST_URL . '" value="' . $privmsg_id . '" />';
    }

    //
    // Send smilies to template
    //
    generate_smilies('inline');

    $privmsg_subject = clean_title($privmsg_subject);

    template()->assign_vars([
        'SUBJECT' => htmlCHR($privmsg_subject),
        'USERNAME' => $to_username,
        'MESSAGE' => $privmsg_message,
        'FORUM_NAME' => __('PRIVATE_MESSAGE'),

        'BOX_NAME' => $l_box_name,
        'INBOX' => $inbox_url,
        'SENTBOX' => $sentbox_url,
        'OUTBOX' => $outbox_url,
        'SAVEBOX' => $savebox_url,

        'POSTING_TYPE_TITLE' => $post_a,

        'S_HIDDEN_FORM_FIELDS' => $s_hidden_fields,
        'S_POST_ACTION' => PM_URL,

        'U_SEARCH_USER' => 'search?mode=searchuser',
        'U_VIEW_FORUM' => PM_URL
    ]);
} else {
    //
    // Reset PM counters
    //
    user()->data['user_new_privmsg'] = 0;
    user()->data['user_unread_privmsg'] = userdata('user_new_privmsg') + userdata('user_unread_privmsg');
    user()->data['user_last_privmsg'] = userdata('session_start');

    //
    // Update unread status
    //
    \TorrentPier\Sessions::db_update_userdata(userdata(), [
        'user_unread_privmsg' => 'user_unread_privmsg + user_new_privmsg',
        'user_new_privmsg' => 0,
        'user_last_privmsg' => userdata('session_start')
    ]);

    $sql = "UPDATE " . BB_PRIVMSGS . "
		SET privmsgs_type = " . PRIVMSGS_UNREAD_MAIL . "
		WHERE privmsgs_type = " . PRIVMSGS_NEW_MAIL . "
			AND privmsgs_to_userid = " . userdata('user_id');
    if (!DB()->sql_query($sql)) {
        bb_die('Could not update private message new / read status (2) for user');
    }

    //
    // Generate page
    //
    $page_title = __('PRIVATE_MESSAGING');

    //
    // Load templates
    //
    template()->set_filenames(['body' => 'privmsgs.tpl']);

    //
    // New message
    //
    $post_new_mesg_url = '<a href="' . PM_URL . '?mode=post" class="btn-post-topic">' . __('SEND_A_NEW_MESSAGE') . '</a>';

    //
    // General SQL to obtain messages
    //
    $sql_tot = "SELECT COUNT(privmsgs_id) AS total
		FROM " . BB_PRIVMSGS . " ";
    $sql = "SELECT pm.privmsgs_type, pm.privmsgs_id, pm.privmsgs_date, pm.privmsgs_subject, u.user_id, u.username, u.user_rank
		FROM " . BB_PRIVMSGS . " pm, " . BB_USERS . " u ";
    switch ($folder) {
        case 'inbox':
            $sql_tot .= "WHERE privmsgs_to_userid = " . userdata('user_id') . "
				AND ( privmsgs_type =  " . PRIVMSGS_NEW_MAIL . "
					OR privmsgs_type = " . PRIVMSGS_READ_MAIL . "
					OR privmsgs_type = " . PRIVMSGS_UNREAD_MAIL . " )";

            $sql .= "WHERE pm.privmsgs_to_userid = " . userdata('user_id') . "
				AND u.user_id = pm.privmsgs_from_userid
				AND ( pm.privmsgs_type =  " . PRIVMSGS_NEW_MAIL . "
					OR pm.privmsgs_type = " . PRIVMSGS_READ_MAIL . "
					OR privmsgs_type = " . PRIVMSGS_UNREAD_MAIL . " )";
            break;

        case 'outbox':
            $sql_tot .= "WHERE privmsgs_from_userid = " . userdata('user_id') . "
				AND ( privmsgs_type =  " . PRIVMSGS_NEW_MAIL . "
					OR privmsgs_type = " . PRIVMSGS_UNREAD_MAIL . " )";

            $sql .= "WHERE pm.privmsgs_from_userid = " . userdata('user_id') . "
				AND u.user_id = pm.privmsgs_to_userid
				AND ( pm.privmsgs_type =  " . PRIVMSGS_NEW_MAIL . "
					OR privmsgs_type = " . PRIVMSGS_UNREAD_MAIL . " )";
            break;

        case 'sentbox':
            $sql_tot .= "WHERE privmsgs_from_userid = " . userdata('user_id') . "
				AND privmsgs_type =  " . PRIVMSGS_SENT_MAIL;

            $sql .= "WHERE pm.privmsgs_from_userid = " . userdata('user_id') . "
				AND u.user_id = pm.privmsgs_to_userid
				AND pm.privmsgs_type =  " . PRIVMSGS_SENT_MAIL;
            break;

        case 'savebox':
            $sql_tot .= "WHERE ( ( privmsgs_to_userid = " . userdata('user_id') . "
					AND privmsgs_type = " . PRIVMSGS_SAVED_IN_MAIL . " )
				OR ( privmsgs_from_userid = " . userdata('user_id') . "
					AND privmsgs_type = " . PRIVMSGS_SAVED_OUT_MAIL . ") )";

            $sql .= "WHERE u.user_id = pm.privmsgs_from_userid
				AND ( ( pm.privmsgs_to_userid = " . userdata('user_id') . "
					AND pm.privmsgs_type = " . PRIVMSGS_SAVED_IN_MAIL . " )
				OR ( pm.privmsgs_from_userid = " . userdata('user_id') . "
					AND pm.privmsgs_type = " . PRIVMSGS_SAVED_OUT_MAIL . " ) )";
            break;

        default:
            bb_die(__('NO_SUCH_FOLDER'));
            break;
    }

    //
    // Show messages over previous x days/months
    //
    if ($submit_msgdays && (!empty($_POST['msgdays']) || !empty($_GET['msgdays']))) {
        $msg_days = (!empty($_POST['msgdays'])) ? (int)$_POST['msgdays'] : (int)$_GET['msgdays'];
        $min_msg_time = TIMENOW - ($msg_days * 86400);

        $limit_msg_time_total = " AND privmsgs_date > $min_msg_time";
        $limit_msg_time = " AND pm.privmsgs_date > $min_msg_time ";

        if (!empty($_POST['msgdays'])) {
            $start = 0;
        }
    } else {
        $limit_msg_time = $limit_msg_time_total = '';
        $msg_days = 0;
    }

    $sql .= $limit_msg_time . " ORDER BY pm.privmsgs_date DESC LIMIT $start, " . config()->get('topics_per_page');
    $sql_all_tot = $sql_tot;
    $sql_tot .= $limit_msg_time_total;

    //
    // Get messages
    //
    if (!($result = DB()->sql_query($sql_tot))) {
        bb_die('Could not query private message information #1');
    }

    $pm_total = ($row = DB()->sql_fetchrow($result)) ? $row['total'] : 0;

    if (!($result = DB()->sql_query($sql_all_tot))) {
        bb_die('Could not query private message information #2');
    }

    $pm_all_total = ($row = DB()->sql_fetchrow($result)) ? $row['total'] : 0;

    //
    // Build select box
    //
    $previous_days = [0, 1, 7, 14, 30, 90, 180, 364];
    $previous_days_text = [__('ALL_POSTS'), __('1_DAY'), __('7_DAYS'), __('2_WEEKS'), __('1_MONTH'), __('3_MONTHS'), __('6_MONTHS'), __('1_YEAR')];

    $select_msg_days = '';
    for ($i = 0, $iMax = count($previous_days); $i < $iMax; $i++) {
        $selected = ($msg_days == $previous_days[$i]) ? ' selected' : '';
        $select_msg_days .= '<option value="' . $previous_days[$i] . '"' . $selected . '>' . $previous_days_text[$i] . '</option>';
    }

    //
    // Define correct icons
    //
    switch ($folder) {
        case 'inbox':
            $l_box_name = __('INBOX');
            break;
        case 'outbox':
            $l_box_name = __('OUTBOX');
            break;
        case 'savebox':
            $l_box_name = __('SAVEBOX');
            break;
        case 'sentbox':
            $l_box_name = __('SENTBOX');
            break;
    }
    $post_pm = PM_URL . "?mode=post";
    $post_pm_img = '<a href="' . $post_pm . '" class="btn-post-topic">' . __('POST_NEW_PM') . '</a>';
    $post_pm = '<a href="' . $post_pm . '">' . __('POST_NEW_PM') . '</a>';

    //
    // Output data for inbox status
    //
    $box_limit_img_length = $box_limit_percent = $l_box_size_status = '';
    $max_pm = ($folder != 'outbox') ? config()->get("max_{$folder}_privmsgs") : null;

    if ($max_pm) {
        $box_limit_percent = min(round(($pm_all_total / $max_pm) * 100), 100);
        $box_limit_img_length = min(round(($pm_all_total / $max_pm) * config()->get('privmsg_graphic_length')), config()->get('privmsg_graphic_length'));
        $box_limit_remain = max(($max_pm - $pm_all_total), 0);

        template()->assign_var('PM_BOX_SIZE_INFO');

        switch ($folder) {
            case 'inbox':
                $l_box_size_status = sprintf(__('INBOX_SIZE'), $box_limit_percent);
                break;
            case 'sentbox':
                $l_box_size_status = sprintf(__('SENTBOX_SIZE'), $box_limit_percent);
                break;
            case 'savebox':
                $l_box_size_status = sprintf(__('SAVEBOX_SIZE'), $box_limit_percent);
                break;
            default:
                $l_box_size_status = '';
                break;
        }
    }

    //
    // Dump vars to template
    //
    template()->assign_vars([
        'BOX_NAME' => $l_box_name,
        'BOX_EXPL' => ($folder == 'outbox') ? __('OUTBOX_EXPL') : '',
        'INBOX' => $inbox_url,
        'SENTBOX' => $sentbox_url,
        'OUTBOX' => $outbox_url,
        'SAVEBOX' => $savebox_url,

        'POST_PM_IMG' => $post_pm_img,
        'POST_PM' => $post_pm,

        'INBOX_LIMIT_IMG_WIDTH' => max(4, $box_limit_img_length),
        'INBOX_LIMIT_PERCENT' => $box_limit_percent,

        'BOX_SIZE_STATUS' => ($l_box_size_status) ?: '',

        'L_FROM_OR_TO' => ($folder == 'inbox' || $folder == 'savebox') ? __('FROM') : __('TO'),

        'S_PRIVMSGS_ACTION' => PM_URL . "?folder=$folder",
        'S_HIDDEN_FIELDS' => '',
        'S_POST_NEW_MSG' => $post_new_mesg_url,
        'S_SELECT_MSG_DAYS' => $select_msg_days,

        'U_POST_NEW_TOPIC' => PM_URL . '?mode=post'
    ]);

    //
    // Okay, let's build the correct folder
    //
    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not query private messages');
    }

    if ($row = DB()->sql_fetchrow($result)) {
        $i = 0;
        do {
            $privmsg_id = $row['privmsgs_id'];

            $flag = $row['privmsgs_type'];

            $icon_flag = ($flag == PRIVMSGS_NEW_MAIL || $flag == PRIVMSGS_UNREAD_MAIL) ? theme_images('pm_unreadmsg') : theme_images('pm_readmsg');
            $icon_flag_alt = ($flag == PRIVMSGS_NEW_MAIL || $flag == PRIVMSGS_UNREAD_MAIL) ? __('UNREAD_MESSAGE') : __('READ_MESSAGE');

            $msg_userid = $row['user_id'];
            $msg_user = profile_url($row);
            $msg_subject = censor()->censorString($row['privmsgs_subject']);

            $u_subject = PM_URL . "?folder=$folder&amp;mode=read&amp;" . POST_POST_URL . "=$privmsg_id";

            $msg_date = bb_date($row['privmsgs_date']);

            if ($flag == PRIVMSGS_NEW_MAIL && $folder == 'inbox') {
                $msg_subject = '<b>' . $msg_subject . '</b>';
                $msg_date = '<b>' . $msg_date . '</b>';
                $msg_user = '<b>' . $msg_user . '</b>';
            }

            $row_class = !($i & 1) ? 'row1' : 'row2';
            $i++;

            template()->assign_block_vars('listrow', [
                'ROW_CLASS' => $row_class,
                'FROM' => $msg_user,
                'SUBJECT' => htmlCHR($msg_subject),
                'DATE' => $msg_date,
                'DATE_RAW' => $row['privmsgs_date'],

                'PRIVMSG_FOLDER_IMG' => $icon_flag,
                'L_PRIVMSG_FOLDER_ALT' => $icon_flag_alt,
                'S_MARK_ID' => $privmsg_id,
                'U_READ' => $u_subject
            ]);
        } while ($row = DB()->sql_fetchrow($result));

        generate_pagination(PM_URL . "?folder=$folder", $pm_total, config()->get('topics_per_page'), $start);
    } else {
        template()->assign_block_vars('switch_no_messages', []);
    }
}

template()->assign_vars(['PAGE_TITLE' => @$page_title]);

require(PAGE_HEADER);

template()->pparse('body');

require(PAGE_FOOTER);

//
// Functions
//
function pm_die($msg)
{
    $msg .= '<br /><br />';
    $msg .= sprintf(__('CLICK_RETURN_INBOX'), '<a href="' . PM_URL . "?folder=inbox" . '">', '</a> ');
    $msg .= sprintf(__('CLICK_RETURN_SENTBOX'), '<a href="' . PM_URL . "?folder=sentbox" . '">', '</a> ');
    $msg .= sprintf(__('CLICK_RETURN_OUTBOX'), '<a href="' . PM_URL . "?folder=outbox" . '">', '</a> ');
    $msg .= sprintf(__('CLICK_RETURN_SAVEBOX'), '<a href="' . PM_URL . "?folder=savebox" . '">', '</a> ');
    $msg .= '<br /><br />';
    $msg .= sprintf(__('CLICK_RETURN_INDEX'), '<a href="' . "index.php" . '">', '</a>');

    bb_die($msg);
}
