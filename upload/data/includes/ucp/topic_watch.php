<?php

if (empty($bb_cfg['topic_notify_enabled']))
{
	bb_die($lang['DISABLED']);
}

$page_cfg['use_tablesorter']   = true;
$page_cfg['include_bbcode_js'] = true;
$tracking_topics = get_tracks('topic');

$user_id = $userdata['user_id'];
$start = isset($_GET['start']) ? abs(intval($_GET['start'])) : 0;
$per_page = $bb_cfg['topics_per_page'];

if (isset($_POST['topic_id_list']))
{
	$topic_ids = implode(",", $_POST['topic_id_list']);
	$sql = "DELETE FROM ". BB_TOPICS_WATCH ."  WHERE topic_id IN(". $topic_ids .") AND user_id = $user_id";
	if (!($result = DB() ->sql_query($sql)))
	{
		bb_die('Could not delete topic watch information #1');
	}
}

$template->assign_vars(array(
	'PAGE_TITLE'    => $lang['WATCHED_TOPICS'],
	'S_FORM_ACTION' => BB_ROOT .'profile.php?mode=watch',
));

$sql = "SELECT COUNT(topic_id) as watch_count FROM ". BB_TOPICS_WATCH ." WHERE user_id = $user_id";
if ( !($result = DB() ->sql_query($sql)) )
{
	bb_die('Could not obtain watch topic information #2');
}
$row = DB() ->sql_fetchrow($result);
$watch_count = ( $row['watch_count'] ) ? $row['watch_count'] : 0;
DB() ->sql_freeresult($result);

if ($watch_count > 0)
{
	$sql = "SELECT w.*, t.*, f.*, u.*, u2.username as last_username, u2.user_id as last_user_id,
		u2.user_level as last_user_level, u2.user_rank as last_user_rank
	FROM ". BB_TOPICS_WATCH ." w, ". BB_TOPICS ." t, ". BB_USERS ." u, ". BB_FORUMS ." f, ". BB_POSTS ." p, " . BB_USERS . " u2
	WHERE w.topic_id = t.topic_id
		AND t.forum_id = f.forum_id
		AND p.post_id = t.topic_last_post_id
		AND p.poster_id = u2.user_id
		AND t.topic_poster = u.user_id
		AND w.user_id = $user_id
	GROUP BY t.topic_last_post_time DESC
	LIMIT $start, $per_page";
	if (!($result = DB() ->sql_query($sql)))
	{
		bb_die('Could not obtain watch topic information #3');
	}
	$watch = DB() ->sql_fetchrowset($result);

	if ($watch)
	{
		for ( $i = 0; $i < count($watch); $i++ )
		{
			$is_unread = is_unread($watch[$i]['topic_last_post_time'], $watch[$i]['topic_id'], $watch[$i]['forum_id']);

			$template->assign_block_vars('watch', array(
				'ROW_CLASS'         => ( !($i % 2) ) ? 'row1' : 'row2',
				'POST_ID'           => $watch[$i]['topic_first_post_id'],
				'TOPIC_ID'          => $watch[$i]['topic_id'],
				'TOPIC_TITLE'       => wbr(str_short($watch[$i]['topic_title'], 70)),
				'FULL_TOPIC_TITLE'  => wbr($watch[$i]['topic_title']),
				'U_TOPIC'           => TOPIC_URL . $watch[$i]['topic_id'],
				'FORUM_TITLE'       => wbr($watch[$i]['forum_name']),
				'U_FORUM'           => FORUM_URL . $watch[$i]['forum_id'],
				'REPLIES'           => $watch[$i]['topic_replies'],
				'AUTHOR'            => profile_url($watch[$i]),
				'LAST_POST'         => bb_date($watch[$i]['topic_last_post_time']) .'<br />'. profile_url(array('user_id' => $watch[$i]['last_user_id'], 'username' => $watch[$i]['last_username'], 'user_rank' => $watch[$i]['last_user_rank'])),
				'LAST_POST_ID'      => $watch[$i]['topic_last_post_id'],
				'IS_UNREAD'         => $is_unread,
				'TOPIC_ICON'        => get_topic_icon($watch[$i], $is_unread),
				'PAGINATION'        => ($watch[$i]['topic_status'] == TOPIC_MOVED) ? '' : build_topic_pagination(TOPIC_URL . $watch[$i]['topic_id'], $watch[$i]['topic_replies'], $bb_cfg['posts_per_page']),
			));
		}

		$template->assign_vars(array(
			'MATCHES'     => (count($watch) == 1) ? sprintf($lang['FOUND_SEARCH_MATCH'], count($watch)) : sprintf($lang['FOUND_SEARCH_MATCHES'], count($watch)),
			'PAGINATION'  => generate_pagination(BB_ROOT .'profile.php?mode=watch', $watch_count, $per_page, $start),
			'PAGE_NUMBER' => sprintf($lang['PAGE_OF'], ( floor( $start / $per_page ) + 1 ), ceil( $watch_count / $per_page )),
			'U_PER_PAGE'  => BB_ROOT .'profile.php?mode=watch',
			'PER_PAGE'    => $per_page,
		));
	}
	DB() ->sql_freeresult($result);
}
else
{
	meta_refresh(BB_ROOT, '3');
	bb_die($lang['NO_WATCHED_TOPICS']);
}

print_page('usercp_topic_watch.tpl');