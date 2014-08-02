<?php

define('IN_FORUM', true);
define('BB_SCRIPT', 'report');
define('BB_ROOT', './');
require(BB_ROOT .'common.php');
require(INC_DIR .'bbcode.php');
require(INC_DIR .'functions_report.php');

// Init userdata
$user->session_start(array('req_login' => true));

if(!$bb_cfg['reports_enabled']) bb_die($lang['REPORTS_DISABLE']);

$return_links = array(
	'index' => '<br /><br />' . sprintf($lang['CLICK_RETURN_INDEX'], '<a href="index.php">', '</a>'),
	'list' => '<br /><br />' . sprintf($lang['CLICK_RETURN_REPORT_LIST'], '<a href="report.php">', '</a>')
);

if (isset($_POST['mode']) || isset($_GET['mode']))
{
	$mode = (isset($_POST['mode'])) ? $_POST['mode'] : $_GET['mode'];
}
else
{
	$mode = '';
}

$report_modules = report_modules();

//
// Check for matching report module
//
if (!empty($mode))
{
	foreach (array_keys($report_modules) as $report_module_id)
	{
		$report_module =& $report_modules[$report_module_id];

		if (!empty($report_module->mode) && $mode == $report_module->mode)
		{
			break;
		}

		unset($report_module);
	}
}

//
// Report module matched, show report form
//
if (isset($report_module))
{
	$errors = array();

	if (isset($_POST['id']) || isset($_GET['id']))
	{
		$report_subject_id = (isset($_POST['id'])) ? (int) $_POST['id'] : (int) $_GET['id'];
	}
	else
	{
		$report_subject_id = 0;
	}

	//
	// Check authorisation, check for duplicate reports
	//
	if (!$report_module->auth_check('auth_write'))
	{
		bb_die($report_module->lang['AUTH_WRITE_ERROR'] . $report_module->return_link($report_subject_id) . $return_links['index']);
	}
	else if (!$report_module->duplicates && report_duplicate_check($report_module->id, $report_subject_id))
	{
		bb_die($report_module->lang['DUPLICATE_ERROR'] . $report_module->return_link($report_subject_id) . $return_links['index']);
	}

	if (isset($_POST['submit']))
	{
		$report_reason = (isset($_POST['reason'])) ? (int) $_POST['reason'] : 0;
		$report_desc = (isset($_POST['message'])) ? $_POST['message'] : '';

		//
		// Obtain report title if necessary
		//
		if (method_exists($report_module, 'subject_obtain'))
		{
			$report_title = addslashes($report_module->subject_obtain($report_subject_id));
		}
		else
		{
			$report_title = (isset($_POST['title'])) ? $_POST['title'] : '';
			$report_subject_id = 0;
		}

		//
		// Validate values
		//
		if (empty($report_title))
		{
			$errors[] = $lang['REPORT_TITLE_EMPTY'];
		}

		if (empty($report_desc))
		{
			$errors[] = $lang['REPORT_DESC_EMPTY'];
		}

		//
		// Insert report
		//
		if (empty($errors))
		{
			$report_desc = str_replace("\'", "'", $report_desc);
			$report_title = clean_title($report_title);

			report_insert($report_module->id, $report_subject_id, $report_reason, $report_title, $report_desc, false);

			bb_die($lang['REPORT_INSERTED'] . $report_module->return_link($report_subject_id) . $return_links['index']);
		}
	}
	else if (isset($_POST['cancel']))
	{
		$redirect_url = (method_exists($report_module, 'subject_url')) ? $report_module->subject_url($report_subject_id, true) : "index.php";
		redirect($redirect_url);
	}

	$page_title = $report_module->lang['WRITE_REPORT'];
	include(PAGE_HEADER);
	$template->set_filenames(array(
		'body' => 'report_form_body.tpl')
	);

	//
	// Show validation errors
	//
	if (!empty($errors))
	{
		$template->assign_block_vars('switch_report_errors', array());
		foreach ($errors as $error)
		{
			$template->assign_block_vars('switch_report_errors.report_errors', array(
				'MESSAGE' => $error,
			));
		}
	}

	//
	// Generate report reasons select
	//
	if ($report_reasons = $report_module->reasons_obtain())
	{
		$template->assign_block_vars('switch_report_reasons', array());

		foreach ($report_reasons as $reason_id => $reason_desc)
		{
			$template->assign_block_vars('switch_report_reasons.report_reasons', array(
				'ID' => $reason_id,
				'DESC' => $reason_desc,
				'CHECKED' => (isset($report_reason) && $report_reason == $reason_id) ? ' selected="selected"' : '',
			));
		}
	}

	//
	// Show report subject, check for correct subject
	//
	if (method_exists($report_module, 'subject_obtain'))
	{
		if ($report_subject = $report_module->subject_obtain($report_subject_id))
		{
			$template->assign_block_vars('switch_report_subject', array());
			$template->assign_var('REPORT_SUBJECT', $report_subject);

			if (method_exists($report_module, 'subject_url'))
			{
				$template->assign_block_vars('switch_report_subject.switch_url', array());
				$template->assign_var('U_REPORT_SUBJECT', $report_module->subject_url($report_subject_id));
			}
		}
		else
		{
			bb_die($report_module->lang['WRITE_REPORT_ERROR'] . $return_links['index']);
		}
	}
	//
	// Show report title input
	//
	else
	{
		$template->assign_block_vars('switch_report_title', array());
	}

	$hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" /><input type="hidden" name="id" value="' . $report_subject_id . '" />';

	$template->assign_vars(array(
		'S_REPORT_ACTION' => "report.php",
		'S_HIDDEN_FIELDS' => $hidden_fields,

		'L_WRITE_REPORT' => $report_module->lang['WRITE_REPORT'],
		'L_WRITE_REPORT_EXPLAIN' => $report_module->lang['WRITE_REPORT_EXPLAIN'],
		'REPORT_TITLE' => (!method_exists($report_module, 'subject_obtain') && isset($report_title)) ? stripslashes($report_title) : '',
		'REPORT_DESC' => (isset($report_desc)) ? stripslashes($report_desc) : '',
	));

	$template->pparse('body');
	include(PAGE_FOOTER);
}
else
{
	if ($userdata['user_level'] != ADMIN && ($bb_cfg['report_list_admin'] || $userdata['user_level'] != MOD))
	{
		redirect("index.php");
	}

	$params = array('open', 'process', 'clear', 'delete');
	foreach ($params as $param)
	{
		if (isset($_POST[$param]))
		{
			$mode = $param;
		}
	}

	// Report status css classes
	$report_status_classes = array(
		REPORT_NEW => 'report_new',
		REPORT_OPEN => 'report_open',
		REPORT_IN_PROCESS => 'report_process',
		REPORT_CLEARED => 'report_cleared',
		REPORT_DELETE => 'report_delete'
	);

	switch ($mode)
	{
		case 'open':
		case 'process':
		case 'clear':
		case 'delete':
			//
			// Validate report ids
			//
			if (isset($_POST[POST_REPORT_URL]) || isset($_GET[POST_REPORT_URL]))
			{
				$report_id = (isset($_POST[POST_REPORT_URL])) ? $_POST[POST_REPORT_URL] : $_GET[POST_REPORT_URL];
				$reports = array((int) $report_id);

				$single_report = true;
			}
			else if (isset($_POST['reports']))
			{
				$reports = array();
				foreach ($_POST['reports'] as $report_id)
				{
					$reports[] = (int) $report_id;
				}

				$single_report = false;
			}

			if (empty($reports))
			{
				meta_refresh('report.php', 3);
				bb_die($lang['NO_REPORTS_SELECTED'] . $return_links['list'] . $return_links['index']);
			}

			//
			// Cancel action
			//
			if (isset($_POST['cancel']))
			{
				$redirect_url = ($single_report) ? "report.php?" . POST_REPORT_URL . '=' . $reports[0] : "report.php";
				redirect($redirect_url);
			}

			//
			// Hidden fields
			//
			$hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" />';
			if ($single_report)
			{
				$hidden_fields .= '<input type="hidden" name="' . POST_REPORT_URL . '" value="' . $reports[0] . '" />';
			}
			else
			{
				foreach ($reports as $report_id)
				{
					$hidden_fields .= '<input type="hidden" name="reports[]" value="' . $report_id . '" />';
				}
			}

			$template->assign_vars(array(
				'S_CONFIRM_ACTION' => "report.php",
				'S_HIDDEN_FIELDS' => $hidden_fields,
			));

			//
			// Change reports status
			//
			if ($mode != 'delete')
			{
				if (isset($_POST['confirm']))
				{
					$comment = (isset($_POST['comment'])) ? $_POST['comment'] : '';

					switch ($mode)
					{
						case 'open':
							$status = REPORT_OPEN;
						break;

						case 'process':
							$status = REPORT_IN_PROCESS;
						break;

						case 'clear':
							$status = REPORT_CLEARED;
						break;
					}

					reports_update_status($reports, $status, $comment);

					$meta_url = ($single_report) ? "report.php?" . POST_REPORT_URL . '=' . $reports[0] : "report.php";

					meta_refresh($meta_url, 3);

					$return_link = ($single_report) ? '<br /><br />' . sprintf($lang['CLICK_RETURN_REPORT'], '<a href="' . ("report.php?" . POST_REPORT_URL . '=' . $reports[0]) . '">', '</a>') : '';
					$message = ($single_report) ? 'REPORT_CHANGED' : 'REPORTS_CHANGED';
					bb_die($lang[$message] . $return_link . $return_links['list'] . $return_links['index']);
				}

				$page_title = ($single_report) ? $lang['CHANGE_REPORT'] : $lang['CHANGE_REPORTS'];

				include(PAGE_HEADER);
				$template->set_filenames(array(
					'body' => 'report_change_body.tpl',
				));

				$template->assign_vars(array(
					'MESSAGE_TITLE' => $page_title,
					'MESSAGE_TEXT' => ($single_report) ? $lang['CHANGE_REPORT_EXPLAIN'] : $lang['CHANGE_REPORTS_EXPLAIN'],
				));

				$template->pparse('body');
				include(PAGE_FOOTER);
			}
			//
			// Delete reports
			//
			else
			{
				if (isset($_POST['confirm']))
				{
					reports_delete($reports);
					meta_refresh('report.php', 3);
					$message = ($single_report) ? 'REPORT_DELETED' : 'REPORTS_DELETED';
					bb_die($lang[$message] . $return_links['list'] . $return_links['index']);
				}

				print_confirmation(array(
					'CONFIRM_TITLE' => ($single_report) ? $lang['DELETE_REPORT'] : $lang['DELETE_REPORTS'],
					'QUESTION'      => ($single_report) ? $lang['DELETE_REPORT_EXPLAIN'] : $lang['DELETE_REPORTS_EXPLAIN'],
					'FORM_ACTION'   => "report.php",
					'HIDDEN_FIELDS' => $hidden_fields,
				));
			}
		break;

		case 'reported':
			$cat = (isset($_GET[POST_CAT_URL])) ? (int) $_GET[POST_CAT_URL] : 0;
			$report_subject_id = (isset($_GET['id'])) ? (int) $_GET['id'] : 0;

			if (empty($cat) || empty($report_subject_id) || !isset($report_modules[$cat]))
			{
				bb_die($lang['REPORT_NOT_SUPPORTED'] . $return_links['index']);
			}

			$report_module =& $report_modules[$cat];
			$reports = reports_open_obtain($cat, $report_subject_id);

			//
			// No open reports for the subject, sync report module
			//
			if (empty($reports))
			{
				if (method_exists($report_module, 'sync'))
				{
					$report_module->sync();
				}

				bb_die($lang['NO_REPORTS_FOUND'] . $report_module->return_link($report_subject_id) . $return_links['index']);
			}
			//
			// Redirect to the open report
			//
			else if (count($reports) == 1)
			{
				$redirect_url = "report.php?" . POST_REPORT_URL . '=' . $reports[0]['report_id'];
				redirect($redirect_url);
			}

			$page_title = $lang['OPEN_REPORTS'];
			include(PAGE_HEADER);
			$template->set_filenames(array(
				'body' => 'report_open_body.tpl',
			));

			$template->assign_vars(array(
				'S_REPORT_ACTION', "report.php",

				'L_STATUS_CLEARED' => $lang['REPORT_STATUS'][REPORT_CLEARED],
				'L_STATUS_IN_PROCESS' => $lang['REPORT_STATUS'][REPORT_IN_PROCESS],
				'L_STATUS_OPEN' => $lang['REPORT_STATUS'][REPORT_OPEN],
			));

			//
			// Show list with open reports
			//
			foreach ($reports as $report)
			{
				$template->assign_block_vars('open_reports', array(
					'U_SHOW' => "report.php?" . POST_REPORT_URL . '=' . $report['report_id'],

					'ID' => $report['report_id'],
					'TITLE' => $report['report_title'],
					'AUTHOR' => profile_url($report),
					'TIME' => bb_date($report['report_time']),
				));
			}

			$template->pparse('body');
			include(PAGE_FOOTER);
		break;

		case '':
			$page_title = $lang['REPORTS'];
			include(PAGE_HEADER);
			$template->set_filenames(array(
				'body' => 'report_list_body.tpl')
			);

			$template->assign_vars(array(
				'S_REPORT_ACTION' => "report.php",

				'U_REPORT_INDEX' => "report.php",

				'L_STATUS_CLEARED' => $lang['REPORT_STATUS'][REPORT_CLEARED],
				'L_STATUS_IN_PROCESS' => $lang['REPORT_STATUS'][REPORT_IN_PROCESS],
				'L_STATUS_OPEN' => $lang['REPORT_STATUS'][REPORT_OPEN],
			));

			$cat = (isset($_GET[POST_CAT_URL])) ? (int) $_GET[POST_CAT_URL] : null;
			$cat_url = (!empty($cat)) ? '&amp;' . POST_CAT_URL . "=$cat" : '';

			$show_delete_option = false;

			//
			// Show report list
			//
			$reports = reports_obtain($cat);
			foreach (array_keys($report_modules) as $report_module_id)
			{
				$report_module =& $report_modules[$report_module_id];

				//
				// Check module authorisation
				//
				if (!$report_module->auth_check('auth_view'))
				{
					continue;
				}

				$template->assign_block_vars('report_modules', array(
					'U_SHOW' => "report.php?" . POST_CAT_URL . '=' . $report_module->id,
					'TITLE' => $report_module->lang['REPORT_LIST_TITLE'],
				));

				//
				// No reports in this category, display no reports message
				//
				if (!isset($reports[$report_module->id]))
				{
					if (empty($cat) || $cat == $report_module->id)
					{
						$template->assign_block_vars('report_modules.no_reports', array());
					}

					continue;
				}

				//
				// Check if deletions are allowed
				//
				if ($report_module->auth_check('auth_delete_view'))
				{
					$show_delete_option = true;
				}

				//
				// Show reports
				//
				foreach ($reports[$report_module->id] as $report)
				{
					$template->assign_block_vars('report_modules.reports', array(
						'U_SHOW' => "report.php?" . POST_REPORT_URL . '=' . $report['report_id'] . $cat_url,
						'ROW_CLASS' => $report_status_classes[$report['report_status']],
						'ID' => $report['report_id'],
						'TITLE' => (strlen($report['report_title'] > 53)) ? substr($report['report_title'], 0, 50) . '...' : $report['report_title'],
						'AUTHOR' => profile_url($report),
						'TIME' => bb_date($report['report_time']),
						'STATUS' => $lang['REPORT_STATUS'][$report['report_status']],
					));

					if (isset($_GET[POST_REPORT_URL]) && $_GET[POST_REPORT_URL] == $report['report_id'])
					{
						$template->assign_block_vars('report_modules.reports.switch_current', array());
					}
				}
			}

			if ($show_delete_option)
			{
				$template->assign_block_vars('switch_global_delete_option', array());
			}

			//
			// Show information for one report
			//
			if (isset($_GET[POST_REPORT_URL]))
			{
				$template->set_filenames(array(
					'report_view' => 'report_view_body.tpl')
				);

				if (!$report = report_obtain((int) $_GET[POST_REPORT_URL]))
				{
					bb_die($lang['REPORT_NOT_EXISTS'] . $return_links['list'] . $return_links['index']);
				}

				if ($report['report_status'] == REPORT_NEW)
				{
					reports_update_status($report['report_id'], REPORT_OPEN, '', false, true, false);
					$report['report_status'] = REPORT_OPEN;
				}

				//
				// Show report subject (with or without details, depending on the report module)
				//
				$report_module =& $report_modules[$report['report_module_id']];
				if (method_exists($report_module, 'subject_details_obtain'))
				{
					if ($report_subject = $report_module->subject_details_obtain($report['report_subject']))
					{
						if (isset($report_subject['subject']) || isset($report_subject['details']))
						{
							$template->assign_block_vars('report_subject', array());
						}

						//
						// Assign report subject
						//
						if (isset($report_subject['subject']))
						{
							$template->assign_block_vars('report_subject.switch_subject', array());
							$template->assign_var('REPORT_SUBJECT', $report_subject['subject']);

							if (method_exists($report_module, 'subject_url'))
							{
								$template->assign_block_vars('report_subject.switch_subject.switch_url', array());
								$template->assign_vars(array(
									'S_REPORT_SUBJECT_TARGET' => ($bb_cfg['report_new_window']) ? ' target="_blank"' : '',
									'U_REPORT_SUBJECT' => $report_module->subject_url($report['report_subject']),
								));
							}
						}

						//
						// Assign report subject details
						//
						if (isset($report_subject['details']))
						{
							foreach ($report_subject['details'] as $detail_title => $detail_value)
							{
								$template->assign_block_vars('report_subject.details', array(
									'TITLE' => $report_module->lang[strtoupper($detail_title)],
									'VALUE' => $detail_value,
								));
							}
						}
					}
					else
					{
						$template->assign_block_vars('switch_report_subject_deleted', array());
						$template->assign_var('L_REPORT_SUBJECT_DELETED', $report_module->lang['DELETED_ERROR']);
					}
				}
				else if (method_exists($report_module, 'subject_obtain'))
				{
					if ($report_subject = $report_module->subject_obtain($report['report_subject']))
					{
						//
						// Assign report subject
						//
						$template->assign_block_vars('report_subject', array());
						$template->assign_block_vars('report_subject.switch_subject', array());
						$template->assign_var('REPORT_SUBJECT', $report_subject);

						if (method_exists($report_module, 'subject_url'))
						{
							$template->assign_block_vars('report_subject.switch_subject.switch_url', array());
							$template->assign_vars(array(
								'S_REPORT_SUBJECT_TARGET' => ($bb_cfg['report_new_window']) ? ' target="_blank"' : '',
								'U_REPORT_SUBJECT' => $report_module->subject_url($report['report_subject']),
							));
						}
					}
					else
					{
						$template->assign_block_vars('switch_report_subject_deleted', array());
						$template->assign_var('L_REPORT_SUBJECT_DELETED', $report_module->lang['DELETED_ERROR']);
					}
				}

				//
				// Assign report reason
				//
				if (!empty($report['report_reason_desc']))
				{
					$template->assign_block_vars('switch_report_reason', array());

					$template->assign_var('REPORT_REASON', $report['report_reason_desc']);
				}

				//
				// Show report changes
				//
				if ($report_changes = report_changes_obtain($report['report_id']))
				{
					$template->assign_block_vars('switch_report_changes', array());

					foreach ($report_changes as $report_change)
					{
						$report_change_user = profile_url($report_change);

						$report_change_status = $lang['REPORT_STATUS'][$report_change['report_status']];
						$report_change_time = bb_date($report_change['report_change_time']);

						//
						// Text that contains all information
						//
						if ($report_change['report_status'] == REPORT_DELETE)
						{
							$report_change_text = sprintf($lang['REPORT_CHANGE_DELETE_TEXT'], $report_change_user, $report_change_time);
						}
						else if ($report_change['report_change_comment'] != '')
						{
							$report_change_text = sprintf($lang['REPORT_CHANGE_TEXT_COMMENT'], $report_change_status, $report_change_user, $report_change_time, bbcode2html($report_change['report_change_comment']));
						}
						else
						{
							$report_change_text = sprintf($lang['REPORT_CHANGE_TEXT'], $report_change_status, $report_change_user, $report_change_time);
						}

						$template->assign_block_vars('switch_report_changes.report_changes', array(
							'ROW_CLASS' => $report_status_classes[$report_change['report_status']],
							'STATUS' => $report_change_status,
							'USER' => $report_change_user,
							'TIME' => $report_change_time,
							'TEXT' => $report_change_text,
						));
					}

					//
					// Assign last change information
					//
					$template->assign_vars(array(
						'REPORT_LAST_CHANGE_TIME' => $report_change_time,
						'REPORT_LAST_CHANGE_USER' => profile_url($report_change),
					));
				}

				//
				// Check if deletions are allowed
				//
				if ($report_module->auth_check('auth_delete_view'))
				{
					$template->assign_block_vars('switch_delete_option', array());
				}

				$template->assign_vars(array(
					'S_HIDDEN_FIELDS' => '<input type="hidden" name="' . POST_REPORT_URL . '" value="' . $report['report_id'] . '" />',
					'U_REPORT_AUTHOR_PRIVMSG' => PM_URL . "?mode=post&amp;" . POST_USERS_URL . '=' . $report['user_id'],
					'REPORT_TYPE' => $report_module->lang['REPORT_TYPE'],
					'REPORT_TITLE' => $report['report_title'],
					'REPORT_AUTHOR' => profile_url($report),
					'REPORT_TIME' => bb_date($report['report_time']),
					'REPORT_DESC' => bbcode2html($report['report_desc']),
					'REPORT_STATUS' => $lang['REPORT_STATUS'][$report['report_status']],
					'REPORT_STATUS_CLASS' => $report_status_classes[$report['report_status']],
				));
			}
			//
			// Show report index page
			//
			else
			{
				$template->set_filenames(array(
					'report_view' => 'report_index_body.tpl')
				);

				$statistics = array(
					'Report_count' => 'report_count',
					'Report_modules_count' => 'modules_count',
					'Report_hack_count' => 'report_hack_count');
				foreach ($statistics as $stat_lang => $stat_mode)
				{
					$template->assign_block_vars('report_statistics', array(
						'STATISTIC' => $lang[strtoupper($stat_lang)],
						'VALUE' => report_statistics($stat_mode),
					));
				}

				$deleted_reports = reports_deleted_obtain();
				if (!empty($deleted_reports))
				{
					$template->assign_block_vars('switch_deleted_reports', array());
					foreach ($deleted_reports as $report)
					{
						$report_module =& $report_modules[$report['report_module_id']];

						$template->assign_block_vars('switch_deleted_reports.deleted_reports', array(
							'U_SHOW' => "report.php?" . POST_REPORT_URL . '=' . $report['report_id'] . $cat_url,
							'ID' => $report['report_id'],
							'TITLE' => $report['report_title'],
							'TYPE' => $report_module->lang['REPORT_TYPE'],
							'AUTHOR' => profile_url($report),
							'TIME' => bb_date($report['report_time']),
							'STATUS' => $lang['REPORT_STATUS'][REPORT_DELETE],
						));
					}
				}
			}

			$template->assign_var_from_handle('REPORT_VIEW', 'report_view');

			$template->pparse('body');
			include(PAGE_FOOTER);
		break;

		default:
			bb_die($lang['REPORT_NOT_SUPPORTED'] . $return_links['index']);
		break;
	}
}