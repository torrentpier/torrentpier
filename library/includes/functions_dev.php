<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

function get_sql_log ()
{
	global $DBS, $sphinx, $datastore;

	$log = '';

	foreach ($DBS->srv as $srv_name => $db_obj)
	{
		$log .= !empty($db_obj) ? get_sql_log_html($db_obj, "$srv_name [MySQL]") : '';
	}

	$log .= !empty($sphinx) ? get_sql_log_html($sphinx, '$sphinx') : '';

	if (!empty($datastore->db->dbg))
	{
		$log .= get_sql_log_html($datastore->db, 'cache: datastore ['.$datastore->engine.']');
	}
	else if(!empty($datastore->dbg))
	{
		$log .= get_sql_log_html($datastore, 'cache: datastore ['.$datastore->engine.']');
	}

	return $log;
}

function get_sql_log_html ($db_obj, $log_name)
{
	$log = '';

	foreach ($db_obj->dbg as $i => $dbg)
	{
		$id   = "sql_{$i}_". mt_rand();
		$sql  = short_query($dbg['sql'], true);
		$time = sprintf('%.4f', $dbg['time']);
		$perc = sprintf('[%2d]', $dbg['time']*100/$db_obj->sql_timetotal);
		$info = !empty($dbg['info']) ? $dbg['info'] .' ['. $dbg['src'] .']' : $dbg['src'];

		$log .= ''
		. '<div class="sqlLogRow" title="'. $info .'">'
		.  '<span style="letter-spacing: -1px;">'. $time .' </span>'
		.  '<span title="Copy to clipboard" onclick="$.copyToClipboard( $(\'#'. $id .'\').text() );" style="color: gray; letter-spacing: -1px;">'. $perc .'</span>'
		.  ' '
		.  '<span style="letter-spacing: 0px;" id="'. $id .'">'. $sql .'</span>'
		.  '<span style="color: gray"> # '. $info .' </span>'
		. '</div>'
		. "\n";
	}
	return '
		<div class="sqlLogTitle">'. $log_name .'</div>
		'. $log .'
	';
}