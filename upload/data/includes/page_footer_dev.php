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
	border-width: 0 0 1px 0;
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

echo '</div><!-- / sqlLogHead -->';

if ($sql_log)
{
	echo '<div class="sqlLog" id="sqlLog">'. ($sql_log ? $sql_log : '') .'</div><!-- / sqlLog --><br clear="all" />';
}
?>
<script type="text/javascript">
$(document).ready(fixSqlLog);
</script>