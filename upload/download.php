<?php

define('IN_FORUM', true);
define('BB_SCRIPT', 'download');
define('NO_GZIP', true);
define('BB_ROOT',  './');
require(BB_ROOT .'common.php');
require(ATTACH_DIR .'attachment_mod.php');

$datastore->enqueue(array(
	'attach_extensions',
));

$download_id = request_var('id', 0);
$thumbnail = request_var('thumb', 0);

// Send file to browser
function send_file_to_browser($attachment, $upload_dir)
{
	global $lang, $attach_config;

	$filename = ($upload_dir == '') ? $attachment['physical_filename'] : $upload_dir . '/' . $attachment['physical_filename'];

	$gotit = false;

	if (@!file_exists(@amod_realpath($filename)))
	{
		bb_die($lang['ERROR_NO_ATTACHMENT'] . "<br /><br />" . $filename. "<br /><br />" .$lang['TOR_NOT_FOUND']);
	}
	else
	{
		$gotit = true;
	}

	//
	// Determine the Browser the User is using, because of some nasty incompatibilities.
	// Most of the methods used in this function are from phpMyAdmin. :)
	//
	if (!empty($_SERVER['HTTP_USER_AGENT']))
	{
		$HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
	}
	elseif (!isset($HTTP_USER_AGENT))
	{
		$HTTP_USER_AGENT = '';
	}

	// Correct the mime type - we force application/octet-stream for all files, except images
	// Please do not change this, it is a security precaution
	if (!strstr($attachment['mimetype'], 'image'))
	{
		$attachment['mimetype'] = 'application/octet-stream';
	}

	//bt
	if (!(isset($_GET['original']) && !IS_USER))
	{
		include(INC_DIR .'functions_torrent.php');
		send_torrent_with_passkey($filename);
	}

	// Now the tricky part... let's dance
	header('Pragma: public');
	$real_filename = clean_filename(basename($attachment['real_filename']));
	$mimetype = "{$attachment['mimetype']};";
	$charset = (@$lang['CONTENT_ENCODING']) ? "charset={$lang['CONTENT_ENCODING']};" : '';

	// Send out the Headers
	header("Content-Type: $mimetype $charset name=\"$real_filename\"");
	header("Content-Disposition: inline; filename=\"$real_filename\"");
	unset($real_filename);

	// Now send the File Contents to the Browser
	if ($gotit)
	{
		$size = @filesize($filename);
		if ($size)
		{
			header("Content-length: $size");
		}
		readfile($filename);
	}
	else
	{
		bb_die($lang['ERROR_NO_ATTACHMENT'] . "<br /><br />" . $filename. "<br /><br />" .$lang['TOR_NOT_FOUND']);
	}

	exit;
}

//
// Start Session Management
//
$user->session_start();

set_die_append_msg();

if (!$download_id)
{
	bb_die($lang['NO_ATTACHMENT_SELECTED']);
}

if ($attach_config['disable_mod'] && !IS_ADMIN)
{
	bb_die($lang['ATTACHMENT_FEATURE_DISABLED']);
}

$sql = 'SELECT * FROM ' . BB_ATTACHMENTS_DESC . ' WHERE attach_id = ' . (int) $download_id;

if (!($result = DB()->sql_query($sql)))
{
	bb_die('Could not query attachment information #1');
}

if (!($attachment = DB()->sql_fetchrow($result)))
{
	bb_die($lang['ERROR_NO_ATTACHMENT']);
}

$attachment['physical_filename'] = basename($attachment['physical_filename']);

DB()->sql_freeresult($result);

// get forum_id for attachment authorization or private message authorization
$authorised = false;

$sql = 'SELECT * FROM ' . BB_ATTACHMENTS . ' WHERE attach_id = ' . (int) $attachment['attach_id'];

if (!($result = DB()->sql_query($sql)))
{
	bb_die('Could not query attachment information #2');
}

$auth_pages = DB()->sql_fetchrowset($result);
$num_auth_pages = DB()->num_rows($result);

for ($i = 0; $i < $num_auth_pages && $authorised == false; $i++)
{
	$auth_pages[$i]['post_id'] = intval($auth_pages[$i]['post_id']);

	if ($auth_pages[$i]['post_id'] != 0)
	{
		$sql = 'SELECT forum_id, topic_id FROM ' . BB_POSTS . ' WHERE post_id = ' . (int) $auth_pages[$i]['post_id'];

		if (!($result = DB()->sql_query($sql)))
		{
			bb_die('Could not query post information');
		}

		$row = DB()->sql_fetchrow($result);

		$topic_id = $row['topic_id'];
		$forum_id = $row['forum_id'];

		$is_auth = array();
		$is_auth = auth(AUTH_ALL, $forum_id, $userdata);
		set_die_append_msg($forum_id, $topic_id);

		if ($is_auth['auth_download'])
		{
			$authorised = TRUE;
		}
	}
}

if (!$authorised)
{
	bb_die($lang['SORRY_AUTH_VIEW_ATTACH']);
}

$datastore->rm('cat_forums');

//
// Get Information on currently allowed Extensions
//
$rows = get_extension_informations();
$num_rows = count($rows);

for ($i = 0; $i < $num_rows; $i++)
{
	$extension = strtolower(trim($rows[$i]['extension']));
	$allowed_extensions[] = $extension;
	$download_mode[$extension] = $rows[$i]['download_mode'];
}

// Disallowed
if (!in_array($attachment['extension'], $allowed_extensions) && !IS_ADMIN)
{
	bb_die(sprintf($lang['EXTENSION_DISABLED_AFTER_POSTING'], $attachment['extension']));
}

$download_mode = intval($download_mode[$attachment['extension']]);

if ($thumbnail)
{
	$attachment['physical_filename'] = THUMB_DIR . '/t_' . $attachment['physical_filename'];
}

// Update download count
if (!$thumbnail)
{
	$sql = 'UPDATE ' . BB_ATTACHMENTS_DESC . ' SET download_count = download_count + 1 WHERE attach_id = ' . (int) $attachment['attach_id'];

	if (!DB()->sql_query($sql))
	{
		bb_die('Could not update attachment download count');
	}
}

// Determine the 'presenting'-method
if ($download_mode == PHYSICAL_LINK)
{
	$url = make_url($upload_dir . '/' . $attachment['physical_filename']);
	header('Location: ' . $url);
	exit;
}
else
{
	if (IS_GUEST && !CAPTCHA()->verify_code())
	{
		global $template;

		$redirect_url = !empty($_POST['redirect_url']) ? $_POST['redirect_url'] : @$_SERVER['HTTP_REFERER'];
		$message = '<form action="'. DOWNLOAD_URL . $attachment['attach_id'] .'" method="post">';
		$message .= $lang['CONFIRM_CODE'];
		$message .= '<div class="mrg_10">'. CAPTCHA()->get_html() .'</div>';
		$message .= '<input type="hidden" name="redirect_url" value="'. $redirect_url .'" />';
		$message .= '<input type="submit" class="bold" value="'. $lang['SUBMIT'] .'" /> &nbsp;';
		$message .= '<input type="button" class="bold" value="Вернуться обратно" onclick="document.location.href = \''. $redirect_url .'\';" />';
		$message .= '</form>';

		$template->assign_vars(array(
			'ERROR_MESSAGE' => $message,
		));

		require(PAGE_HEADER);
		require(PAGE_FOOTER);
	}

	send_file_to_browser($attachment, $upload_dir);
	exit;
}