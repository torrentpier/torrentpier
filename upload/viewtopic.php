<?php

define('IN_PHPBB', true);
define('BB_SCRIPT', 'topic');
define('BB_ROOT', './');
require(BB_ROOT ."common.php");
require(INC_DIR .'bbcode.php');

$datastore->enqueue(array(
	'ranks',
));

$page_cfg['load_tpl_vars'] = array(
	'post_buttons',
	'post_icons',
	'topic_icons',
);

$newest = $next_topic_id = 0;
$start  = isset($_GET['start']) ? abs(intval($_GET['start'])) : 0;
$topic_id = isset($_GET[POST_TOPIC_URL]) ? (int) $_GET[POST_TOPIC_URL] : 0;
$post_id  = (!$topic_id && isset($_GET[POST_POST_URL])) ? (int) $_GET[POST_POST_URL] : 0;
$porno_forums = array_flip(explode(',', $bb_cfg['porno_forums']));

// Start session
$user->session_start();

// Posts per page
$posts_per_page = $bb_cfg['posts_per_page'];
$select_ppp = '';

if ($userdata['session_admin'])
{
	if ($req_ppp = abs(intval(@$_REQUEST['ppp'])) AND in_array($req_ppp, $bb_cfg['allowed_posts_per_page']))
	{
		$posts_per_page = $req_ppp;
	}

	$select_ppp = array();
	foreach ($bb_cfg['allowed_posts_per_page'] as $ppp)
	{
		$select_ppp[$ppp] = $ppp;
	}
}

if (isset($_REQUEST['single']))
{
	$posts_per_page = 1;
}
else
{
	$start = floor($start/$posts_per_page) * $posts_per_page;
}

if (!$topic_id && !$post_id)
{
	bb_die($lang['TOPIC_POST_NOT_EXIST']);
}

$tracking_topics = get_tracks('topic');
$tracking_forums = get_tracks('forum');

// Find topic id if user requested a newer or older topic
if ($topic_id && isset($_GET['view']) && ($_GET['view'] == 'next' || $_GET['view'] == 'previous'))
{
	$sql_condition = ($_GET['view'] == 'next') ? '>' : '<';
	$sql_ordering = ($_GET['view'] == 'next') ? 'ASC' : 'DESC';

	$sql = "SELECT t.topic_id
		FROM ". BB_TOPICS ." t, ". BB_TOPICS ." t2
		WHERE t2.topic_id = $topic_id
			AND t.forum_id = t2.forum_id
			AND t.topic_moved_id = 0
			AND t.topic_last_post_id $sql_condition t2.topic_last_post_id
		ORDER BY t.topic_last_post_id $sql_ordering
		LIMIT 1";

	if ($row = DB()->fetch_row($sql))
	{
		$next_topic_id = $topic_id = $row['topic_id'];
	}
	else
	{
		$message = ($_GET['view'] == 'next') ? $lang['NO_NEWER_TOPICS'] : $lang['NO_OLDER_TOPICS'];
		bb_die($message);
	}
}

// Get forum/topic data
if ($topic_id)
{
	$sql = "SELECT t.*, f.*
		FROM ". BB_TOPICS ." t, ". BB_FORUMS ." f
		WHERE t.topic_id = $topic_id
			AND f.forum_id = t.forum_id
		LIMIT 1";
}
else if ($post_id)
{
	$sql = "SELECT t.*, f.*, p.post_time
		FROM ". BB_TOPICS ." t, ". BB_FORUMS ." f, ". BB_POSTS ." p
		WHERE p.post_id = $post_id
			AND t.topic_id = p.topic_id
			AND f.forum_id = t.forum_id
		LIMIT 1";
}
else
{
	bb_die($lang['TOPIC_POST_NOT_EXIST']);
}

if (!$t_data = DB()->fetch_row($sql))
{
	bb_die($lang['TOPIC_POST_NOT_EXIST']);
}

$forum_topic_data =& $t_data;
$topic_id = $t_data['topic_id'];
$forum_id = $t_data['forum_id'];

if ($userdata['session_admin'] && !empty($_REQUEST['mod']))
{
	if (IS_ADMIN)
	{
		$datastore->enqueue(array('viewtopic_forum_select'));
	}
}
if ($t_data['topic_attachment'])
{
	$datastore->enqueue(array(
		'attach_extensions',
	));
}

// Find newest post
if (($next_topic_id || @$_GET['view'] === 'newest') && !IS_GUEST && $topic_id)
{
	$post_time = 'post_time >= '. get_last_read($topic_id, $forum_id);
	$post_id_altern = ($next_topic_id) ? '' : ' OR post_id = '. $t_data['topic_last_post_id'];

	$sql = "SELECT post_id, post_time
		FROM ". BB_POSTS ."
		WHERE topic_id = $topic_id
			AND ($post_time $post_id_altern)
		ORDER BY post_time ASC
		LIMIT 1";

	if ($row = DB()->fetch_row($sql))
	{
		$post_id = $newest = $row['post_id'];
		$t_data['post_time'] = $row['post_time'];
	}
}

if ($post_id && !empty($t_data['post_time']) && ($t_data['topic_replies'] + 1) > $posts_per_page)
{
	$sql = "SELECT COUNT(post_id) AS prev_posts
		FROM ". BB_POSTS ."
		WHERE topic_id = $topic_id
			AND post_time <= {$t_data['post_time']}";

	if ($row = DB()->fetch_row($sql))
	{
		$t_data['prev_posts'] = $row['prev_posts'];
	}
}

// Auth check
$is_auth = auth(AUTH_ALL, $forum_id, $userdata, $t_data);

if (!$is_auth['auth_read'])
{
	if (IS_GUEST)
	{
		$redirect = ($post_id) ? POST_POST_URL . "=$post_id" : POST_TOPIC_URL . "=$topic_id";
		$redirect .= ($start) ? "&start=$start" : '';
		redirect("login.php?redirect=viewtopic.php&$redirect");
	}
	bb_die($lang['TOPIC_POST_NOT_EXIST']);
}

$forum_name  = $t_data['forum_name'];
$topic_title = $t_data['topic_title'];
$topic_id    = $t_data['topic_id'];
$topic_time  = $t_data['topic_time'];

$moderation = (!empty($_REQUEST['mod']) && $is_auth['auth_mod']);

// Redirect to login page if not admin session
$mod_redirect_url = '';

if ($is_auth['auth_mod'])
{
	$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : $_SERVER['REQUEST_URI'];
	$redirect = url_arg($redirect, 'mod', 1, '&');
	$mod_redirect_url = "login.php?redirect=$redirect&admin=1";

	if ($moderation && !$userdata['session_admin'])
	{
		redirect($mod_redirect_url);
	}
}

if ($moderation)
{
	if (IS_ADMIN)
	{
		if (!$forum_select = $datastore->get('viewtopic_forum_select'))
		{
			$datastore->update('viewtopic_forum_select');
			$forum_select = $datastore->get('viewtopic_forum_select');
		}
		$forum_select_html = $forum_select['viewtopic_forum_select'];
	}
	else
	{
		$not_auth_forums_csv = $user->get_not_auth_forums(AUTH_VIEW);
		$forum_select_html = get_forum_select(explode(',', $not_auth_forums_csv), 'new_forum_id');
	}
	$template->assign_vars(array(
		'S_FORUM_SELECT' => $forum_select_html,
	));
}

if ($parent_id = $t_data['forum_parent'])
{
	if (!$forums = $datastore->get('cat_forums'))
	{
		$datastore->update('cat_forums');
		$forums = $datastore->get('cat_forums');
	}

	$template->assign_vars(array(
		'HAS_PARENT_FORUM'  => true,
		'PARENT_FORUM_HREF'	=> FORUM_URL . $parent_id,
		'PARENT_FORUM_NAME' => htmlCHR($forums['f'][$parent_id]['forum_name']),
	));
	unset($forums);
}
$datastore->rm('cat_forums');

if ($post_id && !empty($t_data['prev_posts']))
{
	$start = floor(($t_data['prev_posts'] - 1) / $posts_per_page) * $posts_per_page;
}

//
// Is user watching this thread?
//
$can_watch_topic = $is_watching_topic = false;

if ($bb_cfg['topic_notify_enabled'])
{
	if( $userdata['session_logged_in'] )
	{
		$can_watch_topic = TRUE;

		$sql = "SELECT notify_status
			FROM " . BB_TOPICS_WATCH . "
			WHERE topic_id = $topic_id
				AND user_id = " . $userdata['user_id'];

		if ($row = DB()->fetch_row($sql))
		{
			if ( isset($_GET['unwatch']) )
			{
				if ( $_GET['unwatch'] == 'topic' )
				{
					$is_watching_topic = 0;

					$sql = "DELETE FROM " . BB_TOPICS_WATCH . "
						WHERE topic_id = $topic_id
							AND user_id = " . $userdata['user_id'];
					if ( !($result = DB()->sql_query($sql)) )
					{
						message_die(GENERAL_ERROR, "Could not delete topic watch information", '', __LINE__, __FILE__, $sql);
					}
				}

				$message = $lang['NO_LONGER_WATCHING'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_TOPIC'], '<a href="' . "viewtopic.php?t=$topic_id&amp;start=$start" . '">', '</a>');
				bb_die($message);
			}
			else
			{
				$is_watching_topic = TRUE;

				if ( $row['notify_status'] )
				{
					$sql = "UPDATE " . BB_TOPICS_WATCH . "
						SET notify_status = 0
						WHERE topic_id = $topic_id
							AND user_id = " . $userdata['user_id'];
					if ( !($result = DB()->sql_query($sql)) )
					{
						message_die(GENERAL_ERROR, "Could not update topic watch information", '', __LINE__, __FILE__, $sql);
					}
				}
			}
		}
		else
		{
			if ( isset($_GET['watch']) )
			{
				if ( $_GET['watch'] == 'topic' )
				{
					$is_watching_topic = TRUE;

					$sql = "INSERT INTO " . BB_TOPICS_WATCH . " (user_id, topic_id, notify_status)
						VALUES (" . $userdata['user_id'] . ", $topic_id, 0)";
					if ( !($result = DB()->sql_query($sql)) )
					{
						message_die(GENERAL_ERROR, "Could not insert topic watch information", '', __LINE__, __FILE__, $sql);
					}
				}

				$message = $lang['YOU_ARE_WATCHING'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_TOPIC'], '<a href="' . "viewtopic.php?t=$topic_id&amp;start=$start" . '">', '</a>');
				bb_die($message);
			}
			else
			{
				$is_watching_topic = 0;
			}
		}
	}
	else
	{
		if ( isset($_GET['unwatch']) )
		{
			if ( $_GET['unwatch'] == 'topic' )
			{
				redirect("login.php?redirect=viewtopic.php&t=$topic_id&unwatch=topic");
			}
		}
	}
}

// Generate a 'Show posts in previous x days' select box. If the postdays var is POSTed
// then get it's value, find the number of topics with dates newer than it (to properly
// handle pagination) and alter the main query
$post_days = 0;
$limit_posts_time = '';
$total_replies = $t_data['topic_replies'] + 1;

if (!empty($_REQUEST['postdays']))
{
	if ($post_days = abs(intval($_REQUEST['postdays'])))
	{
		if (!empty($_POST['postdays']))
		{
			$start = 0;
		}
		$min_post_time = TIMENOW - ($post_days*86400);

		$sql = "SELECT COUNT(p.post_id) AS num_posts
			FROM " . BB_TOPICS . " t, " . BB_POSTS . " p
			WHERE t.topic_id = $topic_id
				AND p.topic_id = t.topic_id
				AND p.post_time > $min_post_time";

		$total_replies = ($row = DB()->fetch_row($sql)) ? $row['num_posts'] : 0;
		$limit_posts_time = "AND p.post_time >= $min_post_time ";
	}
}

// Decide how to order the post display
$post_order = (isset($_POST['postorder']) && $_POST['postorder'] !== 'asc') ? 'desc' : 'asc';

//
// Go ahead and pull all data for this topic
//
$sql = "
	SELECT
	  u.username, u.user_id, u.user_posts, u.user_from, u.user_from_flag,
	  u.user_regdate, u.user_rank, u.user_sig, u.user_sig_bbcode_uid,
	  u.user_avatar, u.user_avatar_type, u.user_allowavatar,
	  p.*,
	  h.post_html, IF(h.post_html IS NULL, pt.post_text, NULL) AS post_text,
	  pt.post_subject, pt.bbcode_uid
	FROM      ". BB_POSTS      ." p
	LEFT JOIN ". BB_USERS      ." u  ON(u.user_id = p.poster_id)
	LEFT JOIN ". BB_POSTS_TEXT ." pt ON(pt.post_id = p.post_id)
	LEFT JOIN ". BB_POSTS_HTML ." h  ON(h.post_id = p.post_id)
	WHERE
	    p.topic_id = $topic_id
	  $limit_posts_time
	GROUP BY
	  p.post_id
	ORDER BY
	  p.post_time
	  $post_order
	LIMIT
	  $start, $posts_per_page
";

if ($postrow = DB()->fetch_rowset($sql))
{
	$total_posts = count($postrow);
}
else
{
   bb_die($lang['NO_POSTS_TOPIC']);
}

if (!$ranks = $datastore->get('ranks'))
{
	$datastore->update('ranks');
	$ranks = $datastore->get('ranks');
}

//
// Define censored word matches
//
$orig_word = array();
$replacement_word = array();
obtain_word_list($orig_word, $replacement_word);

//
// Censor topic title
//
if ( count($orig_word) )
{
	$topic_title = preg_replace($orig_word, $replacement_word, $topic_title);
}

//
// Post, reply and other URL generation for
// templating vars
//
$new_topic_url = "posting.php?mode=newtopic&amp;f=$forum_id";
$new_topic_url .= ($t_data['topic_tpl_id']) ? '&tpl=1' : '';
$reply_topic_url = "posting.php?mode=reply&amp;t=$topic_id";
$view_forum_url = "viewforum.php?f=$forum_id";
$view_prev_topic_url = "viewtopic.php?t=$topic_id&amp;view=previous#newest";
$view_next_topic_url = "viewtopic.php?t=$topic_id&amp;view=next#newest";

$reply_img = ( $t_data['forum_status'] == FORUM_LOCKED || $t_data['topic_status'] == TOPIC_LOCKED ) ? $images['reply_locked'] : $images['reply_new'];
$reply_alt = ( $t_data['forum_status'] == FORUM_LOCKED || $t_data['topic_status'] == TOPIC_LOCKED ) ? $lang['TOPIC_LOCKED_SHORT'] : $lang['REPLY_TO_TOPIC'];

// Set 'body' template for attach_mod
$template->set_filenames(array('body' => 'viewtopic.tpl'));

//
// User authorisation levels output
//
$s_auth_can = ( ( $is_auth['auth_post'] ) ? $lang['RULES_POST_CAN'] : $lang['RULES_POST_CANNOT'] ) . '<br />';
$s_auth_can .= ( ( $is_auth['auth_reply'] ) ? $lang['RULES_REPLY_CAN'] : $lang['RULES_REPLY_CANNOT'] ) . '<br />';
$s_auth_can .= ( ( $is_auth['auth_edit'] ) ? $lang['RULES_EDIT_CAN'] : $lang['RULES_EDIT_CANNOT'] ) . '<br />';
$s_auth_can .= ( ( $is_auth['auth_delete'] ) ? $lang['RULES_DELETE_CAN'] : $lang['RULES_DELETE_CANNOT'] ) . '<br />';
$s_auth_can .= ( ( $is_auth['auth_vote'] ) ? $lang['RULES_VOTE_CAN'] : $lang['RULES_VOTE_CANNOT'] ) . '<br />';
$s_auth_can .= ( ($is_auth['auth_attachments'] ) ? $lang['RULES_ATTACH_CAN'] : $lang['RULES_ATTACH_CANNOT'] ) . '<br />';
$s_auth_can .= ( ($is_auth['auth_download'] ) ? $lang['RULES_DOWNLOAD_CAN'] : $lang['RULES_DOWNLOAD_CANNOT'] ) . '<br />';

$topic_mod = '';

if ( $is_auth['auth_mod'] )
{
	$s_auth_can .= $lang['RULES_MODERATE'];

	$topic_mod .= "<a href=\"modcp.php?" . POST_TOPIC_URL . "=$topic_id&amp;mode=delete&amp;sid=" . $userdata['session_id'] . '"><img src="' . $images['topic_mod_delete'] . '" alt="' . $lang['DELETE_TOPIC'] . '" title="' . $lang['DELETE_TOPIC'] . '" border="0" /></a>&nbsp;';

	$topic_mod .= "<a href=\"modcp.php?" . POST_TOPIC_URL . "=$topic_id&amp;mode=move&amp;sid=" . $userdata['session_id'] . '"><img src="' . $images['topic_mod_move'] . '" alt="' . $lang['MOVE_TOPIC'] . '" title="' . $lang['MOVE_TOPIC'] . '" border="0" /></a>&nbsp;';

	$topic_mod .= ( $t_data['topic_status'] == TOPIC_UNLOCKED ) ? "<a href=\"modcp.php?" . POST_TOPIC_URL . "=$topic_id&amp;mode=lock&amp;sid=" . $userdata['session_id'] . '"><img src="' . $images['topic_mod_lock'] . '" alt="' . $lang['LOCK_TOPIC'] . '" title="' . $lang['LOCK_TOPIC'] . '" border="0" /></a>&nbsp;' : "<a href=\"modcp.php?" . POST_TOPIC_URL . "=$topic_id&amp;mode=unlock&amp;sid=" . $userdata['session_id'] . '"><img src="' . $images['topic_mod_unlock'] . '" alt="' . $lang['UNLOCK_TOPIC'] . '" title="' . $lang['UNLOCK_TOPIC'] . '" border="0" /></a>&nbsp;';

	$topic_mod .= "<a href=\"modcp.php?" . POST_TOPIC_URL . "=$topic_id&amp;mode=split&amp;sid=" . $userdata['session_id'] . '"><img src="' . $images['topic_mod_split'] . '" alt="' . $lang['SPLIT_TOPIC'] . '" title="' . $lang['SPLIT_TOPIC'] . '" border="0" /></a>&nbsp;';
	//bt
	if ($t_data['allow_dl_topic'] || $t_data['topic_dl_type'] == TOPIC_DL_TYPE_DL || IS_ADMIN)
	{
		if ($t_data['topic_dl_type'] == TOPIC_DL_TYPE_DL)
		{
			$topic_mod .= "<a href=\"modcp.php?" . POST_TOPIC_URL . "=$topic_id&amp;mode=unset_download&amp;sid=" . $userdata['session_id'] . '"><img src="' . $images['topic_normal'] . '" alt="' . $lang['UNSET_DL_STATUS'] . '" title="' . $lang['UNSET_DL_STATUS'] . '" border="0" /></a>';
		}
		else
		{
			$topic_mod .= "<a href=\"modcp.php?" . POST_TOPIC_URL . "=$topic_id&amp;mode=set_download&amp;sid=" . $userdata['session_id'] . '"><img src="' . $images['topic_dl'] . '" alt="' . $lang['SET_DL_STATUS'] . '" title="' . $lang['SET_DL_STATUS'] . '" border="0" /></a>';
		}
	}
	//bt end
}
//bt
else if (($t_data['topic_poster'] == $userdata['user_id']) && $userdata['session_logged_in'] && $t_data['self_moderated'])
{
	$topic_mod .= "<a href=\"modcp.php?" . POST_TOPIC_URL . "=$topic_id&amp;mode=move&amp;sid=" . $userdata['session_id'] . '"><img src="' . $images['topic_mod_move'] . '" alt="' . $lang['MOVE_TOPIC'] . '" title="' . $lang['MOVE_TOPIC'] . '" border="0" /></a>&nbsp;';
}
//bt end

// Report
//
// Get report topic module and create report link
//
require_once(INC_DIR ."functions_report.php");
$report_topic = report_modules('name', 'report_topic');

if ($report_topic && $report_topic->auth_check('auth_write'))
{
	if ($forum_topic_data['topic_reported'])
	{
		$report_auth = ($userdata['user_level'] == ADMIN || (!$bb_cfg['report_list_admin'] && (!$bb_cfg['report_subject_auth'] || $is_auth['auth_mod'])));
		if ($report_topic->auth_check('auth_view') && $is_auth)
		{
			$target = ($bb_cfg['report_new_window']) ? ' target="_blank"' : '';
			$s_report_topic ='&nbsp;<a href="' . append_sid("report.php?mode=reported&amp;" . POST_CAT_URL . '=' . $report_topic->id . "&amp;id=$topic_id") . '"' . $target . '><img src="' . $images['topic_mod_reported'] . '" alt="' . $report_topic->lang['DUPLICATE_REPORT'] . '" title="' . $report_topic->lang['DUPLICATE_REPORT'] . '" border="0" /></a>&nbsp;';
		}
		else
		{
			$s_report_topic = '&nbsp;<img src="' . $images['topic_mod_reported'] . '" alt="' . $report_topic->lang['DUPLICATE_REPORT'] . '" title="' . $report_topic->lang['DUPLICATE_REPORT'] . '" border="0" />&nbsp;';
		}
	}
	else
	{
		$s_report_topic = '&nbsp;<a href="' . append_sid("report.php?mode=" . $report_topic->mode . "&amp;id=$topic_id") . '"><img src="' . $images['topic_mod_report'] . '" alt="' . $report_topic->lang['WRITE_REPORT'] . '" title="' . $report_topic->lang['WRITE_REPORT'] . '" border="0" /></a>&nbsp;';
	}

	$topic_mod .= $s_report_topic;
	$template->assign_var('S_REPORT_TOPIC', $s_report_topic);
}
// Report [END]

//
// Topic watch information
//
$s_watching_topic = $s_watching_topic_img = '';
if ( $can_watch_topic )
{
	if ( $is_watching_topic )
	{
		$s_watching_topic = "<a href=\"viewtopic.php?" . POST_TOPIC_URL . "=$topic_id&amp;unwatch=topic&amp;start=$start&amp;sid=" . $userdata['session_id'] . '">' . $lang['STOP_WATCHING_TOPIC'] . '</a>';
		$s_watching_topic_img = ( isset($images['topic_un_watch']) ) ? "<a href=\"viewtopic.php?" . POST_TOPIC_URL . "=$topic_id&amp;unwatch=topic&amp;start=$start&amp;sid=" . $userdata['session_id'] . '"><img src="' . $images['topic_un_watch'] . '" alt="' . $lang['STOP_WATCHING_TOPIC'] . '" title="' . $lang['STOP_WATCHING_TOPIC'] . '" border="0"></a>' : '';
	}
	else
	{
		$s_watching_topic = "<a href=\"viewtopic.php?" . POST_TOPIC_URL . "=$topic_id&amp;watch=topic&amp;start=$start&amp;sid=" . $userdata['session_id'] . '">' . $lang['START_WATCHING_TOPIC'] . '</a>';
		$s_watching_topic_img = ( isset($images['Topic_watch']) ) ? "<a href=\"viewtopic.php?" . POST_TOPIC_URL . "=$topic_id&amp;watch=topic&amp;start=$start&amp;sid=" . $userdata['session_id'] . '"><img src="' . $images['Topic_watch'] . '" alt="' . $lang['START_WATCHING_TOPIC'] . '" title="' . $lang['START_WATCHING_TOPIC'] . '" border="0"></a>' : '';
	}
}

// If we've got a hightlight set pass it on to pagination,
$pg_url = TOPIC_URL . $topic_id;
$pg_url .= ($post_days) ? "&amp;postdays=$post_days" : '';
$pg_url .= ($post_order != 'asc') ? "&amp;postorder=$post_order" : '';
$pg_url .= isset($_REQUEST['single']) ? "&amp;single=1" : '';
$pg_url .= ($moderation) ? "&amp;mod=1" : '';
$pg_url .= ($posts_per_page != $bb_cfg['posts_per_page']) ? "&amp;ppp=$posts_per_page" : '';

$pagination = generate_pagination($pg_url, $total_replies, $posts_per_page, $start);

//
// Selects
//
$sel_previous_days = array(
	0   => $lang['ALL_POSTS'],
	1   => $lang['1_DAY'],
	7   => $lang['7_DAYS'],
	14  => $lang['2_WEEKS'],
	30  => $lang['1_MONTH'],
	90  => $lang['3_MONTHS'],
	180 => $lang['6_MONTHS'],
	364 => $lang['1_YEAR'],
);

$sel_post_order_ary = array(
	$lang['OLDEST_FIRST'] => 'asc',
	$lang['NEWEST_FIRST'] => 'desc',
);

//
// Send vars to template
//
$template->assign_vars(array(
	'PAGE_URL'            => $pg_url,
	'PAGE_URL_PPP'        => url_arg($pg_url, 'ppp', null),
	'PAGE_START'          => $start,
	'SHOW_JUMPBOX'        => true,

	'FORUM_ID'            => $forum_id,
	'FORUM_NAME'          => htmlCHR($forum_name),
	'TOPIC_ID'            => $topic_id,
	'PAGE_TITLE'          => $topic_title,
	'TOPIC_TITLE'         => wbr($topic_title),
	'PAGINATION'          => $pagination,
	'PAGE_NUMBER'         => sprintf($lang['PAGE_OF'], ( floor($start/$posts_per_page) + 1 ), ceil( $total_replies / $posts_per_page )),
	'PORNO_FORUM'         => isset($porno_forums[$forum_id]),
	'REPLY_IMG'           => $reply_img,
	'SHOW_BOT_NICK'       => $bb_cfg['show_bot_nick'],
	'T_POST_REPLY'        => $reply_alt,

	'HIDE_FLAGS'          => ($user->opt_js['h_flag'] && $bb_cfg['show_poster_flag']),
	'HIDE_AVATAR'         => $user->opt_js['h_av'],
	'HIDE_RANK_IMG'       => ($user->opt_js['h_rnk_i'] && $bb_cfg['show_rank_image']),
	'HIDE_POST_IMG'       => $user->opt_js['h_post_i'],
	'HIDE_SMILE'          => $user->opt_js['h_smile'],
	'HIDE_SIGNATURE'      => $user->opt_js['h_sig'],
	'SPOILER_OPENED'      => $user->opt_js['sp_op'],

	'HIDE_FLAGS_DIS'      => !$bb_cfg['show_poster_flag'],
	'HIDE_RANK_IMG_DIS'   => !$bb_cfg['show_rank_image'],

	'AUTH_MOD'            => $is_auth['auth_mod'],
	'IN_MODERATION'       => $moderation,
	'L_SELECT_PPP'        => $lang['SELECT_POSTS_PER_PAGE'],
	'SELECT_PPP'          => ($moderation && $select_ppp && $total_replies > $posts_per_page) ? build_select('ppp', $select_ppp, $posts_per_page, null, null, 'onchange="$(\'#ppp\').submit();"') : '',

	'S_SELECT_POST_DAYS'  => build_select('postdays', array_flip($sel_previous_days), $post_days),
	'S_SELECT_POST_ORDER' => build_select('postorder', $sel_post_order_ary, $post_order),
	'S_POST_DAYS_ACTION'  => "viewtopic.php?t=$topic_id&amp;start=$start",
	'S_AUTH_LIST'         => $s_auth_can,
	'S_TOPIC_ADMIN'       => $topic_mod,
	'S_WATCH_TOPIC'       => $s_watching_topic,
	'S_WATCH_TOPIC_IMG'   => $s_watching_topic_img,
	'U_VIEW_TOPIC'        => TOPIC_URL . $topic_id,
	'U_VIEW_FORUM'        => $view_forum_url,
	'U_VIEW_OLDER_TOPIC'  => $view_prev_topic_url,
	'U_VIEW_NEWER_TOPIC'  => $view_next_topic_url,
	'U_POST_NEW_TOPIC'    => $new_topic_url,
	'U_POST_REPLY_TOPIC'  => $reply_topic_url,
	'U_SEARCH_SELF'       => "search.php?uid={$userdata['user_id']}&t=$topic_id&dm=1",

));

// Does this topic contain DL-List?
$template->assign_vars(array(
	'SHOW_TOR_ACT'    => false,
	'PEERS_FULL_LINK' => false,
	'DL_LIST_HREF'    => TOPIC_URL ."$topic_id&amp;dl=names&amp;spmode=full",
));
require(INC_DIR .'torrent_show_dl_list.php');

//
// Does this topic contain a poll?
//
if ( !empty($t_data['topic_vote']) )
{
	$s_hidden_fields = '';

	$sql = "SELECT vd.vote_id, vd.vote_text, vd.vote_start, vd.vote_length, vr.vote_option_id, vr.vote_option_text, vr.vote_result
		FROM " . BB_VOTE_DESC . " vd, " . BB_VOTE_RESULTS . " vr
		WHERE vd.topic_id = $topic_id
			AND vr.vote_id = vd.vote_id
		ORDER BY vr.vote_option_id ASC";
	if ( !($result = DB()->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, "Could not obtain vote data for this topic", '', __LINE__, __FILE__, $sql);
	}

	if ( $vote_info = DB()->sql_fetchrowset($result) )
	{
		DB()->sql_freeresult($result);
		$vote_options = count($vote_info);

		$vote_id = $vote_info[0]['vote_id'];
		$vote_title = $vote_info[0]['vote_text'];

		$sql = "SELECT vote_id
			FROM " . BB_VOTE_USERS . "
			WHERE vote_id = $vote_id
				AND vote_user_id = " . intval($userdata['user_id']);
		if ( !($result = DB()->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, "Could not obtain user vote data for this topic", '', __LINE__, __FILE__, $sql);
		}

		$user_voted = ( $row = DB()->sql_fetchrow($result) ) ? TRUE : 0;
		DB()->sql_freeresult($result);

		if ( isset($_GET['vote']) || isset($_POST['vote']) )
		{
			$view_result = ( ( ( isset($_GET['vote']) ) ? $_GET['vote'] : $_POST['vote'] ) == 'viewresult' ) ? TRUE : 0;
		}
		else
		{
			$view_result = 0;
		}

		$poll_expired = ( $vote_info[0]['vote_length'] ) ? ( ( $vote_info[0]['vote_start'] + $vote_info[0]['vote_length'] < time() ) ? TRUE : 0 ) : 0;

		if ( $user_voted || $view_result || $poll_expired || !$is_auth['auth_vote'] || $t_data['topic_status'] == TOPIC_LOCKED )
		{
			$vote_results_sum = 0;

			for($i = 0; $i < $vote_options; $i++)
			{
				$vote_results_sum += $vote_info[$i]['vote_result'];
			}

			$vote_graphic = 0;
			$vote_graphic_max = count($images['voting_graphic']);

			for($i = 0; $i < $vote_options; $i++)
			{
				$vote_percent = ( $vote_results_sum > 0 ) ? $vote_info[$i]['vote_result'] / $vote_results_sum : 0;
				$vote_graphic_length = round($vote_percent * $bb_cfg['vote_graphic_length']);

				$vote_graphic_img = $images['voting_graphic'][$vote_graphic];
				$vote_graphic = ($vote_graphic < $vote_graphic_max - 1) ? $vote_graphic + 1 : 0;

				if ( count($orig_word) )
				{
					$vote_info[$i]['vote_option_text'] = preg_replace($orig_word, $replacement_word, $vote_info[$i]['vote_option_text']);
				}

				$template->assign_block_vars("poll_option", array(
					'POLL_OPTION_CAPTION' => $vote_info[$i]['vote_option_text'],
					'POLL_OPTION_RESULT' => $vote_info[$i]['vote_result'],
					'POLL_OPTION_PERCENT' => sprintf("%.1d%%", ($vote_percent * 100)),

					'POLL_OPTION_IMG' => $vote_graphic_img,
					'POLL_OPTION_IMG_WIDTH' => $vote_graphic_length)
				);
			}

			$template->assign_vars(array(
				'TPL_POLL_RESULT' => true,
				'TOTAL_VOTES' => $vote_results_sum,
			));
		}
		else
		{
			for($i = 0; $i < $vote_options; $i++)
			{
				if ( count($orig_word) )
				{
					$vote_info[$i]['vote_option_text'] = preg_replace($orig_word, $replacement_word, $vote_info[$i]['vote_option_text']);
				}

				$template->assign_block_vars("poll_option", array(
					'POLL_OPTION_ID' => $vote_info[$i]['vote_option_id'],
					'POLL_OPTION_CAPTION' => $vote_info[$i]['vote_option_text'])
				);
			}

			$template->assign_vars(array(
				'TPL_POLL_BALLOT' => true,
				'U_VIEW_RESULTS' => TOPIC_URL ."$topic_id&amp;vote=viewresult",
			));

			$s_hidden_fields = '<input type="hidden" name="topic_id" value="' . $topic_id . '" /><input type="hidden" name="mode" value="vote" />';
		}

		if ( count($orig_word) )
		{
			$vote_title = preg_replace($orig_word, $replacement_word, $vote_title);
		}

		$s_hidden_fields .= '<input type="hidden" name="sid" value="' . $userdata['session_id'] . '" />';

		$template->assign_vars(array(
			'TOPIC_HAS_POLL' => true,
			'POLL_QUESTION'  => $vote_title,

			'S_HIDDEN_FIELDS' => $s_hidden_fields,
			'S_POLL_ACTION' => "posting.php?mode=vote&amp;t=$topic_id",
		));
	}
}

if ($t_data['topic_attachment'])
{
	require(BB_ROOT .'attach_mod/attachment_mod.php');
	init_display_post_attachments($t_data['topic_attachment']);
}

//
// Update the topic view counter
//
$sql = "INSERT INTO ". BUF_TOPIC_VIEW ."
	(topic_id,  topic_views) VALUES
	($topic_id, 1)
	ON DUPLICATE KEY UPDATE
	topic_views = topic_views + 1";

if ( !DB()->sql_query($sql) )
{
	message_die(GENERAL_ERROR, "Could not update topic views.", '', __LINE__, __FILE__, $sql);
}

$prev_post_time = $max_post_time = 0;

// Report
//
// Get report post module
//
require_once(INC_DIR ."functions_report.php");
$report_post = report_modules('name', 'report_post');
// Report [END]

//
// Okay, let's do the loop, yeah come on baby let's do the loop
// and it goes like this ...
//
for($i = 0; $i < $total_posts; $i++)
{
	$poster_id = $postrow[$i]['user_id'];
	$poster = ( $poster_id == ANONYMOUS ) ? $lang['GUEST'] : $postrow[$i]['username'];

	$post_date = bb_date($postrow[$i]['post_time'], $bb_cfg['post_date_format']);
	$max_post_time = max($max_post_time, $postrow[$i]['post_time']);

	$poster_posts = ( $postrow[$i]['user_id'] != ANONYMOUS ) ? $postrow[$i]['user_posts'] : '';

	$poster_from = ( $postrow[$i]['user_from'] && $postrow[$i]['user_id'] != ANONYMOUS ) ? $postrow[$i]['user_from'] : '';

// FLAGHACK-start
	$poster_from_flag = ( !$user->opt_js['h_flag'] && $postrow[$i]['user_from_flag'] && $postrow[$i]['user_id'] != ANONYMOUS ) ? make_user_flag($postrow[$i]['user_from_flag']) : "";
// FLAGHACK-end

	$poster_joined = ( $postrow[$i]['user_id'] != ANONYMOUS ) ? $lang['JOINED'] . ': ' . bb_date($postrow[$i]['user_regdate'], $lang['DATE_FORMAT']) : '';



	$poster_longevity = ( $postrow[$i]['user_id'] != ANONYMOUS ) ? delta_time($postrow[$i]['user_regdate']) : '';

	$poster_avatar = '';
	if ( !$user->opt_js['h_av'] && $poster_id != ANONYMOUS )
	{
		$poster_avatar = get_avatar($postrow[$i]['user_avatar'], $postrow[$i]['user_avatar_type'], $postrow[$i]['user_allowavatar']);
	}

	//
	// Generate ranks, set them to empty string initially.
	//
	$poster_rank = $rank_image = '';

	if (!$user->opt_js['h_rnk_i'] AND $user_rank = $postrow[$i]['user_rank'] AND isset($ranks[$user_rank]))
	{
		$rank_image = ($bb_cfg['show_rank_image'] && $ranks[$user_rank]['rank_image']) ? '<img src="'. $ranks[$user_rank]['rank_image'] .'" alt="" title="" border="0" />' : '';
		$poster_rank = ($bb_cfg['show_rank_text']) ? $ranks[$user_rank]['rank_title'] : '';
	}

	//
	// Handle anon users posting with usernames
	//
	if ( $poster_id == ANONYMOUS && $postrow[$i]['post_username'] != '' )
	{
		$poster = $postrow[$i]['post_username'];
	}

	// Buttons
	$pm_btn = '';
	$profile_btn = '';

	$delpost_btn = '';
	$edit_btn = '';
	$ip_btn = '';
	$quote_btn = '';

	if ($poster_id != ANONYMOUS)
	{
		// profile
		$profile_btn = true;
		// pm
		$pm_btn = true;
	}

	if ($poster_id != BOT_UID)
	{
		// Quote
		$quote_btn = true;
		// Edit
		$edit_btn = (($userdata['user_id'] == $poster_id && $is_auth['auth_edit']) || $is_auth['auth_mod']);
		// IP
		$ip_btn = ($is_auth['auth_mod'] || IS_MOD);
	}
	// Delete
	$delpost_btn = ($postrow[$i]['post_id'] != $t_data['topic_first_post_id'] && ($is_auth['auth_mod'] || ($userdata['user_id'] == $poster_id && $is_auth['auth_delete'] && $t_data['topic_last_post_id'] == $postrow[$i]['post_id'] && $postrow[$i]['post_time'] + 3600*3 > TIMENOW)));

	//
	// Parse message and sig
	//
	$post_subject = ( $postrow[$i]['post_subject'] != '' ) ? $postrow[$i]['post_subject'] : '';

	$message = get_parsed_post($postrow[$i]);

	$bbcode_uid = $postrow[$i]['bbcode_uid'];

	$user_sig = ($bb_cfg['allow_sig'] && !$user->opt_js['h_sig'] && $postrow[$i]['enable_sig'] && $postrow[$i]['user_sig']) ? $postrow[$i]['user_sig'] : '';

	if ($user_sig)
	{
		$user_sig = bbcode2html($user_sig);
	}

	//
	// Replace naughty words
	//
	if (count($orig_word))
	{
		$post_subject = preg_replace($orig_word, $replacement_word, $post_subject);

		if ($user_sig)
		{
			$user_sig = str_replace('\"', '"', substr(@preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "@preg_replace(\$orig_word, \$replacement_word, '\\0')", '>' . $user_sig . '<'), 1, -1));
		}

		$message = str_replace('\"', '"', substr(@preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "@preg_replace(\$orig_word, \$replacement_word, '\\0')", '>' . $message . '<'), 1, -1));
	}

	//
	// Replace newlines (we use this rather than nl2br because
	// till recently it wasn't XHTML compliant)
	//
	if ($user_sig)
	{
		$user_sig = '<br />_________________<br />' . str_replace("\n", "\n<br />\n", $user_sig);
	}

	//
	// Editing information
	//
	if ( $postrow[$i]['post_edit_count'] )
	{
		$l_edit_time_total = ( $postrow[$i]['post_edit_count'] == 1 ) ? $lang['EDITED_TIME_TOTAL'] : $lang['EDITED_TIMES_TOTAL'];

		$l_edited_by = '<br /><br />' . sprintf($l_edit_time_total, $poster, bb_date($postrow[$i]['post_edit_time']), $postrow[$i]['post_edit_count']);
	}
	else
	{
		$l_edited_by = '';
	}

	//
	// Again this will be handled by the templating
	// code at some point
	//
	$pg_row_class = !($i % 2) ? 'row2' : 'row1';

	// Report
	//
	// Create report links
	//
	if ($report_post && $report_post->auth_check('auth_write'))
	{
		if ($postrow[$i]['post_reported'])
		{
			$report_auth = ($userdata['user_level'] == ADMIN || (!$bb_cfg['report_list_admin'] && (!$bb_cfg['report_subject_auth'] || $is_auth['auth_mod'])));
			if ($report_post->auth_check('auth_view') && $report_auth)
			{
				$temp_url = append_sid("report.php?mode=reported&amp;" . POST_CAT_URL . '=' . $report_post->id . '&amp;id=' . $postrow[$i]['post_id']);
				$target = ($bb_cfg['report_new_window']) ? ' target="_blank"' : '';
				$report_img = '<a href="' . $temp_url . '"' . $target . '><img src="' . $images['icon_reported'] . '" alt="' . $report_post->lang['DUPLICATE_REPORT'] . '" title="' . $report_post->lang['DUPLICATE_REPORT'] . '" border="0" /></a>';
				$report = '<a href="' . $temp_url . '"' . $target . '>[' . $report_post->lang['DUPLICATE_REPORT'] . ']</a>';
			}
			else
			{
				$report_img = '<img src="' . $images['icon_reported'] . '" alt="' . $report_post->lang['DUPLICATE_REPORT'] . '" title="' . $report_post->lang['DUPLICATE_REPORT'] . '" border="0" />';
				$report = '['. $report_post->lang['DUPLICATE_REPORT'] .']';
			}
		}
		else
		{
			$temp_url = append_sid("report.php?mode=" . $report_post->mode . '&amp;id=' . $postrow[$i]['post_id']);
			$report_img = '<a href="' . $temp_url . '"><img src="' . $images['icon_report'] . '" alt="' . $report_post->lang['WRITE_REPORT'] . '" title="' . $report_post->lang['WRITE_REPORT'] . '" border="0" /></a>';
			$report = '<a class="txtb" href="' . $temp_url . '">[' . $report_post->lang['WRITE_REPORT'] . ']</a>';
		}
	}
	else
	{
		$report_img = $report = '';
	}
	// Report [END]

	$template->assign_block_vars('postrow', array(
		'ROW_CLASS'          => !($i % 2) ? 'row1' : 'row2',
		'POST_ID'            => $postrow[$i]['post_id'],
		'IS_NEWEST'          => ($postrow[$i]['post_id'] == $newest),
		'POSTER_NAME'        => wbr($poster),
		'POSTER_NAME_JS'     => addslashes($poster),
		'POSTER_RANK'        => $poster_rank,
		'RANK_IMAGE'         => $rank_image,
		'POSTER_JOINED'      => ($bb_cfg['show_poster_joined']) ? $poster_longevity : '',

		'POSTER_JOINED_DATE' => $poster_joined,
		'POSTER_POSTS'       => ($bb_cfg['show_poster_posts']) ? $poster_posts : '',
		'POSTER_FROM'        => ($bb_cfg['show_poster_from']) ? wbr($poster_from) : '',
		'POSTER_BOT'         => ($poster_id == BOT_UID),
		'POSTER_ID'          => $poster_id,
		'POSTED_AFTER'       => ($prev_post_time) ? delta_time($postrow[$i]['post_time'], $prev_post_time) : '',
		'POSTER_FROM_FLAG'   => ($bb_cfg['show_poster_flag']) ? $poster_from_flag : '',
		'IS_UNREAD'          => is_unread($postrow[$i]['post_time'], $topic_id, $forum_id),
		'IS_FIRST_POST'      => (!$start && ($postrow[$i]['post_id'] == $t_data['topic_first_post_id'])),
		'MOD_CHECKBOX'       => ($moderation && ($start || defined('SPLIT_FORM_START'))),
		'POSTER_AVATAR'      => $poster_avatar,
		'POST_NUMBER'        => ($i + $start + 1),
		'POST_DATE'          => $post_date,
		'POST_SUBJECT'       => $post_subject,
		'MESSAGE'            => $message,
		'SIGNATURE'          => $user_sig,
		'EDITED_MESSAGE'     => $l_edited_by,

		'PM'      => $pm_btn,
		'PROFILE' => $profile_btn,

		'QUOTE'   => $quote_btn,
		'EDIT'    => $edit_btn,
		'DELETE'  => $delpost_btn,
		'IP'      => $ip_btn,

		// Report
		'REPORT'  => ($bb_cfg['text_buttons']) ? $report : $report_img,
		// Report [END]
	));

	if ($postrow[$i]['post_attachment'] && $is_auth['auth_download'] && function_exists('display_post_attachments'))
	{
		display_post_attachments($postrow[$i]['post_id'], $postrow[$i]['post_attachment']);
	}

	if ($moderation && !defined('SPLIT_FORM_START') && ($start || $postrow[$i]['post_id'] == $t_data['topic_first_post_id']))
	{
		define('SPLIT_FORM_START', TRUE);
	}

	if ($poster_id != BOT_UID)
	{
		$prev_post_time = $postrow[$i]['post_time'];
	}
}

set_tracks(COOKIE_TOPIC, $tracking_topics, $topic_id, $max_post_time);

if (defined('SPLIT_FORM_START'))
{
	$template->assign_vars(array(
		'SPLIT_FORM'     => true,
		'START'          => $start,
		'S_SPLIT_ACTION' => "modcp.php",
		'POST_FORUM_URL' => POST_FORUM_URL,
		'POST_TOPIC_URL' => POST_TOPIC_URL,
	));
}

//
// Quick Reply
//
if ($bb_cfg['show_quick_reply'])
{
	if ($is_auth['auth_reply'] && !($t_data['forum_status'] == FORUM_LOCKED || $t_data['topic_status'] == TOPIC_LOCKED))
	{
		$template->assign_vars(array(
			'QUICK_REPLY'     => true,
			'QR_POST_ACTION'  => "posting.php",
			'QR_TOPIC_ID'     => $topic_id,
			'CAPTCHA_HTML'    => (IS_GUEST) ? CAPTCHA()->get_html() : '',
		));
		if (!IS_GUEST)
		{
			$template->assign_vars(array(
				'QR_ATTACHSIG_CHECKED' => bf($userdata['user_opt'], 'user_opt', 'attachsig'),
				'QR_NOTIFY_CHECKED'    => ($userdata['user_notify'] || $is_watching_topic),
			));
		}
	}
}

$template->assign_vars(array(
	'PG_ROW_CLASS' => isset($pg_row_class) ? $pg_row_class : 'row1',
));

if (IS_ADMIN)
{
	$template->assign_vars(array(
		'U_LOGS' => "admin/admin_log.php?sid={$userdata['session_id']}&amp;t=$topic_id&amp;db=900",
	));
}

print_page('viewtopic.tpl');
