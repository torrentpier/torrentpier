<?php

// ACP Header - START
if (!empty($setmodules))
{
	$module['TorrentPier']['Cron'] = basename(__FILE__) . '?mode=list';
	return;
}

$i = 0;
$tpl = '';

$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
$job_id = isset($_GET['id']) ? (int) $_GET['id'] : '';

$jobs = isset($_POST['select']) ? implode(',', $_POST['select']) : '';
$cron_action = isset($_POST['cron_action']) ? $_POST['cron_action'] : '';
$cron_enabled = isset($_POST['cron_enabled']) ? $_POST['cron_enabled'] : '';
$cron_check_interval = isset($_POST['cron_check_interval']) ? $_POST['cron_check_interval'] : '';

$submit  = isset($_POST['submit']);
$confirm = isset($_POST['confirm']);


if ($mode == 'run' && !$job_id) {
	define('BB_ROOT', './../');
	require(BB_ROOT.'common.php');
	$user->session_start();
	redirect('admin/'.basename(__FILE__) . '?mode=list');
}
else {
	require('./pagestart.php');
}
// ACP Header - END

require(LANG_DIR .'lang_admin_cron.php');
require(INC_DIR .'functions_admin_torrent.php');
require(INC_DIR .'functions_admin_cron.php');

if ($mode == 'list') {
	$sql = "SELECT *
		FROM ". BB_CRON ."
		ORDER BY cron_id";

	if( !$result1 = DB()->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Could not query cron list", "", __LINE__, __FILE__, $sql);
	}

	while($row = DB()->sql_fetchrow($result1))
	{
		$cron_id = $row['cron_id'];
		$cron_active = $row['cron_active'] ? $lang['YES'] : $lang['NO'];
		$cron_title = $row['cron_title'];
		$cron_script = $row['cron_script'];
		$schedule =  $row['schedule'];
		$leech =  $row['run_day'];
		$row_style = ( !($i % 2) ) ? 'row1' : 'row2';
		$last_run =  $row['last_run'];
		$next_run =  $row['next_run'];
		$run_count =  $row['run_counter'];

		$tpl .= "<tr>";
		$tpl .= "<td width=\"2%\" nowrap=\"nowrap\" class=\"$row_style\" align=\"center\"><input type=\"checkbox\" name=\"select[]\" value=\"$cron_id\"  onclick=\"return CheckCB(this);\"></td>";
		$tpl .= "<td width=\"2%\" nowrap=\"nowrap\" class=\"$row_style\" align=\"center\">$cron_id</td>";
		$tpl .= "<td width=\"3%\" nowrap=\"nowrap\" class=\"$row_style\" align=\"center\">$cron_active</td>";
		$tpl .= "<td width=\"30%\" nowrap=\"nowrap\" class=\"$row_style\" align=\"left\">$cron_title</td>";
		$tpl .= "<td width=\"20%\" nowrap=\"nowrap\" class=\"$row_style\" align=\"left\">$cron_script</td>";
		$tpl .= "<td width=\"5%\" nowrap=\"nowrap\" class=\"$row_style\" align=\"center\"><font color=\"#505050\"><b> <span class='leechmed'> $schedule </span></b></font></td>";
		$tpl .= "<td width=\"5%\" nowrap=\"nowrap\" class=\"$row_style\" align=\"center\"><i> $last_run </i></td>";
		$tpl .= "<td width=\"5%\" nowrap=\"nowrap\" class=\"$row_style\" align=\"center\"><i> $next_run </i></td>";
		$tpl .= "<td width=\"1%\" nowrap=\"nowrap\" class=\"$row_style\" align=\"center\"><font color=\"#505050\"><b><i> <span class='leechmed'>$run_count</span></i></b></font></td>";
		$tpl .= "<td width=\"5%\" nowrap=\"nowrap\" class=\"$row_style\" align=\"center\"><font color=\"#505050\">
		<b>
		<a href='admin_cron.php?mode=run&id=$cron_id'>[Run]</a>
		<a href='admin_cron.php?mode=edit&id=$cron_id'>[Edit]</a>
		<a href='admin_cron.php?mode=delete&id=$cron_id'>[Del]</a>

		</b></font></td>";

		$tpl .= "</tr>";

		$i++;
	}
//	$tpl .= "</table>";
	$template->assign_vars(array(
		'TPL_CRON_LIST' => true,
		'LIST'          => $tpl,
		'S_CRON_ACTION' => append_sid("admin_cron.php"),
		'S_MODE'        => 'list',
		));

	$default_cfg_bool = array(
		'cron_enabled'              => true,
	);
	$default_cfg_num = array(
		'cron_check_interval'       => $bb_cfg['cron_check_interval'],
	);

	$cfg = array_merge($default_cfg_bool, $default_cfg_num);

	set_tpl_vars_bool ($default_cfg_bool, $cfg);
	set_tpl_vars_lang ($default_cfg_bool);

	set_tpl_vars      ($default_cfg_num, $cfg);
	set_tpl_vars_lang ($default_cfg_num);

	//detect cron status
	if (@file_exists('../triggers/cron_running')){
		$template->assign_vars(array(
			'CRON_RUNNING' => true,
		));
	}
	print_page('admin_cron.tpl', 'admin');
}
if ($mode == 'repair') {
	if (@file_exists('../triggers/cron_running')) {
		rename("../triggers/cron_running", "../triggers/cron_allowed");
	}
	redirect('admin/'.basename(__FILE__) . '?mode=list');
}
if (($mode == 'run' && $job_id)) {
	run_jobs($job_id);
	redirect('admin/'.basename(__FILE__) . '?mode=list');
}

if ($mode == 'edit' && $job_id) {
	$sql = "SELECT *
		FROM ". BB_CRON ."
		WHERE cron_id = $job_id";

	if( !$result = DB()->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Could not query cron", "", __LINE__, __FILE__, $sql);
	}

	while($row = DB()->sql_fetchrow($result))
	{
		$cron_id = $row['cron_id'];
		$cron_active = $row['cron_active'];
		$cron_title = $row['cron_title'];
		$cron_script = $row['cron_script'];
		$schedule = $row['schedule'];
		$run_day = $row['run_day'];
		$run_time = $row['run_time'];
		$run_order = $row['run_order'];
		$last_run = $row['last_run'];
		$next_run = $row['next_run'];
		$run_interval = $row['run_interval'];
		$log_enabled = $row['log_enabled'];
		$log_file = $row['log_file'];
		$log_sql_queries = $row['log_sql_queries'];
		$disable_board = $row['disable_board'];
		$run_counter = $row['run_counter'];

	}
	//build schedule html
	$schedule_html = '<select name="schedule"   tabindex="13">
                <option value=""></option>
                <option value="hourly">'.$lang['HOURLY'].'</option>
                <option value="daily" >'.$lang['DAILY'].'</option>
                <option value="weekly">'.$lang['WEEKLY'].'</option>
                <option value="monthly">'.$lang['MONTHLY'].'</option>
                <option value="interval">'.$lang['INTERVAL'].'</option>
    </select>';
	$schedule_option = 'value="'.$schedule.'"';
	$schedule_selected = 'value="'.$schedule.'" selected="selected"';
	$schedule_result = str_replace( $schedule_option, $schedule_selected,  $schedule_html);
	//build run_day html
	$run_day_html = '<select name="run_day" width="100px" tabindex="13"><option value=""></option>';
	for ($i = 1; $i <= 28; $i++) {
		if($run_day == $i) {
			$run_day_html .= '<option value="'.$i.'" selected="selected">'.$i.'</option>' ;
		}
		else {
			$run_day_html .= '<option value="'.$i.'">'.$i.'</option>' ;
		}
	}
	$run_day_html .= '</select>';
	//
	$template->assign_vars(array(
		'TPL_CRON_EDIT'     => true,
		'S_CRON_ACTION'     => append_sid("admin_cron.php"),
		'S_MODE'            => 'edit',
		'SCHEDULE'          => $schedule_result,
		'RUN_DAY'           => $run_day_html,
		'L_CRON_EDIT_HEAD'  => $lang['CRON_EDIT_HEAD_EDIT'],
	));


	$default_cfg_str = array(
		'cron_title'                   => $cron_title,
		'cron_script'                  => $cron_script,
		'run_time'                     => $run_time,
		'last_run'                     => $last_run,
		'next_run'                     => $next_run,
		'run_interval'                 => $run_interval,
		'log_file'                     => $log_file,
	);
	$default_cfg_bool = array(
		'cron_active'              => $cron_active,
		'log_enabled'              => $log_enabled,
		'log_sql_queries'          => $log_sql_queries,
		'disable_board'            => $disable_board,
	);
	$default_cfg_num = array(
		'cron_id'                      => $cron_id,
		'run_order'                    => $run_order,
		'run_counter'                  => $run_counter,
	);

	$cfg = array_merge($default_cfg_str, $default_cfg_bool, $default_cfg_num);

	set_tpl_vars      ($default_cfg_str, $cfg);
	set_tpl_vars_lang ($default_cfg_str);

	set_tpl_vars_bool ($default_cfg_bool, $cfg);
	set_tpl_vars_lang ($default_cfg_bool);

	set_tpl_vars      ($default_cfg_num, $cfg);
	set_tpl_vars_lang ($default_cfg_num);

	print_page('admin_cron.tpl', 'admin');
}
if ($mode == 'add') {

		$cron_id = 'none';
		$cron_active = 1;
		$cron_title = '';
		$cron_script = '';
		$schedule = '';
		$run_day = '';
		$run_time = '';
		$run_order = 255;
		$last_run = '0000-00-00 00:00:00';
		$next_run = '0000-00-00 00:00:00';
		$run_interval = '';
		$log_enabled = 0;
		$log_file = '';
		$log_sql_queries = 0;
		$disable_board = 0;
		$run_counter = 0;


	//build schedule html
	$schedule_html = '<select name="schedule"   tabindex="13">
                <option value=""></option>
                <option value="hourly">'.$lang['HOURLY'].'</option>
                <option value="daily" >'.$lang['DAILY'].'</option>
                <option value="weekly">'.$lang['WEEKLY'].'</option>
                <option value="monthly">'.$lang['MONTHLY'].'</option>
                <option value="interval">'.$lang['INTERVAL'].'</option>
    </select>';
	$schedule_option = 'value="'.$schedule.'"';
	$schedule_selected = 'value="'.$schedule.'" selected="selected"';
	$schedule_result = str_replace( $schedule_option, $schedule_selected,  $schedule_html);
	//build run_day html
	$run_day_html = '<select name="run_day" width="100px" tabindex="13"><option value=""></option>';
	for ($i = 1; $i <= 28; $i++) {
		if($run_day == $i) {
			$run_day_html .= '<option value="'.$i.'" selected="selected">'.$i.'</option>' ;
		}
		else {
			$run_day_html .= '<option value="'.$i.'">'.$i.'</option>' ;
		}
	}
	$run_day_html .= '</select>';
	//
	$template->assign_vars(array(
		'TPL_CRON_EDIT'     => true,
		'S_CRON_ACTION'     => append_sid("admin_cron.php"),
		'S_MODE'            => 'add',
		'SCHEDULE'          => $schedule_result,
		'RUN_DAY'           => $run_day_html,
		'L_CRON_EDIT_HEAD'  => $lang['CRON_EDIT_HEAD_ADD'],
	));


	$default_cfg_str = array(
		'cron_title'                   => $cron_title,
		'cron_script'                  => $cron_script,
		'run_time'                     => $run_time,
		'last_run'                     => $last_run,
		'next_run'                     => $next_run,
		'run_interval'                 => $run_interval,
		'log_file'                     => $log_file,
	);
	$default_cfg_bool = array(
		'cron_active'              => $cron_active,
		'log_enabled'              => $log_enabled,
		'log_sql_queries'          => $log_sql_queries,
		'disable_board'            => $disable_board,
	);
	$default_cfg_num = array(
		'cron_id'                      => $cron_id,
		'run_order'                    => $run_order,
		'run_counter'                  => $run_counter,
	);

	$cfg = array_merge($default_cfg_str, $default_cfg_bool, $default_cfg_num);

	set_tpl_vars      ($default_cfg_str, $cfg);
	set_tpl_vars_lang ($default_cfg_str);

	set_tpl_vars_bool ($default_cfg_bool, $cfg);
	set_tpl_vars_lang ($default_cfg_bool);

	set_tpl_vars      ($default_cfg_num, $cfg);
	set_tpl_vars_lang ($default_cfg_num);

	print_page('admin_cron.tpl', 'admin');
}

if ($mode == 'delete' && $job_id) {
	if (isset($_GET['confirm'])) {
		delete_jobs($job_id);
		redirect('admin/'.basename(__FILE__) . '?mode=list');
	}
	else {
		message_die(GENERAL_MESSAGE, "Are you sure? <br /> <a href='admin_cron.php?mode=delete&id=$job_id&confirm'>Delete</a> <a href='javascript:history.back(-1)'>Back</a>");
	}
}

if ($submit && $confirm) {
	if ($_POST['mode'] == 'list') {
		if ($cron_action == 'run' && $jobs) {
			run_jobs($jobs);
		}
		else if ($cron_action == 'delete' && $jobs) {
			delete_jobs($jobs);
		}
		else if (($cron_action == 'disable' || $cron_action == 'enable') && $jobs) {
			toggle_active($jobs, $cron_action);
		}
		$bb_cfg_options = array(
			'cron_enabled',
			'cron_check_interval'
		);
		foreach ($bb_cfg_options as $option) {
			update_config_php($option, $_POST[$option]);
		}
		redirect('admin/'.basename(__FILE__) . '?mode=list');
	}
	else if (validate_cron_post($_POST) == 1) {
		if($_POST['mode'] == 'edit') {
			update_cron_job($_POST);
		}
		else if ($_POST['mode'] == 'add'){
			insert_cron_job($_POST);
		}
		else {
			bb_die();
		}
		redirect('admin/'.basename(__FILE__) . '?mode=list');
	}
	else {
		$message = validate_cron_post($_POST);
		message_die(GENERAL_MESSAGE, $message);
	}

}