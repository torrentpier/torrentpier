<?php

if (!empty($setmodules))
{
	$module['FORUMS']['PERMISSIONS'] = basename(__FILE__);
	return;
}
require('./pagestart.php');

$forum_auth_fields = array(
	'auth_view',
	'auth_read',
	'auth_reply',
	'auth_edit',
	'auth_delete',
	'auth_vote',
	'auth_pollcreate',
	'auth_attachments',
	'auth_download',
	'auth_post',
	'auth_sticky',
	'auth_announce',
);

// View  Read  Reply  Edit  Delete  Vote  Poll  PostAttach  DownAttach  PostTopic  Sticky  Announce
$simple_auth_ary = array(
/* Public */     0 => array(AUTH_ALL,  AUTH_ALL,  AUTH_ALL,  AUTH_REG,  AUTH_REG,  AUTH_REG,  AUTH_REG,  AUTH_REG,  AUTH_ALL,  AUTH_ALL,  AUTH_MOD,  AUTH_MOD), // Public
/* Reg */        1 => array(AUTH_ALL,  AUTH_ALL,  AUTH_REG,  AUTH_REG,  AUTH_REG,  AUTH_REG,  AUTH_REG,  AUTH_REG,  AUTH_REG,  AUTH_REG,  AUTH_MOD,  AUTH_MOD), // Registered
/* Reg [Hid] */  2 => array(AUTH_REG,  AUTH_REG,  AUTH_REG,  AUTH_REG,  AUTH_REG,  AUTH_REG,  AUTH_REG,  AUTH_REG,  AUTH_REG,  AUTH_REG,  AUTH_MOD,  AUTH_MOD), // Registered [Hidden]
/* Priv */       3 => array(AUTH_REG,  AUTH_ACL,  AUTH_ACL,  AUTH_ACL,  AUTH_ACL,  AUTH_ACL,  AUTH_ACL,  AUTH_ACL,  AUTH_ACL,  AUTH_ACL,  AUTH_MOD,  AUTH_MOD), // Private
/* Priv [Hid] */ 4 => array(AUTH_ACL,  AUTH_ACL,  AUTH_ACL,  AUTH_ACL,  AUTH_ACL,  AUTH_ACL,  AUTH_ACL,  AUTH_ACL,  AUTH_ACL,  AUTH_ACL,  AUTH_MOD,  AUTH_MOD), // Private [Hidden]
/* MOD */        5 => array(AUTH_REG,  AUTH_MOD,  AUTH_MOD,  AUTH_MOD,  AUTH_MOD,  AUTH_MOD,  AUTH_MOD,  AUTH_MOD,  AUTH_MOD,  AUTH_MOD,  AUTH_MOD,  AUTH_MOD), // Moderators
/* MOD [Hid] */  6 => array(AUTH_MOD,  AUTH_MOD,  AUTH_MOD,  AUTH_MOD,  AUTH_MOD,  AUTH_MOD,  AUTH_MOD,  AUTH_MOD,  AUTH_MOD,  AUTH_MOD,  AUTH_MOD,  AUTH_MOD), // Moderators [Hidden]
);

$simple_auth_types = array(
	$lang['PUBLIC'],
	$lang['REGISTERED'],
	$lang['REGISTERED'] .' ['. $lang['HIDDEN'] .']',
	$lang['PRIVATE'],
	$lang['PRIVATE']    .' ['. $lang['HIDDEN'] .']',
	$lang['MODERATORS'],
	$lang['MODERATORS'] .' ['. $lang['HIDDEN'] .']',
);

$field_names = array();
foreach ($forum_auth_fields as $auth_type)
{
	$field_names[$auth_type] = $lang[strtoupper($auth_type)];
}

$forum_auth_levels = array('ALL',   'REG',    'PRIVATE', 'MOD',    'ADMIN');
$forum_auth_const  = array(AUTH_ALL, AUTH_REG, AUTH_ACL,  AUTH_MOD, AUTH_ADMIN);

if (@$_REQUEST[POST_FORUM_URL])
{
	$forum_id = (int) $_REQUEST[POST_FORUM_URL];
	$forum_sql = "WHERE forum_id = $forum_id";
}
else
{
	unset($forum_id);
	$forum_sql = '';
}

if( isset($_GET['adv']) )
{
	$adv = intval($_GET['adv']);
}
else
{
	unset($adv);
}

//
// Start program proper
//
if( isset($_POST['submit']) )
{
	$sql = '';

	if(!empty($forum_id))
	{
		if(isset($_POST['simpleauth']))
		{
			$simple_ary = $simple_auth_ary[intval($_POST['simpleauth'])];

			for($i = 0; $i < count($simple_ary); $i++)
			{
				$sql .= ( ( $sql != '' ) ? ', ' : '' ) . $forum_auth_fields[$i] . ' = ' . $simple_ary[$i];
			}

			if (is_array($simple_ary))
			{
			$sql = "UPDATE " . BB_FORUMS . " SET $sql WHERE forum_id = $forum_id";
			}
		}
		else
		{
			for ($i = 0; $i < count($forum_auth_fields); $i++)
			{
				$value = intval($_POST[$forum_auth_fields[$i]]);

				if ($forum_auth_fields[$i] == 'auth_vote')
				{
					if ($_POST['auth_vote'] == AUTH_ALL)
					{
						$value = AUTH_REG;
					}
				}

				$sql .= ( ( $sql != '' ) ? ', ' : '' ) .$forum_auth_fields[$i] . ' = ' . $value;
			}

			$sql = "UPDATE " . BB_FORUMS . " SET $sql WHERE forum_id = $forum_id";
		}

		if ($sql != '')
		{
			if (!DB()->sql_query($sql))
			{
				bb_die('Could not update auth table');
			}
		}

		$forum_sql = '';
		$adv = 0;
	}

	$datastore->update('cat_forums');
	bb_die($lang['FORUM_AUTH_UPDATED'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_FORUMAUTH'],  '<a href="'."admin_forumauth.php".'">', "</a>"));

} // End of submit

//
// Get required information, either all forums if
// no id was specified or just the requsted if it
// was
//
$forum_rows = DB()->fetch_rowset("SELECT * FROM ". BB_FORUMS ." $forum_sql");

if (empty($forum_id))
{
	// Output the selection table if no forum id was specified
	$template->assign_vars(array(
		'TPL_AUTH_SELECT_FORUM' => true,
		'S_AUTH_ACTION' => 'admin_forumauth.php',
		'S_AUTH_SELECT' => get_forum_select('admin', 'f', null, 80),
	));

}
else
{
	// Output the authorisation details if an id was specified
	$forum_name = $forum_rows[0]['forum_name'];

	@reset($simple_auth_ary);
	while (list($key, $auth_levels) = each($simple_auth_ary))
	{
		$matched = 1;
		for ($k = 0; $k < count($auth_levels); $k++)
		{
			$matched_type = $key;

			if ($forum_rows[0][$forum_auth_fields[$k]] != $auth_levels[$k])
			{
				$matched = 0;
			}
		}

		if ( $matched )
		{
			break;
		}
	}

	//
	// If we didn't get a match above then we
	// automatically switch into 'advanced' mode
	//
	if ( !isset($adv) && !$matched )
	{
		$adv = 1;
	}

	$s_column_span = 0;

	if (empty($adv))
	{
		$simple_auth = '<select name="simpleauth">';

		for($j = 0; $j < count($simple_auth_types); $j++)
		{
			$selected = ( $matched_type == $j ) ? ' selected="selected"' : '';
			$simple_auth .= '<option value="' . $j . '"' . $selected . '>' . $simple_auth_types[$j] . '</option>';
		}

		$simple_auth .= '</select>';

		$template->assign_block_vars('forum_auth', array(
			'CELL_TITLE' => $lang['SIMPLE_MODE'],
			'S_AUTH_LEVELS_SELECT' => $simple_auth,
		));

		$s_column_span++;
	}
	else
	{
		//
		// Output values of individual
		// fields
		//
		for ($j = 0; $j < count($forum_auth_fields); $j++)
		{
			$custom_auth[$j] = '&nbsp;<select name="' . $forum_auth_fields[$j] . '">';

			for ($k = 0; $k < count($forum_auth_levels); $k++)
			{
				$selected = ( $forum_rows[0][$forum_auth_fields[$j]] == $forum_auth_const[$k] ) ? ' selected="selected"' : '';
				$custom_auth[$j] .= '<option value="' . $forum_auth_const[$k] . '"' . $selected . '>' . $lang['FORUM_' . strtoupper($forum_auth_levels[$k])] . '</OPTION>';
			}
			$custom_auth[$j] .= '</select>&nbsp;';

			$cell_title = $field_names[$forum_auth_fields[$j]];

			$template->assign_block_vars('forum_auth', array(
				'CELL_TITLE' => $cell_title,
				'S_AUTH_LEVELS_SELECT' => $custom_auth[$j],
			));

			$s_column_span++;
		}
	}

	$adv_mode = ( empty($adv) ) ? '1' : '0';
	$switch_mode = "admin_forumauth.php?f=$forum_id&amp;adv=$adv_mode";
	$switch_mode_text = ( empty($adv) ) ? $lang['ADVANCED_MODE'] : $lang['SIMPLE_MODE'];
	$u_switch_mode = '<a href="' . $switch_mode . '">' . $switch_mode_text . '</a>';

	$s_hidden_fields = '<input type="hidden" name="' . POST_FORUM_URL . '" value="' . $forum_id . '">';

	$template->assign_vars(array(
		'TPL_EDIT_FORUM_AUTH' => true,
		'FORUM_NAME'          => htmlCHR($forum_name),
		'U_SWITCH_MODE'       => $u_switch_mode,
		'S_FORUMAUTH_ACTION'  => 'admin_forumauth.php',
		'S_COLUMN_SPAN'       => $s_column_span,
		'S_HIDDEN_FIELDS'     => $s_hidden_fields,
	));
}

print_page('admin_forumauth.tpl', 'admin');