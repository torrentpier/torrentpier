<?php

class report_privmsg extends report_module
{
	var $mode = 'reportprivmsg';
	var $duplicates = false;

	//
	// Constructor
	//
	function report_privmsg($id, $data, $lang)
	{
		$this->id = $id;
		$this->data = $data;
		$this->lang = $lang;
	}

	//
	// Synchronizing function
	//
	function sync($uninstall = false)
	{
		$sql = 'UPDATE ' . BB_PRIVMSGS . '
			SET privmsgs_reported = 0';
		if (!DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Could not reset privmsgs reported flag', '', __LINE__, __FILE__, $sql);
		}

		if (!$uninstall)
		{
			$sql = 'SELECT report_subject
				FROM ' . BB_REPORTS . '
				WHERE report_module_id = ' . $this->id . '
					AND report_status NOT IN(' . REPORT_CLEARED . ', ' . REPORT_DELETE . ')
				GROUP BY report_subject';
			if (!$result = DB()->sql_query($sql))
			{
				message_die(GENERAL_ERROR, 'Could not obtain open reports', '', __LINE__, __FILE__, $sql);
			}

			$open_ids = array();
			while ($row = DB()->sql_fetchrow($result))
			{
				$open_ids[] = $row['report_subject'];
			}
			DB()->sql_freeresult($result);

			if (!empty($open_ids))
			{
				$sql = 'UPDATE ' . BB_PRIVMSGS . '
					SET privmsgs_reported = 1
					WHERE privmsgs_id IN(' . implode(', ', $open_ids) . ')';
				if (!DB()->sql_query($sql))
				{
					message_die(GENERAL_ERROR, 'Could not sync privmsgs reported flag', '', __LINE__, __FILE__, $sql);
				}
			}
		}
	}

	//
	// Module action: Insert
	//
	function action_insert($report_subject, $report_id)
	{
		$sql = 'UPDATE ' . BB_PRIVMSGS . '
			SET privmsgs_reported = 1
			WHERE privmsgs_id = ' . (int) $report_subject;
		if (!DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Could not update privmsgs reported flag', '', __LINE__, __FILE__, $sql);
		}
	}

	//
	// Module action: Update status
	//
	function action_update_status($report_subjects, $report_status)
	{
		switch ($report_status)
		{
			case REPORT_CLEARED:
			case REPORT_DELETE:
				$this->action_delete($report_subjects);
			break;

			default:
				report_prepare_subjects($report_subjects, true);

				$sql = 'UPDATE ' . BB_PRIVMSGS . '
					SET privmsgs_reported = 1
					WHERE privmsgs_id IN(' . implode(', ', $report_subjects) . ')';
				if (!DB()->sql_query($sql))
				{
					message_die(GENERAL_ERROR, 'Could not update privmsgs reported flag', '', __LINE__, __FILE__, $sql);
				}
			break;
		}
	}

	//
	// Module action: Delete
	//
	function action_delete($report_subjects)
	{
		report_prepare_subjects($report_subjects, true);

		$sql = 'SELECT report_subject
			FROM ' . BB_REPORTS . '
			WHERE report_module_id = ' . $this->id . '
				AND report_id NOT IN(' . implode(', ', array_keys($report_subjects)) . ')
				AND report_subject IN(' . implode(', ', $report_subjects) . ')
				AND report_status NOT IN(' . REPORT_CLEARED . ', ' . REPORT_DELETE . ')
			GROUP BY report_subject';
		if (!$result = DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Could not get open reports', '', __LINE__, __FILE__, $sql);
		}

		$open_ids = array();
		while ($row = DB()->sql_fetchrow($result))
		{
			$open_ids[] = $row['report_subject'];
		}
		DB()->sql_freeresult($result);

		if (!empty($open_ids))
		{
			$sql = 'UPDATE ' . BB_PRIVMSGS . '
				SET privmsgs_reported = 1
				WHERE privmsgs_id IN(' . implode(', ', $open_ids) . ')';
			if (!DB()->sql_query($sql))
			{
				message_die(GENERAL_ERROR, 'Could not update topic reported flag', '', __LINE__, __FILE__, $sql);
			}
		}

		$clear_ids = array();
		foreach ($report_subjects as $report_subject)
		{
			if (!in_array($report_subject, $open_ids))
			{
				$clear_ids[] = $report_subject;
			}
		}

		if (!empty($clear_ids))
		{
			$sql = 'UPDATE ' . BB_PRIVMSGS . '
				SET privmsgs_reported = 0
				WHERE privmsgs_id IN(' . implode(', ', $clear_ids) . ')';
			if (!DB()->sql_query($sql))
			{
				message_die(GENERAL_ERROR, 'Could not update topic reported flag', '', __LINE__, __FILE__, $sql);
			}
		}
	}

	//
	// Returns url to a report subject
	//
	function subject_url($report_subject, $non_html_amp = false)
	{
		$sep = ($non_html_amp) ? '&' : '&amp;';
		return 'privmsg.php?mode=read'.$sep . POST_POST_URL . '=' . (int) $report_subject;
	}

	//
	// Returns report subject title
	//
	function subject_obtain($report_subject)
	{
		global $userdata;

		$sql = 'SELECT privmsgs_subject
			FROM ' . BB_PRIVMSGS . '
			WHERE privmsgs_to_userid = ' . $userdata['user_id'] . '
				AND privmsgs_id = ' . (int) $report_subject;
		if (!$result = DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Could not obtain report subject', '', __LINE__, __FILE__, $sql);
		}

		$row = DB()->sql_fetchrow($result);
		DB()->sql_freeresult($result);

		return ($row) ? $row['privmsgs_subject'] : false;
	}

	//
	// Returns report subject details
	//
	function subject_details_obtain($report_subject)
	{
		$sql = 'SELECT p.privmsgs_subject, p.privmsgs_from_userid, pt.privmsgs_text, u.username, u.user_id, u.user_rank
			FROM ' . BB_PRIVMSGS . ' p
			INNER JOIN ' . BB_PRIVMSGS_TEXT . ' pt
				ON pt.privmsgs_text_id = privmsgs_id
			LEFT JOIN ' . BB_USERS . ' u
				ON u.user_id = p.privmsgs_from_userid
			WHERE privmsgs_id = ' . (int) $report_subject;
		if (!$result = DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Could not obtain report subject details", '', __LINE__, __FILE__, $sql);
		}

		$row = DB()->sql_fetchrow($result);
		DB()->sql_freeresult($result);

		if (!$row)
		{
			return false;
		}

		$subject_details = array(
			'MESSAGE_ID' => '#' . $report_subject,
			'MESSAGE_FROM' => profile_url($row),
			'MESSAGE_TITLE' => $row['privmsgs_subject'],
			'MESSAGE_TEXT' => $row['privmsgs_text']);

		$this->_subject_details_prepare($subject_details['MESSAGE_TEXT'], $subject_details['MESSAGE_TITLE'], $row);

		return array(
			'details' => $subject_details);
	}

	//
	// Helper function for subject_details_obtain(), prepares private message and private
	// message subject
	//
	function _subject_details_prepare(&$message, &$subject, $row)
	{
		global $bb_cfg, $userdata, $datastore;
		include(INC_DIR . "bbcode.php");

		//
		// If the board has HTML off but the post has HTML
		// on then we process it, else leave it alone
		//
		/*if ((!$bb_cfg['allow_html'] || !$userdata['user_allowhtml']) && $row['privmsgs_enable_html'])
		{
			$message = preg_replace('#(<)([\/]?.*?)(>)#is', '&lt;\\2&gt;', $message);
		}*/

		$message = bbcode2html($message);

		$orig_word = $replacement_word = array();
		obtain_word_list($orig_word, $replacement_word);

		if (!empty($orig_word))
		{
			$subject = preg_replace($orig_word, $replacement_word, $subject);
			$message = preg_replace($orig_word, $replacement_word, $message);
		}
	}
}