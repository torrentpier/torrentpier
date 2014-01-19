<?php

// ACP Header - START
if (!empty($setmodules))
{
	$module['Users']['Disallow'] = basename(__FILE__);
	return;
}
require('./pagestart.php');
// ACP Header - END

$message = '';

if( isset($_POST['add_name']) )
{
	include(INC_DIR .'functions_validate.php');

	$disallowed_user = ( isset($_POST['disallowed_user']) ) ? trim($_POST['disallowed_user']) : trim($_GET['disallowed_user']);

	if ($disallowed_user == '')
	{
		message_die(GENERAL_MESSAGE, $lang['FIELDS_EMPTY']);
	}
	if( !validate_username($disallowed_user) )
	{
		$message = $lang['DISALLOWED_ALREADY'];
	}
	else
	{
		$sql = "INSERT INTO " . BB_DISALLOW . " (disallow_username)
			VALUES('" . DB()->escape($disallowed_user) . "')";
		$result = DB()->sql_query( $sql );
		if ( !$result )
		{
			message_die(GENERAL_ERROR, "Could not add disallowed user.", '',__LINE__, __FILE__, $sql);
		}
		$message = $lang['DISALLOW_SUCCESSFUL'];
	}

	$message .= '<br /><br />'. sprintf($lang['CLICK_RETURN_DISALLOWADMIN'], '<a href="admin_disallow.php">', '</a>') . '<br /><br />'. sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>');

	message_die(GENERAL_MESSAGE, $message);
}
elseif (isset($_POST['delete_name']))
{
	$disallowed_id = (isset($_POST['disallowed_id']) ) ? intval( $_POST['disallowed_id'] ) : intval( $_GET['disallowed_id']);

	$sql = "DELETE FROM " . BB_DISALLOW . " WHERE disallow_id = $disallowed_id";
	$result = DB()->sql_query($sql);
	if( !$result )
	{
		message_die(GENERAL_ERROR, "Couldn't removed disallowed user.", '',__LINE__, __FILE__, $sql);
	}

	$message .= $lang['DISALLOWED_DELETED'] .'<br /><br />'. sprintf($lang['CLICK_RETURN_DISALLOWADMIN'], '<a href="admin_disallow.php">', '</a>') .'<br /><br />'. sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>');

	message_die(GENERAL_MESSAGE, $message);

}

//
// Grab the current list of disallowed usernames...
//
$sql = "SELECT * FROM " . BB_DISALLOW;
$result = DB()->sql_query($sql);
if( !$result )
{
	message_die(GENERAL_ERROR, "Couldn't get disallowed users.", '', __LINE__, __FILE__, $sql );
}

$disallowed = DB()->sql_fetchrowset($result);

//
// Ok now generate the info for the template, which will be put out no matter
// what mode we are in.
//
$disallow_select = '<select name="disallowed_id">';

if( count($disallowed) <= 0 )
{
	$disallow_select .= '<option value="">' . $lang['NO_DISALLOWED'] . '</option>';
}
else
{
	for( $i = 0; $i < count($disallowed); $i++ )
	{
		$disallow_select .= '<option value="' . $disallowed[$i]['disallow_id'] . '">' . $disallowed[$i]['disallow_username'] . '</option>';
	}
}

$disallow_select .= '</select>';

$template->assign_vars(array(
	'S_DISALLOW_SELECT' => $disallow_select,
	'S_FORM_ACTION'     => "admin_disallow.php",
));

print_page('admin_disallow.tpl', 'admin');