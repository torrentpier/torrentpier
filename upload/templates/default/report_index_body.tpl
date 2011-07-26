<table cellspacing="1" cellpadding="4" border="0" width="100%" class="forumline">
	<tr>
		<th class="thHead">{L_REPORT_INDEX}</th>
	</tr>
	<tr>
		<td class="row1" style="padding: 10px">
			<span class="maintitle">{L_STATISTICS}:</span><br /><br />
			<table cellspacing="1" cellpadding="4" border="0" width="90%" align="center" class="forumline">
				<tr> 
					<th class="thCornerL" width="50%">{L_STATISTIC}</th>
					<th class="thCornerR">{L_VALUE}</th>
				</tr>
				<!-- BEGIN report_statistics -->
				<tr>
					<td class="row1"><span class="gen">{report_statistics.STATISTIC}:</span></td>
					<td class="row1"><span class="gen">{report_statistics.VALUE}</span></td>
				</tr>
				<!-- END report_statistics -->
			</table>
			
			<br />
			
			<!-- BEGIN switch_deleted_reports -->
			<span class="maintitle">{L_DELETED_REPORTS}:</span><br /><br />
			
			<form action="{S_REPORT_ACTION}" method="post" id="report_list_deleted" style="margin: 0">
				<table cellspacing="1" cellpadding="4" border="0" width="90%" align="center" class="forumline">
					<tr>
						<th class="thCornerL" colspan="2" width="70%">{L_REPORTS}</th>
						<th class="thCornerR">{L_REPORT_TYPE}</th>
					</tr>
					<!-- BEGIN deleted_reports -->
					<tr>
						<td class="report_delete" nowrap="nowrap"><input type="checkbox" name="reports[]" value="{switch_deleted_reports.deleted_reports.ID}" /></td>
						<td class="report_delete" width="70%">
							<a href="{switch_deleted_reports.deleted_reports.U_SHOW}" class="gen">{switch_deleted_reports.deleted_reports.TITLE}</a><br />
							<span class="gensmall">
								{L_REPORT_BY} <a href="{switch_deleted_reports.deleted_reports.U_AUTHOR}" class="gensmall">{switch_deleted_reports.deleted_reports.AUTHOR}</a>
							</span>
						</td>
						<td class="report_delete" align="center" width="30%"><span class="gen">{switch_deleted_reports.deleted_reports.TYPE}</span></td>
					</tr>
					<!-- END deleted_reports -->
					<tr>
						<td class="catBottom" colspan="3" align="center">
							<select name="mode" class="report_mode" onchange="submit()">
								<option value="" selected="selected">{L_ACTION}</option>
								<option value="" disabled="disabled"></option>
								<option value="delete">{L_DELETE}</option>
								<optgroup label="{L_REPORT_MARK}">
									<option value="clear" class="report_cleared">{L_STATUS_CLEARED}</option>
									<option value="process" class="report_process">{L_STATUS_IN_PROCESS}</option>
									<option value="open" class="report_open">{L_STATUS_OPEN}</option>
								</option>
							</select>
							<noscript>
								<input type="submit" name="submit" class="liteoption" value="{L_SUBMIT}" />
							</noscript>
						</td>
					</tr>
				</table>
			</form>
			
			<table cellspacing="2" cellpadding="2" border="0" width="90%" align="center">
				<tr> 
					<td class="gensmall">
						<a href="javascript:checked_toggle('report_list_deleted',true)" class="gensmall">{L_MARK_ALL}</a> ::
						<a href="javascript:checked_switch('report_list_deleted')" class="gensmall">{L_INVERT_SELECT}</a>
					</td>
				</tr>
			</table>
			
			<br />
			<!-- END switch_deleted_reports -->
		</td>
	</tr>
	<tr>
		<td class="catBottom">
		</td>
	</tr>
</table>