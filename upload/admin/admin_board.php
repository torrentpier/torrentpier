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
$sql = "SELECT * FROM " . BB_CONFIG;
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

//
// Escape any quotes in the site description for proper display in the text
// box on the admin page
//

$template->assign_vars(array(
	'S_CONFIG_ACTION' => append_sid('admin_board.php'),

	'SITENAME' => htmlCHR($new['sitename']),
	'CONFIG_SITE_DESCRIPTION' => htmlCHR($new['site_desc']),
	'DISABLE_BOARD' => ($new['board_disable']) ? true : false,
	
	'ACTIVATION_NONE' => USER_ACTIVATION_NONE,
	'ACTIVATION_NONE_CHECKED' => ($new['require_activation'] == USER_ACTIVATION_NONE) ? 'checked="checked"' : '',
	'ACTIVATION_USER' => USER_ACTIVATION_SELF,
	'ACTIVATION_USER_CHECKED' => ($new['require_activation'] == USER_ACTIVATION_SELF) ? 'checked="checked"' : '',
	'ACTIVATION_ADMIN' => USER_ACTIVATION_ADMIN,
	'ACTIVATION_ADMIN_CHECKED' => ($new['require_activation'] == USER_ACTIVATION_ADMIN) ? 'checked="checked"' : '',
	
	'VISUAL_CONFIRM' => ($new['enable_confirm']) ? true : false,
	'ALLOW_AUTOLOGIN' => ($new['allow_autologin']) ? true : false,
	'AUTOLOGIN_TIME' => (int) $new['max_autologin_time'],
	'BOARD_EMAIL_FORM' => ($new['board_email_form']) ? true : false,
	'MAX_POLL_OPTIONS' => $new['max_poll_options'],
	'FLOOD_INTERVAL' => $new['flood_interval'],
	'TOPICS_PER_PAGE' => $new['topics_per_page'],
	'POSTS_PER_PAGE' => $new['posts_per_page'],
	'HOT_TOPIC' => $new['hot_threshold'],
	'LANG_SELECT' => language_select($new['default_lang'], 'default_lang', 'language'),
	'DEFAULT_DATEFORMAT' => $new['default_dateformat'],
	'TIMEZONE_SELECT' => tz_select($new['board_timezone'], 'board_timezone'),
	'PRIVMSG_DISABLE' => ($new['privmsg_disable']) ? false : true,
	'INBOX_LIMIT' => $new['max_inbox_privmsgs'],
	'SENTBOX_LIMIT' => $new['max_sentbox_privmsgs'],
	'SAVEBOX_LIMIT' => $new['max_savebox_privmsgs'],
	'MAX_LOGIN_ATTEMPTS' => $new['max_login_attempts'],
	'LOGIN_RESET_TIME' => $new['login_reset_time'],
	'PRUNE_ENABLE' => ($new['prune_enable']) ? true : false,
	'ALLOW_BBCODE' => ($new['allow_bbcode']) ? true : false,
	'ALLOW_SMILIES' => ($new['allow_smilies']) ? true : false,
	'ALLOW_SIG' => ($new['allow_sig']) ? true : false,
	'SIG_SIZE' => $new['max_sig_chars'],
	'ALLOW_NAMECHANGE' => ($new['allow_namechange']) ? true : false,
	'ALLOW_AVATARS_LOCAL' => ($new['allow_avatar_local']) ? true : false,
	'ALLOW_AVATAR_REMOTE' => ($new['allow_avatar_remote']) ? true : false,
	'ALLOW_AVATAR_UPLOAD' => ($new['allow_avatar_upload']) ? true : false,
	'AVATAR_FILESIZE' => $new['avatar_filesize'],
	'AVATAR_MAX_HEIGHT' => $new['avatar_max_height'],
	'AVATAR_MAX_WIDTH' => $new['avatar_max_width'],
	'AVATAR_PATH' => $new['avatar_path'],
	'AVATAR_GALLERY_PATH' => $new['avatar_gallery_path'],
	'SMILIES_PATH' => $new['smilies_path'],
	'INBOX_PRIVMSGS' => $new['max_inbox_privmsgs'],
	'SENTBOX_PRIVMSGS' => $new['max_sentbox_privmsgs'],
	'SAVEBOX_PRIVMSGS' => $new['max_savebox_privmsgs'],
	'EMAIL_FROM' => $new['board_email'],
	'EMAIL_SIG' => $new['board_email_sig'],
	'SMTP_DELIVERY' => ($new['smtp_delivery']) ? true : false,
	'SMTP_HOST' => $new['smtp_host'],
	'SMTP_USERNAME' => $new['smtp_username'],
	'SMTP_PASSWORD' => $new['smtp_password'],
));

print_page('admin_board.tpl', 'admin');