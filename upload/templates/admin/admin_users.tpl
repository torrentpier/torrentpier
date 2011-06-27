
<!-- IF TPL_ADMIN_USER_SELECT -->
<!--========================================================================-->

<h1>{L_USER_ADMIN}</h1>

<p>{L_USER_EXPLAIN}</p>
<br /><br />

<form method="post" name="post" action="{S_USER_ACTION}">
{S_HIDDEN_FIELDS}
<input type="hidden" name="mode" value="edit" />

<table class="forumline wAuto">
<tr>
	<th>{L_USER_SELECT}</th>
</tr>
<tr>
	<td class="row1 tCenter pad_8">
		<p class="mrg_12">
			<input type="text" class="post" name="username" maxlength="50" size="20" />
			<input type="button" name="usersubmit" value="{L_FIND_USERNAME}" onclick="window.open('{U_SEARCH_USER}', '_phpbbsearch', 'HEIGHT=250,resizable=yes,WIDTH=400');return false;" />
		</p>
		<p class="mrg_12">
			<input type="submit" name="submituser" value="{L_LOOK_UP_USER}" class="bold" />
		</p>
	</td>
</tr>
</table>

</form>

<br /><br /><br /><br />

<!--========================================================================-->
<!-- ENDIF / TPL_ADMIN_USER_SELECT -->

<!-- IF TPL_ADMIN_USER_EDIT -->
<!--========================================================================-->

<h1>{L_USER_ADMIN}</h1>

<p>{L_USER_EXPLAIN}</p>
<br />

<form action="{S_PROFILE_ACTION}" {S_FORM_ENCTYPE} method="post">
<table class="forumline">
	<tr>
	  <th colspan="2">{L_REGISTRATION_INFO}</th>
	</tr>
	<tr>
	  <td class="row2" colspan="2"><span class="small">{L_ITEMS_REQUIRED}</span></td>
	</tr>
	<tr>
	  <td class="row1" width="38%"><span class="gen">{L_USERNAME}: *</span></td>
	  <td class="row2">
		<input class="post" type="text" name="username" size="35" maxlength="40" value="{USERNAME}" />
	  </td>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{L_EMAIL_ADDRESS}: *</span></td>
	  <td class="row2">
		<input class="post" type="text" name="email" size="35" maxlength="255" value="{EMAIL}" />
	  </td>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{L_NEW_PASSWORD}: *</span><br />
		<span class="small">{L_PASSWORD_IF_CHANGED}</span></td>
	  <td class="row2">
		<input class="post" type="password" name="password" size="35" maxlength="32" />
	  </td>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{L_CONFIRM_PASSWORD}: * </span><br />
		<span class="small">{L_PASSWORD_CONFIRM_IF_CHANGED}</span></td>
	  <td class="row2">
		<input class="post" type="password" name="password_confirm" size="35" maxlength="32" />
	  </td>
	</tr>
	<tr>
	  <td class="cat" colspan="2">&nbsp;</td>
	</tr>
	<tr>
	  <th colspan="2">{L_PROFILE_INFO}</th>
	</tr>
	<tr>
	  <td class="row2" colspan="2"><span class="small">{L_PROFILE_INFO_NOTICE}</span></td>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{L_ICQ}</span></td>
	  <td class="row2">
		<input class="post" type="text" name="icq" size="10" maxlength="15" value="{ICQ}" />
	  </td>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{L_WEBSITE}</span></td>
	  <td class="row2">
		<input class="post" type="text" name="website" size="35" maxlength="255" value="{WEBSITE}" />
	  </td>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{L_LOCATION}</span></td>
	  <td class="row2">
		<input class="post" type="text" name="location" size="35" maxlength="100" value="{LOCATION}" />
	  </td>
	</tr>
<!-- FLAGHACK-start -->
	<tr>
	  <td class="row1"><span class="gen">{L_FLAG}:</span></td>
	  <td class="row2"><span class="small">
	  <table>
	  <tr>
			<td>{FLAG_SELECT}&nbsp;&nbsp;&nbsp;&nbsp;</td>
	  		<td><img src="../images/flags/{FLAG_START}" name="user_flag" /></td>
		</tr></table>
	  </span></td>
	</tr>
<!-- FLAGHACK-end -->
	<tr>
	  <td class="row1"><span class="gen">{L_OCCUPATION}</span></td>
	  <td class="row2">
		<input class="post" type="text" name="occupation" size="35" maxlength="100" value="{OCCUPATION}" />
	  </td>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{L_INTERESTS}</span></td>
	  <td class="row2">
		<input class="post" type="text" name="interests" size="35" maxlength="150" value="{INTERESTS}" />
	  </td>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{L_SIGNATURE}</span><br />
		<span class="small">{L_SIGNATURE_EXP}<br />
		<br />
		{BBCODE_STATUS}<br />
		{SMILIES_STATUS}</span></td>
	  <td class="row2">
		<textarea class="post" name="signature" rows="6" cols="45">{SIGNATURE}</textarea>
	  </td>
	</tr>
	<tr>
	  <td class="cat" colspan="2">&nbsp;</td>
	</tr>
	<tr>
	  <th colspan="2">{L_PREFERENCES}</th>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{L_PUBLIC_VIEW_EMAIL}</span></td>
	  <td class="row2">
		<input type="radio" name="viewemail" value="1" {VIEW_EMAIL_YES} />
		<span class="gen">{L_YES}</span>&nbsp;&nbsp;
		<input type="radio" name="viewemail" value="0" {VIEW_EMAIL_NO} />
		<span class="gen">{L_NO}</span></td>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{L_HIDE_USER}</span></td>
	  <td class="row2">
		<input type="radio" name="hideonline" value="1" {HIDE_USER_YES} />
		<span class="gen">{L_YES}</span>&nbsp;&nbsp;
		<input type="radio" name="hideonline" value="0" {HIDE_USER_NO} />
		<span class="gen">{L_NO}</span></td>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{L_NOTIFY_ON_REPLY}</span></td>
	  <td class="row2">
		<input type="radio" name="notifyreply" value="1" {NOTIFY_REPLY_YES} />
		<span class="gen">{L_YES}</span>&nbsp;&nbsp;
		<input type="radio" name="notifyreply" value="0" {NOTIFY_REPLY_NO} />
		<span class="gen">{L_NO}</span></td>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{L_NOTIFY_ON_PRIVMSG}</span></td>
	  <td class="row2">
		<input type="radio" name="notifypm" value="1" {NOTIFY_PM_YES} />
		<span class="gen">{L_YES}</span>&nbsp;&nbsp;
		<input type="radio" name="notifypm" value="0" {NOTIFY_PM_NO} />
		<span class="gen">{L_NO}</span></td>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{L_ALWAYS_ADD_SIGNATURE}</span></td>
	  <td class="row2">
		<input type="radio" name="attachsig" value="1" {ALWAYS_ADD_SIGNATURE_YES} />
		<span class="gen">{L_YES}</span>&nbsp;&nbsp;
		<input type="radio" name="attachsig" value="0" {ALWAYS_ADD_SIGNATURE_NO} />
		<span class="gen">{L_NO}</span></td>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{L_BOARD_LANGUAGE}</span></td>
	  <td class="row2">{LANGUAGE_SELECT}</td>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{L_TIMEZONE}</span></td>
	  <td class="row2">{TIMEZONE_SELECT}</td>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{L_DATE_FORMAT}</span><br />
		<span class="small">{L_DATE_FORMAT_EXPLAIN}</span></td>
	  <td class="row2">
		<input class="post" type="text" name="dateformat" value="{DATE_FORMAT}" maxlength="16" />
	  </td>
	</tr>
	<tr>
	  <td class="cat" colspan="2">&nbsp;</td>
	</tr>
	<tr>
	  <th colspan="2">{L_AVATAR_PANEL}</th>
	</tr>
	<tr align="center">
	  <td class="row1" colspan="2">
	  <table width="70%">
	  <tr>
			<td width="65%"><span class="small">{L_AVATAR_EXP}</span></td>
			<td align="center"><span class="small">{L_CURRENT_IMAGE}</span><br />
			  {ADMIN_AVATAR}<br />
			  <input type="checkbox" name="avatardel" />
			  &nbsp;<span class="small">{L_DELETE_AVATAR}</span></td>
		</tr>
		</table>
	  </td>
	</tr>

	<!-- BEGIN avatar_local_upload -->
	<tr>
	  <td class="row1"><span class="gen">{L_UPLOAD_AVATAR_FILE}</span></td>
	  <td class="row2">
		<input type="hidden" name="MAX_FILE_SIZE" value="{AVATAR_SIZE}" />
		<input type="file" name="avatar" class="post" style="width: 200px"  />
	  </td>
	</tr>
	<!-- END avatar_local_upload -->
	<!-- BEGIN avatar_remote_upload -->
	<tr>
	  <td class="row1"><span class="gen">{L_UPLOAD_AVATAR_URL}</span></td>
	  <td class="row2">
		<input class="post" type="text" name="avatarurl" size="40" style="width: 200px"  />
	  </td>
	</tr>
	<!-- END avatar_remote_upload -->
	<!-- BEGIN avatar_remote_link -->
	<tr>
	  <td class="row1"><span class="gen">{L_LINK_REMOTE_AVATAR}</span></td>
	  <td class="row2">
		<input class="post" type="text" name="avatarremoteurl" size="40" style="width: 200px"  />
	  </td>
	</tr>
	<!-- END avatar_remote_link -->
	<!-- BEGIN avatar_local_gallery -->
	<tr>
	  <td class="row1"><span class="gen">{L_AVATAR_GALLERY}</span></td>
	  <td class="row2">
		<input type="submit" name="avatargallery" value="{L_SHOW_GALLERY}" class="liteoption" />
	  </td>
	</tr>
	<!-- END avatar_local_gallery -->

	<tr>
	  <td class="cat" colspan="2">&nbsp;</td>
	</tr>
	<tr>
	  <th colspan="2">{L_SPECIAL}</th>
	</tr>
	<tr>
	  <td class="row1" colspan="2"><span class="small">{L_SPECIAL_EXPLAIN}</span></td>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{L_UPLOAD_QUOTA}</span></td>
	  <td class="row2">{S_SELECT_UPLOAD_QUOTA}</td>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{L_PM_QUOTA}</span></td>
	  <td class="row2">{S_SELECT_PM_QUOTA}</td>
	</tr>

	<tr>
	  <td class="row1"><span class="gen">{L_USER_ACTIVE}</span></td>
	  <td class="row2">
		<input type="radio" name="user_status" value="1" {USER_ACTIVE_YES} />
		<span class="gen">{L_YES}</span>&nbsp;&nbsp;
		<input type="radio" name="user_status" value="0" {USER_ACTIVE_NO} />
		<span class="gen">{L_NO}</span></td>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{L_ALLOW_PM}</span></td>
	  <td class="row2">
		<input type="radio" name="user_allowpm" value="1" {ALLOW_PM_YES} />
		<span class="gen">{L_YES}</span>&nbsp;&nbsp;
		<input type="radio" name="user_allowpm" value="0" {ALLOW_PM_NO} />
		<span class="gen">{L_NO}</span></td>
	</tr>
	<tr>
	  <td class="row1"><span class="gen">{L_ALLOW_AVATAR}</span></td>
	  <td class="row2">
		<input type="radio" name="user_allowavatar" value="1" {ALLOW_AVATAR_YES} />
		<span class="gen">{L_YES}</span>&nbsp;&nbsp;
		<input type="radio" name="user_allowavatar" value="0" {ALLOW_AVATAR_NO} />
		<span class="gen">{L_NO}</span></td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">{L_SELECT_RANK}</span></td>
		<td class="row2"><select name="user_rank">{RANK_SELECT_BOX}</select></td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">{L_DELETE_USER}?</span></td>
		<td class="row2">
			<div>
				<input type="checkbox" name="deleteuser">
				{L_DELETE_USER_EXPLAIN}
			</div>
			<div id="del_user_options">
				<input type="checkbox" name="delete_user_posts">
				{L_DELETE_USER_POSTS}
			</div>
		</td>
	</tr>
	<tr>
	  <td class="catBottom" colspan="2">{S_HIDDEN_FIELDS}
		<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />
		&nbsp;&nbsp;
		<input type="reset" value="{L_RESET}" class="liteoption" />
	  </td>
	</tr>
</table></form>

<!--========================================================================-->
<!-- ENDIF / TPL_ADMIN_USER_EDIT -->

<!-- IF TPL_ADMIN_USER_AVATAR_GALLERY -->
<!--========================================================================-->

<h1>{L_USER_ADMIN}</h1>

<p>{L_USER_EXPLAIN}</p>
<br />

<form action="{S_PROFILE_ACTION}" method="post">

<table class="forumline">
	<tr>
	  <th colspan="{S_COLSPAN}">{L_AVATAR_GALLERY}</th>
	</tr>
	<tr>
	  <td class="catBottom" colspan="6"><span class="med">{L_SELECT_CATEGORY}:&nbsp;<select name="avatarcategory">{S_OPTIONS_CATEGORIES}</select>&nbsp;<input type="submit" class="liteoption" value="{L_GO}" name="avatargallery" /></span></td>
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
	  <td class="catBottom" colspan="{S_COLSPAN}">{S_HIDDEN_FIELDS}
		<input type="submit" name="submitavatar" value="{L_SELECT_AVATAR}" class="mainoption" />
		&nbsp;&nbsp;
		<input type="submit" name="cancelavatar" value="{L_RETURN_PROFILE}" class="liteoption" />
	  </td>
	</tr>
  </table>
</form>

<!--========================================================================-->
<!-- ENDIF / TPL_ADMIN_USER_AVATAR_GALLERY -->

