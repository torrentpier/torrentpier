<table cellspacing="1" cellpadding="4" border="0" width="100%" class="forumline">
	<tr>
		<th class="thHead">{REPORT_TYPE}</th>
	</tr>
	<tr>
		<td class="row1" style="padding: 10px">
			<span class="maintitle">{REPORT_TITLE}</span><br /><br />

			<!-- BEGIN switch_report_subject_deleted -->
			<table cellspacing="1" cellpadding="4" border="0" width="90%" align="center" class="bodyline">
				<tr>
					<td class="row2" align="center"><span class="gen">{L_REPORT_SUBJECT_DELETED}</span></td>
				</tr>
			</table>
			<br />
			<!-- END switch_report_subject_deleted -->

			<!-- BEGIN report_subject -->
			<table cellspacing="1" cellpadding="4" border="0" width="90%" align="center" class="bodyline">
				<!-- BEGIN switch_subject -->
				<tr>
					<td class="row2" valign="top" width="25%"><span class="gen">{L_REPORT_SUBJECT}:</span></td>
					<td class="row2"><span class="gen">
						<!-- BEGIN switch_url -->
						<a href="{U_REPORT_SUBJECT}" class="gen"{S_REPORT_SUBJECT_TARGET}>
						<!-- END switch_url -->
							{REPORT_SUBJECT}
						<!-- BEGIN switch_url -->
						</a>
						<!-- END switch_url -->
					</span></td>
				</tr>
				<!-- END switch_subject -->
				<!-- BEGIN details -->
				<tr>
					<td class="row2" valign="top" width="25%"><span class="gen">{report_subject.details.TITLE}:</span></td>
					<td class="row2"><span class="gen">{report_subject.details.VALUE}</span></td>
				</tr>
				<!-- END details -->
			</table>
			<br />
			<!-- END report_subject -->

			<table cellspacing="1" cellpadding="4" border="0" width="90%" align="center" class="bodyline">
				<tr>
					<td class="row2" width="25%"><span class="genmed">{L_REPORTED_BY}:</span></td>
					<td class="row2"><span class="genmed">
						{REPORT_AUTHOR}&nbsp;
						[ <a href="{U_REPORT_AUTHOR_PRIVMSG}" class="genmed">{L_SEND_PRIVATE_MESSAGE}</a> ]
					</span></td>
				</tr>
				<tr>
					<td class="row2"><span class="genmed">{L_REPORTED_TIME}:</span></td>
					<td class="row2"><span class="genmed">{REPORT_TIME}</span></td>
				</tr>
				<!-- BEGIN switch_report_reason -->
				<tr>
					<td class="row2" valign="top"><span class="gen">{L_REASON}:</span></td>
					<td class="row2"><span class="gen">{REPORT_REASON}</span></td>
				</tr>
				<!-- END switch_report_reason -->
				<tr>
					<td class="row2" valign="top"><span class="gen">{L_MESSAGE}:</span></td>
					<td class="row2"><div class="post_wrap">{REPORT_DESC}</div></td>
				</tr>
			</table>

			<br />

			<table cellspacing="1" cellpadding="4" border="0" width="90%" align="center" class="bodyline">
				<tr>
					<td class="row2" width="25%"><span class="gen">{L_STATUS}:</span></td>
					<td class="row2">
						<div class="report_pixel {REPORT_STATUS_CLASS}"></div>
						<span class="gen"><strong>{REPORT_STATUS}</strong></span>
					</td>
				</tr>
				<!-- BEGIN switch_report_changes -->
				<tr>
					<td class="row2"><span class="genmed">{L_LAST_CHANGED_BY}:</span></td>
					<td class="row2"><span class="genmed">
						{REPORT_LAST_CHANGE_USER} ({REPORT_LAST_CHANGE_TIME})
					</span></td>
				</tr>
				<!-- END switch_report_changes -->
			</table>

			<br />
		</td>
	</tr>
	<!-- BEGIN switch_report_changes -->
	<tr>
		<td class="cat" align="center"><span class="cattitle">{L_CHANGES}</span></td>
	</tr>
	<!-- BEGIN report_changes -->
	<tr>
		<td class="{switch_report_changes.report_changes.ROW_CLASS}"><div class="med post_wrap">{switch_report_changes.report_changes.TEXT}</div></td>
	</tr>
	<!-- END report_changes -->
	<!-- END switch_report_changes -->
	<tr>
		<td class="catBottom" align="center">
			<form action="{S_REPORT_ACTION}" method="post" style="margin: 0">
				<input type="submit" name="open" class="liteoption" value="{L_STATUS_OPEN}" />
				<input type="submit" name="process" class="liteoption" value="{L_STATUS_IN_PROCESS}" />
				<input type="submit" name="clear" class="liteoption" value="{L_STATUS_CLEARED}" />
				<!-- BEGIN switch_delete_option -->
				<input type="submit" name="delete" class="liteoption" value="{L_DELETE}" />
				<!-- END switch_delete_option -->
				{S_HIDDEN_FIELDS}
			</form>
		</td>
	</tr>
</table>