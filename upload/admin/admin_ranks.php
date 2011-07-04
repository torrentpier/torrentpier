<?php

// ACP Header - START
if (!empty($setmodules))
{
	$module['Users']['Ranks'] = basename(__FILE__);
	return;
}

function update_ranks () { $GLOBALS['datastore']->update('ranks'); }
register_shutdown_function('update_ranks');

require('./pagestart.php');
// ACP Header - END

$_POST['special_rank'] = 1;
$_POST['min_posts'] = -1;

if( isset($_GET['mode']) || isset($_POST['mode']) )
{
	$mode = isset($_GET['mode']) ? $_GET['mode'] : $_POST['mode'];
}
else
{
	//
	// These could be entered via a form button
	//
	if( isset($_POST['add']) )
	{
		$mode = "add";
	}
	else if( isset($_POST['save']) )
	{
		$mode = "save";
	}
	else
	{
		$mode = "";
	}
}


if( $mode != "" )
{
	if( $mode == "edit" || $mode == "add" )
	{
		//
		// They want to add a new rank, show the form.
		//
		$rank_id = ( isset($_GET['id']) ) ? intval($_GET['id']) : 0;

		$s_hidden_fields = "";

		if( $mode == "edit" )
		{
			if( empty($rank_id) )
			{
				message_die(GENERAL_MESSAGE, $lang['MUST_SELECT_RANK']);
			}

			$sql = "SELECT * FROM " . BB_RANKS . "
				WHERE rank_id = $rank_id";
			if(!$result = DB()->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Couldn't obtain rank data", "", __LINE__, __FILE__, $sql);
			}

			$rank_info = DB()->sql_fetchrow($result);
			$s_hidden_fields .= '<input type="hidden" name="id" value="' . $rank_id . '" />';

		}
		else
		{
			$rank_info['rank_special'] = 0;
		}

		$s_hidden_fields .= '<input type="hidden" name="mode" value="save" />';

		$rank_is_special = ( $rank_info['rank_special'] ) ? "checked=\"checked\"" : "";
		$rank_is_not_special = ( !$rank_info['rank_special'] ) ? "checked=\"checked\"" : "";

		$template->assign_vars(array(
			'TPL_RANKS_EDIT' => true,

			"RANK" => @$rank_info['rank_title'],
			"SPECIAL_RANK" => $rank_is_special,
			"NOT_SPECIAL_RANK" => $rank_is_not_special,
			"MINIMUM" => ( $rank_is_special ) ? "" : @$rank_info['rank_min'],
			"IMAGE" => ( @$rank_info['rank_image'] ) ? $rank_info['rank_image'] : "images/ranks/rank_image.gif",
			"IMAGE_DISPLAY" => ( @$rank_info['rank_image'] ) ? '<img src="../' . $rank_info['rank_image'] . '" />' : "",

			"L_RANKS_TEXT" => $lang['RANKS_EXPLAIN'],

			"S_RANK_ACTION" => append_sid("admin_ranks.php"),
			"S_HIDDEN_FIELDS" => $s_hidden_fields)
		);

	}
	else if( $mode == "save" )
	{
		//
		// Ok, they sent us our info, let's update it.
		//

		$rank_id = ( isset($_POST['id']) ) ? intval($_POST['id']) : 0;
		$rank_title = ( isset($_POST['title']) ) ? trim($_POST['title']) : "";
		$special_rank = ( $_POST['special_rank'] == 1 ) ? TRUE : 0;
		$min_posts = ( isset($_POST['min_posts']) ) ? intval($_POST['min_posts']) : -1;
		$rank_image = ( (isset($_POST['rank_image'])) ) ? trim($_POST['rank_image']) : "";

		if( $rank_title == "" )
		{
			message_die(GENERAL_MESSAGE, $lang['MUST_SELECT_RANK']);
		}

		if( $special_rank == 1 )
		{
			$max_posts = -1;
			$min_posts = -1;
		}

		//
		// The rank image has to be a jpg, gif or png
		//
		if($rank_image != "")
		{
			if ( !preg_match("/(\.gif|\.png|\.jpg)$/is", $rank_image))
			{
				$rank_image = "";
			}
		}

		if ($rank_id)
		{
			if (!$special_rank)
			{
				$sql = "UPDATE " . BB_USERS . "
					SET user_rank = 0
					WHERE user_rank = $rank_id";

				if( !$result = DB()->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, $lang['NO_UPDATE_RANKS'], "", __LINE__, __FILE__, $sql);
				}
			}
			$sql = "UPDATE " . BB_RANKS . "
				SET rank_title = '" . DB()->escape($rank_title) . "', rank_special = $special_rank, rank_min = $min_posts, rank_image = '" . DB()->escape($rank_image) . "'
				WHERE rank_id = $rank_id";

			$message = $lang['RANK_UPDATED'];
		}
		else
		{
			$sql = "INSERT INTO " . BB_RANKS . " (rank_title, rank_special, rank_min, rank_image)
				VALUES ('" . DB()->escape($rank_title) . "', $special_rank, $min_posts, '" . DB()->escape($rank_image) . "')";

			$message = $lang['RANK_ADDED'];
		}

		if( !$result = DB()->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't update/insert into ranks table", "", __LINE__, __FILE__, $sql);
		}

		$message .= "<br /><br />" . sprintf($lang['CLICK_RETURN_RANKADMIN'], "<a href=\"" . append_sid("admin_ranks.php") . "\">", "</a>") . "<br /><br />" . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], "<a href=\"" . append_sid("index.php?pane=right") . "\">", "</a>");

		message_die(GENERAL_MESSAGE, $message);

	}
	else if( $mode == "delete" )
	{
		//
		// Ok, they want to delete their rank
		//

		if( isset($_POST['id']) || isset($_GET['id']) )
		{
			$rank_id = ( isset($_POST['id']) ) ? intval($_POST['id']) : intval($_GET['id']);
		}
		else
		{
			$rank_id = 0;
		}

		if( $rank_id )
		{
			$sql = "DELETE FROM " . BB_RANKS . "
				WHERE rank_id = $rank_id";

			if( !$result = DB()->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't delete rank data", "", __LINE__, __FILE__, $sql);
			}

			$sql = "UPDATE " . BB_USERS . "
				SET user_rank = 0
				WHERE user_rank = $rank_id";

			if( !$result = DB()->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, $lang['NO_UPDATE_RANKS'], "", __LINE__, __FILE__, $sql);
			}

			$message = $lang['RANK_REMOVED'] . "<br /><br />" . sprintf($lang['CLICK_RETURN_RANKADMIN'], "<a href=\"" . append_sid("admin_ranks.php") . "\">", "</a>") . "<br /><br />" . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], "<a href=\"" . append_sid("index.php?pane=right") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $message);

		}
		else
		{
			message_die(GENERAL_MESSAGE, $lang['MUST_SELECT_RANK']);
		}
	}
	else
	{
		message_die(GENERAL_MESSAGE, 'Invalid mode');
	}
}
else
{
	//
	// Show the default page
	//
	$sql = "SELECT * FROM " . BB_RANKS . "
		ORDER BY rank_min, rank_title";
	if( !$result = DB()->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't obtain ranks data", "", __LINE__, __FILE__, $sql);
	}
	$rank_count = DB()->num_rows($result);

	$rank_rows = DB()->sql_fetchrowset($result);

	$template->assign_vars(array(
		'TPL_RANKS_LIST' => true,

		"L_RANKS_TEXT" => $lang['RANKS_EXPLAIN'],
		"L_RANK" => $lang['RANK_TITLE'],
		"L_ADD_RANK" => $lang['ADD_NEW_RANK'],

		"S_RANKS_ACTION" => append_sid("admin_ranks.php"))
	);

	for($i = 0; $i < $rank_count; $i++)
	{
		$rank = $rank_rows[$i]['rank_title'];
		$special_rank = $rank_rows[$i]['rank_special'];
		$rank_id = $rank_rows[$i]['rank_id'];
		$rank_min = $rank_rows[$i]['rank_min'];

		if( $special_rank == 1 )
		{
			$rank_min = $rank_max = "-";
		}

		$row_class = !($i % 2) ? 'row1' : 'row2';

		$rank_is_special = ( $special_rank ) ? $lang['YES'] : $lang['NO'];

		$template->assign_block_vars("ranks", array(
			"ROW_CLASS" => $row_class,
			"RANK" => $rank,
			"IMAGE_DISPLAY" => ( @$rank_rows[$i]['rank_image'] ) ? '<img src="../' . $rank_rows[$i]['rank_image'] . '" />' : "",
			"SPECIAL_RANK" => $rank_is_special,
			"RANK_MIN" => $rank_min,

			"U_RANK_EDIT" => append_sid("admin_ranks.php?mode=edit&amp;id=$rank_id"),
			"U_RANK_DELETE" => append_sid("admin_ranks.php?mode=delete&amp;id=$rank_id"))
		);
	}
}

print_page('admin_ranks.tpl', 'admin');
