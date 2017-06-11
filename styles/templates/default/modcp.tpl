<h1 class="pagetitle">{PAGE_TITLE}</h1>

<p class="nav"><a href="{U_INDEX}">{T_INDEX}</a></p>

<!-- IF TPL_MODCP_IP -->
<!--========================================================================-->

<table class="forumline">
<tr>
	<th>{L_IP_INFO}</th>
</tr>
<tr>
	<td class="catTitle">{L_THIS_POSTS_IP}</td>
</tr>
<tr>
	<td class="row1 pad_6">
		<p class="floatL"><b>{IP}</b></p>
		<p class="floatR">[ <a href="{U_LOOKUP_IP}">{L_LOOKUP_IP}</a> ]&nbsp;</p>
	</td>
</tr>
<tr>
	<td class="catTitle">{L_USERS_THIS_IP}</td>
</tr>
<!-- BEGIN userrow -->
<tr>
	<td class="{userrow.ROW_CLASS} pad_4 nowrap">
		<p class="floatL" style="width: 160px;"><a href="{userrow.U_PROFILE}"><b>{userrow.USERNAME}</b></a></p>
		<p class="floatL">[ {L_POSTS}: {userrow.POSTS} ]</p>
		<p class="floatR">[ <a href="{userrow.U_SEARCHPOSTS}">{L_SEARCH_USER_POSTS_SHORT}</a> ]&nbsp;</p>
	</td>
</tr>
<!-- END userrow -->
<tr>
	<td class="catTitle">{L_OTHER_IP_THIS_USER}</td>
</tr>
<!-- BEGIN iprow -->
<tr>
	<td class="{iprow.ROW_CLASS} pad_4 nowrap">
		<p class="floatL" style="_width: 160px; min-width: 160px;">{iprow.IP}</p>
		<p class="floatL">[ {L_POSTS}: {iprow.POSTS} ]</p>
		<p class="floatR">[ <a href="{iprow.U_LOOKUP_IP}">{L_LOOKUP_IP}</a> ]&nbsp;</p>
	</td>
</tr>
<!-- END iprow -->
</table>

<!--========================================================================-->
<!-- ENDIF / TPL_MODCP_IP -->

<!-- IF TPL_MODCP_MOVE -->
<!--========================================================================-->

<form action="{S_MODCP_ACTION}" method="post">
{S_HIDDEN_FIELDS}

<table class="forumline">
<tr>
	<th><b>{MESSAGE_TITLE}</b></th>
</tr>
<tr>
	<td class="row1">

		<div class="mrg_14 tCenter">
			<table class="borderless bCenter" cellspacing="0" cellpadding="0">
			<tr>
				<td class="nowrap vTop">{L_MOVE_TO_FORUM}</td>
				<td>
					<p>{S_FORUM_SELECT}</p>
				</td>
			</tr>
			</table>
		</div>

		<!-- IF SHOW_LEAVESHADOW || SHOW_BOT_OPTIONS -->
		<div style="margin: -6px;">
		<table class="borderless bCenter" cellspacing="0" cellpadding="0">
		<tr>
			<td class="nowrap">
				<!-- IF SHOW_LEAVESHADOW -->
				<p class="mrg_2"><input type="checkbox" name="move_leave_shadow" id="move_leave_shadow" /><label for="move_leave_shadow">{L_LEAVE_SHADOW_TOPIC}</label></p>
				<!-- ENDIF -->
				<!-- IF SHOW_BOT_OPTIONS -->
				<p class="mrg_2"><input type="checkbox" name="insert_bot_msg" id="insert_bot_msg" checked="checked" /><label for="insert_bot_msg">{L_BOT_LEAVE_MSG_MOVED}</label></p>
				<!-- ENDIF -->
			</td>
		</tr>
		</table>
		</div>
		<!-- ENDIF -->

		<h4 class="mrg_14 tCenter">{MESSAGE_TEXT}</h4>

		<!-- IF TOPIC_TITLES -->
		<table class="borderless bCenter">
		<tr>
			<td class="med">
				<ul><li>{TOPIC_TITLES}</li></ul>
			</td>
		</tr>
		</table>
		<!-- ENDIF -->

		<div class="mrg_14 tCenter">
			<input class="mainoption" type="submit" name="confirm" value="{L_YES}" />&nbsp;
			<input class="liteoption" type="submit" name="cancel" value="{L_NO}" />
		</div>

	</td>
</tr>
</table>

</form>

<!--========================================================================-->
<!-- ENDIF / TPL_MODCP_MOVE -->

<div class="bottom_info">

	<div class="spacer_4"></div>

	<div id="timezone">
		<p>{CURRENT_TIME}</p>
		<p>{S_TIMEZONE}</p>
	</div>
	<div class="clear"></div>

</div><!--/bottom_info-->
