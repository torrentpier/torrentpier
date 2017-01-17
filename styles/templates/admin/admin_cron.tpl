<script type="text/javascript">
$(document).ready(function() {
	$("#check_all").click(function () {
		if (!$("#check_all").is(":checked"))
			$(".checkbox").removeAttr("checked");
		else
			$(".checkbox").attr("checked","checked");
	});
});
</script>

<style type="text/css">
#cron_true, #cron_false { width: 95%; }
#cron_true td { background: #d9facb; padding: 8px 8px; color: #286e0f; }
#cron_false td { background: #eaeadf; padding: 8px 8px; color: #286e0f; }
table.cron_true { width: 100%; border: 2px solid #169900; background: #60f950; margin: 0 auto; }
table.cron_false { width: 100%; border: 2px solid #796405; background: #d7b101; margin: 0 auto; }
tr.hl-tr:hover td { background-color: #CFC !important; }
</style>

<!-- IF TPL_CRON_LIST -->
<!--========================================================================-->
<form action="{S_CRON_ACTION}" method="post">
<input class="text" type="hidden" name="mode" value="{S_MODE}" />

<table cellpadding="2" cellspacing="0" width="100%">
	<tr>
		<td width="80%">
			<h1>{L_CRON}</h1>
			<a href="admin_cron.php?mode=add">{L_ADD_JOB}</a>
		</td>
		<td width="20%" class="vBottom tRight nowrap med">
		<!-- IF TPL_CRON_LIST -->
			<table id="cron_<!-- IF CRON_ENABLED -->true<!-- ELSE -->false<!-- ENDIF -->" class="cron_<!-- IF CRON_ENABLED -->true<!-- ELSE -->false<!-- ENDIF -->">
				<tr>
					<td>{L_CRON_ENABLED}</td>
					<td>
						<label><input type="radio" name="cron_enabled" value="1" <!-- IF CRON_ENABLED -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
						<label><input type="radio" name="cron_enabled" value="0" <!-- IF not CRON_ENABLED -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
					</td>
					<td>{L_CRON_CHECK_INTERVAL}</td>
					<td>
						<input class="post" type="text" size="10" maxlength="255" name="cron_check_interval" value="{CRON_CHECK_INTERVAL}" />
					</td>
					<td>
						<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />
					</td>
				</tr>
			</table>
		<!-- ELSE IF CRON_RUNNING -->
			<table id="tor_blocked" class="error">
				<tr>
					<td>
						<p class="error_msg">{L_CRON_WORKS} <a href="admin_cron.php?mode=repair"><b>{L_REPAIR_CRON}</b></a></p>
					</td>
				</tr>
			</table>
		<!-- ENDIF -->
		</td>
	</tr>
</table>
<br />

<table class="forumline">
<tr>
	<th colspan="10">{L_CRON_LIST}</th>
</tr>

<tr class="row3 med tCenter">
	<td><input type="checkbox" id="check_all"></td>
	<td>{L_CRON_ID}</td>
	<td>{L_CRON_ACTIVE}</td>
	<td>{L_CRON_TITLE}</td>
	<td>{L_CRON_SCRIPT}</td>
	<td>{L_CRON_SCHEDULE}</td>
	<td>{L_CRON_LAST_RUN}</td>
	<td>{L_CRON_NEXT_RUN}</td>
	<td>{L_CRON_RUN_COUNT}</td>
	<td>{L_CRON_MANAGE}</td>
</tr>

<!-- BEGIN list -->
<tr class="{list.ROW_CLASS} hl-tr">
	<td align="center"><input type="checkbox" name="select[]" class="checkbox" value="{list.CRON_ID}"></td>
	<td nowrap="nowrap" align="center">{list.JOB_ID}</td>
	<td nowrap="nowrap" align="center">{list.CRON_ACTIVE}</td>
	<td nowrap="nowrap" align="left">{list.CRON_TITLE}</td>
	<td nowrap="nowrap" align="left">{list.CRON_SCRIPT}</td>
	<td nowrap="nowrap" class="med tCenter">{list.SCHEDULE}</td>
	<td nowrap="nowrap" align="center">{list.LAST_RUN}</td>
	<td nowrap="nowrap" align="center">{list.NEXT_RUN}</td>
	<td nowrap="nowrap" align="center"><span style="color: #505050;" class="leechmed"><b>{list.RUN_COUNT}</b></span></td>
	<td nowrap="nowrap" align="center">
		<a href="admin_cron.php?mode=run&id={list.CRON_ID}"><img src="{SITE_URL}styles/images/icon_sync.gif" alt="[Run]" title="{L_CRON_RUN}" /></a>
		<a href="admin_cron.php?mode=edit&id={list.CRON_ID}"><img src="{SITE_URL}styles/images/icon_edit.gif" alt="[Edit]" title="{L_CRON_EDIT_HEAD_EDIT}" /></a>
		<a href="admin_cron.php?mode=delete&id={list.CRON_ID}"><img src="{SITE_URL}styles/images/icon_delete.gif" alt="[Del]" title="{L_CRON_DEL}" onclick="return cfm('{L_DELETE_JOB}');" /></a>
	</td>
</tr>
<!-- END list -->
</table>

<table class="forumline">
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
			<label><input onclick="toggle_disabled('send', this.checked)" type="checkbox" name="confirm" value="1" /></label>&nbsp;
			<input type="submit" name="submit" id="send" value="{L_SUBMIT}" class="mainoption" disabled="disabled" />
		</td>
	</tr>
</table>
</form>
<br />
<!--========================================================================-->
<!-- ENDIF / TPL_CRON_LIST -->

<!-- IF TPL_CRON_EDIT -->
<h1>{L_ADD_JOB}</h1>

<a href="admin_cron.php?mode=list">{L_CRON_LIST}</a>
<br /><br />

<form action="{S_CRON_ACTION}" method="post">
<input class="text" type="hidden" name="mode" value="{S_MODE}" />

<table class="forumline">
<col class="row1" width="60%">
<col class="row2">
<tr>
	<th colspan="2">{L_ADD_JOB}</th>
</tr>
<tr>
	<td><h4>{L_CRON_ID}</h4></td>
	<td><input class="text" type="hidden" size="30" maxlength="255" name="cron_id" value="{CRON_ID}" /> <b>{CRON_ID}</b></td>
</tr>
<tr>
	<td>
		<h4>{L_CRON_ACTIVE}</h4>
		<h6>{L_CRON_ACTIVE_EXPL}</h6>
	</td>
	<td>
		<label><input type="radio" name="cron_active" value="1" <!-- IF CRON_ACTIVE -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="cron_active" value="0" <!-- IF not CRON_ACTIVE -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
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
	<td><h4>{L_CRON_SCHEDULE}</h4></td>
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
	<td>
		<label><input type="radio" name="log_enabled" value="1" <!-- IF LOG_ENABLED -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="log_enabled" value="0" <!-- IF not LOG_ENABLED -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_LOG_FILE}</h4><h6>{L_LOG_FILE_EXPL}</h6></td>
	<td><input class="post" type="text" size="35" maxlength="255" name="log_file" value="{LOG_FILE}" /></td>
</tr>
<tr>
	<td><h4>{L_LOG_SQL_QUERIES}</h4></td>
	<td>
		<label><input type="radio" name="log_sql_queries" value="1" <!-- IF LOG_SQL_QUERIES -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="log_sql_queries" value="0" <!-- IF not LOG_SQL_QUERIES -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_FORUM_DISABLE}</h4><h6>{L_BOARD_DISABLE_EXPL}</h6></td>
	<td>
		<label><input type="radio" name="disable_board" value="1" <!-- IF DISABLE_BOARD -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="disable_board" value="0" <!-- IF not DISABLE_BOARD -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
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
