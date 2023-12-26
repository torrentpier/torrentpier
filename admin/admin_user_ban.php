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
    // Ban action
    if (!empty($_POST['username'])) {
        if (!$this_userdata = get_userdata($_POST['username'], true)) {
            bb_die($lang['NO_USER_ID_SPECIFIED'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_BANADMIN'], '<a href="admin_user_ban.php">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
        }

        if (!getBanInfo((int)$this_userdata['user_id'])) {
            $sql = 'INSERT INTO ' . BB_BANLIST . ' (ban_userid) VALUES (' . $this_userdata['user_id'] . ')';
            if (!DB()->sql_query($sql)) {
                bb_die('Could not insert ban_userid info into database');
            }
        }
    }

    // Unban action
    $where_sql = '';

    if (!empty($_POST['unban_user'])) {
        $user_list = $_POST['unban_user'];

        for ($i = 0, $iMax = count($user_list); $i < $iMax; $i++) {
            if ($user_list[$i] != -1) {
                $where_sql .= (($where_sql != '') ? ', ' : '') . (int)$user_list[$i];
            }
        }

        if ($where_sql != '') {
            $sql = 'DELETE FROM ' . BB_BANLIST . " WHERE ban_id IN ($where_sql)";
            if (!DB()->sql_query($sql)) {
                bb_die('Could not delete ban info from database');
            }
        }
    }

    $datastore->update('ban_list');
    bb_die($lang['BAN_UPDATE_SUCESSFUL'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_BANADMIN'], '<a href="admin_user_ban.php">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
} else {
    $template->assign_vars(['S_BANLIST_ACTION' => 'admin_user_ban.php']);

    $select_userlist = '';
    foreach (getBanInfo() as $ban) {
        $select_userlist .= '<option value="' . $ban['ban_id'] . '">' . get_username($ban['ban_userid']) . '</option>';
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
