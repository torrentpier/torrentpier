<?php

if (!defined('IN_FORUM')) die("Hacking attempt");

define('FILENAME_PREFIX',         false);
define('FILENAME_PREFIX_LENGTH',  6);
define('FILENAME_MAX_LENGTH',     180);
define('FILENAME_CRYPTIC',        false);
define('FILENAME_CRYPTIC_LENGTH', 64);

class attach_parent
{
	var $post_attach = false;
	var $attach_filename = '';
	var $filename = '';
	var $type = '';
	var $extension = '';
	var $file_comment = '';
	var $num_attachments = 0; // number of attachments in message
	var $filesize = 0;
	var $filetime = 0;
	var $thumbnail = 0;
	var $page = 0; // On which page we are on ? This should be filled by child classes.

	// Switches
	var $add_attachment_body = 0;
	var $posted_attachments_body = 0;

	/**
	* Constructor
	*/
	function attach_parent()
	{
		$this->add_attachment_body = get_var('add_attachment_body', 0);
		$this->posted_attachments_body = get_var('posted_attachments_body', 0);

		$this->file_comment					= get_var('filecomment', '');
		$this->attachment_id_list			= get_var('attach_id_list', array(0));
		$this->attachment_comment_list		= get_var('comment_list', array(''));
		$this->attachment_filesize_list		= get_var('filesize_list', array(0));
		$this->attachment_filetime_list		= get_var('filetime_list', array(0));
		$this->attachment_filename_list		= get_var('filename_list', array(''));
		$this->attachment_extension_list	= get_var('extension_list', array(''));
		$this->attachment_mimetype_list		= get_var('mimetype_list', array(''));

		$this->filename = (isset($_FILES['fileupload']) && isset($_FILES['fileupload']['name']) && $_FILES['fileupload']['name'] != 'none') ? trim(stripslashes($_FILES['fileupload']['name'])) : '';

		$this->attachment_list = get_var('attachment_list', array(''));
		$this->attachment_thumbnail_list = get_var('attach_thumbnail_list', array(0));
	}

	/**
	* Get Quota Limits
	*/
	function get_quota_limits($userdata_quota, $user_id = 0)
	{
		global $attach_config;

//		$priority = 'group;user';
		$priority = 'user;group';

		if (IS_ADMIN)
		{
			$attach_config['pm_filesize_limit'] = 0; // Unlimited
			$attach_config['upload_filesize_limit'] = 0; // Unlimited
			return;
		}

		$quota_type = QUOTA_UPLOAD_LIMIT;
		$limit_type = 'upload_filesize_limit';
		$default = 'attachment_quota';

		if (!$user_id)
		{
			$user_id = intval($userdata_quota['user_id']);
		}

		$priority = explode(';', $priority);
		$found = false;

		for ($i = 0; $i < sizeof($priority); $i++)
		{
			if (($priority[$i] == 'group') && (!$found))
			{
				// Get Group Quota, if we find one, we have our quota
				$sql = 'SELECT u.group_id
					FROM ' . BB_USER_GROUP . ' u, ' . BB_GROUPS . ' g
					WHERE g.group_single_user = 0
						AND u.user_pending = 0
						AND u.group_id = g.group_id
						AND u.user_id = ' . $user_id;

				if (!($result = DB()->sql_query($sql)))
				{
					bb_die('Could not get user group');
				}

				$rows = DB()->sql_fetchrowset($result);
				$num_rows = DB()->num_rows($result);
				DB()->sql_freeresult($result);

				if ($num_rows > 0)
				{
					$group_id = array();

					for ($j = 0; $j < $num_rows; $j++)
					{
						$group_id[] = (int) $rows[$j]['group_id'];
					}

					$sql = 'SELECT l.quota_limit
						FROM ' . BB_QUOTA . ' q, ' . BB_QUOTA_LIMITS . ' l
						WHERE q.group_id IN (' . implode(', ', $group_id) . ')
							AND q.group_id <> 0
							AND q.quota_type = ' . $quota_type . '
							AND q.quota_limit_id = l.quota_limit_id
						ORDER BY l.quota_limit DESC
						LIMIT 1';

					if (!($result = DB()->sql_query($sql)))
					{
						bb_die('Could not get group quota');
					}

					if (DB()->num_rows($result) > 0)
					{
						$row = DB()->sql_fetchrow($result);
						$attach_config[$limit_type] = $row['quota_limit'];
						$found = TRUE;
					}
					DB()->sql_freeresult($result);
				}
			}

			if ($priority[$i] == 'user' && !$found)
			{
				// Get User Quota, if the user is not in a group or the group has no quotas
				$sql = 'SELECT l.quota_limit
					FROM ' . BB_QUOTA . ' q, ' . BB_QUOTA_LIMITS . ' l
					WHERE q.user_id = ' . $user_id . '
						AND q.user_id <> 0
						AND q.quota_type = ' . $quota_type . '
						AND q.quota_limit_id = l.quota_limit_id
					LIMIT 1';

				if (!($result = DB()->sql_query($sql)))
				{
					bb_die('Could not get user quota');
				}

				if (DB()->num_rows($result) > 0)
				{
					$row = DB()->sql_fetchrow($result);
					$attach_config[$limit_type] = $row['quota_limit'];
					$found = TRUE;
				}
				DB()->sql_freeresult($result);
			}
		}

		if (!$found)
		{
			// Set Default Quota Limit
			$quota_id = ($quota_type == QUOTA_UPLOAD_LIMIT) ? $attach_config['default_upload_quota'] : $attach_config['default_pm_quota'];

			if ($quota_id == 0)
			{
				$attach_config[$limit_type] = $attach_config[$default];
			}
			else
			{
				$sql = 'SELECT quota_limit
					FROM ' . BB_QUOTA_LIMITS . '
					WHERE quota_limit_id = ' . (int) $quota_id . '
					LIMIT 1';

				if (!($result = DB()->sql_query($sql)))
				{
					bb_die('Could not get default quota limit');
				}

				if (DB()->num_rows($result) > 0)
				{
					$row = DB()->sql_fetchrow($result);
					$attach_config[$limit_type] = $row['quota_limit'];
				}
				else
				{
					$attach_config[$limit_type] = $attach_config[$default];
				}
				DB()->sql_freeresult($result);
			}
		}

		// Never exceed the complete Attachment Upload Quota
		if ($quota_type == QUOTA_UPLOAD_LIMIT)
		{
			if ($attach_config[$limit_type] > $attach_config[$default])
			{
				$attach_config[$limit_type] = $attach_config[$default];
			}
		}
	}

	/**
	* Handle all modes... (intern)
	* @private
	*/
	function handle_attachments($mode)
	{
		global $is_auth, $attach_config, $refresh, $post_id, $submit, $preview, $error, $error_msg, $lang;

		//
		// ok, what shall we do ;)
		//

		if (IS_ADMIN)
		{
			$max_attachments = ADMIN_MAX_ATTACHMENTS;
		}
		else
		{
			$max_attachments = intval($attach_config['max_attachments']);
		}

		$sql_id = 'post_id';

		// nothing, if the user is not authorized or attachment mod disabled
		if (intval($attach_config['disable_mod']) || !$is_auth['auth_attachments'])
		{
			return false;
		}

		// Init Vars
		$attachments = array();

		if (!$refresh)
		{
			$add = (isset($_POST['add_attachment'])) ? TRUE : FALSE;
			$delete = (isset($_POST['del_attachment'])) ? TRUE : FALSE;
			$edit = ( isset($_POST['edit_comment']) ) ? TRUE : FALSE;
			$update_attachment = ( isset($_POST['update_attachment']) ) ? TRUE : FALSE;
			$del_thumbnail = ( isset($_POST['del_thumbnail']) ) ? TRUE : FALSE;

			$add_attachment_box = (!empty($_POST['add_attachment_box'])) ? TRUE : FALSE;
			$posted_attachments_box = (!empty($_POST['posted_attachments_box'])) ? TRUE : FALSE;

			$refresh = $add || $delete || $edit || $del_thumbnail || $update_attachment || $add_attachment_box || $posted_attachments_box;
		}

		// Get Attachments
		$attachments = get_attachments_from_post($post_id);

		$auth = ($is_auth['auth_edit'] || $is_auth['auth_mod']) ? TRUE : FALSE;

		if (!$submit && $mode == 'editpost' && $auth)
		{
			if (!$refresh && !$preview && !$error)
			{
				for ($i = 0; $i < sizeof($attachments); $i++)
				{
					$this->attachment_list[] = $attachments[$i]['physical_filename'];
					$this->attachment_comment_list[] = $attachments[$i]['comment'];
					$this->attachment_filename_list[] = $attachments[$i]['real_filename'];
					$this->attachment_extension_list[] = $attachments[$i]['extension'];
					$this->attachment_mimetype_list[] = $attachments[$i]['mimetype'];
					$this->attachment_filesize_list[] = $attachments[$i]['filesize'];
					$this->attachment_filetime_list[] = $attachments[$i]['filetime'];
					$this->attachment_id_list[] = $attachments[$i]['attach_id'];
					$this->attachment_thumbnail_list[] = $attachments[$i]['thumbnail'];
				}
			}
		}

		$this->num_attachments = sizeof($this->attachment_list);

		if ($submit)
		{
			if ($mode == 'newtopic' || $mode == 'reply' || $mode == 'editpost')
			{
				if ($this->filename != '')
				{
					if ($this->num_attachments < intval($max_attachments))
					{
						$this->upload_attachment($this->page);

						if (!$error && $this->post_attach)
						{
							array_unshift($this->attachment_list, $this->attach_filename);
							array_unshift($this->attachment_comment_list, $this->file_comment);
							array_unshift($this->attachment_filename_list, $this->filename);
							array_unshift($this->attachment_extension_list, $this->extension);
							array_unshift($this->attachment_mimetype_list, $this->type);
							array_unshift($this->attachment_filesize_list, $this->filesize);
							array_unshift($this->attachment_filetime_list, $this->filetime);
							array_unshift($this->attachment_id_list, '0');
							array_unshift($this->attachment_thumbnail_list, $this->thumbnail);

							$this->file_comment = '';
							$this->post_attach = FALSE;
						}
					}
					else
					{
						$error = TRUE;
						if(!empty($error_msg))
						{
							$error_msg .= '<br />';
						}
						$error_msg .= sprintf($lang['TOO_MANY_ATTACHMENTS'], intval($max_attachments));
					}
				}
			}
		}

		if ($preview || $refresh || $error)
		{
			$delete_attachment = ( isset($_POST['del_attachment']) ) ? TRUE : FALSE;
			$delete_thumbnail = (isset($_POST['del_thumbnail'])) ? TRUE : FALSE;

			$add_attachment = (isset($_POST['add_attachment'])) ? TRUE : FALSE;
			$edit_attachment = (isset($_POST['edit_comment'])) ? TRUE : FALSE;
			$update_attachment = (isset($_POST['update_attachment']) ) ? TRUE : FALSE;

			// Perform actions on temporary attachments
			if ($delete_attachment || $delete_thumbnail)
			{
				// store old values
				$actual_id_list				= get_var('attach_id_list', array(0));
				$actual_comment_list		= get_var('comment_list', array(''));
				$actual_filename_list		= get_var('filename_list', array(''));
				$actual_extension_list		= get_var('extension_list', array(''));
				$actual_mimetype_list		= get_var('mimetype_list', array(''));
				$actual_filesize_list		= get_var('filesize_list', array(0));
				$actual_filetime_list		= get_var('filetime_list', array(0));

				$actual_list				= get_var('attachment_list', array(''));
				$actual_thumbnail_list		= get_var('attach_thumbnail_list', array(0));

				// clean values
				$this->attachment_list = array();
				$this->attachment_comment_list = array();
				$this->attachment_filename_list = array();
				$this->attachment_extension_list = array();
				$this->attachment_mimetype_list = array();
				$this->attachment_filesize_list = array();
				$this->attachment_filetime_list = array();
				$this->attachment_id_list = array();
				$this->attachment_thumbnail_list = array();

				// restore values :)
				if (isset($_POST['attachment_list']))
				{
					for ($i = 0; $i < sizeof($actual_list); $i++)
					{
						$restore = FALSE;
						$del_thumb = FALSE;

						if ($delete_thumbnail)
						{
							if ( !isset($_POST['del_thumbnail'][$actual_list[$i]]) )
							{
								$restore = TRUE;
							}
							else
							{
								$del_thumb = TRUE;
							}
						}
						if ( $delete_attachment )
						{
							if ( !isset($_POST['del_attachment'][$actual_list[$i]]) )
							{
								$restore = TRUE;
							}
						}

						if ( $restore )
						{
							$this->attachment_list[] = $actual_list[$i];
							$this->attachment_comment_list[] = $actual_comment_list[$i];
							$this->attachment_filename_list[] = $actual_filename_list[$i];
							$this->attachment_extension_list[] = $actual_extension_list[$i];
							$this->attachment_mimetype_list[] = $actual_mimetype_list[$i];
							$this->attachment_filesize_list[] = $actual_filesize_list[$i];
							$this->attachment_filetime_list[] = $actual_filetime_list[$i];
							$this->attachment_id_list[] = $actual_id_list[$i];
							$this->attachment_thumbnail_list[] = $actual_thumbnail_list[$i];
						}
						else if (!$del_thumb)
						{
							// delete selected attachment
							if ($actual_id_list[$i] == '0' )
							{
								unlink_attach($actual_list[$i]);

								if ($actual_thumbnail_list[$i] == 1)
								{
									unlink_attach($actual_list[$i], MODE_THUMBNAIL);
								}
							}
							else
							{
								delete_attachment($post_id, $actual_id_list[$i], $this->page);
							}
						}
						else if ($del_thumb)
						{
							// delete selected thumbnail
							$this->attachment_list[] = $actual_list[$i];
							$this->attachment_comment_list[] = $actual_comment_list[$i];
							$this->attachment_filename_list[] = $actual_filename_list[$i];
							$this->attachment_extension_list[] = $actual_extension_list[$i];
							$this->attachment_mimetype_list[] = $actual_mimetype_list[$i];
							$this->attachment_filesize_list[] = $actual_filesize_list[$i];
							$this->attachment_filetime_list[] = $actual_filetime_list[$i];
							$this->attachment_id_list[] = $actual_id_list[$i];
							$this->attachment_thumbnail_list[] = 0;

							if ($actual_id_list[$i] == 0)
							{
								unlink_attach($actual_list[$i], MODE_THUMBNAIL);
							}
							else
							{
								$sql = 'UPDATE ' . BB_ATTACHMENTS_DESC . ' SET thumbnail = 0 WHERE attach_id = ' . (int) $actual_id_list[$i];

								if (!(DB()->sql_query($sql)))
								{
									bb_die('Unable to update ' . BB_ATTACHMENTS_DESC);
								}
							}
						}
					}
				}
			}
			else if ($edit_attachment || $update_attachment || $add_attachment || $preview)
			{
				if ($edit_attachment)
				{
					$actual_comment_list = get_var('comment_list', array(''));

					$this->attachment_comment_list = array();

					for ($i = 0; $i < sizeof($this->attachment_list); $i++)
					{
						$this->attachment_comment_list[$i] = $actual_comment_list[$i];
					}
				}

				if ($update_attachment)
				{
					if ($this->filename == '')
					{
						$error = TRUE;
						if(!empty($error_msg))
						{
							$error_msg .= '<br />';
						}
						$error_msg .= $lang['ERROR_EMPTY_ADD_ATTACHBOX'];
					}

					$this->upload_attachment($this->page);

					if (!$error)
					{
						$actual_list = get_var('attachment_list', array(''));
						$actual_id_list = get_var('attach_id_list', array(0));

						$attachment_id = 0;
						$actual_element = 0;

						for ($i = 0; $i < sizeof($actual_id_list); $i++)
						{
							if (isset($_POST['update_attachment'][$actual_id_list[$i]]))
							{
								$attachment_id = intval($actual_id_list[$i]);
								$actual_element = $i;
							}
						}

						// Get current informations to delete the Old Attachment
						$sql = 'SELECT physical_filename, comment, thumbnail
							FROM ' . BB_ATTACHMENTS_DESC . '
							WHERE attach_id = ' . (int) $attachment_id;

						if (!($result = DB()->sql_query($sql)))
						{
							bb_die('Unable to select old attachment entry');
						}

						if (DB()->num_rows($result) != 1)
						{
							$error = TRUE;
							if(!empty($error_msg))
							{
								$error_msg .= '<br />';
							}
							$error_msg .= $lang['ERROR_MISSING_OLD_ENTRY'];
						}

						$row = DB()->sql_fetchrow($result);
						DB()->sql_freeresult($result);

						$comment = (trim($this->file_comment) == '') ? trim($row['comment']) : trim($this->file_comment);

						// Update Entry
						$sql_ary = array(
							'physical_filename'		=> (string) basename($this->attach_filename),
							'real_filename'			=> (string) basename($this->filename),
							'comment'				=> (string) $comment,
							'extension'				=> (string) strtolower($this->extension),
							'mimetype'				=> (string) strtolower($this->type),
							'filesize'				=> (int) $this->filesize,
							'filetime'				=> (int) $this->filetime,
							'thumbnail'				=> (int) $this->thumbnail
						);

						$sql = 'UPDATE ' . BB_ATTACHMENTS_DESC . ' SET ' . attach_mod_sql_build_array('UPDATE', $sql_ary) . '
							WHERE attach_id = ' . (int) $attachment_id;

						if (!(DB()->sql_query($sql)))
						{
							bb_die('Unable to update the attachment');
						}

						// Delete the Old Attachment
						unlink_attach($row['physical_filename']);

						if (intval($row['thumbnail']) == 1)
						{
							unlink_attach($row['physical_filename'], MODE_THUMBNAIL);
						}

						//bt
						if ($this->attachment_extension_list[$actual_element] === TORRENT_EXT && $attachments[$actual_element]['tracker_status'])
						{
							include(INC_DIR .'functions_torrent.php');
							tracker_unregister($attachment_id);
						}
						//bt end

						// Make sure it is displayed
						$this->attachment_list[$actual_element] = $this->attach_filename;
						$this->attachment_comment_list[$actual_element] = $comment;
						$this->attachment_filename_list[$actual_element] = $this->filename;
						$this->attachment_extension_list[$actual_element] = $this->extension;
						$this->attachment_mimetype_list[$actual_element] = $this->type;
						$this->attachment_filesize_list[$actual_element] = $this->filesize;
						$this->attachment_filetime_list[$actual_element] = $this->filetime;
						$this->attachment_id_list[$actual_element] = $actual_id_list[$actual_element];
						$this->attachment_thumbnail_list[$actual_element] = $this->thumbnail;
						$this->file_comment = '';
					}
				}

				if (($add_attachment || $preview) && $this->filename != '')
				{
					if ($this->num_attachments < intval($max_attachments))
					{
						$this->upload_attachment($this->page);

						if (!$error)
						{
							array_unshift($this->attachment_list, $this->attach_filename);
							array_unshift($this->attachment_comment_list, $this->file_comment);
							array_unshift($this->attachment_filename_list, $this->filename);
							array_unshift($this->attachment_extension_list, $this->extension);
							array_unshift($this->attachment_mimetype_list, $this->type);
							array_unshift($this->attachment_filesize_list, $this->filesize);
							array_unshift($this->attachment_filetime_list, $this->filetime);
							array_unshift($this->attachment_id_list, '0');
							array_unshift($this->attachment_thumbnail_list, $this->thumbnail);

							$this->file_comment = '';
						}
					}
					else
					{
						$error = TRUE;
						if(!empty($error_msg))
						{
							$error_msg .= '<br />';
						}
						$error_msg .= sprintf($lang['TOO_MANY_ATTACHMENTS'], intval($max_attachments));
					}
				}
			}
		}

		return TRUE;
	}

	/**
	* Basic Insert Attachment Handling for all Message Types
	*/
	function do_insert_attachment($mode, $message_type, $message_id)
	{
		global $upload_dir;

		if (intval($message_id) < 0)
		{
			return FALSE;
		}

		global $post_info, $userdata;

		$post_id = (int) $message_id;
		$user_id_1 = (isset($post_info['poster_id'])) ? (int) $post_info['poster_id'] : 0;

		if (!$user_id_1)
		{
			$user_id_1 = (int) $userdata['user_id'];
		}

		if ($mode == 'attach_list')
		{
			for ($i = 0; $i < sizeof($this->attachment_list); $i++)
			{
				if ($this->attachment_id_list[$i])
				{
					//bt
					if ($this->attachment_extension_list[$i] === TORRENT_EXT && !defined('TORRENT_ATTACH_ID'))
					{
						define('TORRENT_ATTACH_ID', $this->attachment_id_list[$i]);
					}
					//bt end

					// update entry in db if attachment already stored in db and filespace
					$sql = 'UPDATE ' . BB_ATTACHMENTS_DESC . "
						SET comment = '" . @attach_mod_sql_escape($this->attachment_comment_list[$i]) . "'
						WHERE attach_id = " . $this->attachment_id_list[$i];

					if (!(DB()->sql_query($sql)))
					{
						bb_die('Unable to update the file comment');
					}
				}
				else
				{
					if (empty($this->attachment_mimetype_list[$i]) && $this->attachment_extension_list[$i] === TORRENT_EXT)
					{
						$this->attachment_mimetype_list[$i] = 'application/x-bittorrent';
					}

					// insert attachment into db
					$sql_ary = array(
						'physical_filename'		=> (string) basename($this->attachment_list[$i]),
						'real_filename'			=> (string) basename($this->attachment_filename_list[$i]),
						'comment'				=> (string) @$this->attachment_comment_list[$i],
						'extension'				=> (string) strtolower($this->attachment_extension_list[$i]),
						'mimetype'				=> (string) strtolower($this->attachment_mimetype_list[$i]),
						'filesize'				=> (int) $this->attachment_filesize_list[$i],
						'filetime'				=> (int) $this->attachment_filetime_list[$i],
						'thumbnail'				=> (int) $this->attachment_thumbnail_list[$i]
					);

					$sql = 'INSERT INTO ' . BB_ATTACHMENTS_DESC . ' ' . attach_mod_sql_build_array('INSERT', $sql_ary);

					if (!(DB()->sql_query($sql)))
					{
						bb_die('Could not store Attachment.<br />Your '. $message_type .' has been stored');
					}

					$attach_id = DB()->sql_nextid();

					//bt
					if ($this->attachment_extension_list[$i] === TORRENT_EXT && !defined('TORRENT_ATTACH_ID'))
					{
						define('TORRENT_ATTACH_ID', $attach_id);
					}
					//bt end

					$sql_ary = array(
						'attach_id'		=> (int) $attach_id,
						'post_id'		=> (int) $post_id,
						'user_id_1'		=> (int) $user_id_1,
					);

					$sql = 'INSERT INTO ' . BB_ATTACHMENTS . ' ' . attach_mod_sql_build_array('INSERT', $sql_ary);

					if (!(DB()->sql_query($sql)))
					{
						bb_die('Could not store Attachment.<br />Your '. $message_type .' has been stored');
					}
				}
			}

			return TRUE;
		}

		if ($mode == 'last_attachment')
		{
			if ($this->post_attach && !isset($_POST['update_attachment']))
			{
				// insert attachment into db, here the user submited it directly
				$sql_ary = array(
					'physical_filename'		=> (string) basename($this->attach_filename),
					'real_filename'			=> (string) basename($this->filename),
					'comment'				=> (string) $this->file_comment,
					'extension'				=> (string) strtolower($this->extension),
					'mimetype'				=> (string) strtolower($this->type),
					'filesize'				=> (int) $this->filesize,
					'filetime'				=> (int) $this->filetime,
					'thumbnail'				=> (int) $this->thumbnail
				);

				$sql = 'INSERT INTO ' . BB_ATTACHMENTS_DESC . ' ' . attach_mod_sql_build_array('INSERT', $sql_ary);

				// Inform the user that his post has been created, but nothing is attached
				if (!(DB()->sql_query($sql)))
				{
					bb_die('Could not store Attachment.<br />Your '. $message_type .' has been stored');
				}

				$attach_id = DB()->sql_nextid();

				$sql_ary = array(
					'attach_id'		=> (int) $attach_id,
					'post_id'		=> (int) $post_id,
					'user_id_1'		=> (int) $user_id_1,
				);

				$sql = 'INSERT INTO ' . BB_ATTACHMENTS . ' ' . attach_mod_sql_build_array('INSERT', $sql_ary);

				if (!(DB()->sql_query($sql)))
				{
					bb_die('Could not store Attachment.<br />Your '. $message_type .' has been stored');
				}
			}
		}
	}

	/**
	* Attachment Mod entry switch/output (intern)
	* @private
	*/
	function display_attachment_bodies()
	{
		global $attach_config, $is_auth, $lang, $template, $upload_dir, $forum_id;

		// Choose what to display
		$value_add = $value_posted = 0;

		$this->add_attachment_body = 1;
		$this->posted_attachments_body = 1;

		$s_hidden = '<input type="hidden" name="add_attachment_body" value="' . $value_add . '" />';
		$s_hidden .= '<input type="hidden" name="posted_attachments_body" value="' . $value_posted . '" />';

		$template->assign_vars(array(
			'ADD_ATTACH_HIDDEN_FIELDS' => $s_hidden,
		));

		$attachments = array();

		if (sizeof($this->attachment_list) > 0)
		{
			$hidden = '';
			for ($i = 0; $i < sizeof($this->attachment_list); $i++)
			{
				$hidden .= '<input type="hidden" name="attachment_list[]" value="' . $this->attachment_list[$i] . '" />';
				$hidden .= '<input type="hidden" name="filename_list[]" value="' . $this->attachment_filename_list[$i] . '" />';
				$hidden .= '<input type="hidden" name="extension_list[]" value="' . $this->attachment_extension_list[$i] . '" />';
				$hidden .= '<input type="hidden" name="mimetype_list[]" value="' . $this->attachment_mimetype_list[$i] . '" />';
				$hidden .= '<input type="hidden" name="filesize_list[]" value="' . @$this->attachment_filesize_list[$i] . '" />';
				$hidden .= '<input type="hidden" name="filetime_list[]" value="' . @$this->attachment_filetime_list[$i] . '" />';
				$hidden .= '<input type="hidden" name="attach_id_list[]" value="' . @$this->attachment_id_list[$i] . '" />';
				$hidden .= '<input type="hidden" name="attach_thumbnail_list[]" value="' . @$this->attachment_thumbnail_list[$i] . '" />';

				if (!$this->posted_attachments_body || sizeof($this->attachment_list) == 0)
				{
					$hidden .= '<input type="hidden" name="comment_list[]" value="' . $this->attachment_comment_list[$i] . '" />';
				}
			}
			$template->assign_var('POSTED_ATTACHMENTS_HIDDEN_FIELDS', $hidden);
		}

		if ($this->add_attachment_body)
		{
			$template->assign_vars(array(
				'TPL_ADD_ATTACHMENT' => true,
				'FILE_COMMENT'       => htmlspecialchars($this->file_comment),
				'FILESIZE'           => $attach_config['max_filesize'],
				'FILENAME'           => htmlspecialchars($this->filename),
				'S_FORM_ENCTYPE'     => 'enctype="multipart/form-data"',
			));
		}

		if ($this->posted_attachments_body && sizeof($this->attachment_list) > 0)
		{
			$template->assign_vars(array(
				'TPL_POSTED_ATTACHMENTS' => true,
			));

			for ($i = 0; $i < sizeof($this->attachment_list); $i++)
			{
				if (@$this->attachment_id_list[$i] == 0)
				{
					$download_link = $upload_dir . '/' . basename($this->attachment_list[$i]);
				}
				else
				{
					$download_link = BB_ROOT . DOWNLOAD_URL . $this->attachment_id_list[$i];
				}

				$template->assign_block_vars('attach_row', array(
					'FILE_NAME'         => @htmlspecialchars($this->attachment_filename_list[$i]),
					'ATTACH_FILENAME'   => @$this->attachment_list[$i],
					'FILE_COMMENT'      => @htmlspecialchars($this->attachment_comment_list[$i]),
					'ATTACH_ID'         => @$this->attachment_id_list[$i],
					'U_VIEW_ATTACHMENT' => $download_link,
				));

				// Thumbnail there ? And is the User Admin or Mod ? Then present the 'Delete Thumbnail' Button
				if (@intval($this->attachment_thumbnail_list[$i]) == 1 && ((isset($is_auth['auth_mod']) && $is_auth['auth_mod']) || IS_ADMIN))
				{
					$template->assign_block_vars('attach_row.switch_thumbnail', array());
				}

				if (@$this->attachment_id_list[$i])
				{
					$template->assign_block_vars('attach_row.switch_update_attachment', array());
				}
			}
		}

		$template->assign_var('ATTACHBOX');
	}

	/**
	* Upload an Attachment to Filespace (intern)
	*/
	function upload_attachment()
	{
		global $error, $error_msg, $lang, $attach_config, $userdata, $upload_dir, $forum_id;

		$this->post_attach = ($this->filename != '') ? TRUE : FALSE;

		if ($this->post_attach)
		{
			$r_file = trim(basename($this->filename));
			$file = $_FILES['fileupload']['tmp_name'];
			$this->type = $_FILES['fileupload']['type'];

			if (isset($_FILES['fileupload']['size']) && $_FILES['fileupload']['size'] == 0)
			{
				bb_die('Tried to upload empty file');
			}

			$this->type = strtolower($this->type);
			$this->extension = strtolower(get_extension($this->filename));

			$this->filesize = @filesize($file);
			$this->filesize = intval($this->filesize);

			$sql = 'SELECT g.allow_group, g.max_filesize, g.cat_id, g.forum_permissions
				FROM ' . BB_EXTENSION_GROUPS . ' g, ' . BB_EXTENSIONS . " e
				WHERE g.group_id = e.group_id
					AND e.extension = '" . attach_mod_sql_escape($this->extension) . "'
				LIMIT 1";

			if (!($result = DB()->sql_query($sql)))
			{
				bb_die('Could not query extensions');
			}

			$row = DB()->sql_fetchrow($result);
			DB()->sql_freeresult($result);

			$allowed_filesize = ($row['max_filesize']) ? $row['max_filesize'] : $attach_config['max_filesize'];
			$cat_id = intval($row['cat_id']);
			$auth_cache = trim($row['forum_permissions']);

			// check Filename
			if (preg_match("#[\\/:*?\"<>|]#i", $this->filename))
			{
				$error = TRUE;
				if(!empty($error_msg))
				{
					$error_msg .= '<br />';
				}
				$error_msg .= sprintf($lang['INVALID_FILENAME'], htmlspecialchars($this->filename));
			}

			// check php upload-size
			if (!$error && $file == 'none')
			{
				$error = TRUE;
				if(!empty($error_msg))
				{
					$error_msg .= '<br />';
				}
				$ini_val = 'ini_get';

				$max_size = @$ini_val('upload_max_filesize');

				if ($max_size == '')
				{
					$error_msg .= $lang['ATTACHMENT_PHP_SIZE_NA'];
				}
				else
				{
					$error_msg .= sprintf($lang['ATTACHMENT_PHP_SIZE_OVERRUN'], $max_size);
				}
			}

			// Check Extension
			if (!$error && intval($row['allow_group']) == 0)
			{
				$error = TRUE;
				if(!empty($error_msg))
				{
					$error_msg .= '<br />';
				}
				$error_msg .= sprintf($lang['DISALLOWED_EXTENSION'], htmlspecialchars($this->extension));
			}

			// Check Forum Permissions
			if (!$error && !IS_ADMIN && !is_forum_authed($auth_cache, $forum_id) && trim($auth_cache) != '')
			{
				$error = TRUE;
				if(!empty($error_msg))
				{
					$error_msg .= '<br />';
				}
				$error_msg .= sprintf($lang['DISALLOWED_EXTENSION_WITHIN_FORUM'], htmlspecialchars($this->extension));
			}

			//bt
			// Check if user can post torrent
			global $post_data;

			if (!$error && $this->extension === TORRENT_EXT && !$post_data['first_post'])
			{
				$error = TRUE;
				if (!empty($error_msg))
				{
					$error_msg .= '<br />';
				}
				$error_msg .= $lang['ALLOWED_ONLY_1ST_POST_ATTACH'];
			}
			//bt end

			// Upload File

			$this->thumbnail = 0;

			if (!$error)
			{
				//
				// Prepare Values
				$this->filetime = TIMENOW;

				$this->filename = $r_file;

				// physical filename
				//$this->attach_filename = strtolower($this->filename);
				$this->attach_filename = $this->filename;

				//bt
				if (FILENAME_CRYPTIC)
				{
					$this->attach_filename = make_rand_str(FILENAME_CRYPTIC_LENGTH);
				}
				else
				{ // original
					$this->attach_filename = html_entity_decode(trim(stripslashes($this->attach_filename)));
					$this->attach_filename = delete_extension($this->attach_filename);
					$this->attach_filename = str_replace(array(' ','-'), array('_','_'), $this->attach_filename);
					$this->attach_filename = str_replace('__', '_', $this->attach_filename);
					$this->attach_filename = str_replace(array(',', '.', '!', '?', 'ь', 'Ь', 'ц', 'Ц', 'д', 'Д', ';', ':', '@', "'", '"', '&'), array('', '', '', '', 'ue', 'ue', 'oe', 'oe', 'ae', 'ae', '', '', '', '', '', 'and'), $this->attach_filename);
					$this->attach_filename = str_replace(array('$', 'Я', '>','<','§','%','=','/','(',')','#','*','+',"\\",'{','}','[',']'), array('dollar', 'ss','greater','lower','paragraph','percent','equal','','','','','','','','','','',''), $this->attach_filename);
					// Remove non-latin characters
					$this->attach_filename = preg_replace("/([\xC2\xC3])([\x80-\xBF])/e", "chr(ord('\\1')<<6&0xC0|ord('\\2')&0x3F)", $this->attach_filename);
					$this->attach_filename = rawurlencode($this->attach_filename);
					$this->attach_filename = preg_replace("/(%[0-9A-F]{1,2})/i", '', $this->attach_filename);
					$this->attach_filename = trim($this->attach_filename);
				}
				$this->attach_filename = str_replace(array('&amp;','&',' '), '_', $this->attach_filename);
				$this->attach_filename = str_replace('php', '_php_', $this->attach_filename);
				$this->attach_filename = substr(trim($this->attach_filename), 0, FILENAME_MAX_LENGTH);

				for ($i=0, $max_try=5; $i <= $max_try; $i++)
				{
					$fn_prefix = make_rand_str(FILENAME_PREFIX_LENGTH) .'_';
					$new_physical_filename = clean_filename($fn_prefix . $this->attach_filename);

					if (!physical_filename_already_stored($new_physical_filename))
					{
						break;
					}
					if ($i == $max_try)
					{
						bb_die('Could not create filename for attachment');
					}
				}
				$this->attach_filename = $new_physical_filename;

				// Do we have to create a thumbnail ?
				if ($cat_id == IMAGE_CAT && intval($attach_config['img_create_thumbnail']))
				{
					$this->thumbnail = 1;
				}
			}

			if ($error)
			{
				$this->post_attach = FALSE;
				return;
			}

			// Upload Attachment
			if (!$error)
			{
				// Descide the Upload method
				$ini_val = 'ini_get';

				$safe_mode = @$ini_val('safe_mode');

				if (@$ini_val('open_basedir'))
				{
					$upload_mode = 'move';
				}
				else if ( @$ini_val('safe_mode') )
				{
					$upload_mode = 'move';
				}
				else
				{
					$upload_mode = 'copy';
				}

				// Ok, upload the Attachment
				if (!$error)
				{
					$this->move_uploaded_attachment($upload_mode, $file);
				}
			}

			// Now, check filesize parameters
			if (!$error)
			{
				if (!$this->filesize)
				{
					$this->filesize = intval(@filesize($upload_dir . '/' . $this->attach_filename));
				}
			}

			// Check Image Size, if it's an image
			if (!$error && !IS_ADMIN && $cat_id == IMAGE_CAT)
			{
				list($width, $height) = image_getdimension($upload_dir . '/' . $this->attach_filename);

				if ($width != 0 && $height != 0 && intval($attach_config['img_max_width']) != 0 && intval($attach_config['img_max_height']) != 0)
				{
					if ($width > intval($attach_config['img_max_width']) || $height > intval($attach_config['img_max_height']))
					{
						$error = TRUE;
						if(!empty($error_msg))
						{
							$error_msg .= '<br />';
						}
						$error_msg .= sprintf($lang['ERROR_IMAGESIZE'], intval($attach_config['img_max_width']), intval($attach_config['img_max_height']));
					}
				}
			}

			// check Filesize
			if (!$error && $allowed_filesize != 0 && $this->filesize > $allowed_filesize && !(IS_ADMIN || IS_MOD || IS_GROUP_MEMBER))
			{
				$allowed_filesize = humn_size($allowed_filesize);

				$error = TRUE;
				if(!empty($error_msg))
				{
					$error_msg .= '<br />';
				}
				$error_msg .= sprintf($lang['ATTACHMENT_TOO_BIG'], $allowed_filesize);
			}

			// Check our complete quota
			if ($attach_config['attachment_quota'])
			{
				$sql = 'SELECT sum(filesize) as total FROM ' . BB_ATTACHMENTS_DESC;

				if (!($result = DB()->sql_query($sql)))
				{
					bb_die('Could not query total filesize #1');
				}

				$row = DB()->sql_fetchrow($result);
				DB()->sql_freeresult($result);

				$total_filesize = $row['total'];

				if (($total_filesize + $this->filesize) > $attach_config['attachment_quota'])
				{
					$error = TRUE;
					if(!empty($error_msg))
					{
						$error_msg .= '<br />';
					}
					$error_msg .= $lang['ATTACH_QUOTA_REACHED'];
				}

			}

			$this->get_quota_limits($userdata);

			// Check our user quota
			if ($attach_config['upload_filesize_limit'])
			{
				$sql = 'SELECT attach_id
					FROM ' . BB_ATTACHMENTS . '
					WHERE user_id_1 = ' . (int) $userdata['user_id'] . '
					GROUP BY attach_id';

				if (!($result = DB()->sql_query($sql)))
				{
					bb_die('Could not query attachments');
				}

				$attach_ids = DB()->sql_fetchrowset($result);
				$num_attach_ids = DB()->num_rows($result);
				DB()->sql_freeresult($result);

				$attach_id = array();

				for ($i = 0; $i < $num_attach_ids; $i++)
				{
					$attach_id[] = intval($attach_ids[$i]['attach_id']);
				}

				if ($num_attach_ids > 0)
				{
					// Now get the total filesize
					$sql = 'SELECT sum(filesize) as total
						FROM ' . BB_ATTACHMENTS_DESC . '
						WHERE attach_id IN (' . implode(', ', $attach_id) . ')';

					if (!($result = DB()->sql_query($sql)))
					{
						bb_die('Could not query total filesize #2');
					}

					$row = DB()->sql_fetchrow($result);
					DB()->sql_freeresult($result);
					$total_filesize = $row['total'];
				}
				else
				{
					$total_filesize = 0;
				}

				if (($total_filesize + $this->filesize) > $attach_config['upload_filesize_limit'])
				{
					$upload_filesize_limit = $attach_config['upload_filesize_limit'];
					$size_lang = ($upload_filesize_limit >= 1048576) ? $lang['MB'] : ( ($upload_filesize_limit >= 1024) ? $lang['KB'] : $lang['BYTES'] );

					if ($upload_filesize_limit >= 1048576)
					{
						$upload_filesize_limit = round($upload_filesize_limit / 1048576 * 100) / 100;
					}
					else if($upload_filesize_limit >= 1024)
					{
						$upload_filesize_limit = round($upload_filesize_limit / 1024 * 100) / 100;
					}

					$error = TRUE;
					if(!empty($error_msg))
					{
						$error_msg .= '<br />';
					}
					$error_msg .= sprintf($lang['USER_UPLOAD_QUOTA_REACHED'], $upload_filesize_limit, $size_lang);
				}
			}

			if ($error)
			{
				unlink_attach($this->attach_filename);
				unlink_attach($this->attach_filename, MODE_THUMBNAIL);
				$this->post_attach = FALSE;
			}
		}
	}

	// Copy the temporary attachment to the right location (copy, move_uploaded_file)
	function move_uploaded_attachment($upload_mode, $file)
	{
		global $error, $error_msg, $lang, $upload_dir;

		if (!is_uploaded_file($file))
		{
			bb_die('Unable to upload file. The given source has not been uploaded');
		}

		switch ($upload_mode)
		{
			case 'copy':

				if (!@copy($file, $upload_dir . '/' . basename($this->attach_filename)))
				{
					if (!@move_uploaded_file($file, $upload_dir . '/' . basename($this->attach_filename)))
					{
						$error = TRUE;
						if(!empty($error_msg))
						{
							$error_msg .= '<br />';
						}
						$error_msg .= sprintf($lang['GENERAL_UPLOAD_ERROR'], './' . $upload_dir . '/' . $this->attach_filename);
						return;
					}
				}
				@chmod($upload_dir . '/' . basename($this->attach_filename), 0666);

			break;

			case 'move':

				if (!@move_uploaded_file($file, $upload_dir . '/' . basename($this->attach_filename)))
				{
					if (!@copy($file, $upload_dir . '/' . basename($this->attach_filename)))
					{
						$error = TRUE;
						if(!empty($error_msg))
						{
							$error_msg .= '<br />';
						}
						$error_msg .= sprintf($lang['GENERAL_UPLOAD_ERROR'], './' . $upload_dir . '/' . $this->attach_filename);
						return;
					}
				}
				@chmod($upload_dir . '/' . $this->attach_filename, 0666);

			break;
		}

		if (!$error && $this->thumbnail == 1)
		{
			$source = $upload_dir . '/' . basename($this->attach_filename);
			$dest_file = amod_realpath($upload_dir);
			$dest_file .= '/' . THUMB_DIR . '/t_' . basename($this->attach_filename);

			if (!create_thumbnail($source, $dest_file, $this->type))
			{
				if (!$file || !create_thumbnail($file, $dest_file, $this->type))
				{
					$this->thumbnail = 0;
				}
			}
		}
	}
}

/**
* @package attachment_mod
* Attachment posting
*/
class attach_posting extends attach_parent
{
	/**
	* Constructor
	*/
	function attach_posting()
	{
		$this->attach_parent();
		$this->page = 0;
	}

	/**
	* Insert an Attachment into a Post (this is the second function called from posting.php)
	*/
	function insert_attachment($post_id)
	{
		global $is_auth, $mode;

		// Insert Attachment ?
		if (!empty($post_id) && ($mode == 'newtopic' || $mode == 'reply' || $mode == 'editpost') && $is_auth['auth_attachments'])
		{
			$this->do_insert_attachment('attach_list', 'post', $post_id);
			$this->do_insert_attachment('last_attachment', 'post', $post_id);

			if ((sizeof($this->attachment_list) > 0 || $this->post_attach) && !isset($_POST['update_attachment']))
			{
				$sql = 'UPDATE ' . BB_POSTS . ' SET post_attachment = 1 WHERE post_id = ' . (int) $post_id;

				if (!(DB()->sql_query($sql)))
				{
					bb_die('Unable to update posts table');
				}

				$sql = 'SELECT topic_id FROM ' . BB_POSTS . ' WHERE post_id = ' . (int) $post_id;

				if (!($result = DB()->sql_query($sql)))
				{
					bb_die('Unable to select posts table');
				}

				$row = DB()->sql_fetchrow($result);
				DB()->sql_freeresult($result);

				$sql = 'UPDATE ' . BB_TOPICS . ' SET topic_attachment = 1 WHERE topic_id = ' . (int) $row['topic_id'];

				if (!(DB()->sql_query($sql)))
				{
					bb_die('Unable to update topics table');
				}
			}
		}
	}

	/**
	* Handle Attachments (Add/Delete/Edit/Show) - This is the first function called from every message handler
	*/
	function posting_attachment_mod()
	{
		global $mode, $confirm, $is_auth, $post_id, $delete, $refresh;

		if (!$refresh)
		{
			$add_attachment_box = (!empty($_POST['add_attachment_box'])) ? TRUE : FALSE;
			$posted_attachments_box = (!empty($_POST['posted_attachments_box'])) ? TRUE : FALSE;

			$refresh = $add_attachment_box || $posted_attachments_box;
		}

		// Choose what to display
		$result = $this->handle_attachments($mode);

		if ($result === false)
		{
			return;
		}

		if ($confirm && ($delete || $mode == 'delete' || $mode == 'editpost') && ($is_auth['auth_delete'] || $is_auth['auth_mod']))
		{
			if ($post_id)
			{
				delete_attachment($post_id);
			}
		}

		$this->display_attachment_bodies();
	}

}

/**
* Entry Point
*/
function execute_posting_attachment_handling()
{
	global $attachment_mod;

	$attachment_mod['posting'] = new attach_posting();
	$attachment_mod['posting']->posting_attachment_mod();
}