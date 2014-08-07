<?php

if (!empty($setmodules))
{
	if ($bb_cfg['reports_enabled']) $module['MODS']['REPORTS'] = basename(__FILE__) .'?mode=config';
	return;
}
require('./pagestart.php');

require(INC_DIR . 'functions_report.php');
require(INC_DIR . 'functions_report_admin.php');

$return_links = array(
	'index' => '<br /><br />'. sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'),
	'config' => '<br /><br />'. sprintf($lang['CLICK_RETURN_REPORT_CONFIG'], '<a href="admin_reports.php?mode=config">', '</a>'),
	'admin' => '<br /><br />'. sprintf($lang['CLICK_RETURN_REPORT_ADMIN'], '<a href="admin_reports.php">', '</a>')
);

$redirect_url = 'admin/admin_reports.php';

$template->assign_var('S_REPORT_ACTION', 'admin_reports.php');

if (isset($_POST['mode']) || isset($_GET['mode']))
{
	$mode = (isset($_POST['mode'])) ? $_POST['mode'] : $_GET['mode'];

	//
	// allow multiple modes (we need this for sub-modes, e.g. the report reasons)
	//
	if (is_array($mode))
	{
		$modes = $mode;
		$mode = $modes[0];
	}
	else
	{
		$modes = array($mode);
	}
}
else
{
	$mode = '';
	$modes = array();
}

//
// Configuration page
//
if ($mode == 'config')
{
	if (isset($_POST['submit']))
	{
		$config_update = (isset($_POST['bb_cfg'])) ? $_POST['bb_cfg'] : array();

		bb_update_config($config_update);
		report_modules_cache_clean();

		bb_die($lang['REPORT_CONFIG_UPDATED'] . $return_links['config'] . $return_links['index']);
	}
	else
	{
		$template->assign_vars(array(
			'S_HIDDEN_FIELDS' => '<input type="hidden" name="mode" value="config" />',

			'REPORT_SUBJECT_AUTH_ON' => ($bb_cfg['report_subject_auth']) ? ' checked="checked"' : '',
			'REPORT_SUBJECT_AUTH_OFF' => (!$bb_cfg['report_subject_auth']) ? ' checked="checked"' : '',
			'REPORT_MODULES_CACHE_ON' => ($bb_cfg['report_modules_cache']) ? ' checked="checked"' : '',
			'REPORT_MODULES_CACHE_OFF' => (!$bb_cfg['report_modules_cache']) ? ' checked="checked"' : '',
			'REPORT_NOTIFY_CHANGE' => ($bb_cfg['report_notify'] == REPORT_NOTIFY_CHANGE) ? ' checked="checked"' : '',
			'REPORT_NOTIFY_NEW' => ($bb_cfg['report_notify'] == REPORT_NOTIFY_NEW) ? ' checked="checked"' : '',
			'REPORT_NOTIFY_OFF' => (!$bb_cfg['report_notify']) ? ' checked="checked"' : '',
			'REPORT_LIST_ADMIN_ON' => ($bb_cfg['report_list_admin']) ? ' checked="checked"' : '',
			'REPORT_LIST_ADMIN_OFF' => (!$bb_cfg['report_list_admin']) ? ' checked="checked"' : '',
			'REPORT_NEW_WINDOW_ON' => ($bb_cfg['report_new_window']) ? ' checked="checked"' : '',
			'REPORT_NEW_WINDOW_OFF' => (!$bb_cfg['report_new_window']) ? ' checked="checked"' : '',
		));

		print_page('report_config_body.tpl', 'admin');
	}
}
else if (isset($_POST[POST_CAT_URL]) || isset($_GET[POST_CAT_URL]))
{
	$module_id = (isset($_POST[POST_CAT_URL])) ? (int) $_POST[POST_CAT_URL] : (int) $_GET[POST_CAT_URL];

	if (!$report_module = report_modules('id', $module_id))
	{
		bb_die($lang['REPORT_MODULE_NOT_EXISTS'] . $return_links['admin'] . $return_links['index']);
	}

	switch ($mode)
	{
		//
		// Edit module
		//
		case 'edit':
			if (isset($_POST['submit']))
			{
				$module_notify = (isset($_POST['report_module_notify']) && $_POST['report_module_notify'] == 1) ? 1 : 0;
				$module_prune = (isset($_POST['report_module_prune'])) ? (int) $_POST['report_module_prune'] : $report_module->data['report_module_prune'];

				$auth_write = (isset($_POST['auth_write'])) ? (int) $_POST['auth_write'] : $report_module->data['auth_write'];
				$auth_view = (isset($_POST['auth_view'])) ? (int) $_POST['auth_view'] : $report_module->data['auth_view'];
				$auth_notify = (isset($_POST['auth_notify'])) ? (int) $_POST['auth_notify'] : $report_module->data['auth_notify'];
				$auth_delete = (isset($_POST['auth_delete'])) ? (int) $_POST['auth_delete'] : $report_module->data['auth_delete'];

				report_module_edit($module_id, $module_notify, $module_prune, $auth_write, $auth_view, $auth_notify, $auth_delete);

				bb_die($lang['REPORT_MODULE_EDITED'] . $return_links['admin'] . $return_links['index']);
			}
			else if (isset($_POST['cancel']))
			{
				redirect($redirect_url);
			}

			$module_info = $report_module->info();

			$hidden_fields = '<input type="hidden" name="mode" value="edit" /><input type="hidden" name="' . POST_CAT_URL . '" value="' . $module_id . '" />';

			$template->assign_vars(array(
				'S_HIDDEN_FIELDS' => $hidden_fields,
				'MODULE_TITLE' => $module_info['title'],
				'MODULE_EXPLAIN' => $module_info['explain'],
				'MODULE_NOTIFY_ON' => ($report_module->data['report_module_notify']) ? ' checked="checked"' : '',
				'MODULE_NOTIFY_OFF' => (!$report_module->data['report_module_notify']) ? ' checked="checked"' : '',
				'MODULE_PRUNE' => $report_module->data['report_module_prune'])
			);

			//
			// Authorisation selects
			//
			report_auth_select('auth_write', $report_module->data['auth_write'], array(REPORT_AUTH_USER, REPORT_AUTH_MOD, REPORT_AUTH_ADMIN));
			report_auth_select('auth_view', $report_module->data['auth_view']);
			report_auth_select('auth_notify', $report_module->data['auth_notify']);
			report_auth_select('auth_delete', $report_module->data['auth_delete'], array(REPORT_AUTH_MOD, REPORT_AUTH_CONFIRM, REPORT_AUTH_ADMIN));

			print_page('report_module_edit_body.tpl', 'admin');
		break;

		//
		// Report reasons
		//
		case 'reasons':
			$reason_mode = (isset($modes[1])) ? $modes[1] : '';

			$temp_url = "admin_reports.php?mode=reasons&amp;" . POST_CAT_URL . "=$module_id";
			$return_links['reasons'] = '<br /><br />' . sprintf($lang['CLICK_RETURN_REPORT_REASONS'], '<a href="' . $temp_url . '">', '</a>');

			$redirect_url = 'admin/admin_reports.php?mode=reasons&' . POST_CAT_URL . "=$module_id";

			if (isset($_POST[POST_REPORT_REASON_URL]) || isset($_GET[POST_REPORT_REASON_URL]))
			{
				$reason_id = (isset($_POST[POST_REPORT_REASON_URL])) ? (int) $_POST[POST_REPORT_REASON_URL] : (int) $_GET[POST_REPORT_REASON_URL];

				switch ($reason_mode)
				{
					//
					// Edit reason
					//
					case 'edit':
						$errors = array();

						if (isset($_POST['submit']))
						{
							$reason_desc = (isset($_POST['report_reason_desc'])) ? htmlspecialchars($_POST['report_reason_desc']) : '';

							//
							// Validate reason desc
							//
							if (empty($reason_desc))
							{
								$errors[] = $lang['REASON_DESC_EMPTY'];
							}

							if (empty($errors))
							{
								$reason_desc = str_replace("\'", "'", $reason_desc);

								report_reason_edit($reason_id, $module_id, $reason_desc);

								bb_die($lang['REPORT_REASON_EDITED'] . $return_links['reasons'] . $return_links['admin'] . $return_links['index']);
							}
						}
						else if (isset($_POST['cancel']))
						{
							redirect($redirect_url);
						}

						//
						// Show validation errors
						//
						if (!empty($errors))
						{
							$template->assign_block_vars('switch_report_errors', array());
							foreach ($errors as $error)
							{
								$template->assign_block_vars('switch_report_errors.report_errors', array(
									'MESSAGE' => $error)
								);
							}
						}

						if (!$report_reason = report_reason_obtain($reason_id))
						{
							bb_die($lang['REPORT_REASON_NOT_EXISTS'] . $return_links['reasons'] . $return_links['admin'] . $return_links['index']);
						}

						if (isset($reason_desc))
						{
							$report_reason['report_reason_desc'] = stripslashes($reason_desc);
						}

						$hidden_fields = '<input type="hidden" name="mode[]" value="reasons" /><input type="hidden" name="' . POST_CAT_URL . '" value="' . $module_id . '" />';
						$hidden_fields .= '<input type="hidden" name="mode[]" value="edit" /><input type="hidden" name="' . POST_REPORT_REASON_URL . '" value="' . $reason_id . '" />';

						$template->assign_vars(array(
							'S_HIDDEN_FIELDS' => $hidden_fields,
							'REASON_DESC' => $report_reason['report_reason_desc'])
						);

						print_page('report_reason_edit_body.tpl', 'admin');
					break;

					//
					// Move reason up/down
					//
					case 'up':
					case 'down':
						report_reason_move($reason_mode, $reason_id);

						redirect($redirect_url);
					break;

					//
					// Delete reason
					//
					case 'delete':
						if (isset($_POST['confirm']))
						{
							report_reason_delete($reason_id);

							bb_die($lang['REPORT_REASON_DELETED'] . $return_links['reasons'] . $return_links['admin'] . $return_links['index']);
						}
						else if (isset($_POST['cancel']))
						{
							redirect($redirect_url);
						}

						$hidden_fields = '<input type="hidden" name="mode[]" value="reasons" /><input type="hidden" name="' . POST_CAT_URL . '" value="' . $module_id . '" />';
						$hidden_fields .= '<input type="hidden" name="mode[]" value="delete" /><input type="hidden" name="' . POST_REPORT_REASON_URL . '" value="' . $reason_id . '" />';

						print_confirmation(array(
							'CONFIRM_TITLE' => $lang['DELETE_REASON'],
							'QUESTION'      => $lang['DELETE_REPORT_REASON_EXPLAIN'],
							'FORM_ACTION'   => "admin_reports.php",
							'HIDDEN_FIELDS' => $hidden_fields,
						));
					break;

					default:
						bb_die($lang['REPORT_NOT_SUPPORTED'] . $return_links['reasons'] . $return_links['admin'] . $return_links['index']);
					break;
				}
			}
			else
			{
				switch ($reason_mode)
				{
					//
					// Add reason
					//
					case 'add':
						$errors = array();

						if (isset($_POST['submit']))
						{
							$reason_desc = (isset($_POST['report_reason_desc'])) ? htmlspecialchars($_POST['report_reason_desc']) : '';

							//
							// Validate reason desc
							//
							if (empty($reason_desc))
							{
								$errors[] = $lang['REASON_DESC_EMPTY'];
							}

							if (empty($errors))
							{
								$reason_desc = str_replace("\'", "'", $reason_desc);

								report_reason_insert($module_id, $reason_desc);

								bb_die($lang['REPORT_REASON_ADDED'] . $return_links['reasons'] . $return_links['admin'] . $return_links['index']);
							}
						}
						else if (isset($_POST['cancel']))
						{
							redirect($redirect_url);
						}

						//
						// Show validation errors
						//
						if (!empty($errors))
						{
							$template->assign_block_vars('switch_report_errors', array());
							foreach ($errors as $error)
							{
								$template->assign_block_vars('switch_report_errors.report_errors', array(
									'MESSAGE' => $error)
								);
							}
						}

						$hidden_fields = '<input type="hidden" name="mode[]" value="reasons" /><input type="hidden" name="' . POST_CAT_URL . '" value="' . $module_id . '" />';
						$hidden_fields .= '<input type="hidden" name="mode[]" value="add" />';

						$template->assign_vars(array(
							'S_HIDDEN_FIELDS' => $hidden_fields,
							'REASON_DESC' => (isset($reason_desc)) ? stripslashes($reason_desc) : '')
						);

						print_page('report_reason_edit_body.tpl', 'admin');
					break;

					case '':

						if ($report_reasons = $report_module->reasons_obtain())
						{
							foreach ($report_reasons as $reason_id => $reason_desc)
							{
								$template->assign_block_vars('report_reasons', array(
									'DESC' => $reason_desc,

									'U_EDIT' => 'admin_reports.php?mode[]=reasons&amp;' . POST_CAT_URL . "=$module_id&amp;mode[]=edit&amp;" . POST_REPORT_REASON_URL . "=$reason_id",
									'U_MOVE_UP' => 'admin_reports.php?mode[]=reasons&amp;' . POST_CAT_URL . "=$module_id&amp;mode[]=up&amp;" . POST_REPORT_REASON_URL . "=$reason_id",
									'U_MOVE_DOWN' => 'admin_reports.php?mode[]=reasons&amp;' . POST_CAT_URL . "=$module_id&amp;mode[]=down&amp;" . POST_REPORT_REASON_URL . "=$reason_id",
									'U_DELETE' => 'admin_reports.php?mode[]=reasons&amp;' . POST_CAT_URL . "=$module_id&amp;mode[]=delete&amp;" . POST_REPORT_REASON_URL . "=$reason_id")
								);
							}
						}
						else
						{
							$template->assign_block_vars('switch_no_reasons', array());
						}

						$template->assign_vars(array(
							'U_ADD_REASON' => 'admin_reports.php?mode[]=reasons&amp;' . POST_CAT_URL . "=$module_id&amp;mode[]=add",
							'U_MODULES'    => "admin_reports.php",
						));

						print_page('report_module_reasons_body.tpl', 'admin');
					break;

					default:
						bb_die($lang['REPORT_NOT_SUPPORTED'] . $return_links['reasons'] . $return_links['admin'] . $return_links['index']);
					break;
				}
			}
		break;

		//
		// Move module up/down
		//
		case 'up':
		case 'down':
			report_module_move($mode, $module_id);

			redirect($redirect_url);
		break;

		//
		// Synchronize module
		//
		case 'sync':
			if (!method_exists($report_module, 'sync'))
			{
				bb_die($lang['REPORT_NOT_SUPPORTED'] . $return_links['admin'] . $return_links['index']);
			}

			$report_module->sync();

			bb_die($lang['REPORT_MODULE_SYNCED'] . $return_links['admin'] . $return_links['index']);
		break;

		//
		// Uninstall module
		//
		case 'uninstall':
			if (isset($_POST['confirm']))
			{
				report_module_uninstall($module_id);

				bb_die($lang['REPORT_MODULE_UNINSTALLED'] . $return_links['admin'] . $return_links['index']);
			}
			else if (isset($_POST['cancel']))
			{
				redirect($redirect_url);
			}

			$hidden_fields = '<input type="hidden" name="mode" value="uninstall" /><input type="hidden" name="' . POST_CAT_URL . '" value="' . $module_id . '" />';

			print_confirmation(array(
				'CONFIRM_TITLE' => $lang['UNINSTALL_REPORT_MODULE'],
				'QUESTION'      => $lang['UNINSTALL_REPORT_MODULE_EXPLAIN'],
				'FORM_ACTION'   => 'admin_reports.php',
				'HIDDEN_FIELDS' => $hidden_fields,
			));
		break;

		default:
			bb_die($lang['REPORT_NOT_SUPPORTED'] . $return_links['admin'] . $return_links['index']);
		break;
	}
}
else if (isset($_POST['module']) || isset($_GET['module']))
{
	$module_name = (isset($_POST['module'])) ? stripslashes($_POST['module']) : stripslashes($_GET['module']);

	if (!$report_module = report_modules_inactive('name', $module_name))
	{
		bb_die($lang['REPORT_MODULE_NOT_EXISTS'] . $return_links['admin'] . $return_links['index']);
	}

	switch ($mode)
	{
		//
		// Install module
		//
		case 'install':
			if (isset($_POST['submit']))
			{
				$module_notify = (isset($_POST['report_module_notify']) && $_POST['report_module_notify'] == 1) ? 1 : 0;
				$module_prune = (isset($_POST['report_module_prune'])) ? (int) $_POST['report_module_prune'] : 0;

				$auth_write = (isset($_POST['auth_write'])) ? (int) $_POST['auth_write'] : REPORT_AUTH_USER;
				$auth_view = (isset($_POST['auth_view'])) ? (int) $_POST['auth_view'] : REPORT_AUTH_MOD;
				$auth_notify = (isset($_POST['auth_notify'])) ? (int) $_POST['auth_notify'] : REPORT_AUTH_MOD;
				$auth_delete = (isset($_POST['auth_delete'])) ? (int) $_POST['auth_delete'] : REPORT_AUTH_ADMIN;

				report_module_install($module_notify, $module_prune, $module_name, $auth_write, $auth_view, $auth_notify, $auth_delete, false);

				bb_die($lang['REPORT_MODULE_INSTALLED'] . $return_links['admin'] . $return_links['index']);
			}
			else if (isset($_POST['cancel']))
			{
				redirect($redirect_url);
			}

			$module_info = $report_module->info();

			$hidden_fields = '<input type="hidden" name="mode" value="install" /><input type="hidden" name="module" value="' . htmlspecialchars($module_name) . '" />';

			$template->assign_vars(array(
				'S_HIDDEN_FIELDS' => $hidden_fields,

				'MODULE_TITLE' => $module_info['title'],
				'MODULE_EXPLAIN' => $module_info['explain'],
				'MODULE_NOTIFY_ON' => ($bb_cfg['report_notify']) ? ' checked="checked"' : '',
				'MODULE_NOTIFY_OFF' => (!$bb_cfg['report_notify']) ? ' checked="checked"' : '',
				'MODULE_PRUNE' => 0,
			));

			//
			// Authorisation selects
			//
			report_auth_select('auth_write', REPORT_AUTH_USER, array(REPORT_AUTH_USER, REPORT_AUTH_MOD, REPORT_AUTH_ADMIN));
			report_auth_select('auth_view', REPORT_AUTH_MOD);
			report_auth_select('auth_notify', REPORT_AUTH_MOD);
			report_auth_select('auth_delete', REPORT_AUTH_CONFIRM, array(REPORT_AUTH_MOD, REPORT_AUTH_CONFIRM, REPORT_AUTH_ADMIN));

			print_page('report_module_edit_body.tpl', 'admin');
		break;

		default:
			bb_die($lang['REPORT_NOT_SUPPORTED'] . $return_links['admin'] . $return_links['index']);
		break;
	}
}
else
{
	switch ($mode)
	{
		case '':
			$report_modules = report_modules();
			$report_counts = report_counts_obtain();
			$report_reason_counts = report_reason_counts_obtain();

			//
			// Display installed modules
			//
			$template->assign_block_vars('installed_modules', array());
			foreach (array_keys($report_modules) as $report_module_id)
			{
				$report_module =& $report_modules[$report_module_id];
				$module_info = $report_module->info();

				$template->assign_block_vars('installed_modules.modules', array(
					'L_REASONS' => sprintf($lang['REASONS'], $report_reason_counts[$report_module->id]),

					'MODULE_TITLE' => $module_info['title'],
					'MODULE_EXPLAIN' => $module_info['explain'],
					'REPORT_COUNT' => $report_counts[$report_module->id],

					'U_EDIT' => 'admin_reports.php?mode=edit&amp;' . POST_CAT_URL . '=' . $report_module->id,
					'U_REASONS' => 'admin_reports.php?mode=reasons&amp;' . POST_CAT_URL . '=' . $report_module->id,
					'U_MOVE_UP' => 'admin_reports.php?mode=up&amp;' . POST_CAT_URL . '=' . $report_module->id,
					'U_MOVE_DOWN' => 'admin_reports.php?mode=down&amp;' . POST_CAT_URL . '=' . $report_module->id,
					'U_SYNC' => 'admin_reports.php?mode=sync&amp;' . POST_CAT_URL . '=' . $report_module->id,
					'U_UNINSTALL' => 'admin_reports.php?mode=uninstall&amp;' . POST_CAT_URL . '=' . $report_module->id)
				);

				//
				// Display sync option if available
				//
				if (method_exists($report_module, 'sync'))
				{
					$template->assign_block_vars('installed_modules.modules.switch_sync', array());
				}
			}

			if (empty($report_modules))
			{
				$template->assign_block_vars('installed_modules.switch_no_modules', array());
			}

			$report_modules_inactive = report_modules_inactive();

			//
			// Display inactive modules
			//
			$template->assign_block_vars('inactive_modules', array());
			foreach (array_keys($report_modules_inactive) as $key)
			{
				$report_module =& $report_modules_inactive[$key];
				$module_info = $report_module->info();

				$template->assign_block_vars('inactive_modules.modules', array(
					'MODULE_TITLE' => $module_info['title'],
					'MODULE_EXPLAIN' => $module_info['explain'],
					'REPORT_COUNT' => '-',

					'U_INSTALL' => 'admin_reports.php?mode=install&amp;module=' . $report_module->data['module_name'])
				);
			}

			if (empty($report_modules_inactive))
			{
				$template->assign_block_vars('inactive_modules.switch_no_modules', array());
			}

			print_page('report_modules_body.tpl', 'admin');
		break;

		default:
			bb_die($lang['REPORT_NOT_SUPPORTED'] . $return_links['admin'] . $return_links['index']);
		break;
	}
}