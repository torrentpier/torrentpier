<?php

define('IN_PHPBB', true);
define('BB_SCRIPT', 'posting');
define('BB_ROOT', './');
require(BB_ROOT ."common.php");
require(INC_DIR .'bbcode.php');
require(INC_DIR .'functions_post.php');
require(BB_ROOT .'attach_mod/attachment_mod.php');

$page_cfg['load_tpl_vars'] = array(
	'post_icons',
);

$submit      = (bool) @$_REQUEST['post'];
$preview     = (bool) @$_REQUEST['preview'];
$delete      = (bool) @$_REQUEST['delete'];
$poll_delete = (bool) @$_REQUEST['poll_delete'];
$poll_add    = (bool) @$_REQUEST['add_poll_option'];
$poll_edit   = (bool) @$_REQUEST['edit_poll_option'];
$topic_tpl   = (bool) @$_REQUEST['tpl'];

$forum_id = (int) @$_REQUEST[POST_FORUM_URL];
$topic_id = (int) @$_REQUEST[POST_TOPIC_URL];
$post_id  = (int) @$_REQUEST[POST_POST_URL];

$mode = (string) @$_REQUEST['mode'];

$confirm = isset($_POST['confirm']);

$poll_id = null;

$refresh = $preview || $poll_add || $poll_edit || $poll_delete;
$orig_word = $replacement_word = array();

// Set topic type
$topic_type = (@$_POST['topictype']) ? (int) $_POST['topictype'] : POST_NORMAL;
$topic_type = in_array($topic_type, array(POST_NORMAL, POST_STICKY, POST_ANNOUNCE)) ? $topic_type : POST_NORMAL;

if ($mode == 'smilies')
{
	generate_smilies('window');
	exit;
}

$tracking_topics = get_tracks('topic');
$tracking_forums = get_tracks('forum');

// Start session management
$user->session_start();

// Quick Reply
$template->assign_vars(array(
	'L_FONT_SEL'         => $lang['QR_FONT_SEL'],
	'L_FONT_COLOR_SEL'   => $lang['QR_COLOR_SEL'],
	'L_FONT_SIZE_SEL'    => $lang['QR_SIZE_SEL'],
	'L_STEEL_BLUE'       => $lang['COLOR_STEEL_BLUE'],
	'L_COLOR_GRAY'       => $lang['COLOR_GRAY'],
	'L_COLOR_DARK_GREEN' => $lang['COLOR_DARK_GREEN'],
));

// What auth type do we need to check?
$is_auth = array();
switch ($mode)
{
	case 'newtopic':
	    if(bf($userdata['user_opt'], 'user_opt', 'allow_topic'))
	    {
	    	bb_die($lang['RULES_POST_CANNOT']);
	    }
		if ($topic_type == POST_ANNOUNCE)
		{
			$is_auth_type = 'auth_announce';
		}
		else if ($topic_type == POST_STICKY)
		{
			$is_auth_type = 'auth_sticky';
		}
		else
		{
			$is_auth_type = 'auth_post';
		}
		break;
	case 'reply':
	case 'quote':
		if(bf($userdata['user_opt'], 'user_opt', 'allow_post'))
	    {
	    	bb_die($lang['RULES_REPLY_CANNOT']);
	    }
		$is_auth_type = 'auth_reply';
		break;
	case 'editpost':
	    if(bf($userdata['user_opt'], 'user_opt', 'allow_post_edit'))
	    {
	    	bb_die($lang['RULES_EDIT_CANNOT']);
	    }
		$is_auth_type = 'auth_edit';
		break;
	case 'delete':
	case 'poll_delete':
		$is_auth_type = 'auth_delete';
		break;
	case 'vote':
		$is_auth_type = 'auth_vote';
		break;
	default:
		message_die(GENERAL_MESSAGE, $lang['NO_POST_MODE']);
		break;
}

// Here we do various lookups to find topic_id, forum_id, post_id etc.
// Doing it here prevents spoofing (eg. faking forum_id, topic_id or post_id
$error_msg = '';
$post_data = array();
switch ($mode)
{
	case 'newtopic':
		if (!$forum_id)
		{
			message_die(GENERAL_MESSAGE, $lang['FORUM_NOT_EXIST']);
		}
		$sql = "SELECT * FROM ". BB_FORUMS ." WHERE forum_id = $forum_id LIMIT 1";
		break;

	case 'reply':
	case 'vote':
		if (!$topic_id)
		{
			message_die(GENERAL_MESSAGE, $lang['NO_TOPIC_ID']);
		}
		$sql = "SELECT f.*, t.*
			FROM ". BB_FORUMS ." f, ". BB_TOPICS ." t
			WHERE t.topic_id = $topic_id
				AND f.forum_id = t.forum_id
			LIMIT 1";
		break;

	case 'quote':
	case 'editpost':
	case 'delete':
	case 'poll_delete':
		if (!$post_id)
		{
			message_die(GENERAL_MESSAGE, $lang['NO_POST_ID']);
		}

		$select_sql = 'SELECT f.*, t.*, p.*';
		$select_sql .= (!$submit) ? ', pt.*, u.username, u.user_id' : '';

		$from_sql = "FROM ". BB_POSTS ." p, ". BB_TOPICS ." t, ". BB_FORUMS ." f";
		$from_sql .= (!$submit) ? ", " . BB_POSTS_TEXT . " pt, " . BB_USERS . " u" : '';

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
		message_die(GENERAL_MESSAGE, $lang['NO_VALID_MODE']);
}

if ($post_info = DB()->fetch_row($sql))
{
	$forum_id = $post_info['forum_id'];
	$forum_name = $post_info['forum_name'];

	$is_auth = auth(AUTH_ALL, $forum_id, $userdata, $post_info);

	if ($post_info['forum_status'] == FORUM_LOCKED && !$is_auth['auth_mod'])
	{
	   message_die(GENERAL_MESSAGE, $lang['FORUM_LOCKED']);
	}
	else if ($mode != 'newtopic' && $post_info['topic_status'] == TOPIC_LOCKED && !$is_auth['auth_mod'])
	{
	   message_die(GENERAL_MESSAGE, $lang['TOPIC_LOCKED']);
	}

	if ($mode == 'editpost' || $mode == 'delete' || $mode == 'poll_delete')
	{
		$topic_id = $post_info['topic_id'];

		$post_data['poster_post'] = ($post_info['poster_id'] == $userdata['user_id']);
		$post_data['first_post'] = ($post_info['topic_first_post_id'] == $post_id);
		$post_data['last_post'] = ($post_info['topic_last_post_id'] == $post_id);
		$post_data['last_topic'] = ($post_info['forum_last_post_id'] == $post_id);
		$post_data['has_poll'] = (bool) $post_info['topic_vote'];
		$post_data['topic_type'] = $post_info['topic_type'];
		$post_data['poster_id'] = $post_info['poster_id'];

		if ($post_data['first_post'] && $post_data['has_poll'])
		{
			$sql = "SELECT *
				FROM ". BB_VOTE_DESC ." vd, ". BB_VOTE_RESULTS ." vr
				WHERE vd.topic_id = $topic_id
					AND vr.vote_id = vd.vote_id
				ORDER BY vr.vote_option_id";

			if (!$result = DB()->sql_query($sql))
			{
				message_die(GENERAL_ERROR, 'Could not obtain vote data for this topic', '', __LINE__, __FILE__, $sql);
			}

			$poll_options = array();
			$poll_results_sum = 0;
			if ($row = DB()->sql_fetchrow($result))
			{
				$poll_title = $row['vote_text'];
				$poll_id = $row['vote_id'];
				$poll_length = $row['vote_length'] / 86400;

				do
				{
					$poll_options[$row['vote_option_id']] = $row['vote_option_text'];
					$poll_results_sum += $row['vote_result'];
				}
				while ($row = DB()->sql_fetchrow($result));
			}
			$post_data['edit_poll'] = ((!$poll_results_sum || $is_auth['auth_mod']) && $post_data['first_post']);
		}
		else
		{
			$post_data['edit_poll'] = ($post_data['first_post'] && $is_auth['auth_pollcreate']);
		}

		// Can this user edit/delete the post/poll?
		if ($post_info['poster_id'] != $userdata['user_id'] && !$is_auth['auth_mod'])
		{
			$message = ($delete || $mode == 'delete') ? $lang['DELETE_OWN_POSTS'] : $lang['EDIT_OWN_POSTS'];
			$message .= '<br /><br />'. sprintf($lang['CLICK_RETURN_TOPIC'], '<a href="'. append_sid(TOPIC_URL ."$topic_id") .'">', '</a>');

			message_die(GENERAL_MESSAGE, $message);
		}
		else if (!$post_data['last_post'] && !$is_auth['auth_mod'] && ($mode == 'delete' || $delete))
		{
			message_die(GENERAL_MESSAGE, $lang['CANNOT_DELETE_REPLIED']);
		}
		else if (!$post_data['edit_poll'] && !$is_auth['auth_mod'] && ($mode == 'poll_delete' || $poll_delete))
		{
			message_die(GENERAL_MESSAGE, $lang['CANNOT_DELETE_POLL']);
		}
	}
	else
	{
		if ($mode == 'quote')
		{
			$topic_id = $post_info['topic_id'];
		}
		if ($mode == 'newtopic')
		{
			$post_data['topic_type'] = POST_NORMAL;
		}
		$post_data['first_post'] = ($mode == 'newtopic');
		$post_data['last_post']  = false;
		$post_data['has_poll']   = false;
		$post_data['edit_poll']  = false;
	}
	if ($mode == 'poll_delete' && !$poll_id)
	{
		message_die(GENERAL_MESSAGE, $lang['NO_SUCH_POST']);
	}
}
else
{
	message_die(GENERAL_MESSAGE, $lang['NO_SUCH_POST']);
}

// The user is not authed, if they're not logged in then redirect
// them, else show them an error message
if (!$is_auth[$is_auth_type])
{
	if (!IS_GUEST)
	{
		message_die(GENERAL_MESSAGE, sprintf($lang['SORRY_'. strtoupper($is_auth_type)], $is_auth[$is_auth_type .'_type']));
	}

	switch ($mode)
	{
		case 'newtopic':
			$redirect = "mode=newtopic&f=$forum_id";
			break;
		case 'reply':
			$redirect = "mode=reply&t=$topic_id";
			break;
		case 'quote':
		case 'editpost':
			$redirect = "mode=quote&p=$post_id";
			break;
		default:
			$redirect = '';
	}
	redirect("login.php?redirect=/posting.php?$redirect");
}

if ($mode == 'newtopic' && $topic_tpl && $post_info['topic_tpl_id'])
{
	require(INC_DIR .'topic_templates.php');
}

// Notify
if ($submit || $refresh)
{
	$notify_user = (int) !empty($_POST['notify']);
}
else
{
	$notify_user = bf($userdata['user_opt'], 'user_opt', 'notify');
	if (!IS_GUEST && $mode != 'newtopic' && !$notify_user)
	{
		$notify_user = (int) DB()->fetch_row("
			SELECT topic_id
			FROM ". BB_TOPICS_WATCH ."
			WHERE topic_id = $topic_id
			  AND user_id = ". $userdata['user_id'] ."
		");
	}
}

$update_post_time = !empty($_POST['update_post_time']);

execute_posting_attachment_handling();

// если за время пока вы писали ответ, в топике появились новые сообщения, перед тем как ваше сообщение будет отправлено, выводится предупреждение с обзором этих сообщений
$topic_has_new_posts = false;

if (!IS_GUEST && $mode != 'newtopic' && ($submit || $preview || $mode == 'quote' || $mode == 'reply') && isset($_COOKIE[COOKIE_TOPIC]))
{
	if ($topic_last_read = max(intval(@$tracking_topics[$topic_id]), intval(@$tracking_forums[$forum_id])))
	{
		$sql = "SELECT p.*, pt.post_text, u.username
			FROM ". BB_POSTS ." p, ". BB_POSTS_TEXT ." pt, ". BB_USERS ." u
			WHERE p.topic_id = ". (int) $topic_id ."
				AND u.user_id = p.poster_id
				AND pt.post_id = p.post_id
				AND p.post_time > $topic_last_read
			ORDER BY p.post_time
			LIMIT ". $bb_cfg['posts_per_page'];

		if ($rowset = DB()->fetch_rowset($sql))
		{
			$topic_has_new_posts = true;

			foreach ($rowset as $i => $row)
			{
				if ($row['poster_id'] == ANONYMOUS)
				{
					$new_post_username = (!$row['post_username']) ? $lang['GUEST'] : $row['post_username'];
				}
				else
				{
					$new_post_username = $row['username'];
				}

				$template->assign_block_vars('new_posts', array(
					'ROW_CLASS'      => !($i % 2) ? 'row1' : 'row2',
					'POSTER_NAME'    => $new_post_username,
					'POSTER_NAME_JS' => addslashes($new_post_username),
					'POST_DATE'      => bb_date($row['post_time'], $bb_cfg['post_date_format']),
					'MESSAGE'        => get_parsed_post($row),
				));
			}
			$template->assign_vars(array(
				'TPL_SHOW_NEW_POSTS'  => true,
			));

			set_tracks(COOKIE_TOPIC, $tracking_topics, $topic_id);
			unset($rowset);
		}
	}
}

// --------------------
//  What shall we do?
//
if ( ( $delete || $poll_delete || $mode == 'delete' ) && !$confirm )
{
	if (isset($_POST['cancel']))
	{
		redirect(POST_URL . "$post_id#$post_id");
	}
	//
	// Confirm deletion
	//
	$hidden_fields = array(
		'p'    => $post_id,
		'mode' => ($delete || $mode == "delete") ? 'delete' : 'poll_delete',
	);

	print_confirmation(array(
		'QUESTION'      => ($delete || $mode == 'delete') ? $lang['CONFIRM_DELETE'] : $lang['CONFIRM_DELETE_POLL'],
		'FORM_ACTION'   => "posting.php",
		'HIDDEN_FIELDS' => build_hidden_fields($hidden_fields),
	));
}
else if ( $mode == 'vote' )
{
	//
	// Vote in a poll
	//
	if ( !empty($_POST['vote_id']) )
	{
		$vote_option_id = intval($_POST['vote_id']);

		$sql = "SELECT vd.vote_id
			FROM " . BB_VOTE_DESC . " vd, " . BB_VOTE_RESULTS . " vr
			WHERE vd.topic_id = $topic_id
				AND vr.vote_id = vd.vote_id
				AND vr.vote_option_id = $vote_option_id
			GROUP BY vd.vote_id";
		if ( !($result = DB()->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not obtain vote data for this topic', '', __LINE__, __FILE__, $sql);
		}

		if ( $vote_info = DB()->sql_fetchrow($result) )
		{
			$vote_id = $vote_info['vote_id'];

			$sql = "SELECT *
				FROM " . BB_VOTE_USERS . "
				WHERE vote_id = $vote_id
					AND vote_user_id = " . $userdata['user_id'];
			if ( !($result2 = DB()->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Could not obtain user vote data for this topic', '', __LINE__, __FILE__, $sql);
			}

			if ( !($row = DB()->sql_fetchrow($result2)) )
			{
				$sql = "UPDATE " . BB_VOTE_RESULTS . "
					SET vote_result = vote_result + 1
					WHERE vote_id = $vote_id
						AND vote_option_id = $vote_option_id";
				if ( !DB()->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, 'Could not update poll result', '', __LINE__, __FILE__, $sql);
				}

				$sql = "INSERT INTO " . BB_VOTE_USERS . " (vote_id, vote_user_id, vote_user_ip)
					VALUES ($vote_id, " . $userdata['user_id'] . ", '". USER_IP ."')";
				if ( !DB()->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Could not insert user_id for poll", "", __LINE__, __FILE__, $sql);
				}

				$message = $lang['VOTE_CAST'];
			}
			else
			{
				$message = $lang['ALREADY_VOTED'];
			}
			DB()->sql_freeresult($result2);
		}
		else
		{
			$message = $lang['NO_VOTE_OPTION'];
		}
		DB()->sql_freeresult($result);

		meta_refresh(append_sid("viewtopic.php?" . POST_TOPIC_URL . "=$topic_id"));
		$message .= '<br /><br />' . sprintf($lang['CLICK_RETURN_TOPIC'], '<a href="' . append_sid("viewtopic.php?" . POST_TOPIC_URL . "=$topic_id") . '">', '</a>');
		message_die(GENERAL_MESSAGE, $message);
	}
	else
	{
		redirect(append_sid("viewtopic.php?" . POST_TOPIC_URL . "=$topic_id", true));
	}
}
//snp
// else if ( $submit || $confirm )
else if ( ($submit || $confirm) && !$topic_has_new_posts )
//snp end
{
	//
	// Submit post/vote (newtopic, edit, reply, etc.)
	//
	$return_message = '';
	$return_meta = '';

	switch ( $mode )
	{
		case 'editpost':
		case 'newtopic':
		case 'reply':
			$username = ( !empty($_POST['username']) ) ? clean_username($_POST['username']) : '';
			$subject = ( !empty($_POST['subject']) ) ? clean_title($_POST['subject']) : '';
			$message = ( !empty($_POST['message']) ) ? prepare_message($_POST['message']) : '';
			$poll_title = ( isset($_POST['poll_title']) && $is_auth['auth_pollcreate'] ) ? clean_title($_POST['poll_title']) : '';
			$poll_options = ( isset($_POST['poll_option_text']) && $is_auth['auth_pollcreate'] ) ? $_POST['poll_option_text'] : '';
			$poll_length = ( isset($_POST['poll_length']) && $is_auth['auth_pollcreate'] ) ? $_POST['poll_length'] : '';

			prepare_post($mode, $post_data, $error_msg, $username, $subject, $message, $poll_title, $poll_options, $poll_length);

			if (!$error_msg)
			{
				$topic_type = ( isset($post_data['topic_type']) && $topic_type != $post_data['topic_type'] && !$is_auth['auth_sticky'] && !$is_auth['auth_announce'] ) ? $post_data['topic_type'] : $topic_type;

				submit_post($mode, $post_data, $return_message, $return_meta, $forum_id, $topic_id, $post_id, $poll_id, $topic_type, DB()->escape($username), DB()->escape($subject), DB()->escape($message), DB()->escape($poll_title), $poll_options, $poll_length, $update_post_time);
			}
			break;

		case 'delete':
		case 'poll_delete':
			require_once(INC_DIR .'functions_admin.php');
			delete_post($mode, $post_data, $return_message, $return_meta, $forum_id, $topic_id, $post_id, $poll_id);
			break;
	}

	if (!$error_msg)
	{
		if (!in_array($mode, array('editpost', 'delete', 'poll_delete')))
		{
			$user_id = ( $mode == 'reply' || $mode == 'newtopic' ) ? $userdata['user_id'] : $post_data['poster_id'];
			update_post_stats($mode, $post_data, $forum_id, $topic_id, $post_id, $user_id);
		}
		$attachment_mod['posting']->insert_attachment($post_id);

		if (!$error_msg && $mode != 'poll_delete')
		{
			user_notification($mode, $post_data, $post_info['topic_title'], $forum_id, $topic_id, $post_id, $notify_user);
		}

		if ($mode == 'newtopic' || $mode == 'reply')
		{
			set_tracks(COOKIE_TOPIC, $tracking_topics, $topic_id);
		}

		if (defined('TORRENT_ATTACH_ID') && $bb_cfg['bt_newtopic_auto_reg'] && !$error_msg)
		{
			include(INC_DIR .'functions_torrent.php');
			if(!DB()->fetch_row("SELECT attach_id FROM ". BB_BT_TORRENTS ." WHERE attach_id = ". TORRENT_ATTACH_ID))
			{
				tracker_register(TORRENT_ATTACH_ID, 'newtopic');
			}
		}

		if ($mode == 'reply' && $post_info['topic_status'] == TOPIC_LOCKED)
		{
			$locked_warn = '
				<div class="warnColor1">
					<b>'. $lang['LOCKED_WARN'] .'</b>
				</div>
				<br /><hr /><br />
			';
			$return_message = $locked_warn . $return_message;
		}

		message_die(GENERAL_MESSAGE, $return_message);
	}
}

//snp
//if( $refresh || isset($_POST['del_poll_option']) || $error_msg != '' )
if( $refresh || isset($_POST['del_poll_option']) || $error_msg || ($submit && $topic_has_new_posts) )
//snp end
{
	$username = ( !empty($_POST['username']) ) ? clean_username($_POST['username']) : '';
	$subject = ( !empty($_POST['subject']) ) ? clean_title($_POST['subject']) : '';
	$message = ( !empty($_POST['message']) ) ? prepare_message($_POST['message']) : '';

	$poll_title = ( !empty($_POST['poll_title']) ) ? clean_title($_POST['poll_title']) : '';
	$poll_length = ( isset($_POST['poll_length']) ) ? max(0, intval($_POST['poll_length'])) : 0;

	$poll_options = array();
	if ( !empty($_POST['poll_option_text']) )
	{
#		while( list($option_id, $option_text) = @each($_POST['poll_option_text']) )
		foreach ($_POST['poll_option_text'] as $option_id => $option_text)
		{
			if( isset($_POST['del_poll_option'][$option_id]) )
			{
				unset($poll_options[$option_id]);
			}
			else if ( !empty($option_text) )
			{
				$poll_options[$option_id] = clean_title($option_text);
			}
		}
	}

	if ( $poll_add && !empty($_POST['add_poll_option_text']) )
	{
		$poll_options[] = clean_title($_POST['add_poll_option_text']);
	}

	if ($preview)
	{
		$preview_subject  = $subject;
		$preview_username = $username;
		$preview_message  = htmlCHR($message, false, ENT_NOQUOTES);
		$preview_message  = bbcode2html($preview_message);

		$template->assign_vars(array(
			'TPL_PREVIEW_POST' => true,
			'TOPIC_TITLE'      => wbr($preview_subject),
			'POST_SUBJECT'     => $preview_subject,
			'POSTER_NAME'      => $preview_username,
			'POST_DATE'        => bb_date(TIMENOW),
			'PREVIEW_MSG'      => $preview_message,
		));
	}
}
else
{
	// User default entry point
	if ( $mode == 'newtopic' )
	{
		$username = ($userdata['session_logged_in']) ? $userdata['username'] : '';
		$poll_title = '';
		$poll_length = '';
		$subject = '';
		$message = '';
	}
	else if ( $mode == 'reply' )
	{
		$username = ( $userdata['session_logged_in'] ) ? $userdata['username'] : '';
		$subject = '';
		$message = '';
	}
	else if ( $mode == 'quote' || $mode == 'editpost' )
	{
		$subject = ( $post_data['first_post'] ) ? $post_info['topic_title'] : $post_info['post_subject'];
		$message = $post_info['post_text'];

		if ( $mode == 'quote' )
		{
			if (!defined('WORD_LIST_OBTAINED'))
			{
				$orig_word = array();
				$replace_word = array();
				obtain_word_list($orig_word, $replace_word);
				define('WORD_LIST_OBTAINED', TRUE);
			}

            if($post_info['post_attachment'] && !IS_AM) $message = $post_info['topic_title'];

			// Use trim to get rid of spaces placed there by MS-SQL 2000
			$quote_username = ( trim($post_info['post_username']) != '' ) ? $post_info['post_username'] : $post_info['username'];
			$message = '[quote="' . $quote_username . '"]' . $message . '[/quote]';
			// hide user passkey
			$message = preg_replace('#(?<=\?uk=)[a-zA-Z0-9]{10}(?=&)#', 'passkey', $message);
			// hide sid
			$message = preg_replace('#(?<=[\?&;]sid=)[a-zA-Z0-9]{12}#', 'sid', $message);

			if ( !empty($orig_word) )
			{
				$subject = ( !empty($subject) ) ? preg_replace($orig_word, $replace_word, $subject) : '';
				$message = ( !empty($message) ) ? preg_replace($orig_word, $replace_word, $message) : '';
			}

			if ( !preg_match('/^Re:/', $subject) && strlen($subject) > 0 )
			{
				$subject = 'Re: ' . $subject;
			}

			$mode = 'reply';
		}
		else
		{
			$username = ( $post_info['user_id'] == ANONYMOUS && !empty($post_info['post_username']) ) ? $post_info['post_username'] : '';
		}
	}
}

if ($error_msg)
{
	$template->assign_vars(array(
		'ERROR_MESSAGE' => $error_msg,
	));
}

if (IS_GUEST || ($mode == 'editpost' && $post_info['poster_id'] == ANONYMOUS))
{
	$template->assign_var('POSTING_USERNAME');
}

//
// Notify checkbox
//
if (!IS_GUEST)
{
	if ($mode != 'editpost' || ($mode == 'editpost' && $post_info['poster_id'] != ANONYMOUS))
	{
		$template->assign_var('SHOW_NOTIFY_CHECKBOX');
	}
}

//
// Topic type selection
//
$topic_type_toggle = '';
if ( $mode == 'newtopic' || ( $mode == 'editpost' && $post_data['first_post'] ) )
{
	$template->assign_block_vars('switch_type_toggle', array());

	if( $is_auth['auth_sticky'] )
	{
		$topic_type_toggle .= '<input type="radio" name="topictype" value="' . POST_STICKY . '"';
		if ( isset($post_data['topic_type']) && ($post_data['topic_type'] == POST_STICKY || $topic_type == POST_STICKY) )
		{
			$topic_type_toggle .= ' checked="checked"';
		}
		$topic_type_toggle .= ' /> ' . $lang['POST_STICKY'] . '&nbsp;&nbsp;';
	}

	if( $is_auth['auth_announce'] )
	{
		$topic_type_toggle .= '<input type="radio" name="topictype" value="' . POST_ANNOUNCE . '"';
		if ( isset($post_data['topic_type']) && ($post_data['topic_type'] == POST_ANNOUNCE || $topic_type == POST_ANNOUNCE) )
		{
			$topic_type_toggle .= ' checked="checked"';
		}
		$topic_type_toggle .= ' /> ' . $lang['POST_ANNOUNCEMENT'] . '&nbsp;&nbsp;';
	}

	if ( $topic_type_toggle != '' )
	{
		$topic_type_toggle = $lang['POST_TOPIC_AS'] . ': <input type="radio" name="topictype" value="' . POST_NORMAL .'"' . ( (!isset($post_data['topic_type']) || $post_data['topic_type'] == POST_NORMAL || $topic_type == POST_NORMAL) ? ' checked="checked"' : '' ) . ' /> ' . $lang['POST_NORMAL'] . '&nbsp;&nbsp;' . $topic_type_toggle;
	}
}
//bt
$topic_dl_type = (isset($post_info['topic_dl_type'])) ? $post_info['topic_dl_type'] : 0;

if ($topic_dl_type || $post_info['allow_dl_topic'] || $is_auth['auth_mod'])
{
	if (!$topic_type_toggle)
	{
		$topic_type_toggle = $lang['POST_TOPIC_AS'] . ': ';
	}

	$dl_ds = $dl_ch = $dl_hid = '';
	$dl_type_name = 'topic_dl_type';
	$dl_type_val = ($topic_dl_type) ? 1 : 0;

	if (!$post_info['allow_dl_topic'] && !$is_auth['auth_mod'])
	{
		$dl_ds = ' disabled="disabled" ';
		$dl_hid = '<input type="hidden" name="topic_dl_type" value="'. $dl_type_val .'" />';
		$dl_type_name = '';
	}

	$dl_ch = ($mode == 'editpost' && $post_data['first_post'] && $topic_dl_type) ? ' checked="checked" ' : '';

	$topic_type_toggle .= '<nobr><input type="checkbox" name="'. $dl_type_name .'" id="topic_dl_type_id" '. $dl_ds . $dl_ch .' /><label for="topic_dl_type_id"> Download</label></nobr>';
	$topic_type_toggle .= $dl_hid;
}
//bt end

$hidden_form_fields = '<input type="hidden" name="mode" value="' . $mode . '" />';

switch( $mode )
{
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

$template->set_filenames(array(
	'body' => 'posting.tpl',
));

$template->assign_vars(array(
	'FORUM_NAME' => htmlCHR($forum_name),
	'PAGE_TITLE' => $page_title,
	'POSTING_TYPE_TITLE' => $page_title,
	'POSTING_TOPIC_ID' => ($mode != 'newtopic') ? $topic_id : '',
	'POSTING_TOPIC_TITLE' => ($mode != 'newtopic') ? wbr($post_info['topic_title']) : '',

	'SHOW_VIRTUAL_KEYBOARD' => $bb_cfg['show_virtual_keyboard'],

	'U_VIEW_FORUM' => append_sid("viewforum.php?" . POST_FORUM_URL . "=$forum_id"))
);

if ($mode == 'newtopic' || $post_data['first_post'])
{
	$template->assign_var('POSTING_SUBJECT');
}

// Update post time
if ($mode == 'editpost' && $post_data['last_post'] && !$post_data['first_post'])
{
	$template->assign_vars(array(
		'SHOW_UPDATE_POST_TIME'    => ($is_auth['auth_mod'] || ($post_data['poster_post'] && $post_info['post_time'] + 3600*3 > TIMENOW)),
		'UPDATE_POST_TIME_CHECKED' => ($post_data['poster_post'] && ($post_info['post_time'] + 3600*2 > TIMENOW)),
	));
}

//
// Output the data to the template
//

$template->assign_vars(array(
	'USERNAME' => @$username,
	'CAPTCHA_HTML' => (IS_GUEST) ? CAPTCHA()->get_html() : '',
	'SUBJECT' => $subject,
	'MESSAGE' => $message,

	'L_SUBJECT' => $lang['SUBJECT'],
	'L_MESSAGE_BODY' => $lang['MESSAGE_BODY'],
	'L_CONFIRM_DELETE' => $lang['CONFIRM_DELETE'],
	'L_DISABLE_BBCODE' => $lang['DISABLE_BBCODE_POST'],
	'L_DISABLE_SMILIES' => $lang['DISABLE_SMILIES_POST'],
	'L_NOTIFY_ON_REPLY' => $lang['NOTIFY'],
	'L_DELETE_POST' => $lang['DELETE_POST'],
	'L_UPDATE_POST_TIME' => $lang['UPDATE_POST_TIME'],

	'L_BBCODE_B_HELP' => $lang['BBCODE_B_HELP'],
	'L_BBCODE_I_HELP' => $lang['BBCODE_I_HELP'],
	'L_BBCODE_U_HELP' => $lang['BBCODE_U_HELP'],
	'L_BBCODE_Q_HELP' => $lang['BBCODE_Q_HELP'],
	'L_BBCODE_C_HELP' => $lang['BBCODE_C_HELP'],
	'L_BBCODE_L_HELP' => $lang['BBCODE_L_HELP'],
	'L_BBCODE_O_HELP' => $lang['BBCODE_O_HELP'],
	'L_BBCODE_P_HELP' => $lang['BBCODE_P_HELP'],
	'L_BBCODE_W_HELP' => $lang['BBCODE_W_HELP'],
	'L_BBCODE_A_HELP' => $lang['BBCODE_A_HELP'],
	'L_BBCODE_S_HELP' => $lang['BBCODE_S_HELP'],
	'L_BBCODE_F_HELP' => $lang['BBCODE_F_HELP'],
	'L_EMPTY_MESSAGE' => $lang['EMPTY_MESSAGE'],

	'L_FONT_COLOR' => $lang['FONT_COLOR'],
	'L_COLOR_DEFAULT' => $lang['COLOR_DEFAULT'],
	'L_COLOR_DARK_RED' => $lang['COLOR_DARK_RED'],
	'L_COLOR_RED' => $lang['COLOR_RED'],
	'L_COLOR_ORANGE' => $lang['COLOR_ORANGE'],
	'L_COLOR_BROWN' => $lang['COLOR_BROWN'],
	'L_COLOR_YELLOW' => $lang['COLOR_YELLOW'],
	'L_COLOR_GREEN' => $lang['COLOR_GREEN'],
	'L_COLOR_OLIVE' => $lang['COLOR_OLIVE'],
	'L_COLOR_CYAN' => $lang['COLOR_CYAN'],
	'L_COLOR_BLUE' => $lang['COLOR_BLUE'],
	'L_COLOR_DARK_BLUE' => $lang['COLOR_DARK_BLUE'],
	'L_COLOR_INDIGO' => $lang['COLOR_INDIGO'],
	'L_COLOR_VIOLET' => $lang['COLOR_VIOLET'],
	'L_COLOR_WHITE' => $lang['COLOR_WHITE'],
	'L_COLOR_BLACK' => $lang['COLOR_BLACK'],

	'L_FONT_SIZE' => $lang['FONT_SIZE'],
	'L_FONT_TINY' => $lang['FONT_TINY'],
	'L_FONT_SMALL' => $lang['FONT_SMALL'],
	'L_FONT_NORMAL' => $lang['FONT_NORMAL'],
	'L_FONT_LARGE' => $lang['FONT_LARGE'],
	'L_FONT_HUGE' => $lang['FONT_HUGE'],

	'L_STYLES_TIP' => $lang['STYLES_TIP'],

	'U_VIEWTOPIC' => ( $mode == 'reply' ) ? append_sid("viewtopic.php?" . POST_TOPIC_URL . "=$topic_id&amp;postorder=desc") : '',

	'S_NOTIFY_CHECKED' => ( $notify_user ) ? 'checked="checked"' : '',
	'S_TYPE_TOGGLE' => $topic_type_toggle,
	'S_TOPIC_ID' => $topic_id,
	'S_POST_ACTION' => append_sid("posting.php"),
	'S_HIDDEN_FORM_FIELDS' => $hidden_form_fields)
);

// Output the data to the template (for MAIL.RU Keyboard)
$template->assign_vars(array(
	'L_KB_TITLE' => $lang['KB_TITLE'],
	'L_LAYOUT' => $lang['KB_RUS_KEYLAYOUT'],
	'L_NONE' => $lang['KB_NONE'],
	'L_TRANSLIT' => $lang['KB_TRANSLIT'],
	'L_TRADITIONAL' => $lang['KB_TRADITIONAL'],
	'L_RULES' => $lang['KB_RULES'],
	'L_SHOW' => $lang['KB_SHOW'],
	'L_CLOSE' =>  $lang['KB_CLOSE'],
	'L_TRANSLIT_OPERA7' => $lang['KB_TRANSLIT_OPERA7'],
	'L_TRANSLIT_MOZILLA' => $lang['KB_TRANSLIT_MOZILLA'],
	'S_VISIBILITY_RULES' => 'position:absolute;visibility:hidden;',
	'S_VISIBILITY_KEYB' => 'position:absolute;visibility:hidden;',
	'S_VISIBILITY_OFF' => '')
);
//
// Poll entry switch/output
//
if( ( $mode == 'newtopic' || ( $mode == 'editpost' && $post_data['edit_poll']) ) && $is_auth['auth_pollcreate'] )
{
	$template->assign_vars(array(
		'L_ADD_A_POLL' => $lang['ADD_POLL'],
		'L_ADD_POLL_EXPLAIN' => $lang['ADD_POLL_EXPLAIN'],
		'L_POLL_QUESTION' => $lang['POLL_QUESTION'],
		'L_POLL_OPTION' => $lang['POLL_OPTION'],
		'L_ADD_OPTION' => $lang['ADD_OPTION'],
		'L_POLL_LENGTH' => $lang['POLL_FOR'],
		'L_DAYS' => $lang['DAYS'],
		'L_POLL_LENGTH_EXPLAIN' => $lang['POLL_FOR_EXPLAIN'],
		'L_POLL_DELETE' => $lang['DELETE_POLL'],

		'POLL_TITLE' => @$poll_title,
		'POLL_LENGTH' => @$poll_length)
	);

	if( $mode == 'editpost' && $post_data['edit_poll'] && $post_data['has_poll'])
	{
		$template->assign_block_vars('switch_poll_delete_toggle', array());
	}

	if( !empty($poll_options) )
	{
		while( list($option_id, $option_text) = each($poll_options) )
		{
			$template->assign_block_vars('poll_option_rows', array(
				'POLL_OPTION' => str_replace('"', '&quot;', $option_text),

				'S_POLL_OPTION_NUM' => $option_id)
			);
		}
	}

	$template->assign_var('POLLBOX');
}

//
// Topic review
//
if( $mode == 'reply' && $is_auth['auth_read'] )
{
	topic_review($topic_id);
}

require(PAGE_HEADER);

$template->pparse('body');

require(PAGE_FOOTER);