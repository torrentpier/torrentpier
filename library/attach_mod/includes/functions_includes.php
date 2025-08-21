<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

/**
 * Called from admin_users.php and admin_groups.php in order to process Quota Settings (admin/admin_users.php:admin/admin_groups.php)
 */
function attachment_quota_settings($admin_mode, $mode, $submit = false)
{
    global $template, $lang, $attach_config;
    $this_userdata = [];

    if ($attach_config['upload_dir'][0] == '/' || ($attach_config['upload_dir'][0] != '/' && $attach_config['upload_dir'][1] == ':')) {
        $upload_dir = $attach_config['upload_dir'];
    } else {
        $upload_dir = BB_ROOT . $attach_config['upload_dir'];
    }

    include ATTACH_DIR . '/includes/functions_selects.php';
    if (!function_exists("process_quota_settings")) {
        include ATTACH_DIR . '/includes/functions_admin.php';
    }

    $user_id = 0;

    if ($admin_mode == 'user') {
        // We overwrite submit here... to be sure
        $submit = isset($_POST['submit']);

        if (!$submit && $mode != 'save') {
            $user_id = get_var(POST_USERS_URL, 0);
            $u_name = get_var('username', '');

            if (!$user_id && !$u_name) {
                bb_die($lang['NO_USER_ID_SPECIFIED']);
            }

            if ($user_id) {
                $this_userdata['user_id'] = $user_id;
            } else {
                // Get userdata is handling the sanitizing of username
                $this_userdata = get_userdata($_POST['username'], true);
            }

            $user_id = (int)$this_userdata['user_id'];
        } else {
            $user_id = get_var('id', 0);

            if (!$user_id) {
                bb_die($lang['NO_USER_ID_SPECIFIED']);
            }
        }
    }

    if ($admin_mode == 'user' && !$submit && $mode != 'save') {
        // Show the contents
        $sql = 'SELECT quota_limit_id, quota_type FROM ' . BB_QUOTA . ' WHERE user_id = ' . (int)$user_id;

        if (!($result = DB()->sql_query($sql))) {
            bb_die('Unable to get quota settings #1');
        }

        $pm_quota = $upload_quota = 0;

        if ($row = DB()->sql_fetchrow($result)) {
            do {
                if ($row['quota_type'] == QUOTA_UPLOAD_LIMIT) {
                    $upload_quota = $row['quota_limit_id'];
                } elseif ($row['quota_type'] == QUOTA_PM_LIMIT) {
                    $pm_quota = $row['quota_limit_id'];
                }
            } while ($row = DB()->sql_fetchrow($result));
        } else {
            // Set Default Quota Limit
            $upload_quota = $attach_config['default_upload_quota'];
            $pm_quota = $attach_config['default_pm_quota'];
        }
        DB()->sql_freeresult($result);

        $template->assign_vars([
            'S_SELECT_UPLOAD_QUOTA' => quota_limit_select('user_upload_quota', $upload_quota),
            'S_SELECT_PM_QUOTA' => quota_limit_select('user_pm_quota', $pm_quota)
        ]);
    }

    if ($admin_mode == 'user' && $submit && @$_POST['delete_user']) {
        process_quota_settings($admin_mode, $user_id, QUOTA_UPLOAD_LIMIT, 0);
        process_quota_settings($admin_mode, $user_id, QUOTA_PM_LIMIT, 0);
    } elseif ($admin_mode == 'user' && $submit && $mode == 'save') {
        // Get the contents
        $upload_quota = get_var('user_upload_quota', 0);
        $pm_quota = get_var('user_pm_quota', 0);

        process_quota_settings($admin_mode, $user_id, QUOTA_UPLOAD_LIMIT, $upload_quota);
        process_quota_settings($admin_mode, $user_id, QUOTA_PM_LIMIT, $pm_quota);
    }

    if ($admin_mode == 'group' && $mode == 'newgroup') {
        return;
    }

    if ($admin_mode == 'group' && !$submit && isset($_POST['edit'])) {
        // Get group id again
        $group_id = get_var(POST_GROUPS_URL, 0);

        // Show the contents
        $sql = 'SELECT quota_limit_id, quota_type FROM ' . BB_QUOTA . ' WHERE group_id = ' . (int)$group_id;

        if (!($result = DB()->sql_query($sql))) {
            bb_die('Unable to get quota settings #2');
        }

        $pm_quota = $upload_quota = 0;

        if ($row = DB()->sql_fetchrow($result)) {
            do {
                if ($row['quota_type'] == QUOTA_UPLOAD_LIMIT) {
                    $upload_quota = $row['quota_limit_id'];
                } elseif ($row['quota_type'] == QUOTA_PM_LIMIT) {
                    $pm_quota = $row['quota_limit_id'];
                }
            } while ($row = DB()->sql_fetchrow($result));
        } else {
            // Set Default Quota Limit
            $upload_quota = $attach_config['default_upload_quota'];
            $pm_quota = $attach_config['default_pm_quota'];
        }
        DB()->sql_freeresult($result);

        $template->assign_vars([
            'S_SELECT_UPLOAD_QUOTA' => quota_limit_select('group_upload_quota', $upload_quota),
            'S_SELECT_PM_QUOTA' => quota_limit_select('group_pm_quota', $pm_quota)
        ]);
    }

    if ($admin_mode == 'group' && $submit && isset($_POST['group_delete'])) {
        $group_id = get_var(POST_GROUPS_URL, 0);

        process_quota_settings($admin_mode, $group_id, QUOTA_UPLOAD_LIMIT, 0);
        process_quota_settings($admin_mode, $group_id, QUOTA_PM_LIMIT, 0);
    } elseif ($admin_mode == 'group' && $submit) {
        $group_id = get_var(POST_GROUPS_URL, 0);

        // Get the contents
        $upload_quota = get_var('group_upload_quota', 0);
        $pm_quota = get_var('group_pm_quota', 0);

        process_quota_settings($admin_mode, $group_id, QUOTA_UPLOAD_LIMIT, $upload_quota);
        process_quota_settings($admin_mode, $group_id, QUOTA_PM_LIMIT, $pm_quota);
    }
}
