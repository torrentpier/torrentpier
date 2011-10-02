<?php

define('IN_PHPBB',   true);
define('BB_SCRIPT', 'download');
define('IN_SERVICE', true);
define('NO_GZIP', true);
define('BB_ROOT',  './');
require(BB_ROOT ."common.php");
require(BB_ROOT ."attach_mod/attachment_mod.php");

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

	if (!intval($attach_config['allow_ftp_upload']))
	{
		if (@!file_exists(@amod_realpath($filename)))
		{
			message_die(GENERAL_ERROR, $lang['ERROR_NO_ATTACHMENT'] . "<br /><br />" . $filename.  "<br /><br />" .$lang['TOR_NOT_FOUND']);
		}
		else
		{
			$gotit = true;
		}
	}

	//
	// Determine the Browser the User is using, because of some nasty incompatibilities.
	// Most of the methods used in this function are from phpMyAdmin. :)
	//
	if (!empty($_SERVER['HTTP_USER_AGENT']))
	{
		$HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
	}
	else if (!isset($HTTP_USER_AGENT))
	{
		$HTTP_USER_AGENT = '';
	}

	if (preg_match('/Opera(\/| )([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version))
	{
		$browser_version = $log_version[2];
		$browser_agent = 'opera';
	}
	else if (preg_match('/MSIE ([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version))
	{
		$browser_version = $log_version[1];
		$browser_agent = 'ie';
	}
	else if (preg_match('/OmniWeb\/([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version))
	{
		$browser_version = $log_version[1];
		$browser_agent = 'omniweb';
	}
	else if (preg_match('/Netscape([0-9]{1})/', $HTTP_USER_AGENT, $log_version))
	{
		$browser_version = $log_version[1];
		$browser_agent = 'netscape';
	}
	else if (preg_match('/Mozilla\/([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version))
	{
		$browser_version = $log_version[1];
		$browser_agent = 'mozilla';
	}
	else if (preg_match('/Konqueror\/([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version))
	{
		$browser_version = $log_version[1];
		$browser_agent = 'konqueror';
	}
	else
	{
		$browser_version = 0;
		$browser_agent = 'other';
	}

	// Correct the mime type - we force application/octetstream for all files, except images
	// Please do not change this, it is a security precaution
	if (!strstr($attachment['mimetype'], 'image'))
	{
		$attachment['mimetype'] = ($browser_agent == 'ie' || $browser_agent == 'opera') ? 'application/octetstream' : 'application/octet-stream';
	}

	//bt
	global $userdata;

	if (!(isset($_GET['original']) && !IS_USER))
	{
		include(INC_DIR .'functions_torrent.php');
		send_torrent_with_passkey($filename);
	}
	//bt end

	// Now the tricky part... let's dance
//	@ob_end_clean();
//	@ini_set('zlib.output_compression', 'Off');
	header('Pragma: public');
//	header('Content-Transfer-Encoding: none');

//$real_filename = html_entity_decode(basename($attachment['real_filename']));
	$real_filename = clean_filename(basename($attachment['real_filename']));
	$mimetype = "{$attachment['mimetype']};";
	$charset = (@$lang['CONTENT_ENCODING']) ? "charset={$lang['CONTENT_ENCODING']};" : '';

	// Send out the Headers
	header("Content-Type: $mimetype $charset name=\"$real_filename\"");
	header("Content-Disposition: inline; filename=\"$real_filename\"");

	unset($real_filename);
	//
	// Now send the File Contents to the Browser
	//
	if ($gotit)
	{
		$size = @filesize($filename);
		if ($size)
		{
			header("Content-length: $size");
		}
		readfile($filename);
	}
	else if (!$gotit && intval($attach_config['allow_ftp_upload']))
	{
		$conn_id = attach_init_ftp();

		$ini_val = ( @phpversion() >= '4.0.0' ) ? 'ini_get' : 'get_cfg_var';

		$tmp_path = ( !@$ini_val('safe_mode') ) ? '/tmp' : $upload_dir;
		$tmp_filename = @tempnam($tmp_path, 't0000');

		@unlink($tmp_filename);

		$mode = FTP_BINARY;
		if ( (preg_match("/text/i", $attachment['mimetype'])) || (preg_match("/html/i", $attachment['mimetype'])) )
		{
			$mode = FTP_ASCII;
		}

		$result = @ftp_get($conn_id, $tmp_filename, $filename, $mode);

		if (!$result)
		{
			message_die(GENERAL_ERROR, $lang['ERROR_NO_ATTACHMENT'] . "<br /><br />" . $filename.  "<br /><br />" .$lang['TOR_NOT_FOUND']);
		}

		@ftp_quit($conn_id);

		$size = @filesize($tmp_filename);
		if ($size)
		{
			header("Content-length: $size");
		}
		readfile($tmp_filename);
		@unlink($tmp_filename);
	}
	else
	{
		message_die(GENERAL_ERROR, $lang['ERROR_NO_ATTACHMENT'] . "<br /><br />" . $filename.  "<br /><br />" .$lang['TOR_NOT_FOUND']);
	}

	exit;
}
//
// End Functions
//

//
// Start Session Management
//
$user->session_start();

if (!$download_id)
{
	message_die(GENERAL_ERROR, $lang['NO_ATTACHMENT_SELECTED']);
}

if ($attach_config['disable_mod'] && !IS_ADMIN)
{
	message_die(GENERAL_MESSAGE, $lang['ATTACHMENT_FEATURE_DISABLED']);
}

$sql = 'SELECT *
	FROM ' . BB_ATTACHMENTS_DESC . '
	WHERE attach_id = ' . (int) $download_id;

if (!($result = DB()->sql_query($sql)))
{
	message_die(GENERAL_ERROR, 'Could not query attachment informations', '', __LINE__, __FILE__, $sql);
}

if (!($attachment = DB()->sql_fetchrow($result)))
{
	message_die(GENERAL_MESSAGE, $lang['ERROR_NO_ATTACHMENT']);
}

$attachment['physical_filename'] = basename($attachment['physical_filename']);

DB()->sql_freeresult($result);

// get forum_id for attachment authorization or private message authorization
$authorised = false;

$sql = 'SELECT *
	FROM ' . BB_ATTACHMENTS . '
	WHERE attach_id = ' . (int) $attachment['attach_id'];

if (!($result = DB()->sql_query($sql)))
{
	message_die(GENERAL_ERROR, 'Could not query attachment informations', '', __LINE__, __FILE__, $sql);
}

$auth_pages = DB()->sql_fetchrowset($result);
$num_auth_pages = DB()->num_rows($result);

for ($i = 0; $i < $num_auth_pages && $authorised == false; $i++)
{
	$auth_pages[$i]['post_id'] = intval($auth_pages[$i]['post_id']);

	if ($auth_pages[$i]['post_id'] != 0)
	{
		$sql = 'SELECT forum_id
			FROM ' . BB_POSTS . '
			WHERE post_id = ' . (int) $auth_pages[$i]['post_id'];

		if ( !($result = DB()->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not query post information', '', __LINE__, __FILE__, $sql);
		}

		$row = DB()->sql_fetchrow($result);

		$forum_id = $row['forum_id'];

		$is_auth = array();
		$is_auth = auth(AUTH_ALL, $forum_id, $userdata);

		if ($is_auth['auth_download'])
		{
			$authorised = TRUE;
		}
	}
}


if (!$authorised)
{
	message_die(GENERAL_MESSAGE, $lang['SORRY_AUTH_VIEW_ATTACH']);
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

// disallowed ?
if (!in_array($attachment['extension'], $allowed_extensions) && !IS_ADMIN)
{
	message_die(GENERAL_MESSAGE, sprintf($lang['EXTENSION_DISABLED_AFTER_POSTING'], $attachment['extension']));
}

$download_mode = intval($download_mode[$attachment['extension']]);

if ($thumbnail)
{
	$attachment['physical_filename'] = THUMB_DIR . '/t_' . $attachment['physical_filename'];
}

// Update download count
if (!$thumbnail)
{
	$sql = 'UPDATE ' . BB_ATTACHMENTS_DESC . '
	SET download_count = download_count + 1
	WHERE attach_id = ' . (int) $attachment['attach_id'];

	if (!DB()->sql_query($sql))
	{
		message_die(GENERAL_ERROR, 'Couldn\'t update attachment download count', '', __LINE__, __FILE__, $sql);
	}
}

// Determine the 'presenting'-method
if ($download_mode == PHYSICAL_LINK)
{
	$server_protocol = ($bb_cfg['cookie_secure']) ? 'https://' : 'http://';
	$server_name = preg_replace('/^\/?(.*?)\/?$/', '\1', trim($bb_cfg['server_name']));
	$server_port = ($bb_cfg['server_port'] <> 80) ? ':' . trim($bb_cfg['server_port']) : '';
	$script_name = preg_replace('/^\/?(.*?)\/?$/', '/\1', trim($bb_cfg['script_path']));

	if ($script_name[strlen($script_name)] != '/')
	{
		$script_name .= '/';
	}

	if (intval($attach_config['allow_ftp_upload']))
	{
		if (trim($attach_config['download_path']) == '')
		{
			message_die(GENERAL_ERROR, 'Physical Download not possible with the current Attachment Setting');
		}

		$url = trim($attach_config['download_path']) . '/' . $attachment['physical_filename'];
		$redirect_path = $url;
	}
	else
	{
		$url = $upload_dir . '/' . $attachment['physical_filename'];
//		$url = preg_replace('/^\/?(.*?\/)?$/', '\1', trim($url));
		$redirect_path = $server_protocol . $server_name . $server_port . $script_name . $url;
	}

	// Redirect via an HTML form for PITA webservers
	if (@preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE')))
	{
		header('Refresh: 0; URL=' . $redirect_path);
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta http-equiv="refresh" content="0; url=' . $redirect_path . '"><title>Redirect</title></head><body><div align="center">If your browser does not support meta redirection please click <a href="' . $redirect_path . '">HERE</a> to be redirected</div></body></html>';
		exit;
	}

	// Behave as per HTTP/1.1 spec for others
	header('Location: ' . $redirect_path);
	exit;
}
else
{
    if(IS_GUEST && !CAPTCHA()->verify_code())
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

	if (intval($attach_config['allow_ftp_upload']))
	{
		// We do not need a download path, we are not downloading physically
		send_file_to_browser($attachment, '');
		exit;
	}
	else
	{
		send_file_to_browser($attachment, $upload_dir);
		exit;
	}
}