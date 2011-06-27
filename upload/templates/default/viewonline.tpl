<p class="nav"><a href="{U_INDEX}">{T_INDEX}</a></p>

<table class="forumline">
<tr>
	<th>{L_USERNAME}</th>
	<th>{L_LAST_UPDATE}</th>
</tr>
<tr>
	<td class="catTitle" colspan="2">{TOTAL_REGISTERED_USERS_ONLINE}</td>
</tr>
<!-- BEGIN reg_user_row -->
<tr class="{reg_user_row.ROW_CLASS}">
	<td><a href="{reg_user_row.U_USER_PROFILE}" class="gen">{reg_user_row.USERNAME}</a></td>
	<td class="tCenter">{reg_user_row.LASTUPDATE}</td>
</tr>
<!-- END reg_user_row -->
<tr>
	<td class="catTitle" colspan="2">{TOTAL_GUEST_USERS_ONLINE}</td>
</tr>
<!-- BEGIN guest_user_row -->
<tr class="{guest_user_row.ROW_CLASS}">
	<td>{guest_user_row.USERNAME}</td>
	<td class="tCenter">{guest_user_row.LASTUPDATE}</td>
</tr>
<!-- END guest_user_row -->
</table>

<div class="spacer_4"></div>

<div class="bottom_info">

	<p style="float: left">{L_ONLINE_EXPLAIN}</p>

	<div id="timezone">
		<p>{LAST_VISIT_DATE}</p>
		<p>{CURRENT_TIME}</p>
		<p>{S_TIMEZONE}</p>
	</div>
	<div class="clear"></div>

</div><!--/bottom_info-->