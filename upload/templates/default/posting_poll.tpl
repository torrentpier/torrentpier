<tbody class="pad_4">
<tr>
	<th colspan="2" class="thHead">{L_ADD_POLL}</th>
</tr>
<tr>
	<td><b>{L_POLL_QUESTION}</b></td>
	<td><input type="text" name="poll_title" size="50" maxlength="255" value="{POLL_TITLE}" /></td>
</tr>
<!-- BEGIN poll_option_rows -->
<tr>
	<td><b>{L_POLL_OPTION}</b></td>
	<td>
		<input type="text" name="poll_option_text[{poll_option_rows.S_POLL_OPTION_NUM}]" size="50" maxlength="255" value="{poll_option_rows.POLL_OPTION}" />&nbsp;
		<input type="submit" name="edit_poll_option" value="{L_UPDATE}" />&nbsp;
		<input type="submit" name="del_poll_option[{poll_option_rows.S_POLL_OPTION_NUM}]" value="{L_DELETE}" />
	</td>
</tr>
<!-- END poll_option_rows -->
<tr>
	<td><b>{L_POLL_OPTION}</b></td>
	<td>
		<input type="text" name="add_poll_option_text" size="50" maxlength="255" value="{ADD_POLL_OPTION}" />&nbsp;
		<input type="submit" name="add_poll_option" value="{L_ADD_OPTION}" />
	</td>
</tr>
<tr>
	<td><b>{L_POLL_FOR}</b></td>
	<td>
		<input type="text" name="poll_length" size="3" maxlength="3" value="{POLL_LENGTH}" />&nbsp;
		<b>{L_DAYS}</b>&nbsp;&nbsp;
		<span class="small">{L_POLL_FOR_EXPLAIN}</span>
	</td>
</tr>
<!-- BEGIN switch_poll_delete_toggle -->
<tr>
	<td><b>{L_DELETE_POLL}</b></td>
	<td><input type="checkbox" name="poll_delete" /></td>
</tr>
<!-- END switch_poll_delete_toggle -->
</tbody>