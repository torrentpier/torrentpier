<SCRIPT language=JavaScript title="check">
function CheckAll(Element,Name){
if(document.getElementById) {
	thisCheckBoxes = Element.parentNode.parentNode.parentNode.getElementsByTagName('input');
	for (i = 1; i < thisCheckBoxes.length; i++){
		if (thisCheckBoxes[i].name == Name){
			thisCheckBoxes[i].checked = Element.checked;
			Colorize(document.getElementById(thisCheckBoxes[i].id.replace('cb','tr')), thisCheckBoxes[i]);
		}
	}
	}
}

function Colorize(Element, CBElement){
if(document.getElementById) {
	if(Element && CBElement){
		Element.className = ( CBElement.checked ? 'selected' : 'default' );
	}
}
}

function CheckRadioTR(Element){
if(document.getElementById) {
	CheckTR(Element);
	thisTRs = Element.parentNode.getElementsByTagName('tr');
	for (i = 0; i < thisTRs.length; i++){
		if (thisTRs[i].id != Element.id && thisTRs[i].className != 'header') thisTRs[i].className = 'default';
	}
}
}

function CheckTR(Element){
if(document.getElementById) {
	thisCheckbox = document.getElementById(Element.id.replace('tr','cb'));
	thisCheckbox.checked = !thisCheckbox.checked;
	Colorize(Element, thisCheckbox);
}
}

function CheckCB(Element){
if(document.getElementById) {
	if(document.getElementById(Element.id.replace('cb','tr'))){Element.checked = !Element.checked;}
}
}
</SCRIPT>
<!-- IF TPL_CRON_LIST -->
<!--========================================================================-->
<form action="{S_CRON_ACTION}" method="post">
<input class="text" type="hidden" name="mode" value="{S_MODE}" />

<table class="forumline">
<tr>
<td colspan="10" class="catTitle">
  {L_CRON_LIST}
</td>
</tr>
<tr>
<th width="5%" nowrap="nowrap" align="center">
 <INPUT onclick="CheckAll(this,'select[]')" type="checkbox">
</th>
<th width="5%" nowrap="nowrap" align="center">

  {L_CRON_ID}

</th>
<th width="5%" nowrap="nowrap" align="center">

  {L_CRON_ACTIVE}

</th>
<th width="30%" nowrap="nowrap" align="center">

  {L_CRON_TITLE}

</th>
<th width="20%" nowrap="nowrap" align="center">

  {L_CRON_SCRIPT}

</th>
<th width="5%" nowrap="nowrap" align="center">

  {L_CRON_SCHEDULE}

</th>
<th width="5%" nowrap="nowrap" align="center">

  {L_CRON_LAST_RUN}

</th>
<th width="5%" nowrap="nowrap" align="center">

  {L_CRON_NEXT_RUN}

</th>
<th width="1%" nowrap="nowrap" align="center">

  {L_CRON_RUN_COUNT}

</th>
<th width="10%" nowrap="nowrap" align="center">

  {L_CRON_MANAGE}

</th>
</tr>

{LIST}

</table>
<table class="forumline">
<tr>
<td colspan="2" class="catTitle">
  {L_CRON_OPTIONS}
</td>
</tr>
<tr>
	<td width="50%" nowrap="nowrap" class="row1" align="left">{L_CRON_ENABLED}</td>
	<td width="50%" nowrap="nowrap" class="row1" align="left"><label for="cron_enabled1"><input type="radio" name="cron_enabled" id="cron_enabled1" value="1" {CRON_ENABLED_YES} /> {L_CRON_ENABLED_YES}&nbsp;</label><label for="cron_enabled2">&nbsp;<input type="radio" name="cron_enabled" id="cron_enabled2" value="0" {CRON_ENABLED_NO} /> {L_CRON_ENABLED_NO} &nbsp;</label></td>
</tr>
<tr>
	<td width="50%" nowrap="nowrap" class="row1" align="left">{L_CRON_CHECK_INTERVAL}</td>
	<td width="50%" nowrap="nowrap" class="row1" align="left"><input class="post" type="text" size="35" maxlength="255" name="cron_check_interval" value="{CRON_CHECK_INTERVAL}" /></td>
</tr>
<tr>
<td colspan="2" class="catBottom">
	{L_WITH_SELECTED}
	<select name="cron_action" id="cron_select" >
				<option value="" selected="selected" class="select-action">&raquo; {L_NOTHING}</option>
				<option value="run">{L_CRON_RUN}</option>
				<option value="delete">{L_CRON_DEL}</option>
				<option value="disable">{L_CRON_DISABLE}</option>
				<option value="enable">{L_CRON_ENABLE}</option>
	</select>
	<label for="confirm">&nbsp;<input onclick="toggle_disabled('send', this.checked)" id="confirm" type="checkbox" name="confirm" value="1" /></label>
	<input type="submit" name="submit" id="send" value="{L_SUBMIT}" class="mainoption" disabled="disabled" />&nbsp;&nbsp;	
</td>
</tr>
</table>
</form>
<br />
<center>
<a href="admin_cron.php?mode=run"><b>{L_RUN_MAIN_CRON}</b></a><br />
<a href="admin_cron.php?mode=add"><b>{L_ADD_JOB}</b></a>
</center>
{CRON_ACTION}
<!-- IF CRON_RUNNING -->
<center><b>{L_CRON_WORKS}
<a href="admin_cron.php?mode=repair">{L_REPAIR_CRON}</b></a></center>
<!-- ENDIF / CRON_RUNNING -->

<!--========================================================================-->
<!-- ENDIF / TPL_CRON_LIST -->

<!-- IF TPL_CRON_EDIT -->
<h1>{L_CRON_EDIT_HEAD}</h1>
<form action="{S_CRON_ACTION}" method="post">
<input class="text" type="hidden" name="mode" value="{S_MODE}" />

<table class="forumline">
<col class="row1" width="60%">
<col class="row2">
<tr>
	<th colspan="2">{L_CRON_EDIT_HEAD}</th>
</tr>
<tr>
	<td><h4>{L_CRON_ID}</h4></td>
	<td><input class="text" type="hidden" size="30" maxlength="255" name="cron_id" value="{CRON_ID}" /> <b>{CRON_ID}</b></td>
</tr>
<tr>
	<td><h4>{L_CRON_ACTIVE}</h4><h6>{L_CRON_ACTIVE_EXPL}</h6></td>
	<td><label for="cron_active1"><input type="radio" name="cron_active" id="cron_active1" value="1" {CRON_ACTIVE_YES} /> {L_CRON_ACTIVE_YES}&nbsp;</label><label for="cron_active2">&nbsp;<input type="radio" name="cron_active" id="cron_active2" value="0" {CRON_ACTIVE_NO} /> {L_CRON_ACTIVE_NO} &nbsp;</label></td>
</tr>
<tr>
	<td><h4>{L_CRON_TITLE}</h4></td>
	<td><input class="post" type="text" size="35" maxlength="255" name="cron_title" value="{CRON_TITLE}" /></td>
</tr>
<tr>
	<td><h4>{L_CRON_SCRIPT}</h4><h6>{L_CRON_SCRIPT_EXPL}</h6></td>
	<td><input class="post" type="text" size="35" maxlength="255" name="cron_script" value="{CRON_SCRIPT}" /></td>
</tr>
<tr>
	<td><h4>{L_SCHEDULE}</h4></td>
	<td>{SCHEDULE}</td>
</tr>
<tr>
	<td><h4>{L_RUN_DAY}</h4><h6>{L_RUN_DAY_EXPL}</h6></td>
	<td>{RUN_DAY}</td>
</tr>
<tr>
	<td><h4>{L_RUN_TIME}</h4><h6>{L_RUN_TIME_EXPL}</h6></td>
	<td><input class="post" type="text" size="35" maxlength="255" name="run_time" value="{RUN_TIME}" /></td>
</tr>
<tr>
	<td><h4>{L_RUN_ORDER}</h4></td>
	<td><input class="post" type="text" size="35" maxlength="255" name="run_order" value="{RUN_ORDER}" /></td>
</tr>
<tr>
	<td><h4>{L_LAST_RUN}</h4></td>
	<td><input class="post" type="text" size="35" maxlength="255" name="last_run" value="{LAST_RUN}" /></td>
</tr>
<tr>
	<td><h4>{L_NEXT_RUN}</h4></td>
	<td><input class="post" type="text" size="35" maxlength="255" name="next_run" value="{NEXT_RUN}" /></td>
</tr>
<tr>
	<td><h4>{L_RUN_INTERVAL}</h4><h6>{L_RUN_INTERVAL_EXPL}</h6></td>
	<td><input class="post" type="text" size="35" maxlength="255" name="run_interval" value="{RUN_INTERVAL}" /></td>
</tr>
<tr>
	<td><h4>{L_LOG_ENABLED}</h4></td>
	<td><label for="log_enabled1"><input type="radio" name="log_enabled" id="log_enabled1" value="1" {LOG_ENABLED_YES} /> {L_LOG_ENABLED_YES}&nbsp;</label><label for="log_enabled2">&nbsp;<input type="radio" name="log_enabled" id="log_enabled2" value="0" {LOG_ENABLED_NO} /> {L_LOG_ENABLED_NO} &nbsp;</label></td>
</tr>
<tr>
	<td><h4>{L_LOG_FILE}</h4><h6>{L_LOG_FILE_EXPL}</h6></td>
	<td><input class="post" type="text" size="35" maxlength="255" name="log_file" value="{LOG_FILE}" /></td>
</tr>
<tr>
	<td><h4>{L_LOG_SQL_QUERIES}</h4></td>
	<td><label for="log_sql_queries1"><input type="radio" name="log_sql_queries" id="log_sql_queries1" value="1" {LOG_SQL_QUERIES_YES} /> {L_LOG_SQL_QUERIES_YES}&nbsp;</label><label for="log_sql_queries2">&nbsp;<input type="radio" name="log_sql_queries" id="log_sql_queries2" value="0" {LOG_SQL_QUERIES_NO} /> {L_LOG_SQL_QUERIES_NO} &nbsp;</label></td>
</tr>
<tr>
	<td><h4>{L_DISABLE_BOARD}</h4><h6>{L_DISABLE_BOARD_EXPL}</h6></td>
	<td><label for="disable_board1"><input type="radio" name="disable_board" id="disable_board1" value="1" {DISABLE_BOARD_YES} /> {L_DISABLE_BOARD_YES}&nbsp;</label><label for="disable_board2">&nbsp;<input type="radio" name="disable_board" id="disable_board2" value="0" {DISABLE_BOARD_NO} /> {L_DISABLE_BOARD_NO} &nbsp;</label></td>
</tr>
<tr>
	<td><h4>{L_RUN_COUNTER}</h4></td>
	<td><input class="post" type="text" size="35" maxlength="255" name="run_counter" value="{RUN_COUNTER}" /></td>
</tr>

<tr>
	<td colspan="2" class="catBottom">
		<input type="reset" value="{L_RESET}" class="liteoption" />&nbsp;&nbsp;
		<input type="submit" name="submit" id="send" value="{L_SUBMIT}" class="mainoption" disabled="disabled" />&nbsp;&nbsp;
		<label for="confirm">{L_CONFIRM}&nbsp;<input onclick="toggle_disabled('send', this.checked)" id="confirm" type="checkbox" name="confirm" value="1" /></label>
	</td>
</tr>
</table>

</form>
<!-- ENDIF / TPL_CRON_EDIT -->