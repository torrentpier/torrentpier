<?php

/**
* All Attachment Functions only needed in Admin
*/

/**
* Set/Change Quotas
*/
function process_quota_settings($mode, $id, $quota_type, $quota_limit_id = 0)
{
	$id = (int) $id;
	$quota_type = (int) $quota_type;
	$quota_limit_id = (int) $quota_limit_id;

	if ($mode == 'user')
	{
		if (!$quota_limit_id)
		{
			$sql = 'DELETE FROM ' . BB_QUOTA . "
				WHERE user_id = $id
					AND quota_type = $quota_type";
		}
		else
		{
			// Check if user is already entered
			$sql = 'SELECT user_id
				FROM ' . BB_QUOTA . "
				WHERE user_id = $id
					AND quota_type = $quota_type";

			if( !($result = DB()->sql_query($sql)) )
			{
				bb_die('Could not get entry #1');
			}

			if (DB()->num_rows($result) == 0)
			{
				$sql_ary = array(
					'user_id'		=> (int) $id,
					'group_id'		=> 0,
					'quota_type'	=> (int) $quota_type,
					'quota_limit_id'=> (int) $quota_limit_id
				);

				$sql = 'INSERT INTO ' . BB_QUOTA . ' ' . attach_mod_sql_build_array('INSERT', $sql_ary);
			}
			else
			{
				$sql = 'UPDATE ' . BB_QUOTA . "
					SET quota_limit_id = $quota_limit_id
					WHERE user_id = $id
						AND quota_type = $quota_type";
			}
			DB()->sql_freeresult($result);
		}

		if (!($result = DB()->sql_query($sql)))
		{
			bb_die('Unable to update quota settings');
		}

	}
	else if ($mode == 'group')
	{
		if (!$quota_limit_id)
		{
			$sql = 'DELETE FROM ' . BB_QUOTA . "
				WHERE group_id = $id
					AND quota_type = $quota_type";

			if (!($result = DB()->sql_query($sql)))
			{
				bb_die('Unable to delete quota settings');
			}
		}
		else
		{
			// Check if user is already entered
			$sql = 'SELECT group_id
				FROM ' . BB_QUOTA . "
				WHERE group_id = $id
					AND quota_type = $quota_type";

			if (!($result = DB()->sql_query($sql)))
			{
				bb_die('Could not get entry #2');
			}

			if (DB()->num_rows($result) == 0)
			{
				$sql = 'INSERT INTO ' . BB_QUOTA . " (user_id, group_id, quota_type, quota_limit_id)
					VALUES (0, $id, $quota_type, $quota_limit_id)";
			}
			else
			{
				$sql = 'UPDATE ' . BB_QUOTA . " SET quota_limit_id = $quota_limit_id
					WHERE group_id = $id AND quota_type = $quota_type";
			}

			if (!DB()->sql_query($sql))
			{
				bb_die('Unable to update quota settings');
			}
		}
	}
}

/**
* sort multi-dimensional Array
*/
function sort_multi_array ($sort_array, $key, $sort_order, $pre_string_sort = 0)
{
	$last_element = sizeof($sort_array) - 1;

	if (!$pre_string_sort)
	{
		$string_sort = (!is_numeric(@$sort_array[$last_element-1][$key]) ) ? true : false;
	}
	else
	{
		$string_sort = $pre_string_sort;
	}

	for ($i = 0; $i < $last_element; $i++)
	{
		$num_iterations = $last_element - $i;

		for ($j = 0; $j < $num_iterations; $j++)
		{
			$next = 0;

			// do checks based on key
			$switch = false;
			if (!$string_sort)
			{
				if (($sort_order == 'DESC' && intval(@$sort_array[$j][$key]) < intval(@$sort_array[$j + 1][$key])) || ($sort_order == 'ASC' && intval(@$sort_array[$j][$key]) > intval(@$sort_array[$j + 1][$key])))
				{
					$switch = true;
				}
			}
			else
			{
				if (($sort_order == 'DESC' && strcasecmp(@$sort_array[$j][$key], @$sort_array[$j + 1][$key]) < 0) || ($sort_order == 'ASC' && strcasecmp(@$sort_array[$j][$key], @$sort_array[$j + 1][$key]) > 0))
				{
					$switch = true;
				}
			}

			if ($switch)
			{
				$temp = $sort_array[$j];
				$sort_array[$j] = $sort_array[$j + 1];
				$sort_array[$j + 1] = $temp;
			}
		}
	}

	return $sort_array;
}

/**
* Returns the filesize of the upload directory in human readable format
*/
function get_formatted_dirsize()
{
	global $attach_config, $upload_dir, $lang;

	$upload_dir_size = 0;

	if ($dirname = @opendir($upload_dir))
	{
		while ($file = @readdir($dirname))
		{
			if ($file != 'index.php' && $file != '.htaccess' && !is_dir($upload_dir . '/' . $file) && !is_link($upload_dir . '/' . $file))
			{
				$upload_dir_size += @filesize($upload_dir . '/' . $file);
			}
		}
		@closedir($dirname);
	}
	else
	{
		$upload_dir_size = $lang['NOT_AVAILABLE'];
		return $upload_dir_size;
	}

	return humn_size($upload_dir_size);
}

/*
* Build SQL-Statement for the search feature
*/
function search_attachments($order_by, &$total_rows)
{
	global $lang;

	$where_sql = array();

	// Get submitted Vars
	$search_vars = array('search_keyword_fname', 'search_keyword_comment', 'search_author', 'search_size_smaller', 'search_size_greater', 'search_count_smaller', 'search_count_greater', 'search_days_greater', 'search_forum', 'search_cat');

	for ($i = 0; $i < sizeof($search_vars); $i++)
	{
		$$search_vars[$i] = get_var($search_vars[$i], '');
	}

	// Author name search
	if ($search_author != '')
	{
		// Bring in line with 2.0.x expected username
		$search_author = addslashes(html_entity_decode($search_author));
		$search_author = stripslashes(clean_username($search_author));

		// Prepare for directly going into sql query
		$search_author = str_replace('*', '%', attach_mod_sql_escape($search_author));

		// We need the post_id's, because we want to query the Attachment Table
		$sql = 'SELECT user_id FROM ' . BB_USERS . " WHERE username LIKE '$search_author'";

		if (!($result = DB()->sql_query($sql)))
		{
			bb_die('Could not obtain list of matching users (searching for: ' . $search_author . ')');
		}

		$matching_userids = '';
		if ( $row = DB()->sql_fetchrow($result) )
		{
			do
			{
				$matching_userids .= (($matching_userids != '') ? ', ' : '') . intval($row['user_id']);
			}
			while ($row = DB()->sql_fetchrow($result));

			DB()->sql_freeresult($result);
		}
		else
		{
			bb_die($lang['NO_ATTACH_SEARCH_MATCH']);
		}

		$where_sql[] = ' (t.user_id_1 IN (' . $matching_userids . ')) ';
	}

	// Search Keyword
	if ($search_keyword_fname != '')
	{
		$match_word = str_replace('*', '%', $search_keyword_fname);
		$where_sql[] = " (a.real_filename LIKE '" . attach_mod_sql_escape($match_word) . "') ";
	}

	if ($search_keyword_comment != '')
	{
		$match_word = str_replace('*', '%', $search_keyword_comment);
		$where_sql[] = " (a.comment LIKE '" . attach_mod_sql_escape($match_word) . "') ";
	}

	// Search Download Count
	if ($search_count_smaller != '' || $search_count_greater != '')
	{
		if ($search_count_smaller != '')
		{
			$where_sql[] = ' (a.download_count < ' . (int) $search_count_smaller . ') ';
		}
		else if ($search_count_greater != '')
		{
			$where_sql[] = ' (a.download_count > ' . (int) $search_count_greater . ') ';
		}
	}

	// Search Filesize
	if ($search_size_smaller != '' || $search_size_greater != '')
	{
		if ($search_size_smaller != '')
		{
			$where_sql[] = ' (a.filesize < ' . (int) $search_size_smaller . ') ';
		}
		else if ($search_size_greater != '')
		{
			$where_sql[] = ' (a.filesize > ' . (int) $search_size_greater . ') ';
		}
	}

	// Search Attachment Time
	if ($search_days_greater != '')
	{
		$where_sql[] = ' (a.filetime < ' . ( TIMENOW - ((int) $search_days_greater * 86400)) . ') ';
	}

	// Search Forum
	if ($search_forum)
	{
		$where_sql[] = ' (p.forum_id = ' . intval($search_forum) . ') ';
	}

	// Search Cat... nope... sorry :(

	$sql = 'SELECT a.*, t.post_id, p.post_time, p.topic_id
		FROM ' . BB_ATTACHMENTS . ' t, ' . BB_ATTACHMENTS_DESC . ' a, ' . BB_POSTS . ' p WHERE ';

	if (sizeof($where_sql) > 0)
	{
		$sql .= implode('AND', $where_sql) . ' AND ';
	}

	$sql .= 't.post_id = p.post_id AND a.attach_id = t.attach_id ';

	$total_rows_sql = $sql;

	$sql .= $order_by;

	if (!($result = DB()->sql_query($sql)))
	{
		bb_die('Could not query attachments #1');
	}

	$attachments = DB()->sql_fetchrowset($result);
	$num_attach = DB()->num_rows($result);
	DB()->sql_freeresult($result);

	if ($num_attach == 0)
	{
		bb_die($lang['NO_ATTACH_SEARCH_MATCH']);
	}

	if (!($result = DB()->sql_query($total_rows_sql)))
	{
		bb_die('Could not query attachments #2');
	}

	$total_rows = DB()->num_rows($result);
	DB()->sql_freeresult($result);

	return $attachments;
}

/**
* perform LIMIT statement on arrays
*/
function limit_array($array, $start, $pagelimit)
{
	// array from start - start+pagelimit
	$limit = (sizeof($array) < ($start + $pagelimit)) ? sizeof($array) : $start + $pagelimit;

	$limit_array = array();

	for ($i = $start; $i < $limit; $i++)
	{
		$limit_array[] = $array[$i];
	}

	return $limit_array;
}