<h1 class="pagetitle">{PAGE_TITLE}<!-- IF GROUP_NAME --> :: {GROUP_NAME}<!-- ENDIF --></h1>
<!-- IF GROUP_TYPE --><div class="pad_4">{L_GROUP_TYPE}: <b>{GROUP_TYPE}</b></div><!-- ENDIF -->
<!-- IF PAGINATION --><div class="pad_4">{PAGE_NUMBER}</div><!-- ENDIF -->
<div class="spacer_4"></div>

<p class="nav"><a href="{U_INDEX}">{T_INDEX}</a></p>

<!-- IF SELECT_GROUP -->
<!--========================================================================-->

<table class="forumline">
<tr>
	<th>{L_MEMBERSHIP_DETAILS}</th>
</tr>
<tbody class="row1">
<tr>
	<td class="pad_4">
	<table class="bordered wAuto bCenter">
	<tr class="row2">
		<!-- BEGIN groups -->
		<td class="pad_8 med bold">{groups.MEMBERSHIP}</td>
		<!-- END groups -->
	</tr>
	<tr>
		<!-- BEGIN groups -->
		<td class="vTop pad_4">
			<ul>
				{groups.GROUP_SELECT}
			</ul>
		</td>
		<!-- END groups -->
	</tr>
	</table>
	</td>
</tr>
</tbody>
<tr>
	<td class="catBottom">&nbsp;</td>
</tr>
</table>

<!--========================================================================-->
<!-- ENDIF / SELECT_GROUP -->

<!-- IF GROUP_INFO -->
<!--========================================================================-->

<form action="{S_GROUP_ACTION}" method="post">
{S_HIDDEN_FIELDS}

<table class="forumline pad_4">
<col class="row1" width="20%">
<col class="row2" width="100%">
<tr>
	<th colspan="2">{L_GROUP_INFORMATION}</th>
</tr>
<tr>
	<td colspan="2"><h2>{GROUP_NAME}</h2></td>
</tr>
<tr>
	<td align="center" valign="top">
		{GROUP_AVATAR}
	</td>
	<td valign="top">
		<!-- IF GROUP_DESCRIPTION -->
		<div class="post_wrap">{GROUP_DESCRIPTION}</div>
		<!-- ELSE -->
			-
		<!-- ENDIF / GROUP_DESCRIPTION -->
	</td>
</tr>
<!-- IF GROUP_SIGNATURE -->
<tr>
	<td>{L_GROUP_SIGNATURE}:</td>
	<td><div class="post_wrap">{GROUP_SIGNATURE}</div></td>
</tr>
<!-- ENDIF / GROUP_SIGNATURE -->
<!-- IF RELEASE_GROUP -->
<tr>
	<td>{L_GROUP_TYPE}:</td>
	<td>{L_RELEASE_GROUP}</td>
</tr>
<!-- ENDIF -->
<tr>
	<td>{L_GROUP_TIME}:</td>
	<td>{GROUP_TIME}</td>
</tr>
<tr>
	<td>{L_GROUP_MEMBERSHIP}:</td>
	<td><p>{GROUP_DETAILS}
	<!-- IF SHOW_SUBSCRIBE_CONTROLS -->
		<input class="mainoption" type="submit" name="joingroup" value="{L_JOIN_A_GROUP}" onclick="return confirm('{L_JOIN_A_GROUP}?');" />
	<!-- ENDIF -->
	<!-- IF SHOW_UNSUBSCRIBE_CONTROLS -->
		<input class="mainoption" type="submit" name="{CONTROL_NAME}" value="{L_UNSUBSCRIBE_GROUP}" onclick="return confirm('{L_UNSUBSCRIBE_GROUP}?');" />
	<!-- ENDIF -->
	</p>
	</td>
</tr>
<!-- BEGIN switch_mod_option -->
<tr>
	<td>{L_GROUP_CONFIGURATION}:</td>
	<td>
		<a href="{U_GROUP_CONFIG}">{L_GROUP_GOTO_CONFIG}</a>
	</td>
</tr>
<!-- END switch_mod_option -->
</table>

</form>

<div class="spacer_10"></div>
<p class="nav"><a href="{U_GROUP_MEMBERS}" name="members">{L_GROUP_MEMBERS}</a><!-- IF RELEASE_GROUP -->&nbsp;::&nbsp;<a href="{U_GROUP_RELEASES}" name="releases">{L_GROUPS_RELEASES}</a><!-- ENDIF --></p>

<!-- IF MEMBERS -->
<form action="{S_GROUP_ACTION}" method="post" name="post">
{S_HIDDEN_FIELDS}

<table class="forumline tablesorter">
<thead>
<tr>
	<th class="{sorter: 'digit'}" ><b class="tbs-text">#</b></th>
	<th class="{sorter: false}" ><b class="tbs-text">{L_AVATAR}</b></th>
	<th class="{sorter: 'text'}" ><b class="tbs-text">{L_USERNAME}</b></th>
	<th class="{sorter: false}" ><b class="tbs-text">{L_PM}</b></th>
	<th class="{sorter: 'text'}" ><b class="tbs-text">{L_EMAIL}</b></th>
	<th class="{sorter: false}" ><b class="tbs-text">{L_LOCATION}</b></th>
	<th width="10%" class="{sorter: 'digit'}" ><b class="tbs-text">{L_JOINED}</b></th>
	<th class="{sorter: 'digit'}" ><b class="tbs-text">{L_POSTS_SHORT}</b></th>
	<th class="{sorter: false}" ><b class="tbs-text">{L_WEBSITE}</b></th>
	<th width="10%" class="{sorter: false}" ><b class="tbs-text">{L_EFFECTIVE_DATE}</b></th>
	<th class="{sorter: false}" ><b class="tbs-text">#</b></th>
</tr>
<tr>
	<td colspan="11" class="catTitle">{L_GROUP_MODERATOR}</td>
</tr>
<tr class="row1 tCenter">
	<td width="3%">{ROW_NUMBER}</td>
	<td width="3%" align="center">{MOD_AVATAR}</td>
	<td><b>{MOD_USER}</b></td>
	<td>{MOD_PM}</td>
	<td>{MOD_EMAIL}</td>
	<td>{MOD_FROM}</td>
	<td class="small">{MOD_JOINED}</td>
	<td>{MOD_POSTS}</td>
	<td>{MOD_WWW}</td>
	<td class="small">{MOD_TIME}</td>
	<td width="3%">&nbsp;</td>
</tr>
<tr>
	<td colspan="11" class="catTitle">{L_GROUP_MEMBERS}</td>
</tr>
</thead>

<!-- BEGIN member -->
<tr class="{member.ROW_CLASS} tCenter">
	<td width="3%">{member.ROW_NUMBER}</td>
	<td width="3%" align="center">{member.AVATAR_IMG}</td>
	<td>{member.USER}</td>
	<td>{member.PM}</td>
	<td>{member.EMAIL}</td>
	<td>{member.FROM}</td>
	<td class="small">{member.JOINED}</td>
	<td>{member.POSTS}</td>
	<td>{member.WWW}</td>
	<td class="small">{member.TIME}</td>
	<td width="3%">
		<!-- BEGIN switch_mod_option -->
		<input type="checkbox" name="members[]" value="{member.USER_ID}" />
		<!-- END switch_mod_option -->
	</td>
</tr>
<!-- END member -->

<!-- BEGIN switch_no_members -->
<tr>
	<td colspan="11" class="row1 tCenter pad_10">{L_NO_GROUP_MEMBERS}</td>
</tr>
<!-- END switch_no_members -->

<!-- BEGIN switch_hidden_group -->
<tr>
	<td colspan="11" class="row1 tCenter">{L_HIDDEN_GROUP_MEMBERS}</td>
</tr>
<!-- END switch_hidden_group -->

<!-- BEGIN switch_mod_option -->
<tfoot>
<tr>
	<td colspan="11" class="cat" style="padding: 2px 12px;">
		<p id="add_group_member" class="floatL">
			<input type="text" name="username" maxlength="50" size="20" />
			<input type="submit" name="add" value="{L_ADD_MEMBER}" class="mainoption" />
			<input type="button" name="usersubmit" value="{L_FIND_USERNAME}" class="liteoption" onclick="window.open('{U_SEARCH_USER}', '_bbsearch', IWP_US);return false;" />
		</p>
		<p class="floatR" style="padding-top: 1px;">
			<input type="submit" name="remove" value="{L_REMOVE_SELECTED}" class="mainoption" onclick="return confirm('{L_REMOVE_SELECTED}?');" />
		</p>
	</td>
</tr>
</tfoot>
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
<thead>
<tr>
	<th class="{sorter: false}" ><b class="tbs-text">#</b></th>
	<th class="{sorter: false}" ><b class="tbs-text">{L_AVATAR}</b></th>
	<th class="{sorter: 'text'}" ><b class="tbs-text">{L_USERNAME}</b></th>
	<th class="{sorter: false}" ><b class="tbs-text">{L_PM}</b></th>
	<th class="{sorter: 'text'}" ><b class="tbs-text">{L_EMAIL}</b></th>
	<th class="{sorter: false}" ><b class="tbs-text">{L_LOCATION}</b></th>
	<th class="{sorter: 'digit'}" ><b class="tbs-text">{L_JOINED}</b></th>
	<th class="{sorter: 'digit'}" ><b class="tbs-text">{L_POSTS_SHORT}</b></th>
	<th class="{sorter: false}" ><b class="tbs-text">{L_WEBSITE}</b></th>
</tr>
<tr>
	<td class="catTitle" colspan="9">{L_PENDING_MEMBERS}</td>
</tr>
</thead>
<!-- BEGIN pending -->
<tr class="{pending.ROW_CLASS} tCenter">
	<td width="3%"><input type="checkbox" name="pending_members[]" value="{pending.USER_ID}"/></td>
	<td width="3%">{pending.AVATAR_IMG}</td>
	<td>{pending.USER}</td>
	<td>{pending.PM}</td>
	<td>{pending.EMAIL}</td>
	<td>{pending.FROM}</td>
	<td>{pending.JOINED}</td>
	<td>{pending.POSTS}</td>
	<td>{pending.WWW}</td>
</tr>
<!-- END pending -->
<tfoot>
<tr>
	<td class="cat" colspan="9">
		<input type="submit" name="approve" value="{L_APPROVE_SELECTED}" onclick="return confirm('{L_APPROVE_SELECTED}?');" class="mainoption" />
		&nbsp;
		<input type="submit" name="deny" value="{L_DENY_SELECTED}" onclick="return confirm('{L_DENY_SELECTED}?');" class="liteoption" />
	</td>
</tr>
</tfoot>
</table>
<!-- ENDIF / PENDING_USERS -->
</form>
<!-- ENDIF / MEMBERS -->

<!-- IF RELEASES -->
<table class="forumline tablesorter">
	<thead>
	<tr>
		<th class="{sorter: false}" ><b class="tbs-text">#</b></th>
		<th class="{sorter: false}" ><b class="tbs-text">{L_AVATAR}</b></th>
		<th class="{sorter: 'text'}" ><b class="tbs-text">{L_USERNAME}</b></th>
		<th class="{sorter: 'text'}" ><b class="tbs-text">{L_TOPIC}</b></th>
		<th class="{sorter: 'text'}" ><b class="tbs-text">{L_FORUM}</b></th>
		<th class="{sorter: 'digit'}" width="3%"><b class="tbs-text">{L_BT_CREATED}</b></th>
	</tr>
	<tr>
		<td class="catTitle" colspan="9">{L_GROUPS_RELEASES}</td>
	</tr>
	</thead>
	<!-- BEGIN releases -->
	<tr class="{releases.ROW_CLASS} tCenter">
		<td width="3%">{releases.ROW_NUMBER}</td>
		<td width="3%">{releases.AVATAR_IMG}</td>
		<td><b>{releases.RELEASER}</b></td>
		<td>{releases.RELEASE_NAME}</td>
		<td>{releases.RELEASE_FORUM}</td>
		<td>{releases.RELEASE_TIME}</td>
	</tr>
	<!-- END releases -->
	<tfoot>
	<tr>
		<td class="cat" colspan="9" align="center"><b><a href="{U_SEARCH_RELEASES}">{L_MORE_RELEASES}</a></b></td>
	</tr>
	</tfoot>
</table>

<div class="bottom_info">
	<div class="nav">
		<p style="float: left">{PAGE_NUMBER}</p>
		<p style="float: right">{PAGINATION}</p>
		<div class="clear"></div>
	</div>
</div>
<!-- ENDIF / RELEASES -->

<!--========================================================================-->
<!-- ENDIF / GROUP_INFO -->

<!--bottom_info-->
<div class="bottom_info">
<div class="spacer_4"></div>
	<div id="timezone">
		<p>{CURRENT_TIME}</p>
		<p>{S_TIMEZONE}</p>
	</div>
	<div class="clear"></div>
</div><!--/bottom_info-->
