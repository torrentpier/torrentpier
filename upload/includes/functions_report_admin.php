<?php

if (!defined('BB_ROOT')) {
	die(basename(__FILE__));
}

//
// Obtains report count for each report module
//
function report_counts_obtain()
{
	$sql = 'SELECT rm.report_module_id, COUNT(r.report_id) AS report_count
		FROM ' . BB_REPORTS_MODULES . ' rm
		LEFT JOIN ' . BB_REPORTS . ' r
			ON r.report_module_id = rm.report_module_id
		GROUP BY rm.report_module_id';
	if (!$result = DB()->sql_query($sql)) {
		bb_die('Could not obtain report counts');
	}

	$report_counts = array();
	while ($row = DB()->sql_fetchrow($result)) {
		$report_counts[$row['report_module_id']] = $row['report_count'];
	}
	DB()->sql_freeresult($result);

	return $report_counts;
}

//
// Obtains report reason count for each report module
//
function report_reason_counts_obtain()
{
	$sql = 'SELECT rm.report_module_id, COUNT(rr.report_reason_id) AS reason_count
		FROM ' . BB_REPORTS_MODULES . ' rm
		LEFT JOIN ' . BB_REPORTS_REASONS . ' rr
			ON rr.report_module_id = rm.report_module_id
		GROUP BY rm.report_module_id';
	if (!$result = DB()->sql_query($sql)) {
		bb_die('Could not obtain report reason counts');
	}

	$report_reason_counts = array();
	while ($row = DB()->sql_fetchrow($result)) {
		$report_reason_counts[$row['report_module_id']] = $row['reason_count'];
	}
	DB()->sql_freeresult($result);

	return $report_reason_counts;
}

//
// Obtains inactive report modules, includes modules and stores module objects
//
function report_modules_inactive($mode = 'all', $module = null)
{
	global $bb_cfg;
	static $modules;

	if (!isset($modules)) {
		if (!class_exists("report_module"))
			include(INC_DIR . "report_module.php");

		$installed_modules = report_modules('names');

		$deny_modes = array('open', 'process', 'clear', 'delete', 'reported');

		$dir = @opendir(INC_DIR . 'report_hack');

		$modules = array();

		while ($file = @readdir($dir)) {
			if (!preg_match('#(.*)\.' . bb_preg_quote('php', '#') . '$#', $file, $matches)) {
				continue;
			}

			// exclude installed modules
			$module_name = $matches[1];
			if (isset($installed_modules[$module_name])) {
				continue;
			}

			// include module file
			include(INC_DIR . "report_hack/$file");

			// Include language file
			$lang = array();

			$lang_file = LANG_ROOT_DIR . "{$bb_cfg['default_lang']}/report_hack/$module_name.php";
			if (file_exists($lang_file)) {
				include($lang_file);
			}

			// Create module object
			$modules[$module_name] = new $module_name(0, array('module_name' => $module_name), $lang);

			//
			// Check validity of the module
			//
			if (!empty($modules[$module_name]->mode) && in_array($modules[$module_name]->mode, $deny_modes)) {
				unset($modules[$module_name]);
			}
			if (!isset($modules[$module_name]->id) || !isset($modules[$module_name]->data) || !isset($modules[$module_name]->lang) || !isset($modules[$module_name]->duplicates)) {
				unset($modules[$module_name]);
			}
		}

		@closedir($dir);
	}

	switch ($mode) {
		case 'all':
			return $modules;
			break;

		case 'name':
			if (!isset($module)) {
				return false;
			}

			return (isset($modules[$module])) ? $modules[$module] : false;
			break;

		default:
			return false;
			break;
	}
}

//
// Generates the auth select box
//
function report_auth_select($block_name, $default, $select_items = array(REPORT_AUTH_MOD, REPORT_AUTH_ADMIN))
{
	global $lang, $template;

	foreach ($select_items as $value) {
		$template->assign_block_vars($block_name, array(
				'VALUE' => $value,
				'TITLE' => $lang['REPORT_AUTH'][$value],
				'SELECTED' => ($value == $default) ? ' selected="selected"' : '')
		);
	}
}

//
// Installs a report module
//
function report_module_install($module_notify, $module_prune, $module_name, $auth_write, $auth_view, $auth_notify, $auth_delete, $check = true)
{
	global $bb_cfg;

	//
	// Check module
	//
	if ($check) {
		if (!$report_module = report_modules_inactive('name', $module_name)) {
			return false;
		}
	}

	//
	// Get module order
	//
	$sql = 'SELECT MAX(report_module_order) AS max_order
		FROM ' . BB_REPORTS_MODULES;
	if (!$result = DB()->sql_query($sql)) {
		bb_die('Could not obtain max order #1');
	}

	$max_order = DB()->sql_fetchfield('max_order', 0, $result);
	DB()->sql_freeresult($result);

	//
	// Insert module
	//
	$sql = 'INSERT INTO ' . BB_REPORTS_MODULES . ' (report_module_order, report_module_notify, report_module_prune,
		report_module_name, auth_write, auth_view, auth_notify, auth_delete)
		VALUES(' . ($max_order + 1) . ', ' . (int)$module_notify . ', ' . (int)$module_prune . ",
			'" . DB()->escape($module_name) . "', " . (int)$auth_write . ', ' . (int)$auth_view . ',
			' . (int)$auth_notify . ', ' . (int)$auth_delete . ')';
	if (!DB()->sql_query($sql)) {
		bb_die('Could not install report module');
	}

	$module_id = DB()->sql_nextid();

	//
	// Clean modules cache
	//
	if ($bb_cfg['report_modules_cache']) {
		report_modules_cache_clean();
	}

	return $module_id;
}

//
// Edits a module
//
function report_module_edit($module_id, $module_notify, $module_prune, $auth_write, $auth_view, $auth_notify, $auth_delete)
{
	global $bb_cfg;

	$sql = 'UPDATE ' . BB_REPORTS_MODULES . '
		SET
			report_module_notify = ' . (int)$module_notify . ',
			report_module_prune = ' . (int)$module_prune . ',
			auth_write = ' . (int)$auth_write . ',
			auth_view = ' . (int)$auth_view . ',
			auth_notify = ' . (int)$auth_notify . ',
			auth_delete = ' . (int)$auth_delete . '
		WHERE report_module_id = ' . (int)$module_id;
	if (!DB()->sql_query($sql)) {
		bb_die('Could not edit report module');
	}

	//
	// Clean modules cache
	//
	if ($bb_cfg['report_modules_cache']) {
		report_modules_cache_clean();
	}
}

//
// Moves a module to another position (up or down), reorders other modules
//
function report_module_move($mode, $module_id, $steps = 1)
{
	global $bb_cfg;

	if (!$report_module = report_modules('id', $module_id)) {
		return false;
	}

	switch ($mode) {
		case 'up':
			$sql = 'UPDATE ' . BB_REPORTS_MODULES . "
				SET report_module_order = report_module_order + 1
				WHERE report_module_order >= " . ($report_module->data['report_module_order'] - (int)$steps) . '
					AND report_module_order < ' . $report_module->data['report_module_order'];
			break;

		case 'down':
			$sql = 'UPDATE ' . BB_REPORTS_MODULES . "
				SET report_module_order = report_module_order - 1
				WHERE report_module_order <= " . ($report_module->data['report_module_order'] + (int)$steps) . '
					AND report_module_order > ' . $report_module->data['report_module_order'];
			break;

		default:
			return false;
			break;
	}

	if (!DB()->sql_query($sql)) {
		bb_die('Could not update module order #1');
	}

	if (DB()->affected_rows()) {
		$op = ($mode == 'up') ? '-' : '+';
		$sql = 'UPDATE ' . BB_REPORTS_MODULES . "
			SET report_module_order = report_module_order $op 1
			WHERE report_module_id = " . (int)$module_id;
		if (!DB()->sql_query($sql)) {
			bb_die('Could not update module order #2');
		}
	}

	DB()->sql_query('');

	//
	// Clean modules cache
	//
	if ($bb_cfg['report_modules_cache']) {
		report_modules_cache_clean();
	}

	return true;
}

//
// Uninstalls a report module
//
function report_module_uninstall($module_id)
{
	global $bb_cfg;

	//
	// Obtain reports in this module
	//
	$sql = 'SELECT report_id
		FROM ' . BB_REPORTS . '
		WHERE report_module_id = ' . (int)$module_id;
	if (!$result = DB()->sql_query($sql)) {
		bb_die('Could not obtain report ids');
	}

	$report_ids = array();
	while ($row = DB()->sql_fetchrow($result)) {
		$report_ids = $row['report_id'];
	}
	DB()->sql_freeresult($result);

	// delete reports
	reports_delete($report_ids, false, false);

	//
	// Sync module
	//
	$report_module = report_modules('id', $module_id);
	if (method_exists($report_module, 'sync')) {
		$report_module->sync(true);
	}

	//
	// Update module order
	//
	$sql = 'UPDATE ' . BB_REPORTS_MODULES . '
		SET report_module_order = report_module_order - 1
		WHERE report_module_order > ' . $report_module->data['report_module_order'];
	if (!DB()->sql_query($sql)) {
		bb_die('Could not update module order #3');
	}

	//
	// Delete report reasons
	//
	$sql = 'DELETE FROM ' . BB_REPORTS_REASONS . '
		WHERE report_module_id = ' . (int)$module_id;
	if (!DB()->sql_query($sql)) {
		bb_die('Could not delete report reason #1');
	}

	//
	// Delete module
	//
	$sql = 'DELETE FROM ' . BB_REPORTS_MODULES . '
		WHERE report_module_id = ' . (int)$module_id;
	if (!DB()->sql_query($sql)) {
		bb_die('Could not delete report module');
	}

	//
	// Clean modules cache
	//
	if ($bb_cfg['report_modules_cache']) {
		report_modules_cache_clean();
	}
}

//
// Obtains a report reason
//
function report_reason_obtain($reason_id)
{
	$sql = 'SELECT report_reason_desc
		FROM ' . BB_REPORTS_REASONS . '
		WHERE report_reason_id = ' . (int)$reason_id;
	if (!$result = DB()->sql_query($sql)) {
		bb_die('Could not obtain report reason #1');
	}

	$row = DB()->sql_fetchrow($result);
	DB()->sql_freeresult($result);

	return $row;
}

//
// Inserts a report reason
//
function report_reason_insert($module_id, $reason_desc)
{
	//
	// Get reason order
	//
	$sql = 'SELECT MAX(report_reason_order) AS max_order
		FROM ' . BB_REPORTS_REASONS;
	if (!$result = DB()->sql_query($sql)) {
		bb_die('Could not obtain max order #2');
	}

	$max_order = DB()->sql_fetchfield('max_order', 0, $result);
	DB()->sql_freeresult($result);

	//
	// Insert reason
	//
	$sql = 'INSERT INTO ' . BB_REPORTS_REASONS . ' (report_module_id, report_reason_order, report_reason_desc)
		VALUES(' . (int)$module_id . ', ' . ($max_order + 1) . ", '" . DB()->escape($reason_desc) . "')";
	if (!DB()->sql_query($sql)) {
		bb_die('Could not insert report reason');
	}

	return DB()->sql_nextid();
}

//
// Edits a report reason
//
function report_reason_edit($reason_id, $module_id, $reason_desc)
{
	$sql = 'UPDATE ' . BB_REPORTS_REASONS . '
		SET
			report_module_id = ' . (int)$module_id . ",
			report_reason_desc = '" . DB()->escape($reason_desc) . "'
		WHERE report_reason_id = " . (int)$reason_id;
	if (!DB()->sql_query($sql)) {
		bb_die('Could not update report reason');
	}
}

//
// Moves a report reason to another position (up or down), reorders other report reasons
//
function report_reason_move($mode, $reason_id, $steps = 1)
{
	//
	// Obtain report reason information
	//
	$sql = 'SELECT report_module_id, report_reason_order
		FROM ' . BB_REPORTS_REASONS . '
		WHERE report_reason_id = ' . (int)$reason_id;
	if (!$result = DB()->sql_query($sql)) {
		bb_die('Could not obtain report reason #3');
	}

	$row = DB()->sql_fetchrow($result);
	DB()->sql_freeresult($result);

	if (!$row) {
		return false;
	}

	switch ($mode) {
		case 'up':
			$sql = 'UPDATE ' . BB_REPORTS_REASONS . '
				SET report_reason_order = report_reason_order + 1
				WHERE report_module_id = ' . $row['report_module_id'] . '
					AND report_reason_order >= ' . ($row['report_reason_order'] - (int)$steps) . '
					AND report_reason_order < ' . $row['report_reason_order'];
			break;

		case 'down':
			$sql = 'UPDATE ' . BB_REPORTS_REASONS . '
				SET report_reason_order = report_reason_order - 1
				WHERE report_module_id = ' . $row['report_module_id'] . '
					AND report_reason_order <= ' . ($row['report_reason_order'] + (int)$steps) . '
					AND report_reason_order > ' . $row['report_reason_order'];
			break;

		default:
			return false;
			break;
	}

	if (!DB()->sql_query($sql)) {
		bb_die('Could not update report reason order #1');
	}

	if (DB()->affected_rows()) {
		$op = ($mode == 'up') ? '-' : '+';
		$sql = 'UPDATE ' . BB_REPORTS_REASONS . "
			SET report_reason_order = report_reason_order $op 1
			WHERE report_reason_id = " . (int)$reason_id;
		if (!DB()->sql_query($sql)) {
			bb_die('Could not update report reason order #2');
		}
	}

	DB()->sql_query('');

	return true;
}

//
// Deletes a report reason
//
function report_reason_delete($reason_id)
{
	//
	// Obtain report reason information
	//
	$sql = 'SELECT report_module_id, report_reason_order
		FROM ' . BB_REPORTS_REASONS . '
		WHERE report_reason_id = ' . (int)$reason_id;
	if (!$result = DB()->sql_query($sql)) {
		bb_die('Could not obtain report reason #3');
	}

	$row = DB()->sql_fetchrow($result);
	DB()->sql_freeresult($result);

	if (!$row) {
		return;
	}

	//
	// Update report reason order
	//
	$sql = 'UPDATE ' . BB_REPORTS_REASONS . '
		SET report_reason_order = report_reason_order - 1
		WHERE report_module_id = ' . $row['report_module_id'] . '
			AND report_reason_order > ' . $row['report_reason_order'];
	if (!DB()->sql_query($sql)) {
		bb_die('Could not update report reason order #3');
	}

	//
	// Delete report reason
	//
	$sql = 'DELETE FROM ' . BB_REPORTS_REASONS . '
		WHERE report_reason_id = ' . (int)$reason_id;
	if (!DB()->sql_query($sql)) {
		bb_die('Could not delete report reason #2');
	}
}