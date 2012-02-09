<!-- IF CONFIG -->
<h1>{L_GENERAL_CONFIG}</h1>

<p>{L_CONFIG_EXPLAIN}</p>
<br />

<a href="admin_board.php?mode=config" class="bold">{L_GENERAL_CONFIG}</a> &#0183;
<a href="admin_board.php?mode=config_mods">{L_CONFIG_MODS}</a>
<br /><br />

<form action="{S_CONFIG_ACTION}" method="post">
{S_HIDDEN_FIELDS}

<table class="forumline">
<col class="row1">
<col class="row2">
<tr>
	<th colspan="2">{L_GENERAL_SETTINGS}</th>
</tr>
<tr>
	<td><h4>{L_SITE_NAME}</h4></td>
	<td><input class="post" type="text" size="25" maxlength="100" name="sitename" value="{SITENAME}" /></td>
</tr>
<tr>
	<td><h4>{L_SITE_DESC}</h4></td>
	<td><input class="post" type="text" size="40" maxlength="255" name="site_desc" value="{CONFIG_SITE_DESCRIPTION}" /></td>
</tr>
<tr>
	<td><h4>{L_BOARD_DISABLE}</h4><h6>{L_BOARD_DISABLE_EXPLAIN}</h6></td>
	<td>
	    <label><input type="radio" name="board_disable" value="1" <!-- IF DISABLE_BOARD -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="board_disable" value="0" <!-- IF not DISABLE_BOARD -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_ACCT_ACTIVATION}</h4></td>
	<td>
		<div><label><input type="radio" name="require_activation" value="{ACTIVATION_NONE}" {ACTIVATION_NONE_CHECKED} />{L_ACC_NONE}</label></div>
		<div><label><input type="radio" name="require_activation" value="{ACTIVATION_USER}" {ACTIVATION_USER_CHECKED} />{L_ACC_USER}</label></div>
		<div><label><input type="radio" name="require_activation" value="{ACTIVATION_ADMIN}" {ACTIVATION_ADMIN_CHECKED} />{L_ACC_ADMIN}</label></div>
	</td>
</tr>
<tr>
	<td><h4>{L_ALLOW_AUTOLOGIN}</h4><h6>{L_ALLOW_AUTOLOGIN_EXPLAIN}</h6></td>
	<td>
	    <label><input type="radio" name="allow_autologin" value="1" <!-- IF ALLOW_AUTOLOGIN -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="allow_autologin" value="0" <!-- IF not ALLOW_AUTOLOGIN -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_AUTOLOGIN_TIME}</h4><h6>{L_AUTOLOGIN_TIME_EXPLAIN}</h6></td>
	<td>
	    <input class="post" type="text" size="3" maxlength="4" name="max_autologin_time" value="{AUTOLOGIN_TIME}" /> {L_DAYS}
	</td>
</tr>
<tr>
	<td><h4>{L_BOARD_EMAIL_FORM}</h4><h6>{L_BOARD_EMAIL_FORM_EXPLAIN}</h6></td>
	<td>
	    <label><input type="radio" name="board_email_form" value="1" <!-- IF BOARD_EMAIL_FORM -->checked="checked"<!-- ENDIF --> /> {L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="board_email_form" value="0" <!-- IF not BOARD_EMAIL_FORM -->checked="checked"<!-- ENDIF --> /> {L_DISABLED}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_FLOOD_INTERVAL}</h4><h6>{L_FLOOD_INTERVAL_EXPLAIN}</h6></td>
	<td>
	    <input class="post" type="text" size="3" maxlength="4" name="flood_interval" value="{FLOOD_INTERVAL}" /> {L_SEC}
	</td>
</tr>
<!--
<tr>
	<td><h4>{L_MAX_LOGIN_ATTEMPTS}</h4><h6>{L_MAX_LOGIN_ATTEMPTS_EXPLAIN}</h6></td>
	<td>
	    <input class="post" type="text" size="3" maxlength="4" name="max_login_attempts" value="{MAX_LOGIN_ATTEMPTS}" />
	</td>
</tr>
<tr>
	<td><h4>{L_LOGIN_RESET_TIME}</h4><h6>{L_LOGIN_RESET_TIME_EXPLAIN}</h6></td>
	<td>
	    <input class="post" type="text" size="3" maxlength="4" name="login_reset_time" value="{LOGIN_RESET_TIME}" />
	</td>
</tr>
-->
<tr>
	<td><h4>{L_TOPICS_PER_PAGE}</h4></td>
	<td>
	    <input class="post" type="text" name="topics_per_page" size="5" maxlength="4" value="{TOPICS_PER_PAGE}" />
	</td>
</tr>
<tr>
	<td><h4>{L_POSTS_PER_PAGE}</h4></td>
	<td>
	    <input class="post" type="text" name="posts_per_page" size="5" maxlength="4" value="{POSTS_PER_PAGE}" />
	</td>
</tr>
<tr>
	<td><h4>{L_HOT_THRESHOLD}</h4></td>
	<td><input class="post" type="text" name="hot_threshold" size="5" maxlength="4" value="{HOT_TOPIC}" /></td>
</tr>
<tr>
	<td><h4>{L_DEFAULT_LANGUAGE}</h4></td>
	<td>{LANG_SELECT}</td>
</tr>
<tr>
	<td><h4>{L_DATE_FORMAT}</h4><h6>{L_DATE_FORMAT_EXPLAIN}</h6></td>
	<td><input class="post" type="text" name="default_dateformat" value="{DEFAULT_DATEFORMAT}" /></td>
</tr>
<tr>
	<td><h4>{L_SYSTEM_TIMEZONE}</h4></td>
	<td>{TIMEZONE_SELECT}</td>
</tr>
<tr>
	<td><h4>{L_ENABLE_PRUNE}</h4></td>
	<td>
	    <label><input type="radio" name="prune_enable" value="1" <!-- IF PRUNE_ENABLE -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="prune_enable" value="0" <!-- IF not PRUNE_ENABLE -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
</tr>
<tr>
	<th colspan="2">{L_PRIVATE_MESSAGING}</th>
</tr>
<tr>
	<td><h4>{L_DISABLE_PRIVMSG}</h4></td>
	<td>
	    <label><input type="radio" name="privmsg_disable" value="0" <!-- IF PRIVMSG_DISABLE -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="privmsg_disable" value="1" <!-- IF not PRIVMSG_DISABLE -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_INBOX_LIMITS}</h4></td>
	<td><input class="post" type="text" maxlength="4" size="4" name="max_inbox_privmsgs" value="{INBOX_LIMIT}" /></td>
</tr>
<tr>
	<td><h4>{L_SENTBOX_LIMITS}</h4></td>
	<td><input class="post" type="text" maxlength="4" size="4" name="max_sentbox_privmsgs" value="{SENTBOX_LIMIT}" /></td>
</tr>
<tr>
	<td><h4>{L_SAVEBOX_LIMITS}</h4></td>
	<td><input class="post" type="text" maxlength="4" size="4" name="max_savebox_privmsgs" value="{SAVEBOX_LIMIT}" /></td>
</tr>
<tr>
	<th colspan="2">{L_ABILITIES_SETTINGS}</th>
</tr>
<tr>
	<td><h4>{L_MAX_POLL_OPTIONS}</h4></td>
	<td><input class="post" type="text" name="max_poll_options" size="4" maxlength="4" value="{MAX_POLL_OPTIONS}" /></td>
</tr>
<tr>
	<td><h4>{L_ALLOW_BBCODE}</h4></td>
	<td>
	    <label><input type="radio" name="allow_bbcode" value="1" <!-- IF ALLOW_BBCODE -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="allow_bbcode" value="0" <!-- IF not ALLOW_BBCODE -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_ALLOW_SMILIES}</h4></td>
	<td>
	    <label><input type="radio" name="allow_smilies" value="1" <!-- IF ALLOW_SMILIES -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="allow_smilies" value="0" <!-- IF not ALLOW_SMILIES -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_SMILIES_PATH}</h4><h6>{L_SMILIES_PATH_EXPLAIN}</h6></td>
	<td><input class="post" type="text" size="20" maxlength="255" name="smilies_path" value="{SMILIES_PATH}" /></td>
</tr>
<tr>
	<td><h4>{L_ALLOW_SIG}</h4></td>
	<td>
	    <label><input type="radio" name="allow_sig" value="1" <!-- IF ALLOW_SMILIES -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="allow_sig" value="0" <!-- IF not ALLOW_SMILIES -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_MAX_SIG_LENGTH}</h4><h6>{L_MAX_SIG_LENGTH_EXPLAIN}</h6></td>
	<td><input class="post" type="text" size="5" maxlength="4" name="max_sig_chars" value="{SIG_SIZE}" /></td>
</tr>
<tr>
	<td><h4>{L_ALLOW_NAME_CHANGE}</h4></td>
	<td>
	    <label><input type="radio" name="allow_namechange" value="1" <!-- IF ALLOW_NAMECHANGE -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="allow_namechange" value="0" <!-- IF not ALLOW_NAMECHANGE -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
</tr>
<tr>
	<th colspan="2">{L_AVATAR_SETTINGS}</th>
</tr>
<tr>
	<td><h4>{L_ALLOW_LOCAL}</h4></td>
	<td>
	    <label><input type="radio" name="allow_avatar_local" value="1" <!-- IF ALLOW_AVATARS_LOCAL -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="allow_avatar_local" value="0" <!-- IF not ALLOW_AVATARS_LOCAL -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_ALLOW_REMOTE}</h4><h6>{L_ALLOW_REMOTE_EXPLAIN}</h6></td>
	<td>
	    <label><input type="radio" name="allow_avatar_remote" value="1" <!-- IF ALLOW_AVATAR_REMOTE -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="allow_avatar_remote" value="0" <!-- IF not ALLOW_AVATAR_REMOTE -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_ALLOW_UPLOAD}</h4></td>
	<td>
	    <label><input type="radio" name="allow_avatar_upload" value="1" <!-- IF ALLOW_AVATAR_UPLOAD -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="allow_avatar_upload" value="0" <!-- IF not ALLOW_AVATAR_UPLOAD -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_MAX_FILESIZE}</h4><h6>{L_MAX_FILESIZE_EXPLAIN}</h6></td>
	<td><input class="post" type="text" size="10" maxlength="10" name="avatar_filesize" value="{AVATAR_FILESIZE}" /> {L_BYTES}</td>
</tr>
<tr>
	<td><h4>{L_MAX_AVATAR_SIZE}</h4><h6>{L_MAX_AVATAR_SIZE_EXPLAIN}</h6></td>
	<td><input class="post" type="text" size="3" maxlength="4" name="avatar_max_height" value="{AVATAR_MAX_HEIGHT}" /> x <input class="post" type="text" size="3" maxlength="4" name="avatar_max_width" value="{AVATAR_MAX_WIDTH}"></td>
</tr>
<tr>
	<td><h4>{L_AVATAR_STORAGE_PATH}</h4><h6>{L_AVATAR_STORAGE_PATH_EXPLAIN}</h6></td>
	<td><input class="post" type="text" size="20" maxlength="255" name="avatar_path" value="{AVATAR_PATH}" /></td>
</tr>
<tr>
	<td><h4>{L_AVATAR_GALLERY_PATH}</h4><h6>{L_AVATAR_GALLERY_PATH_EXPLAIN}</h6></td>
	<td><input class="post" type="text" size="20" maxlength="255" name="avatar_gallery_path" value="{AVATAR_GALLERY_PATH}" /></td>
</tr>
<tr>
	<td><h4>{L_NOAVATAR}</h4></td>
	<td><input class="post" type="text" size="35" maxlength="255" name="no_avatar" value="{NO_AVATAR}" /></td>
</tr>
<tr>
	<th colspan="2">{L_EMAIL_SETTINGS}</th>
</tr>
<tr>
	<td><h4>{L_ADMIN_EMAIL}</h4></td>
	<td><input class="post" type="text" size="25" maxlength="100" name="board_email" value="{EMAIL_FROM}" /></td>
</tr>
<tr>
	<td><h4>{L_EMAIL_SIG}</h4><h6>{L_EMAIL_SIG_EXPLAIN}</h6></td>
	<td><textarea name="board_email_sig" rows="5" cols="30">{EMAIL_SIG}</textarea></td>
</tr>
<tr>
	<td><h4>{L_USE_SMTP}</h4><h6>{L_USE_SMTP_EXPLAIN}</h6></td>
	<td>
	    <label><input type="radio" name="smtp_delivery" value="1" <!-- IF SMTP_DELIVERY -->checked="checked"<!-- ENDIF --> />{L_YES}</label>&nbsp;&nbsp;
		<label><input type="radio" name="smtp_delivery" value="0" <!-- IF not SMTP_DELIVERY -->checked="checked"<!-- ENDIF --> />{L_NO}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_SMTP_SERVER}</h4></td>
	<td><input class="post" type="text" name="smtp_host" value="{SMTP_HOST}" size="25" maxlength="50" /></td>
</tr>
<tr>
	<td><h4>{L_SMTP_USERNAME}</h4><h6>{L_SMTP_USERNAME_EXPLAIN}</h6></td>
	<td><input class="post" type="text" name="smtp_username" value="{SMTP_USERNAME}" size="25" maxlength="255" /></td>
</tr>
<tr>
	<td><h4>{L_SMTP_PASSWORD}</h4><h6>{L_SMTP_PASSWORD_EXPLAIN}</h6></td>
	<td><input class="post" type="password" name="smtp_password" value="{SMTP_PASSWORD}" size="25" maxlength="255" /></td>
</tr>

<!-- ENDIF / CONFIG -->

<!-- IF CONFIG_MODS -->
<h1>{L_CONFIG_MODS}</h1>

<p>{L_MODS_EXPLAIN}</p>
<br />

<a href="admin_board.php?mode=config">{L_GENERAL_CONFIG}</a> &#0183;
<a href="admin_board.php?mode=config_mods" class="bold">{L_CONFIG_MODS}</a>
<br /><br />

<form action="{S_CONFIG_ACTION}" method="post">
{S_HIDDEN_FIELDS}

<table class="forumline">
<col class="row1">
<col class="row2">
<tr>
	<th colspan="2">{L_CONFIG_MODS}</th>
</tr>
<tr>
	<td><h4>{L_REPORT_MODULE}</h4></td>
	<td>
	    <label><input type="radio" name="reports_enabled" value="1" <!-- IF REPORTS_ENABLED -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="reports_enabled" value="0" <!-- IF not REPORTS_ENABLED -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_MAGNET}</h4></td>
	<td>
	    <label><input type="radio" name="magnet_links_enabled" value="1" <!-- IF MAGNET_LINKS_ENABLED -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="magnet_links_enabled" value="0" <!-- IF not MAGNET_LINKS_ENABLED -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_GENDER}</h4></td>
	<td>
	    <label><input type="radio" name="gender" value="1" <!-- IF GENDER -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="gender" value="0" <!-- IF not GENDER -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_CALLSEED}</h4></td>
	<td>
	    <label><input type="radio" name="callseed" value="1" <!-- IF CALLSEED -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="callseed" value="0" <!-- IF not CALLSEED -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_TRACKER_STATS}</h4></td>
	<td>
	    <label><input type="radio" name="tor_stats" value="1" <!-- IF TOR_STATS -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="tor_stats" value="0" <!-- IF not TOR_STATS -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_WHOIS_INFO}</h4></td>
	<td><input class="post" type="text" size="28" maxlength="100" name="whois_info" value="{WHOIS_INFO}" /></td>
</tr>
<tr>
	<td><h4>{L_SHOW_MOD_HOME_PAGE}</h4></td>
	<td>
	    <label><input type="radio" name="show_mod_index" value="1" <!-- IF SHOW_MOD_INDEX -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="show_mod_index" value="0" <!-- IF not SHOW_MOD_INDEX -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>

<tr class="row3 med">
	<td class="bold tCenter" colspan="2">{L_LATEST_NEWS}</td>
</tr>
<tr>
	<td><h4>{L_LATEST_NEWS}</h4></td>
	<td>
	    <label><input type="radio" name="show_latest_news" value="1" <!-- IF SHOW_LATEST_NEWS -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="show_latest_news" value="0" <!-- IF not SHOW_LATEST_NEWS -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_MAX_NEWS_TITLE}</h4></td>
	<td><input class="post" type="text" size="25" maxlength="100" name="max_news_title" value="{MAX_NEWS_TITLE}" /></td>
</tr>
<tr>
	<td><h4>{L_NEWS_COUNT}</h4></td>
	<td><input class="post" type="text" size="25" maxlength="100" name="latest_news_count" value="{LATEST_NEWS_COUNT}" /></td>
</tr>
<tr>
	<td><h4>{L_NEWS_FORUM_ID}</h4></td>
	<td><input class="post" type="text" size="25" maxlength="100" name="latest_news_forum_id" value="{LATEST_NEWS_FORUM_ID}" /></td>
</tr>

<tr class="row3 med">
	<td class="bold tCenter" colspan="2">{L_NETWORK_NEWS}</td>
</tr>
<tr>
	<td><h4>{L_NETWORK_NEWS}</h4></td>
	<td>
	    <label><input type="radio" name="show_network_news" value="1" <!-- IF SHOW_NETWORK_NEWS -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="show_network_news" value="0" <!-- IF not SHOW_NETWORK_NEWS -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_MAX_NEWS_TITLE}</h4></td>
	<td><input class="post" type="text" size="25" maxlength="100" name="max_net_title" value="{MAX_NET_TITLE}" /></td>
</tr>
<tr>
	<td><h4>{L_NEWS_COUNT}</h4></td>
	<td><input class="post" type="text" size="25" maxlength="100" name="network_news_count" value="{NETWORK_NEWS_COUNT}" /></td>
</tr>
<tr>
	<td><h4>{L_NEWS_FORUM_ID}</h4></td>
	<td><input class="post" type="text" size="25" maxlength="100" name="network_news_forum_id" value="{NETWORK_NEWS_FORUM_ID}" /></td>
</tr>

<tr class="row3 med">
	<td class="bold tCenter" colspan="2">{L_GALLERY}</td>
</tr>
<tr>
	<td><h4>{L_GALLERY}</h4></td>
	<td>
	    <label><input type="radio" name="gallery_enabled" value="1" <!-- IF GALLERY_ENABLED -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="gallery_enabled" value="0" <!-- IF not GALLERY_ENABLED -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>
<tr>
	<td><h4>{L_PIC_GALLERY}</h4></td>
	<td><input class="post" type="text" size="25" maxlength="100" name="pic_dir" value="{PIC_DIR}" /></td>
</tr>
<tr>
	<td><h4>{L_PIC_SIZE}</h4></td>
	<td><input class="post" type="text" size="25" maxlength="100" name="pic_max_size" value="{PIC_MAX_SIZE}" />&nbsp;{L_MB}</td>
</tr>
<tr>
	<td><h4>{L_AUTO_DELETE_POSTED_PICS}</h4></td>
	<td>
	    <label><input type="radio" name="auto_delete_posted_pics" value="1" <!-- IF AUTO_DELETE_POSTED_PICS -->checked="checked"<!-- ENDIF --> />{L_ENABLED}</label>&nbsp;&nbsp;
		<label><input type="radio" name="auto_delete_posted_pics" value="0" <!-- IF not AUTO_DELETE_POSTED_PICS -->checked="checked"<!-- ENDIF --> />{L_DISABLED}</label>
	</td>
</tr>
<!-- ENDIF / CONFIG_MODS -->
<tr>
	<td class="catBottom" colspan="2">
		<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />&nbsp;&nbsp;
		<input type="reset" value="{L_RESET}" class="liteoption" />
	</td>
</tr>
</table>

</form>

<br clear="all" />