<p class="nav"><a href="{U_INDEX}">{T_INDEX}</a></p>

<form action="{S_PROFILE_ACTION}" method="post">
{S_HIDDEN_FIELDS}

<table class="forumline">
<tr>
	<th colspan="5">{L_AVATAR_GALLERY}</th>
</tr>
<tr>
	<td class="catBottom" colspan="5">{L_SELECT_CATEGORY}: {S_CATEGORY_SELECT} <input type="submit" value="{L_GO}" name="avatargallery" class="lite" /></td>
</tr>

<!-- BEGIN avatar_row -->
<tr>
	<!-- BEGIN avatar_column -->
	<td class="row1" align="center"><img src="{avatar_row.avatar_column.AVATAR_IMAGE}" /></td>
	<!-- END avatar_column -->
</tr>
<tr>
	<!-- BEGIN avatar_option_column -->
	<td class="row2" align="center"><input type="radio" name="avatarselect" value="{avatar_row.avatar_option_column.S_OPTIONS_AVATAR}" /></td>
	<!-- END avatar_option_column -->
</tr>
<!-- END avatar_row -->

<tr>
	<td class="catBottom" colspan="5">
		<input type="submit" name="submitavatar" value="{L_SELECT_AVATAR}" class="main" />&nbsp;&nbsp;
		<input type="submit" name="cancelavatar" value="{L_RETURN_PROFILE}" class="lite" />
	</td>
</tr>
</table>

</form>