
<!-- IF TPL_ADMIN_FRAMESET -->
<!--========================================================================-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html dir="{L_CONTENT_DIRECTION}">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={L_CONTENT_ENCODING}" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<title>Administration</title>
</head>

<frameset cols="220,*" rows="*" border="1" framespacing="1" frameborder="yes">
  <frame src="{S_FRAME_NAV}" name="nav" marginwidth="0" marginheight="0" scrolling="auto">
  <frame src="{S_FRAME_MAIN}" name="main" marginwidth="0" marginheight="0" scrolling="auto">
</frameset>

<noframes>
	<body bgcolor="#FFFFFF" text="#000000">
		<p>Sorry, your browser doesn't seem to support frames</p>
	</body>
</noframes>

</html>
<!--========================================================================-->
<!-- ENDIF / TPL_ADMIN_FRAMESET -->

<!-- IF TPL_ADMIN_NAVIGATE -->
<!--========================================================================-->

<style type="text/css">
body { background: #E5E5E5; min-width: 10px; }
#body_container { background: #E5E5E5; padding: 4px 3px 4px; }
table.forumline { margin: 0 auto; }
</style>

<table class="forumline" id="acp_main_nav">
	<col class="row1">
	<tr>
		<th>{L_ADMIN}</th>
	</tr>
	<tr>
		<td><a href="{U_ADMIN_INDEX}" target="main" class="med">{L_ADMIN_INDEX}</a></td>
	</tr>
	<tr>
		<td><a href="{U_FORUM_INDEX}" target="_parent" class="med">{L_MAIN_INDEX}</a></td>
	</tr>
	<!-- BEGIN catrow -->
	<tr>
		<td class="catTitle">{catrow.ADMIN_CATEGORY}</td>
	</tr>
	<!-- BEGIN modulerow -->
	<tr>
		<td><a href="{catrow.modulerow.U_ADMIN_MODULE}" target="main" class="med">{catrow.modulerow.ADMIN_MODULE}</a></td>
	</tr>
	<!-- END modulerow -->
	<!-- END catrow -->
</table>

<!--========================================================================-->
<!-- ENDIF / TPL_ADMIN_NAVIGATE -->

<!-- IF TPL_ADMIN_MAIN -->
<!--========================================================================-->

<br />

<table>
	<tr>
		<td><b>{L_CLEAR_CACHE}:</b></td>
		<td>
			<a href="{U_CLEAR_DATASTORE}">{L_DATASTORE}</a>,&nbsp;
			<a href="{U_CLEAR_TPL_CACHE}">{L_TEMPLATES}</a>&nbsp;
		</td>
	</tr>
		<td><b>{L_UPDATE}:</b></td>
		<td>
			<a href="{U_UPDATE_USER_LEVEL}">{L_USER_LEVELS}</a>&nbsp;
		</td>
	</tr>
	</tr>
		<td><b>{L_SYNCHRONIZE}:</b></td>
		<td>
			<a href="{U_SYNC_TOPICS}">{L_TOPICS}</a>,&nbsp;
			<a href="{U_SYNC_USER_POSTS}">{L_USER_POSTS_COUNT}</a>&nbsp;
		</td>
	</tr>
</table>
<br />

<table class="forumline">
	<tr>
		<th colspan="2">{L_VERSION_INFORMATION}</th>
	</tr>
	<tr>
		<td class="row1" nowrap="nowrap"  width="25%">{L_TP_VERSION}:</td>
		<td class="row2"><b>{$bb_cfg['tp_version']} ({$bb_cfg['tp_release_state']})</b></td>
	</tr>
	<tr>
		<td class="row1" nowrap="nowrap"  width="25%">{L_TP_RELEASE_DATE}:</td>
		<td class="row2"><b>{$bb_cfg['tp_release_date']}</b></td>
	</tr>
</table>
<h3>{L_FORUM_STATS}</h3>

<table class="forumline">
	<tr>
		<th width="25%">{L_STATISTIC}</th>
		<th width="25%">{L_VALUE}</th>
		<th width="25%">{L_STATISTIC}</th>
		<th width="25%">{L_VALUE}</th>
	</tr>
	<tr>
		<td class="row1" nowrap="nowrap">{L_NUMBER_POSTS}:</td>
		<td class="row2"><b>{NUMBER_OF_POSTS}</b></td>
		<td class="row1" nowrap="nowrap">{L_POSTS_PER_DAY}:</td>
		<td class="row2"><b>{POSTS_PER_DAY}</b></td>
	</tr>
	<tr>
		<td class="row1" nowrap="nowrap">{L_NUMBER_TOPICS}:</td>
		<td class="row2"><b>{NUMBER_OF_TOPICS}</b></td>
		<td class="row1" nowrap="nowrap">{L_TOPICS_PER_DAY}:</td>
		<td class="row2"><b>{TOPICS_PER_DAY}</b></td>
	</tr>
	<tr>
		<td class="row1" nowrap="nowrap">{L_NUMBER_USERS}:</td>
		<td class="row2"><b>{NUMBER_OF_USERS}</b></td>
		<td class="row1" nowrap="nowrap">{L_USERS_PER_DAY}:</td>
		<td class="row2"><b>{USERS_PER_DAY}</b></td>
	</tr>
	<tr>
		<td class="row1" nowrap="nowrap">{L_BOARD_STARTED}:</td>
		<td class="row2"><b>{START_DATE}</b></td>
		<td class="row1" nowrap="nowrap">{L_AVATAR_DIR_SIZE}:</td>
		<td class="row2"><b>{AVATAR_DIR_SIZE}</b></td>
	</tr>
	<tr>
		<td class="row1" nowrap="nowrap">{L_DB_SIZE}:</td>
		<td class="row2"><b>{DB_SIZE}</b></td>
		<td class="row1" nowrap="nowrap">{L_GZIP_COMPRESSION}:</td>
		<td class="row2"><b>{GZIP_COMPRESSION}</b></td>
	</tr>
</table>

<a name="online"></a>
<h3>{L_WHOSONLINE}</h3>

<!-- IF SHOW_USERS_ONLINE -->
<table class="forumline">
	<tr>
		<th>&nbsp;{L_USERNAME}&nbsp;</th>
		<th>&nbsp;{L_LOGIN}<br />{L_LAST_UPDATE}&nbsp;</th>
		<th>&nbsp;{L_IP_ADDRESS}&nbsp;</th>
	</tr>
	<!-- BEGIN reg_user_row -->
	<tr>
		<td nowrap="nowrap" class="{reg_user_row.ROW_CLASS}">&nbsp;<span class="gen"><a href="{reg_user_row.U_USER_PROFILE}" class="gen">{reg_user_row.USERNAME}</a></span>&nbsp;</td>
		<td align="center" nowrap="nowrap" class="{reg_user_row.ROW_CLASS}">&nbsp;<span class="gen">{reg_user_row.STARTED}-{reg_user_row.LASTUPDATE}</span>&nbsp;</td>
		<td class="{reg_user_row.ROW_CLASS}">&nbsp;<span class="gen"><a href="{reg_user_row.U_WHOIS_IP}" class="gen" target="_phpbbwhois">{reg_user_row.IP_ADDRESS}</a></span>&nbsp;</td>
	</tr>
	<!-- END reg_user_row -->
	<tr>
		<td colspan="3" class="row3"><img src="{SPACER}" width="1" height="1" alt="."></td>
	</tr>
	<!-- BEGIN guest_user_row -->
	<tr>
		<td nowrap="nowrap" class="{guest_user_row.ROW_CLASS}">&nbsp;<span class="gen">{guest_user_row.USERNAME}</span>&nbsp;</td>
		<td align="center" nowrap="nowrap" class="{guest_user_row.ROW_CLASS}">&nbsp;<span class="gen">{guest_user_row.STARTED}-{guest_user_row.LASTUPDATE}</span>&nbsp;</td>
		<td class="{guest_user_row.ROW_CLASS}">&nbsp;<span class="gen"><a href="{guest_user_row.U_WHOIS_IP}" target="_phpbbwhois">{guest_user_row.IP_ADDRESS}</a></span>&nbsp;</td>
	</tr>
	<!-- END guest_user_row -->
</table>
<!-- ELSE -->
<a href="{USERS_ONLINE_HREF}#online">{L_WHOSONLINE}</a>
<!-- ENDIF -->

<!--========================================================================-->
<!-- ENDIF / TPL_ADMIN_MAIN -->

