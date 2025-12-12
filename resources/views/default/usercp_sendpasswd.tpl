<!-- IF CAPTCHA_HTML -->
<script type="text/javascript">
ajax.callback.user_register = function(data){
	$('#'+ data.mode).html(data.html);
};
</script>
<!-- ENDIF -->
<p class="nav"><a href="{U_INDEX}">{T_INDEX}</a></p>

<form action="{S_PROFILE_ACTION}" method="post">
{S_HIDDEN_FIELDS}

<table class="forumline">
<col class="row1">
<col class="row2">
<tbody class="pad_4">
<tr>
	<th colspan="2">{L_FORGOTTEN_PASSWORD}</th>
</tr>
<tr>
	<td colspan="2" class="row2 small">{L_ITEMS_REQUIRED}</td>
</tr>
<tr>
	<td>{L_EMAIL_ADDRESS}: *</td>
	<td><input type="text" class="post" name="email" size="50" maxlength="255" /></td>
</tr>
<!-- IF CAPTCHA_HTML -->
<tr>
	<td>{L_CAPTCHA}: *</td>
	<td>{CAPTCHA_HTML}</td>
</tr>
<!-- ENDIF -->
<tr>
	<td colspan="2" class="catBottom">
		<input type="submit" name="submit" class="main" value="{L_SUBMIT}" />&nbsp;
		<input type="reset" name="reset" class="lite" value="{L_RESET}" />
	</td>
</tr>
</tbody>
</table>

</form>
