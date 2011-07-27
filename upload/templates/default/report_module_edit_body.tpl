<form action="{S_REPORT_ACTION}" method="post">
	<table width="100%" cellpadding="4" cellspacing="1" border="0" class="forumline" align="center">
		<tr>
			<th class="thHead" colspan="2">{L_EDIT_REPORT_MODULE}</th>
		</tr>
		<tr> 
			<td class="row1" width="40%" valign="top"><span class="gen">{L_REPORT_MODULE}:</span></td>
			<td class="row2">
				<span class="gen">{MODULE_TITLE}</span><br />
				<span class="gensmall">{MODULE_EXPLAIN}</span>
			</td>
		</tr>
		<tr>
			<td class="row1"><span class="gen">{L_REPORT_NOTIFY}:</span></td>
			<td class="row2">
				<label for="report_module_notify_1">
					<input type="radio" name="report_module_notify" id="report_module_notify_1" value="1"{MODULE_NOTIFY_ON} />{L_ENABLED}
				</label>
				&nbsp;
				<label for="report_module_notify_0">
					<input type="radio" name="report_module_notify" id="report_module_notify_0" value="0"{MODULE_NOTIFY_OFF} />{L_DISABLED}
				</label>
			</td>
		</tr>
		<tr>
			<td class="row1">
				<label for="report_module_prune" class="gen">{L_REPORT_PRUNE}</label>:<br />
				<span class="gensmall">{L_REPORT_PRUNE_EXPLAIN}</span>
			</td>
			<td class="row2">
				<input type="text" class="post" name="report_module_prune" id="report_module_prune" size="3" maxlength="3" value="{MODULE_PRUNE}" />
				{L_DAYS}
			</td>
		</tr>
		<tr>
			<th class="thSides" colspan="2">{L_REPORT_PERMISSIONS}</th>
		</tr>
		<tr>
			<td class="row1">
				<label for="auth_write" class="gen">{L_WRITE}</label>:
			</td>
			<td class="row2">
				<select name="auth_write" id="auth_write">
					<!-- BEGIN auth_write -->
					<option value="{auth_write.VALUE}"{auth_write.SELECTED}>{auth_write.TITLE}</option>
					<!-- END auth_write -->
				</select>
			</td>
		</tr>
		<tr>
			<td class="row1">
				<label for="auth_view" class="gen">{L_VIEW}</label>:
			</td>
			<td class="row2">
				<select name="auth_view" id="auth_view">
					<!-- BEGIN auth_view -->
					<option value="{auth_view.VALUE}"{auth_view.SELECTED}>{auth_view.TITLE}</option>
					<!-- END auth_view -->
				</select>
			</td>
		</tr>
		<tr>
			<td class="row1">
				<label for="auth_notify" class="gen">{L_REPORT_NOTIFY}</label>:<br />
				<span class="gensmall">{L_REPORT_AUTH_NOTIFY_EXPLAIN}</span>
			</td>
			<td class="row2">
				<select name="auth_notify" id="auth_notify">
					<!-- BEGIN auth_notify -->
					<option value="{auth_notify.VALUE}"{auth_notify.SELECTED}>{auth_notify.TITLE}</option>
					<!-- END auth_notify -->
				</select>
			</td>
		</tr>
		<tr>
			<td class="row1">
				<label for="auth_delete" class="gen">{L_DELETE}</label>:<br />
				<span class="gensmall">{L_REPORT_AUTH_DELETE_EXPLAIN}</span>
			</td>
			<td class="row2">
				<select name="auth_delete" id="auth_delete">
					<!-- BEGIN auth_delete -->
					<option value="{auth_delete.VALUE}"{auth_delete.SELECTED}>{auth_delete.TITLE}</option>
					<!-- END auth_delete -->
				</select>
			</td>
		</tr>
		<tr>
			<td class="catBottom" colspan="2" align="center">
				{S_HIDDEN_FIELDS}
				<input type="submit" name="submit" class="mainoption" value="{L_SUBMIT}" />
				<input type="submit" name="cancel" class="liteoption" value="{L_CANCEL}" />
			</td>
		</tr>
	</table>
</form>