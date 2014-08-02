<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$exec_output = array();
$exec_return_status = 0;

if ($bb_cfg['site_backup_shell_cmd'])
{
	exec($bb_cfg['site_backup_shell_cmd'], $exec_output, $exec_return_status);
	$cron_runtime_log = join(LOG_LF, $exec_output) . LOG_LF;
}

if ($exec_return_status && !$bb_cfg['emailer_disabled'] && $bb_cfg['tech_admin_email'])
{
	require_once(INC_DIR .'emailer.class.php');
	$emailer = new emailer($bb_cfg['smtp_delivery']);

	$emailer->from($bb_cfg['sitename'] ." <{$bb_cfg['board_email']}>");
	$emailer->replyto($bb_cfg['sitename'] ." <{$bb_cfg['board_email']}>");

	$emailer->use_template('blank');
	$emailer->email_address($bb_cfg['tech_admin_email']);
	$emailer->set_subject("Site Backup failed [{$bb_cfg['server_name']}]");

	$emailer->assign_vars(array(
		'MESSAGE' => $cron_runtime_log,
	));
	$emailer->send();
	$emailer->reset();
}

sleep(10);