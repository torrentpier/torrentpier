<?php

class report_user extends report_module
{
	var $mode = 'reportuser';
	var $duplicates = true;

	//
	// Constructor
	//
	function report_user($id, $data, $lang)
	{
		$this->id = $id;
		$this->data = $data;
		$this->lang = $lang;
	}

	//
	// Returns url to a report subject
	//
	function subject_url($id, $non_html_amp = false)
	{
		$sep = ($non_html_amp) ? '&' : '&amp;';
		return 'profile.php?mode=viewprofile'. $sep. POST_USERS_URL .'=' . (int) $id;
	}

	//
	// Returns report subject title
	//
	function subject_obtain($report_subject)
	{
		global $db;

		$sql = 'SELECT username
			FROM ' . BB_USERS . '
			WHERE user_id = ' . (int) $report_subject;
		if (!$result = DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Could not obtain report subject', '', __LINE__, __FILE__, $sql);
		}

		$row = DB()->sql_fetchrow($result);
		DB()->sql_freeresult($result);

		return ($row) ? $row['username'] : false;
	}
}