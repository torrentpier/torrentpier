<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

//
// Functions
//
function cron_get_file_lock ()
{
	$lock_obtained = false;

	if (file_exists(CRON_ALLOWED))
	{
#		bb_log(date('H:i:s - ') . getmypid() .' -x-- FILE-LOCK try'. LOG_LF, CRON_LOG_DIR .'cron_check');

		$lock_obtained = @rename(CRON_ALLOWED, CRON_RUNNING);
	}
	elseif (file_exists(CRON_RUNNING))
	{
		cron_release_deadlock();
	}
	elseif (!file_exists(CRON_ALLOWED) && !file_exists(CRON_RUNNING))
	{
		file_write('', CRON_ALLOWED);
		$lock_obtained = @rename(CRON_ALLOWED, CRON_RUNNING);
	}

	return $lock_obtained;
}

function cron_track_running ($mode)
{
	@define('CRON_STARTMARK', TRIGGERS_DIR .'cron_started_at_'. date('Y-m-d_H-i-s') .'_by_pid_'. getmypid());

	if ($mode == 'start')
	{
		cron_touch_lock_file(CRON_RUNNING);
		file_write('', CRON_STARTMARK);
	}
	elseif ($mode == 'end')
	{
		@unlink(CRON_STARTMARK);
	}
}

//
// Run cron
//
if (cron_get_file_lock())
{
	ignore_user_abort(true);
	register_shutdown_function('cron_release_file_lock');
	register_shutdown_function('cron_enable_board');

#	bb_log(date('H:i:s - ') . getmypid() .' --x- FILE-LOCK OBTAINED ###############'. LOG_LF, CRON_LOG_DIR .'cron_check');

	cron_track_running('start');

	require(CRON_DIR .'cron_check.php');

	cron_track_running('end');
}

if (defined('IN_CRON'))
{
	bb_log(date('H:i:s - ') . getmypid() .' --x- ALL jobs FINISHED *************************************************'. LOG_LF, CRON_LOG_DIR .'cron_check');
}