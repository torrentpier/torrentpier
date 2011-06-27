<?php

// ACP Header - START
if (!empty($setmodules))
{
	$module['Users']['Manage'] = basename(__FILE__);
	return;
}
require('./pagestart.php');
// ACP Header - END

require(INC_DIR .'bbcode.php');
require(INC_DIR .'functions_post.php');
require(INC_DIR .'functions_selects.php');
require(INC_DIR .'functions_validate.php');
require(INC_DIR .'functions_group.php');

array_deep($_POST, 'trim');

$html_entities_match = array('#<#', '#>#');
$html_entities_replace = array('&lt;', '&gt;');

$message = $error_msg = $username_sql = $signature_bbcode_uid = '';
$group_moderator = $mark_list = array();

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';

function return_msg_ua ($status_msg)
{
	global $lang;

	$message = $status_msg;

	$message .= '<br /><br />';
	$message .= sprintf($lang['CLICK_RETURN_USERADMIN'], '<a href="'. append_sid("admin_users.php") .'">', '</a>');
	$message .= '<br /><br />';
	$message .= sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="'. append_sid("index.php?pane=right") .'">', '</a>');

	return $message;
}

//
// Begin program
//
if ( $mode == 'edit' || $mode == 'save' && ( isset($_POST['username']) || isset($_GET[POST_USERS_URL]) || isset( $_POST[POST_USERS_URL]) ) )
{
	attachment_quota_settings('user', @$_POST['submit'], $mode);

	//
	// Ok, the profile has been modified and submitted, let's update
	//
	if ( ( $mode == 'save' && isset( $_POST['submit'] ) ) || isset( $_POST['avatargallery'] ) || isset( $_POST['submitavatar'] ) || isset( $_POST['cancelavatar'] ) )
	{
		$user_id = (int) $_POST['id'];

		if (!$this_userdata = get_userdata($user_id))
		{
			message_die(GENERAL_MESSAGE, $lang['NO_USER_ID_SPECIFIED']);
		}

		if ($userdata['user_id'] != $user_id)
		{
			if (!empty($_POST['deleteuser']))
			{
				delete_user_sessions($user_id);
				user_delete($user_id, !empty($_POST['delete_user_posts']));

				if ($this_userdata['user_level'] == MOD)
				{
					$datastore->update('moderators');
				}

				message_die(GENERAL_MESSAGE, return_msg_ua($lang['USER_DELETED']));
			}
			else if (!empty($_POST['delete_user_posts']))
			{
				post_delete('user', $user_id);
				message_die(GENERAL_MESSAGE, return_msg_ua('User posts were deleted'));
			}
		}

		$username = ( !empty($_POST['username']) ) ? clean_username($_POST['username']) : '';
		$email = ( !empty($_POST['email']) ) ? trim(strip_tags(htmlspecialchars( $_POST['email'] ) )) : '';

		$password = ( !empty($_POST['password']) ) ? trim(strip_tags(htmlspecialchars( $_POST['password'] ) )) : '';
		$password_confirm = ( !empty($_POST['password_confirm']) ) ? trim(strip_tags(htmlspecialchars( $_POST['password_confirm'] ) )) : '';

		$icq = ( !empty($_POST['icq']) ) ? trim(strip_tags( $_POST['icq'] ) ) : '';

		$website = ( !empty($_POST['website']) ) ? trim(strip_tags( $_POST['website'] ) ) : '';
		$location = ( !empty($_POST['location']) ) ? trim(strip_tags( $_POST['location'] ) ) : '';
		$occupation = ( !empty($_POST['occupation']) ) ? trim(strip_tags( $_POST['occupation'] ) ) : '';
		$interests = ( !empty($_POST['interests']) ) ? trim(strip_tags( $_POST['interests'] ) ) : '';
		$signature = ( !empty($_POST['signature']) ) ? trim(str_replace('<br />', "\n", $_POST['signature'] ) ) : '';

		validate_optional_fields($icq, $website, $location, $occupation, $interests, $signature);

		$allowviewonline = ( isset( $_POST['hideonline']) ) ? ( ( $_POST['hideonline'] ) ? 0 : TRUE ) : TRUE;
		$notifyreply = ( isset( $_POST['notifyreply']) ) ? ( ( $_POST['notifyreply'] ) ? TRUE : 0 ) : 0;
		$notifypm = ( isset( $_POST['notifypm']) ) ? ( ( $_POST['notifypm'] ) ? TRUE : 0 ) : TRUE;
		$viewemail = (int) !empty($_POST['viewemail']);
		$attachsig = (int) !empty($_POST['attachsig']);

		$user_lang = ( $_POST['language'] != $bb_cfg['board_lang'] ) ? $_POST['language'] : '';
		$user_timezone = ( isset($_POST['timezone']) ) ? str_replace(',', '.', doubleval($_POST['timezone'])) : $bb_cfg['board_timezone'];

		$user_flag = (@$_POST['user_flag'] && $_POST['user_flag'] != 'blank.gif') ? $_POST['user_flag'] : '';

		$user_template = ( @$_POST['template'] ) ? $_POST['template'] : @$bb_cfg['board_template'];
		$user_dateformat = (!empty($_POST['dateformat']) && $_POST['dateformat'] != $bb_cfg['board_dateformat']) ? $_POST['dateformat'] : '';

		$user_avatar_local = ( isset( $_POST['avatarselect'] ) && !empty($_POST['submitavatar'] ) && $bb_cfg['allow_avatar_local'] ) ? $_POST['avatarselect'] : ( ( isset( $_POST['avatarlocal'] )  ) ? $_POST['avatarlocal'] : '' );
		$user_avatar_category = ( isset($_POST['avatarcatname']) && $bb_cfg['allow_avatar_local'] ) ? htmlspecialchars($_POST['avatarcatname']) : '' ;

		$user_avatar_remoteurl = ( !empty($_POST['avatarremoteurl']) ) ? trim( $_POST['avatarremoteurl'] ) : '';
		$user_avatar_url = ( !empty($_POST['avatarurl']) ) ? trim( $_POST['avatarurl'] ) : '';
		$user_avatar_loc = ( @$_FILES['avatar']['tmp_name'] != "none") ? $_FILES['avatar']['tmp_name'] : '';
		$user_avatar_name = ( !empty($_FILES['avatar']['name']) ) ? $_FILES['avatar']['name'] : '';
		$user_avatar_size = ( !empty($_FILES['avatar']['size']) ) ? $_FILES['avatar']['size'] : 0;
		$user_avatar_filetype = ( !empty($_FILES['avatar']['type']) ) ? $_FILES['avatar']['type'] : '';

		$user_avatar = ( empty($user_avatar_loc) ) ? $this_userdata['user_avatar'] : '';
		$user_avatar_type = ( empty($user_avatar_loc) ) ? $this_userdata['user_avatar_type'] : '';

		$user_status = ( !empty($_POST['user_status']) ) ? intval( $_POST['user_status'] ) : 0;
		$user_allowpm = ( !empty($_POST['user_allowpm']) ) ? intval( $_POST['user_allowpm'] ) : 0;
		$user_rank = ( !empty($_POST['user_rank']) ) ? intval( $_POST['user_rank'] ) : 0;
		$user_allowavatar = ( !empty($_POST['user_allowavatar']) ) ? intval( $_POST['user_allowavatar'] ) : 0;

		if( isset( $_POST['avatargallery'] ) || isset( $_POST['submitavatar'] ) || isset( $_POST['cancelavatar'] ) )
		{
			$username = stripslashes($username);
			$email = stripslashes($email);
			$password = '';
			$password_confirm = '';

			$icq = stripslashes($icq);

			$website = htmlspecialchars(stripslashes($website));
			$location = htmlspecialchars(stripslashes($location));
			$occupation = htmlspecialchars(stripslashes($occupation));
			$interests = htmlspecialchars(stripslashes($interests));
			$signature = htmlspecialchars(stripslashes($signature));

			$user_lang = stripslashes($user_lang);
			$user_dateformat = htmlspecialchars(stripslashes($user_dateformat));

			if ( !isset($_POST['cancelavatar']))
			{
				$user_avatar = $user_avatar_category . '/' . $user_avatar_local;
				$user_avatar_type = USER_AVATAR_GALLERY;
			}
		}
	}

	if( isset( $_POST['submit'] ) )
	{
		include(INC_DIR . 'ucp/usercp_avatar.php');

		$error = FALSE;

		if (stripslashes($username) != $this_userdata['username'])
		{
			unset($rename_user);

			if ( stripslashes(strtolower($username)) != strtolower($this_userdata['username']) )
			{
				$result = validate_username($username);
				if ( $result['error'] )
				{
					$error = TRUE;
					$error_msg .= ( ( isset($error_msg) ) ? '<br />' : '' ) . $result['error_msg'];
				}
				else if ( strtolower(str_replace("\\'", "''", $username)) == strtolower($userdata['username']) )
				{
					$error = TRUE;
					$error_msg .= ( ( isset($error_msg) ) ? '<br />' : '' ) . $lang['USERNAME_TAKEN'];
				}
			}

			if (!$error)
			{
				$username_sql = "username = '" . str_replace("\\'", "''", $username) . "', ";
				$rename_user = $username; // Used for renaming usergroup
			}
		}

		$passwd_sql = '';
		if( !empty($password) && !empty($password_confirm) )
		{
			//
			// Awww, the user wants to change their password, isn't that cute..
			//
			if($password != $password_confirm)
			{
				$error = TRUE;
				$error_msg .= ( ( isset($error_msg) ) ? '<br />' : '' ) . $lang['PASSWORD_MISMATCH'];
			}
			else
			{
				$password = md5($password);
				$passwd_sql = "user_password = '$password', ";
			}
		}
		else if( $password && !$password_confirm )
		{
			$error = TRUE;
			$error_msg .= ( ( isset($error_msg) ) ? '<br />' : '' ) . $lang['PASSWORD_MISMATCH'];
		}
		else if( !$password && $password_confirm )
		{
			$error = TRUE;
			$error_msg .= ( ( isset($error_msg) ) ? '<br />' : '' ) . $lang['PASSWORD_MISMATCH'];
		}

		if ($signature != '')
		{
			$sig_length_check = preg_replace('/(\[.*?)(=.*?)\]/is', '\\1]', stripslashes($signature));
			$signature_bbcode_uid = ($bb_cfg['allow_bbcode']) ? make_bbcode_uid() : '';
			$signature = prepare_message($signature, $bb_cfg['allow_bbcode'], $bb_cfg['allow_smilies'], $signature_bbcode_uid);

			if ( strlen($sig_length_check) > $bb_cfg['max_sig_chars'] )
			{
				$error = TRUE;
				$error_msg .=  ( ( isset($error_msg) ) ? '<br />' : '' ) . $lang['SIGNATURE_TOO_LONG'];
			}
		}

		//
		// Avatar stuff
		//
		$avatar_sql = "";
		if( isset($_POST['avatardel']) )
		{
			if( $this_userdata['user_avatar_type'] == USER_AVATAR_UPLOAD && $this_userdata['user_avatar'] != "" )
			{
				if( @file_exists(@phpbb_realpath('./../' . $bb_cfg['avatar_path'] . "/" . $this_userdata['user_avatar'])) )
				{
					@unlink('./../' . $bb_cfg['avatar_path'] . "/" . $this_userdata['user_avatar']);
				}
			}
			$avatar_sql = ", user_avatar = '', user_avatar_type = " . USER_AVATAR_NONE;
		}
		else if( ( $user_avatar_loc != "" || !empty($user_avatar_url) ) && !$error )
		{
			//
			// Only allow one type of upload, either a
			// filename or a URL
			//
			if( !empty($user_avatar_loc) && !empty($user_avatar_url) )
			{
				$error = TRUE;
				if( isset($error_msg) )
				{
					$error_msg .= "<br />";
				}
				$error_msg .= $lang['ONLY_ONE_AVATAR'];
			}

			if( $user_avatar_loc != "" )
			{
				if( file_exists(@phpbb_realpath($user_avatar_loc)) && preg_match("/\.(gif|jpg|png)$/", $user_avatar_name) )
				{
					if( $user_avatar_size <= $bb_cfg['avatar_filesize'] && $user_avatar_size > 0)
					{
						$error_type = false;

						//
						// Opera appends the image name after the type, not big, not clever!
						//
						preg_match("'image\/[x\-]*([a-z]+)'", $user_avatar_filetype, $user_avatar_filetype);
						$user_avatar_filetype = $user_avatar_filetype[1];

						switch( $user_avatar_filetype )
						{
							case "jpeg":
							case "pjpeg":
							case "jpg":
								$imgtype = '.jpg';
								break;
							case "gif":
								$imgtype = '.gif';
								break;
							case "png":
								$imgtype = '.png';
								break;
							default:
								$error = true;
								$error_msg = (!empty($error_msg)) ? $error_msg . "<br />" . $lang['AVATAR_FILETYPE'] : $lang['AVATAR_FILETYPE'];
								break;
						}

						if( !$error )
						{
							list($width, $height) = @getimagesize($user_avatar_loc);

							if( $width <= $bb_cfg['avatar_max_width'] && $height <= $bb_cfg['avatar_max_height'] )
							{
								$user_id = $this_userdata['user_id'];

								$avatar_filename = $user_id . $imgtype;

								if( $this_userdata['user_avatar_type'] == USER_AVATAR_UPLOAD && $this_userdata['user_avatar'] != "" )
								{
									if( @file_exists(@phpbb_realpath("./../" . $bb_cfg['avatar_path'] . "/" . $this_userdata['user_avatar'])) )
									{
										@unlink("./../" . $bb_cfg['avatar_path'] . "/". $this_userdata['user_avatar']);
									}
								}
								@copy($user_avatar_loc, "./../" . $bb_cfg['avatar_path'] . "/$avatar_filename");

								$avatar_sql = ", user_avatar = '$avatar_filename', user_avatar_type = " . USER_AVATAR_UPLOAD;
							}
							else
							{
								$l_avatar_size = sprintf($lang['AVATAR_IMAGESIZE'], $bb_cfg['avatar_max_width'], $bb_cfg['avatar_max_height']);

								$error = true;
								$error_msg = ( !empty($error_msg) ) ? $error_msg . "<br />" . $l_avatar_size : $l_avatar_size;
							}
						}
					}
					else
					{
						$l_avatar_size = sprintf($lang['AVATAR_FILESIZE'], round($bb_cfg['avatar_filesize'] / 1024));

						$error = true;
						$error_msg = ( !empty($error_msg) ) ? $error_msg . "<br />" . $l_avatar_size : $l_avatar_size;
					}
				}
				else
				{
					$error = true;
					$error_msg = ( !empty($error_msg) ) ? $error_msg . "<br />" . $lang['AVATAR_FILETYPE'] : $lang['AVATAR_FILETYPE'];
				}
			}
			else if( !empty($user_avatar_url) )
			{
				//
				// First check what port we should connect
				// to, look for a :[xxxx]/ or, if that doesn't
				// exist assume port 80 (http)
				//
				preg_match("/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/", $user_avatar_url, $url_ary);

				if( !empty($url_ary[4]) )
				{
					$port = (!empty($url_ary[3])) ? $url_ary[3] : 80;

					$fsock = @fsockopen($url_ary[2], $port, $errno, $errstr);
					if( $fsock )
					{
						$base_get = "/" . $url_ary[4];

						//
						// Uses HTTP 1.1, could use HTTP 1.0 ...
						//
						@fputs($fsock, "GET $base_get HTTP/1.1\r\n");
						@fputs($fsock, "HOST: " . $url_ary[2] . "\r\n");
						@fputs($fsock, "Connection: close\r\n\r\n");

						$avatar_data = '';
						while( !@feof($fsock) )
						{
							$avatar_data .= @fread($fsock, $bb_cfg['avatar_filesize']);
						}
						@fclose($fsock);

						if( preg_match("/Content-Length\: ([0-9]+)[^\/ ][\s]+/i", $avatar_data, $file_data1) && preg_match("/Content-Type\: image\/[x\-]*([a-z]+)[\s]+/i", $avatar_data, $file_data2) )
						{
							$file_size = $file_data1[1];
							$file_type = $file_data2[1];

							switch( $file_type )
							{
								case "jpeg":
								case "pjpeg":
								case "jpg":
									$imgtype = '.jpg';
									break;
								case "gif":
									$imgtype = '.gif';
									break;
								case "png":
									$imgtype = '.png';
									break;
								default:
									$error = true;
									$error_msg = (!empty($error_msg)) ? $error_msg . "<br />" . $lang['AVATAR_FILETYPE'] : $lang['AVATAR_FILETYPE'];
									break;
							}

							if( !$error && $file_size > 0 && $file_size < $bb_cfg['avatar_filesize'] )
							{
								$avatar_data = substr($avatar_data, strlen($avatar_data) - $file_size, $file_size);

								$tmp_filename = tempnam ("/tmp", $this_userdata['user_id'] . "-");
								$fptr = @fopen($tmp_filename, "wb");
								$bytes_written = @fwrite($fptr, $avatar_data, $file_size);
								@fclose($fptr);

								if( $bytes_written == $file_size )
								{
									list($width, $height) = @getimagesize($tmp_filename);

									if( $width <= $bb_cfg['avatar_max_width'] && $height <= $bb_cfg['avatar_max_height'] )
									{
										$user_id = $this_userdata['user_id'];

										$avatar_filename = $user_id . $imgtype;

										if( $this_userdata['user_avatar_type'] == USER_AVATAR_UPLOAD && $this_userdata['user_avatar'] != "")
										{
											if( file_exists(@phpbb_realpath("./../" . $bb_cfg['avatar_path'] . "/" . $this_userdata['user_avatar'])) )
											{
												@unlink("./../" . $bb_cfg['avatar_path'] . "/" . $this_userdata['user_avatar']);
											}
										}
										@copy($tmp_filename, "./../" . $bb_cfg['avatar_path'] . "/$avatar_filename");
										@unlink($tmp_filename);

										$avatar_sql = ", user_avatar = '$avatar_filename', user_avatar_type = " . USER_AVATAR_UPLOAD;
									}
									else
									{
										$l_avatar_size = sprintf($lang['AVATAR_IMAGESIZE'], $bb_cfg['avatar_max_width'], $bb_cfg['avatar_max_height']);

										$error = true;
										$error_msg = ( !empty($error_msg) ) ? $error_msg . "<br />" . $l_avatar_size : $l_avatar_size;
									}
								}
								else
								{
									//
									// Error writing file
									//
									@unlink($tmp_filename);
									message_die(GENERAL_ERROR, "Could not write avatar file to local storage. Please contact the board administrator with this message", "", __LINE__, __FILE__);
								}
							}
						}
						else
						{
							//
							// No data
							//
							$error = true;
							$error_msg = ( !empty($error_msg) ) ? $error_msg . "<br />" . $lang['FILE_NO_DATA'] : $lang['FILE_NO_DATA'];
						}
					}
					else
					{
						//
						// No connection
						//
						$error = true;
						$error_msg = ( !empty($error_msg) ) ? $error_msg . "<br />" . $lang['NO_CONNECTION_URL'] : $lang['NO_CONNECTION_URL'];
					}
				}
				else
				{
					$error = true;
					$error_msg = ( !empty($error_msg) ) ? $error_msg . "<br />" . $lang['INCOMPLETE_URL'] : $lang['INCOMPLETE_URL'];
				}
			}
			else if( !empty($user_avatar_name) )
			{
				$l_avatar_size = sprintf($lang['AVATAR_FILESIZE'], round($bb_cfg['avatar_filesize'] / 1024));

				$error = true;
				$error_msg = ( !empty($error_msg) ) ? $error_msg . "<br />" . $l_avatar_size : $l_avatar_size;
			}
		}
		else if( $user_avatar_remoteurl != "" && $avatar_sql == "" && !$error )
		{
			if( !preg_match("#^http:\/\/#i", $user_avatar_remoteurl) )
			{
				$user_avatar_remoteurl = "http://" . $user_avatar_remoteurl;
			}

			if( preg_match("#^(http:\/\/[a-z0-9\-]+?\.([a-z0-9\-]+\.)*[a-z]+\/.*?\.(gif|jpg|png)$)#is", $user_avatar_remoteurl) )
			{
				$avatar_sql = ", user_avatar = '" . str_replace("\'", "''", $user_avatar_remoteurl) . "', user_avatar_type = " . USER_AVATAR_REMOTE;
			}
			else
			{
				$error = true;
				$error_msg = ( !empty($error_msg) ) ? $error_msg . "<br />" . $lang['WRONG_REMOTE_AVATAR_FORMAT'] : $lang['WRONG_REMOTE_AVATAR_FORMAT'];
			}
		}
		else if( $user_avatar_local != "" && $avatar_sql == "" && !$error )
		{
			$avatar_sql = ", user_avatar = '" . str_replace("\'", "''", phpbb_ltrim(basename($user_avatar_category), "'") . '/' . phpbb_ltrim(basename($user_avatar_local), "'")) . "', user_avatar_type = " . USER_AVATAR_GALLERY;
		}

		//
		// Update entry in DB
		//
		if( !$error )
		{
			$update_user_opt = array(
				'viewemail',
				'attachsig',
			);
			$user_opt = $this_userdata['user_opt'];

			foreach ($update_user_opt as $opt)
			{
				setbit($user_opt, $bf['user_opt'][$opt], !empty($_POST[$opt]));
			}

			$sql = "UPDATE " . BB_USERS . "
				SET " . $username_sql . $passwd_sql . "
					user_email = '" . str_replace("\'", "''", $email) . "',
					user_icq = '" . str_replace("\'", "''", $icq) . "',
					user_website = '" . str_replace("\'", "''", $website) . "',
					user_occ = '" . str_replace("\'", "''", $occupation) . "',
					user_from = '" . str_replace("\'", "''", $location) . "',
					user_from_flag = '$user_flag',
					user_interests = '" . str_replace("\'", "''", $interests) . "',
					user_sig = '" . str_replace("\'", "''", $signature) . "',
					user_opt = $user_opt,
					user_allow_viewonline = $allowviewonline,
					user_notify = $notifyreply,
					user_notify_pm = $notifypm,
					user_sig_bbcode_uid = '$signature_bbcode_uid',
					user_allowavatar = $user_allowavatar,
					user_allow_pm = $user_allowpm,
					user_lang = '" . str_replace("\'", "''", $user_lang) . "',
					user_timezone = $user_timezone,
					user_dateformat = '" . str_replace("\'", "''", $user_dateformat) . "',
					user_active = $user_status,
					user_rank = $user_rank" . $avatar_sql . ",
					user_actkey = ''
				WHERE user_id = $user_id";

			if( $result = DB()->sql_query($sql) )
			{
				// Delete user session, to prevent the user navigating the forum (if logged in) when disabled
				if (!$user_status)
				{
					delete_user_sessions($user_id);
				}

				$message .= $lang['ADMIN_USER_UPDATED'];
			}
			else
			{
				$error = TRUE;
				$error_msg .= ( ( isset($error_msg) ) ? '<br />' : '' ) . $lang['ADMIN_USER_FAIL'];
			}

			if ($this_userdata['user_level'] == MOD)
			{
				$datastore->update('moderators');
			}

			if ($this_userdata['user_active'] != $user_status)
			{
				$log_action_type = (!$user_status) ? 'adm_user_ban' : 'adm_user_unban';

				$log_action->admin($log_action_type, array(
					'log_msg' => 'user: '. get_usernames_for_log($user_id),
				));
			}

			$message .= '<br /><br />' . sprintf($lang['CLICK_RETURN_USERADMIN'], '<a href="' . append_sid("admin_users.php") . '">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="' . append_sid("index.php?pane=right") . '">', '</a>');

			message_die(GENERAL_MESSAGE, $message);
		}
		else
		{
			$template->assign_vars(array('ERROR_MESSAGE' => $error_msg));

			$username = htmlspecialchars(stripslashes($username));
			$email = stripslashes($email);
			$password = '';
			$password_confirm = '';

			$icq = stripslashes($icq);

			$website = htmlspecialchars(stripslashes($website));
			$location = htmlspecialchars(stripslashes($location));
			$occupation = htmlspecialchars(stripslashes($occupation));
			$interests = htmlspecialchars(stripslashes($interests));
			$signature = htmlspecialchars(stripslashes($signature));

			$user_lang = stripslashes($user_lang);
			$user_dateformat = htmlspecialchars(stripslashes($user_dateformat));
		}
	}
	else if( !isset( $_POST['submit'] ) && $mode != 'save' && !isset( $_POST['avatargallery'] ) && !isset( $_POST['submitavatar'] ) && !isset( $_POST['cancelavatar'] ) )
	{
		if( isset( $_GET[POST_USERS_URL]) || isset( $_POST[POST_USERS_URL]) )
		{
			$user_id = ( isset( $_POST[POST_USERS_URL]) ) ? intval( $_POST[POST_USERS_URL]) : intval( $_GET[POST_USERS_URL]);
			$this_userdata = get_userdata($user_id);
			if( !$this_userdata )
			{
				message_die(GENERAL_MESSAGE, $lang['NO_USER_ID_SPECIFIED'] );
			}
		}
		else
		{
			$this_userdata = get_userdata($_POST['username'], true);
			if( !$this_userdata )
			{
				message_die(GENERAL_MESSAGE, $lang['NO_USER_ID_SPECIFIED'] );
			}
		}

		//
		// Now parse and display it as a template
		//
		$user_id = $this_userdata['user_id'];
		$username = $this_userdata['username'];
		$email = $this_userdata['user_email'];
		$password = '';
		$password_confirm = '';

		$icq = $this_userdata['user_icq'];

		$website = htmlspecialchars($this_userdata['user_website']);
		$location = htmlspecialchars($this_userdata['user_from']);
		$user_flag = htmlspecialchars($this_userdata['user_from_flag']);

		$occupation = htmlspecialchars($this_userdata['user_occ']);
		$interests = htmlspecialchars($this_userdata['user_interests']);

		$signature = ($this_userdata['user_sig_bbcode_uid'] != '') ? preg_replace('#:' . $this_userdata['user_sig_bbcode_uid'] . '#si', '', $this_userdata['user_sig']) : $this_userdata['user_sig'];
		$signature = preg_replace($html_entities_match, $html_entities_replace, $signature);

		$viewemail = bf($this_userdata['user_opt'], 'user_opt', 'viewemail');
		$notifypm = $this_userdata['user_notify_pm'];
		$notifyreply = $this_userdata['user_notify'];
		$attachsig = bf($this_userdata['user_opt'], 'user_opt', 'attachsig');
		$allowviewonline = $this_userdata['user_allow_viewonline'];

		$user_avatar = $this_userdata['user_avatar'];
		$user_avatar_type = $this_userdata['user_avatar_type'];
		$user_timezone = $this_userdata['user_timezone'];

		$user_lang       = ($this_userdata['user_lang']) ? $this_userdata['user_lang'] : $bb_cfg['default_lang'];
		$user_dateformat = ($this_userdata['user_dateformat']) ? $this_userdata['user_dateformat'] : $bb_cfg['default_dateformat'];

		$user_status = $this_userdata['user_active'];
		$user_allowavatar = $this_userdata['user_allowavatar'];
		$user_allowpm = $this_userdata['user_allow_pm'];

		$bbcode_status = ($bb_cfg['allow_bbcode']) ? $lang['BBCODE_IS_ON'] : $lang['BBCODE_IS_OFF'];
		$smilies_status = ($bb_cfg['allow_smilies']) ? $lang['SMILIES_ARE_ON'] : $lang['SMILIES_ARE_OFF'];
	}

	if( isset($_POST['avatargallery']) && !$error )
	{
		if( !$error )
		{
			$user_id = intval($_POST['id']);

			$dir = @opendir("../" . $bb_cfg['avatar_gallery_path']);

			$avatar_images = array();
			while( $file = @readdir($dir) )
			{
				if( $file != "." && $file != ".." && !is_file(phpbb_realpath("./../" . $bb_cfg['avatar_gallery_path'] . "/" . $file)) && !is_link(phpbb_realpath("./../" . $bb_cfg['avatar_gallery_path'] . "/" . $file)) )
				{
					$sub_dir = @opendir("../" . $bb_cfg['avatar_gallery_path'] . "/" . $file);

					$avatar_row_count = 0;
					$avatar_col_count = 0;

					while( $sub_file = @readdir($sub_dir) )
					{
						if( preg_match("/(\.gif$|\.png$|\.jpg)$/is", $sub_file) )
						{
							$avatar_images[$file][$avatar_row_count][$avatar_col_count] = $sub_file;

							$avatar_col_count++;
							if( $avatar_col_count == 5 )
							{
								$avatar_row_count++;
								$avatar_col_count = 0;
							}
						}
					}
				}
			}

			@closedir($dir);

			if( isset($_POST['avatarcategory']) )
			{
				$category = htmlspecialchars($_POST['avatarcategory']);
			}
			else
			{
				list($category, ) = each($avatar_images);
			}
			@reset($avatar_images);

			$s_categories = "";
			while( list($key) = each($avatar_images) )
			{
				$selected = ( $key == $category ) ? "selected=\"selected\"" : "";
				if( count($avatar_images[$key]) )
				{
					$s_categories .= '<option value="' . $key . '"' . $selected . '>' . ucfirst($key) . '</option>';
				}
			}

			$s_colspan = 0;
			for($i = 0; $i < count($avatar_images[$category]); $i++)
			{
				$template->assign_block_vars("avatar_row", array());

				$s_colspan = max($s_colspan, count($avatar_images[$category][$i]));

				for($j = 0; $j < count($avatar_images[$category][$i]); $j++)
				{
					$template->assign_block_vars("avatar_row.avatar_column", array(
						"AVATAR_IMAGE" => "../" . $bb_cfg['avatar_gallery_path'] . '/' . $category . '/' . $avatar_images[$category][$i][$j])
					);

					$template->assign_block_vars("avatar_row.avatar_option_column", array(
						"S_OPTIONS_AVATAR" => $avatar_images[$category][$i][$j])
					);
				}
			}

			$s_hidden_fields = '<input type="hidden" name="mode" value="edit" /><input type="hidden" name="avatarcatname" value="' . $category . '" />';
			$s_hidden_fields .= '<input type="hidden" name="id" value="' . $user_id . '" />';

			$s_hidden_fields .= '<input type="hidden" name="username" value="' . str_replace("\"", "&quot;", $username) . '" />';
			$s_hidden_fields .= '<input type="hidden" name="email" value="' . str_replace("\"", "&quot;", $email) . '" />';
			$s_hidden_fields .= '<input type="hidden" name="icq" value="' . str_replace("\"", "&quot;", $icq) . '" />';
			$s_hidden_fields .= '<input type="hidden" name="website" value="' . str_replace("\"", "&quot;", $website) . '" />';
			$s_hidden_fields .= '<input type="hidden" name="location" value="' . str_replace("\"", "&quot;", $location) . '" />';

// FLAGHACK-start
			$s_hidden_fields .= '<input type="hidden" name="user_flag" value="' . $user_flag . '" />';
// FLAGHACK-end

			$s_hidden_fields .= '<input type="hidden" name="occupation" value="' . str_replace("\"", "&quot;", $occupation) . '" />';
			$s_hidden_fields .= '<input type="hidden" name="interests" value="' . str_replace("\"", "&quot;", $interests) . '" />';
			$s_hidden_fields .= '<input type="hidden" name="signature" value="' . str_replace("\"", "&quot;", $signature) . '" />';
			$s_hidden_fields .= '<input type="hidden" name="viewemail" value="' . $viewemail . '" />';
			$s_hidden_fields .= '<input type="hidden" name="notifypm" value="' . $notifypm . '" />';
			$s_hidden_fields .= '<input type="hidden" name="notifyreply" value="' . $notifyreply . '" />';
			$s_hidden_fields .= '<input type="hidden" name="attachsig" value="' . $attachsig . '" />';
			$s_hidden_fields .= '<input type="hidden" name="hideonline" value="' . !$allowviewonline . '" />';
			$s_hidden_fields .= '<input type="hidden" name="language" value="' . $user_lang . '" />';
			$s_hidden_fields .= '<input type="hidden" name="timezone" value="' . $user_timezone . '" />';
			$s_hidden_fields .= '<input type="hidden" name="dateformat" value="' . htmlCHR($user_dateformat) . '" />';

			$s_hidden_fields .= '<input type="hidden" name="user_status" value="' . $user_status . '" />';
			$s_hidden_fields .= '<input type="hidden" name="user_allowpm" value="' . $user_allowpm . '" />';
			$s_hidden_fields .= '<input type="hidden" name="user_allowavatar" value="' . $user_allowavatar . '" />';
			$s_hidden_fields .= '<input type="hidden" name="user_rank" value="' . $user_rank . '" />';

			$template->assign_vars(array(
				'TPL_ADMIN_USER_AVATAR_GALLERY' => true,

				"L_USER_EXPLAIN" => $lang['USER_ADMIN_EXPLAIN'],

				"S_OPTIONS_CATEGORIES" => $s_categories,
				"S_COLSPAN" => $s_colspan,
				"S_PROFILE_ACTION" => append_sid("admin_users.php?mode=$mode"),
				"S_HIDDEN_FIELDS" => $s_hidden_fields)
			);
		}
	}
	else
	{
		$s_hidden_fields = '<input type="hidden" name="mode" value="save" />';
		$s_hidden_fields .= '<input type="hidden" name="id" value="' . $this_userdata['user_id'] . '" />';

		if( !empty($user_avatar_local) )
		{
			$s_hidden_fields .= '<input type="hidden" name="avatarlocal" value="' . $user_avatar_local . '" /><input type="hidden" name="avatarcatname" value="' . $user_avatar_category . '" />';
		}

		if( $user_avatar_type )
		{
			switch( $user_avatar_type )
			{
				case USER_AVATAR_UPLOAD:
					$avatar = '<img src="../' . $bb_cfg['avatar_path'] . '/' . $user_avatar . '" alt="" />';
					break;
				case USER_AVATAR_REMOTE:
					$avatar = '<img src="' . $user_avatar . '" alt="" />';
					break;
				case USER_AVATAR_GALLERY:
					$avatar = '<img src="../' . $bb_cfg['avatar_gallery_path'] . '/' . $user_avatar . '" alt="" />';
					break;
			}
		}
		else
		{
			$avatar = "";
		}

		$sql = "SELECT * FROM " . BB_RANKS . "
			WHERE rank_special = 1
			ORDER BY rank_title";
		if ( !($result = DB()->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not obtain ranks data', '', __LINE__, __FILE__, $sql);
		}

		$rank_select_box = '<option value="0">' . $lang['NO_ASSIGNED_RANK'] . '</option>';
		while( $row = DB()->sql_fetchrow($result) )
		{
			$rank = $row['rank_title'];
			$rank_id = $row['rank_id'];

			$selected = ( $this_userdata['user_rank'] == $rank_id ) ? ' selected="selected"' : '';
			$rank_select_box .= '<option value="' . $rank_id . '"' . $selected . '>' . $rank . '</option>';
		}

		//
		// Let's do an overall check for settings/versions which would prevent
		// us from doing file uploads....
		//
		$ini_val = ( phpversion() >= '4.0.0' ) ? 'ini_get' : 'get_cfg_var';
		$form_enctype = ( !@$ini_val('file_uploads') || phpversion() == '4.0.4pl1' || !$bb_cfg['allow_avatar_upload'] || ( phpversion() < '4.0.3' && @$ini_val('open_basedir') != '' ) ) ? '' : 'enctype="multipart/form-data"';

		// query to get the list of flags
		$sql = "SELECT *
			FROM " . BB_COUNTRIES . "
			ORDER BY country_id";
		if(!$flags_result = DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Couldn't obtain flags information.", "", __LINE__, __FILE__, $sql);
		}
		$flag_row = DB()->sql_fetchrowset($flags_result);
		$num_flags = DB()->num_rows($flags_result);

		// build the html select statement
		if(!defined('COUNTRIES_LANG'))
		{
			include(LANG_DIR . "lang_countries.php");
		}

		$flag_start_image = 'blank.gif' ;
		$selected = ( isset($user_flag) ) ? '' : HTML_SELECTED;
		$flag_select = "<select name=\"user_flag\" onChange=\"document.images['user_flag'].src = '../images/flags/' + this.value + '.png';\" >";
		$flag_select .= "<option value=\"blank.gif\"$selected>" . $lang['SELECT_COUNTRY'] . "</option>";

		for ($i = 0; $i < $num_flags; $i++)
		{
			$country_code = $flag_row[$i]['country_code'];
			$country_name = $lang['COUNTRIES'][$country_code];
			$flag_image = $country_code . '.png';
			$selected = ( isset( $user_flag) ) ? (($user_flag == $country_code) ? HTML_SELECTED : '' ) : '' ;
			$flag_select .= "\t<option value=\"$country_code\"$selected>$country_name</option>";
			if ( isset( $user_flag) && ($user_flag == $country_code))
			{
				$flag_start_image = $flag_image ;
			}
		}
		$flag_select .= '</select>';

		$template->assign_vars(array(
			'TPL_ADMIN_USER_EDIT' => true,

			'USERNAME' => $username,
			'EMAIL' => $email,
			'ICQ' => $icq,
			'OCCUPATION' => $occupation,
			'INTERESTS' => $interests,
			'LOCATION' => $location,
			'L_FLAG' => $lang['COUNTRY_FLAG'],
			'FLAG_SELECT' => $flag_select,
			'FLAG_START' => $flag_start_image,

			'WEBSITE' => $website,
			'SIGNATURE' => str_replace('<br />', "\n", $signature),
			'VIEW_EMAIL_YES' => ($viewemail) ? 'checked="checked"' : '',
			'VIEW_EMAIL_NO' => (!$viewemail) ? 'checked="checked"' : '',
			'HIDE_USER_YES' => (!$allowviewonline) ? 'checked="checked"' : '',
			'HIDE_USER_NO' => ($allowviewonline) ? 'checked="checked"' : '',
			'NOTIFY_PM_YES' => ($notifypm) ? 'checked="checked"' : '',
			'NOTIFY_PM_NO' => (!$notifypm) ? 'checked="checked"' : '',
			'ALWAYS_ADD_SIGNATURE_YES' => ($attachsig) ? 'checked="checked"' : '',
			'ALWAYS_ADD_SIGNATURE_NO' => (!$attachsig) ? 'checked="checked"' : '',
			'NOTIFY_REPLY_YES' => ( $notifyreply ) ? 'checked="checked"' : '',
			'NOTIFY_REPLY_NO' => ( !$notifyreply ) ? 'checked="checked"' : '',
			'ADMIN_AVATAR' => $avatar,
			'LANGUAGE_SELECT' => language_select($user_lang),
			'TIMEZONE_SELECT' => tz_select($user_timezone),
			'STYLE_SELECT' => $bb_cfg['tpl_name'],
			'DATE_FORMAT' => $user_dateformat,
			'ALLOW_PM_YES' => ($user_allowpm) ? 'checked="checked"' : '',
			'ALLOW_PM_NO' => (!$user_allowpm) ? 'checked="checked"' : '',
			'ALLOW_AVATAR_YES' => ($user_allowavatar) ? 'checked="checked"' : '',
			'ALLOW_AVATAR_NO' => (!$user_allowavatar) ? 'checked="checked"' : '',
			'USER_ACTIVE_YES' => ($user_status) ? 'checked="checked"' : '',
			'USER_ACTIVE_NO' => (!$user_status) ? 'checked="checked"' : '',
			'RANK_SELECT_BOX' => $rank_select_box,

			'L_USER_EXPLAIN' => $lang['USER_ADMIN_EXPLAIN'],
			'L_BOARD_LANGUAGE' => $lang['BOARD_LANG'],
			'L_ALWAYS_ADD_SIGNATURE' => $lang['ALWAYS_ADD_SIG'],

			'L_SPECIAL' => $lang['USER_SPECIAL'],
			'L_SPECIAL_EXPLAIN' => $lang['USER_SPECIAL_EXPLAIN'],
			'L_USER_ACTIVE' => $lang['USER_STATUS'],
			'L_ALLOW_PM' => $lang['USER_ALLOWPM'],
			'L_ALLOW_AVATAR' => $lang['USER_ALLOWAVATAR'],

			'L_AVATAR_EXP' => $lang['ADMIN_AVATAR_EXPLAIN'],
			'L_DELETE_AVATAR' => $lang['DELETE_IMAGE'],
			'L_AVATAR_GALLERY' => $lang['SELECT_FROM_GALLERY'],
			'L_SHOW_GALLERY' => $lang['VIEW_AVATAR_GALLERY'],
			'L_LINK_REMOTE_AVATAR' => $lang['LINK_REMOTE_AVATAR'],

			'L_SIGNATURE_EXP' => sprintf($lang['SIGNATURE_EXPLAIN'], $bb_cfg['max_sig_chars'] ),
			'L_NOTIFY_ON_REPLY' => $lang['ALWAYS_NOTIFY'],
			'L_PROFILE_INFO_NOTICE' => $lang['PROFILE_INFO_WARN'],
			'S_FORM_ENCTYPE' => $form_enctype,

			'BBCODE_STATUS' => sprintf(@$bbcode_status, '<a href="../' . append_sid("faq.php?mode=bbcode") . '" target="_phpbbcode">', '</a>'),
			'SMILIES_STATUS' => @$smilies_status,

			'L_DELETE_USER' => $lang['USER_DELETE'],
			'L_DELETE_USER_EXPLAIN' => $lang['USER_DELETE_EXPLAIN'],
			'L_SELECT_RANK' => $lang['RANK_TITLE'],

			'S_HIDDEN_FIELDS' => $s_hidden_fields,
			'S_PROFILE_ACTION' => append_sid("admin_users.php"))
		);

		if( file_exists(@phpbb_realpath('./../' . $bb_cfg['avatar_path'])) && ($bb_cfg['allow_avatar_upload'] == TRUE) )
		{
			if ( $form_enctype != '' )
			{
				$template->assign_block_vars('avatar_local_upload', array() );
			}
			$template->assign_block_vars('avatar_remote_upload', array() );
		}

		if( file_exists(@phpbb_realpath('./../' . $bb_cfg['avatar_gallery_path'])) && ($bb_cfg['allow_avatar_local'] == TRUE) )
		{
			$template->assign_block_vars('avatar_local_gallery', array() );
		}

		if( $bb_cfg['allow_avatar_remote'] == TRUE )
		{
			$template->assign_block_vars('avatar_remote_link', array() );
		}
	}
}
else
{
	//
	// Default user selection box
	//
	$template->assign_vars(array(
		'TPL_ADMIN_USER_SELECT' => true,

		'L_USER_EXPLAIN' => $lang['USER_ADMIN_EXPLAIN'],

		'U_SEARCH_USER' => append_sid("./../search.php?mode=searchuser"),

		'S_USER_ACTION' => append_sid("admin_users.php"),
		'S_USER_SELECT' => @$select_list)
	);
}

print_page('admin_users.tpl', 'admin');