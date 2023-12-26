<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    $module['USERS']['BAN_MANAGEMENT'] = basename(__FILE__);
    return;
}

require __DIR__ . '/pagestart.php';

if (isset($_POST['submit'])) {
    $user_list = [];
    if (!empty($_POST['username'])) {
        if (!$this_userdata = get_userdata($_POST['username'], true)) {
            bb_die($lang['NO_USER_ID_SPECIFIED']);
        }

        $user_list[] = $this_userdata['user_id'];
    }

    $sql = 'SELECT * FROM ' . BB_BANLIST;
    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not obtain banlist information');
    }

    $current_banlist = DB()->sql_fetchrowset($result);
    DB()->sql_freeresult($result);

    for ($i = 0, $iMax = count($user_list); $i < $iMax; $i++) {
        $in_banlist = false;
        for ($j = 0, $jMax = count($current_banlist); $j < $jMax; $j++) {
            if ($user_list[$i] == $current_banlist[$j]['ban_userid']) {
                $in_banlist = true;
            }
        }

        if (!$in_banlist) {
            $sql = 'INSERT INTO ' . BB_BANLIST . ' (ban_userid) VALUES (' . $user_list[$i] . ')';
            if (!DB()->sql_query($sql)) {
                bb_die('Could not insert ban_userid info into database');
            }
        }
    }

    $where_sql = '';

    if (isset($_POST['unban_user'])) {
        $user_list = $_POST['unban_user'];

        for ($i = 0, $iMax = count($user_list); $i < $iMax; $i++) {
            if ($user_list[$i] != -1) {
                $where_sql = (int)$user_list[$i];
            }
        }

        if ($where_sql != '') {
            $sql = 'DELETE FROM ' . BB_BANLIST . " WHERE ban_id IN ($where_sql)";
            if (!DB()->sql_query($sql)) {
                bb_die('Could not delete ban info from database');
            }
        }
    }

    bb_die($lang['BAN_UPDATE_SUCESSFUL'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_BANADMIN'], '<a href="admin_user_ban.php">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
} else {
    $template->assign_vars(['S_BANLIST_ACTION' => 'admin_user_ban.php']);
    $userban_count = 0;

    $sql = 'SELECT b.ban_id, u.user_id, u.username
		FROM ' . BB_BANLIST . ' b, ' . BB_USERS . ' u
		WHERE u.user_id = b.ban_userid
			AND b.ban_userid <> 0
			AND u.user_id <> ' . GUEST_UID . '
		ORDER BY u.username ASC';
    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not select current user_id ban list');
    }

    $user_list = DB()->sql_fetchrowset($result);
    DB()->sql_freeresult($result);

    $select_userlist = '';
    for ($i = 0, $iMax = count($user_list); $i < $iMax; $i++) {
        $select_userlist .= '<option value="' . $user_list[$i]['ban_id'] . '">' . $user_list[$i]['username'] . '</option>';
        $userban_count++;
    }

    if ($select_userlist == '') {
        $select_userlist = '<option value="-1">' . $lang['NO_BANNED_USERS'] . '</option>';
    }
    $select_userlist = '<select name="unban_user[]" multiple size="5">' . $select_userlist . '</select>';

    $template->assign_vars([
        'U_SEARCH_USER' => './../search.php?mode=searchuser',
        'S_UNBAN_USERLIST_SELECT' => $select_userlist,
        'S_BAN_ACTION' => 'admin_user_ban.php'
    ]);
}

print_page('admin_user_ban.tpl', 'admin');
