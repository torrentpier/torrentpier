<h1 class="pagetitle">{PAGE_TITLE}<!-- IF GROUP_NAME --> :: {GROUP_NAME}<!-- ENDIF --></h1>

<p class="nav"><a href="{U_INDEX}">{T_INDEX}</a></p>

<!-- IF SELECT_GROUP -->
<!--========================================================================-->

<table class="forumline">
<col class="row1">
<col class="row2">
<tr>
	<th colspan="2">{L_MEMBERSHIP_DETAILS}</th>
</tr>
<!-- BEGIN groups -->
<tr>
	<td width="30%"><span class="gen">{groups.MEMBERSHIP}</span></td>
	<td style="padding: 5px 30px;">
	<form method="get" action="{S_USERGROUP_ACTION}">
	{S_HIDDEN_FIELDS}
		<div style="float: left;">
			{groups.GROUP_SELECT}
		</div>
		<div style="float: right;">
			<input type="submit" value="{L_VIEW_INFORMATION}" class="liteoption" />
		</div>
	</form>
	</td>
</tr>
<!-- END groups -->
</table>

<!--========================================================================-->
<!-- ENDIF / SELECT_GROUP -->

<!-- IF GROUP_INFO -->
<!--========================================================================-->

<form action="{S_GROUPCP_ACTION}" method="post">
{S_HIDDEN_FIELDS}

<table class="forumline pad_4">
<col class="row1" width="20%">
<col class="row2" width="100%">
<tr>
	<th colspan="2">{L_GROUP_INFORMATION}</th>
</tr>
<tr>
	<td>{L_GROUP_NAME}:</td>
	<td><b>{GROUP_NAME}</b></td>
</tr>
<!-- IF GROUP_DESCRIPTION -->
<tr>
	<td>{L_GROUP_DESCRIPTION}:</td>
	<td>{GROUP_DESCRIPTION}</td>
</tr>
<!-- ENDIF / GROUP_DESCRIPTION -->
<tr>
	<td>{L_GROUP_MEMBERSHIP}:</td>
	<td><p>{GROUP_DETAILS}</p>
	<!-- IF SHOW_SUBSCRIBE_CONTROLS -->
	<p class="mrg_4">
		<input class="mainoption" type="submit" name="joingroup" value="{L_JOIN_A_GROUP}" onclick="return confirm('{L_JOIN_A_GROUP}?');" />&nbsp;&nbsp;
	</p>
	<!-- ENDIF -->
	<!-- IF SHOW_UNSUBSCRIBE_CONTROLS -->
	<p class="mrg_4">
		<input class="mainoption" type="submit" name="{CONTROL_NAME}" value="{L_UNSUBSCRIBE_GROUP}" onclick="return confirm('{L_UNSUBSCRIBE_GROUP}?');" />&nbsp;&nbsp;
	</p>
	<!-- ENDIF -->
	</td>
</tr>
<!-- BEGIN switch_mod_option -->
<tr>
	<td>{L_GROUP_TYPE}:</td>
	<td>
		<p>
			<label><input type="radio" name="group_type" value="{S_GROUP_OPEN_TYPE}" {S_GROUP_OPEN_CHECKED} />{L_GROUP_OPEN}</label> &nbsp;&nbsp;
			<label><input type="radio" name="group_type" value="{S_GROUP_CLOSED_TYPE}" {S_GROUP_CLOSED_CHECKED} />{L_GROUP_CLOSED}</label> &nbsp;&nbsp;
			<label><input type="radio" name="group_type" value="{S_GROUP_HIDDEN_TYPE}" {S_GROUP_HIDDEN_CHECKED} />{L_GROUP_HIDDEN}</label>
		</p>
		<p class="mrg_4">
			<input class="mainoption" type="submit" name="groupstatus" value="{L_UPDATE}" />
		</p>
	</td>
</tr>
<!-- END switch_mod_option -->
</table>

</form>

<div class="spacer_10"></div>

<form action="{S_GROUPCP_ACTION}" method="post" name="post">
{S_HIDDEN_FIELDS}

<table class="forumline">
<tr>
	<th>{L_PRIVATE_MESSAGE}</th>
	<th>{L_USERNAME}</th>
	<th>{L_POSTS_SHORT}</th>
	<th>{L_LOCATION}</th>
	<th>{L_EMAIL}</th>
	<th>{L_WEBSITE}</th>
	<th>{L_SELECT}</th>
</tr>
<tr>
	<td colspan="7" class="catTitle">{L_GROUP_MODERATOR}</td>
</tr>
<tr class="row1 tCenter">
	<td>{MOD_PM_IMG}</td>
	<td><a href="{U_MOD_VIEWPROFILE}" class="gen"><b>{MOD_USERNAME}</b></a></td>
	<td>{MOD_POSTS}</td>
	<td>{MOD_FROM}</td>
	<td>{MOD_EMAIL_IMG}</td>
	<td>{MOD_WWW_IMG}</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td colspan="7" class="catTitle">{L_GROUP_MEMBERS}</td>
</tr>
<!-- BEGIN member -->
<tr class="{member.ROW_CLASS} tCenter">
	<td>{member.PM_IMG}</td>
	<td><a href="{member.U_VIEWPROFILE}" class="gen">{member.USERNAME}</a></td>
	<td>{member.POSTS}</td>
	<td>{member.FROM}</td>
	<td>{member.EMAIL_IMG}</td>
	<td>{member.WWW_IMG}</td>
	<td>
		<!-- BEGIN switch_mod_option -->
		<input type="checkbox" name="members[]" value="{member.USER_ID}" />
		<!-- END switch_mod_option -->
	</td>
</tr>
<!-- END member -->

<!-- BEGIN switch_no_members -->
<tr>
	<td colspan="7" class="row1 tCenter">{L_NO_GROUP_MEMBERS}</td>
</tr>
<!-- END switch_no_members -->

<!-- BEGIN switch_hidden_group -->
<tr>
	<td colspan="7" class="row1 tCenter">{L_HIDDEN_GROUP_MEMBERS}</td>
</tr>
<!-- END switch_hidden_group -->

<!-- BEGIN switch_mod_option -->
<tr>
	<td colspan="7" class="cat" style="padding: 2px 12px;">
		<p id="add_group_member" class="floatL">
			<input type="text" name="username" maxlength="50" size="20" />
			<input type="submit" name="add" value="{L_ADD_MEMBER}" class="mainoption" />
			<input type="button" name="usersubmit" value="{L_FIND_USERNAME}" class="liteoption" onclick="window.open('{U_SEARCH_USER}', '_phpbbsearch', 'HEIGHT=250,resizable=yes,WIDTH=400');return false;" />
		</p>
		<p class="floatR" style="padding-top: 1px;">
			<input type="submit" name="remove" value="{L_REMOVE_SELECTED}" class="mainoption" onclick="return confirm('{L_REMOVE_SELECTED}?');" />
		</p>
	</td>
</tr>
<!-- END switch_mod_option -->

</table>

<div class="bottom_info">

	<div class="nav">
		<p style="float: left">{PAGE_NUMBER}</p>
		<p style="float: right">{PAGINATION}</p>
		<div class="clear"></div>
	</div>

</div><!--/bottom_info-->

<!-- IF PENDING_USERS -->
<table class="forumline">
<tr>
		<th>{L_SELECT}</th>
		<th>{L_USERNAME}</th>
		<th>{L_POSTS_SHORT}</th>
		<th>{L_LOCATION}</th>
		<th>{L_EMAIL}</th>
		<th>{L_WEBSITE}</th>
		<th>{L_PRIVATE_MESSAGE}</th>
</tr>
<tr>
		<td class="catTitle" colspan="8">{L_PENDING_MEMBERS}</td>
</tr>
<!-- BEGIN pending -->
<tr class="{pending.ROW_CLASS} tCenter">
	<td><input type="checkbox" name="pending_members[]" value="{pending.USER_ID}" /></td>
	<td><a href="{pending.U_VIEWPROFILE}" class="gen">{pending.USERNAME}</a></td>
	<td>{pending.POSTS}</td>
	<td>{pending.FROM}</td>
	<td>{pending.EMAIL_IMG}</td>
	<td>{pending.WWW_IMG}</td>
	<td>{pending.PM_IMG}</td>
</tr>
<!-- END pending -->
<tr>
	<td class="cat" colspan="7">
		<input type="submit" name="approve" value="{L_APPROVE_SELECTED}" onclick="return confirm('{L_APPROVE_SELECTED}?');" class="mainoption" />
		&nbsp;
		<input type="submit" name="deny" value="{L_DENY_SELECTED}" onclick="return confirm('{L_DENY_SELECTED}?');" class="liteoption" />
	</td>
</tr>
</table>

<!-- ENDIF / PENDING_USERS -->

</form>

<!--========================================================================-->
<!-- ENDIF / GROUP_INFO -->

<!--bottom_info-->
<div class="bottom_info">

<div class="spacer_4"></div>

	<div id="timezone">
		<p>{LAST_VISIT_DATE}</p>
		<p>{CURRENT_TIME}</p>
		<p>{S_TIMEZONE}</p>
	</div>
	<div class="clear"></div>

</div><!--/bottom_info-->