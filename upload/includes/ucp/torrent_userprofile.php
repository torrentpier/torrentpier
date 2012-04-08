<?php

if (!defined('IN_VIEWPROFILE')) die(basename(__FILE__));

if (!$profiledata['user_id'] || $profiledata['user_id'] == ANONYMOUS)
{
	message_die(GENERAL_ERROR, $lang['NO_USER_ID_SPECIFIED']);
}

$seeding = $leeching = $releasing = array();

$profile_user_id = intval($profiledata['user_id']);
$current_time = (isset($_GET['time']) && $_GET['time'] == 'all') ? 0 : TIMENOW;

// Get username
if (!$username = $profiledata['username'])
{
	message_die(GENERAL_ERROR, 'Tried obtaining data for a non-existent user');
}

if ($profile_user_id == $userdata['user_id'])
{
	$template->assign_vars(array(
		'EDIT_PROF'      => true,
		'EDIT_PROF_HREF' => "profile.php?mode=editprofile",
	));
}
else
{
	$template->assign_vars(array('EDIT_PROF' => false));
}

// Auth
$excluded_forums_csv = $user->get_excluded_forums(AUTH_VIEW);
$not_auth_forums_sql = ($excluded_forums_csv) ? "
	AND f.forum_id NOT IN($excluded_forums_csv)
	AND f.forum_parent NOT IN($excluded_forums_csv)
" : '';

// Get users active torrents
$sql = 'SELECT f.forum_id, f.forum_name, t.topic_title, tor.tor_type, tor.size, tr.*
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
		$is_gold = '';
		if ($tr_cfg['gold_silver_enabled'])
		{
		    if ($releasing[$i]['tor_type'] == TOR_TYPE_GOLD)
		    {
                $is_gold = '<img src="images/tor_gold.gif" width="16" height="15" title="'.$lang['GOLD'].'" />&nbsp;';
            }
            elseif ($releasing[$i]['tor_type'] == TOR_TYPE_SILVER)
            {
                $is_gold = '<img src="images/tor_silver.gif" width="16" height="15" title="'.$lang['SILVER'].'" />&nbsp;';
            }
        }

		$template->assign_block_vars('released.releasedrow', array(
			'FORUM_NAME'   => htmlCHR($releasing[$i]['forum_name']),
			'TOPIC_TITLE'  => ($releasing[$i]['update_time']) ? wbr($releasing[$i]['topic_title']) : '<s>'. wbr($releasing[$i]['topic_title']) .'</s>',
			'U_VIEW_FORUM' => "viewforum.php?". POST_FORUM_URL .'='. $releasing[$i]['forum_id'],
			'U_VIEW_TOPIC' => "viewtopic.php?". POST_TOPIC_URL .'='. $releasing[$i]['topic_id'] .'&amp;spmode=full#seeders',
			'TOR_TYPE'   => $is_gold,
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
		$is_gold = '';
		if ($tr_cfg['gold_silver_enabled'])
		{
		    if ($seeding[$i]['tor_type'] == TOR_TYPE_GOLD)
		    {
                $is_gold = '<img src="images/tor_gold.gif" width="16" height="15" title="'.$lang['GOLD'].'" />&nbsp;';
            }
            elseif ($seeding[$i]['tor_type'] == TOR_TYPE_SILVER)
            {
		        $is_gold = '<img src="images/tor_silver.gif" width="16" height="15" title="'.$lang['SILVER'].'" />&nbsp;';
            }
        }

		$template->assign_block_vars('seed.seedrow', array(
			'FORUM_NAME'   => htmlCHR($seeding[$i]['forum_name']),
			'TOPIC_TITLE'  => ($seeding[$i]['update_time']) ? wbr($seeding[$i]['topic_title']) : '<s>'. wbr($seeding[$i]['topic_title']) .'</s>',
			'U_VIEW_FORUM' => "viewforum.php?". POST_FORUM_URL .'='. $seeding[$i]['forum_id'],
			'U_VIEW_TOPIC' => "viewtopic.php?". POST_TOPIC_URL .'='. $seeding[$i]['topic_id'] .'&amp;spmode=full#seeders',
			'TOR_TYPE'   => $is_gold,
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
		$is_gold = '';
		if ($tr_cfg['gold_silver_enabled'])
		{
		    if ($leeching[$i]['tor_type'] == TOR_TYPE_GOLD)
		    {
                $is_gold = '<img src="images/tor_gold.gif" width="16" height="15" title="'.$lang['GOLD'].'" />&nbsp;';
            }
            elseif ($leeching[$i]['tor_type'] == TOR_TYPE_SILVER)
            {
                $is_gold = '<img src="images/tor_silver.gif" width="16" height="15" title="'.$lang['SILVER'].'" />&nbsp;';
            }
        }

		$compl_size = ($leeching[$i]['remain'] && $leeching[$i]['size'] && $leeching[$i]['size'] > $leeching[$i]['remain']) ? ($leeching[$i]['size'] - $leeching[$i]['remain']) : 0;
		$compl_perc = ($compl_size) ? floor($compl_size * 100 / $leeching[$i]['size']) : 0;

		$template->assign_block_vars('leech.leechrow', array(
			'FORUM_NAME'   => htmlCHR($leeching[$i]['forum_name']),
			'TOPIC_TITLE'  => ($leeching[$i]['update_time']) ? wbr($leeching[$i]['topic_title']) : '<s>'. wbr($leeching[$i]['topic_title']) .'</s>',
			'U_VIEW_FORUM' => "viewforum.php?". POST_FORUM_URL .'='. $leeching[$i]['forum_id'],
			'U_VIEW_TOPIC' => "viewtopic.php?". POST_TOPIC_URL .'='. $leeching[$i]['topic_id'] .'&amp;spmode=full#leechers',
			'COMPL_PERC'   => $compl_perc,
			'TOR_TYPE'   => $is_gold,
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

	'RELEASED_ROWSPAN' => ($releasing_count) ? 'rowspan="'. ($releasing_count + 1) .'"' : '',
	'SEED_ROWSPAN'     => ($seeding_count) ? 'rowspan="'. ($seeding_count + 1) .'"' : '',
	'LEECH_ROWSPAN'    => ($leeching_count) ? 'rowspan="'. ($leeching_count + 1) .'"' : '',
));

$template->assign_vars(array('SHOW_SEARCH_DL' => false));

if (!IS_USER || $profile_user_id == $userdata['user_id'])
{
	$page_cfg['dl_links_user_id'] = $profile_user_id;
}
