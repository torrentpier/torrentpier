<?php

class report_topic extends report_module
{
	var $mode = 'reporttopic';
	var $duplicates = false;
	var $subject_auth = array();

	//
	// Constructor
	//
	function report_topic($id, $data, $lang)
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
		$sql = 'UPDATE ' . BB_TOPICS . '
			SET topic_reported = 0';
		if (!DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Could not reset topic reported flag', '', __LINE__, __FILE__, $sql);
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
				$sql = 'UPDATE ' . BB_TOPICS . '
					SET topic_reported = 1
					WHERE topic_id IN(' . implode(', ', $open_ids) . ')';
				if (!DB()->sql_query($sql))
				{
					message_die(GENERAL_ERROR, 'Could not sync topic reported flag', '', __LINE__, __FILE__, $sql);
				}
			}
		}
	}

	//
	// Module action: Insert
	//
	function action_insert($report_subject, $report_id, $report_subject_data)
	{
		$sql = 'UPDATE ' . BB_TOPICS . '
			SET topic_reported = 1
			WHERE topic_id = ' . (int) $report_subject;
		if (!DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Could not update topic reported flag', '', __LINE__, __FILE__, $sql);
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

				$sql = 'UPDATE ' . BB_TOPICS . '
					SET topic_reported = 1
					WHERE topic_id IN(' . implode(', ', $report_subjects) . ')';
				if (!DB()->sql_query($sql))
				{
					message_die(GENERAL_ERROR, 'Could not update topic reported flag', '', __LINE__, __FILE__, $sql);
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
			$sql = 'UPDATE ' . BB_TOPICS . '
				SET topic_reported = 1
				WHERE topic_id IN(' . implode(', ', $open_ids) . ')';
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
			$sql = 'UPDATE ' . BB_TOPICS . '
				SET topic_reported = 0
				WHERE topic_id IN(' . implode(', ', $clear_ids) . ')';
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
		return 'viewtopic.php?'. POST_TOPIC_URL .'='. (int) $report_subject;
	}

	//
	// Returns report subject title
	//
	function subject_obtain($report_subject)
	{
		$sql = 'SELECT topic_title
			FROM ' . BB_TOPICS . '
			WHERE topic_id = ' . (int) $report_subject;
		if (!$result = DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Could not obtain report subject', '', __LINE__, __FILE__, $sql);
		}

		$row = DB()->sql_fetchrow($result);
		DB()->sql_freeresult($result);

		return ($row) ? $row['topic_title'] : false;
	}

	//
	// Obtains additional subject data
	//
	function subject_data_obtain($report_subject)
	{
		$sql = 'SELECT forum_id
			FROM ' . BB_TOPICS . '
			WHERE topic_id = ' . (int) $report_subject;
		if (!$result = DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Could not obtain report subject data', '', __LINE__, __FILE__, $sql);
		}

		$row = DB()->sql_fetchrow($result);
		DB()->sql_freeresult($result);

		return $row;
	}

	//
	// Obtains subjects authorisation
	//
	function subjects_auth_obtain($user_id, $report_subjects)
	{
		report_prepare_subjects($report_subjects);
		$moderated_forums = user_moderated_forums($user_id);

		//
		// Check stored forum ids
		//
		$check_topics = array();
		foreach ($report_subjects as $report_subject)
		{
			if (in_array($report_subject[1]['forum_id'], $moderated_forums))
			{
				$this->subjects_auth[$user_id][$report_subject[0]] = true;
			}
			else
			{
				$this->subjects_auth[$user_id][$report_subject[0]] = false;

				$check_topics[] = $report_subject[0];
			}
		}

		//
		// Check current forum ids
		//
		if (!empty($check_topics))
		{
			$sql = 'SELECT topic_id, forum_id
				FROM ' . BB_TOPICS . '
				WHERE topic_id IN(' . implode(', ', $check_topics) . ')';
			if (!$result = DB()->sql_query($sql))
			{
				message_die(GENERAL_ERROR, 'Could not obtain current forum ids', '', __LINE__, __FILE__, $sql);
			}

			while ($row = DB()->sql_fetchrow($result))
			{
				if (in_array($row['forum_id'], $moderated_forums))
				{
					$this->subjects_auth[$user_id][$row['topic_id']] = true;
				}
			}
			DB()->sql_freeresult($result);
		}
	}
}