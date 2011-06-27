
<h1>{L_GENERAL_CONFIG}</h1>

<p>{L_CONFIG_EXPLAIN}</p>
<br />

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
	<td><input type="radio" name="board_disable" value="1" {S_DISABLE_BOARD_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="board_disable" value="0" {S_DISABLE_BOARD_NO} /> {L_NO}</td>
</tr>
<tr>
	<td><h4>{L_ACCT_ACTIVATION}</h4></td>
	<td>
		<div><input type="radio" name="require_activation" value="{ACTIVATION_NONE}" {ACTIVATION_NONE_CHECKED} />{L_ACC_NONE}</div>
		<div><input type="radio" name="require_activation" value="{ACTIVATION_USER}" {ACTIVATION_USER_CHECKED} />{L_ACC_USER}</div>
		<div><input type="radio" name="require_activation" value="{ACTIVATION_ADMIN}" {ACTIVATION_ADMIN_CHECKED} />{L_ACC_ADMIN}</div>
	</td>
</tr>
<tr>
	<td><h4>{L_VISUAL_CONFIRM}</h4><h6>{L_VISUAL_CONFIRM_EXPLAIN}</h6></td>
	<td><input type="radio" name="enable_confirm" value="1" {CONFIRM_ENABLE} />{L_YES}&nbsp; &nbsp;<input type="radio" name="enable_confirm" value="0" {CONFIRM_DISABLE} />{L_NO}</td>
</tr>
<tr>
	<td><h4>{L_ALLOW_AUTOLOGIN}</h4><h6>{L_ALLOW_AUTOLOGIN_EXPLAIN}</h6></td>
	<td><input type="radio" name="allow_autologin" value="1" {ALLOW_AUTOLOGIN_YES} />{L_YES}&nbsp; &nbsp;<input type="radio" name="allow_autologin" value="0" {ALLOW_AUTOLOGIN_NO} />{L_NO}</td>
</tr>
<tr>
	<td><h4>{L_AUTOLOGIN_TIME}</h4><h6>{L_AUTOLOGIN_TIME_EXPLAIN}</h6></td>
	<td><input class="post" type="text" size="3" maxlength="4" name="max_autologin_time" value="{AUTOLOGIN_TIME}" /> days</td>
</tr>
<tr>
	<td><h4>{L_BOARD_EMAIL_FORM}</h4><h6>{L_BOARD_EMAIL_FORM_EXPLAIN}</h6></td>
	<td><input type="radio" name="board_email_form" value="1" {BOARD_EMAIL_FORM_ENABLE} /> {L_ENABLED}&nbsp;&nbsp;<input type="radio" name="board_email_form" value="0" {BOARD_EMAIL_FORM_DISABLE} /> {L_DISABLED}</td>
</tr>
<tr>
	<td><h4>{L_FLOOD_INTERVAL}</h4><h6>{L_FLOOD_INTERVAL_EXPLAIN}</h6></td>
	<td><input class="post" type="text" size="3" maxlength="4" name="flood_interval" value="{FLOOD_INTERVAL}" /> sec</td>
</tr>
<!--
<tr>
	<td><h4>{L_MAX_LOGIN_ATTEMPTS}</h4><h6>{L_MAX_LOGIN_ATTEMPTS_EXPLAIN}</h6></td>
	<td><input class="post" type="text" size="3" maxlength="4" name="max_login_attempts" value="{MAX_LOGIN_ATTEMPTS}" /></td>
</tr>
<tr>
	<td><h4>{L_LOGIN_RESET_TIME}</h4><h6>{L_LOGIN_RESET_TIME_EXPLAIN}</h6></td>
	<td><input class="post" type="text" size="3" maxlength="4" name="login_reset_time" value="{LOGIN_RESET_TIME}" /></td>
</tr>
-->
<tr>
	<td><h4>{L_TOPICS_PER_PAGE}</h4></td>
	<td><input class="post" type="text" name="topics_per_page" size="5" maxlength="4" value="{TOPICS_PER_PAGE}" /></td>
</tr>
<tr>
	<td><h4>{L_POSTS_PER_PAGE}</h4></td>
	<td><input class="post" type="text" name="posts_per_page" size="5" maxlength="4" value="{POSTS_PER_PAGE}" /></td>
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
	<td><input type="radio" name="prune_enable" value="1" {PRUNE_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="prune_enable" value="0" {PRUNE_NO} /> {L_NO}</td>
</tr>
<tr>
	<th colspan="2">{L_PRIVATE_MESSAGING}</th>
</tr>
<tr>
	<td><h4>{L_DISABLE_PRIVMSG}</h4></td>
	<td><input type="radio" name="privmsg_disable" value="0" {S_PRIVMSG_ENABLED} />{L_ENABLED}&nbsp; &nbsp;<input type="radio" name="privmsg_disable" value="1" {S_PRIVMSG_DISABLED} />{L_DISABLED}</td>
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
	<td><input type="radio" name="allow_bbcode" value="1" {BBCODE_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_bbcode" value="0" {BBCODE_NO} /> {L_NO}</td>
</tr>
<tr>
	<td><h4>{L_ALLOW_SMILIES}</h4></td>
	<td><input type="radio" name="allow_smilies" value="1" {SMILE_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_smilies" value="0" {SMILE_NO} /> {L_NO}</td>
</tr>
<tr>
	<td><h4>{L_SMILIES_PATH}</h4><h6>{L_SMILIES_PATH_EXPLAIN}</h6></td>
	<td><input class="post" type="text" size="20" maxlength="255" name="smilies_path" value="{SMILIES_PATH}" /></td>
</tr>
<tr>
	<td><h4>{L_ALLOW_SIG}</h4></td>
	<td><input type="radio" name="allow_sig" value="1" {SIG_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_sig" value="0" {SIG_NO} /> {L_NO}</td>
</tr>
<tr>
	<td><h4>{L_MAX_SIG_LENGTH}</h4><h6>{L_MAX_SIG_LENGTH_EXPLAIN}</h6></td>
	<td><input class="post" type="text" size="5" maxlength="4" name="max_sig_chars" value="{SIG_SIZE}" /></td>
</tr>
<tr>
	<td><h4>{L_ALLOW_NAME_CHANGE}</h4></td>
	<td><input type="radio" name="allow_namechange" value="1" {NAMECHANGE_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_namechange" value="0" {NAMECHANGE_NO} /> {L_NO}</td>
</tr>
<tr>
	<th colspan="2">{L_AVATAR_SETTINGS}</th>
</tr>
<tr>
	<td><h4>{L_ALLOW_LOCAL}</h4></td>
	<td><input type="radio" name="allow_avatar_local" value="1" {AVATARS_LOCAL_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_avatar_local" value="0" {AVATARS_LOCAL_NO} /> {L_NO}</td>
</tr>
<tr>
	<td><h4>{L_ALLOW_REMOTE}</h4><h6>{L_ALLOW_REMOTE_EXPLAIN}</h6></td>
	<td><input type="radio" name="allow_avatar_remote" value="1" {AVATARS_REMOTE_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_avatar_remote" value="0" {AVATARS_REMOTE_NO} /> {L_NO}</td>
</tr>
<tr>
	<td><h4>{L_ALLOW_UPLOAD}</h4></td>
	<td><input type="radio" name="allow_avatar_upload" value="1" {AVATARS_UPLOAD_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="allow_avatar_upload" value="0" {AVATARS_UPLOAD_NO} /> {L_NO}</td>
</tr>
<tr>
	<td><h4>{L_MAX_FILESIZE}</h4><h6>{L_MAX_FILESIZE_EXPLAIN}</h6></td>
	<td><input class="post" type="text" size="4" maxlength="10" name="avatar_filesize" value="{AVATAR_FILESIZE}" /> Bytes</td>
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
	<td><input type="radio" name="smtp_delivery" value="1" {SMTP_YES} /> {L_YES}&nbsp;&nbsp;<input type="radio" name="smtp_delivery" value="0" {SMTP_NO} /> {L_NO}</td>
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
<tr>
	<td class="catBottom" colspan="2">
		<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />&nbsp;&nbsp;<input type="reset" value="{L_RESET}" class="liteoption" />
	</td>
</tr>
</table>

</form>

<br clear="all" />