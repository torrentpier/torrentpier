<h1>{L_CONFIGURATION_TITLE}</h1>

<p>{L_REPORT_CONFIG_EXPLAIN}</p>
<br />

<form action="{S_REPORT_ACTION}" method="post">
	<table width="99%" cellpadding="4" cellspacing="1" border="0" align="center" class="forumline">
		<tr>
			<th colspan="2">&nbsp;</th>
		</tr>
		<tr>
			<td class="row1" width="60%">
				<span class="gen">{L_REPORT_SUBJECT_AUTH}:</span><br />
				<span class="gensmall">{L_REPORT_SUBJECT_AUTH_EXPLAIN}</span>
			</td>
			<td class="row2">
				<label for="report_subject_auth_1">
					<input type="radio" name="bb_cfg[report_subject_auth]" id="report_subject_auth_1" value="1"{REPORT_SUBJECT_AUTH_ON} />{L_ENABLED}
				</label>
				&nbsp;
				<label for="report_subject_auth_0">
					<input type="radio" name="bb_cfg[report_subject_auth]" id="report_subject_auth_0" value="0"{REPORT_SUBJECT_AUTH_OFF} />{L_DISABLED}
				</label>
			</td>
		</tr>
		<tr>
			<td class="row1">
				<span class="gen">{L_REPORT_MODULES_CACHE}:</span><br />
				<span class="gensmall">{L_REPORT_MODULES_CACHE_EXPLAIN}</span>
			</td>
			<td class="row2">
				<label for="report_modules_cache_1">
					<input type="radio" name="bb_cfg[report_modules_cache]" id="report_modules_cache_1" value="1"{REPORT_MODULES_CACHE_ON} />{L_YES}
				</label>
				&nbsp;
				<label for="report_modules_cache_0">
					<input type="radio" name="bb_cfg[report_modules_cache]" id="report_modules_cache_0" value="0"{REPORT_MODULES_CACHE_OFF} />{L_NO}
				</label>
			</td>
		</tr>
		<tr>
			<td class="row1" valign="top"><span class="gen">{L_REPORT_NOTIFY}:</span></td>
			<td class="row2">
				<label for="report_notify_2">
					<input type="radio" name="bb_cfg[report_notify]" id="report_notify_2" value="2"{REPORT_NOTIFY_CHANGE} />{L_REPORT_NOTIFY_CHANGE}
				</label>
				<br />
				<label for="report_notify_1">
					<input type="radio" name="bb_cfg[report_notify]" id="report_notify_1" value="1"{REPORT_NOTIFY_NEW} />{L_REPORT_NOTIFY_NEW}
				</label>
				<br />
				<label for="report_notify_0">
					<input type="radio" name="bb_cfg[report_notify]" id="report_notify_0" value="0"{REPORT_NOTIFY_OFF} />{L_DISABLED}
				</label>
			</td>
		</tr>
		<tr>
			<td class="row1"><span class="gen">{L_REPORT_LIST_ADMIN}:</span></td>
			<td class="row2">
				<label for="report_list_admin_1">
					<input type="radio" name="bb_cfg[report_list_admin]" id="report_list_admin_1" value="1"{REPORT_LIST_ADMIN_ON} />{L_YES}
				</label>
				&nbsp;
				<label for="report_list_admin_0">
					<input type="radio" name="bb_cfg[report_list_admin]" id="report_list_admin_0" value="0"{REPORT_LIST_ADMIN_OFF} />{L_NO}
				</label>
			</td>
		</tr>
		<tr>
			<td class="row1">
				<span class="gen">{L_REPORT_NEW_WINDOW}:</span><br />
				<span class="gensmall">{L_REPORT_NEW_WINDOW_EXPLAIN}</span>
			</td>
			<td class="row2">
				<label for="report_new_window_1">
					<input type="radio" name="bb_cfg[report_new_window]" id="report_new_window_1" value="1"{REPORT_NEW_WINDOW_ON} />{L_YES}
				</label>
				&nbsp;
				<label for="report_new_window_0">
					<input type="radio" name="bb_cfg[report_new_window]" id="report_new_window_0" value="0"{REPORT_NEW_WINDOW_OFF} />{L_NO}
				</label>
			</td>
		</tr>
		<tr>
			<td class="catBottom" colspan="2" align="center">
				{S_HIDDEN_FIELDS}
				<input type="submit" name="submit" class="mainoption" value="{L_SUBMIT}" />
				<input type="reset" class="liteoption" value="{L_RESET}" />
			</td>
		</tr>
	</table>
</form>