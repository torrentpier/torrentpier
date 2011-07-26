<?php

if ( !defined('IN_PHPBB') )
{
	die('Hacking attempt');
	exit;
}

function check_image_type(&$type, &$errors)
{
	global $lang;

	switch( $type )
	{
		case 'image/jpeg':
		case 'image/pjpeg':
		case 'image/jpg':
		case 'jpeg':
		case 'pjpeg':
		case 'jpg':
			return '.jpg';
			break;
		case 'gif':
		case 'image/gif':
			return '.gif';
			break;
		case 'png':
		case 'image/x-png':
			return '.png';
			break;
		default:
			$errors[] = $lang['AVATAR_FILETYPE'];
			break;
	}

	return false;
}

function user_avatar_delete($avatar_type, $avatar_file)
{
	global $bb_cfg, $userdata;

	$avatar_file = basename($avatar_file);
	if ( $avatar_type == USER_AVATAR_UPLOAD && $avatar_file != '' )
	{
		if ( @file_exists(@phpbb_realpath('./' . $bb_cfg['avatar_path'] . '/' . $avatar_file)) )
		{
			@unlink('./' . $bb_cfg['avatar_path'] . '/' . $avatar_file);
		}
	}

	return array('user_avatar' => '', 'user_avatar_type' => USER_AVATAR_NONE);
}

function user_avatar_gallery($mode, &$errors, $avatar_filename, $avatar_category)
{
	global $bb_cfg;

	$avatar_filename = phpbb_ltrim(basename($avatar_filename), "'");
	$avatar_category = phpbb_ltrim(basename($avatar_category), "'");

	if(!preg_match('/(\.gif$|\.png$|\.jpg|\.jpeg)$/is', $avatar_filename))
	{
		return '';
	}
	if ($avatar_filename == "" || $avatar_category == "")
	{
		return '';
	}

	if ( file_exists(@phpbb_realpath($bb_cfg['avatar_gallery_path'] . '/' . $avatar_category . '/' . $avatar_filename)) && ($mode == 'editprofile') )
	{
		return array('user_avatar' => DB()->escape($avatar_category . '/' . $avatar_filename), 'user_avatar_type' => USER_AVATAR_GALLERY);
	}
	else
	{
		return '';
	}
}

function user_avatar_url($mode, &$errors, $avatar_filename)
{
	global $lang;

	if ( !preg_match('#^(http)|(ftp):\/\/#i', $avatar_filename) )
	{
		$avatar_filename = 'http://' . $avatar_filename;
	}

	$avatar_filename = substr($avatar_filename, 0, 100);

	if ( !preg_match("#^((ht|f)tp://)([^ \?&=\#\"\n\r\t<]*?(\.(jpg|jpeg|gif|png))$)#is", $avatar_filename) )
	{
		$errors[] = $lang['WRONG_REMOTE_AVATAR_FORMAT'];
		return;
	}

	return array('user_avatar' => DB()->escape($avatar_filename), 'user_avatar_type' => USER_AVATAR_REMOTE);

}

function user_avatar_upload($mode, $avatar_mode, &$current_avatar, &$current_type, &$errors, $avatar_filename, $avatar_realname, $avatar_filesize, $avatar_filetype)
{
	global $bb_cfg, $lang;

	$ini_val = ( @phpversion() >= '4.0.0' ) ? 'ini_get' : 'get_cfg_var';

	$width = $height = 0;
	$type = '';

	if ( $avatar_mode == 'remote' && preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/([^ \?&=\#\"\n\r\t<]*?(\.(jpg|jpeg|gif|png)))$/', $avatar_filename, $url_ary) )
	{
		if ( empty($url_ary[4]) )
		{
			$errors[] = $lang['INCOMPLETE_URL'];
			return;
		}

		$base_get = '/' . $url_ary[4];
		$port = ( !empty($url_ary[3]) ) ? $url_ary[3] : 80;

		if ( !($fsock = @fsockopen($url_ary[2], $port, $errno, $errstr)) )
		{
			$errors[] = $lang['NO_CONNECTION_URL'];
			return;
		}

		@fputs($fsock, "GET $base_get HTTP/1.1\r\n");
		@fputs($fsock, "HOST: " . $url_ary[2] . "\r\n");
		@fputs($fsock, "Connection: close\r\n\r\n");

		$avatar_data = '';
		while( !@feof($fsock) )
		{
			$avatar_data .= @fread($fsock, $bb_cfg['avatar_filesize']);
		}
		@fclose($fsock);

		if (!preg_match('#Content-Length\: ([0-9]+)[^ /][\s]+#i', $avatar_data, $file_data1) || !preg_match('#Content-Type\: image/[x\-]*([a-z]+)[\s]+#i', $avatar_data, $file_data2))
		{
			$errors[] = $lang['FILE_NO_DATA'];
			return;
		}

		$avatar_filesize = $file_data1[1];
		$avatar_filetype = $file_data2[1];

		if ( !$errors && $avatar_filesize > 0 && $avatar_filesize < $bb_cfg['avatar_filesize'] )
		{
			$avatar_data = substr($avatar_data, strlen($avatar_data) - $avatar_filesize, $avatar_filesize);

		//$tmp_path = ( !@$ini_val('safe_mode') ) ? '/tmp' : './' . $bb_cfg['avatar_path'] . '/tmp';
			$tmp_path = ini_get('upload_tmp_dir');
			$tmp_filename = tempnam($tmp_path, uniqid(rand()) . '-');

			$fptr = @fopen($tmp_filename, 'wb');
			$bytes_written = @fwrite($fptr, $avatar_data, $avatar_filesize);
			@fclose($fptr);

			if ( $bytes_written != $avatar_filesize )
			{
				@unlink($tmp_filename);
				message_die(GENERAL_ERROR, 'Could not write avatar file to local storage. Please contact the board administrator with this message', '', __LINE__, __FILE__);
			}

			list($width, $height, $type) = @getimagesize($tmp_filename);
		}
		else
		{
			$errors[] = sprintf($lang['AVATAR_FILESIZE'], round($bb_cfg['avatar_filesize'] / 1024));
		}
	}
	else if ( ( file_exists(@phpbb_realpath($avatar_filename)) ) && preg_match('/\.(jpg|jpeg|gif|png)$/i', $avatar_realname) )
	{
		if ( $avatar_filesize <= $bb_cfg['avatar_filesize'] && $avatar_filesize > 0 )
		{
			preg_match('#image\/[x\-]*([a-z]+)#', $avatar_filetype, $avatar_filetype);
			$avatar_filetype = $avatar_filetype[1];
		}
		else
		{
			$errors[] = sprintf($lang['AVATAR_FILESIZE'], round($bb_cfg['avatar_filesize'] / 1024));
			return;
		}

		list($width, $height, $type) = @getimagesize($avatar_filename);
	}

	if ( !($imgtype = check_image_type($avatar_filetype, $errors)) )
	{
		return;
	}

	switch ($type)
	{
		// GIF
		case 1:
			if ($imgtype != '.gif')
			{
				@unlink($tmp_filename);
				message_die(GENERAL_ERROR, 'Unable to upload file', '', __LINE__, __FILE__);
			}
		break;

		// JPG, JPC, JP2, JPX, JB2
		case 2:
		case 9:
		case 10:
		case 11:
		case 12:
			if ($imgtype != '.jpg' && $imgtype != '.jpeg')
			{
				@unlink($tmp_filename);
				message_die(GENERAL_ERROR, 'Unable to upload file', '', __LINE__, __FILE__);
			}
		break;

		// PNG
		case 3:
			if ($imgtype != '.png')
			{
				@unlink($tmp_filename);
				message_die(GENERAL_ERROR, 'Unable to upload file', '', __LINE__, __FILE__);
			}
		break;

		default:
			@unlink($tmp_filename);
			message_die(GENERAL_ERROR, 'Unable to upload file', '', __LINE__, __FILE__);
	}

	if ( $width > 0 && $height > 0 && $width <= $bb_cfg['avatar_max_width'] && $height <= $bb_cfg['avatar_max_height'] )
	{
		$new_filename = uniqid(rand()) . $imgtype;

		if ( $mode == 'editprofile' && $current_type == USER_AVATAR_UPLOAD && $current_avatar != '' )
		{
			user_avatar_delete($current_type, $current_avatar);
		}

		if( $avatar_mode == 'remote' )
		{
			@copy($tmp_filename, './' . $bb_cfg['avatar_path'] . "/$new_filename");
			@unlink($tmp_filename);
		}
		else
		{
			if ( @$ini_val('open_basedir') != '' )
			{
				if ( @phpversion() < '4.0.3' )
				{
					message_die(GENERAL_ERROR, 'open_basedir is set and your PHP version does not allow move_uploaded_file', '', __LINE__, __FILE__);
				}

				$move_file = 'move_uploaded_file';
			}
			else
			{
				$move_file = 'copy';
			}

			if (!is_uploaded_file($avatar_filename))
			{
				message_die(GENERAL_ERROR, 'Unable to upload file', '', __LINE__, __FILE__);
			}
			$move_file($avatar_filename, './' . $bb_cfg['avatar_path'] . "/$new_filename");
		}

		@chmod('./' . $bb_cfg['avatar_path'] . "/$new_filename", 0777);
	    return array('user_avatar' => $new_filename, 'user_avatar_type' => USER_AVATAR_UPLOAD);
	}
	else
	{
		$errors[] = sprintf($lang['AVATAR_IMAGESIZE'], $bb_cfg['avatar_max_width'], $bb_cfg['avatar_max_height']);
	    return '';
	}
}
