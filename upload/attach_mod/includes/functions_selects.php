<?php

/**
* Functions to build select boxes ;)
*/

/**
* select group
*/
function group_select($select_name, $default_group = 0)
{
	global $lang;

	$sql = 'SELECT group_id, group_name
		FROM ' . BB_EXTENSION_GROUPS . '
		ORDER BY group_name';

	if (!($result = DB()->sql_query($sql)))
	{
		message_die(GENERAL_ERROR, "Couldn't query Extension Groups Table", "", __LINE__, __FILE__, $sql);
	}

	$group_select = '<select name="' . $select_name . '">';

	$group_name = DB()->sql_fetchrowset($result);
	$num_rows = DB()->num_rows($result);
	DB()->sql_freeresult($result);

	if ($num_rows > 0)
	{
		$group_name[$num_rows]['group_id'] = 0;
		$group_name[$num_rows]['group_name'] = $lang['NOT_ASSIGNED'];

		for ($i = 0; $i < sizeof($group_name); $i++)
		{
			if (!$default_group)
			{
				$selected = ($i == 0) ? ' selected="selected"' : '';
			}
			else
			{
				$selected = ($group_name[$i]['group_id'] == $default_group) ? ' selected="selected"' : '';
			}

			$group_select .= '<option value="' . $group_name[$i]['group_id'] . '"' . $selected . '>' . $group_name[$i]['group_name'] . '</option>';
		}
	}

	$group_select .= '</select>';

	return $group_select;
}

/**
* select download mode
*/
function download_select($select_name, $group_id = 0)
{
	global $types_download, $modes_download;

	if ($group_id)
	{
		$sql = 'SELECT download_mode
			FROM ' . BB_EXTENSION_GROUPS . '
			WHERE group_id = ' . (int) $group_id;

		if (!($result = DB()->sql_query($sql)))
		{
			message_die(GENERAL_ERROR, "Couldn't query Extension Groups Table", "", __LINE__, __FILE__, $sql);
		}
		$row = DB()->sql_fetchrow($result);
		DB()->sql_freeresult($result);

		if (!isset($row['download_mode']))
		{
			return '';
		}

		$download_mode = $row['download_mode'];
	}

	$group_select = '<select name="' . $select_name . '">';

	for ($i = 0; $i < sizeof($types_download); $i++)
	{
		if (!$group_id)
		{
			$selected = ($types_download[$i] == INLINE_LINK) ? ' selected="selected"' : '';
		}
		else
		{
			$selected = ($row['download_mode'] == $types_download[$i]) ? ' selected="selected"' : '';
		}

		$group_select .= '<option value="' . $types_download[$i] . '"' . $selected . '>' . $modes_download[$i] . '</option>';
	}

	$group_select .= '</select>';

	return $group_select;
}

/**
* select category types
*/
function category_select($select_name, $group_id = 0)
{
	global $types_category, $modes_category;

	$sql = 'SELECT group_id, cat_id
		FROM ' . BB_EXTENSION_GROUPS;

	if ( !($result = DB()->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, "Couldn't select Category", "", __LINE__, __FILE__, $sql);
	}

	$rows = DB()->sql_fetchrowset($result);
	$num_rows = DB()->num_rows($result);
	DB()->sql_freeresult($result);

	$type_category = 0;

	if ($num_rows > 0)
	{
		for ($i = 0; $i < $num_rows; $i++)
		{
			if ($group_id == $rows[$i]['group_id'])
			{
				$category_type = $rows[$i]['cat_id'];
			}
		}
	}

	$types = array(NONE_CAT);
	$modes = array('none');

	for ($i = 0; $i < sizeof($types_category); $i++)
	{
		$types[] = $types_category[$i];
		$modes[] = $modes_category[$i];
	}

	$group_select = '<select name="' . $select_name . '" style="width:100px">';

	for ($i = 0; $i < sizeof($types); $i++)
	{
		if (!$group_id)
		{
			$selected = ($types[$i] == NONE_CAT) ? ' selected="selected"' : '';
		}
		else
		{
			$selected = ($types[$i] == $category_type) ? ' selected="selected"' : '';
		}

		$group_select .= '<option value="' . $types[$i] . '"' . $selected . '>' . $modes[$i] . '</option>';
	}

	$group_select .= '</select>';

	return $group_select;
}

/**
* Select size mode
*/
function size_select($select_name, $size_compare)
{
	global $lang;

	$size_types_text = array($lang['BYTES'], $lang['KB'], $lang['MB']);
	$size_types = array('b', 'kb', 'mb');

	$select_field = '<select name="' . $select_name . '">';

	for ($i = 0; $i < sizeof($size_types_text); $i++)
	{
		$selected = ($size_compare == $size_types[$i]) ? ' selected="selected"' : '';
		$select_field .= '<option value="' . $size_types[$i] . '"' . $selected . '>' . $size_types_text[$i] . '</option>';
	}

	$select_field .= '</select>';

	return $select_field;
}

/**
* select quota limit
*/
function quota_limit_select($select_name, $default_quota = 0)
{
	global $lang;

	$sql = 'SELECT quota_limit_id, quota_desc
		FROM ' . BB_QUOTA_LIMITS . '
		ORDER BY quota_limit ASC';

	if ( !($result = DB()->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, "Couldn't query Quota Limits Table", "", __LINE__, __FILE__, $sql);
	}

	$quota_select = '<select name="' . $select_name . '">';
	$quota_name[0]['quota_limit_id'] = 0;
	$quota_name[0]['quota_desc'] = $lang['NOT_ASSIGNED'];

	while ($row = DB()->sql_fetchrow($result))
	{
		$quota_name[] = $row;
	}
	DB()->sql_freeresult($result);

	for ($i = 0; $i < sizeof($quota_name); $i++)
	{
		$selected = ($quota_name[$i]['quota_limit_id'] == $default_quota) ? ' selected="selected"' : '';
		$quota_select .= '<option value="' . $quota_name[$i]['quota_limit_id'] . '"' . $selected . '>' . $quota_name[$i]['quota_desc'] . '</option>';
	}
	$quota_select .= '</select>';

	return $quota_select;
}

/**
* select default quota limit
*/
function default_quota_limit_select($select_name, $default_quota = 0)
{
	global $lang;

	$sql = 'SELECT quota_limit_id, quota_desc
		FROM ' . BB_QUOTA_LIMITS . '
		ORDER BY quota_limit ASC';

	if ( !($result = DB()->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, "Couldn't query Quota Limits Table", "", __LINE__, __FILE__, $sql);
	}

	$quota_select = '<select name="' . $select_name . '">';
	$quota_name[0]['quota_limit_id'] = 0;
	$quota_name[0]['quota_desc'] = $lang['NO_QUOTA_LIMIT'];

	while ($row = DB()->sql_fetchrow($result))
	{
		$quota_name[] = $row;
	}
	DB()->sql_freeresult($result);

	for ($i = 0; $i < sizeof($quota_name); $i++)
	{
		$selected = ( $quota_name[$i]['quota_limit_id'] == $default_quota ) ? ' selected="selected"' : '';
		$quota_select .= '<option value="' . $quota_name[$i]['quota_limit_id'] . '"' . $selected . '>' . $quota_name[$i]['quota_desc'] . '</option>';
	}
	$quota_select .= '</select>';

	return $quota_select;
}