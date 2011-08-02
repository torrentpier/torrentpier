<?php
	if (!defined('BB_ROOT')) die(basename(__FILE__));
?>

<style type="text/css">
.sqlLog {
	clear: both;
	font-family: Courier, monospace;
	font-size: 12px;
	white-space: nowrap;
	background: #F5F5F5;
	border: 1px solid #BBC0C8;
	overflow: auto;
	width: 98%;
	margin: 0 auto;
	padding: 2px 4px;
}
.sqlLogTitle {
	font-weight: bold;
	color: #444444;
	font-size: 11px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	padding-bottom: 2px;
}
.sqlLogRow {
	background-color: #F5F5F5;
	padding-bottom: 1px;
	border: solid #F5F5F5;
	border-width: 0px 0px 1px 0px;
	cursor: pointer;
}
.sqlLogHead {
	text-align: right;
	float: right;
	width: 100%;
}
.sqlLogHead fieldset {
	float: right;
	margin-right: 4px;
}
.sqlLogWrapped {
	white-space: normal;
	overflow: visible;
}
.sqlExplain {
	color: #B50000;
	font-size: 13px;
	cursor: default;
}
.sqlHover {
	border-color: #8B0000;
}
.sqlHighlight {
	background: #FFE4E1;
}
</style>

<?php

if (!empty($_COOKIE['explain']))
{
	foreach ($DBS->srv as $srv_name => $db_obj)
	{
		if (!empty($db_obj->do_explain))
		{
			$db_obj->explain('display');
		}
	}
}

$sql_log = !empty($_COOKIE['sql_log']) ? get_sql_log() : '';

echo '
<script type="text/javascript">
function fixSqlLog() {
	if ($("#sqlLog").height() > 400) {
		$("#sqlLog").height(400);
	}
	$("#sqlLog div.sqlLogRow")
		.hover(
			function(){ $(this).addClass("sqlHover"); },
			function(){ $(this).removeClass("sqlHover"); }
		)
		.click(
			function(){ $(this).toggleClass("sqlHighlight"); }
		)
	;
}
</script>
	<div class="sqlLogHead">
';
if (PROFILER) {
	echo '
		<fieldset class="med" style="padding: 2px 4px 4px;">
		<legend>Profiling</legend>
			min time:
			<input style="width: 60px;" id="prof_min_time" type="text" value="'. (!empty($_COOKIE['prof_min_time']) ? $_COOKIE['prof_min_time'] : '0.1%') .'" />
			<input type="button" value="go" onclick="setProfMinTime(); window.location.reload();" />
			<label><input type="checkbox" onclick="setCookie(\'prof_enabled\', this.checked ? 1 : 0, \'SESSION\'); setProfMinTime(); setProfCookie(this.checked ? 1 : 0); window.location.reload();" '. (!empty($_COOKIE['prof_enabled']) ? HTML_CHECKED : '') .' />enable </label>
		</fieldset>
	';
}
if (DEBUG) {
	echo '
		<fieldset class="med" style="padding: 2px 4px 4px;">
		<legend>Debug</legend>
			<label><input type="checkbox" onclick="setCookie(\'debug_enabled\', this.checked ? 1 : 0, \'SESSION\'); setDebugCookie(this.checked ? 1 : 0); window.location.reload();" '. (!empty($_COOKIE['debug_enabled']) ? HTML_CHECKED : '') .' />enable </label>
		</fieldset>
	';
}

if ($sql_log)
{
echo '
</div><!-- / sqlLogHead -->

<div class="sqlLog" id="sqlLog">
'. ($sql_log ? $sql_log : '') .'
'. (UA_IE ? '<br />' : '') .'
</div><!-- / sqlLog -->

<br clear="all" />
';
}

if (PROFILER && !empty($_COOKIE['prof_enabled']))
{
	require(DEV_DIR .'profiler/profiler.php');
	$profiler = profiler::init(PROFILER);

	$min_time = !empty($_COOKIE['prof_min_time']) ? $_COOKIE['prof_min_time'] : '0.1%';
	$profiler->print_profile_data($min_time);
}

?>
<script type="text/javascript">
$(document).ready(fixSqlLog);

function setProfMinTime ()
{
	var minTime = $p('prof_min_time').value;
	setCookie('prof_min_time', (minTime ? minTime : '0.1%'));
}

function setProfCookie (val)
{
	// http://support.nusphere.com/viewtopic.php?t=586
	if (!val) {
		deleteCookie('DBGSESSID', '/');
	}
	else {
		// СЛОМАНО!! профайлер работает только по нажатию кнопки на тулбаре phpEd, после чего кука сбрасывается
		setCookie('DBGSESSID', '1@clienthost:7869;d=1,p=1', 'SESSION', '/');
	}
}
function setDebugCookie (val)
{
	if (!val) {
		deleteCookie('DBGSESSID', '/');
	}
	else {
		setCookie('DBGSESSID', '1@clienthost:7869;d=1,p=0,c=1', 'SESSION', '/');
	}
}
</script>