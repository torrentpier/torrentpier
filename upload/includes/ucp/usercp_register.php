<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$template->set_filenames(array('body' => 'usercp_register.tpl'));

array_deep($_POST, 'trim');

set_die_append_msg();

if (IS_ADMIN)
{
	require(LANG_ROOT_DIR ."lang_{$userdata['user_lang']}/lang_admin.php");
	$bb_cfg['require_activation'] = false;
}

$can_register = (IS_GUEST || IS_ADMIN);

$submit   = !empty($_POST['submit']);
$errors   = array();
$adm_edit = false;     // редактирование админом чужого профиля

require(INC_DIR .'bbcode.php');
require(INC_DIR .'functions_validate.php');
require(INC_DIR .'functions_selects.php');
require(INC_DIR .'ucp/usercp_avatar.php');

$pr_data = array();   // данные редактируемого либо регистрационного профиля
$db_data = array();   // данные для базы: регистрационные либо измененные данные юзера
$tp_data = array();   // данные для tpl

// Данные профиля
switch ($mode)
{
	/**
	*  Регистрация
	*/
	case 'register':
		if (!$can_register)
		{
			redirect('index.php');
		}
		if (!IS_ADMIN)
		{
			// Ограничение по ипу
			if($bb_cfg['unique_ip'])
			{
				if($users = DB()->fetch_row("SELECT user_id, username FROM ". BB_USERS ." WHERE user_reg_ip = '". USER_IP ."' LIMIT 1"))
				{
					bb_die(sprintf($lang['ALREADY_REG_IP'], '<a href="'. PROFILE_URL . $users['user_id'] .'"><b>'. $users['username'] .'</b></a>', $bb_cfg['tech_admin_email']));
				}
			}
			// Отключение регистрации
			if ($bb_cfg['new_user_reg_disabled'])
			{
				bb_die($lang['NEW_USER_REG_DISABLED']);
			}
			// Ограничение по времени
			else if ($bb_cfg['new_user_reg_restricted'])
			{
				if (in_array(date('G'), array(0,/*1,2,3,4,5,6,7,8,11,12,13,14,15,16,*/17,18,19,20,21,22,23)))
				{
					bb_die($lang['REGISTERED_IN_TIME']);
				}

			}
			// Вывод начальной страницы с условиями регистрации
			/*if (empty($_POST['reg_agreed']))
			{
				print_page('agreement.tpl');
			}*/
		}

		// field => can_edit
		$profile_fields = array(
			'username'         => true,
			'user_password'    => true,
			'user_email'       => true,
			'user_timezone'    => true,
			'user_lang'        => true,
		);

		$pr_data = array(
			'user_id'          => ANONYMOUS,
			'username'         => '',
			'user_password'    => '',
			'user_email'       => '',
			'user_timezone'    => $bb_cfg['board_timezone'],
			'user_lang'        => $bb_cfg['default_lang'],
			'user_opt'         => 0,
		);
		break;

	/**
	*  Редактирование профиля
	*/
	case 'editprofile':
		if (IS_GUEST)
		{
			login_redirect();
		}

		// field => can_edit
		$profile_fields = array(
			'user_active'      => IS_ADMIN,
			'username'         => (IS_ADMIN || $bb_cfg['allow_namechange']),
			'user_password'    => true,
			'user_email'       => true,      // должен быть после user_password
			'user_lang'        => true,
			'user_gender'      => true,
			'user_birthday'    => true,
			'user_timezone'    => true,
			'user_opt'         => true,
			'user_icq'         => true,
			'user_skype'       => true,
			'user_website'     => true,
			'user_from'        => true,
			'user_sig'         => true,
			'user_occ'         => true,
			'user_interests'   => true,
			'user_avatar_type' => true,
		);

		// Выбор профиля: для юзера свой, для админа любой
		if (IS_ADMIN && !empty($_REQUEST['u']))
		{
			$pr_user_id = (int) $_REQUEST['u'];
			$adm_edit   = ($pr_user_id != $userdata['user_id']);
		}
		else
		{
			$pr_user_id = $userdata['user_id'];
		}
		$profile_fields_sql = join(', ', array_keys($profile_fields));
		$sql = "
			SELECT
				user_id,
				user_level,
				user_avatar,
				$profile_fields_sql
			FROM ". BB_USERS ."
			WHERE user_id = $pr_user_id
			LIMIT 1
		";
		if (!$pr_data = DB()->fetch_row($sql))
		{
			bb_die($lang['PROFILE_NOT_FOUND']);
		}

		if (!bf($pr_data['user_opt'], 'user_opt', 'allow_avatar') && ($bb_cfg['allow_avatar_upload'] || $bb_cfg['allow_avatar_local'] || $bb_cfg['allow_avatar_remote']))
		{
			$template->assign_block_vars('switch_avatar_block', array());

			if ($bb_cfg['allow_avatar_upload'] && file_exists(@phpbb_realpath('./' . $bb_cfg['avatar_path'])))
			{
				$template->assign_block_vars('switch_avatar_block.switch_avatar_local_upload', array());
				$template->assign_block_vars('switch_avatar_block.switch_avatar_remote_upload', array());
			}

			if ($bb_cfg['allow_avatar_remote'])
			{
				$template->assign_block_vars('switch_avatar_block.switch_avatar_remote_link', array());
			}

			if ($bb_cfg['allow_avatar_local'] && file_exists(@phpbb_realpath('./' . $bb_cfg['avatar_gallery_path'])))
			{
				$template->assign_block_vars('switch_avatar_block.switch_avatar_local_gallery', array());
			}
		}
		else
		{
			$template->assign_block_vars('not_avatar_block', array());
		}
        break;

	default:
		trigger_error("invalid mode: $mode", E_USER_ERROR);
}

// CAPTCHA
$need_captcha = ($mode == 'register' && !IS_ADMIN);

if ($submit)
{
	if ($need_captcha && !CAPTCHA()->verify_code())
	{
		$errors[] = $lang['CONFIRM_CODE_WRONG'];
	}
}

// Валидация данных
$cur_pass_valid = $adm_edit;

foreach ($profile_fields as $field => $can_edit)
{
	switch ($field)
	{
	/**
	*  Активация (edit, reg)
	*/
	case 'user_active':
		$active = isset($_POST['user_active']) ? (int) $_POST['user_active'] : $pr_data['user_active'];
		if ($submit && $adm_edit)
		{
            $pr_data['user_active'] = $active;
			$db_data['user_active'] = $active;
		}
		break;
		
	/**
	*  Имя (edit, reg)
	*/
	case 'username':
	    $username = !empty($_POST['username']) ? clean_username($_POST['username']) : $pr_data['username'];

		if ($submit)
		{
			if (!$errors AND $err = validate_username($username) && $mode == 'register')
			{
				$errors[] = $err;
			}
			if($can_edit && $username != $pr_data['username'] || $mode == 'register')
			{				$pr_data['username'] = $username;
				$db_data['username'] = $username;			}
		}
		$tp_data['CAN_EDIT_USERNAME'] = $can_edit;
		$tp_data['USERNAME'] = $pr_data['username'];
		break;

	/**
	*  Пароль (edit, reg)
	*/
	case 'user_password':
		if ($submit)
		{
			$cur_pass = (string) @$_POST['cur_pass'];
			$new_pass = (string) @$_POST['new_pass'];
			$cfm_pass = (string) @$_POST['cfm_pass'];

			// пароль для гостя и при смене пароля юзером
			if (!empty($new_pass))
			{
				if (mb_strlen($new_pass, 'UTF-8') > 20)
				{
					$errors[] = sprintf($lang['CHOOSE_PASS_ERR_MAX'], 20);
				}
				elseif (mb_strlen($new_pass, 'UTF-8') < 5)
				{
					$errors[] = sprintf($lang['CHOOSE_PASS_ERR_MIN'], 5);
				}
				elseif ($new_pass != $cfm_pass)
				{
					$errors[] = $lang['CHOOSE_PASS_ERR'];
				}
				$db_data['user_password'] = md5(md5($new_pass));
			}

			if ($mode == 'register')
			{
				if (empty($new_pass))
				{
					$errors[] = $lang['CHOOSE_PASS'];
				}
			}
			else
			{
				if (!empty($cur_pass))
				{
					$cur_pass_valid = ($pr_data['user_password'] === md5(md5($cur_pass)));
				}
				if (!empty($new_pass) && !$cur_pass_valid)
				{
					$errors[] = $lang['CHOOSE_PASS_FAILED'];
				}
			}
		}

		break;

	/**
	*  E-mail (edit, reg)
	*/
	case 'user_email':
		$email = !empty($_POST['user_email']) ? (string) $_POST['user_email'] : $pr_data['user_email'];
		if ($submit)
		{
			if ($mode == 'register')
			{
				if ($err = validate_email($email))
				{
					$errors[] = $err;
				}
				$db_data['user_email'] = $email;
			}
			else if ($email != $pr_data['user_email'])  // если смена мейла юзером
			{
				if (!$cur_pass_valid)
				{
					$errors[] = $lang['CONFIRM_PASSWORD_EXPLAIN'];
				}
				if (!$errors AND $err = validate_email($email))
				{
					$errors[] = $err;
				}
				$pr_data['user_active'] = 0;
				$db_data['user_active'] = 0;
				$db_data['user_email'] = $email;
			}
		}
		$tp_data['USER_EMAIL'] = htmlCHR($email);
		break;

	/**
	*  Язык (edit, reg)
	*/
	case 'user_lang':
		$user_lang = isset($_POST['user_lang']) ? (string) $_POST['user_lang'] : $pr_data['user_lang'];
		if ($submit && ($user_lang != $pr_data['user_lang'] || $mode == 'register'))
		{
            $pr_data['user_lang'] = $user_lang;
			$db_data['user_lang'] = $user_lang;
		}
		break;

	/**
	*  Часовой пояс (edit, reg)
	*/
	case 'user_timezone':
		$user_timezone = isset($_POST['user_timezone']) ? (int) $_POST['user_timezone'] : $pr_data['user_timezone'];
		if ($submit && ($user_timezone != $pr_data['user_timezone'] || $mode == 'register'))
		{
			if (isset($lang['TZ'][$user_timezone]))
			{
				$pr_data['user_timezone'] = $user_timezone;
				$db_data['user_timezone'] = $user_timezone;
			}
		}
		break;

	/**
	*  Пол (edit, reg)
	*/
	case 'user_gender':
		$gender = isset($_POST['user_gender']) ? (int) $_POST['user_gender'] : $pr_data['user_gender'];
		if ($submit && $gender != $pr_data['user_gender'])
		{
            $pr_data['user_gender'] = $gender;
			$db_data['user_gender'] = $gender;
		}
		$tp_data['USER_GENDER'] = build_select('user_gender', array_flip($lang['GENDER_SELECT']), $pr_data['user_gender']);
		break;

    /**
	*  Возраст (edit, reg)
	*/
	case 'user_birthday':
		$b_day  = (isset($_POST['b_day'])) ? (int) $_POST['b_day'] : realdate($pr_data['user_birthday'], 'j');
		$b_md   = (isset($_POST['b_md'])) ? (int) $_POST['b_md'] : realdate($pr_data['user_birthday'], 'n');
		$b_year = (isset($_POST['b_year'])) ? (int) $_POST['b_year'] : realdate($pr_data['user_birthday'], 'Y');

		if ($b_day || $b_md || $b_year)
		{
			if (!checkdate($b_md, $b_day, $b_year))
			{
				$errors[] = $lang['WRONG_BIRTHDAY_FORMAT'];
				$birthday = $next_birthday_greeting = 0;
			}
			else
			{
				$birthday = mkrealdate($b_day, $b_md, $b_year);
				$next_birthday_greeting = (date('md') < $b_md . (($b_day <= 9) ? '0' : '') . $b_day) ? date('Y') : date('Y')+1;
			}
		}
		else
		{
			$birthday = $next_birthday_greeting = 0;
		}

        if ($submit && $birthday != $pr_data['user_birthday'])
		{
			$pr_data['user_birthday'] = $birthday;
			$db_data['user_birthday'] = (int) $birthday;
			$db_data['user_next_birthday_greeting'] = $next_birthday_greeting;
		}
		break;

	/**
	*  opt (edit)
	*/
	case 'user_opt':
		$user_opt = $pr_data['user_opt'];

		$update_user_opt = array(
		    'viewemail'        => true,
		    'allow_viewonline' => true,
		    'notify'           => true,
			'notify_pm'        => true,
			'hide_porn_forums' => true,
			'allow_dls'        => true,
		);
		foreach ($update_user_opt as $opt => $can_change_opt)
		{
			if ($submit && $can_change_opt && isset($_POST[$opt]))
			{
				setbit($user_opt, $bf['user_opt'][$opt], !empty($_POST[$opt]));
			}
			$tp_data[strtoupper($opt)] = bf($user_opt, 'user_opt', $opt);
		}
		if ($submit && $user_opt != $pr_data['user_opt'])
		{
			$pr_data['user_opt'] = $user_opt;
			$db_data['user_opt'] = (int) $user_opt;
		}
		break;

	/**
	*  ICQ (edit)
	*/
	case 'user_icq':
		$icq = isset($_POST['user_icq']) ? (string) $_POST['user_icq'] : $pr_data['user_icq'];
		if ($submit && $icq != $pr_data['user_icq'])
		{
			if ($icq == '' || preg_match('#^\d{6,15}$#', $icq))
			{
				$pr_data['user_icq'] = $icq;
				$db_data['user_icq'] = (string) $icq;
			}
			else
			{
				$pr_data['user_icq'] = '';
				$errors[] = htmlCHR($lang['ICQ_ERROR']);
			}
		}
		$tp_data['USER_ICQ'] = $pr_data['user_icq'];
		break;

	/**
	*  Сайт (edit)
	*/
	case 'user_website':
		$website = isset($_POST['user_website']) ? (string) $_POST['user_website'] : $pr_data['user_website'];
		$website = htmlCHR($website);
		if ($submit && $website != $pr_data['user_website'])
		{
			if ($website == '' || preg_match('#^https?://[\w\#!$%&~/.\-;:=,?@а-яА-Я\[\]+]+$#iu', $website))
			{
				$pr_data['user_website'] = $website;
				$db_data['user_website'] = (string) $website;
			}
			else
			{
				$errors[] = htmlCHR($lang['WEBSITE_ERROR']);
			}
		}
		$tp_data['USER_WEBSITE'] = $pr_data['user_website'];
		break;

	/**
	*  Откуда (edit)
	*/
	case 'user_from':
		$from = isset($_POST['user_from']) ? (string) $_POST['user_from'] : $pr_data['user_from'];
		$from = htmlCHR($from);
		if ($submit && $from != $pr_data['user_from'])
		{
			$pr_data['user_from'] = $from;
			$db_data['user_from'] = (string) $from;
		}
		$tp_data['USER_FROM'] = $pr_data['user_from'];
		break;

	/**
	*  Подпись (edit)
	*/
	case 'user_sig':
		$sig = isset($_POST['user_sig']) ? (string) $_POST['user_sig'] : $pr_data['user_sig'];
		if ($submit && $sig != $pr_data['user_sig'])
		{
			$sig = prepare_message($sig);

			if (mb_strlen($sig, "UTF-8") > $bb_cfg['max_sig_chars'])
			{
				$errors[] = $lang['SIGNATURE_TOO_LONG'];
			}
			else if (preg_match('#<(a|b|i|u|table|tr|td|img) #i', $sig) || preg_match('#(href|src|target|title)=#i', $sig))
			{
				$errors[] = $lang['SIGNATURE_ERROR_HTML'];
			}

			$pr_data['user_sig'] = $sig;
			$db_data['user_sig'] = (string) $sig;
		}
		$tp_data['USER_SIG'] = $pr_data['user_sig'];
		break;

	/**
	*  Род занятий (edit)
	*/
	case 'user_occ':
		$occ = isset($_POST['user_occ']) ? (string) $_POST['user_occ'] : $pr_data['user_occ'];
		$occ = htmlCHR($occ);
		if ($submit && $occ != $pr_data['user_occ'])
		{
			$pr_data['user_occ'] = $occ;
			$db_data['user_occ'] = (string) $occ;
		}
		$tp_data['USER_OCC'] = $pr_data['user_occ'];
		break;

	/**
	*  Интересы
	*/
	case 'user_interests':
		$interests = isset($_POST['user_interests']) ? (string) $_POST['user_interests'] : $pr_data['user_interests'];
		$interests = htmlCHR($interests);
		if ($submit && $interests != $pr_data['user_interests'])
		{
			$pr_data['user_interests'] = $interests;
			$db_data['user_interests'] = (string) $interests;
		}
		$tp_data['USER_INTERESTS'] = $pr_data['user_interests'];
		break;

	/**
	*  Skype
	*/
	case 'user_skype':
		$skype = isset($_POST['user_skype']) ? (string) $_POST['user_skype'] : $pr_data['user_skype'];
		if ($submit && $skype != $pr_data['user_skype'])
		{
			if ($skype != '' && !preg_match("#^[a-zA-Z0-9_.\-@,]{6,32}$#", $skype))
			{
				$errors[] = $lang['SKYPE_ERROR'];
			}
			$pr_data['user_skype'] = $skype;
			$db_data['user_skype'] = (string) $skype;
		}
		$tp_data['USER_SKYPE'] = $pr_data['user_skype'];
		break;

	case 'user_avatar_type':
		if(isset($_POST['avatargallery']) && !$errors)
		{
			$avatar_category = (!empty($_POST['avatarcategory'])) ? htmlspecialchars($_POST['avatarcategory']) : '';

	        $dir = @opendir($bb_cfg['avatar_gallery_path']);

			$avatar_images = array();
			while($file = @readdir($dir))
			{
				if($file != '.' && $file != '..' && !is_file($bb_cfg['avatar_gallery_path'] . '/' . $file) && !is_link($bb_cfg['avatar_gallery_path'] . '/' . $file))
				{
					$sub_dir = @opendir($bb_cfg['avatar_gallery_path'] . '/' . $file);

					$avatar_row_count = 0;
					$avatar_col_count = 0;
					while($sub_file = @readdir($sub_dir))
					{
						if(preg_match('/(\.gif$|\.png$|\.jpg|\.jpeg)$/is', $sub_file))
						{
							$avatar_images[$file][$avatar_row_count][$avatar_col_count] = $sub_file;
							$avatar_name[$file][$avatar_row_count][$avatar_col_count] = ucfirst(str_replace("_", " ", preg_replace('/^(.*)\..*$/', '\1', $sub_file)));

							$avatar_col_count++;
							if($avatar_col_count == 5)
							{
								$avatar_row_count++;
								$avatar_col_count = 0;
							}
						}
					}
				}
			}

			@closedir($dir);

			@ksort($avatar_images);
			@reset($avatar_images);

			if(empty($category))
			{
				list($category,) = each($avatar_images);
			}
			@reset($avatar_images);

			$s_categories = '<select name="avatarcategory">';
			while(list($key) = each($avatar_images))
			{
				$selected = ($key == $category) ? ' selected="selected"' : '';
				if(count($avatar_images[$key]))
				{
					$s_categories .= '<option value="' . $key . '"' . $selected . '>' . ucfirst($key) . '</option>';
				}
			}
			$s_categories .= '</select>';

			$s_colspan = 0;
			for($i = 0; $i < @count($avatar_images[$category]); $i++)
			{
				$template->assign_block_vars("avatar_row", array());

				$s_colspan = max($s_colspan, count($avatar_images[$category][$i]));

				for($j = 0; $j < count($avatar_images[$category][$i]); $j++)
				{
					$template->assign_block_vars('avatar_row.avatar_column', array(
						"AVATAR_IMAGE" => $bb_cfg['avatar_gallery_path'] . '/' . $category . '/' . $avatar_images[$category][$i][$j],
						"AVATAR_NAME" => $avatar_name[$category][$i][$j])
					);

					$template->assign_block_vars('avatar_row.avatar_option_column', array(
						"S_OPTIONS_AVATAR" => $avatar_images[$category][$i][$j])
					);
				}
			}

			$s_hidden_vars = '<input type="hidden" name="avatarcatname" value="' . $category . '" />';

			$template->assign_vars(array(
				'S_CATEGORY_SELECT' => $s_categories,
				'S_COLSPAN' => $s_colspan,
				'S_PROFILE_ACTION' => append_sid("profile.php?mode=$mode"),
				'S_HIDDEN_FIELDS' => $s_hidden_vars)
			);

			$template->set_filenames(array('body' => 'usercp_avatar_gallery.tpl'));
		}

		$user_avatar_local = (isset($_POST['avatarselect']) && !empty($_POST['submitavatar']) && $bb_cfg['allow_avatar_local']) ? htmlspecialchars($_POST['avatarselect']) : ((isset($_POST['avatarlocal'])) ? htmlspecialchars($_POST['avatarlocal']) : '');
		$user_avatar_category = (isset($_POST['avatarcatname']) && $bb_cfg['allow_avatar_local']) ? htmlspecialchars($_POST['avatarcatname']) : '';

		$user_avatar_remoteurl = (!empty($_POST['avatarremoteurl'])) ? trim(htmlspecialchars($_POST['avatarremoteurl'])) : '';
		$user_avatar_upload = (!empty($_POST['avatarurl'])) ? trim($_POST['avatarurl']) : ((!empty($_FILES['avatar']) && $_FILES['avatar']['tmp_name'] != "none") ? $_FILES['avatar']['tmp_name'] : '');
		$user_avatar_name = (!empty($_FILES['avatar']['name'])) ? $_FILES['avatar']['name'] : '';
		$user_avatar_size = (!empty($_FILES['avatar']['size'])) ? $_FILES['avatar']['size'] : 0;
		$user_avatar_filetype = (!empty($_FILES['avatar']['type'])) ? $_FILES['avatar']['type'] : '';

		$user_avatar = (empty($user_avatar_local)) ? $pr_data['user_avatar'] : '';
		$user_avatar_type = (empty($user_avatar_local)) ? $pr_data['user_avatar_type'] : '';

		if ((isset($_POST['avatargallery']) || isset($_POST['submitavatar']) || isset($_POST['cancelavatar'])) && (!isset($submit)))
		{
			if (!isset($_POST['cancelavatar']))
			{
				$user_avatar = $user_avatar_category . '/' . $user_avatar_local;
				$user_avatar_type = USER_AVATAR_GALLERY;
			}
		}

		$ini_val = (phpversion() >= '4.0.0') ? 'ini_get' : 'get_cfg_var';
		$form_enctype = (@$ini_val('file_uploads') == '0' || strtolower(@$ini_val('file_uploads') == 'off') || phpversion() == '4.0.4pl1' || !$bb_cfg['allow_avatar_upload'] || (phpversion() < '4.0.3' && @$ini_val('open_basedir') != '')) ? '' : 'enctype="multipart/form-data"';

		$avatar = '';

		if (isset($_POST['avatardel']) && $mode == 'editprofile')
		{
			$avatar = user_avatar_delete($pr_data['user_avatar_type'], $pr_data['user_avatar']);
		}
		else if ((!empty($user_avatar_upload) || !empty($user_avatar_name)) && $bb_cfg['allow_avatar_upload'])
		{
			if (!empty($user_avatar_upload))
			{
				$avatar_mode = (empty($user_avatar_name)) ? 'remote' : 'local';
				$avatar = user_avatar_upload($mode, $avatar_mode, $pr_data['user_avatar'], $pr_data['user_avatar_type'], $errors, $user_avatar_upload, $user_avatar_name, $user_avatar_size, $user_avatar_filetype);
			}
			else if (!empty($user_avatar_name))
			{
				$errors[] = sprintf($lang['AVATAR_FILESIZE'], round($bb_cfg['avatar_filesize'] / 1024));
			}
		}
		else if ($user_avatar_remoteurl != '' && $bb_cfg['allow_avatar_remote'])
		{
			user_avatar_delete($pr_data['user_avatar_type'], $pr_data['user_avatar']);
			$avatar = user_avatar_url($mode, $errors, $user_avatar_remoteurl);
		}
		else if ($user_avatar_local != '' && $bb_cfg['allow_avatar_local'])
		{
			user_avatar_delete($pr_data['user_avatar_type'], $pr_data['user_avatar']);
			$avatar = user_avatar_gallery($mode, $errors, $user_avatar_local, $user_avatar_category);
		}

        if($avatar)
        {
        	$user_avatar = $avatar['user_avatar'];
        	$user_avatar_type = $avatar['user_avatar_type'];
            $hidden_vars = '';
        	foreach($_POST as $name => $key)
        	{
	        	$hidden_vars .= '<input type="hidden" name="'. $name .'" value="'. $key .'" />';
        	}
        	$tp_data['USER_AVATAR'] = get_avatar($user_avatar, $user_avatar_type) . $hidden_vars;
        }
        else
        {
        	$tp_data['USER_AVATAR'] = get_avatar($pr_data['user_avatar'], $pr_data['user_avatar_type'], !bf($pr_data['user_opt'], 'user_opt', 'allow_avatar'));
        }
	    if ($submit && !bf($pr_data['user_opt'], 'user_opt', 'allow_avatar'))
		{
			if ($user_avatar != $pr_data['user_avatar'] || $user_avatar_type != $pr_data['user_avatar_type'])
			{
				$db_data['user_avatar'] = $avatar['user_avatar'];
				$db_data['user_avatar_type'] = $avatar['user_avatar_type'];
			}
		}
		break;

	/**
	*  default
	*/
	default:
		trigger_error("invalid profile field: $field", E_USER_ERROR);
	}
}

if($bb_cfg['birthday']['enabled'] && $mode != 'register')
{
	$days = array($lang['DELTA_TIME']['INTERVALS']['mday'][0] => 0);
	for($i=1; $i<=31; $i++)
	{
		$days[$i] = $i;
	}
	$s_birthday = build_select('b_day', $days, $b_day);

	$months = array($lang['DELTA_TIME']['INTERVALS']['mon'][0] => 0);
	for($i=1; $i<=12; $i++)
	{
		$month = bb_date(mktime(0, 0, 0, ($i+1), 0, 0), 'F');
		$months[$month] = $i;
	}
	$s_birthday .= build_select('b_md', $months, $b_md);

	$year = bb_date(TIMENOW, 'Y', 'false');
	$years = array($lang['DELTA_TIME']['INTERVALS']['year'][0] => 0);
	for($i=$year-$bb_cfg['birthday']['max_user_age']; $i<=$year-$bb_cfg['birthday']['min_user_age']; $i++)
	{
		$years[$i] = $i;
	}
	$s_birthday .= build_select('b_year', $years, $b_year);
	$tp_data['BIRTHDAY'] = $s_birthday;
}

// submit
if ($submit && !$errors)
{
	/**
	*  Создание нового профиля
	*/
	if ($mode == 'register')
	{
		if ($bb_cfg['require_activation'] == USER_ACTIVATION_SELF || $bb_cfg['require_activation'] == USER_ACTIVATION_ADMIN)
		{
			$user_actkey = make_rand_str(12);
			$db_data['user_active'] = 0;
			$db_data['user_actkey'] = $user_actkey;
		}
		else
		{
			$db_data['user_active'] = 1;
			$db_data['user_actkey'] = '';
		}
		$db_data['user_regdate'] = TIMENOW;

        if(!IS_ADMIN) $db_data['user_reg_ip'] = USER_IP;

		$sql_args = DB()->build_array('INSERT', $db_data);

		DB()->query("INSERT INTO ". BB_USERS . $sql_args);
		$new_user_id = DB()->sql_nextid();

		if (IS_ADMIN)
		{
			$message = $lang['ACCOUNT_ADDED'];
		}
		else
		{
			if ($bb_cfg['require_activation'] == USER_ACTIVATION_SELF)
			{
				$message = $lang['ACCOUNT_INACTIVE'];
				$email_template = 'user_welcome_inactive';
			}
			else if ($bb_cfg['require_activation'] == USER_ACTIVATION_ADMIN)
			{
				$message = $lang['ACCOUNT_INACTIVE_ADMIN'];
				$email_template = 'admin_welcome_inactive';
			}
			else
			{
				$message = $lang['ACCOUNT_ADDED'];
				$email_template = 'user_welcome';
			}

			include(INC_DIR . 'emailer.class.php');
			$emailer = new emailer($bb_cfg['smtp_delivery']);

			$emailer->from($bb_cfg['board_email']);
			$emailer->replyto($bb_cfg['board_email']);

			$emailer->use_template($email_template, $user_lang);
			$emailer->email_address($email);
			$emailer->set_subject(sprintf($lang['WELCOME_SUBJECT'], $bb_cfg['sitename']));

			$emailer->assign_vars(array(
				'SITENAME'    => $bb_cfg['sitename'],
				'WELCOME_MSG' => sprintf($lang['WELCOME_SUBJECT'], $bb_cfg['sitename']),
				'USERNAME'    => html_entity_decode($username),
				'PASSWORD'    => $new_pass,
				'EMAIL_SIG'   => str_replace('<br />', "\n", "-- \n" . $bb_cfg['board_email_sig']),

				'U_ACTIVATE'  => make_url('profile.php?mode=activate&' . POST_USERS_URL . '=' . $new_user_id . '&act_key=' . $db_data['user_actkey'])
			));

			$emailer->send();
			$emailer->reset();

			if ($bb_cfg['require_activation'] == USER_ACTIVATION_ADMIN)
			{
				$emailer->from($bb_cfg['board_email']);
				$emailer->replyto($bb_cfg['board_email']);

				$emailer->email_address($email);
				$emailer->use_template("admin_activate", $user_lang);
				$emailer->set_subject($lang['NEW_ACCOUNT_SUBJECT']);

				$emailer->assign_vars(array(
					'USERNAME'   => html_entity_decode($username),
					'EMAIL_SIG'  => str_replace('<br />', "\n", "-- \n" . $bb_cfg['board_email_sig']),

					'U_ACTIVATE' => make_url('profile.php?mode=activate&' . POST_USERS_URL . '=' . $new_user_id . '&act_key=' . $db_data['user_actkey'])
				));
				$emailer->send();
				$emailer->reset();
			}
		}

		$message = $message . '<br /><br />' . sprintf($lang['CLICK_RETURN_INDEX'],  '<a href="' . append_sid("index.php") . '">', '</a>');

		bb_die($message);
	}
	/**
	*  Редактирование
	*/
	else
	{
		// если что-то было изменено
		if ($db_data)
		{
			if (!$pr_data['user_active'])
			{
				$user_actkey = make_rand_str(12);
                $pr_data['user_actkey'] = $user_actkey;
				$db_data['user_actkey'] = $user_actkey;

				include(INC_DIR . 'emailer.class.php');
				$emailer = new emailer($bb_cfg['smtp_delivery']);

 				$emailer->from($bb_cfg['board_email']);
				$emailer->replyto($bb_cfg['board_email']);

				if($bb_cfg['require_activation'] == USER_ACTIVATION_ADMIN)
				{
					$emailer->use_template("admin_activate", $pr_data['user_lang']);
				}
				else
				{
					$emailer->use_template('user_activate', $pr_data['user_lang']);
				}
				$emailer->email_address($email);
				$emailer->set_subject($lang['REACTIVATE']);

				$emailer->assign_vars(array(
					'SITENAME'   => $bb_cfg['sitename'],
					'USERNAME'   => html_entity_decode($username),
					'EMAIL_SIG'  => (!empty($bb_cfg['board_email_sig'])) ? str_replace('<br />', "\n", "-- \n" . $bb_cfg['board_email_sig']) : '',

					'U_ACTIVATE' => make_url("profile.php?mode=activate&u={$pr_data['user_id']}&act_key=$user_actkey"),
				));
				$emailer->send();
				$emailer->reset();

				$message = $lang['PROFILE_UPDATED_INACTIVE'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_INDEX'],  '<a href="' . append_sid("index.php") . '">', '</a>');
			    $user->session_end();
			}
			else
			{
				meta_refresh(append_sid("index.php"), 10);
				$message = $lang['PROFILE_UPDATED'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_INDEX'],  '<a href="' . append_sid("index.php") . '">', '</a>');
			}

			$sql_args = DB()->build_array('UPDATE', $db_data);

			DB()->query("UPDATE ". BB_USERS ." SET $sql_args WHERE user_id = {$pr_data['user_id']} LIMIT 1");

			if ($pr_data['user_id'] != $userdata['user_id'])
			{
				if ($pr_data['user_level'] == MOD && !empty($db_data['username']))
				{
					$datastore->update('moderators');
				}
			}

            cache_rm_user_sessions ($pr_data['user_id']);

			if($adm_edit)
			{
				bb_die($lang['PROFILE_USER'] . " <b>{$pr_data['username']}</b> " . $lang['GOOD_UPDATE']);
			}
			elseif(!$pr_data['user_active'])
			{
				bb_die($lang['PROFILE_UPDATED_INACTIVE'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_INDEX'],  '<a href="' . append_sid("index.php") . '">', '</a>'));
			}
			else
			{
				meta_refresh(append_sid("index.php"), 10);
				bb_die($lang['PROFILE_UPDATED'] . '<br /><br /><a href="' . PROFILE_URL . $userdata['user_id'] . '">'.$lang['RETURN_PROFILE'].'</a><br /><br />' . sprintf($lang['CLICK_RETURN_INDEX'],  '<a href="' . append_sid("index.php") . '">', '</a>'));
			}
		}
		else
		{
			bb_die($lang['NOTHING_HAS_CHANGED']);
		}
	}
}

$template->assign_vars($tp_data);

$template->assign_vars(array(
	'PAGE_TITLE'         => ($mode == 'editprofile') ? $lang['EDIT_PROFILE'] . ($adm_edit ? " :: {$pr_data['username']}" : '') : $lang['REGISTER'],
	'SHOW_REG_AGREEMENT' => ($mode == 'register' && !IS_ADMIN),
	'ERROR_MESSAGE'      => ($errors) ? join('<br />', array_unique($errors)) : '',
	'MODE'               => $mode,
	'EDIT_PROFILE'       => ($mode == 'editprofile'),
	'ADM_EDIT'           => $adm_edit,
	'SHOW_PASS'          => ($adm_edit || ($mode == 'register' && IS_ADMIN)),
	'CAPTCHA_HTML'       => ($need_captcha) ? CAPTCHA()->get_html() : '',

    'LANGUAGE_SELECT'    => language_select($user_lang, 'user_lang'),
	'TIMEZONE_SELECT'    => tz_select($user_timezone, 'user_timezone'),

    'AVATAR_EXPLAIN'     => sprintf($lang['AVATAR_EXPLAIN'], $bb_cfg['avatar_max_width'], $bb_cfg['avatar_max_height'], (round($bb_cfg['avatar_filesize'] / 1024))),
    'SIGNATURE_EXPLAIN'  => sprintf($lang['SIGNATURE_EXPLAIN'], $bb_cfg['max_sig_chars']),

    'SIG_DISALLOWED'     => bf($pr_data['user_opt'], 'user_opt', 'allow_sig'),

	'PR_USER_ID'         => $pr_data['user_id'],
	'U_RESET_AUTOLOGIN'  => "login.php?logout=1&amp;reset_autologin=1&amp;sid={$userdata['session_id']}",

));

//bt
if ($mode == 'editprofile' && $userdata['session_logged_in'])
{
	$template->assign_block_vars('switch_bittorrent', array());

	$row = DB()->fetch_row("SELECT auth_key FROM ". BB_BT_USERS ." WHERE user_id = $pr_user_id");
	$curr_passkey = ($row['auth_key']) ? $row['auth_key'] : '';

	$template->assign_vars(array(
		'S_GEN_PASSKEY'           => '<a href="#" onclick="ajax.exec({ action: \'gen_passkey\', user_id: '. $pr_user_id .' }); return false;">' . $lang['BT_GEN_PASSKEY_URL'] . '</a>',
		'CURR_PASSKEY'            => $curr_passkey,
	));
}
//bt end

require(PAGE_HEADER);

$template->pparse('body');

require(PAGE_FOOTER);
