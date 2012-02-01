<form action="{S_REPORT_ACTION}" method="post">
	<!-- BEGIN switch_report_errors -->
	<table cellpadding="4" cellspacing="1" border="0" width="100%" align="center" class="forumline">
		<tr> 
			<td class="row1" align="center"><span class="gen">
				<!-- BEGIN report_errors -->
				{switch_report_errors.report_errors.MESSAGE}<br />
				<!-- END report_errors -->
			</span></td>
		</tr>
	</table>
	<br />
	<!-- END switch_report_errors -->
	
	<table width="100%" cellpadding="4" cellspacing="1" border="0" class="forumline" align="center">
		<tr>
			<th class="thHead" colspan="2">{L_ADD_REASON}</th>
		</tr>
		<tr>
			<td class="row1" width="40%">
				<label for="report_reason_desc" class="gen">{L_FORUM_DESC}</label>:<br />
				<span class="gensmall">{L_REASON_DESC_EXPLAIN}</span>
			</td>
			<td class="row2">
				<input type="text" class="post" name="report_reason_desc" id="report_reason_desc" size="50" maxlength="255" style="width: 100%" value="{REASON_DESC}" />
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