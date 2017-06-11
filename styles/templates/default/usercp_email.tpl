<div class="spacer_12"></div>

<p class="nav"><a href="{U_INDEX}">{T_INDEX}</a></p>

<form action="{S_POST_ACTION}" method="post" name="post" onSubmit="return checkForm(this);">
{S_HIDDEN_FIELDS}

<table class="forumline">
<col class="row1">
<col class="row2">
<tbody class="pad_4">
<tr>
	<th colspan="2"><b>{L_SEND_EMAIL_MSG}</b></th>
</tr>
<tr>
	<td width="25%"><b>{L_RECIPIENT}</b></td>
	<td width="75%"><b>{USERNAME}</b></td>
</tr>
<tr>
	<td><b>{L_SUBJECT}</b></td>
	<td><input type="text" name="subject" size="80" /></td>
</tr>
<tr>
	<td valign="top"><b>{L_MESSAGE}</b><p class="small pad_6">{L_EMAIL_MESSAGE_DESC}</p></td>
	<td><textarea name="message" rows="25" cols="80">{MESSAGE}</textarea></td>
</tr>
<tr>
	<td colspan="2" class="catBottom">
		<input type="submit" name="submit" class="main" value="{L_SEND_EMAIL}" />
	</td>
</tr>
</tbody>
</table>

</form>

<!--bottom_info-->
<div class="bottom_info">

	<div class="spacer_8"></div>

	<div id="timezone">
		<p>{CURRENT_TIME}</p>
		<p>{S_TIMEZONE}</p>
	</div>
	<div class="clear"></div>

</div><!--/bottom_info-->

<script type="text/javascript">
function checkForm(formObj) {

  var formErrors = false;

	if (formObj.message.value.length < 2) {
		formErrors = "{L_EMPTY_MESSAGE_EMAIL}";
	}
	else if ( formObj.subject.value.length < 2)
	{
		formErrors = "{L_EMPTY_SUBJECT_EMAIL}";
	}

	if (formErrors) {
		alert(formErrors);
		return false;
	}
}
</script>
