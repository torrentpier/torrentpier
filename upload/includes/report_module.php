<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

class report_module
{
	var $subjects_auth = array();

	//
	// Returns module info for the ACP
	//
	function info()
	{
		return array(
			'title' => $this->lang['MODULE_TITLE'],
			'explain' => $this->lang['MODULE_EXPLAIN']);
	}

	//
	// Generates a return link based on the subject_url() method
	//
	function return_link($id)
	{
		global $lang;

		if (method_exists($this, 'subject_url') && isset($this->lang['CLICK_RETURN']))
		{
			return '<br /><br />' . sprintf($this->lang['CLICK_RETURN'], '<a href="' . $this->subject_url($id) . '">', '</a>');
		}
		else
		{
			return '';
		}
	}

	//
	// Returns report reasons of the module
	//
	function reasons_obtain()
	{
		global $lang;

		$sql = 'SELECT report_reason_id, report_reason_desc
			FROM ' . BB_REPORTS_REASONS . '
			WHERE report_module_id = ' . (int) $this->id . '
			ORDER BY report_reason_order';
		if (!$result = DB()->sql_query($sql))
		{
			message_die(GENERAL_ERROR, 'Could not obtain report reasons', '', __LINE__, __FILE__, $sql);
		}

		$rows = array();
		while ($row = DB()->sql_fetchrow($result))
		{
			$rows[$row['report_reason_id']] = (isset($lang[$row['report_reason_desc']])) ? $lang[$row['report_reason_desc']] : $row['report_reason_desc'];
		}
		DB()->sql_freeresult($result);

		return (!empty($rows)) ? $rows : false;
	}

	//
	// Checks module authorisation
	//
	function auth_check($auth_names, $userdata = null)
	{
		if (!isset($userdata))
		{
			global $userdata;
		}

		if ($userdata['user_id'] == ANONYMOUS)
		{
			return false;
		}

		//
		// Set "virtual" column
		//
		if (!isset($this->data['auth_delete_view']))
		{
			if ($this->data['auth_delete'] == REPORT_AUTH_CONFIRM)
			{
				$this->data['auth_delete_view'] = REPORT_AUTH_MOD;
			}
			else
			{
				$this->data['auth_delete_view'] = $this->data['auth_delete'];
			}
		}

		switch ($userdata['user_level'])
		{
			case ADMIN:
				return true;
			break;

			case MOD:
				$auth_value = REPORT_AUTH_MOD;
			break;

			case GROUP_MEMBER:
			case USER:
				$auth_value = REPORT_AUTH_USER;
			break;

			default:
				return false;
			break;
		}

		if (!is_array($auth_names))
		{
			$auth_names = array($auth_names);
		}

		//
		// Check authorisation
		//
		foreach ($auth_names as $auth_name)
		{
			if ($this->data[$auth_name] > $auth_value)
			{
				return false;
			}
		}

		return true;
	}

	//
	// Checks the authorisation to view the specified report subjects
	//
	function subjects_auth_check(&$report_subjects, $userdata = null)
	{
		if (!method_exists($this, 'subjects_auth_obtain') || empty($report_subjects))
		{
			return true;
		}

		if (!isset($userdata))
		{
			global $userdata;
		}

		if ($userdata['user_level'] == ADMIN)
		{
			return true;
		}
		else if ($userdata['user_level'] != MOD)
		{
			return false;
		}

		report_prepare_subjects($report_subjects);

		$user_id = $userdata['user_id'];
		if (!isset($this->subject_auth[$user_id]))
		{
			$this->subject_auth[$user_id] = array();
			$this->subjects_auth_obtain($user_id, $report_subjects);
		}
		else
		{
			$check_ids = array();
			foreach ($report_subjects as $report_id => $report_subject)
			{
				if (!isset($this->subjects_auth[$user_id][$report_subject[0]]))
				{
					$check_ids[] = $report_subjects[$report_id];
				}
			}

			if (!empty($check_ids))
			{
				$this->subjects_auth_obtain($user_id, $check_ids);
			}
		}

		$subjects_count = count($report_subjects);
		foreach ($report_subjects as $report_id => $report_subject)
		{
			if (!$this->subjects_auth[$user_id][$report_subject[0]])
			{
				unset($report_subjects[$report_id]);
			}
		}

		return ($subjects_count == count($report_subjects));
	}
}