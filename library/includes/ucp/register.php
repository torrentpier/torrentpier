<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

array_deep($_POST, 'trim');

set_die_append_msg();

if (IS_ADMIN) {
    $bb_cfg['reg_email_activation'] = false;

    $new_user = (int)request_var('admin', '');
    if ($new_user) {
        $gen_simple_header = true;
    }

    $template->assign_vars(array(
        'NEW_USER' => $new_user,
    ));
}

$can_register = (IS_GUEST || IS_ADMIN);

$submit = !empty($_POST['submit']);
$errors = array();
$adm_edit = false; // редактирование админом чужого профиля

require INC_DIR . '/bbcode.php';
require INC_DIR . '/functions_validate.php';
require INC_DIR . '/functions_selects.php';

$pr_data = array();   // данные редактируемого либо регистрационного профиля
$db_data = array();   // данные для базы: регистрационные либо измененные данные юзера
$tp_data = array();   // данные для tpl

// Данные профиля
switch ($mode) {
    /**
     *  Регистрация
     */
    case 'register':
        if (!$can_register) {
            redirect('index.php');
        }

        if (!IS_ADMIN) {
            // Ограничение по ip
            if ($bb_cfg['unique_ip']) {
                if ($users = DB()->fetch_row("SELECT user_id, username FROM " . BB_USERS . " WHERE user_reg_ip = '" . USER_IP . "' LIMIT 1")) {
                    bb_die(sprintf($lang['ALREADY_REG_IP'], '<a href="' . PROFILE_URL . $users['user_id'] . '"><b>' . $users['username'] . '</b></a>', $bb_cfg['tech_admin_email']));
                }
            }
            // Отключение регистрации
            if ($bb_cfg['new_user_reg_disabled'] || ($bb_cfg['reg_email_activation'] && !$bb_cfg['emailer']['enabled'])) {
                bb_die($lang['NEW_USER_REG_DISABLED']);
            } // Ограничение по времени
            elseif ($bb_cfg['new_user_reg_restricted']) {
                if (in_array(date('G'), array(0, /*1,2,3,4,5,6,7,8,11,12,13,14,15,16,*/
                    17, 18, 19, 20, 21, 22, 23))) {
                    bb_die($lang['REGISTERED_IN_TIME']);
                }
            }
        }

        // field => can_edit
        $profile_fields = array(
            'username' => true,
            'user_password' => true,
            'user_email' => true,
            'user_timezone' => true,
            'user_lang' => true,
            'user_opt' => true,
        );

        $pr_data = array(
            'user_id' => GUEST_UID,
            'username' => '',
            'user_password' => '',
            'user_email' => '',
            'user_timezone' => $bb_cfg['board_timezone'],
            'user_lang' => $bb_cfg['default_lang'],
            'user_opt' => 0,
            'avatar_ext_id' => 0,
        );
        break;

    /**
     *  Редактирование профиля
     */
    case 'editprofile':
        if (IS_GUEST) {
            login_redirect();
        }

        // field => can_edit
        $profile_fields = array(
            'user_active' => IS_ADMIN,
            'username' => (IS_ADMIN || $bb_cfg['allow_namechange']),
            'user_password' => true,
            'user_email' => true, // должен быть после user_password
            'user_lang' => true,
            'user_gender' => true,
            'user_birthday' => true,
            'user_timezone' => true,
            'user_opt' => true,
            'avatar_ext_id' => true,
            'user_icq' => true,
            'user_skype' => true,
            'user_twitter' => true,
            'user_website' => true,
            'user_from' => true,
            'user_sig' => true,
            'user_occ' => true,
            'user_interests' => true,
            'tpl_name' => true,
        );

        // Выбор профиля: для юзера свой, для админа любой
        if (IS_ADMIN && !empty($_REQUEST['u'])) {
            $pr_user_id = (int)$_REQUEST['u'];
            $adm_edit = ($pr_user_id != $userdata['user_id']);
        } else {
            $pr_user_id = $userdata['user_id'];
        }
        $profile_fields_sql = implode(', ', array_keys($profile_fields));
        $sql = "
			SELECT
				user_id,
				user_rank,
				user_level,
				$profile_fields_sql
			FROM " . BB_USERS . "
			WHERE user_id = $pr_user_id
			LIMIT 1
		";
        if (!$pr_data = DB()->fetch_row($sql)) {
            bb_die($lang['PROFILE_NOT_FOUND']);
        }
        break;

    default:
        trigger_error("invalid mode: $mode", E_USER_ERROR);
}

// Captcha
$need_captcha = ($mode == 'register' && !IS_ADMIN && !$bb_cfg['captcha']['disabled']);

if ($submit) {
    if ($need_captcha && !bb_captcha('check')) {
        $errors[] = $lang['CAPTCHA_WRONG'];
    }
}

// Валидация данных
$cur_pass_valid = $adm_edit;

foreach ($profile_fields as $field => $can_edit) {
    switch ($field) {
        /**
         *  Активация (edit, reg)
         */
        case 'user_active':
            $active = isset($_POST['user_active']) ? (int)$_POST['user_active'] : $pr_data['user_active'];
            if ($submit && $adm_edit) {
                $pr_data['user_active'] = $active;
                $db_data['user_active'] = $active;
            }
            break;

        /**
         *  Имя (edit, reg)
         */
        case 'username':
            $username = !empty($_POST['username']) ? clean_username($_POST['username']) : $pr_data['username'];

            if ($submit) {
                $err = validate_username($username);
                if (!$errors and $err && $mode == 'register') {
                    $errors[] = $err;
                }
                if ($can_edit && $username != $pr_data['username'] || $mode == 'register') {
                    $pr_data['username'] = $username;
                    $db_data['username'] = $username;
                }
            }
            $tp_data['CAN_EDIT_USERNAME'] = $can_edit;
            $tp_data['USERNAME'] = $pr_data['username'];
            break;

        /**
         *  Пароль (edit, reg)
         */
        case 'user_password':
            if ($submit) {
                $cur_pass = (string)@$_POST['cur_pass'];
                $new_pass = (string)@$_POST['new_pass'];
                $cfm_pass = (string)@$_POST['cfm_pass'];

                // пароль для гостя и при смене пароля юзером
                if (!empty($new_pass)) {
                    if (mb_strlen($new_pass, 'UTF-8') > 20) {
                        $errors[] = sprintf($lang['CHOOSE_PASS_ERR_MAX'], 20);
                    } elseif (mb_strlen($new_pass, 'UTF-8') < 4) {
                        $errors[] = sprintf($lang['CHOOSE_PASS_ERR_MIN'], 4);
                    } elseif ($new_pass != $cfm_pass) {
                        $errors[] = $lang['CHOOSE_PASS_ERR'];
                    }
                    $db_data['user_password'] = md5(md5($new_pass));
                }

                if ($mode == 'register') {
                    if (empty($new_pass)) {
                        $errors[] = $lang['CHOOSE_PASS'];
                    }
                } else {
                    if (!empty($cur_pass)) {
                        $cur_pass_valid = ($pr_data['user_password'] === md5(md5($cur_pass)));
                    }
                    if (!empty($new_pass) && !$cur_pass_valid) {
                        $errors[] = $lang['CHOOSE_PASS_FAILED'];
                    }
                }
            }
            break;

        /**
         *  E-mail (edit, reg)
         */
        case 'user_email':
            $email = !empty($_POST['user_email']) ? (string)$_POST['user_email'] : $pr_data['user_email'];
            if ($submit) {
                if ($mode == 'register') {
                    if (empty($email)) {
                        $errors[] = $lang['CHOOSE_E_MAIL'];
                    }
                    if (!$errors and $err = validate_email($email)) {
                        $errors[] = $err;
                    }
                    $db_data['user_email'] = $email;
                } elseif ($email != $pr_data['user_email']) {
                    // если смена мейла юзером

                    if (!$cur_pass_valid) {
                        $errors[] = $lang['CONFIRM_PASSWORD_EXPLAIN'];
                    }
                    if (!$errors and $err = validate_email($email)) {
                        $errors[] = $err;
                    }
                    if ($bb_cfg['reg_email_activation']) {
                        $pr_data['user_active'] = 0;
                        $db_data['user_active'] = 0;
                    }
                    $db_data['user_email'] = $email;
                }
            }
            $tp_data['USER_EMAIL'] = htmlCHR($email);
            break;

        /**
         *  Язык (edit, reg)
         */
        case 'user_lang':
            $user_lang = isset($_POST['user_lang']) ? (string)$_POST['user_lang'] : $pr_data['user_lang'];
            if ($submit && ($user_lang != $pr_data['user_lang'] || $mode == 'register')) {
                $pr_data['user_lang'] = $user_lang;
                $db_data['user_lang'] = $user_lang;
            }
            break;

        /**
         *  Часовой пояс (edit, reg)
         */
        case 'user_timezone':
            $user_timezone = isset($_POST['user_timezone']) ? (int)$_POST['user_timezone'] : $pr_data['user_timezone'];
            if ($submit && ($user_timezone != $pr_data['user_timezone'] || $mode == 'register')) {
                if (isset($lang['TZ'][$user_timezone])) {
                    $pr_data['user_timezone'] = $user_timezone;
                    $db_data['user_timezone'] = $user_timezone;
                }
            }
            break;

        /**
         *  Пол (edit, reg)
         */
        case 'user_gender':
            $user_gender = isset($_POST['user_gender']) ? (int)$_POST['user_gender'] : $pr_data['user_gender'];
            if ($submit && $user_gender != $pr_data['user_gender']) {
                $pr_data['user_gender'] = $user_gender;
                $db_data['user_gender'] = $user_gender;
            }
            $tp_data['USER_GENDER'] = build_select('user_gender', array_flip($lang['GENDER_SELECT']), $pr_data['user_gender']);
            break;

        /**
         *  Возраст (edit)
         */
        case 'user_birthday':
            $user_birthday = isset($_POST['user_birthday']) ? (string)$_POST['user_birthday'] : $pr_data['user_birthday'];

            if ($submit && $user_birthday != $pr_data['user_birthday']) {
                $birthday_date = date_parse($user_birthday);

                if (!empty($birthday_date['year'])) {
                    if (strtotime($user_birthday) >= TIMENOW) {
                        $errors[] = $lang['WRONG_BIRTHDAY_FORMAT'];
                    } elseif (bb_date(TIMENOW, 'Y', false) - $birthday_date['year'] > $bb_cfg['birthday_max_age']) {
                        $errors[] = sprintf($lang['BIRTHDAY_TO_HIGH'], $bb_cfg['birthday_max_age']);
                    } elseif (bb_date(TIMENOW, 'Y', false) - $birthday_date['year'] < $bb_cfg['birthday_min_age']) {
                        $errors[] = sprintf($lang['BIRTHDAY_TO_LOW'], $bb_cfg['birthday_min_age']);
                    }
                }

                $pr_data['user_birthday'] = $user_birthday;
                $db_data['user_birthday'] = $user_birthday;
            }
            $tp_data['USER_BIRTHDAY'] = $pr_data['user_birthday'];
            break;

        /**
         *  opt (edit)
         */
        case 'user_opt':
            $user_opt = $pr_data['user_opt'];
            $reg_mode = ($mode == 'register');

            $update_user_opt = array(
                #	'user_opt_name'  => ($reg_mode) ? #reg_value : #in_login_change
                'user_viewemail' => ($reg_mode) ? false : true,
                'user_viewonline' => ($reg_mode) ? false : true,
                'user_notify' => ($reg_mode) ? true : true,
                'user_notify_pm' => ($reg_mode) ? true : true,
                'user_porn_forums' => ($reg_mode) ? false : true,
                'user_dls' => ($reg_mode) ? false : true,
                'user_callseed' => ($reg_mode) ? true : true,
                'user_retracker' => ($reg_mode) ? true : true,
            );

            foreach ($update_user_opt as $opt => $can_change_opt) {
                if ($submit && (isset($_POST[$opt]) && $can_change_opt || $reg_mode)) {
                    $change_opt = ($reg_mode) ? $can_change_opt : !empty($_POST[$opt]);
                    setbit($user_opt, $bf['user_opt'][$opt], $change_opt);
                }
                $tp_data[strtoupper($opt)] = bf($user_opt, 'user_opt', $opt);
            }
            if ($submit && ($user_opt != $pr_data['user_opt'] || $reg_mode)) {
                $pr_data['user_opt'] = $user_opt;
                $db_data['user_opt'] = (int)$user_opt;
            }
            break;

        /**
         *  Avatar (edit)
         */
        case 'avatar_ext_id':
            if ($submit && !bf($pr_data['user_opt'], 'user_opt', 'dis_avatar')) {
                if (isset($_POST['delete_avatar'])) {
                    delete_avatar($pr_data['user_id'], $pr_data['avatar_ext_id']);
                    $pr_data['avatar_ext_id'] = 0;
                    $db_data['avatar_ext_id'] = 0;
                } elseif (!empty($_FILES['avatar']['name']) && $bb_cfg['avatars']['up_allowed']) {
                    $upload = new TorrentPier\Legacy\Common\Upload();

                    if ($upload->init($bb_cfg['avatars'], $_FILES['avatar']) and $upload->store('avatar', $pr_data)) {
                        $pr_data['avatar_ext_id'] = $upload->file_ext_id;
                        $db_data['avatar_ext_id'] = (int)$upload->file_ext_id;
                    } else {
                        $errors = array_merge($errors, $upload->errors);
                    }
                }
            }
            $tp_data['AVATARS_MAX_SIZE'] = humn_size($bb_cfg['avatars']['max_size']);
            break;

        /**
         *  ICQ (edit)
         */
        case 'user_icq':
            $icq = isset($_POST['user_icq']) ? (string)$_POST['user_icq'] : $pr_data['user_icq'];
            if ($submit && $icq != $pr_data['user_icq']) {
                if ($icq == '' || preg_match('#^\d{6,15}$#', $icq)) {
                    $pr_data['user_icq'] = $icq;
                    $db_data['user_icq'] = (string)$icq;
                } else {
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
            $website = isset($_POST['user_website']) ? (string)$_POST['user_website'] : $pr_data['user_website'];
            $website = htmlCHR($website);
            if ($submit && $website != $pr_data['user_website']) {
                if ($website == '' || preg_match('#^https?://[\w\#!$%&~/.\-;:=,?@а-яА-Я\[\]+]+$#iu', $website)) {
                    $pr_data['user_website'] = $website;
                    $db_data['user_website'] = (string)$website;
                } else {
                    $pr_data['user_website'] = '';
                    $errors[] = htmlCHR($lang['WEBSITE_ERROR']);
                }
            }
            $tp_data['USER_WEBSITE'] = $pr_data['user_website'];
            break;

        /**
         *  Откуда (edit)
         */
        case 'user_from':
            $from = isset($_POST['user_from']) ? (string)$_POST['user_from'] : $pr_data['user_from'];
            $from = htmlCHR($from);
            if ($submit && $from != $pr_data['user_from']) {
                $pr_data['user_from'] = $from;
                $db_data['user_from'] = (string)$from;
            }
            $tp_data['USER_FROM'] = $pr_data['user_from'];
            break;

        /**
         *  Подпись (edit)
         */
        case 'user_sig':
            $sig = isset($_POST['user_sig']) ? (string)$_POST['user_sig'] : $pr_data['user_sig'];
            if ($submit && $sig != $pr_data['user_sig']) {
                $sig = prepare_message($sig);

                if (mb_strlen($sig, 'UTF-8') > $bb_cfg['max_sig_chars']) {
                    $errors[] = $lang['SIGNATURE_TOO_LONG'];
                } elseif (preg_match('#<(a|b|i|u|table|tr|td|img) #i', $sig) || preg_match('#(href|src|target|title)=#i', $sig)) {
                    $errors[] = $lang['SIGNATURE_ERROR_HTML'];
                }

                $pr_data['user_sig'] = $sig;
                $db_data['user_sig'] = (string)$sig;
            }
            $tp_data['USER_SIG'] = $pr_data['user_sig'];
            break;

        /**
         *  Род занятий (edit)
         */
        case 'user_occ':
            $occ = isset($_POST['user_occ']) ? (string)$_POST['user_occ'] : $pr_data['user_occ'];
            $occ = htmlCHR($occ);
            if ($submit && $occ != $pr_data['user_occ']) {
                $pr_data['user_occ'] = $occ;
                $db_data['user_occ'] = (string)$occ;
            }
            $tp_data['USER_OCC'] = $pr_data['user_occ'];
            break;

        /**
         *  Интересы
         */
        case 'user_interests':
            $interests = isset($_POST['user_interests']) ? (string)$_POST['user_interests'] : $pr_data['user_interests'];
            $interests = htmlCHR($interests);
            if ($submit && $interests != $pr_data['user_interests']) {
                $pr_data['user_interests'] = $interests;
                $db_data['user_interests'] = (string)$interests;
            }
            $tp_data['USER_INTERESTS'] = $pr_data['user_interests'];
            break;

        /**
         *  Skype
         */
        case 'user_skype':
            $skype = isset($_POST['user_skype']) ? (string)$_POST['user_skype'] : $pr_data['user_skype'];
            if ($submit && $skype != $pr_data['user_skype']) {
                if ($skype != '' && !preg_match("#^[a-zA-Z0-9_.\-@,]{6,32}$#", $skype)) {
                    $errors[] = $lang['SKYPE_ERROR'];
                }
                $pr_data['user_skype'] = $skype;
                $db_data['user_skype'] = (string)$skype;
            }
            $tp_data['USER_SKYPE'] = $pr_data['user_skype'];
            break;

        /**
         *  Twitter
         */
        case 'user_twitter':
            $twitter = isset($_POST['user_twitter']) ? (string)$_POST['user_twitter'] : $pr_data['user_twitter'];
            if ($submit && $twitter != $pr_data['user_twitter']) {
                if ($twitter != '' && !preg_match("#^[a-zA-Z0-9_]{1,15}$#", $twitter)) {
                    $errors[] = $lang['TWITTER_ERROR'];
                }
                $pr_data['user_twitter'] = $twitter;
                $db_data['user_twitter'] = (string)$twitter;
            }
            $tp_data['USER_TWITTER'] = $pr_data['user_twitter'];
            break;

        /**
         *  Выбор шаблона (edit)
         */
        case 'tpl_name':
            $templates = isset($_POST['tpl_name']) ? (string)$_POST['tpl_name'] : $pr_data['tpl_name'];
            $templates = htmlCHR($templates);
            if ($submit && $templates != $pr_data['tpl_name']) {
                $pr_data['tpl_name'] = $bb_cfg['tpl_name'];
                $db_data['tpl_name'] = (string)$bb_cfg['tpl_name'];
                foreach ($bb_cfg['templates'] as $folder => $name) {
                    if ($templates == $folder) {
                        $pr_data['tpl_name'] = $templates;
                        $db_data['tpl_name'] = (string)$templates;
                    }
                }
            }
            $tp_data['TEMPLATES_SELECT'] = templates_select($pr_data['tpl_name'], 'tpl_name');
            break;

        /**
         *  default
         */
        default:
            trigger_error("invalid profile field: $field", E_USER_ERROR);
    }
}

// submit
if ($submit && !$errors) {
    /**
     *  Создание нового профиля
     */
    if ($mode == 'register') {
        if ($bb_cfg['reg_email_activation']) {
            $user_actkey = make_rand_str(12);
            $db_data['user_active'] = 0;
            $db_data['user_actkey'] = $user_actkey;
        } else {
            $db_data['user_active'] = 1;
            $db_data['user_actkey'] = '';
        }
        $db_data['user_regdate'] = TIMENOW;

        if (!IS_ADMIN) {
            $db_data['user_reg_ip'] = USER_IP;
        }

        if (!isset($db_data['tpl_name'])) {
            $db_data['tpl_name'] = (string)$bb_cfg['tpl_name'];
        }

        $sql_args = DB()->build_array('INSERT', $db_data);

        DB()->query("INSERT INTO " . BB_USERS . $sql_args);
        $new_user_id = DB()->sql_nextid();

        if (IS_ADMIN) {
            set_pr_die_append_msg($new_user_id);
            $message = $lang['ACCOUNT_ADDED'];
        } else {
            if ($bb_cfg['reg_email_activation']) {
                $message = $lang['ACCOUNT_INACTIVE'];
                $email_subject = sprintf($lang['EMAILER_SUBJECT']['USER_WELCOME_INACTIVE'], $bb_cfg['sitename']);
                $email_template = 'user_welcome_inactive';
            } else {
                $message = $lang['ACCOUNT_ADDED'];
                $email_subject = sprintf($lang['EMAILER_SUBJECT']['USER_WELCOME'], $bb_cfg['sitename']);
                $email_template = 'user_welcome';
            }

            /** @var TorrentPier\Legacy\Emailer() $emailer */
            $emailer = new TorrentPier\Legacy\Emailer();

            $emailer->set_from([$bb_cfg['board_email'] => $bb_cfg['sitename']]);
            $emailer->set_to([$email => $username]);
            $emailer->set_subject($email_subject);

            $emailer->set_template($email_template, $user_lang);
            $emailer->assign_vars(array(
                'SITENAME' => $bb_cfg['sitename'],
                'WELCOME_MSG' => sprintf($lang['WELCOME_SUBJECT'], $bb_cfg['sitename']),
                'USERNAME' => html_entity_decode($username),
                'PASSWORD' => $new_pass,
                'U_ACTIVATE' => make_url('profile.php?mode=activate&' . POST_USERS_URL . '=' . $new_user_id . '&act_key=' . $db_data['user_actkey'])
            ));

            $emailer->send();
        }

        bb_die($message);
    } /**
     *  Редактирование
     */
    else {
        set_pr_die_append_msg($pr_data['user_id']);

        // если что-то было изменено
        if ($db_data) {
            if (!$pr_data['user_active']) {
                $user_actkey = make_rand_str(12);
                $pr_data['user_actkey'] = $user_actkey;
                $db_data['user_actkey'] = $user_actkey;

                /** @var TorrentPier\Legacy\Emailer() $emailer */
                $emailer = new TorrentPier\Legacy\Emailer();

                $emailer->set_from([$bb_cfg['board_email'] => $bb_cfg['sitename']]);
                $emailer->set_to([$email => $username]);
                $emailer->set_subject($subject);
                $emailer->set_subject($lang['EMAILER_SUBJECT']['USER_ACTIVATE']);

                $emailer->set_template('user_activate', $pr_data['user_lang']);
                $emailer->assign_vars(array(
                    'SITENAME' => $bb_cfg['sitename'],
                    'USERNAME' => html_entity_decode($username),
                    'U_ACTIVATE' => make_url("profile.php?mode=activate&u={$pr_data['user_id']}&act_key=$user_actkey"),
                ));

                $emailer->send();

                $message = $lang['PROFILE_UPDATED_INACTIVE'];
                $user->session_end();
            } else {
                meta_refresh('index.php', 10);
                $message = $lang['PROFILE_UPDATED'];
            }

            $sql_args = DB()->build_array('UPDATE', $db_data);

            DB()->query("UPDATE " . BB_USERS . " SET $sql_args WHERE user_id = {$pr_data['user_id']}");

            if ($pr_data['user_id'] != $userdata['user_id']) {
                if ($pr_data['user_level'] == MOD && !empty($db_data['username'])) {
                    $datastore->update('moderators');
                }
            }

            cache_rm_user_sessions($pr_data['user_id']);

            if ($adm_edit) {
                bb_die($lang['PROFILE_USER'] . ' <b>' . profile_url($pr_data) . '</b> ' . $lang['GOOD_UPDATE']);
            } elseif (!$pr_data['user_active']) {
                bb_die($lang['PROFILE_UPDATED_INACTIVE']);
            } else {
                meta_refresh('index.php', 10);
                bb_die($lang['PROFILE_UPDATED']);
            }
        } else {
            bb_die($lang['NOTHING_HAS_CHANGED']);
        }
    }
}

$template->assign_vars($tp_data);

$template->assign_vars(array(
    'PAGE_TITLE' => ($mode == 'editprofile') ? $lang['EDIT_PROFILE'] . ($adm_edit ? " :: {$pr_data['username']}" : '') : $lang['REGISTER'],
    'SHOW_REG_AGREEMENT' => ($mode == 'register' && !IS_ADMIN),
    'ERROR_MESSAGE' => ($errors) ? implode('<br />', array_unique($errors)) : '',
    'MODE' => $mode,
    'EDIT_PROFILE' => ($mode == 'editprofile'),
    'ADM_EDIT' => $adm_edit,
    'SHOW_PASS' => ($adm_edit || ($mode == 'register' && IS_ADMIN)),
    'CAPTCHA_HTML' => ($need_captcha) ? bb_captcha('get') : '',

    'LANGUAGE_SELECT' => language_select($pr_data['user_lang'], 'user_lang'),
    'TIMEZONE_SELECT' => tz_select($pr_data['user_timezone'], 'user_timezone'),
    'USER_TIMEZONE' => $pr_data['user_timezone'],

    'AVATAR_EXPLAIN' => sprintf($lang['AVATAR_EXPLAIN'], $bb_cfg['avatars']['max_width'], $bb_cfg['avatars']['max_height'], (round($bb_cfg['avatars']['max_size'] / 1024))),
    'AVATAR_DISALLOWED' => bf($pr_data['user_opt'], 'user_opt', 'dis_avatar'),
    'AVATAR_DIS_EXPLAIN' => sprintf($lang['AVATAR_DISABLE'], $bb_cfg['terms_and_conditions_url']),
    'AVATAR_IMG' => get_avatar($pr_data['user_id'], $pr_data['avatar_ext_id'], !bf($pr_data['user_opt'], 'user_opt', 'dis_avatar')),

    'SIGNATURE_EXPLAIN' => sprintf($lang['SIGNATURE_EXPLAIN'], $bb_cfg['max_sig_chars']),
    'SIG_DISALLOWED' => bf($pr_data['user_opt'], 'user_opt', 'dis_sig'),

    'PR_USER_ID' => $pr_data['user_id'],
    'U_RESET_AUTOLOGIN' => LOGIN_URL . "?logout=1&amp;reset_autologin=1&amp;sid={$userdata['session_id']}",
));

print_page('usercp_register.tpl');
