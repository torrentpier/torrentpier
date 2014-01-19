<?php

require('./pagestart.php');

//
// Generate relevant output
//
if( isset($_GET['pane']) && $_GET['pane'] == 'left' )
{
	$dir = @opendir('.');

	$setmodules = 1;
	while( $file = @readdir($dir) )
	{
		if( preg_match('/^admin_.*?\.php$/', $file) )
		{
			include('./' . $file);
		}
	}

	@closedir($dir);

	unset($setmodules);

	$template->assign_vars(array(
		'TPL_ADMIN_NAVIGATE' => true,
		'U_FORUM_INDEX' => "../index.php",
		'U_ADMIN_INDEX' => "index.php?pane=right",
	));

	ksort($module);

	while( list($cat, $action_array) = each($module) )
	{
		$cat = ( !empty($lang[strtoupper($cat)]) ) ? $lang[strtoupper($cat)] : preg_replace('/_/', ' ', $cat);

		$template->assign_block_vars('catrow', array(
			'ADMIN_CATEGORY' => $cat,
		));

		ksort($action_array);

		$row_count = 0;
		while( list($action, $file)	= each($action_array) )
		{
			$row_class = !($row_count % 2) ? 'row1' : 'row2';

			$action = ( !empty($lang[strtoupper($action)]) ) ? $lang[strtoupper($action)] : preg_replace('/_/', ' ', $action);

			$template->assign_block_vars('catrow.modulerow', array(
				'ROW_CLASS' => $row_class,
				'ADMIN_MODULE' => $action,
				'U_ADMIN_MODULE' => $file)
			);
			$row_count++;
		}
	}
}
elseif( isset($_GET['pane']) && $_GET['pane'] == 'right' )
{
	$template->assign_vars(array(
		'TPL_ADMIN_MAIN' => true,
	));

	//
	// Get forum statistics
	//
	$total_posts = get_db_stat('postcount');
	$total_users = get_db_stat('usercount');
	$total_topics = get_db_stat('topiccount');

	$start_date = bb_date($bb_cfg['board_startdate']);

	$boarddays = ( TIMENOW - $bb_cfg['board_startdate'] ) / 86400;

	$posts_per_day = sprintf('%.2f', $total_posts / $boarddays);
	$topics_per_day = sprintf('%.2f', $total_topics / $boarddays);
	$users_per_day = sprintf('%.2f', $total_users / $boarddays);

	$avatar_dir_size = 0;

	if ($avatar_dir = @opendir(BB_ROOT . $bb_cfg['avatar_path']))
	{
		while( $file = @readdir($avatar_dir) )
		{
			if( $file != '.' && $file != '..' )
			{
				$avatar_dir_size += @filesize(BB_ROOT . $bb_cfg['avatar_path'] . '/' . $file);
			}
		}
		@closedir($avatar_dir);


		$avatar_dir_size = humn_size($avatar_dir_size);
	}
	else
	{
		// Couldn't open Avatar dir.
		$avatar_dir_size = $lang['NOT_AVAILABLE'];
	}

	if(intval($posts_per_day) > $total_posts)
	{
		$posts_per_day = $total_posts;
	}

	if(intval($topics_per_day) > $total_topics)
	{
		$topics_per_day = $total_topics;
	}

	if($users_per_day > $total_users)
	{
		$users_per_day = $total_users;
	}

	//
	// DB size ... MySQL only
	//
	// This code is heavily influenced by a similar routine
	// in phpMyAdmin 2.2.0
	//

	$sql = "SELECT VERSION() AS mysql_version";
	if ($result = DB()->sql_query($sql))
	{
		$row = DB()->sql_fetchrow($result);
		$version = $row['mysql_version'];

		if (preg_match('/^(3\.23|4\.|5\.|10\.)/', $version))
		{
			$dblist = array();
			foreach ($bb_cfg['db'] as $name => $row)
			{
				$sql = "SHOW TABLE STATUS FROM {$row[1]}";
				if ($result = DB()->sql_query($sql))
				{
					$tabledata_ary = DB()->sql_fetchrowset($result);

					$dbsize = 0;
					for ($i = 0; $i < count($tabledata_ary); $i++)
					{
						if( @$tabledata_ary[$i]['Type'] != 'MRG_MYISAM' )
						{
							$dbsize += $tabledata_ary[$i]['Data_length'] + $tabledata_ary[$i]['Index_length'];
						}
					}
					$dblist[] = '<span title="'. $name .'">'. humn_size($dbsize) .'</span>';
				}
			}
			$dbsize = implode('&nbsp;|&nbsp;', $dblist);
		}
		else
		{
			$dbsize = $lang['NOT_AVAILABLE'];
		}
	}
	else
	{
		$dbsize = $lang['NOT_AVAILABLE'];
	}

	$template->assign_vars(array(
		'NUMBER_OF_POSTS'  => $total_posts,
		'NUMBER_OF_TOPICS' => $total_topics,
		'NUMBER_OF_USERS'  => $total_users,
		'START_DATE'       => $start_date,
		'POSTS_PER_DAY'    => $posts_per_day,
		'TOPICS_PER_DAY'   => $topics_per_day,
		'USERS_PER_DAY'    => $users_per_day,
		'AVATAR_DIR_SIZE'  => $avatar_dir_size,
		'DB_SIZE'		   => $dbsize,
		'GZIP_COMPRESSION' => ( $bb_cfg['gzip_compress'] ) ? $lang['ON'] : $lang['OFF'],
	));
	//
	// End forum statistics
	//
	if (@$_GET['users_online'])
	{
		$template->assign_vars(array(
			'SHOW_USERS_ONLINE' => true,
		));
		//
		// Get users online information.
		//
		$sql = "SELECT u.user_id, u.username, u.user_rank, s.session_time AS user_session_time, u.user_opt, s.session_logged_in, s.session_ip, s.session_start
			FROM " . BB_USERS . " u, " . BB_SESSIONS . " s
			WHERE s.session_logged_in = 1
				AND u.user_id = s.session_user_id
				AND u.user_id <> " . GUEST_UID . "
				AND s.session_time >= " . ( TIMENOW - 300 ) . "
			ORDER BY s.session_ip ASC, s.session_time DESC";
		if(!$result = DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Couldn't obtain regd user/online information.", '', __LINE__, __FILE__, $sql);
		}
		$onlinerow_reg = DB()->sql_fetchrowset($result);

		$sql = "SELECT session_logged_in, session_time, session_ip, session_start
			FROM " . BB_SESSIONS . "
			WHERE session_logged_in = 0
				AND session_time >= " . ( TIMENOW - 300 ) . "
			ORDER BY session_ip ASC, session_time DESC";
		if(!$result = DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Couldn't obtain guest user/online information.", '', __LINE__, __FILE__, $sql);
		}
		$onlinerow_guest = DB()->sql_fetchrowset($result);

		$reg_userid_ary = array();

		if( count($onlinerow_reg) )
		{
			$registered_users = 0;

			for($i=0, $cnt=count($onlinerow_reg); $i < $cnt; $i++)
			{
				if( !inarray($onlinerow_reg[$i]['user_id'], $reg_userid_ary) )
				{
					$reg_userid_ary[] = $onlinerow_reg[$i]['user_id'];

					$username = $onlinerow_reg[$i]['username'];

					if( !bf($onlinerow_reg[$i]['user_opt'], 'user_opt', 'allow_viewonline'))
					{
						$registered_users++;
						$hidden = FALSE;
					}
					else
					{
						@$hidden_users++;
						$hidden = TRUE;
					}

					$row_class = 'row1';

					$reg_ip = decode_ip($onlinerow_reg[$i]['session_ip']);

					$template->assign_block_vars('reg_user_row', array(
						'ROW_CLASS'  => $row_class,
						'USER'       => profile_url($onlinerow_reg[$i]),
						'STARTED'    => bb_date($onlinerow_reg[$i]['session_start'], 'H:i', 'false'),
						'LASTUPDATE' => bb_date($onlinerow_reg[$i]['user_session_time'], 'H:i', 'false'),
						'IP_ADDRESS' => $reg_ip,
						'U_WHOIS_IP' => $bb_cfg['whois_info'] . $reg_ip,
					));
				}
			}
		}

		//
		// Guest users
		//
		if( count($onlinerow_guest) )
		{
			$guest_users = 0;

			for($i = 0; $i < count($onlinerow_guest); $i++)
			{
				$guest_userip_ary[] = $onlinerow_guest[$i]['session_ip'];
				$guest_users++;

				$row_class = 'row2';

				$guest_ip = decode_ip($onlinerow_guest[$i]['session_ip']);

				$template->assign_block_vars('guest_user_row', array(
					'ROW_CLASS'  => $row_class,
					'STARTED'    => bb_date($onlinerow_guest[$i]['session_start'], 'H:i', 'false'),
					'LASTUPDATE' => bb_date($onlinerow_guest[$i]['session_time'], 'H:i' , 'false'),
					'IP_ADDRESS' => $guest_ip,
					'U_WHOIS_IP' => $bb_cfg['whois_info'] . $guest_ip,
				));
			}
		}
	}
	else
	{
		$template->assign_vars(array(
			'USERS_ONLINE_HREF' => "index.php?pane=right&users_online=1&sid={$userdata['session_id']}",
		));
	}

	$template->assign_vars(array(
		'U_CLEAR_TPL_CACHE'   => "xs_cache.php?clear=",
		'U_UPDATE_USER_LEVEL' => "index.php?update_user_level=1",
		'U_SYNC_TOPICS'       => "index.php?sync_topics=1",
		'U_SYNC_USER_POSTS'   => "index.php?sync_user_posts=1",
	));
}
elseif (isset($_REQUEST['update_user_level']))
{
	require(INC_DIR .'functions_group.php');
	update_user_level('all');
	bb_die($lang['USER_LEVELS_UPDATED']);
}
elseif (isset($_REQUEST['sync_topics']))
{
	sync('topic', 'all');
	sync('forum', 'all');
	bb_die($lang['TOPICS_DATA_SYNCHRONIZED']);
}
elseif (isset($_REQUEST['sync_user_posts']))
{
	sync('user_posts', 'all');
	bb_die($lang['USER POSTS COUNT SYNCHRONIZED']);
}
else
{
	//
	// Generate frameset
	//
	$template->assign_vars(array(
		'TPL_ADMIN_FRAMESET' => true,
		'S_FRAME_NAV'        => "index.php?pane=left",
		'S_FRAME_MAIN'       => "index.php?pane=right",
	));
	send_no_cache_headers();
	print_page('index.tpl', 'admin', 'no_header');
}

print_page('index.tpl', 'admin');

//
// Functions
//
function inarray($needle, $haystack)
{
	for($i = 0; $i < sizeof($haystack); $i++ )
	{
		if( $haystack[$i] == $needle )
		{
			return true;
		}
	}
	return false;
}