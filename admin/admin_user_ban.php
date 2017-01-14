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

if (!empty($setmodules)) {
    $module['USERS']['BAN_MANAGEMENT'] = basename(__FILE__);
    return;
}
require('./pagestart.php');

if (isset($_POST['submit'])) {
    $user_bansql = '';
    $email_bansql = '';
    $ip_bansql = '';

    $user_list = array();
    if (!empty($_POST['username'])) {
        $this_userdata = get_userdata($_POST['username'], true);
        if (!$this_userdata) {
            bb_die($lang['NO_USER_ID_SPECIFIED']);
        }

        $user_list[] = $this_userdata['user_id'];
    }

    $ip_list = array();
    if (isset($_POST['ban_ip'])) {
        $ip_list_temp = explode(',', $_POST['ban_ip']);

        for ($i = 0; $i < count($ip_list_temp); $i++) {
            if (preg_match('/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})[ ]*\-[ ]*([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$/', trim($ip_list_temp[$i]), $ip_range_explode)) {
                $ip_1_counter = $ip_range_explode[1];
                $ip_1_end = $ip_range_explode[5];

                while ($ip_1_counter <= $ip_1_end) {
                    $ip_2_counter = ($ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[2] : 0;
                    $ip_2_end = ($ip_1_counter < $ip_1_end) ? 254 : $ip_range_explode[6];

                    if ($ip_2_counter == 0 && $ip_2_end == 254) {
                        $ip_2_counter = 255;
                        $ip_2_fragment = 255;

                        $ip_list[] = encode_ip("$ip_1_counter.255.255.255");
                    }

                    while ($ip_2_counter <= $ip_2_end) {
                        $ip_3_counter = ($ip_2_counter == $ip_range_explode[2] && $ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[3] : 0;
                        $ip_3_end = ($ip_2_counter < $ip_2_end || $ip_1_counter < $ip_1_end) ? 254 : $ip_range_explode[7];

                        if ($ip_3_counter == 0 && $ip_3_end == 254) {
                            $ip_3_counter = 255;
                            $ip_3_fragment = 255;

                            $ip_list[] = encode_ip("$ip_1_counter.$ip_2_counter.255.255");
                        }

                        while ($ip_3_counter <= $ip_3_end) {
                            $ip_4_counter = ($ip_3_counter == $ip_range_explode[3] && $ip_2_counter == $ip_range_explode[2] && $ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[4] : 0;
                            $ip_4_end = ($ip_3_counter < $ip_3_end || $ip_2_counter < $ip_2_end) ? 254 : $ip_range_explode[8];

                            if ($ip_4_counter == 0 && $ip_4_end == 254) {
                                $ip_4_counter = 255;
                                $ip_4_fragment = 255;

                                $ip_list[] = encode_ip("$ip_1_counter.$ip_2_counter.$ip_3_counter.255");
                            }

                            while ($ip_4_counter <= $ip_4_end) {
                                $ip_list[] = encode_ip("$ip_1_counter.$ip_2_counter.$ip_3_counter.$ip_4_counter");
                                $ip_4_counter++;
                            }
                            $ip_3_counter++;
                        }
                        $ip_2_counter++;
                    }
                    $ip_1_counter++;
                }
            } elseif (preg_match('/^([\w\-_]\.?){2,}$/is', trim($ip_list_temp[$i]))) {
                $ip = gethostbynamel(trim($ip_list_temp[$i]));

                for ($j = 0; $j < count($ip); $j++) {
                    if (!empty($ip[$j])) {
                        $ip_list[] = encode_ip($ip[$j]);
                    }
                }
            } elseif (preg_match('/^([0-9]{1,3})\.([0-9\*]{1,3})\.([0-9\*]{1,3})\.([0-9\*]{1,3})$/', trim($ip_list_temp[$i]))) {
                $ip_list[] = encode_ip(str_replace('*', '255', trim($ip_list_temp[$i])));
            }
        }
    }

    $email_list = array();
    if (isset($_POST['ban_email'])) {
        $email_list_temp = explode(',', $_POST['ban_email']);

        for ($i = 0; $i < count($email_list_temp); $i++) {
            if (preg_match('/^(([a-z0-9&\'\.\-_\+])|(\*))+@(([a-z0-9\-])|(\*))+\.([a-z0-9\-]+\.)*?[a-z]+$/is', trim($email_list_temp[$i]))) {
                $email_list[] = trim($email_list_temp[$i]);
            }
        }
    }

    $sql = "SELECT * FROM " . BB_BANLIST;
    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not obtain banlist information');
    }

    $current_banlist = DB()->sql_fetchrowset($result);
    DB()->sql_freeresult($result);

    $kill_session_sql = '';
    for ($i = 0; $i < count($user_list); $i++) {
        $in_banlist = false;
        for ($j = 0; $j < count($current_banlist); $j++) {
            if ($user_list[$i] == $current_banlist[$j]['ban_userid']) {
                $in_banlist = true;
            }
        }

        if (!$in_banlist) {
            $kill_session_sql .= (($kill_session_sql != '') ? ' OR ' : '') . "session_user_id = " . $user_list[$i];

            $sql = "INSERT INTO " . BB_BANLIST . " (ban_userid) VALUES (" . $user_list[$i] . ")";
            if (!DB()->sql_query($sql)) {
                bb_die('Could not insert ban_userid info into database');
            }
        }
    }

    for ($i = 0; $i < count($ip_list); $i++) {
        $in_banlist = false;
        for ($j = 0; $j < count($current_banlist); $j++) {
            if ($ip_list[$i] == $current_banlist[$j]['ban_ip']) {
                $in_banlist = true;
            }
        }

        if (!$in_banlist) {
            if (preg_match('/(ff\.)|(\.ff)/is', chunk_split($ip_list[$i], 2, '.'))) {
                $kill_ip_sql = "session_ip LIKE '" . str_replace('.', '', preg_replace('/(ff\.)|(\.ff)/is', '%', chunk_split($ip_list[$i], 2, "."))) . "'";
            } else {
                $kill_ip_sql = "session_ip = '" . $ip_list[$i] . "'";
            }

            $kill_session_sql .= (($kill_session_sql != '') ? ' OR ' : '') . $kill_ip_sql;

            $sql = "INSERT INTO " . BB_BANLIST . " (ban_ip) VALUES ('" . $ip_list[$i] . "')";
            if (!DB()->sql_query($sql)) {
                bb_die('Could not insert ban_ip info into database');
            }
        }
    }

    // Now we'll delete all entries from the session table
    if ($kill_session_sql != '') {
        $sql = "DELETE FROM " . BB_SESSIONS . " WHERE $kill_session_sql";
        if (!DB()->sql_query($sql)) {
            bb_die('Could not delete banned sessions from database');
        }
    }

    for ($i = 0; $i < count($email_list); $i++) {
        $in_banlist = false;
        for ($j = 0; $j < count($current_banlist); $j++) {
            if ($email_list[$i] == $current_banlist[$j]['ban_email']) {
                $in_banlist = true;
            }
        }

        if (!$in_banlist) {
            $sql = "INSERT INTO " . BB_BANLIST . " (ban_email) VALUES ('" . DB()->escape($email_list[$i]) . "')";
            if (!DB()->sql_query($sql)) {
                bb_die('Could not insert ban_email info into database');
            }
        }
    }

    $where_sql = '';

    if (isset($_POST['unban_user'])) {
        $user_list = $_POST['unban_user'];

        for ($i = 0; $i < count($user_list); $i++) {
            if ($user_list[$i] != -1) {
                $where_sql .= (($where_sql != '') ? ', ' : '') . intval($user_list[$i]);
            }
        }
    }

    if (isset($_POST['unban_ip'])) {
        $ip_list = $_POST['unban_ip'];

        for ($i = 0; $i < count($ip_list); $i++) {
            if ($ip_list[$i] != -1) {
                $where_sql .= (($where_sql != '') ? ', ' : '') . DB()->escape($ip_list[$i]);
            }
        }
    }

    if (isset($_POST['unban_email'])) {
        $email_list = $_POST['unban_email'];

        for ($i = 0; $i < count($email_list); $i++) {
            if ($email_list[$i] != -1) {
                $where_sql .= (($where_sql != '') ? ', ' : '') . DB()->escape($email_list[$i]);
            }
        }
    }

    if ($where_sql != '') {
        $sql = "DELETE FROM " . BB_BANLIST . " WHERE ban_id IN ($where_sql)";
        if (!DB()->sql_query($sql)) {
            bb_die('Could not delete ban info from database');
        }
    }

    bb_die($lang['BAN_UPDATE_SUCESSFUL'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_BANADMIN'], '<a href="admin_user_ban.php">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
} else {
    $template->assign_vars(array(
        'S_BANLIST_ACTION' => 'admin_user_ban.php',
    ));

    $userban_count = 0;
    $ipban_count = 0;
    $emailban_count = 0;

    $sql = "SELECT b.ban_id, u.user_id, u.username
		FROM " . BB_BANLIST . " b, " . BB_USERS . " u
		WHERE u.user_id = b.ban_userid
			AND b.ban_userid <> 0
			AND u.user_id <> " . GUEST_UID . "
		ORDER BY u.username ASC";
    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not select current user_id ban list');
    }

    $user_list = DB()->sql_fetchrowset($result);
    DB()->sql_freeresult($result);

    $select_userlist = '';
    for ($i = 0; $i < count($user_list); $i++) {
        $select_userlist .= '<option value="' . $user_list[$i]['ban_id'] . '">' . $user_list[$i]['username'] . '</option>';
        $userban_count++;
    }

    if ($select_userlist == '') {
        $select_userlist = '<option value="-1">' . $lang['NO_BANNED_USERS'] . '</option>';
    }

    $select_userlist = '<select name="unban_user[]" multiple="multiple" size="5">' . $select_userlist . '</select>';

    $sql = "SELECT ban_id, ban_ip, ban_email FROM " . BB_BANLIST . " ORDER BY ban_ip";
    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not select current ip ban list');
    }

    $banlist = DB()->sql_fetchrowset($result);
    DB()->sql_freeresult($result);

    $select_iplist = '';
    $select_emaillist = '';

    for ($i = 0; $i < count($banlist); $i++) {
        $ban_id = $banlist[$i]['ban_id'];

        if (!empty($banlist[$i]['ban_ip'])) {
            $ban_ip = str_replace('255', '*', decode_ip($banlist[$i]['ban_ip']));
            $select_iplist .= '<option value="' . $ban_id . '">' . $ban_ip . '</option>';
            $ipban_count++;
        } elseif (!empty($banlist[$i]['ban_email'])) {
            $ban_email = $banlist[$i]['ban_email'];
            $select_emaillist .= '<option value="' . $ban_id . '">' . $ban_email . '</option>';
            $emailban_count++;
        }
    }

    if ($select_iplist == '') {
        $select_iplist = '<option value="-1">' . $lang['NO_BANNED_IP'] . '</option>';
    }

    if ($select_emaillist == '') {
        $select_emaillist = '<option value="-1">' . $lang['NO_BANNED_EMAIL'] . '</option>';
    }

    $select_iplist = '<select name="unban_ip[]" multiple="multiple" size="15">' . $select_iplist . '</select>';
    $select_emaillist = '<select name="unban_email[]" multiple="multiple" size="10">' . $select_emaillist . '</select>';

    $template->assign_vars(array(
        'U_SEARCH_USER' => './../search.php?mode=searchuser',
        'S_UNBAN_USERLIST_SELECT' => $select_userlist,
        'S_UNBAN_IPLIST_SELECT' => $select_iplist,
        'S_UNBAN_EMAILLIST_SELECT' => $select_emaillist,
        'S_BAN_ACTION' => 'admin_user_ban.php',
    ));
}

print_page('admin_user_ban.tpl', 'admin');
