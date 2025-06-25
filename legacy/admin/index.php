<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine.
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 *
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 *
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

require __DIR__.'/pagestart.php';

// Statistics
if (!$stats = $datastore->get('stats')) {
    $datastore->update('stats');
    $stats = $datastore->get('stats');
}

// Check for updates
if (!$update_data = $datastore->get('check_updates')) {
    $datastore->update('check_updates');
    $update_data = $datastore->get('check_updates');
}

// Generate relevant output
if (isset($_GET['pane']) && $_GET['pane'] == 'left') {
    $module = [];

    // Scan modules
    if (!CACHE('bb_cache')->get('admin_module_'.$user->id)) {
        $dir = opendir('.');
        $setmodules = true;
        while ($file = readdir($dir)) {
            if (preg_match('/^admin_.*?\.php$/', $file)) {
                include './'.$file;
            }
        }
        unset($setmodules);
        closedir($dir);

        // Set modules into cache
        CACHE('bb_cache')->set('admin_module_'.$user->id, $module, 600);
    }

    // Get modules from cache
    $module = CACHE('bb_cache')->get('admin_module_'.$user->id);

    $template->assign_vars([
        'TPL_ADMIN_NAVIGATE' => true,
        'U_FORUM_INDEX'      => '../index.php',
        'U_ADMIN_INDEX'      => 'index.php?pane=right',
    ]);

    ksort($module);

    foreach ($module as $cat => $action_array) {
        $cat = (!empty($lang[$cat])) ? $lang[$cat] : str_replace('_', ' ', $cat);

        $template->assign_block_vars('catrow', [
            'ADMIN_CATEGORY' => $cat,
        ]);

        ksort($action_array);

        $row_count = 0;
        foreach ($action_array as $action => $file) {
            $row_class = !($row_count % 2) ? 'row1' : 'row2';

            $action = (!empty($lang[$action])) ? $lang[$action] : str_replace('_', ' ', $action);

            $template->assign_block_vars('catrow.modulerow', [
                'ROW_CLASS'      => $row_class,
                'ADMIN_MODULE'   => $action,
                'U_ADMIN_MODULE' => $file,
            ]);
            $row_count++;
        }
    }
} elseif (isset($_GET['pane']) && $_GET['pane'] == 'right') {
    $template->assign_vars([
        'TPL_ADMIN_MAIN'  => true,
        'ADMIN_LOCK'      => (bool) config()->get('board_disable'),
        'ADMIN_LOCK_CRON' => is_file(BB_DISABLED),
    ]);

    // Check for updates
    if (isset($update_data['available_update'])) {
        $template->assign_block_vars('updater', [
            'UPDATE_AVAILABLE'    => $update_data['available_update'],
            'NEW_VERSION_NUMBER'  => $update_data['latest_version'],
            'NEW_VERSION_SIZE'    => $update_data['latest_version_size'],
            'NEW_VERSION_DL_LINK' => $update_data['latest_version_dl_link'],
            'NEW_VERSION_LINK'    => $update_data['latest_version_link'],
            'NEW_VERSION_MD5'     => $update_data['latest_version_checksum'],
        ]);
    }

    // Get forum statistics
    $total_posts = $stats['postcount'];
    $total_topics = $stats['topiccount'];
    $total_users = $stats['usercount'];
    $start_date = bb_date(config()->get('board_startdate'));
    $boarddays = (TIMENOW - config()->get('board_startdate')) / 86400;

    $posts_per_day = sprintf('%.2f', $total_posts / $boarddays);
    $topics_per_day = sprintf('%.2f', $total_topics / $boarddays);
    $users_per_day = sprintf('%.2f', $total_users / $boarddays);

    $avatar_dir_size = 0;

    if ($avatar_dir = opendir(config()->get('avatars.upload_path'))) {
        while ($file = readdir($avatar_dir)) {
            if ($file != '.' && $file != '..') {
                $avatar_dir_size += @filesize(config()->get('avatars.upload_path').$file);
            }
        }
        closedir($avatar_dir);

        $avatar_dir_size = humn_size($avatar_dir_size);
    } else {
        $avatar_dir_size = $lang['NOT_AVAILABLE'];
    }

    if ((int) $posts_per_day > $total_posts) {
        $posts_per_day = $total_posts;
    }

    if ((int) $topics_per_day > $total_topics) {
        $topics_per_day = $total_topics;
    }

    if ($users_per_day > $total_users) {
        $users_per_day = $total_users;
    }

    $template->assign_vars([
        'NUMBER_OF_POSTS'  => $total_posts,
        'NUMBER_OF_TOPICS' => $total_topics,
        'NUMBER_OF_USERS'  => $total_users,
        'START_DATE'       => $start_date,
        'POSTS_PER_DAY'    => $posts_per_day,
        'TOPICS_PER_DAY'   => $topics_per_day,
        'USERS_PER_DAY'    => $users_per_day,
        'AVATAR_DIR_SIZE'  => $avatar_dir_size,
    ]);

    if (isset($_GET['users_online'])) {
        $template->assign_vars([
            'SHOW_USERS_ONLINE' => true,
        ]);

        // Get users online information.
        $sql = 'SELECT u.user_id, u.username, u.user_rank, s.session_time AS user_session_time, u.user_opt, s.session_logged_in, s.session_ip, s.session_start
			FROM '.BB_USERS.' u, '.BB_SESSIONS.' s
			WHERE s.session_logged_in = 1
				AND u.user_id = s.session_user_id
				AND u.user_id <> '.GUEST_UID.'
				AND s.session_time >= '.(TIMENOW - 300).'
			ORDER BY s.session_ip ASC, s.session_time DESC';
        if (!$result = DB()->sql_query($sql)) {
            bb_die('Could not obtain reged user / online information');
        }
        $onlinerow_reg = DB()->sql_fetchrowset($result);

        // Get guests online information.
        $sql = 'SELECT session_logged_in, session_time, session_ip, session_start
			FROM '.BB_SESSIONS.'
			WHERE session_logged_in = 0
				AND session_time >= '.(TIMENOW - 300).'
			ORDER BY session_ip ASC, session_time DESC';
        if (!$result = DB()->sql_query($sql)) {
            bb_die('Could not obtain guest user / online information');
        }
        $onlinerow_guest = DB()->sql_fetchrowset($result);

        // Reg users
        if (count($onlinerow_reg)) {
            $users_count = 0;

            for ($i = 0, $iMax = count($onlinerow_reg); $i < $iMax; $i++) {
                $users_count++;
                $row_class = 'row1';
                $reg_ip = \TorrentPier\Helpers\IPHelper::long2ip_extended($onlinerow_reg[$i]['session_ip']);

                $template->assign_block_vars('reg_user_row', [
                    'ROW_CLASS'  => $row_class,
                    'USER'       => profile_url($onlinerow_reg[$i], true),
                    'STARTED'    => bb_date($onlinerow_reg[$i]['session_start'], 'd-M-Y H:i', false),
                    'LASTUPDATE' => bb_date($onlinerow_reg[$i]['user_session_time'], 'd-M-Y H:i', false),
                    'IP_ADDRESS' => $reg_ip,
                    'U_WHOIS_IP' => config()->get('whois_info').$reg_ip,
                ]);
            }
        }

        // Guest users
        if (count($onlinerow_guest)) {
            $guest_users = 0;

            for ($i = 0, $iMax = count($onlinerow_guest); $i < $iMax; $i++) {
                $guest_users++;
                $row_class = 'row2';
                $guest_ip = \TorrentPier\Helpers\IPHelper::long2ip_extended($onlinerow_guest[$i]['session_ip']);

                $template->assign_block_vars('guest_user_row', [
                    'ROW_CLASS'  => $row_class,
                    'STARTED'    => bb_date($onlinerow_guest[$i]['session_start'], 'd-M-Y H:i', false),
                    'LASTUPDATE' => bb_date($onlinerow_guest[$i]['session_time'], 'd-M-Y H:i', false),
                    'IP_ADDRESS' => $guest_ip,
                    'U_WHOIS_IP' => config()->get('whois_info').$guest_ip,
                ]);
            }
        }
    } else {
        $template->assign_vars([
            'USERS_ONLINE_HREF' => 'index.php?pane=right&users_online=1',
        ]);
    }
} else {
    // Generate frameset
    $template->assign_vars([
        'CONTENT_ENCODING'   => DEFAULT_CHARSET,
        'TPL_ADMIN_FRAMESET' => true,
    ]);
    send_no_cache_headers();
    print_page('index.tpl', 'admin', 'no_header');
}

print_page('index.tpl', 'admin');
