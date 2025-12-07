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

if (!config()->get('emailer.enabled')) {
    bb_die(__('EMAILER_DISABLED'));
}

$need_captcha = (request()->query->get('mode') == 'sendpassword' && !IS_ADMIN && !config()->get('captcha.disabled'));

if (request()->post->has('submit')) {
    if ($need_captcha && !bb_captcha('check')) {
        bb_die(__('CAPTCHA_WRONG'));
    }
    $email = (!empty(request()->post->get('email'))) ? trim(strip_tags(htmlspecialchars(request()->post->get('email')))) : '';
    $sql = "SELECT * FROM " . BB_USERS . " WHERE user_email = '" . DB()->escape($email) . "'";
    if ($result = DB()->sql_query($sql)) {
        if ($row = DB()->sql_fetchrow($result)) {
            if (!$row['user_active']) {
                bb_die(__('NO_SEND_ACCOUNT_INACTIVE'));
            }
            if (in_array($row['user_level'], [MOD, ADMIN])) {
                bb_die(__('NO_SEND_ACCOUNT'));
            }

            $username = $row['username'];
            $user_id = $row['user_id'];

            $user_actkey = make_rand_str(ACTKEY_LENGTH);
            $user_password = make_rand_str(PASSWORD_MIN_LENGTH);

            $sql = "UPDATE " . BB_USERS . "
				SET user_newpasswd = '$user_password', user_actkey = '$user_actkey'
				WHERE user_id = " . $row['user_id'];
            if (!DB()->sql_query($sql)) {
                bb_die('Could not update new password information');
            }

            // Sending email
            $emailer = new TorrentPier\Emailer();

            $emailer->set_to($row['user_email'], $username);
            $emailer->set_subject(__('EMAILER_SUBJECT')['USER_ACTIVATE_PASSWD']);

            $emailer->set_template('user_activate_passwd', $row['user_lang']);
            $emailer->assign_vars([
                'USERNAME' => $username,
                'PASSWORD' => $user_password,
                'U_ACTIVATE' => make_url('profile?mode=activate&' . POST_USERS_URL . '=' . $user_id . '&act_key=' . $user_actkey)
            ]);

            $emailer->send();

            bb_die(__('PASSWORD_UPDATED'));
        } else {
            bb_die(__('NO_EMAIL_MATCH'));
        }
    } else {
        bb_die('Could not obtain user information for sendpassword');
    }
} else {
    $email = $username = '';
}

template()->assign_vars([
    'USERNAME' => $username,
    'EMAIL' => $email,
    'CAPTCHA_HTML' => ($need_captcha) ? bb_captcha('get') : '',
    'S_HIDDEN_FIELDS' => '',
    'S_PROFILE_ACTION' => 'profile?mode=sendpassword'
]);

print_page('usercp_sendpasswd.tpl');
