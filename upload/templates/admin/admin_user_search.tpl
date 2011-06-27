
<!-- IF TPL_ADMIN_USER_SEARCH_MAIN -->
<!--========================================================================-->

<h1>{L_USER_SEARCH}</h1>

<p>{L_SEARCH_EXPLAIN}</p>
<br />

<form method="post" name="post" action="{S_SEARCH_ACTION}"><input type="hidden" name="dosearch" value="true" />

<table class="forumline">
	<tr>
		<th>&nbsp;</th>
	</tr>
	<tr>
		<td class="row1" nowrap="nowrap"><span class="gen"><b>{L_USERNAME}:</b>&nbsp;<input class="post" type="text" name="username" maxlength="255" size="25" />&nbsp;<input type="submit" class="post2" name="search_username" value="{L_SEARCH}" /> {L_REGEX} <input type="checkbox" name="search_username_regex" value="true" /></span></td>
	</tr>
	<tr>
		<td class="row2"><span class="small">{L_USERNAME_EXPLAIN}</span></td>
	</tr>
	<tr>
	  <td class="row3"><img src="{SPACER}" width="1" height="1" alt="."></td>
	</tr>
	<tr>
		<td class="row1" nowrap="nowrap"><span class="gen"><b>{L_EMAIL}:</b>&nbsp;<input class="post" type="text" name="email" maxlength="255" size="25" />&nbsp;<input type="submit" class="post2" name="search_email" value="{L_SEARCH}" /> {L_REGEX} <input type="checkbox" name="search_email_regex" value="true" /></span></td>
	</tr>
	<tr>
		<td class="row2"><span class="small">{L_EMAIL_EXPLAIN}</span></td>
	</tr>
	<tr>
	  <td class="row3"><img src="{SPACER}" width="1" height="1" alt="."></td>
	</tr>
	<tr>
		<td class="row1" nowrap="nowrap"><span class="gen"><b>{L_IP}:</b>&nbsp;<input class="post" type="text" name="ip_address" maxlength="255" size="25" />&nbsp;<input type="submit" class="post2" name="search_ip" value="{L_SEARCH}" /></span></td>
	</tr>
	<tr>
		<td class="row2"><span class="small">{L_IP_EXPLAIN}</span></td>
	</tr>
	<tr>
	  <td class="row3"><img src="{SPACER}" width="1" height="1" alt="."></td>
	</tr>
	<tr>
		<td class="row1" nowrap="nowrap"><span class="gen"><b>{L_USERS_JOINED}</b>&nbsp;<select name="date_type" class="post"><option value="before" selected="selected">{L_BEFORE}</option><option value="after">{L_AFTER}</option></select>&nbsp;<input class="post" type="text" name="date_year" value="{YEAR}" size="4" maxlength="4" />/<input class="post" type="text" name="date_month" value="{MONTH}" size="2" maxlength="2" />/<input class="post" type="text" name="date_day" value="{DAY}" maxlength="2" size="2" />&nbsp;<input type="submit" class="post2" name="search_joindate" value="{L_SEARCH}" /></span></td>
	</tr>
	<tr>
		<td class="row2"><span class="small">{L_JOIN_DATE_EXPLAIN}</span></td>
	</tr>
	<tr>
	  <td class="row3"><img src="{SPACER}" width="1" height="1" alt="."></td>
	</tr>
	<!-- BEGIN groups_exist -->
	<tr>
		<td class="row1" nowrap="nowrap"><span class="gen"><b>{L_GROUP_MEMBERS}:</b>&nbsp;<select name="group_id" class="post">{GROUP_LIST}</select>&nbsp;<input type="submit" class="post2" name="search_group" value="{L_SEARCH}" /></span></td>
	</tr>
	<tr>
		<td class="row2"><span class="small">{L_SEARCH_USERS_GROUPS_EXPLAIN}</span></td>
	</tr>
	<tr>
	  <td class="row3"><img src="{SPACER}" width="1" height="1" alt="."></td>
	</tr>
	<!-- END groups_exist -->
	<!-- BEGIN ranks_exist -->
	<tr>
		<td class="row1" nowrap="nowrap"><span class="gen"><b>{L_POSTER_RANK}:</b>&nbsp;<select name="rank_id" class="post">{RANK_SELECT_BOX}</select>&nbsp;<input type="submit" class="post2" name="search_rank" value="{L_SEARCH}" /></span></td>
	</tr>
	<tr>
		<td class="row2"><span class="small">{L_SEARCH_USERS_RANKS_EXPLAIN}</span></td>
	</tr>
	<tr>
	  <td class="row3"><img src="{SPACER}" width="1" height="1" alt="."></td>
	</tr>
	<!-- END ranks_exist -->
	<tr>
		<td class="row1" nowrap="nowrap"><span class="gen"><b>{L_POSTCOUNT}</b>&nbsp;<select name="postcount_type"><option value="equals" selected="selected">{L_EQUALS}</option><option value="lesser">{L_LESSERTHAN}</option><option value="greater">{L_GREATERTHAN}</option></select>&nbsp;<input class="post" type="text" name="postcount_value" maxlength="25" size="5" />&nbsp;<input type="submit" class="post2" name="search_postcount" value="{L_SEARCH}" /></span></td>
	</tr>
	<tr>
		<td class="row2"><span class="small">{L_POSTCOUNT_EXPLAIN}</span></td>
	</tr>
	<tr>
	  <td class="row3"><img src="{SPACER}" width="1" height="1" alt="."></td>
	</tr>
	<tr>
		<td class="row1" nowrap="nowrap"><span class="gen"><b>{L_USERFIELD}:</b>&nbsp;<select name="userfield_type"><option value="icq" selected="selected">{L_ICQ}</option><option value="website">{L_WEBSITE}</option><option value="location">{L_LOCATION}</option><option value="interests">{L_INTERESTS}</option></select>&nbsp;<input class="post" type="text" name="userfield_value" maxlength="25" size="25" />&nbsp;<input type="submit" class="post2" name="search_userfield" value="{L_SEARCH}" /> {L_REGEX} <input type="checkbox" name="search_userfield_regex" value="true" /></span></td>
	</tr>
	<tr>
		<td class="row2"><span class="small">{L_USERFIELD_EXPLAIN}</span></td>
	</tr>
	<tr>
	  <td class="row3"><img src="{SPACER}" width="1" height="1" alt="."></td>
	</tr>
	<tr>
		<td class="row1" nowrap="nowrap"><span class="gen"><b>{L_LASTVISITED}</b>&nbsp;<select name="lastvisited_type"><option value="in">{L_IN_THE_LAST}</option><option value="after">{L_AFTER_THE_LAST}</option></select>&nbsp;<select name="lastvisited_days">{LASTVISITED_LIST}</select>&nbsp;<input type="submit" class="post2" name="search_lastvisited" value="{L_SEARCH}" /></span></td>
	</tr>
	<tr>
		<td class="row2"><span class="small">{L_LASTVISITED_EXPLAIN}</span></td>
	</tr>
	<tr>
	  <td class="row3"><img src="{SPACER}" width="1" height="1" alt="."></td>
	</tr>
	<tr>
		<td class="row1" nowrap="nowrap"><span class="gen"><b>{L_LANGUAGE}:</b>&nbsp;{LANGUAGE_LIST}&nbsp;<input type="submit" class="post2" name="search_language" value="{L_SEARCH}" /></span></td>
	</tr>
	<tr>
		<td class="row2"><span class="small">{L_LANGUAGE_EXPLAIN}</span></td>
	</tr>
	<tr>
	  <td class="row3"><img src="{SPACER}" width="1" height="1" alt="."></td>
	</tr>
	<tr>
		<td class="row1" nowrap="nowrap"><span class="gen"><b>{L_TIMEZONE}:</b>&nbsp;{TIMEZONE_LIST}&nbsp;<input type="submit" class="post2" name="search_timezone" value="{L_SEARCH}" /></span></td>
	</tr>
	<tr>
		<td class="row2"><span class="small">{L_TIMEZONE_EXPLAIN}</span></td>
	</tr>
	<tr>
	  <td class="row3"><img src="{SPACER}" width="1" height="1" alt="."></td>
	</tr>
	<!-- BEGIN forums_exist -->
	<tr>
		<td class="row1" nowrap="nowrap"><span class="gen"><b>{L_MODERATORS_OF}:</b>&nbsp;<select name="moderators_forum">{FORUMS_LIST}</select>&nbsp;<input type="submit" class="post2" name="search_moderators" value="{L_SEARCH}" /></span></td>
	</tr>
	<tr>
		<td class="row2"><span class="small">{L_MODERATORS_OF_EXPLAIN}</span></td>
	</tr>
	<tr>
	  <td class="row3"><img src="{SPACER}" width="1" height="1" alt="."></td>
	</tr>
	<!-- END forums_exist -->
	<tr>
		<td class="row1" nowrap="nowrap"><span class="gen"><select name="misc" class="post"><option value="admins" selected="selected">{L_ADMINS}</option><option value="mods">{L_MODERATORS}</option><option value="banned">{L_BANNED_USERS}</option><option value="disabled">{L_DISABLED_USERS}</option><option value="disabled_pms">{L_USERS_DISABLED_PMS}</option></select>&nbsp;<input type="submit" class="post2" class="post2" name="search_misc" value="{L_SEARCH}" /></span></td>
	</tr>
	<tr>
		<td class="row2"><span class="small">{L_MISC_EXPLAIN}</span></td>
	</tr>
	<tr>
		<td class="row3"><img src="{SPACER}" width="1" height="1" alt="."></td>
	</tr>
</table>
</form>

<!--========================================================================-->
<!-- ENDIF / TPL_ADMIN_USER_SEARCH_MAIN -->

<!-- IF TPL_ADMIN_USER_SEARCH_RESULTS -->
<!--========================================================================-->

<h1>{L_USER_SEARCH}</h1>

<p>{NEW_SEARCH}</p>
<br />

<form action="{S_POST_ACTION}" method="post" name="post">
<table width="98%" align="center">
	<tr>
		<td align="center" class="nav"><span class="gen">{L_SORT_OPTIONS}</span> <a href="{U_USERNAME}">{L_USERNAME}</a> | <a href="{U_EMAIL}">{L_EMAIL}</a> | <a href="{U_POSTS}">{L_POSTS}</a> | <a href="{U_JOINDATE}">{L_JOINDATE}</a> | <a href="{U_LASTVISIT}">{L_LASTVISIT}</a></td>
	</tr>
</table>
<p>&nbsp;</p>
<table width="98%" align="center">
	<tr>
		<td class="nav"><span class="gen">{PAGE_NUMBER}</span></td>
		<td align="right" class="nav" nowrap="nowrap"><span class="gen">{PAGINATION}</span></td>
	</tr>
</table>
<table class="forumline">
	<tr>
		<th>{L_USERNAME}</th>
		<th>{L_EMAIL}</th>
		<th>{L_JOINDATE}</th>
		<th>{L_POSTS}</th>
		<th>{L_LASTVISIT}</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<th>&nbsp;</th>
		<th>{L_ACCOUNT_STATUS}</th>
	</tr>
	<!-- BEGIN userrow -->
	<tr>
		<td class="{userrow.ROW_CLASS}" align="center" nowrap="nowrap"><span class="gen">&nbsp;<a href="{userrow.U_VIEWPROFILE}" class="gen">{userrow.USERNAME}</a>&nbsp;</span></td>
		<td class="{userrow.ROW_CLASS}" align="center" nowrap="nowrap"><span class="gen">&nbsp;{userrow.EMAIL}&nbsp;</span></td>
		<td class="{userrow.ROW_CLASS}" align="center" nowrap="nowrap"><span class="gen">&nbsp;{userrow.JOINDATE}&nbsp;</span></td>
		<td class="{userrow.ROW_CLASS}" align="center" nowrap="nowrap"><span class="gen">&nbsp;<a href="{userrow.U_VIEWPOSTS}" class="gen">{userrow.POSTS}</a>&nbsp;</span></td>
		<td class="{userrow.ROW_CLASS}" align="center" nowrap="nowrap"><span class="gen">&nbsp;{userrow.LASTVISIT}&nbsp;</span></td>
		<td class="{userrow.ROW_CLASS}" align="center" nowrap="nowrap"><span class="gen">&nbsp;<a href="{userrow.U_MANAGE}" class="gen">{L_MANAGE}</a>&nbsp;</span></td>
		<td class="{userrow.ROW_CLASS}" align="center" nowrap="nowrap"><span class="gen">&nbsp;<a href="{userrow.U_PERMISSIONS}" class="gen">{L_PERMISSIONS}</a>&nbsp;</span></td>
		<td class="{userrow.ROW_CLASS}" align="center" nowrap="nowrap"><span class="gen">&nbsp;{userrow.BAN}&nbsp;</span></td>
		<td class="{userrow.ROW_CLASS}" align="center" nowrap="nowrap"><span class="gen">&nbsp;{userrow.ABLED}&nbsp;</span></td>
	</tr>
	<!-- END userrow -->
	<tr>
		<td class="row3" colspan="9"><img src="{SPACER}" width="1" height="1" alt="."></td>
	</tr>
</table>
<table width="100%">
	<tr>
		<td align="right" valign="top"></td>
	</tr>
</table>
</form>

<!--========================================================================-->
<!-- ENDIF / TPL_ADMIN_USER_SEARCH_RESULTS -->

