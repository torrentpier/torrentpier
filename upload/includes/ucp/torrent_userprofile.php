<?php

if (!defined('IN_VIEWPROFILE')) die(basename(__FILE__));

if (!$profiledata['user_id'] || $profiledata['user_id'] == ANONYMOUS)
{
	message_die(GENERAL_ERROR, $lang['NO_USER_ID_SPECIFIED']);
}

$seeding = $leeching = $releasing = array();

$profile_user_id = intval($profiledata['user_id']);
$current_time = (isset($_GET['time']) && $_GET['time'] == 'all') ? 0 : time();

// Get username
if (!$username = $profiledata['username'])
{
	message_die(GENERAL_ERROR, 'Tried obtaining data for a non-existent user');
}

if ($profile_user_id == $userdata['user_id'])
{
	$template->assign_vars(array(
		'EDIT_PROF'      => true,
		'EDIT_PROF_HREF' => append_sid("profile.php?mode=editprofile"),
	));
}
else
{
	$template->assign_vars(array('EDIT_PROF' => false));
}

// Set tpl vars for bt_userdata
show_bt_userdata($profile_user_id);

if (IS_ADMIN)
{
	$template->assign_vars(array(
		'SHOW_PASSKEY'   => true,
		'S_GEN_PASSKEY'  => '<a href="#" onclick="ajax.exec({ action: \'gen_passkey\', user_id: '. $profile_user_id .' }); return false;">'. $lang['BT_GEN_PASSKEY_URL'] .'</a>',
		'CAN_EDIT_RATIO' => IS_ADMIN,
	));
}
else
{
	$template->assign_vars(array(
		'CAN_EDIT_RATIO' => false,
	));
}

// Auth
$not_auth_forums_sql = ($f = $user->get_not_auth_forums(AUTH_READ)) ? "AND f.forum_id NOT IN($f)" : '';
$datastore->rm('cat_forums');

// Get users active torrents
$sql = 'SELECT f.forum_id, f.forum_name, t.topic_title, tor.size, tr.*
	FROM '. BB_FORUMS .' f, '. BB_TOPICS .' t, '. BB_BT_TRACKER .' tr, '. BB_BT_TORRENTS ." tor
	WHERE tr.user_id = $profile_user_id
		AND tr.topic_id = tor.topic_id
		AND tor.topic_id = t.topic_id
		AND t.forum_id = f.forum_id
			$not_auth_forums_sql
	GROUP BY tr.topic_id
	ORDER BY f.forum_name, t.topic_title";

if (!$result = DB()->sql_query($sql))
{
	message_die(GENERAL_ERROR, 'Could not query users torrent profile information', '', __LINE__, __FILE__, $sql);
}

if ($rowset = @DB()->sql_fetchrowset($result))
{
	DB()->sql_freeresult($result);
	$rowset_count = count($rowset);

	for ($i=0; $i<$rowset_count; $i++)
	{
		if ($rowset[$i]['releaser'])
		{
			$releasing[] = $rowset[$i];
		}
		else if ($rowset[$i]['seeder'])
		{
			$seeding[] = $rowset[$i];
		}
		else
		{
			$leeching[] = $rowset[$i];
		}
	}
	unset($rowset);
}

if ($releasing_count = count($releasing))
{
	$template->assign_block_vars('released', array());

	for ($i=0; $i<$releasing_count; $i++)
	{
		$template->assign_block_vars('released.releasedrow', array(
			'FORUM_NAME'   => htmlCHR($releasing[$i]['forum_name']),
			'TOPIC_TITLE'  => wbr($releasing[$i]['topic_title']),
			'U_VIEW_FORUM' => "viewforum.php?". POST_FORUM_URL .'='. $releasing[$i]['forum_id'],
			'U_VIEW_TOPIC' => "viewtopic.php?". POST_TOPIC_URL .'='. $releasing[$i]['topic_id'] .'&amp;spmode=full#seeders',
		));
	}
}
else
{
	$template->assign_block_vars('switch_releasing_none', array());
}

if ($seeding_count = count($seeding))
{
	$template->assign_block_vars('seed', array());

	for ($i=0; $i<$seeding_count; $i++)
	{
		$template->assign_block_vars('seed.seedrow', array(
			'FORUM_NAME'   => htmlCHR($seeding[$i]['forum_name']),
			'TOPIC_TITLE'  => wbr($seeding[$i]['topic_title']),
			'U_VIEW_FORUM' => "viewforum.php?". POST_FORUM_URL .'='. $seeding[$i]['forum_id'],
			'U_VIEW_TOPIC' => "viewtopic.php?". POST_TOPIC_URL .'='. $seeding[$i]['topic_id'] .'&amp;spmode=full#seeders',
		));
	}
}
else
{
	$template->assign_block_vars('switch_seeding_none', array());
}

if ($leeching_count = count($leeching))
{
	$template->assign_block_vars('leech', array());

	for ($i=0; $i<$leeching_count; $i++)
	{
		$compl_size = ($leeching[$i]['remain'] && $leeching[$i]['size'] && $leeching[$i]['size'] > $leeching[$i]['remain']) ? ($leeching[$i]['size'] - $leeching[$i]['remain']) : 0;
		$compl_perc = ($compl_size) ? floor($compl_size * 100 / $leeching[$i]['size']) : 0;

		$template->assign_block_vars('leech.leechrow', array(
			'FORUM_NAME'   => htmlCHR($leeching[$i]['forum_name']),
			'TOPIC_TITLE'  => wbr($leeching[$i]['topic_title']),
			'U_VIEW_FORUM' => "viewforum.php?". POST_FORUM_URL .'='. $leeching[$i]['forum_id'],
			'U_VIEW_TOPIC' => "viewtopic.php?". POST_TOPIC_URL .'='. $leeching[$i]['topic_id'] .'&amp;spmode=full#leechers',
			'COMPL_PERC'   => $compl_perc,
		));
	}
}
else
{
	$template->assign_block_vars('switch_leeching_none', array());
}

$template->assign_vars(array(
	'USERNAME'   => $username,
	'L_RELEASINGS'=> '<b>'. $lang['RELEASING'] .'</b>'. (($releasing_count) ? "<br />[ <b>$releasing_count</b> ]" : ''),
	'L_SEEDINGS'  => '<b>'. $lang['SEEDING'] .'</b>'. (($seeding_count) ? "<br />[ <b>$seeding_count</b> ]" : ''),
	'L_LEECHINGS' => '<b>'. $lang['LEECHING'] .'</b>'. (($leeching_count) ? "<br />[ <b>$leeching_count</b> ]" : ''),

	'L_VIEW_TOR_PROF'  => sprintf($lang['VIEWING_USER_BT_PROFILE'], $username),
	'RELEASED_ROWSPAN' => ($releasing_count) ? 'rowspan="'. ($releasing_count + 1) .'"' : '',
	'SEED_ROWSPAN'     => ($seeding_count) ? 'rowspan="'. ($seeding_count + 1) .'"' : '',
	'LEECH_ROWSPAN'    => ($leeching_count) ? 'rowspan="'. ($leeching_count + 1) .'"' : '',
));

$template->assign_vars(array('SHOW_SEARCH_DL' => false));

if (!IS_USER || $profile_user_id == $userdata['user_id'])
{
	$page_cfg['dl_links_user_id'] = $profile_user_id;
}

$sql = 'SELECT SUM(speed_up) as speed_up, SUM(speed_down) as speed_down
    FROM '. BB_BT_TRACKER .'
    WHERE user_id = ' . $profile_user_id . '';

if ($row = DB()->fetch_row($sql))
{
	$speed_up   = ($row['speed_up']) ? humn_size($row['speed_up']).'/s' : '-';
	$speed_down = ($row['speed_down']) ? humn_size($row['speed_down']).'/s' : '-';

	$template->assign_vars(array(
		'SPEED_UP'   => $speed_up,
		'SPEED_DOWN' => $speed_down,
	));
}