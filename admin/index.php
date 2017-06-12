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

require __DIR__ . '/pagestart.php';

// Generate relevant output
if (isset($_GET['pane']) && $_GET['pane'] == 'left') {
    $module = [];
    if (!$module = CACHE('bb_cache')->get('admin_module_' . $user->id)) {
        $dir = opendir('.');
        $setmodules = 1;
        while ($file = readdir($dir)) {
            if (preg_match('/^admin_.*?\.php$/', $file)) {
                include './' . $file;
            }
        }
        unset($setmodules);
        closedir($dir);
        CACHE('bb_cache')->set('admin_module_' . $user->id, $module, 600);
    }

    $module = CACHE('bb_cache')->get('admin_module_' . $user->id);

    $template->assign_vars(array(
        'TPL_ADMIN_NAVIGATE' => true,
        'U_FORUM_INDEX' => '../index.php',
        'U_ADMIN_INDEX' => 'index.php?pane=right',
    ));

    ksort($module);

    foreach ($module as $cat => $action_array) {
        $cat = (!empty($lang[$cat])) ? $lang[$cat] : preg_replace('/_/', ' ', $cat);

        $template->assign_block_vars('catrow', array(
            'ADMIN_CATEGORY' => $cat,
        ));

        ksort($action_array);

        $row_count = 0;
        foreach ($action_array as $action => $file) {
            $row_class = !($row_count % 2) ? 'row1' : 'row2';

            $action = (!empty($lang[$action])) ? $lang[$action] : preg_replace('/_/', ' ', $action);

            $template->assign_block_vars('catrow.modulerow', array(
                'ROW_CLASS' => $row_class,
                'ADMIN_MODULE' => $action,
                'U_ADMIN_MODULE' => $file,
            ));
            $row_count++;
        }
    }
} elseif (isset($_GET['pane']) && $_GET['pane'] == 'right') {
    $template->assign_vars(array(
        'TPL_ADMIN_MAIN' => true,
        'ADMIN_LOCK' => $bb_cfg['board_disable'] ? true : false,
        'ADMIN_LOCK_CRON' => file_exists(BB_DISABLED) ? true : false,
    ));

    // Get forum statistics
    $total_posts = get_db_stat('postcount');
    $total_users = get_db_stat('usercount');
    $total_topics = get_db_stat('topiccount');
    $start_date = bb_date($bb_cfg['board_startdate']);
    $boarddays = (TIMENOW - $bb_cfg['board_startdate']) / 86400;

    $posts_per_day = sprintf('%.2f', $total_posts / $boarddays);
    $topics_per_day = sprintf('%.2f', $total_topics / $boarddays);
    $users_per_day = sprintf('%.2f', $total_users / $boarddays);

    $avatar_dir_size = 0;

    if ($avatar_dir = opendir($bb_cfg['avatars']['upload_path'])) {
        while ($file = readdir($avatar_dir)) {
            if ($file != '.' && $file != '..') {
                $avatar_dir_size += @filesize(BB_ROOT . $bb_cfg['avatar_path'] . '/' . $file);
            }
        }
        closedir($avatar_dir);

        $avatar_dir_size = humn_size($avatar_dir_size);
    } else {
        $avatar_dir_size = $lang['NOT_AVAILABLE'];
    }

    if ((int)$posts_per_day > $total_posts) {
        $posts_per_day = $total_posts;
    }

    if ((int)$topics_per_day > $total_topics) {
        $topics_per_day = $total_topics;
    }

    if ($users_per_day > $total_users) {
        $users_per_day = $total_users;
    }

    $template->assign_vars(array(
        'NUMBER_OF_POSTS' => $total_posts,
        'NUMBER_OF_TOPICS' => $total_topics,
        'NUMBER_OF_USERS' => $total_users,
        'START_DATE' => $start_date,
        'POSTS_PER_DAY' => $posts_per_day,
        'TOPICS_PER_DAY' => $topics_per_day,
        'USERS_PER_DAY' => $users_per_day,
        'AVATAR_DIR_SIZE' => $avatar_dir_size,
    ));

    if (isset($_GET['users_online'])) {
        $template->assign_vars(array(
            'SHOW_USERS_ONLINE' => true,
        ));

        // Get users online information.
        $sql = 'SELECT u.user_id, u.username, u.user_rank, s.session_time AS user_session_time, u.user_opt, s.session_logged_in, s.session_ip, s.session_start
			FROM ' . BB_USERS . ' u, ' . BB_SESSIONS . ' s
			WHERE s.session_logged_in = 1
				AND u.user_id = s.session_user_id
				AND u.user_id <> ' . GUEST_UID . '
				AND s.session_time >= ' . (TIMENOW - 300) . '
			ORDER BY s.session_ip ASC, s.session_time DESC';
        if (!$result = DB()->sql_query($sql)) {
            bb_die('Could not obtain reged user / online information');
        }
        $onlinerow_reg = DB()->sql_fetchrowset($result);

        $sql = 'SELECT session_logged_in, session_time, session_ip, session_start
			FROM ' . BB_SESSIONS . '
			WHERE session_logged_in = 0
				AND session_time >= ' . (TIMENOW - 300) . '
			ORDER BY session_ip ASC, session_time DESC';
        if (!$result = DB()->sql_query($sql)) {
            bb_die('Could not obtain guest user / online information');
        }
        $onlinerow_guest = DB()->sql_fetchrowset($result);

        $reg_userid_ary = array();

        if (count($onlinerow_reg)) {
            $registered_users = $hidden_users = 0;

            for ($i = 0, $iMax = count($onlinerow_reg); $i < $iMax; $i++) {
                if (!in_array($onlinerow_reg[$i]['user_id'], $reg_userid_ary)) {
                    $reg_userid_ary[] = $onlinerow_reg[$i]['user_id'];

                    $username = $onlinerow_reg[$i]['username'];

                    if (bf($onlinerow_reg[$i]['user_opt'], 'user_opt', 'user_viewonline')) {
                        $hidden_users++;
                        $hidden = true;
                    } else {
                        $registered_users++;
                        $hidden = false;
                    }

                    $row_class = 'row1';

                    $reg_ip = decode_ip($onlinerow_reg[$i]['session_ip']);

                    $template->assign_block_vars('reg_user_row', array(
                        'ROW_CLASS' => $row_class,
                        'USER' => profile_url($onlinerow_reg[$i]),
                        'STARTED' => bb_date($onlinerow_reg[$i]['session_start'], 'H:i', false),
                        'LASTUPDATE' => bb_date($onlinerow_reg[$i]['user_session_time'], 'H:i', false),
                        'IP_ADDRESS' => $reg_ip,
                        'U_WHOIS_IP' => $bb_cfg['whois_info'] . $reg_ip,
                    ));
                }
            }
        }

        // Guest users
        if (count($onlinerow_guest)) {
            $guest_users = 0;

            for ($i = 0, $iMax = count($onlinerow_guest); $i < $iMax; $i++) {
                $guest_userip_ary[] = $onlinerow_guest[$i]['session_ip'];
                $guest_users++;

                $row_class = 'row2';

                $guest_ip = decode_ip($onlinerow_guest[$i]['session_ip']);

                $template->assign_block_vars('guest_user_row', array(
                    'ROW_CLASS' => $row_class,
                    'STARTED' => bb_date($onlinerow_guest[$i]['session_start'], 'H:i', false),
                    'LASTUPDATE' => bb_date($onlinerow_guest[$i]['session_time'], 'H:i', false),
                    'IP_ADDRESS' => $guest_ip,
                    'U_WHOIS_IP' => $bb_cfg['whois_info'] . $guest_ip,
                ));
            }
        }
    } else {
        $template->assign_vars(array(
            'USERS_ONLINE_HREF' => 'index.php?pane=right&users_online=1',
        ));
    }
} else {
    // Generate frameset
    $template->assign_vars(array(
        'CONTENT_ENCODING' => $bb_cfg['charset'],
        'TPL_ADMIN_FRAMESET' => true,
    ));
    send_no_cache_headers();
    print_page('index.tpl', 'admin', 'no_header');
}

print_page('index.tpl', 'admin');
