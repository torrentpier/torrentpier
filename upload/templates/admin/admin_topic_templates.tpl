<h1>{L_ADMIN_TITLE}</h1>

<P>{L_ADMIN_TEXT}</p>
<br />

<form method="post" action="{S_ACTION}">
{S_HIDDEN_FIELDS}

<table class="forumline w70">
	<tr>
		<th>{L_FORUM}</th>
		<th>{L_TEMPLATE}</th>
	</tr>
	<!-- BEGIN forum -->
	<tr class="{forum.ROW_CLASS}">
		<td class="{forum.FORUM_CLASS}" style="{forum.SF_PAD}{forum.FORUM_STYLE}">{forum.FORUM_NAME}</td>
		<td>{forum.TPL_SELECT}</td>
	</tr>
	<!-- END forum -->
	<tr>
		<td class="catBottom" colspan="2">
			<input type="submit" name="submit" id="send" value="{L_SUBMIT}" class="mainoption" disabled="disabled" />&nbsp;&nbsp;
			<label for="confirm">{L_CONFIRM}&nbsp;<input onclick="toggle_disabled('send', this.checked)" id="confirm" type="checkbox" name="confirm" value="1" /></label>
		</td>
	</tr>
</table>

</form>
