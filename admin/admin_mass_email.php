<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    $module['MODS']['MASS_EMAIL'] = basename(__FILE__);
    return;
}

require __DIR__ . '/pagestart.php';

if (!$bb_cfg['emailer']['enabled']) {
    bb_die($lang['EMAILER_DISABLED']);
}

set_time_limit(1200);

$subject = trim(request_var('subject', ''));
$message = (string)request_var('message', '');
$group_id = (int)request_var(POST_GROUPS_URL, 0);
$reply_to = (string)request_var('reply_to', $bb_cfg['board_email']);
$message_type = (string)request_var('message_type', '');

$errors = $user_id_sql = [];

if (isset($_POST['submit'])) {
    if (!$subject) {
        $errors[] = $lang['EMPTY_SUBJECT'];
    }
    if (!$message) {
        $errors[] = $lang['EMPTY_MESSAGE'];
    }
    if (!$group_id) {
        $errors[] = $lang['GROUP_NOT_EXIST'];
    }

    if (!$errors) {
        $sql = DB()->fetch_rowset('SELECT ban_userid FROM ' . BB_BANLIST . ' WHERE ban_userid != 0');

        foreach ($sql as $row) {
            $user_id_sql[] = ',' . $row['ban_userid'];
        }
        $user_id_sql = implode('', $user_id_sql);

        if ($group_id != -1) {
            $user_list = DB()->fetch_rowset('
				SELECT u.username, u.user_email, u.user_lang
				FROM ' . BB_USERS . ' u, ' . BB_USER_GROUP . " ug
				WHERE ug.group_id = $group_id
					AND ug.user_pending = 0
					AND u.user_id = ug.user_id
					AND u.user_active = 1
					AND u.user_id NOT IN(" . EXCLUDED_USERS . $user_id_sql . ')
			');
        } else {
            $user_list = DB()->fetch_rowset('
				SELECT username, user_email, user_lang
				FROM ' . BB_USERS . '
				WHERE user_active = 1
					AND user_id NOT IN(' . EXCLUDED_USERS . $user_id_sql . ')
			');
        }

        foreach ($user_list as $i => $row) {
            // Sending email
            $emailer = new TorrentPier\Emailer();

            $emailer->set_to($row['user_email'], $row['username']);
            $emailer->set_subject($subject);
            $emailer->set_reply($reply_to);

            $emailer->set_template('admin_send_email');
            $emailer->assign_vars(array(
                'SUBJECT' => html_entity_decode($subject),
                'MESSAGE' => html_entity_decode($message),
            ));

            $emailer->send($message_type);
        }
    }
}

//
// Generate page
//
$sql = 'SELECT group_id, group_name
	FROM ' . BB_GROUPS . '
	WHERE group_single_user = 0
	ORDER BY group_name
';

$groups = array('-- ' . $lang['ALL_USERS'] . ' --' => -1);
foreach (DB()->fetch_rowset($sql) as $row) {
    $groups[$row['group_name']] = $row['group_id'];
}

$template->assign_vars(array(
    'MESSAGE' => $message,
    'SUBJECT' => $subject,
    'REPLY_TO' => $reply_to,

    'ERROR_MESSAGE' => $errors ? implode('<br />', array_unique($errors)) : '',

    'S_USER_ACTION' => 'admin_mass_email.php',
    'S_GROUP_SELECT' => build_select(POST_GROUPS_URL, $groups),
));

print_page('admin_mass_email.tpl', 'admin');
