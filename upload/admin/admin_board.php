<?php

// ACP Header - START
if (!empty($setmodules))
{
	$module['General']['Configuration'] = basename(__FILE__);
	return;
}
require('./pagestart.php');
// ACP Header - END

require(INC_DIR .'functions_selects.php');

//
// Pull all config data
//
$sql = "SELECT *
	FROM " . BB_CONFIG;
if(!$result = DB()->sql_query($sql))
{
	message_die(CRITICAL_ERROR, "Could not query config information in admin_board", "", __LINE__, __FILE__, $sql);
}
else
{
	while( $row = DB()->sql_fetchrow($result) )
	{
		$config_name = $row['config_name'];
		$config_value = $row['config_value'];
		$default_config[$config_name] = $config_value;

		$new[$config_name] = isset($_POST[$config_name]) ? $_POST[$config_name] : $default_config[$config_name];

		// Attempt to prevent a mistake with this value.
		if ($config_name == 'avatar_path')
		{
			$new['avatar_path'] = trim($new['avatar_path']);
			if (strstr($new['avatar_path'], "\0") || !is_dir(BB_ROOT . $new['avatar_path']) || !is_writable(BB_ROOT . $new['avatar_path']))
			{
				$new['avatar_path'] = $default_config['avatar_path'];
			}
		}

		if (isset($_POST['submit']) && $row['config_value'] != $new[$config_name])
		{
			bb_update_config(array($config_name => $new[$config_name]));
		}
	}

	if( isset($_POST['submit']) )
	{
		$message = $lang['CONFIG_UPDATED'] . "<br /><br />" . sprintf($lang['CLICK_RETURN_CONFIG'], "<a href=\"" . append_sid("admin_board.php") . "\">", "</a>") . "<br /><br />" . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], "<a href=\"" . append_sid("index.php?pane=right") . "\">", "</a>");

		message_die(GENERAL_MESSAGE, $message);
	}
}

$lang_select = language_select($new['default_lang'], 'default_lang', "language");
$timezone_select = tz_select($new['board_timezone'], 'board_timezone');

$disable_board_yes = ( $new['board_disable'] ) ? "checked=\"checked\"" : "";
$disable_board_no = ( !$new['board_disable'] ) ? "checked=\"checked\"" : "";

$bbcode_yes = ( $new['allow_bbcode'] ) ? "checked=\"checked\"" : "";
$bbcode_no = ( !$new['allow_bbcode'] ) ? "checked=\"checked\"" : "";

$activation_none = ( $new['require_activation'] == USER_ACTIVATION_NONE ) ? "checked=\"checked\"" : "";
$activation_user = ( $new['require_activation'] == USER_ACTIVATION_SELF ) ? "checked=\"checked\"" : "";
$activation_admin = ( $new['require_activation'] == USER_ACTIVATION_ADMIN ) ? "checked=\"checked\"" : "";

$confirm_yes = ($new['enable_confirm']) ? 'checked="checked"' : '';
$confirm_no = (!$new['enable_confirm']) ? 'checked="checked"' : '';

$allow_autologin_yes = ($new['allow_autologin']) ? 'checked="checked"' : '';
$allow_autologin_no = (!$new['allow_autologin']) ? 'checked="checked"' : '';

$board_email_form_yes = ( $new['board_email_form'] ) ? "checked=\"checked\"" : "";
$board_email_form_no = ( !$new['board_email_form'] ) ? "checked=\"checked\"" : "";

$privmsg_on = ( !$new['privmsg_disable'] ) ? "checked=\"checked\"" : "";
$privmsg_off = ( $new['privmsg_disable'] ) ? "checked=\"checked\"" : "";

$prune_yes = ( $new['prune_enable'] ) ? "checked=\"checked\"" : "";
$prune_no = ( !$new['prune_enable'] ) ? "checked=\"checked\"" : "";

$smile_yes = ( $new['allow_smilies'] ) ? "checked=\"checked\"" : "";
$smile_no = ( !$new['allow_smilies'] ) ? "checked=\"checked\"" : "";

$sig_yes = ( $new['allow_sig'] ) ? "checked=\"checked\"" : "";
$sig_no = ( !$new['allow_sig'] ) ? "checked=\"checked\"" : "";

$namechange_yes = ( $new['allow_namechange'] ) ? "checked=\"checked\"" : "";
$namechange_no = ( !$new['allow_namechange'] ) ? "checked=\"checked\"" : "";

$avatars_local_yes = ( $new['allow_avatar_local'] ) ? "checked=\"checked\"" : "";
$avatars_local_no = ( !$new['allow_avatar_local'] ) ? "checked=\"checked\"" : "";
$avatars_remote_yes = ( $new['allow_avatar_remote'] ) ? "checked=\"checked\"" : "";
$avatars_remote_no = ( !$new['allow_avatar_remote'] ) ? "checked=\"checked\"" : "";
$avatars_upload_yes = ( $new['allow_avatar_upload'] ) ? "checked=\"checked\"" : "";
$avatars_upload_no = ( !$new['allow_avatar_upload'] ) ? "checked=\"checked\"" : "";

$smtp_yes = ( $new['smtp_delivery'] ) ? "checked=\"checked\"" : "";
$smtp_no = ( !$new['smtp_delivery'] ) ? "checked=\"checked\"" : "";

//
// Escape any quotes in the site description for proper display in the text
// box on the admin page
//
$template->assign_vars(array(
	"S_CONFIG_ACTION"=> append_sid("admin_board.php"),

	"SITENAME" => htmlCHR($new['sitename']),
	"CONFIG_SITE_DESCRIPTION" => htmlCHR($new['site_desc']),
	"S_DISABLE_BOARD_YES" => $disable_board_yes,
	"S_DISABLE_BOARD_NO" => $disable_board_no,
	"ACTIVATION_NONE" => USER_ACTIVATION_NONE,
	"ACTIVATION_NONE_CHECKED" => $activation_none,
	"ACTIVATION_USER" => USER_ACTIVATION_SELF,
	"ACTIVATION_USER_CHECKED" => $activation_user,
	"ACTIVATION_ADMIN" => USER_ACTIVATION_ADMIN,
	"ACTIVATION_ADMIN_CHECKED" => $activation_admin,
	"CONFIRM_ENABLE" => $confirm_yes,
	"CONFIRM_DISABLE" => $confirm_no,
	"ALLOW_AUTOLOGIN_YES" => $allow_autologin_yes,
	"ALLOW_AUTOLOGIN_NO" => $allow_autologin_no,
	"AUTOLOGIN_TIME" => (int) $new['max_autologin_time'],
	"BOARD_EMAIL_FORM_ENABLE" => $board_email_form_yes,
	"BOARD_EMAIL_FORM_DISABLE" => $board_email_form_no,
	"MAX_POLL_OPTIONS" => $new['max_poll_options'],
	"FLOOD_INTERVAL" => $new['flood_interval'],
	"TOPICS_PER_PAGE" => $new['topics_per_page'],
	"POSTS_PER_PAGE" => $new['posts_per_page'],
	"HOT_TOPIC" => $new['hot_threshold'],
	"LANG_SELECT" => $lang_select,
	"DEFAULT_DATEFORMAT" => $new['default_dateformat'],
	"TIMEZONE_SELECT" => $timezone_select,
	"S_PRIVMSG_ENABLED" => $privmsg_on,
	"S_PRIVMSG_DISABLED" => $privmsg_off,
	"INBOX_LIMIT" => $new['max_inbox_privmsgs'],
	"SENTBOX_LIMIT" => $new['max_sentbox_privmsgs'],
	"SAVEBOX_LIMIT" => $new['max_savebox_privmsgs'],
	"MAX_LOGIN_ATTEMPTS" => $new['max_login_attempts'],
	"LOGIN_RESET_TIME" => $new['login_reset_time'],
	"PRUNE_YES" => $prune_yes,
	"PRUNE_NO" => $prune_no,
	"BBCODE_YES" => $bbcode_yes,
	"BBCODE_NO" => $bbcode_no,
	"SMILE_YES" => $smile_yes,
	"SMILE_NO" => $smile_no,
	"SIG_YES" => $sig_yes,
	"SIG_NO" => $sig_no,
	"SIG_SIZE" => $new['max_sig_chars'],
	"NAMECHANGE_YES" => $namechange_yes,
	"NAMECHANGE_NO" => $namechange_no,
	"AVATARS_LOCAL_YES" => $avatars_local_yes,
	"AVATARS_LOCAL_NO" => $avatars_local_no,
	"AVATARS_REMOTE_YES" => $avatars_remote_yes,
	"AVATARS_REMOTE_NO" => $avatars_remote_no,
	"AVATARS_UPLOAD_YES" => $avatars_upload_yes,
	"AVATARS_UPLOAD_NO" => $avatars_upload_no,
	"AVATAR_FILESIZE" => $new['avatar_filesize'],
	"AVATAR_MAX_HEIGHT" => $new['avatar_max_height'],
	"AVATAR_MAX_WIDTH" => $new['avatar_max_width'],
	"AVATAR_PATH" => $new['avatar_path'],
	"AVATAR_GALLERY_PATH" => $new['avatar_gallery_path'],
	"SMILIES_PATH" => $new['smilies_path'],
	"INBOX_PRIVMSGS" => $new['max_inbox_privmsgs'],
	"SENTBOX_PRIVMSGS" => $new['max_sentbox_privmsgs'],
	"SAVEBOX_PRIVMSGS" => $new['max_savebox_privmsgs'],
	"EMAIL_FROM" => $new['board_email'],
	"EMAIL_SIG" => $new['board_email_sig'],
	"SMTP_YES" => $smtp_yes,
	"SMTP_NO" => $smtp_no,
	"SMTP_HOST" => $new['smtp_host'],
	"SMTP_USERNAME" => $new['smtp_username'],
	"SMTP_PASSWORD" => $new['smtp_password'])
);

print_page('admin_board.tpl', 'admin');