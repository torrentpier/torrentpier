<h1>{L_BAN_CONTROL}</h1>

<p>{L_BAN_EXPLAIN}</p>
<br />

<form method="post" name="post" action="{S_BANLIST_ACTION}">

<table width="80%" class="forumline">
	<tr>
		<th colspan="2">{L_BAN_USERNAME}</th>
	</tr>
	<tr>
		<td class="row1">{L_USERNAME}:</td>
		<td class="row2"><input type="text" class="post" name="username" maxlength="50" size="20" /> <input type="hidden" name="mode" value="edit" />{S_HIDDEN_FIELDS} <input type="submit" name="usersubmit" value="{L_FIND_USERNAME}" class="liteoption" onClick="window.open('{U_SEARCH_USER}', '_bbsearch', 'HEIGHT=250,resizable=yes,WIDTH=400');return false;" /></td>
	</tr>
	<tr>
		<th colspan="2">{L_UNBAN_USERNAME}</th>
	</tr>
	<tr>
		<td class="row1">{L_USERNAME}: <br /><span class="small">{L_UNBAN_USERNAME_EXPLAIN}</span></td>
		<td class="row2">{S_UNBAN_USERLIST_SELECT}</td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2"><input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />&nbsp;&nbsp;<input type="reset" value="{L_RESET}" class="liteoption" /></td>
	</tr>
</table>

</form>

<br/>
