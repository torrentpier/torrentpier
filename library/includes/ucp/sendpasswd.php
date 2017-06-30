<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2017 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

set_die_append_msg();

if (!config('email.enabled')) {
    bb_die(trans('messages.EMAILER_DISABLED'));
}

$need_captcha = ($_GET['mode'] == 'sendpassword' && !IS_ADMIN && !config('tp.captcha.disabled'));

if (isset($_POST['submit'])) {
    if ($need_captcha && !bb_captcha('check')) {
        bb_die(trans('messages.CAPTCHA_WRONG'));
    }
    $email = (!empty($_POST['email'])) ? trim(strip_tags(htmlspecialchars($_POST['email']))) : '';
    $sql = 'SELECT * FROM ' . BB_USERS . " WHERE user_email = '" . OLD_DB()->escape($email) . "'";
    if ($result = OLD_DB()->sql_query($sql)) {
        if ($row = OLD_DB()->sql_fetchrow($result)) {
            if (!$row['user_active']) {
                bb_die(trans('messages.NO_SEND_ACCOUNT_INACTIVE'));
            }
            if (in_array($row['user_level'], array(MOD, ADMIN))) {
                bb_die(trans('messages.NO_SEND_ACCOUNT'));
            }

            $username = $row['username'];
            $user_id = $row['user_id'];

            $user_actkey = make_rand_str(12);
            $user_password = make_rand_str(8);

            $sql = 'UPDATE ' . BB_USERS . "
				SET user_newpasswd = '$user_password', user_actkey = '$user_actkey'
				WHERE user_id = " . $row['user_id'];
            if (!OLD_DB()->sql_query($sql)) {
                bb_die('Could not update new password information');
            }

            /** @var TorrentPier\Legacy\Emailer() $emailer */
            $emailer = new TorrentPier\Legacy\Emailer();

            $emailer->set_from([config('tp.board_email') => config('tp.sitename')]);
            $emailer->set_to([$row['user_email'] => $username]);
            $emailer->set_subject(trans('messages.EMAILER_SUBJECT.USER_ACTIVATE_PASSWD'));

            $emailer->set_template('user_activate_passwd', $row['user_lang']);
            $emailer->assign_vars(array(
                'SITENAME' => config('tp.sitename'),
                'USERNAME' => $username,
                'PASSWORD' => $user_password,
                'U_ACTIVATE' => make_url('profile.php?mode=activate&' . POST_USERS_URL . '=' . $user_id . '&act_key=' . $user_actkey)
            ));

            $emailer->send();

            bb_die(trans('messages.PASSWORD_UPDATED'));
        } else {
            bb_die(trans('messages.NO_EMAIL_MATCH'));
        }
    } else {
        bb_die('Could not obtain user information for sendpassword');
    }
} else {
    $email = $username = '';
}

$template->assign_vars(array(
    'USERNAME' => $username,
    'EMAIL' => $email,
    'CAPTCHA_HTML' => $need_captcha ? bb_captcha('get') : '',
    'S_HIDDEN_FIELDS' => '',
    'S_PROFILE_ACTION' => 'profile.php?mode=sendpassword',
));

print_page('usercp_sendpasswd.tpl');
