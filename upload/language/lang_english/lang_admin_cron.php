<?php

$lang['CRON_LIST'] = 'Cron list';
$lang['CRON_ID'] = 'ID';
$lang['CRON_ACTIVE'] = 'On';
$lang['CRON_ACTIVE_EXPL'] = 'Active tasks';
$lang['CRON_TITLE'] = 'Title';
$lang['CRON_SCRIPT'] = 'Script';
$lang['CRON_SCHEDULE'] = 'Schedule';
$lang['CRON_LAST_RUN'] = 'Last Run';
$lang['CRON_NEXT_RUN'] = 'Next Run';
$lang['CRON_RUN_COUNT'] = 'Runs';
$lang['CRON_MANAGE'] = 'Manage';
$lang['CRON_OPTIONS'] = 'Cron options';

$lang['CRON_ENABLED'] = 'Cron enabled';
$lang['CRON_CHECK_INTERVAL'] = 'Cron check interval (sec)';

$lang['WITH_SELECTED'] = 'With selected';
$lang['NOTHING'] = 'do nothing';
$lang['CRON_RUN'] = 'Run';
$lang['CRON_DEL'] = 'Delete';
$lang['CRON_DISABLE'] = 'Disable';
$lang['CRON_ENABLE'] = 'Enable';

$lang['CRON_WORKS'] = 'Cron is now works or is broken -> ';
$lang['REPAIR_CRON'] = 'Repair Cron';

$lang['CRON_EDIT_HEAD_EDIT'] = 'Edit job';
$lang['CRON_EDIT_HEAD_ADD'] = 'Add job';
$lang['CRON_SCRIPT_EXPL'] = 'name of the script from "includes/cron/jobs/"';;
$lang['SCHEDULE'] = array(
    'select'   => '&raquo; Select start',
    'hourly'   => 'hourly',
	'daily'    => 'daily',
	'weekly'   => 'weekly',
	'monthly'  => 'monthly',
	'interval' => 'interval'
);
$lang['NOSELECT'] = 'No select';
$lang['RUN_DAY'] = 'Run day';
$lang['RUN_DAY_EXPL'] = 'the day when this job run';
$lang['RUN_TIME'] = 'Run time';
$lang['RUN_TIME_EXPL'] = 'the time when this job run (e.g. 05:00:00)';
$lang['RUN_ORDER'] = 'Run order';
$lang['LAST_RUN'] = 'Last Run';
$lang['NEXT_RUN'] = 'Next Run';
$lang['RUN_INTERVAL'] = 'Run interval';
$lang['RUN_INTERVAL_EXPL'] = 'e.g. 00:10:00';
$lang['LOG_ENABLED'] = 'Log enabled';
$lang['LOG_FILE'] = 'Log file';
$lang['LOG_FILE_EXPL'] = 'the file for save the log';
$lang['LOG_SQL_QUERIES'] = 'Log SQL queries';
$lang['DISABLE_BOARD'] = 'Disable board';
$lang['DISABLE_BOARD_EXPL'] = 'disable board when this job is run';
$lang['RUN_COUNTER'] = 'Run counter';

$lang['JOB_REMOVED'] = 'The problem was successfully removed';
$lang['SCRIPT_DUPLICATE'] = 'Script <b>'. @$_POST['cron_script'] .'</b> already exists!';
$lang['TITLE_DUPLICATE'] = 'Task Name <b>'. @$_POST['cron_title'] .'</b> already exists!';
$lang['CLICK_RETURN_JOBS_ADDED'] = '%sReturn to the addition problem%s';
$lang['CLICK_RETURN_JOBS'] = '%sBack to the Task Scheduler%s';