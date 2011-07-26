<form action="{S_REPORT_ACTION}" method="post">
	<table cellpadding="2" cellspacing="2" border="0" width="100%" align="center">
		<tr> 
			<td class="nav"><a href="{U_INDEX}" class="nav">{T_INDEX}</a></td>
		</tr>
	</table>
	
	<!-- BEGIN switch_report_errors -->
	<table cellpadding="4" cellspacing="1" border="0" width="80%" align="center" class="forumline">
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
	
	<table cellpadding="4" cellspacing="1" border="0" width="80%" align="center" class="forumline">
		<tr> 
			<th class="thHead" colspan="2">{L_WRITE_REPORT}</th>
		</tr>
		<tr>
			<td class="row3" colspan="2"><span class="gen">{L_WRITE_REPORT_EXPLAIN}</span></td>
		</tr>
		<!-- BEGIN switch_report_subject -->
		<tr>
			<td class="row1" width="25%"><span class="genmed">{L_REPORT_SUBJECT}:</span></td>
			<td class="row2"><span class="genmed">
				<!-- BEGIN switch_url -->
				<a href="{U_REPORT_SUBJECT}" class="genmed">
				<!-- END switch_url -->
					{REPORT_SUBJECT}
				<!-- BEGIN switch_url -->
				</a>
				<!-- END switch_url -->
			</span></td>
		</tr>
		<!-- END switch_report_subject -->
		<!-- BEGIN switch_report_reasons -->
		<tr>
			<td class="row1" width="25%"><label for="reasons" class="genmed">{L_REASON}:</label></td>
			<td class="row2">
				<select name="reason" id="reasons">
					<!-- BEGIN report_reasons -->
					<option value="{switch_report_reasons.report_reasons.ID}"{switch_report_reasons.report_reasons.CHECKED}>{switch_report_reasons.report_reasons.DESC}</option>
					<!-- END report_reasons -->
				</select>
			</td>
		</tr>
		<!-- END switch_report_reasons -->
		<!-- BEGIN switch_report_title -->
		<tr>
			<td class="row1" width="25%"><label for="title" class="genmed">{L_POST_SUBJECT}:</label></td>
			<td class="row2"><input type="text" class="post" name="title" id="title" size="50" maxlength="255" style="width: 100%" value="{REPORT_TITLE}" /></td>
		</tr>
		<!-- END switch_report_title -->
		<tr>
			<td class="row1" width="25%" valign="top"><label for="message" class="genmed">{L_MESSAGE}:</label></td>
			<td class="row2"><textarea class="post" name="message" id="message" rows="6" cols="70" style="width: 100%">{REPORT_DESC}</textarea></td>
		</tr>
		<tr>
			<td class="catBottom" colspan="2" align="center">
				<input type="submit" name="submit" class="mainoption" value="{L_SUBMIT}" />
				<input type="submit" name="cancel" class="liteoption" value="{L_CANCEL}" />
			</td>
		</tr>
	</table>
	{S_HIDDEN_FIELDS}
</form>