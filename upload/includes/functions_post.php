<?php
/***************************************************************************
 *                            functions_post.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: functions_post.php,v 1.9.2.40 2005/12/22 11:34:02 acydburn Exp $
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

if (!defined('BB_ROOT')) die(basename(__FILE__));

//
// Prepare a message for posting
//
function prepare_post(&$mode, &$post_data, &$error_msg, &$username, &$subject, &$message, &$poll_title, &$poll_options, &$poll_length)
{
	global $bb_cfg, $userdata, $lang;

	// Check username
	if (!empty($username))
	{
		$username = clean_username($username);

		if (!$userdata['session_logged_in'] || ($userdata['session_logged_in'] && $username != $userdata['username']))
		{
			require(INC_DIR .'functions_validate.php');

			if ($err = validate_username($username))
			{
				$error_msg .= $err;
			}
		}
		else
		{
			$username = '';
		}
	}

	// Check subject
	if (!empty($subject))
	{
		$subject = preg_replace('#&amp;#', '&', htmlspecialchars(trim($subject)));
	}
	else if ($mode == 'newtopic' || ($mode == 'editpost' && $post_data['first_post']))
	{
		$error_msg .= (!empty($error_msg)) ? '<br />' . $lang['EMPTY_SUBJECT'] : $lang['EMPTY_SUBJECT'];
	}

	// Check message
	if (!empty($message))
	{

	}
	else if ($mode != 'delete' && $mode != 'poll_delete')
	{
		$error_msg .= (!empty($error_msg)) ? '<br />' . $lang['EMPTY_MESSAGE'] : $lang['EMPTY_MESSAGE'];
	}

	//
	// Handle poll stuff
	//
	if ($mode == 'newtopic' || ($mode == 'editpost' && $post_data['first_post']))
	{
		$poll_length = (isset($poll_length)) ? max(0, intval($poll_length)) : 0;

		if (!empty($poll_title))
		{
			$poll_title = htmlspecialchars(trim($poll_title));
		}

		if(!empty($poll_options))
		{
			$temp_option_text = array();
			while(list($option_id, $option_text) = @each($poll_options))
			{
				$option_text = trim($option_text);
				if (!empty($option_text))
				{
					$temp_option_text[$option_id] = htmlspecialchars($option_text);
				}
			}
			$option_text = $temp_option_text;

			if (count($poll_options) < 2)
			{
				$error_msg .= (!empty($error_msg)) ? '<br />' . $lang['TO_FEW_POLL_OPTIONS'] : $lang['TO_FEW_POLL_OPTIONS'];
			}
			else if (count($poll_options) > $bb_cfg['max_poll_options'])
			{
				$error_msg .= (!empty($error_msg)) ? '<br />' . $lang['TO_MANY_POLL_OPTIONS'] : $lang['TO_MANY_POLL_OPTIONS'];
			}
			else if ($poll_title == '')
			{
				$error_msg .= (!empty($error_msg)) ? '<br />' . $lang['EMPTY_POLL_TITLE'] : $lang['EMPTY_POLL_TITLE'];
			}
		}
	}

    // Check smilies limit
    if($bb_cfg['max_smilies'])
    {
		$count_smilies = substr_count(bbcode2html($message), '<img class="smile" src="'. $bb_cfg['smilies_path']);
		if($count_smilies > $bb_cfg['max_smilies'])
		{
			$to_many_smilies = sprintf($lang['MAX_SMILIES_PER_POST'], $bb_cfg['max_smilies']);
			$error_msg .= (!empty($error_msg)) ? '<br />'. $to_many_smilies : $to_many_smilies;
		}
    }

	if (IS_GUEST && !CAPTCHA()->verify_code())
	{
		$error_msg .= (!empty($error_msg)) ? '<br />' . $lang['CONFIRM_CODE_WRONG'] : $lang['CONFIRM_CODE_WRONG'];
	}
}

//
// Post a new topic/reply/poll or edit existing post/poll
//
function submit_post($mode, &$post_data, &$message, &$meta, &$forum_id, &$topic_id, &$post_id, &$poll_id, &$topic_type, $post_username, $post_subject, $post_message, $poll_title, &$poll_options, &$poll_length, $update_post_time)
{
	global $bb_cfg, $lang;
	global $userdata, $post_info, $is_auth;

	$current_time = time();

	// Flood control
	$row = null;
	$where_sql = (IS_GUEST) ? "p.poster_ip = '". USER_IP ."'" : "p.poster_id = {$userdata['user_id']}";

	if ($mode == 'newtopic' || $mode == 'reply')
	{
		$sql = "SELECT MAX(p.post_time) AS last_post_time FROM ". BB_POSTS ." p WHERE $where_sql";

		if ($row = DB()->fetch_row($sql) AND $row['last_post_time'])
		{
			if ($userdata['user_level'] == USER)
			{
				if (TIMENOW - $row['last_post_time'] < $bb_cfg['flood_interval'])
				{
					message_die(GENERAL_MESSAGE, $lang['FLOOD_ERROR']);
				}
			}
		}
	}

	// Double Post Control
	if ($mode != 'editpost' && !empty($row['last_post_time']) && !IS_AM)
	{
		$sql = "
			SELECT pt.post_text
			FROM ". BB_POSTS ." p, ". BB_POSTS_TEXT ." pt
			WHERE
					$where_sql
				AND p.post_time = ". (int) $row['last_post_time'] ."
				AND pt.post_id = p.post_id
			LIMIT 1
		";

		if ($row = DB()->fetch_row($sql))
		{
			$last_msg = str_replace("\'", "''", $last_msg);

			if ($last_msg == $post_message)
			{
				message_die(GENERAL_MESSAGE, $lang['DOUBLE_POST_ERROR']);
			}
		}
	}

	if ($mode == 'newtopic' || ($mode == 'editpost' && $post_data['first_post']))
	{
		$topic_vote = (!empty($poll_title) && count($poll_options) >= 2) ? 1 : 0;

		$topic_dl_type = (isset($_POST['topic_dl_type']) && ($post_info['allow_reg_tracker'] || $post_info['allow_dl_topic'] || $is_auth['auth_mod'])) ? TOPIC_DL_TYPE_DL : TOPIC_DL_TYPE_NORMAL;

		$sql  = ($mode != "editpost") ? "INSERT INTO " . BB_TOPICS . " (topic_title, topic_poster, topic_time, forum_id, topic_status, topic_type, topic_dl_type, topic_vote) VALUES ('$post_subject', " . $userdata['user_id'] . ", $current_time, $forum_id, " . TOPIC_UNLOCKED . ", $topic_type, $topic_dl_type, $topic_vote)" : "UPDATE " . BB_TOPICS . " SET topic_title = '$post_subject', topic_type = $topic_type, topic_dl_type = $topic_dl_type " . ((@$post_data['edit_vote'] || !empty($poll_title)) ? ", topic_vote = " . $topic_vote : "") . " WHERE topic_id = $topic_id";

		if (!DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
		}

		if ($mode == 'newtopic')
		{
			$topic_id = DB()->sql_nextid();
		}
	}

	$edited_sql = ($mode == 'editpost' && !$post_data['last_post'] && $post_data['poster_post']) ? ", post_edit_time = $current_time, post_edit_count = post_edit_count + 1 " : "";

	if ($update_post_time && $mode == 'editpost' && $post_data['last_post'] && !$post_data['first_post'])
	{
		$edited_sql .= ", post_time = $current_time ";
		//lpt
		$result = DB()->sql_query("
			UPDATE ". BB_TOPICS ." SET
				topic_last_post_time = $current_time
			WHERE topic_id = $topic_id
			LIMIT 1
		");
	}

	$sql = ($mode != "editpost") ? "INSERT INTO " . BB_POSTS . " (topic_id, forum_id, poster_id, post_username, post_time, poster_ip) VALUES ($topic_id, $forum_id, " . $userdata['user_id'] . ", '$post_username', $current_time, '". USER_IP ."')" : "UPDATE " . BB_POSTS . " SET post_username = '$post_username'" . $edited_sql . " WHERE post_id = $post_id";
	if (!DB()->sql_query($sql))
	{
		message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
	}

	if ($mode != 'editpost')
	{
		$post_id = DB()->sql_nextid();
	}

	$sql = ($mode != 'editpost') ? "INSERT INTO " . BB_POSTS_TEXT . " (post_id, post_subject, post_text) VALUES ($post_id, '$post_subject', '$post_message')" : "UPDATE " . BB_POSTS_TEXT . " SET post_text = '$post_message', post_subject = '$post_subject' WHERE post_id = $post_id";
	if (!DB()->sql_query($sql))
	{
		message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
	}

	if ($userdata['user_id'] != BOT_UID)
	{
		add_search_words($post_id, stripslashes($post_message), stripslashes($post_subject));
	}

	update_post_html(array(
		'post_id'        => $post_id,
		'post_text'      => $post_message,
	));

	//
	// Add poll
	//
	if (($mode == 'newtopic' || ($mode == 'editpost' && $post_data['edit_poll'])) && !empty($poll_title) && count($poll_options) >= 2)
	{
		$sql = (!$post_data['has_poll']) ? "INSERT INTO " . BB_VOTE_DESC . " (topic_id, vote_text, vote_start, vote_length) VALUES ($topic_id, '$poll_title', $current_time, " . ($poll_length * 86400) . ")" : "UPDATE " . BB_VOTE_DESC . " SET vote_text = '$poll_title', vote_length = " . ($poll_length * 86400) . " WHERE topic_id = $topic_id";
		if (!DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
		}

		$delete_option_sql = '';
		$old_poll_result = array();
		if ($mode == 'editpost' && $post_data['has_poll'])
		{
			$sql = "SELECT vote_option_id, vote_result
				FROM " . BB_VOTE_RESULTS . "
				WHERE vote_id = $poll_id
				ORDER BY vote_option_id ASC";
			if (!($result = DB()->sql_query($sql)))
			{
				message_die(GENERAL_ERROR, 'Could not obtain vote data results for this topic', '', __LINE__, __FILE__, $sql);
			}

			while ($row = DB()->sql_fetchrow($result))
			{
				$old_poll_result[$row['vote_option_id']] = $row['vote_result'];

				if (!isset($poll_options[$row['vote_option_id']]))
				{
					$delete_option_sql .= ($delete_option_sql != '') ? ', ' . $row['vote_option_id'] : $row['vote_option_id'];
				}
			}
		}
		else
		{
			$poll_id = DB()->sql_nextid();
		}

		@reset($poll_options);

		$poll_option_id = 1;
		while (list($option_id, $option_text) = each($poll_options))
		{
			if (!empty($option_text))
			{
				$option_text = str_replace("\'", "''", htmlspecialchars($option_text));
				$poll_result = ($mode == "editpost" && isset($old_poll_result[$option_id])) ? $old_poll_result[$option_id] : 0;

				$sql = ($mode != "editpost" || !isset($old_poll_result[$option_id])) ? "INSERT INTO " . BB_VOTE_RESULTS . " (vote_id, vote_option_id, vote_option_text, vote_result) VALUES ($poll_id, $poll_option_id, '$option_text', $poll_result)" : "UPDATE " . BB_VOTE_RESULTS . " SET vote_option_text = '$option_text', vote_result = $poll_result WHERE vote_option_id = $option_id AND vote_id = $poll_id";
				if (!DB()->sql_query($sql))
				{
					message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
				}
				$poll_option_id++;
			}
		}

		if ($delete_option_sql != '')
		{
			$sql = "DELETE FROM " . BB_VOTE_RESULTS . "
				WHERE vote_option_id IN ($delete_option_sql)
					AND vote_id = $poll_id";
			if (!DB()->sql_query($sql))
			{
				message_die(GENERAL_ERROR, 'Error deleting pruned poll options', '', __LINE__, __FILE__, $sql);
			}
		}
	}

	meta_refresh(append_sid("viewtopic.php?" . POST_POST_URL . "=" . $post_id) . '#' . $post_id);
	$message = $lang['STORED'] . '<br /><br />' . sprintf($lang['CLICK_VIEW_MESSAGE'], '<a href="' . append_sid("viewtopic.php?" . POST_POST_URL . "=" . $post_id) . '#' . $post_id . '">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_FORUM'], '<a href="' . append_sid("viewforum.php?" . POST_FORUM_URL . "=$forum_id") . '">', '</a>');

    return $mode;
}

//
// Update post stats and details
//
function update_post_stats($mode, $post_data, $forum_id, $topic_id, $post_id, $user_id)
{
	$sign = ($mode == 'delete') ? '- 1' : '+ 1';
	$forum_update_sql = "forum_posts = forum_posts $sign";
	$topic_update_sql = '';

	if ($mode == 'delete')
	{
		if ($post_data['last_post'])
		{
			if ($post_data['first_post'])
			{
				$forum_update_sql .= ', forum_topics = forum_topics - 1';
			}
			else
			{
				$topic_update_sql .= 'topic_replies = topic_replies - 1';

				$sql = "SELECT MAX(post_id) AS last_post_id, MAX(post_time) AS topic_last_post_time
					FROM " . BB_POSTS . "
					WHERE topic_id = $topic_id";
				if (!($result = DB()->sql_query($sql)))
				{
					message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
				}

				if ($row = DB()->sql_fetchrow($result))
				{
					$topic_update_sql .= ", topic_last_post_id = {$row['last_post_id']}, topic_last_post_time = {$row['topic_last_post_time']}";
				}
			}

			if ($post_data['last_topic'])
			{
				$sql = "SELECT MAX(post_id) AS last_post_id
					FROM " . BB_POSTS . "
					WHERE forum_id = $forum_id";
				if (!($result = DB()->sql_query($sql)))
				{
					message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
				}

				if ($row = DB()->sql_fetchrow($result))
				{
					$forum_update_sql .= ($row['last_post_id']) ? ', forum_last_post_id = ' . $row['last_post_id'] : ', forum_last_post_id = 0';
				}
			}
		}
		else if ($post_data['first_post'])
		{
			$sql = "SELECT MIN(post_id) AS first_post_id
				FROM " . BB_POSTS . "
				WHERE topic_id = $topic_id";
			if (!($result = DB()->sql_query($sql)))
			{
				message_die(GENERAL_ERROR, 'Error in deleting post', '', __LINE__, __FILE__, $sql);
			}

			if ($row = DB()->sql_fetchrow($result))
			{
				$topic_update_sql .= 'topic_replies = topic_replies - 1, topic_first_post_id = ' . $row['first_post_id'];
			}
		}
		else
		{
			$topic_update_sql .= 'topic_replies = topic_replies - 1';
		}
	}
	else if ($mode != 'poll_delete')
	{
		$forum_update_sql .= ", forum_last_post_id = $post_id" . (($mode == 'newtopic') ? ", forum_topics = forum_topics $sign" : "");
		$topic_update_sql = "topic_last_post_id = $post_id, topic_last_post_time = ". TIMENOW . (($mode == 'reply') ? ", topic_replies = topic_replies $sign" : ", topic_first_post_id = $post_id");
	}
	else
	{
		$topic_update_sql .= 'topic_vote = 0';
	}

	$sql = "UPDATE " . BB_FORUMS . " SET
		$forum_update_sql
		WHERE forum_id = $forum_id";
	if (!DB()->sql_query($sql))
	{
		message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
	}

	if ($topic_update_sql != '')
	{
		$sql = "UPDATE " . BB_TOPICS . " SET
			$topic_update_sql
			WHERE topic_id = $topic_id";
		if (!DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
		}
	}

	if ($mode != 'poll_delete')
	{
		$sql = "UPDATE " . BB_USERS . "
			SET user_posts = user_posts $sign
			WHERE user_id = $user_id";
		if (!DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Error in posting', '', __LINE__, __FILE__, $sql);
		}
	}
}

//
// Delete a post/poll
//
function delete_post($mode, $post_data, &$message, &$meta, $forum_id, $topic_id, $post_id, $poll_id)
{
	global $lang;

	if ($mode == 'poll_delete')
	{
		$message = $lang['POLL_DELETE'];
		poll_delete($topic_id);
	}
	else
	{
		$message = $lang['DELETED'];
		post_delete($post_id);
	}

	if (!($mode == 'delete' && $post_data['first_post'] && $post_data['last_post']))
	{
		$message .= '<br /><br />';
		$message .= sprintf($lang['CLICK_RETURN_TOPIC'], '<a href="'. append_sid(TOPIC_URL . $topic_id) .'">', '</a>');
	}

	$message .= '<br /><br />';
	$message .= sprintf($lang['CLICK_RETURN_FORUM'], '<a href="'. append_sid(FORUM_URL . $forum_id) .'">', '</a>');
}

//
// Handle user notification on new post
//
function user_notification($mode, &$post_data, &$topic_title, &$forum_id, &$topic_id, &$post_id, &$notify_user)
{
	global $bb_cfg, $lang;
	global $userdata;

	if (!$bb_cfg['topic_notify_enabled'])
	{
		return;
	}

	$current_time = time();

	if ($mode != 'delete')
	{
		if ($mode == 'reply')
		{
			$sql = "SELECT ban_userid
				FROM " . BB_BANLIST;
			if (!($result = DB()->sql_query($sql)))
			{
				message_die(GENERAL_ERROR, 'Could not obtain banlist', '', __LINE__, __FILE__, $sql);
			}

			$user_id_sql = '';
			while ($row = DB()->sql_fetchrow($result))
			{
				if (isset($row['ban_userid']) && !empty($row['ban_userid']))
				{
					$user_id_sql .= ', ' . $row['ban_userid'];
				}
			}

			$sql = "SELECT u.user_id, u.user_email, u.user_lang
				FROM " . BB_TOPICS_WATCH . " tw, " . BB_USERS . " u
				WHERE tw.topic_id = $topic_id
					AND tw.user_id NOT IN (" . $userdata['user_id'] . ", " . BOT_UID . ", " . ANONYMOUS . $user_id_sql . ")
					AND tw.notify_status = " . TOPIC_WATCH_UN_NOTIFIED . "
					AND u.user_id = tw.user_id";
			if (!($result = DB()->sql_query($sql)))
			{
				message_die(GENERAL_ERROR, 'Could not obtain list of topic watchers', '', __LINE__, __FILE__, $sql);
			}

			$update_watched_sql = '';
			$bcc_list_ary = array();

			if ($row = DB()->sql_fetchrow($result))
			{
				// Sixty second limit
				@set_time_limit(60);

				do
				{
					if ($row['user_email'] != '')
					{
						$bcc_list_ary[$row['user_lang']][] = $row['user_email'];
					}
					$update_watched_sql .= ($update_watched_sql != '') ? ', ' . $row['user_id'] : $row['user_id'];
				}
				while ($row = DB()->sql_fetchrow($result));

				//
				// Let's do some checking to make sure that mass mail functions
				// are working in win32 versions of php.
				//
				if (preg_match('/[c-z]:\\\.*/i', getenv('PATH')) && !$bb_cfg['smtp_delivery'])
				{
					$ini_val = (@phpversion() >= '4.0.0') ? 'ini_get' : 'get_cfg_var';

					// We are running on windows, force delivery to use our smtp functions
					// since php's are broken by default
					if (!@$ini_val('sendmail_path'))
					{
						$bb_cfg['smtp_delivery'] = 1;
						$bb_cfg['smtp_host'] = @$ini_val('SMTP');
					}
				}

				if (sizeof($bcc_list_ary))
				{
					include(INC_DIR .'emailer.class.php');
					$emailer = new emailer($bb_cfg['smtp_delivery']);

					$script_name = preg_replace('/^\/?(.*?)\/?$/', '\1', trim($bb_cfg['script_path']));
					$script_name = ($script_name != '') ? $script_name . '/viewtopic.php' : 'viewtopic.php';
					$server_name = trim($bb_cfg['server_name']);
					$server_protocol = ($bb_cfg['cookie_secure']) ? 'https://' : 'http://';
					$server_port = ($bb_cfg['server_port'] <> 80) ? ':' . trim($bb_cfg['server_port']) . '/' : '/';

					$orig_word = array();
					$replacement_word = array();
					obtain_word_list($orig_word, $replacement_word);

					$emailer->from($bb_cfg['board_email']);
					$emailer->replyto($bb_cfg['board_email']);

					if(count($orig_word))
					{
						$topic_title = preg_replace($orig_word, $replacement_word, $topic_title);
                    }

					@reset($bcc_list_ary);
					while (list($user_lang, $bcc_list) = each($bcc_list_ary))
					{
						$emailer->use_template('topic_notify', $user_lang);

						for ($i = 0; $i < count($bcc_list); $i++)
						{
							$emailer->bcc($bcc_list[$i]);
						}

						// The Topic_reply_notification lang string below will be used
						// if for some reason the mail template subject cannot be read
						// ... note it will not necessarily be in the posters own language!
						$emailer->set_subject($lang['TOPIC_REPLY_NOTIFICATION']);

						// This is a nasty kludge to remove the username var ... till (if?)
						// translators update their templates
						$emailer->msg = preg_replace('#[ ]?{USERNAME}#', '', $emailer->msg);

						$emailer->assign_vars(array(
							'TOPIC_TITLE' => $topic_title,
							'SITENAME' => $bb_cfg['sitename'],
							'USERNAME'    => $userdata['username'],
							'EMAIL_SIG' => (!empty($bb_cfg['board_email_sig'])) ? str_replace('<br />', "\n", "-- \n" . $bb_cfg['board_email_sig']) : '',

							'U_TOPIC' => $server_protocol . $server_name . $server_port . $script_name . '?' . POST_POST_URL . "=$post_id#$post_id",
							'U_STOP_WATCHING_TOPIC' => $server_protocol . $server_name . $server_port . $script_name . '?' . POST_TOPIC_URL . "=$topic_id&unwatch=topic")
						);

						$emailer->send();
						$emailer->reset();
					}
				}
			}
			DB()->sql_freeresult($result);

			if ($update_watched_sql != '')
			{
				$sql = "UPDATE " . BB_TOPICS_WATCH . "
					SET notify_status = " . TOPIC_WATCH_NOTIFIED . "
					WHERE topic_id = $topic_id
						AND user_id IN ($update_watched_sql)";
				DB()->sql_query($sql);
			}
		}

		$sql = "SELECT topic_id
			FROM " . BB_TOPICS_WATCH . "
			WHERE topic_id = $topic_id
				AND user_id = " . $userdata['user_id'];
		if (!($result = DB()->sql_query($sql)))
		{
			message_die(GENERAL_ERROR, 'Could not obtain topic watch information', '', __LINE__, __FILE__, $sql);
		}

		$row = DB()->sql_fetchrow($result);

		if (!$notify_user && !empty($row['topic_id']))
		{
			$sql = "DELETE FROM " . BB_TOPICS_WATCH . "
				WHERE topic_id = $topic_id
					AND user_id = " . $userdata['user_id'];
			if (!DB()->sql_query($sql))
			{
				message_die(GENERAL_ERROR, 'Could not delete topic watch information', '', __LINE__, __FILE__, $sql);
			}
		}
		else if ($notify_user && empty($row['topic_id']))
		{
			$sql = "INSERT INTO " . BB_TOPICS_WATCH . " (user_id, topic_id, notify_status)
				VALUES (" . $userdata['user_id'] . ", $topic_id, 0)";
			if (!DB()->sql_query($sql))
			{
				message_die(GENERAL_ERROR, 'Could not insert topic watch information', '', __LINE__, __FILE__, $sql);
			}
		}
	}
}

function insert_post ($mode, $topic_id, $forum_id = '', $old_forum_id = '', $new_topic_id = '', $new_topic_title = '', $old_topic_id = '', $message = '', $poster_id = '')
{
	global $bb_cfg, $lang;
	global $userdata, $is_auth;

	require(DEFAULT_LANG_DIR .'lang_bot.php');

	if (!$topic_id) return;

	$post_username = $post_subject = $post_text = $poster_ip = '';

	$enable_bbcode = $enable_smilies = 0;
	$enable_sig = 1;

	$post_time = $current_time = time();
	$username = $userdata['username'];
	$user_id = $userdata['user_id'];

	if ($mode == 'after_move')
	{
		if (!$forum_id || !$old_forum_id) return;

		$sql = "SELECT forum_id, forum_name
			FROM ". BB_FORUMS ."
			WHERE forum_id IN($forum_id, $old_forum_id)";

		$forum_names = array();
		foreach (DB()->fetch_rowset($sql) as $row)
		{
			$forum_names[$row['forum_id']] = htmlCHR($row['forum_name']);
		}
		if (!$forum_names) return;

		$post_text = sprintf($lang['BOT_TOPIC_MOVED_FROM_TO'], "<a class=\"gen\" href=\"viewforum.php?f=$old_forum_id\">$forum_names[$old_forum_id]</a>", "<a class=\"gen\" href=\"viewforum.php?f=$forum_id\">$forum_names[$forum_id]</a>", "<a class=\"gen\" href=\"profile.php?mode=viewprofile&u=$user_id\">$username</a>");

		$poster_id = BOT_UID;
		$poster_ip = '7f000001';
	}
	else if ($mode == 'after_split_to_old')
	{
		$post_text = sprintf($lang['BOT_MESS_SPLITS'], "<a class=\"gen\" href=\"viewtopic.php?t=$new_topic_id\">". htmlCHR($new_topic_title) ."</a>", "<a class=\"gen\" href=\"profile.php?mode=viewprofile&u=$user_id\">$username</a>");

		$poster_id = BOT_UID;
		$poster_ip = '7f000001';
	}
	else if ($mode == 'after_split_to_new')
	{
		$sql = "SELECT t.topic_title, p.post_time
			FROM ". BB_TOPICS ." t, ". BB_POSTS ." p
			WHERE t.topic_id = $old_topic_id
				AND p.post_id = t.topic_first_post_id";

		if ($row = DB()->fetch_row($sql))
		{
			$old_topic_title = $row['topic_title'];
			$post_time = $row['post_time'] - 1;

			$post_text = sprintf($lang['BOT_TOPIC_SPLITS'], "<a class=\"gen\" href=\"viewtopic.php?t=$old_topic_id\">$old_topic_title</a>", "<a class=\"gen\" href=\"profile.php?mode=viewprofile&u=$user_id\">$username</a>");

			$poster_id = BOT_UID;
			$poster_ip = '7f000001';
		}
		else
		{
			return;
		}
	}
	else
	{
		return;
	}

	$post_columns = 'topic_id,  forum_id,  poster_id,   post_username,   post_time,   poster_ip,   enable_bbcode,  enable_smilies,  enable_sig';
	$post_values = "$topic_id, $forum_id, $poster_id, '$post_username', $post_time, '$poster_ip', $enable_bbcode, $enable_smilies, $enable_sig";

	DB()->query("INSERT INTO ". BB_POSTS ." ($post_columns) VALUES ($post_values)");

	$post_id = DB()->sql_nextid();
	$post_text = DB()->escape($post_text);

	$post_text_columns = 'post_id,   post_subject,    post_text';
	$post_text_values = "$post_id, '$post_subject, '$post_text'";

	DB()->query("INSERT INTO ". BB_POSTS_TEXT ." ($post_text_columns) VALUES ($post_text_values)");
}

function topic_review ($topic_id)
{
	global $bb_cfg, $lang, $template;

	// Fetch posts data
	$review_posts = DB()->fetch_rowset("
		SELECT
			p.*, pt.post_text
			IF(p.poster_id = ". ANONYMOUS .", p.post_username, u.username) AS username, u.user_id
		FROM
			". BB_POSTS      ." p,
			". BB_POSTS_TEXT ." pt,
			". BB_USERS      ." u
		WHERE
			    p.topic_id = ". (int) $topic_id ."
			AND pt.post_id = p.post_id
			AND u.user_id = p.poster_id
		ORDER BY p.post_time DESC
		LIMIT ". $bb_cfg['posts_per_page'] ."
	");

	// Topic posts block
	foreach ($review_posts as $i => $post)
	{
		$poster_name = ($post['username']) ? wbr($post['username']) : $lang['GUEST'];

		$template->assign_block_vars('review', array(
			'ROW_CLASS'      => !($i % 2) ? 'row1' : 'row2',
			'POSTER_NAME'    => $poster_name,
			'POSTER_NAME_JS' => addslashes($poster_name),
			'POST_DATE'      => bb_date($post['post_time'], $bb_cfg['post_date_format']),
			'MESSAGE'        => get_parsed_post($post),
		));
	}

	$template->assign_vars(array(
		'TPL_TOPIC_REVIEW' => (bool) $review_posts,
		'L_TOPIC_REVIEW'   => $lang['TOPIC_REVIEW'],
	));
}