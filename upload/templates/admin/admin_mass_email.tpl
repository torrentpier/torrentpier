
<h1>{L_EMAIL}</h1>

<p>{L_EMAIL_EXPLAIN}</p>
<br />

<form method="post" action="{S_USER_ACTION}">

<table class="forumline">
	<tr>
	  <th colspan="2">{L_COMPOSE}</th>
	</tr>
	<tr>
	  <td class="row1" align="right"><b>{L_RECIPIENTS}</b></td>
	  <td class="row2">{S_GROUP_SELECT}</td>
	</tr>
	<tr>
	  <td class="row1" align="right"><b>{L_EMAIL_SUBJECT}</b></td>
	  <td class="row2"><span class="gen"><input class="post" type="text" name="subject" size="45" maxlength="100" tabindex="2" class="post" value="{SUBJECT}" /></span></td>
	</tr>
	<tr>
	  <td class="row1" align="right" valign="top"> <span class="gen"><b>{L_MESSAGE}</b></span>
	  <td class="row2"><span class="gen"> <textarea name="message" rows="15" cols="35" wrap="virtual" style="width:450px" tabindex="3" class="post">{MESSAGE}</textarea></span>
	</tr>
	<tr>
	  <td class="catBottom" colspan="2"><input type="submit" value="{L_EMAIL}" name="submit" class="mainoption" /></td>
	</tr>
</table>

</form>