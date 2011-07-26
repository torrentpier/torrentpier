<?php

/**
* All Attachment Functions processing the Deletion Process
*/

/**
* Delete Attachment(s) from post(s) (intern)
*/
function delete_attachment($post_id_array = 0, $attach_id_array = 0, $page = 0, $user_id = 0)
{
	global $bb_cfg;

	// Generate Array, if it's not an array
	if ($post_id_array === 0 && $attach_id_array === 0 && $page === 0)
	{
		return;
	}

	if ($post_id_array === 0 && $attach_id_array !== 0)
	{
		$post_id_array = array();

		if (!is_array($attach_id_array))
		{
			if (strstr($attach_id_array, ', '))
			{
				$attach_id_array = explode(', ', $attach_id_array);
			}
			else if (strstr($attach_id_array, ','))
			{
				$attach_id_array = explode(',', $attach_id_array);
			}
			else
			{
				$attach_id = intval($attach_id_array);
				$attach_id_array = array();
				$attach_id_array[] = $attach_id;
			}
		}

		// Get the post_ids to fill the array
		$p_id = 'post_id';

		$sql = "SELECT $p_id
			FROM " . BB_ATTACHMENTS . '
				WHERE attach_id IN (' . implode(', ', $attach_id_array) . ")
			GROUP BY $p_id";

		if ( !($result = DB()->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not select ids', '', __LINE__, __FILE__, $sql);
		}

		$num_post_list = DB()->num_rows($result);

		if ($num_post_list == 0)
		{
			DB()->sql_freeresult($result);
			return;
		}

		while ($row = DB()->sql_fetchrow($result))
		{
			$post_id_array[] = intval($row[$p_id]);
		}
		DB()->sql_freeresult($result);
	}

	if (!is_array($post_id_array))
	{
		if (trim($post_id_array) == '')
		{
			return;
		}

		if (strstr($post_id_array, ', '))
		{
			$post_id_array = explode(', ', $post_id_array);
		}
		else if (strstr($post_id_array, ','))
		{
			$post_id_array = explode(',', $post_id_array);
		}
		else
		{
			$post_id = intval($post_id_array);

			$post_id_array = array();
			$post_id_array[] = $post_id;
		}
	}

	if (!sizeof($post_id_array))
	{
		return;
	}

	// First of all, determine the post id and attach_id
	if ($attach_id_array === 0)
	{
		$attach_id_array = array();

		// Get the attach_ids to fill the array
		$whereclause = 'WHERE post_id IN (' . implode(', ', $post_id_array) . ')';

		$sql = 'SELECT attach_id
			FROM ' . BB_ATTACHMENTS . " $whereclause
			GROUP BY attach_id";

		if ( !($result = DB()->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not select Attachment Ids', '', __LINE__, __FILE__, $sql);
		}

		$num_attach_list = DB()->num_rows($result);

		if ($num_attach_list == 0)
		{
			DB()->sql_freeresult($result);
			return;
		}

		while ($row = DB()->sql_fetchrow($result))
		{
			$attach_id_array[] = (int) $row['attach_id'];
		}
		DB()->sql_freeresult($result);
	}

	if (!is_array($attach_id_array))
	{
		if (strstr($attach_id_array, ', '))
		{
			$attach_id_array = explode(', ', $attach_id_array);
		}
		else if (strstr($attach_id_array, ','))
		{
			$attach_id_array = explode(',', $attach_id_array);
		}
		else
		{
			$attach_id = intval($attach_id_array);

			$attach_id_array = array();
			$attach_id_array[] = $attach_id;
		}
	}

	if (!sizeof($attach_id_array))
	{
		return;
	}

	$sql_id = 'post_id';

	if (sizeof($post_id_array) && sizeof($attach_id_array))
	{
		$sql = 'DELETE FROM ' . BB_ATTACHMENTS . '
			WHERE attach_id IN (' . implode(', ', $attach_id_array) . ")
				AND $sql_id IN (" . implode(', ', $post_id_array) . ')';

		if ( !(DB()->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, $lang['ERROR_DELETED_ATTACHMENTS'], '', __LINE__, __FILE__, $sql);
		}

		//bt
		if ($sql_id == 'post_id')
		{
			// XBTT
			if($bb_cfg['announce_type'] == 'xbt')
			{
				$sql = "INSERT INTO ". BB_BT_TORRENTS ."_del (topic_id, info_hash)
					SELECT topic_id, info_hash
					FROM ". BB_BT_TORRENTS ."
					WHERE attach_id IN(". implode(',', $attach_id_array) .") ON DUPLICATE KEY UPDATE is_del=1";
				if ( !(DB()->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, $lang['Error_deleted_attachments'], '', __LINE__, __FILE__, $sql);
				}
			}
			// XBTT END.

			$sql = "SELECT topic_id
				FROM ". BB_BT_TORRENTS ."
				WHERE attach_id IN(". implode(',', $attach_id_array) .")";

			if (!$result = DB()->sql_query($sql))
			{
				message_die(GENERAL_ERROR, $lang['ERROR_DELETED_ATTACHMENTS'], '', __LINE__, __FILE__, $sql);
			}

			$torrents_sql = array();

			while ($row = DB()->sql_fetchrow($result))
			{
				$torrents_sql[] = $row['topic_id'];
			}

			if ($torrents_sql = implode(',', $torrents_sql))
			{
				// Remove peers from tracker
				$sql = "DELETE FROM ". BB_BT_TRACKER ."
					WHERE topic_id IN($torrents_sql)";

				if (!DB()->sql_query($sql))
				{
					message_die(GENERAL_ERROR, 'Could not delete peers', '', __LINE__, __FILE__, $sql);
				}
			}
			// Delete torrents
			$sql = "DELETE FROM ". BB_BT_TORRENTS ."
				WHERE attach_id IN(". implode(',', $attach_id_array) .")";

			if (!DB()->sql_query($sql))
			{
				message_die(GENERAL_ERROR, $lang['ERROR_DELETED_ATTACHMENTS'], '', __LINE__, __FILE__, $sql);
			}
		}
		//bt end

		for ($i = 0; $i < sizeof($attach_id_array); $i++)
		{
			$sql = 'SELECT attach_id
				FROM ' . BB_ATTACHMENTS . '
						WHERE attach_id = ' . (int) $attach_id_array[$i];

			if ( !($result = DB()->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Could not select Attachment Ids', '', __LINE__, __FILE__, $sql);
			}

				$num_rows = DB()->num_rows($result);
				DB()->sql_freeresult($result);

				if ($num_rows == 0)
				{
					$sql = 'SELECT attach_id, physical_filename, thumbnail
						FROM ' . BB_ATTACHMENTS_DESC . '
							WHERE attach_id = ' . (int) $attach_id_array[$i];

					if ( !($result = DB()->sql_query($sql)) )
					{
						message_die(GENERAL_ERROR, 'Couldn\'t query attach description table', '', __LINE__, __FILE__, $sql);
					}
					$num_rows = DB()->num_rows($result);

					if ($num_rows != 0)
					{
						$num_attach = $num_rows;
						$attachments = DB()->sql_fetchrowset($result);
						DB()->sql_freeresult($result);

						// delete attachments
						for ($j = 0; $j < $num_attach; $j++)
						{
							unlink_attach($attachments[$j]['physical_filename']);

							if (intval($attachments[$j]['thumbnail']) == 1)
							{
								unlink_attach($attachments[$j]['physical_filename'], MODE_THUMBNAIL);
							}

							$sql = 'DELETE FROM ' . BB_ATTACHMENTS_DESC . '
								WHERE attach_id = ' . (int) $attachments[$j]['attach_id'];

							if ( !(DB()->sql_query($sql)) )
							{
								message_die(GENERAL_ERROR, $lang['ERROR_DELETED_ATTACHMENTS'], '', __LINE__, __FILE__, $sql);
							}
						}
					}
					else
					{
						DB()->sql_freeresult($result);
					}
				}
			}
		}

		// Now Sync the Topic/PM
		if (sizeof($post_id_array))
		{
			$sql = 'SELECT topic_id
			FROM ' . BB_POSTS . '
			WHERE post_id IN (' . implode(', ', $post_id_array) . ')
			GROUP BY topic_id';

		if ( !($result = DB()->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Couldn\'t select Topic ID', '', __LINE__, __FILE__, $sql);
		}

		while ($row = DB()->sql_fetchrow($result))
		{
			attachment_sync_topic($row['topic_id']);
		}
		DB()->sql_freeresult($result);
	}
}