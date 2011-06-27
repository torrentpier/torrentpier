<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

array_deep($_POST, 'trim');

set_die_append_msg();

if (IS_ADMIN)
{
	$bb_cfg['reg_email_activation'] = false;
}

$can_register = (IS_GUEST || IS_ADMIN);

$submit   = !empty($_POST['submit']);
$errors   = array();
$adm_edit = false;                      // редактирование админом чужого профиля

require(INC_DIR .'bbcode.php');
require(INC_DIR .'functions_validate.php');
require(INC_DIR .'functions_selects.php');

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
			// Отключение регистрации
			if ($bb_cfg['new_user_reg_disabled'] || ($bb_cfg['reg_email_activation'] && $bb_cfg['emailer_disabled']))
			{
				bb_die('Регистрация новых пользователей временно отключена');
			}
			// Ограничение по времени
			else if ($bb_cfg['new_user_reg_restricted'])
			{
				require(BB_PATH .'/misc/php/registration_restrict_hours.php');
			}
			// Вывод начальной страницы с условиями регистрации
			if (empty($_POST['reg_agreed']))
			{
				print_page('agreement.tpl');
			}
		}

		// field => can_edit
		$profile_fields = array(
			'username'         => true,
			'user_password'    => true,
			'user_email'       => true,
			'user_timezone' => true,
		);

		$pr_data = array(
			'user_id'          => ANONYMOUS,
			'username'         => '',
			'user_password'    => '',
			'user_email'       => '',
			'user_timezone' => $bb_cfg['board_timezone'],
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
			'username'         => IS_ADMIN,
			'user_password'    => true,
			'user_timezone' => true,
			'user_opt'         => true,
			'user_email'       => true,      // должен быть после user_password
			'user_icq'         => true,
			'user_website'     => true,
			'user_from'        => true,
			'user_sig'         => true,
			'user_occ'         => true,
			'user_interests'   => true,
		);

		// Выбор профиля: для юзера свой, для админа любой
		if (IS_ADMIN && !empty($_REQUEST['u']))
		{
			$pr_user_id = (int) $_REQUEST['u'];
			$adm_edit   = ($pr_user_id != $user->id);
		}
		else
		{
			$pr_user_id = $user->id;
		}
		$profile_fields_sql = join(', ', array_keys($profile_fields));
		$sql = "
			SELECT
				user_id,
				user_level,
				$profile_fields_sql
			FROM ". BB_USERS ."
			WHERE user_id = $pr_user_id
			LIMIT 1
		";
		if (!$pr_data = DB()->fetch_row($sql))
		{
			bb_die('Профиль не найден');
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
	*  Имя (edit, reg)
	*/
	case 'username':
		if ($can_edit)
		{
			$username = !empty($_POST['username']) ? clean_username($_POST['username']) : $pr_data['username'];
			if ($submit)
			{
				if ($mode == 'register')
				{
					if (empty($username))
					{
						$errors[] = 'Вы должны выбрать имя';
					}
					if (!$errors AND $err = validate_username($username))
					{
						$errors[] = $err;
					}

					$db_data['username'] = $username;
				}
				else
				{
					if ($username != $pr_data['username'])
					{
						if (!$errors AND $err = validate_username($username))
						{
							$errors[] = $err;
						}
						$db_data['username'] = $username;
					}
				}
			}
			$tp_data['CAN_EDIT_USERNAME'] = true;
			$tp_data['USERNAME'] = $username;
		}
		else
		{
			$tp_data['USERNAME'] = $pr_data['username'];
		}
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
				if (strlen($new_pass) > 20)
				{
					$errors[] = 'Пароль должен быть не длиннее 20 символов';
				}
				else if ($new_pass != $cfm_pass)
				{
					$errors[] = 'Введённые пароли не совпадают';
				}
				$db_data['user_password'] = md5($new_pass);
			}

			if ($mode == 'register')
			{
				if (empty($new_pass))
				{
					$errors[] = 'Вы должны указать пароль';
				}
			}
			else
			{
				if (!empty($cur_pass))
				{
					$cur_pass_valid = ($pr_data['user_password'] === md5($cur_pass));
				}
				if (!empty($new_pass) && !$cur_pass_valid)
				{
					$errors[] = 'Для изменения пароля вы должны правильно указать текущий пароль';
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
				if (empty($email))
				{
					$errors[] = 'Вы должны указать e-mail';
				}
				if (!$errors AND $err = validate_email($email))
				{
					$errors[] = $err;
				}
				$db_data['user_email'] = $email;
			}
			else if ($email != $pr_data['user_email'])  // если смена мейла юзером
			{
				if (!$cur_pass_valid)
				{
					$errors[] = 'Для изменения e-mail вы должны правильно указать текущий пароль';
				}
				if (!$errors AND $err = validate_email($email))
				{
					$errors[] = $err;
				}
				$db_data['user_email'] = $email;
			}
		}
		$tp_data['USER_EMAIL'] = htmlCHR($email);
		break;

	/**
	*  Часовой пояс (edit, reg)
	*/
	case 'user_timezone':
		$user_timezone = isset($_POST['user_timezone']) ? (int) $_POST['user_timezone'] : $pr_data['user_timezone'];
		if ($submit)
		{
			if (isset($lang['TZ'][$user_timezone]) && $user_timezone != $pr_data['user_timezone'])
			{
				$pr_data['user_timezone'] = $user_timezone;
				$db_data['user_timezone'] = (int) $user_timezone;
			}
		}
		break;

	/**
	*  opt (edit)
	*/
	case 'user_opt':
		$user_opt = $pr_data['user_opt'];

		$update_user_opt = array(
			'notify_pm'        => true,
			'hide_porn_forums' => true,
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
		if ($submit)
		{
			if ($icq != $pr_data['user_icq'])
			{
				if ($icq == '' || preg_match('#^\d{6,15}$#', $icq))
				{
					$pr_data['user_icq'] = $icq;
					$db_data['user_icq'] = (string) $icq;
				}
				else
				{
					$pr_data['user_icq'] = '';
					$errors[] = htmlCHR('Поле "ICQ" может содержать только номер icq');
				}
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
		if ($submit)
		{
			if ($website != $pr_data['user_website'])
			{
				if ($website == '' || preg_match('#^https?://[a-z0-9_:;?&=/.%~\-]+$#i', $website))
				{
					$pr_data['user_website'] = $website;
					$db_data['user_website'] = (string) $website;
				}
				else
				{
					$pr_data['user_website'] = '';
					$errors[] = htmlCHR('Поле "Сайт" может содержать только http:// ссылку');
				}
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
		if ($submit)
		{
			if ($from != $pr_data['user_from'])
			{
				$pr_data['user_from'] = $from;
				$db_data['user_from'] = (string) $from;
			}
		}
		$tp_data['USER_FROM'] = $pr_data['user_from'];
		break;

	/**
	*  Подпись (edit)
	*/
	case 'user_sig':
		$sig = isset($_POST['user_sig']) ? (string) $_POST['user_sig'] : $pr_data['user_sig'];
		if ($submit)
		{
			$sig_esc = prepare_message($sig);

			if (strlen($sig) > $bb_cfg['max_sig_chars'])
			{
				$errors[] = 'Слишком длинная подпись';
			}
			else if (preg_match('#speedtest|vkontakte|danasoft#i', $sig))
			{
				$errors[] = 'Подпись нарушает <a href="'. $bb_cfg['terms_and_conditions_url'] .'"><b>правила</b></a>';
			}
			else if (preg_match('#<(a|b|i|u|table|tr|td|img) #i', $sig) || preg_match('#(href|src|target|title)=#i', $sig))
			{
				$errors[] = 'Подпись может содержать только BBCode';
			}
			else if ($sig_esc != $pr_data['user_sig'])
			{
				$pr_data['user_sig'] = $sig_esc;
				$db_data['user_sig'] = (string) $sig_esc;
			}
		}
		$tp_data['USER_SIG'] = $pr_data['user_sig'];
		break;

	/**
	*  Род занятий (edit)
	*/
	case 'user_occ':
		$occ = isset($_POST['user_occ']) ? (string) $_POST['user_occ'] : $pr_data['user_occ'];
		$occ = htmlCHR($occ);
		if ($submit)
		{
			if ($occ != $pr_data['user_occ'])
			{
				$pr_data['user_occ'] = $occ;
				$db_data['user_occ'] = (string) $occ;
			}
		}
		$tp_data['USER_OCC'] = $pr_data['user_occ'];
		break;

	/**
	*  Интересы
	*/
	case 'user_interests':
		$interests = isset($_POST['user_interests']) ? (string) $_POST['user_interests'] : $pr_data['user_interests'];
		$interests = htmlCHR($interests);
		if ($submit)
		{
			if ($interests != $pr_data['user_interests'])
			{
				$pr_data['user_interests'] = $interests;
				$db_data['user_interests'] = (string) $interests;
			}
		}
		$tp_data['USER_INTERESTS'] = $pr_data['user_interests'];
		break;

	/**
	*  default
	*/
	default:
		trigger_error("invalid profile field: $field", E_USER_ERROR);
	}
}

// submit
if ($submit && !$errors)
{
	/**
	*  Создание нового профиля
	*/
	if ($mode == 'register')
	{
		if ($bb_cfg['reg_email_activation'])
		{
			$db_data['user_active'] = 0;
			$user_actkey = make_rand_str(12);
		}
		else
		{
			$db_data['user_active'] = 1;
			$user_actkey = '';
		}
		$db_data['user_regdate'] = time();

		$sql_args = DB()->build_array('INSERT', $db_data);

		DB()->query("INSERT INTO ". BB_USERS . $sql_args);
		$new_user_id = DB()->sql_nextid();

		if (IS_ADMIN)
		{
			set_pr_die_append_msg($new_user_id);
			$die_msg = "Пользователь <b>$username</b> был успешно создан";
		}
		else if ($bb_cfg['reg_email_activation'])
		{
			$email_sbj = "Добро пожаловать на сайт {$bb_cfg['sitename']}";

			require(INC_DIR .'emailer.php');
			$emailer = new emailer('user_welcome_inactive', $email_sbj, $email);

			$emailer->assign_vars(array(
				'WELCOME_MSG' => $email_sbj,
				'USERNAME'    => html_entity_decode($username),
				'PASSWORD'    => $new_pass,
				'U_ACTIVATE'  => make_url("profile.php?mode=activate&u=$new_user_id&act_key=$user_actkey"),
			));
			$emailer->send();

			$die_msg = file_get_contents(BB_PATH .'/misc/html/account_inactive.html');
		}
		else
		{
			$die_msg = 'Спасибо за регистрацию, учётная запись была создана<br /><br />Вы можете войти в систему, используя ваше имя и пароль';
		}
		bb_die($die_msg);
	}
	/**
	*  Редактирование
	*/
	else
	{
		set_pr_die_append_msg($pr_data['user_id']);

		// если что-то было изменено
		if ($db_data)
		{
			$sql_args = DB()->build_array('UPDATE', $db_data);

			DB()->query("UPDATE ". BB_USERS ." SET $sql_args WHERE user_id = {$pr_data['user_id']} LIMIT 1");

			if ($pr_data['user_id'] != $user->id)
			{
				if ($pr_data['user_level'] == MOD && !empty($db_data['username']))
				{
					$datastore->update('moderators');
				}
			}

			$die_msg = ($adm_edit) ? "Профиль <b>{$pr_data['username']}</b> был успешно изменён" : 'Ваш профиль был успешно изменён';
			bb_die($die_msg);
		}
		else
		{
			bb_die('Ничего не было изменено');
		}
	}
}

$template->assign_vars($tp_data);



$template->assign_vars(array(
	'PAGE_TITLE'         => ($mode == 'editprofile') ? 'Редактирование профиля'. ($adm_edit ? " :: {$pr_data['username']}" : '') : 'Регистрация',
	'SHOW_REG_AGREEMENT' => ($mode == 'register' && !IS_ADMIN),
	'ERROR_MESSAGE'      => ($errors) ? join('<br />', $errors) : '',
	'MODE'               => $mode,
	'EDIT_PROFILE'       => ($mode == 'editprofile'),
	'ADM_EDIT'           => $adm_edit,
	'SHOW_PASS'          => ($adm_edit || ($mode == 'register' && IS_ADMIN)),
	'CAPTCHA_HTML'       => ($need_captcha) ? CAPTCHA()->get_html() : '',

	'TIMEZONE_SELECT'    => tz_select($user_timezone, 'user_timezone'),


	'PR_USER_ID'         => $pr_data['user_id'],
	'U_RESET_AUTOLOGIN'  => "login.php?logout=1&amp;reset_autologin=1&amp;sid={$userdata['session_id']}",

));

//bt
if ($mode == 'editprofile' && $userdata['session_logged_in'])
{
	$template->assign_block_vars('switch_bittorrent', array());

	$sql = 'SELECT auth_key
		FROM '. BB_BT_USERS .'
		WHERE user_id = '. $userdata['user_id'];

	if (!$result = DB()->sql_query($sql))
	{
		message_die(GENERAL_ERROR, 'Could not query users passkey', '', __LINE__, __FILE__, $sql);
	}

	$row = DB()->sql_fetchrow($result);
	$curr_passkey = ($row['auth_key']) ? $row['auth_key'] : '';

	$template->assign_vars(array(
		'L_GEN_PASSKEY'           => $lang['BT_GEN_PASSKEY'],
		'L_GEN_PASSKEY_EXPLAIN'   => $lang['BT_GEN_PASSKEY_EXPLAIN'],
		'L_GEN_PASSKEY_EXPLAIN_2' => $lang['BT_GEN_PASSKEY_EXPLAIN_2'],
		'S_GEN_PASSKEY'           => "<a href=\"torrent.php?mode=gen_passkey&amp;u=" . $userdata['user_id'] . '&amp;sid=' . $userdata['session_id'] . '">' . $lang['BT_GEN_PASSKEY_URL'] . '</a>',
		'CURR_PASSKEY'            => $curr_passkey,
	));
}
//bt end

function set_pr_die_append_msg ($pr_uid)
{
	global $template;

	$template->assign_var('BB_DIE_APPEND_MSG', '
		<a href="'. PROFILE_URL . $pr_uid .'" onclick="return post2url(this.href, {after_edit: 1});">Перейти к просмотру профиля</a>
		<br /><br />
		<a href="profile.php?mode=editprofile'. (IS_ADMIN ? "&amp;u=$pr_uid" : '') .'" onclick="return post2url(this.href, {after_edit: 1});">Вернуться к редактированию</a>
		<br /><br />
		<a href="index.php">Вернуться на главную страницу</a>
	');
}


print_page('usercp_register.tpl');