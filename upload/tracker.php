<?php

/*
	This file is part of TorrentPier

	TorrentPier is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	TorrentPier is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	A copy of the GPL 2.0 should have been included with the program.
	If not, see http://www.gnu.org/licenses/

	Official SVN repository and contact information can be found at
	http://code.google.com/p/torrentpier/
 */

define('IN_PHPBB',   true);
define('BB_SCRIPT', 'tracker');
define('BB_ROOT', './');
require(BB_ROOT ."common.php");

// Page config
$page_cfg['include_bbcode_js'] = true;
$page_cfg['use_tablesorter']   = true;

$page_cfg['load_tpl_vars'] = array(
	'post_icons',
);

// Session start
$user->session_start(array('req_login' => $bb_cfg['bt_tor_browse_only_reg']));

$tor_search_limit    = (IS_AM) ? 2000 : 500;
$title_match_limit   = 700;                    // больше $tor_search_limit т.к. ищет по всем темам, а не только по раздачам
$forum_select_size   = (UA_OPERA) ? 21 : 24;   // forum select box max rows
$max_forum_name_len  = 60;                     // inside forum select box
$title_match_max_len = 60;
$poster_name_max_len = 25;
$tor_colspan         = 13;                     // torrents table colspan with all columns
$per_page            = $bb_cfg['topics_per_page'];
$tracker_url         = basename(__FILE__);

$time_format  = 'H:i';
$date_format  = 'j-M-y';
$row_class_1  = 'prow1';
$row_class_2  = 'prow2';

$start = isset($_REQUEST['start']) ? abs(intval($_REQUEST['start'])) : 0;

$set_default = isset($_GET['def']);
$user_id     = $userdata['user_id'];
$lastvisit   = (!IS_GUEST) ? $userdata['user_lastvisit'] : '';
$search_id   = (isset($_GET['search_id']) && verify_id($_GET['search_id'], SEARCH_ID_LENGTH)) ? $_GET['search_id'] : '';
$session_id  = $userdata['session_id'];

$cat_forum = $tor_to_show = $search_in_forums_ary = array();
$title_match_sql = $title_match_q = $search_in_forums_csv = '';
$tr_error = $poster_error = false;
$row_num = $tor_count = 0;

$torrents_tbl = BB_BT_TORRENTS     .' tor';
$cat_tbl      = BB_CATEGORIES      .' c';
$forums_tbl   = BB_FORUMS          .' f';
$topics_tbl   = BB_TOPICS          .' t';
$users_tbl    = BB_USERS           .' u';
$tracker_tbl  = BB_BT_TRACKER      .' tr';
$tr_snap_tbl  = BB_BT_TRACKER_SNAP .' sn';
$dl_stat_tbl  = BB_BT_DLSTATUS     .' dl';

//
// Search options
//
// Key values
$search_all = -1;
$never      = -2;

$sort_asc   = 1;
$sort_desc  = 2;

$ord_posted   = 1;
$ord_name     = 2;
$ord_compl    = 4;
$ord_repl     = 5;
$ord_views    = 6;
$ord_size     = 7;
$ord_last_p   = 8;
$ord_last_s   = 9;
$ord_seeders  = 10;
$ord_leechers = 11;
$ord_sp_up    = 12;
$ord_sp_down  = 13;

// Order options
$order_opt = array(
	$ord_posted    => array(
		                 'lang' => $lang['IS_REGISTERED'],
		                 'sql'  => 'tor.reg_time',
		                ),
	$ord_name      => array(
		                 'lang' => $lang['BT_TOPIC_TITLE'],
		                 'sql'  => 't.topic_title',
		                ),
	$ord_compl     => array(
		                 'lang' => $lang['COMPLETED'],
		                 'sql'  => 'tor.complete_count',
		                ),
	$ord_seeders   => array(
		                 'lang' => 'Seeders',
		                 'sql'  => 'sn.seeders',
		                ),
	$ord_leechers  => array(
		                 'lang' => 'Leechers',
		                 'sql'  => 'sn.leechers',
		                ),
	$ord_sp_up     => array(
		                 'lang' => 'Speed UP',
		                 'sql'  => 'sn.speed_up',
		                ),
	$ord_sp_down   => array(
		                 'lang' => 'Speed DOWN',
		                 'sql'  => 'sn.speed_down',
		                ),
	$ord_repl      => array(
		                 'lang' => $lang['BT_REPLIES'],
		                 'sql'  => 't.topic_replies',
		                ),
	$ord_views     => array(
		                 'lang' => $lang['BT_VIEWS'],
		                 'sql'  => 't.topic_views',
		                ),
	$ord_size      => array(
		                 'lang' => $lang['SIZE'],
		                 'sql'  => 'tor.size',
		                ),
	$ord_last_p    => array(
		                 'lang' => $lang['BT_LAST_POST'],
		                 'sql'  => 't.topic_last_post_id',
		                ),
	$ord_last_s    => array(
		                 'lang' => $lang['BT_SEEDER_LAST_SEEN'],
		                 'sql'  => 'tor.seeder_last_seen',
		                ),
);
$order_select = array();
foreach ($order_opt as $val => $opt)
{
	$order_select[$opt['lang']] = $val;
}

// Sort direction
$sort_opt = array(
	$sort_asc  => array(
		             'lang' => $lang['ASC'],
		             'sql'  => 'ASC',
		            ),
	$sort_desc => array(
		             'lang' => $lang['DESC'],
		             'sql'  => 'DESC',
		            ),
);

// Previous days
$time_opt = array(
	$search_all => array(
		     'lang' => $lang['BT_ALL_DAYS_FOR'],
		     'sql'  => 0,
		    ),
	1  => array(
		     'lang' => $lang['BT_1_DAY_FOR'],
		     'sql'  => TIMENOW - 86400,
		    ),
	3  => array(
		     'lang' => $lang['BT_3_DAY_FOR'],
		     'sql'  => TIMENOW - 86400*3,
		    ),
	7  => array(
		     'lang' => $lang['BT_7_DAYS_FOR'],
		     'sql'  => TIMENOW - 86400*7,
		    ),
	14 => array(
		     'lang' => $lang['BT_2_WEEKS_FOR'],
		     'sql'  => TIMENOW - 86400*14,
		    ),
	30 => array(
		     'lang' => $lang['BT_1_MONTH_FOR'],
		     'sql'  => TIMENOW - 86400*30,
		    ),
);
$time_select = array();
foreach ($time_opt as $val => $opt)
{
	$time_select[$opt['lang']] = $val;
}

// Seeder not seen
$s_not_seen_opt = array(
	$search_all => array(
		     'lang' => $lang['BT_DISREGARD'],
		     'sql'  => 0,
		    ),
	1  => array(
		     'lang' => $lang['BT_1_DAY'],
		     'sql'  => TIMENOW - 86400,
		    ),
	3  => array(
		     'lang' => $lang['BT_3_DAYS'],
		     'sql'  => TIMENOW - 86400*3,
		    ),
	7  => array(
		     'lang' => $lang['BT_7_DAYS'],
		     'sql'  => TIMENOW - 86400*7,
		    ),
	14 => array(
		     'lang' => $lang['BT_2_WEEKS'],
		     'sql'  => TIMENOW - 86400*14,
		    ),
	30 => array(
		     'lang' => $lang['BT_1_MONTH'],
		     'sql'  => TIMENOW - 86400*30,
		    ),
	$never  => array(
		     'lang' => $lang['BT_NEVER'],
		     'sql'  => 0,
		    ),
);
$s_not_seen_select = array();
foreach ($s_not_seen_opt as $val => $opt)
{
	$s_not_seen_select[$opt['lang']] = $val;
}

$GPC = array(
#	  var_name              key_name def_value   GPC type
	'all_words'     => array('allw', 1,           CHBOX),
	'active'        => array('a',    0,           CHBOX),
	'cat'           => array('c',    null,        REQUEST),
	'dl_cancel'     => array('dla',  0,           CHBOX),
	'dl_compl'      => array('dlc',  0,           CHBOX),
	'dl_down'       => array('dld',  0,           CHBOX),
	'dl_will'       => array('dlw',  0,           CHBOX),
	'forum'         => array('f',    $search_all, REQUEST),
	'my'            => array('my',   0,           CHBOX),
	'new'           => array('new',  0,           CHBOX),
	'title_match'   => array('nm',   null,        REQUEST),
	'order'         => array('o',    $ord_posted, SELECT),
	'poster_id'     => array('pid',  null,        GET),
	'poster_name'   => array('pn',   null,        REQUEST),
	'user_releases' => array('rid',  null,        GET),
	'sort'          => array('s',    $sort_desc,  SELECT),
	'seed_exist'    => array('sd',   0,           CHBOX),
	'show_author'   => array('da',   1,           CHBOX),
	'show_cat'      => array('dc',   0,           CHBOX),
	'show_forum'    => array('df',   1,           CHBOX),
	'show_speed'    => array('ds',   0,           CHBOX),
	's_not_seen'    => array('sns',  $search_all, SELECT),
	'time'          => array('tm',   $search_all, SELECT),
);

// Define all GPC vars with default values
foreach ($GPC as $name => $params)
{
	${"{$name}_key"} = $params[KEY_NAME];
	${"{$name}_val"} = $params[DEF_VAL];
}

if (isset($_GET[$user_releases_key]))
{
	// Search releases by user
	$_GET[$poster_id_key] = (int) $_GET[$user_releases_key];
	$_REQUEST[$forum_key] = $search_all;
}
else if (!empty($_REQUEST['max']))
{
	$_REQUEST[$forum_key] = $search_all;
}
else
{
	// Get "checkbox" and "select" vars
	foreach ($GPC as $name => $params)
	{
		if ($params[GPC_TYPE] == CHBOX)
		{
			checkbox_get_val($params[KEY_NAME], ${"{$name}_val"}, $params[DEF_VAL]);
		}
		else if ($params[GPC_TYPE] == SELECT)
		{
			select_get_val($params[KEY_NAME], ${"{$name}_val"}, ${"{$name}_opt"}, $params[DEF_VAL]);
		}
	}
}

// Restore torrents list and search settings if we have valid $search_id
$tor_list_ary = array();
$tor_list_sql = '';

if ($search_id)
{
	$row = DB()->fetch_row("
		SELECT search_array, search_settings
		FROM ". BB_SEARCH ."
		WHERE session_id = '$session_id'
			AND search_type = ". SEARCH_TYPE_TRACKER ."
			AND search_id = '$search_id'
		LIMIT 1
	");

	if (empty($row['search_settings']))
	{
		bb_die($lang['SESSION_EXPIRED']);
	}

	$previous_settings = unserialize($row['search_settings']);
	$tor_list_sql = $row['search_array'];
	$tor_list_ary = explode(',', $tor_list_sql);
	$tor_count    = count($tor_list_ary);
	unset($row);
}

// Get allowed for searching forums list
if (!$forums = $datastore->get('cat_forums'))
{
	$datastore->update('cat_forums');
	$forums = $datastore->get('cat_forums');
}
$cat_title_html = $forums['cat_title_html'];
$forum_name_html = $forums['forum_name_html'];

$excluded_forums_csv = $user->get_excluded_forums(AUTH_READ);
$allowed_forums = array_diff(explode(',', $forums['tracker_forums']), explode(',', $excluded_forums_csv));

foreach ($allowed_forums as $forum_id)
{
	$f = $forums['f'][$forum_id];
	$cat_forum['c'][$f['cat_id']][] = $forum_id;

	if ($f['forum_parent'])
	{
		$cat_forum['subforums'][$forum_id] = true;
		$cat_forum['forums_with_sf'][$f['forum_parent']] = true;
	}
}
unset($forums);
$datastore->rm('cat_forums');

// Get current search settings
if (!$set_default)
{
	// Search in forum or category
	// Get requested cat_id
	$search_in_forums_fary = array();

	if ($req_cat_id =& $_REQUEST[$cat_key])
	{
		if (isset($cat_forum['c'][$req_cat_id]))
		{
			$valid_forums = $cat_forum['c'][$req_cat_id];
			$forum_val = join(',', $valid_forums);
		}
	}
	// Get requested forum_id(s)
	else if ($req_forums =& $_REQUEST[$forum_key])
	{
		if ($req_forums != $search_all)
		{
			$req_forums = (array) $req_forums;
			array_deep($req_forums, 'intval');
			$valid_forums = array_intersect($req_forums, $allowed_forums);
			$forum_val = join(',', $valid_forums);
		}
	}
	else if (isset($previous_settings[$forum_key]))
	{
		$valid_forums = array_intersect(explode(',', $previous_settings[$forum_key]), $allowed_forums);
		$forum_val = join(',', $valid_forums);
	}

    if ($forum_val && $forum_val != $search_all)
	{
		$search_in_forums_ary  = array_slice(explode(',', $forum_val), 0, $max_forums_selected);
		$search_in_forums_fary = array_flip($search_in_forums_ary);
		$search_in_forums_csv  = join(',', $search_in_forums_ary);
		$forum_val = $search_in_forums_csv;
	}
	else
	{
		$forum_val = $search_all;
	}

	// Get poster_id
	if (!$my_val)
	{
		$req_poster_id = '';

		if (isset($_GET[$poster_id_key]) && !$search_id)
		{
			$req_poster_id = intval($_GET[$poster_id_key]);
		}
		else if (isset($_POST[$poster_name_key]) && !$search_id)
		{
			if ($req_poster_name = clean_username($_POST[$poster_name_key]))
			{
				$poster_name_sql = str_replace("\\'", "''", $req_poster_name);

				if ($poster_id = get_user_id($poster_name_sql))
				{
					$poster_id_val = $poster_id;
					$poster_name_val = stripslashes(html_entity_decode($req_poster_name));
				}
				else
				{
					$poster_name_val = $lang['BT_USER_NOT_FOUND'];
					$tr_error = $poster_error = true;
				}
			}
		}
		else if ($search_id && $previous_settings[$poster_id_key])
		{
			$poster_id_val = intval($previous_settings[$poster_id_key]);
			$poster_name_val = ($previous_settings[$poster_name_key]) ? $previous_settings[$poster_name_key] : '';
		}

		if ($req_poster_id)
		{
			if ($req_poster_id == ANONYMOUS)
			{
				$poster_id_val = ANONYMOUS;
				$poster_name_val = $lang['GUEST'];
			}
			else if ($poster_name_val = get_username($req_poster_id))
			{
				$poster_name_val = stripslashes(html_entity_decode($poster_name_val));
				$poster_id_val = $req_poster_id;
			}
		}
	}

	if ($tm =& $_REQUEST[$title_match_key] AND is_string($tm))
	{
		if ($tmp = mb_substr(trim($tm), 0, $title_match_max_len))
		{
			$title_match_val = $tmp;
			$title_match_sql = clean_text_match($title_match_val, true, false, false);
		}
	}
}

$dl_status = array();
if ($dl_cancel_val) $dl_status[] = DL_STATUS_CANCEL;
if ($dl_compl_val)  $dl_status[] = DL_STATUS_COMPLETE;
if ($dl_down_val)   $dl_status[] = DL_STATUS_DOWN;
if ($dl_will_val)   $dl_status[] = DL_STATUS_WILL;
$dl_status_csv = join(',', $dl_status);

// Switches
$only_new    = ($new_val && !IS_GUEST);
$seed_exist  = (bool) $seed_exist_val;
$only_active = ($active_val || $seed_exist);
$dl_search   = ($dl_status && !IS_GUEST);
$only_my     = ($my_val && !IS_GUEST && !$dl_search);
$prev_days   = ($time_val != $search_all);
$poster_id   = (bool) $poster_id_val;
$title_match = (bool) $title_match_sql;
$s_not_seen  = ($s_not_seen_val != $search_all);

$hide_cat    = intval(!$show_cat_val);
$hide_forum  = intval(!$show_forum_val);
$hide_author = intval(!$show_author_val);
$hide_speed  = intval(!$show_speed_val);

if ($s_not_seen_val != $search_all)
{
	$seed_exist_val = 0;
}
if ($seed_exist_val)
{
	$active_val = 1;
}
if ($dl_search)
{
	$my_val = 0;
}

if ($allowed_forums)
{
    // Text search
	$search_match_topics_csv = '';

	if ($title_match)
	{
		$title_match_topics = get_title_match_topics($title_match_sql, $title_match_limit, $search_in_forums_ary);

		if (!$search_match_topics_csv = join(',', $title_match_topics))
		{
			$tr_error = true;
		}
	}
	else
	{
		$title_match_val = '';
	}

	// Get torrents list
	if (!$tr_error && !$tor_list_sql)
	{
		$reg_time         = $time_opt[$time_val]['sql'];
		$poster_id_sql    = (int) $poster_id_val;
		$s_seen_time      = $s_not_seen_opt[$s_not_seen_val]['sql'];
		$s_seen_sign      = ($s_not_seen_val == $never) ? '=' : '<';
		$s_seen_exclude   = ($s_not_seen_val == $never) ? '' : "AND tor.seeder_last_seen != 0";
		$order_by_peers   = ($order_val == $ord_seeders || $order_val == $ord_leechers);
		$order_by_speed   = ($order_val == $ord_sp_up || $order_val == $ord_sp_down);

		$join_t  = in_array($order_val, array($ord_name, $ord_repl, $ord_views, $ord_last_p, $title_match));
		$join_sn = ($only_active || $order_by_peers || $order_by_speed);
		$join_dl = $dl_search;

		// Start building SQL
		$SQL = DB()->get_empty_sql_array();

		// SELECT
		$SQL['SELECT'][] = "tor.topic_id";

		// FROM
		$SQL['FROM'][] = $torrents_tbl;

		if ($join_t)
		{
			$SQL['INNER JOIN'][] = "$topics_tbl ON(t.topic_id = tor.topic_id)";
		}
		if ($join_sn)
		{
			$SQL['LEFT JOIN'][] = "$tr_snap_tbl ON(sn.topic_id = tor.topic_id)";
		}
		if ($join_dl)
		{
			$SQL['INNER JOIN'][] = "$dl_stat_tbl ON(
				    dl.topic_id = tor.topic_id
				AND dl.user_id = $user_id
				AND dl.user_status IN($dl_status_csv)
			)";
		}

		// WHERE
		$title_match_notfound_flag = false;
		if ($search_match_topics_csv)
		{
			$SQL['WHERE'][] = "tor.topic_id IN($search_match_topics_csv)";
		}
		if ($search_in_forums_csv)
		{
			$SQL['WHERE'][] = "tor.forum_id IN($search_in_forums_csv)";
		}
		if ($excluded_forums_csv)
		{
			$SQL['WHERE'][] = "tor.forum_id NOT IN($excluded_forums_csv)";
		}
		if ($poster_id)
		{
			$SQL['WHERE'][] = "tor.poster_id = $poster_id_sql";
		}
		if ($only_new)
		{
			$SQL['WHERE'][] = "tor.reg_time > $lastvisit";
		}
		if ($prev_days)
		{
			$SQL['WHERE'][] = "tor.reg_time > $reg_time";
		}
		if ($s_not_seen)
		{
			$SQL['WHERE'][] = "tor.seeder_last_seen $s_seen_sign $s_seen_time $s_seen_exclude";
		}
		if ($only_my)
		{
			$SQL['WHERE'][] = "tor.poster_id = $user_id";
		}
		if ($only_active)
		{
			$SQL['WHERE'][] = "sn.topic_id IS NOT NULL";
		}
		if ($seed_exist)
		{
			$SQL['WHERE'][] = "sn.seeders >= 1";
		}

		// ORDER
		$SQL['ORDER BY'][] = "{$order_opt[$order_val]['sql']} {$sort_opt[$sort_val]['sql']}";

		// LIMIT
		$SQL['LIMIT'][] = $tor_search_limit;

		if ($title_match && $title_match_notfound_flag)
		{
			$tor_list_sql = '';
			$tor_count = 0;
		}
		else
		{
			foreach (DB()->fetch_rowset($SQL) as $row)
			{
				$tor_list_ary[] = $row['topic_id'];
			}
			$tor_list_sql = join(',', $tor_list_ary);
			$tor_count = count($tor_list_ary);
		}
	}

	if (!$tor_list_sql || $start > $tor_count)
	{
		$template->assign_vars(array(
			'TOR_NOT_FOUND' => true,
			'NO_MATCH_MSG'  => $lang['NO_MATCH'],
		));
	}
	else
	{
		// Save result in DB
		if ($tor_count > $per_page && !$search_id)
		{
			$search_id = make_rand_str(SEARCH_ID_LENGTH);
			$search_type = SEARCH_TYPE_TRACKER;

			$columns =  'session_id,   search_type,   search_id,   search_time,   search_settings,  search_array';
			$values = "'$session_id', $search_type, '$search_id', ". TIMENOW .", '$curr_set_sql', '$tor_list_sql'";

			DB()->query("REPLACE INTO ". BB_SEARCH ." ($columns) VALUES ($values)");
		}
		unset($columns, $values, $curr_set_sql, $tor_list_sql);

		$tor_to_show = ($tor_count > $per_page) ? array_slice($tor_list_ary, $start, $per_page) : $tor_list_ary;

		if (!$tor_to_show = join(',', $tor_to_show))
		{
			bb_die($lang['NO_SEARCH_MATCH']);
		}

		// SELECT
		$select = "
			SELECT
				tor.topic_id, tor.post_id, tor.attach_id, tor.size, tor.reg_time, tor.complete_count, tor.seeder_last_seen, tor.tor_status, tor.tor_type,
				t.topic_title, t.topic_time, t.topic_replies, t.topic_views, sn.seeders, sn.leechers, tor.info_hash
		";
		$select .= (!$hide_speed)  ? ", sn.speed_up, sn.speed_down" : '';
		$select .= (!$hide_forum)  ? ", tor.forum_id" : '';
		$select .= (!$hide_cat)    ? ", f.cat_id" : '';
		$select .= (!$hide_author) ? ", tor.poster_id, u.username" : '';
		$select .= (!IS_GUEST)     ? ", dl.user_status AS dl_status" : '';

		// FROM
		$from = "
			FROM $torrents_tbl
			LEFT JOIN $topics_tbl ON(t.topic_id = tor.topic_id)
		";
		$from .= (!$hide_cat) ? "
			LEFT JOIN $forums_tbl ON(f.forum_id = t.forum_id)
		" : '';
		$from .= (!$hide_author) ? "
			LEFT JOIN $users_tbl ON(u.user_id = tor.poster_id)
		" : '';
		$from .= (!IS_GUEST) ? "
			LEFT JOIN $dl_stat_tbl ON(dl.topic_id = tor.topic_id AND dl.user_id = $user_id)
		" : '';
		$from .= "LEFT JOIN $tr_snap_tbl ON(sn.topic_id = tor.topic_id)";

		// WHERE
		$where = "
			WHERE tor.topic_id IN($tor_to_show)
		";

		// ORDER
		$order = "ORDER BY ". $order_opt[$order_val]['sql'];

		// SORT
		$sort = $sort_opt[$sort_val]['sql'];

		// LIMIT
		$limit = "LIMIT $per_page";

		$sql = "
			$select
			$from
			$where
			$order
				$sort
			$limit
		";

		$passkey = DB()->fetch_row("SELECT auth_key FROM ". BB_BT_USERS ." WHERE user_id = ". (int) $user_id ." LIMIT 1");
		// Build torrents table
		foreach (DB()->fetch_rowset($sql) as $tor)
		{
			$dl = isset($tor['speed_down']) ? $tor['speed_down'] : 0;
			$ul = isset($tor['speed_up']) ? $tor['speed_up'] : 0;

			$seeds  = $tor['seeders'];
			$leechs = $tor['leechers'];
			$s_last = $tor['seeder_last_seen'];
			$att_id = $tor['attach_id'];
			$size   = $tor['size'];
			$tor_magnet = create_magnet($tor['info_hash'], $passkey['auth_key'], $userdata['session_logged_in']);
			$compl  = $tor['complete_count'];
			$dl_sp  = ($dl) ? humn_size($dl, 0, 'KB') .'/s' : '0 KB/s';
			$ul_sp  = ($ul) ? humn_size($ul, 0, 'KB') .'/s' : '0 KB/s';

			$dl_class  = isset($tor['dl_status']) ? $dl_link_css[$tor['dl_status']] : 'genmed';
			$row_class = !($row_num & 1) ? $row_class_1 : $row_class_2;
			$row_num++;

			$cat_id    = (!$hide_cat && isset($tor['cat_id'])) ? $tor['cat_id'] : '';
			$forum_id  = (!$hide_forum && isset($tor['forum_id'])) ? $tor['forum_id'] : '';
			$poster_id = (!$hide_author && isset($tor['poster_id'])) ? $tor['poster_id'] : '';

			// Gold/Silver releases mod
			$is_gold = '';
			if ($bb_cfg['gold_silver_enabled'])
			{
				if ($tor['tor_type'] == TOR_TYPE_GOLD)
				{
					$is_gold = '<img src="images/tor_gold.gif" width="16" height="15" title="'.$lang['GOLD'].'" />&nbsp;';
				}
				elseif ($tor['tor_type'] == TOR_TYPE_SILVER)
				{
					$is_gold = '<img src="images/tor_silver.gif" width="16" height="15" title="'.$lang['SILVER'].'" />&nbsp;';
				}
			}
			// END Gold/Silver releases mod

			$template->assign_block_vars('tor', array(
				'CAT_ID'       => $cat_id,
				'CAT_TITLE'    => ($cat_id) ? $cat_title_html[$cat_id] : '',
				'FORUM_ID'     => $forum_id,
				'FORUM_NAME'   => ($forum_id) ? $forum_name_html[$forum_id] : '',
				'TOPIC_ID'     => $tor['topic_id'],
				'TOPIC_TITLE'  => wbr($tor['topic_title']),
				'TOPIC_TIME'   => bb_date($tor['topic_time'], 'd-M-y') .' <b>&middot;</b> '. delta_time($tor['topic_time']),
				'POST_ID'      => $tor['post_id'],
				'POSTER_ID'    => $poster_id,
				'USERNAME'     => isset($tor['username']) ? wbr($tor['username']) : '',

				'ROW_CLASS'    => $row_class,
				'ROW_NUM'      => $row_num,
				'DL_CLASS'     => $dl_class,
				'IS_NEW'       => (!IS_GUEST && $tor['reg_time'] > $lastvisit),
				'USER_AUTHOR'  => (!IS_GUEST && $poster_id == $user_id),

				'ATTACH_ID'    => $att_id,
				'MAGNET'       => $tor_magnet,
				'TOR_TYPE'     => $is_gold,

				'TOR_FROZEN'   => isset($bb_cfg['tor_frozen'][$tor['tor_status']]),
				'TOR_STATUS_ICON' => $bb_cfg['tor_icons'][$tor['tor_status']],
				'TOR_STATUS_TEXT' => $lang['tor_status'][$tor['tor_status']],

				'TOR_SIZE_RAW' => $size,
				'TOR_SIZE'     => humn_size($size),
				'UL_SPEED'     => $ul_sp,
				'DL_SPEED'     => $dl_sp,
				'SEEDS'        => ($seeds) ? $seeds : 0,
				'SEEDS_TITLE'  => ($seeds) ? 'Seeders' : (" Last seen: \n ". (($s_last) ? bb_date($s_last, $date_format) : 'Never')),
				'LEECHS'       => ($leechs) ? $leechs : 0,
				'COMPLETED'    => ($compl) ? $compl : 0,
				'REPLIES'      => $tor['topic_replies'],
				'VIEWS'        => $tor['topic_views'],
				'ADDED_RAW'    => $tor['reg_time'],
				'ADDED_TIME'   => bb_date($tor['reg_time'], $time_format),
				'ADDED_DATE'   => bb_date($tor['reg_time'], $date_format),
			));
		}
	}
}
else
{
	$template->assign_vars(array(
		'TOR_NOT_FOUND' => true,
		'NO_MATCH_MSG'  => $lang['BT_NO_SEARCHABLE_FORUMS'],
	));
}

// Pagination
if ($tor_count)
{
	$base_url = "$tracker_url?search_id=$search_id";
	$search_matches = ($tor_count == 1) ? sprintf($lang['FOUND_SEARCH_MATCH'], $tor_count) : sprintf($lang['FOUND_SEARCH_MATCHES'], $tor_count);
	$search_max = "(max: $tor_search_limit)";

	$template->assign_vars(array(
		'MATCHES'     => $search_matches,
		'SERACH_MAX'  => $search_max,
		'PAGINATION'  => generate_pagination($base_url, $tor_count, $per_page, $start),
		'PAGE_NUMBER' => sprintf($lang['PAGE_OF'], (floor($start / $per_page) + 1), ceil($tor_count / $per_page)),
	));
}

if(empty($cat_forum))
{
	message_die(GENERAL_MESSAGE, $lang['BT_NO_SEARCHABLE_FORUMS']);
}

// Forum select
$opt = '';
foreach ($cat_forum['c'] as $cat_id => $forums_ary)
{
	$opt .= '<optgroup label="&nbsp;'. $cat_title_html[$cat_id] ."\">\n";

	foreach ($forums_ary as $forum_id)
	{
		$forum_name = $forum_name_html[$forum_id];
		$forum_name = str_short($forum_name, $max_forum_name_len-2);
		$style = '';
		if (!isset($cat_forum['subforums'][$forum_id]))
		{
			$class = 'root_forum';
			$class .= isset($cat_forum['forums_with_sf'][$forum_id]) ? ' has_sf' : '';
			$style = " class=\"$class\"";
		}
		$selected = (isset($search_in_forums_fary[$forum_id])) ? HTML_SELECTED : '';
		$opt .= '<option id="fs-'. $forum_id .'" value="'. $forum_id .'"'. $style . $selected .'>'. (isset($cat_forum['subforums'][$forum_id]) ? HTML_SF_SPACER : '') . $forum_name ."&nbsp;</option>\n";
	}

	$opt .= "</optgroup>\n";
}
$search_all_opt = '<option value="'. $search_all .'"'. (($forum_val == $search_all) ? HTML_SELECTED : '') .'>&nbsp;'. htmlCHR($lang['ALL_AVAILABLE']) ."</option>\n";
$cat_forum_select = "\n".'<select id="fs" style="width: 100%;" name="'. $forum_key .'[]" multiple="multiple" size="'. $forum_select_size ."\">\n". $search_all_opt . $opt ."</select>\n";

// Sort dir
$template->assign_vars(array(
	'SORT_NAME'         => $sort_key,
	'SORT_ASC'          => $sort_asc,
	'SORT_DESC'         => $sort_desc,
	'SORT_ASC_CHECKED'  => ($sort_val == $sort_asc) ? HTML_CHECKED : '',
	'SORT_DESC_CHECKED' => ($sort_val == $sort_desc) ? HTML_CHECKED : '',
));

// Displaying options
$template->assign_vars(array(
	'SHOW_CAT_CHBOX'    => build_checkbox ($show_cat_key,    $lang['BT_SHOW_CAT'],        $show_cat_val),
	'SHOW_FORUM_CHBOX'  => build_checkbox ($show_forum_key,  $lang['BT_SHOW_FORUM'],      $show_forum_val),
	'SHOW_AUTHOR_CHBOX' => build_checkbox ($show_author_key, $lang['BT_SHOW_AUTHOR'],     $show_author_val),
	'SHOW_SPEED_CHBOX'  => build_checkbox ($show_speed_key,  $lang['BT_SHOW_SPEED'],      $show_speed_val),
	'ALL_WORDS_CHBOX'   => build_checkbox ($all_words_key,   $lang['SEARCH_ALL_WORDS'],   $all_words_val),

	'ONLY_MY_CHBOX'     => build_checkbox ($my_key,          $lang['BT_ONLY_MY'],         $only_my,       IS_GUEST),
	'ONLY_ACTIVE_CHBOX' => build_checkbox ($active_key,      $lang['BT_ONLY_ACTIVE'],     $active_val),
	'SEED_EXIST_CHBOX'  => build_checkbox ($seed_exist_key,  $lang['BT_SEED_EXIST'],      $seed_exist),
	'ONLY_NEW_CHBOX'    => build_checkbox ($new_key,         $lang['BT_ONLY_NEW'],        $only_new,      IS_GUEST),

	'DL_CANCEL_CHBOX'   => build_checkbox ($dl_cancel_key,   $lang['SEARCH_DL_CANCEL'],   $dl_cancel_val, IS_GUEST, 'dlCancel'),
	'DL_COMPL_CHBOX'    => build_checkbox ($dl_compl_key,    $lang['SEARCH_DL_COMPLETE'], $dl_compl_val,  IS_GUEST, 'dlComplete'),
	'DL_DOWN_CHBOX'     => build_checkbox ($dl_down_key,     $lang['SEARCH_DL_DOWN'],     $dl_down_val,   IS_GUEST, 'dlDown'),
	'DL_WILL_CHBOX'     => build_checkbox ($dl_will_key,     $lang['SEARCH_DL_WILL'],     $dl_will_val,   IS_GUEST, 'dlWill'),

	'POSTER_NAME_NAME' => $poster_name_key,
	'POSTER_NAME_VAL'  => htmlCHR($poster_name_val),
	'TITLE_MATCH_NAME' => $title_match_key,
	'TITLE_MATCH_VAL'  => $title_match_val,

	'AJAX_TOPICS'      => $user->opt_js['tr_t_ax'],
	'SHOW_TIME_TOPICS' => $user->opt_js['tr_t_t'],
	'U_SEARCH_USER'    => "search.php?mode=searchuser&input_name=$poster_name_key",
));

// Hidden fields
$save_through_pages = array(
	'all_words',
	'active',
	'dl_cancel',
	'dl_compl',
	'dl_down',
	'dl_will',
	'my',
	'new',
	'seed_exist',
	'show_author',
	'show_cat',
	'show_forum',
	'show_speed',
);
$hidden_fields = array();
foreach ($save_through_pages as $name)
{
	$hidden_fields['prev_'. ${"{$name}_key"}] = ${"{$name}_val"};
}

// Set colspan
$tor_colspan = $tor_colspan - $hide_cat - $hide_forum - $hide_author - $hide_speed;

$template->assign_vars(array(
	'PAGE_TITLE'        => $lang['TRACKER'],
	'S_HIDDEN_FIELDS'   => build_hidden_fields($hidden_fields),
	'CAT_FORUM_SELECT'  => $cat_forum_select,
	'ORDER_SELECT'      => build_select($order_key, $order_select, $order_val),
	'TIME_SELECT'       => build_select($time_key, $time_select, $time_val),
	'S_NOT_SEEN_SELECT' => build_select($s_not_seen_key, $s_not_seen_select, $s_not_seen_val),
	'TOR_SEARCH_ACTION' => $tracker_url,
	'TOR_COLSPAN'       => $tor_colspan,
	'TITLE_MATCH_MAX'   => $title_match_max_len,
	'POSTER_NAME_MAX'   => $poster_name_max_len,
	'POSTER_ERROR'      => $poster_error,
	'SHOW_SEARCH_OPT'   => (bool) $allowed_forums,
	'SHOW_CAT'          => $show_cat_val,
	'SHOW_FORUM'        => $show_forum_val,
	'SHOW_AUTHOR'       => $show_author_val,
	'SHOW_SPEED'        => $show_speed_val,

	'TR_CAT_URL'        => "$tracker_url?$cat_key=",
	'TR_FORUM_URL'      => "$tracker_url?$forum_key=",
	'TR_POSTER_URL'     => "$tracker_url?$poster_id_key=",
));

print_page('tracker.tpl');