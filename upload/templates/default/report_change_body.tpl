<table cellspacing="2" cellpadding="2" border="0" width="100%">
	<tr> 
		<td class="nav"><a href="{U_INDEX}" class="nav">{T_INDEX}</a></td>
	</tr>
</table>

<form action="{S_CONFIRM_ACTION}" method="post" style="margin: 0">
	<table cellspacing="1" cellpadding="4" border="0" width="100%" class="forumline">
		<tr>
			<th class="thHead" colspan="2">{MESSAGE_TITLE}</th>
		</tr>
		<tr>
			<td class="row1" colspan="2" align="center" style="padding: 10px"><span class="gen">{MESSAGE_TEXT}</span></td>
		</tr>
		<tr>
			<td class="row2" width="25%" valign="top"><span class="gen">{L_COMMENT}:</span></td>
			<td class="row2">
				<textarea name="comment" rows="5" cols="80" style="width: 100%"></textarea>
			</td>
		</tr>
		<tr>
			<td class="catBottom" colspan="2" align="center">
				{S_HIDDEN_FIELDS}
				<input type="submit" class="mainoption" name="confirm" value="{L_SUBMIT}" />
				<input type="submit" class="liteoption" name="cancel" value="{L_CANCEL}" />
			</td>
		</tr>
	</table>
</form>