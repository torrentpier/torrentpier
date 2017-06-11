<h1>{L_DISALLOW_CONTROL}</h1>

<p>{L_DISALLOW_EXPLAIN}</p>
<br />

<form method="post" action="{S_FORM_ACTION}">

<table class="forumline wAuto">
<col class="row1">
<col class="row2">
<tr>
	<th colspan="2">{L_ADD_DISALLOW_TITLE}</th>
</tr>
<tr>
	<td><h4>{L_USERNAME}</h4><h6>{L_ADD_DISALLOW_EXPLAIN}</h6></td>
	<td><input class="post" type="text" name="disallowed_user" size="30" />&nbsp;<input type="submit" name="add_name" value="{L_ADD_DISALLOW}" class="mainoption" /></td>
</tr>
<tr>
	<th colspan="2">{L_DELETE_DISALLOW_TITLE}</th>
</tr>
<tr>
	<td><h4>{L_USERNAME}</h4><h6>{L_DELETE_DISALLOW_EXPLAIN}</h6></td>
	<td>{S_DISALLOW_SELECT}&nbsp;<input type="submit" name="delete_name" value="{L_DELETE}" class="liteoption" /></td>
</tr>
<tr>
	<td class="catBottom" colspan="2">&nbsp;</td>
</tr>
</table>

</form>
