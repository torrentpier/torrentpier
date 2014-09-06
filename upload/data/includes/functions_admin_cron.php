<?php

function run_jobs($jobs)
{
	global $bb_cfg, $tr_cfg, $datastore;

	define('IN_CRON', true);

	$sql = "SELECT cron_script FROM " . BB_CRON ." WHERE cron_id IN ($jobs)";
	if (!$result = DB()->sql_query($sql))
	{
		bb_die('Could not obtain cron script');
	}

	while ($row = DB()->sql_fetchrow($result))
	{
		$job = $row['cron_script'];
		$job_script = INC_DIR . 'cron/jobs/' . $job;
		require($job_script);
	}
	DB()->query("
			UPDATE ". BB_CRON ." SET
				last_run = NOW(),
				run_counter = run_counter + 1,
				next_run =
			CASE
				WHEN schedule = 'hourly' THEN
					DATE_ADD(NOW(), INTERVAL 1 HOUR)
				WHEN schedule = 'daily' THEN
					DATE_ADD(DATE_ADD(CURDATE(), INTERVAL 1 DAY), INTERVAL TIME_TO_SEC(run_time) SECOND)
				WHEN schedule = 'weekly' THEN
					DATE_ADD(
						DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(NOW()) DAY), INTERVAL 7 DAY),
					INTERVAL CONCAT(ROUND(run_day-1), ' ', run_time) DAY_SECOND)
				WHEN schedule = 'monthly' THEN
					DATE_ADD(
						DATE_ADD(DATE_SUB(CURDATE(), INTERVAL DAYOFMONTH(NOW())-1 DAY), INTERVAL 1 MONTH),
					INTERVAL CONCAT(ROUND(run_day-1), ' ', run_time) DAY_SECOND)
				ELSE
					DATE_ADD(NOW(), INTERVAL TIME_TO_SEC(run_interval) SECOND)
			END
			WHERE cron_id IN ($jobs)
		");
	sleep(3);
	return;
}

function delete_jobs($jobs)
{
	DB()->query("DELETE FROM " . BB_CRON . " WHERE cron_id IN ($jobs)");
	return;
}

function toggle_active($jobs, $cron_action)
{
	$active = ($cron_action == 'disable') ? 0 : 1;
	DB()->query("UPDATE " . BB_CRON . " SET cron_active = $active WHERE cron_id IN ($jobs)");
	return;
}

function validate_cron_post($cron_arr) {
	$errors = 'Errors in: ';
	$errnum = 0;
	if (!$cron_arr['cron_title']){
		$errors .= 'cron title (empty value), ';
		$errnum++;
	}
	if (!$cron_arr['cron_script']){
		$errors .= 'cron script (empty value), ';
		$errnum++;
	}
	if ($errnum > 0){
		$result = $errors . ' total ' . $errnum . ' errors <br/> <a href="javascript:history.back(-1)">Back</a>';
	}
	else {
		$result = 1;
	}
	return $result;
}

function insert_cron_job($cron_arr)
{
	$row = DB()->fetch_row("SELECT cron_title, cron_script FROM ". BB_CRON ." WHERE cron_title = '". $_POST['cron_title'] ."' or cron_script = '". $_POST['cron_script'] ."' ");

	if ($row)
	{
		global $lang;

		if ($_POST['cron_script'] == $row['cron_script'])
		{
			$langmode = $lang['SCRIPT_DUPLICATE'];
		}
		else $langmode = $lang['TITLE_DUPLICATE'];

		$message = $langmode . "<br /><br />" . sprintf($lang['CLICK_RETURN_JOBS_ADDED'], "<a href=\"javascript:history.back(-1)\">", "</a>") . "<br /><br />" . sprintf($lang['CLICK_RETURN_JOBS'], "<a href=\"admin_cron.php?mode=list\">", "</a>") . "<br /><br />" . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], "<a href=\"index.php?pane=right\">", "</a>");

		bb_die($message);
	}

	$cron_active = $cron_arr['cron_active'];
	$cron_title = $cron_arr['cron_title'];
	$cron_script = $cron_arr['cron_script'];
	$schedule = $cron_arr['schedule'];
	$run_day = $cron_arr['run_day'];
	$run_time = $cron_arr['run_time'];
	$run_order = $cron_arr['run_order'];
	$last_run = $cron_arr['last_run'];
	$next_run = $cron_arr['next_run'];
	$run_interval = $cron_arr['run_interval'];
	$log_enabled = $cron_arr['log_enabled'];
	$log_file = $cron_arr['log_file'];
	$log_sql_queries = $cron_arr['log_sql_queries'];
	$disable_board = $cron_arr['disable_board'];
	$run_counter = $cron_arr['run_counter'];

	DB()->query("INSERT INTO ". BB_CRON ." (cron_active, cron_title, cron_script, schedule, run_day, run_time, run_order, last_run, next_run, run_interval, log_enabled, log_file, log_sql_queries, disable_board, run_counter) VALUES (
	$cron_active, '$cron_title', '$cron_script', '$schedule', '$run_day', '$run_time', '$run_order', '$last_run', '$next_run', '$run_interval', $log_enabled, '$log_file', $log_sql_queries, $disable_board, '$run_counter')");
}

function update_cron_job($cron_arr)
{
	$cron_id = $cron_arr['cron_id'];
	$cron_active = $cron_arr['cron_active'];
	$cron_title = DB()->escape($cron_arr['cron_title']);
	$cron_script = DB()->escape($cron_arr['cron_script']);
	$schedule = $cron_arr['schedule'];
	$run_day = $cron_arr['run_day'];
	$run_time = $cron_arr['run_time'];
	$run_order = $cron_arr['run_order'];
	$last_run = $cron_arr['last_run'];
	$next_run = $cron_arr['next_run'];
	$run_interval = $cron_arr['run_interval'];
	$log_enabled = $cron_arr['log_enabled'];
	$log_file = DB()->escape($cron_arr['log_file']);
	$log_sql_queries = $cron_arr['log_sql_queries'];
	$disable_board = $cron_arr['disable_board'];
	$run_counter = $cron_arr['run_counter'];

	DB()->query("UPDATE " . BB_CRON . " SET
		cron_active = '$cron_active',
		cron_title = '$cron_title',
		cron_script = '$cron_script',
		schedule = '$schedule',
		run_day = '$run_day',
		run_time = '$run_time',
		run_order = '$run_order',
		last_run = '$last_run',
		next_run = '$next_run',
		run_interval = '$run_interval',
		log_enabled = '$log_enabled',
		log_file = '$log_file',
		log_sql_queries = '$log_sql_queries',
		disable_board = '$disable_board',
		run_counter = '$run_counter'
	WHERE cron_id = $cron_id
	");
}