<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */
if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

set_die_append_msg();

if (IN_DEMO_MODE) {
    config()->set('reg_email_activation', false);
}

if (IS_ADMIN) {
    config()->set('reg_email_activation', false);

    $new_user = request()->getInt('admin');
    if ($new_user) {
        simple_header(true);
    }

    template()->assign_vars(['NEW_USER' => $new_user]);
}

$can_register = (IS_GUEST || IS_ADMIN);

$submit = request()->post->has('submit');
$errors = [];
$adm_edit = false; // editing someone else's profile by an admin

require INC_DIR . '/bbcode.php';

$pr_data = []; // data of the edited or registration profile
$db_data = []; // data for the database: registration or changed user data
$tp_data = []; // data for tpl

// Profile data
switch ($mode) {
    /**
     *  Registration
     */
    case 'register':
        if (!$can_register) {
            redirect('index.php');
        }

        if (!IS_ADMIN) {
            // IP limit
            if (config()->get('unique_ip')) {
                if ($users = DB()->fetch_row('SELECT user_id, username FROM ' . BB_USERS . " WHERE user_reg_ip = '" . USER_IP . "' LIMIT 1")) {
                    bb_die(sprintf(__('ALREADY_REG_IP'), '<a href="' . url()->member($users['user_id'], $users['username']) . '"><b>' . $users['username'] . '</b></a>', config()->get('tech_admin_email')));
                }
            }
            // Disabling registration
            if (config()->get('new_user_reg_disabled') || (config()->get('reg_email_activation') && !config()->get('emailer.enabled'))) {
                bb_die(__('NEW_USER_REG_DISABLED'));
            } // Time limit
            elseif (config()->get('new_user_reg_restricted')) {
                $tz = config()->get('board_timezone');
                $current_hour = (int)gmdate('G', TIMENOW + (3600 * $tz));
                if (in_array($current_hour, config()->get('new_user_reg_interval'), true)) {
                    $time_info = format_registration_intervals(config()->get('new_user_reg_interval'));
                    $message = sprintf(
                        __('REGISTERED_IN_TIME'),
                        $time_info['intervals'],
                        $time_info['current_time'],
                    );
                    bb_die($message);
                }
            }
        }

        // field => can_edit
        $profile_fields = [
            'username' => true,
            'user_password' => true,
            'user_email' => true,
            'invite_code' => true,
            'user_timezone' => true,
            'user_lang' => true,
            'user_opt' => true,
        ];

        $pr_data = [
            'user_id' => GUEST_UID,
            'username' => '',
            'user_password' => '',
            'user_email' => '',
            'invite_code' => '',
            'user_timezone' => config()->get('board_timezone'),
            'user_lang' => config()->get('default_lang'),
            'user_opt' => 0,
            'avatar_ext_id' => 0,
        ];
        break;

        /**
         *  Profile editing
         */
    case 'editprofile':
        if (IS_GUEST) {
            login_redirect();
        }

        // field => can_edit
        $profile_fields = [
            'user_active' => IS_ADMIN,
            'username' => (IS_ADMIN || config()->get('allow_namechange')) && !IN_DEMO_MODE,
            'user_password' => !IN_DEMO_MODE,
            'user_email' => !IN_DEMO_MODE, // should be after user_password
            'user_lang' => config()->get('allow_change.language'),
            'user_gender' => config()->get('gender'),
            'user_birthday' => config()->get('birthday_enabled'),
            'user_timezone' => config()->get('allow_change.timezone'),
            'user_opt' => true,
            'avatar_ext_id' => true,
            'user_twitter' => true,
            'user_website' => true,
            'user_from' => true,
            'user_sig' => true,
            'user_occ' => true,
            'user_interests' => true,
            'tpl_name' => true,
        ];

        // Select a profile: your own for the user, any for the admin
        if (IS_ADMIN && request()->has(POST_USERS_URL)) {
            $pr_user_id = request()->getInt(POST_USERS_URL);
            $adm_edit = ($pr_user_id != userdata('user_id'));
        } else {
            $pr_user_id = userdata('user_id');
        }
        $profile_fields_sql = implode(', ', array_keys($profile_fields));
        $sql = "
			SELECT
				user_id,
				user_rank,
				user_level,
				{$profile_fields_sql}
			FROM " . BB_USERS . "
			WHERE user_id = {$pr_user_id}
			LIMIT 1
		";
        if (!$pr_data = DB()->fetch_row($sql)) {
            bb_die(__('PROFILE_NOT_FOUND'));
        }
        $pr_data['user_birthday'] = $pr_data['user_birthday']->format('Y-m-d');
        break;

    default:
        throw new RuntimeException("invalid mode: {$mode}");
}

// Captcha
$need_captcha = ($mode == 'register' && !IS_ADMIN && !config()->get('captcha.disabled'));

if ($submit) {
    if ($need_captcha && !bb_captcha('check')) {
        $errors[] = __('CAPTCHA_WRONG');
    }
}

// Data validation
$cur_pass_valid = $adm_edit;
$can_edit_tpl = [];

foreach ($profile_fields as $field => $can_edit) {
    $can_edit = (bool)$can_edit;
    $can_edit_tpl['CAN_EDIT_' . strtoupper($field)] = $can_edit;

    switch ($field) {
        /**
         *  Активация (edit)
         */
        case 'user_active':
            $active = request()->post->has('user_active') ? request()->post->getInt('user_active') : $pr_data['user_active'];
            if ($submit && $adm_edit) {
                $pr_data['user_active'] = $active;
                $db_data['user_active'] = $active;
            }
            break;

            /**
             *  Имя (edit, reg)
             */
        case 'username':
            $username = request()->post->has('username') ? clean_username(request()->post->get('username')) : $pr_data['username'];

            if ($submit && $can_edit) {
                $err = TorrentPier\Validate::username($username);
                if (!$errors and $err && $mode == 'register') {
                    $errors[] = $err;
                }
                if ($username != $pr_data['username'] || $mode == 'register') {
                    $pr_data['username'] = $username;
                    $db_data['username'] = $username;
                }
            }
            $tp_data['USERNAME'] = $pr_data['username'];
            break;

            /**
             *  Invite code (reg)
             */
        case 'invite_code':
            if (config()->get('invites_system.enabled')) {
                $invite_code = request()->post->get('invite_code', '');
                if ($submit) {
                    $inviteCodes = config()->get('invites_system.codes');
                    if (isset($inviteCodes[$invite_code])) {
                        if ($inviteCodes[$invite_code] !== 'permanent') {
                            if (strtotime($inviteCodes[$invite_code]) < TIMENOW) {
                                $errors[] = __('INVITE_EXPIRED');
                            }
                        }
                    } else {
                        $errors[] = __('INCORRECT_INVITE');
                    }
                }
            }
            break;

            /**
             *  Пароль (edit, reg)
             */
        case 'user_password':
            if ($submit && $can_edit) {
                $cur_pass = (string)request()->post->get('cur_pass', '');
                $new_pass = (string)request()->post->get('new_pass', '');
                $cfm_pass = (string)request()->post->get('cfm_pass', '');

                // password for the guest (while registering) and when the user changes the password
                if (!empty($new_pass)) {
                    if ($err = TorrentPier\Validate::password($new_pass, $cfm_pass)) {
                        $errors[] = $err;
                    }

                    $db_data['user_password'] = user()->password_hash($new_pass);
                }

                if ($mode == 'register') {
                    if (empty($new_pass)) {
                        $errors[] = __('CHOOSE_PASS');
                    }
                } else {
                    if (!empty($cur_pass)) {
                        $cur_pass_valid = user()->checkPassword($cur_pass, $pr_data);
                    }
                    if (!empty($new_pass) && !$cur_pass_valid) {
                        $errors[] = __('CHOOSE_PASS_FAILED');
                    }
                }
            }
            break;

            /**
             *  E-mail (edit, reg)
             */
        case 'user_email':
            $email = request()->post->has('user_email') ? (string)request()->post->get('user_email') : $pr_data['user_email'];
            if ($submit && $can_edit) {
                if ($mode == 'register') {
                    if (!$errors and $err = TorrentPier\Validate::email($email)) {
                        $errors[] = $err;
                    }
                    $db_data['user_email'] = $email;
                } elseif ($email != $pr_data['user_email']) {
                    if (config()->get('email_change_disabled') && !$adm_edit && !IS_ADMIN) {
                        $errors[] = __('EMAIL_CHANGING_DISABLED');
                    }
                    if (!$cur_pass_valid) {
                        $errors[] = __('CONFIRM_PASSWORD_EXPLAIN');
                    }
                    if (!$errors and $err = TorrentPier\Validate::email($email)) {
                        $errors[] = $err;
                    }
                    if (config()->get('reg_email_activation')) {
                        $pr_data['user_active'] = 0;
                        $db_data['user_active'] = 0;
                    }
                    $db_data['user_email'] = $email;
                    $pr_data['user_email'] = $email;
                }
            }
            $tp_data['USER_EMAIL'] = htmlCHR($email);
            break;

            /**
             *  Язык (edit, reg)
             */
        case 'user_lang':
            $user_lang = request()->post->has('user_lang') ? (string)request()->post->get('user_lang') : $pr_data['user_lang'];
            if ($submit && ($user_lang != $pr_data['user_lang'] || $mode == 'register')) {
                $pr_data['user_lang'] = $user_lang;
                $db_data['user_lang'] = $user_lang;
            }
            break;

            /**
             *  Часовой пояс (edit, reg)
             */
        case 'user_timezone':
            $user_timezone = request()->post->has('user_timezone') ? (float)request()->post->get('user_timezone') : (float)$pr_data['user_timezone'];
            if ($submit && ($user_timezone != $pr_data['user_timezone'] || $mode == 'register')) {
                if (isset(config()->get('timezones')[str_replace(',', '.', $user_timezone)])) {
                    $pr_data['user_timezone'] = $user_timezone;
                    $db_data['user_timezone'] = $user_timezone;
                }
            }
            break;

            /**
             *  Пол (edit)
             */
        case 'user_gender':
            $user_gender = request()->post->has('user_gender') ? request()->post->getInt('user_gender') : $pr_data['user_gender'];
            if ($submit && $user_gender != $pr_data['user_gender']) {
                $pr_data['user_gender'] = $user_gender;
                $db_data['user_gender'] = $user_gender;
            }
            $tp_data['USER_GENDER'] = build_select('user_gender', array_flip(__('GENDER_SELECT')), $pr_data['user_gender']);
            break;

            /**
             *  Возраст (edit)
             */
        case 'user_birthday':
            $user_birthday = request()->post->has('user_birthday') ? (string)request()->post->get('user_birthday') : $pr_data['user_birthday'];

            if ($submit && $user_birthday !== $pr_data['user_birthday']) {
                $birthday_date = date_parse($user_birthday);

                if (!empty($birthday_date['year'])) {
                    if (strtotime($user_birthday) >= TIMENOW) {
                        $errors[] = __('WRONG_BIRTHDAY_FORMAT');
                    } elseif (bb_date(TIMENOW, 'Y', false) - $birthday_date['year'] > config()->get('birthday_max_age')) {
                        $errors[] = sprintf(__('BIRTHDAY_TO_HIGH'), config()->get('birthday_max_age'));
                    } elseif (bb_date(TIMENOW, 'Y', false) - $birthday_date['year'] < config()->get('birthday_min_age')) {
                        $errors[] = sprintf(__('BIRTHDAY_TO_LOW'), config()->get('birthday_min_age'));
                    }
                }

                $pr_data['user_birthday'] = $db_data['user_birthday'] = !empty($user_birthday) ? $user_birthday : null;
            }
            $tp_data['USER_BIRTHDAY'] = $pr_data['user_birthday'];
            break;

            /**
             *  opt (edit, reg)
             */
        case 'user_opt':
            $user_opt = $pr_data['user_opt'];
            $reg_mode = ($mode == 'register');

            $update_user_opt = [
                //	'user_opt_name'  => ($reg_mode) ? #reg_value : #in_login_change
                'user_viewemail' => $reg_mode ? false : (IS_ADMIN || config()->get('show_email_visibility_settings')),
                'user_viewonline' => $reg_mode ? false : true,
                'user_notify' => $reg_mode ? true : true,
                'user_notify_pm' => $reg_mode ? true : config()->get('pm_notify_enabled'),
                'user_porn_forums' => $reg_mode ? false : true,
                'user_dls' => $reg_mode ? false : true,
                'user_callseed' => $reg_mode ? true : true,
                'user_retracker' => $reg_mode ? true : true,
                'user_hide_torrent_client' => $reg_mode ? true : true,
                'user_hide_peer_country' => $reg_mode ? true : config()->get('ip2country_settings.enabled'),
                'user_hide_peer_username' => $reg_mode ? false : true,
            ];

            foreach ($update_user_opt as $opt => $can_change_opt) {
                if ($submit && (request()->post->has($opt) && $can_change_opt || $reg_mode)) {
                    $change_opt = ($reg_mode) ? $can_change_opt : request()->post->has($opt);
                    setbit($user_opt, bitfields('user_opt')[$opt], $change_opt);
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
                $avatarFile = request()->getFileAsArray('avatar');

                // Integration with MonsterID
                if (empty($avatarFile['name']) && !request()->post->has('delete_avatar') && request()->post->has('use_monster_avatar')) {
                    $monsterAvatar = new Arokettu\MonsterID\Monster($pr_data['user_email'], config()->get('avatars.max_height'));
                    $tempAvatar = tmpfile();
                    $tempAvatarPath = stream_get_meta_data($tempAvatar)['uri'];
                    $monsterAvatar->writeToStream($tempAvatar);

                    // Create avatar file array for MonsterID
                    if (is_file($tempAvatarPath)) {
                        $avatarFile = [
                            'name' => "MonsterID_{$pr_data['user_id']}.png",
                            'type' => mime_content_type($tempAvatarPath),
                            'tmp_name' => $tempAvatarPath,
                            'error' => UPLOAD_ERR_OK,
                            'size' => filesize($tempAvatarPath),
                        ];
                    }
                }

                if (request()->post->has('delete_avatar')) {
                    delete_avatar($pr_data['user_id'], $pr_data['avatar_ext_id']);
                    $pr_data['avatar_ext_id'] = 0;
                    $db_data['avatar_ext_id'] = 0;
                } elseif (!empty($avatarFile['name']) && config()->get('avatars.up_allowed')) {
                    $upload = new TorrentPier\Legacy\Common\Upload();

                    if ($upload->init(config()->getSection('avatars'), $avatarFile, !request()->post->has('use_monster_avatar')) and $upload->store('avatar', $pr_data)) {
                        $pr_data['avatar_ext_id'] = $upload->file_ext_id;
                        $db_data['avatar_ext_id'] = (int)$upload->file_ext_id;
                    } else {
                        $errors = array_merge($errors, $upload->errors);
                    }
                }
            }
            $tp_data['AVATARS_MAX_SIZE'] = humn_size(config()->get('avatars.max_size'));
            break;

            /**
             *  Сайт (edit)
             */
        case 'user_website':
            $website = request()->post->has('user_website') ? (string)request()->post->get('user_website') : $pr_data['user_website'];
            $website = htmlCHR($website);
            if ($submit && $website != $pr_data['user_website']) {
                if ($website == '' || preg_match('#^https?://[\w\#!$%&~/.\-;:=,?@а-яА-Я\[\]+]+$#iu', $website)) {
                    $pr_data['user_website'] = $website;
                    $db_data['user_website'] = (string)$website;
                } else {
                    $pr_data['user_website'] = '';
                    $errors[] = htmlCHR(__('WEBSITE_ERROR'));
                }
            }
            $tp_data['USER_WEBSITE'] = $pr_data['user_website'];
            break;

            /**
             *  Откуда (edit)
             */
        case 'user_from':
            $from = request()->post->has('user_from') ? (string)request()->post->get('user_from') : $pr_data['user_from'];
            if ($submit && $from != $pr_data['user_from']) {
                $pr_data['user_from'] = $from;
                $db_data['user_from'] = (string)$from;
            }
            $tp_data['USER_FROM'] = $pr_data['user_from'];
            $tp_data['COUNTRY_SELECTED'] = render_flag($pr_data['user_from']);
            $tp_data['COUNTRY_SELECT'] = build_select('user_from', array_flip(config()->get('countries')), $pr_data['user_from']);
            $tp_data['CHECKED_MANUAL_COUNTRY'] = ($tp_data['COUNTRY_SELECTED'] === $pr_data['user_from']) ? 'checked' : '';
            break;

            /**
             *  Подпись (edit)
             */
        case 'user_sig':
            $sig = request()->post->has('user_sig') ? (string)request()->post->get('user_sig') : $pr_data['user_sig'];
            if ($submit && $sig != $pr_data['user_sig']) {
                $sig = prepare_message($sig);

                if (mb_strlen($sig, DEFAULT_CHARSET) > config()->get('max_sig_chars')) {
                    $errors[] = __('SIGNATURE_TOO_LONG');
                } elseif (preg_match('#<(a|b|i|u|table|tr|td|img) #i', $sig) || preg_match('#(href|src|target|title)=#i', $sig)) {
                    $errors[] = __('SIGNATURE_ERROR_HTML');
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
            $occ = request()->post->has('user_occ') ? (string)request()->post->get('user_occ') : $pr_data['user_occ'];
            $occ = htmlCHR($occ);
            if ($submit && $occ != $pr_data['user_occ']) {
                $pr_data['user_occ'] = $occ;
                $db_data['user_occ'] = (string)$occ;
            }
            $tp_data['USER_OCC'] = $pr_data['user_occ'];
            break;

            /**
             *  Интересы (edit)
             */
        case 'user_interests':
            $interests = request()->post->has('user_interests') ? (string)request()->post->get('user_interests') : $pr_data['user_interests'];
            $interests = htmlCHR($interests);
            if ($submit && $interests != $pr_data['user_interests']) {
                $pr_data['user_interests'] = $interests;
                $db_data['user_interests'] = (string)$interests;
            }
            $tp_data['USER_INTERESTS'] = $pr_data['user_interests'];
            break;

            /**
             *  Twitter (edit)
             */
        case 'user_twitter':
            $twitter = request()->post->has('user_twitter') ? (string)request()->post->get('user_twitter') : $pr_data['user_twitter'];
            if ($submit && $twitter != $pr_data['user_twitter']) {
                if ($twitter != '' && !preg_match('#^[a-zA-Z0-9_]{1,15}$#', $twitter)) {
                    $errors[] = __('TWITTER_ERROR');
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
            $templates = request()->post->has('tpl_name') ? (string)request()->post->get('tpl_name') : $pr_data['tpl_name'];
            $templates = htmlCHR($templates);
            if ($submit && $templates != $pr_data['tpl_name']) {
                $pr_data['tpl_name'] = config()->get('tpl_name');
                $db_data['tpl_name'] = (string)config()->get('tpl_name');
                $availableTemplates = config()->get('templates');
                foreach ($availableTemplates as $folder => $name) {
                    if ($templates == $folder) {
                        $pr_data['tpl_name'] = $templates;
                        $db_data['tpl_name'] = (string)$templates;
                    }
                }
            }
            $tp_data['TEMPLATES_SELECT'] = TorrentPier\Legacy\Common\Select::template($pr_data['tpl_name'], 'tpl_name');
            break;

            /**
             *  default
             */
        default:
            throw new RuntimeException("invalid profile field: {$field}");
    }
}

// submit
if ($submit && !$errors) {
    /**
     *  Создание нового профиля
     */
    if ($mode == 'register') {
        if (config()->get('reg_email_activation')) {
            $user_actkey = make_rand_str(ACTKEY_LENGTH);
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
            $db_data['tpl_name'] = (string)config()->get('tpl_name');
        }

        $sql_args = DB()->build_array('INSERT', $db_data);

        DB()->query('INSERT INTO ' . BB_USERS . $sql_args);
        $new_user_id = DB()->sql_nextid();

        // Generate passkey
        for ($i = 0, $max_try = 3; $i <= $max_try; $i++) {
            if (TorrentPier\Torrent\Passkey::generate($new_user_id, true)) {
                break;
            }
            if ($i == $max_try) {
                bb_simple_die('Could not generate passkey');
            }
        }

        // Manticore [User Add]
        sync_user_to_manticore($new_user_id, $username);

        if (IS_ADMIN) {
            set_pr_die_append_msg($new_user_id);
            $message = __('ACCOUNT_ADDED');
        } else {
            if (config()->get('reg_email_activation')) {
                $message = __('ACCOUNT_INACTIVE');
                $email_subject = sprintf(__('EMAILER_SUBJECT')['USER_WELCOME_INACTIVE'], config()->get('sitename'));
                $email_template = 'user_welcome_inactive';
            } else {
                $message = __('ACCOUNT_ADDED');
                $email_subject = sprintf(__('EMAILER_SUBJECT')['USER_WELCOME'], config()->get('sitename'));
                $email_template = 'user_welcome';
            }

            // Sending email
            $emailer = new TorrentPier\Emailer();

            $emailer->set_to($email, $username);
            $emailer->set_subject($email_subject);

            $emailer->set_template($email_template, $user_lang);
            $emailer->assign_vars([
                'WELCOME_MSG' => sprintf(__('WELCOME_SUBJECT'), config()->get('sitename')),
                'USERNAME' => html_entity_decode($username),
                'PASSWORD' => $new_pass,
                'U_ACTIVATE' => make_url(ACTIVATE_URL . $new_user_id . '/' . $db_data['user_actkey'] . '/'),
            ]);

            $emailer->send();
        }

        bb_die($message);
    } /**
     *  Редактирование
     */ else {
        set_pr_die_append_msg($pr_data['user_id']);

        // if anything has been changed
        if ($db_data) {
            if (!$pr_data['user_active']) {
                $user_actkey = make_rand_str(ACTKEY_LENGTH);
                $pr_data['user_actkey'] = $user_actkey;
                $db_data['user_actkey'] = $user_actkey;

                // Sending email
                $emailer = new TorrentPier\Emailer();

                $emailer->set_to($email, $username);
                $emailer->set_subject(__('EMAILER_SUBJECT')['USER_ACTIVATE']);

                $emailer->set_template('user_activate', $pr_data['user_lang']);
                $emailer->assign_vars([
                    'USERNAME' => html_entity_decode($username),
                    'U_ACTIVATE' => make_url(ACTIVATE_URL . $pr_data['user_id'] . '/' . $user_actkey . '/'),
                ]);

                $emailer->send();

                $message = __('PROFILE_UPDATED_INACTIVE');
                user()->session_end();
            } else {
                meta_refresh('index.php', 10);
                $message = __('PROFILE_UPDATED');
            }

            $sql_args = DB()->build_array('UPDATE', $db_data);

            DB()->query('UPDATE ' . BB_USERS . " SET {$sql_args} WHERE user_id = {$pr_data['user_id']}");

            // Manticore [Update username]
            if (!empty($db_data['username'])) {
                sync_user_to_manticore($pr_data['user_id'], $db_data['username']);
            }

            if ($pr_data['user_id'] != userdata('user_id')) {
                if ($pr_data['user_level'] == MOD && !empty($db_data['username'])) {
                    datastore()->update('moderators');
                }
            }

            TorrentPier\Sessions::cache_rm_user_sessions($pr_data['user_id']);

            if ($adm_edit) {
                bb_die(__('PROFILE_USER') . ' <b>' . profile_url($pr_data) . '</b> ' . __('GOOD_UPDATE'));
            } elseif (!$pr_data['user_active']) {
                bb_die(__('PROFILE_UPDATED_INACTIVE'));
            } else {
                meta_refresh('index.php', 10);
                bb_die(__('PROFILE_UPDATED'));
            }
        } else {
            bb_die(__('NOTHING_HAS_CHANGED'));
        }
    }
}

template()->assign_vars($can_edit_tpl);
template()->assign_vars($tp_data);

template()->assign_vars([
    'PAGE_TITLE' => ($mode == 'editprofile') ? __('EDIT_PROFILE') . ($adm_edit ? " :: {$pr_data['username']}" : '') : __('REGISTER'),
    'SHOW_REG_AGREEMENT' => ($mode == 'register' && !IS_ADMIN),
    'ERROR_MESSAGE' => ($errors) ? implode('<br />', array_unique($errors)) : '',
    'MODE' => $mode,
    'EDIT_PROFILE' => ($mode == 'editprofile'),
    'ADM_EDIT' => $adm_edit,
    'SHOW_PASS' => ($adm_edit || ($mode == 'register' && IS_ADMIN)),
    'PASSWORD_LONG' => sprintf(__('PASSWORD_LONG'), PASSWORD_MAX_LENGTH, PASSWORD_MIN_LENGTH),
    'INVITE_CODE' => request()->query->has('invite') ? htmlCHR(trim((string)request()->query->get('invite'))) : '',
    'CAPTCHA_HTML' => ($need_captcha) ? bb_captcha('get') : '',

    'LANGUAGE_SELECT' => TorrentPier\Legacy\Common\Select::language($pr_data['user_lang'], 'user_lang'),
    'TIMEZONE_SELECT' => TorrentPier\Legacy\Common\Select::timezone($pr_data['user_timezone'], 'user_timezone'),

    'AVATAR_EXPLAIN' => sprintf(__('AVATAR_EXPLAIN'), config()->get('avatars.max_width'), config()->get('avatars.max_height'), humn_size(config()->get('avatars.max_size'))),
    'AVATAR_DISALLOWED' => bf($pr_data['user_opt'], 'user_opt', 'dis_avatar'),
    'AVATAR_DIS_EXPLAIN' => sprintf(__('AVATAR_DISABLE'), config()->get('terms_and_conditions_url')),
    'AVATAR_IMG' => get_avatar($pr_data['user_id'], $pr_data['avatar_ext_id'], !bf($pr_data['user_opt'], 'user_opt', 'dis_avatar')),

    'SIGNATURE_EXPLAIN' => sprintf(__('SIGNATURE_EXPLAIN'), config()->get('max_sig_chars')),
    'SIG_DISALLOWED' => bf($pr_data['user_opt'], 'user_opt', 'dis_sig'),

    'PR_USER_ID' => $pr_data['user_id'],
    'U_RESET_AUTOLOGIN' => LOGIN_URL . '?logout=1&amp;reset_autologin=1&amp;sid=' . userdata('session_id'),
]);

print_page('usercp_register.tpl');
