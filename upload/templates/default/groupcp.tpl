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
	<td>{L_GROUP_TYPE}:</td>
	<td>
		<p>
			<label><input type="radio" name="group_type" value="{S_GROUP_OPEN_TYPE}" {S_GROUP_OPEN_CHECKED} />{L_GROUP_OPEN}</label> &nbsp;&nbsp;
			<label><input type="radio" name="group_type" value="{S_GROUP_CLOSED_TYPE}" {S_GROUP_CLOSED_CHECKED} />{L_GROUP_CLOSED}</label> &nbsp;&nbsp;
			<label><input type="radio" name="group_type" value="{S_GROUP_HIDDEN_TYPE}" {S_GROUP_HIDDEN_CHECKED} />{L_GROUP_HIDDEN}</label>
			&nbsp; <input class="mainoption" type="submit" name="groupstatus" value="{L_UPDATE}" />
		</p>
	</td>
</tr>
<!-- END switch_mod_option -->
</table>

</form>

<div class="spacer_10"></div>

<form action="{S_GROUPCP_ACTION}" method="post" name="post">
{S_HIDDEN_FIELDS}

<table class="forumline tablesorter">
<thead>
<tr>
	<th class="{sorter: 'digit'}" ><b class="tbs-text">#</b></th>
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
	<td colspan="10" class="catTitle">{L_GROUP_MODERATOR}</td>
</tr>
<tr class="row1 tCenter">
	<td width="3%">{ROW_NUMBER}</td>
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
	<td colspan="10" class="catTitle">{L_GROUP_MEMBERS}</td>
</tr>
</thead>

<!-- BEGIN member -->
<tr class="{member.ROW_CLASS} tCenter">
	<td width="3%">{member.ROW_NUMBER}</td>
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
	<td colspan="10" class="row1 tCenter pad_10">{L_NO_GROUP_MEMBERS}</td>
</tr>
<!-- END switch_no_members -->

<!-- BEGIN switch_hidden_group -->
<tr>
	<td colspan="10" class="row1 tCenter">{L_HIDDEN_GROUP_MEMBERS}</td>
</tr>
<!-- END switch_hidden_group -->

<!-- BEGIN switch_mod_option -->
<tfoot>
<tr>
	<td colspan="10" class="cat" style="padding: 2px 12px;">
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
	<th class="{sorter: false}" ><b class="tbs-text">{L_SELECT}</b></th>
	<th class="{sorter: 'text'}" ><b class="tbs-text">{L_USERNAME}</b></th>
	<th class="{sorter: false}" ><b class="tbs-text">{L_PM}</b></th>
	<th class="{sorter: 'text'}" ><b class="tbs-text">{L_EMAIL}</b></th>
	<th class="{sorter: false}" ><b class="tbs-text">{L_LOCATION}</b></th>
	<th class="{sorter: 'digit'}" ><b class="tbs-text">{L_JOINED}</b></th>
	<th class="{sorter: 'digit'}" ><b class="tbs-text">{L_POSTS_SHORT}</b></th>
	<th class="{sorter: false}" ><b class="tbs-text">{L_WEBSITE}</b></th>
</tr>
<tr>
    <td class="catTitle" colspan="8">{L_PENDING_MEMBERS}</td>
</tr>
</thead>
<!-- BEGIN pending -->
<tr class="{pending.ROW_CLASS} tCenter">
	<td><input type="checkbox" name="pending_members[]" value="{pending.USER_ID}"/></td>
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
	<td class="cat" colspan="8">
		<input type="submit" name="approve" value="{L_APPROVE_SELECTED}" onclick="return confirm('{L_APPROVE_SELECTED}?');" class="mainoption" />
		&nbsp;
		<input type="submit" name="deny" value="{L_DENY_SELECTED}" onclick="return confirm('{L_DENY_SELECTED}?');" class="liteoption" />
	</td>
</tr>
</tfoot>
</table>
<!-- ENDIF / PENDING_USERS -->

</form>

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