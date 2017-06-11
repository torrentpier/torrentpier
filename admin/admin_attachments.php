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
    $filename = basename(__FILE__);
    $module['ATTACHMENTS']['MANAGE'] = $filename . '?mode=manage';
    $module['ATTACHMENTS']['SPECIAL_CATEGORIES'] = $filename . '?mode=cats';
    $module['ATTACHMENTS']['QUOTA_LIMITS'] = $filename . '?mode=quota';
    return;
}
require __DIR__ . '/pagestart.php';

$error = false;

if (($attach_config['upload_dir'][0] == '/') || (($attach_config['upload_dir'][0] != '/') && ($attach_config['upload_dir'][1] == ':'))) {
    $upload_dir = $attach_config['upload_dir'];
} else {
    $upload_dir = '../' . $attach_config['upload_dir'];
}

include ATTACH_DIR . '/includes/functions_selects.php';

// Init Vars
$mode = request_var('mode', '');
$e_mode = request_var('e_mode', '');
$size = request_var('size', '');
$quota_size = request_var('quota_size', '');
$pm_size = request_var('pm_size', '');

$submit = isset($_POST['submit']) ? true : false;
$check_upload = isset($_POST['settings']) ? true : false;
$check_image_cat = isset($_POST['cat_settings']) ? true : false;
$search_imagick = isset($_POST['search_imagick']) ? true : false;

// Re-evaluate the Attachment Configuration
$sql = 'SELECT * FROM ' . BB_ATTACH_CONFIG;

if (!$result = DB()->sql_query($sql)) {
    bb_die('Could not find attachment config table #1');
}

while ($row = DB()->sql_fetchrow($result)) {
    $config_name = $row['config_name'];
    $config_value = $row['config_value'];

    $new_attach[$config_name] = get_var($config_name, trim($attach_config[$config_name]));

    if (!$size && !$submit && $config_name == 'max_filesize') {
        $size = ($attach_config[$config_name] >= 1048576) ? 'mb' : (($attach_config[$config_name] >= 1024) ? 'kb' : 'b');
    }

    if (!$quota_size && !$submit && $config_name == 'attachment_quota') {
        $quota_size = ($attach_config[$config_name] >= 1048576) ? 'mb' : (($attach_config[$config_name] >= 1024) ? 'kb' : 'b');
    }

    if (!$pm_size && !$submit && $config_name == 'max_filesize_pm') {
        $pm_size = ($attach_config[$config_name] >= 1048576) ? 'mb' : (($attach_config[$config_name] >= 1024) ? 'kb' : 'b');
    }

    if (!$submit && ($config_name == 'max_filesize' || $config_name == 'attachment_quota' || $config_name == 'max_filesize_pm')) {
        if ($new_attach[$config_name] >= 1048576) {
            $new_attach[$config_name] = round($new_attach[$config_name] / 1048576 * 100) / 100;
        } elseif ($new_attach[$config_name] >= 1024) {
            $new_attach[$config_name] = round($new_attach[$config_name] / 1024 * 100) / 100;
        }
    }

    if ($submit && ($mode == 'manage' || $mode == 'cats')) {
        if ($config_name == 'max_filesize') {
            $old = $new_attach[$config_name];
            $new_attach[$config_name] = ($size == 'kb') ? round($new_attach[$config_name] * 1024) : (($size == 'mb') ? round($new_attach[$config_name] * 1048576) : $new_attach[$config_name]);
        }

        if ($config_name == 'attachment_quota') {
            $old = $new_attach[$config_name];
            $new_attach[$config_name] = ($quota_size == 'kb') ? round($new_attach[$config_name] * 1024) : (($quota_size == 'mb') ? round($new_attach[$config_name] * 1048576) : $new_attach[$config_name]);
        }

        if ($config_name == 'max_filesize_pm') {
            $old = $new_attach[$config_name];
            $new_attach[$config_name] = ($pm_size == 'kb') ? round($new_attach[$config_name] * 1024) : (($pm_size == 'mb') ? round($new_attach[$config_name] * 1048576) : $new_attach[$config_name]);
        }

        if ($config_name == 'max_filesize') {
            $old_size = $attach_config[$config_name];
            $new_size = $new_attach[$config_name];

            if ($old_size != $new_size) {
                // See, if we have a similar value of old_size in Mime Groups. If so, update these values.
                $sql = 'UPDATE ' . BB_EXTENSION_GROUPS . '
					SET max_filesize = ' . (int)$new_size . '
					WHERE max_filesize = ' . (int)$old_size;

                if (!($result_2 = DB()->sql_query($sql))) {
                    bb_die('Could not update extension group information');
                }
            }

            $sql = 'UPDATE ' . BB_ATTACH_CONFIG . "
				SET	config_value = '" . attach_mod_sql_escape($new_attach[$config_name]) . "'
				WHERE config_name = '" . attach_mod_sql_escape($config_name) . "'";
        } else {
            $sql = 'UPDATE ' . BB_ATTACH_CONFIG . "
				SET	config_value = '" . attach_mod_sql_escape($new_attach[$config_name]) . "'
				WHERE config_name = '" . attach_mod_sql_escape($config_name) . "'";
        }

        if (!DB()->sql_query($sql)) {
            bb_die('Failed to update attachment configuration for ' . $config_name);
        }

        if ($config_name == 'max_filesize' || $config_name == 'attachment_quota' || $config_name == 'max_filesize_pm') {
            $new_attach[$config_name] = $old;
        }
    }
}
DB()->sql_freeresult($result);

// Clear cached config
CACHE('bb_cache')->rm('attach_config');

$select_size_mode = size_select('size', $size);
$select_quota_size_mode = size_select('quota_size', $quota_size);
$select_pm_size_mode = size_select('pm_size', $pm_size);

// Search Imagick
if ($search_imagick) {
    $imagick = '';

    if (preg_match('/convert/i', $imagick)) {
        return true;
    } elseif ($imagick != 'none') {
        if (!preg_match('/WIN/i', PHP_OS)) {
            $retval = @exec('whereis convert');
            $paths = explode(' ', $retval);

            if (is_array($paths)) {
                for ($i = 0, $iMax = count($paths); $i < $iMax; $i++) {
                    $path = basename($paths[$i]);

                    if ($path == 'convert') {
                        $imagick = $paths[$i];
                    }
                }
            }
        } elseif (preg_match('/WIN/i', PHP_OS)) {
            $path = 'c:/imagemagick/convert.exe';

            if (!@file_exists(amod_realpath($path))) {
                $imagick = $path;
            }
        }
    }

    if (!@file_exists(amod_realpath(trim($imagick)))) {
        $new_attach['img_imagick'] = trim($imagick);
    } else {
        $new_attach['img_imagick'] = '';
    }
}

// Check Settings
if ($check_upload) {
    // Some tests...
    $attach_config = array();

    $sql = 'SELECT * FROM ' . BB_ATTACH_CONFIG;

    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not find attachment config table #2');
    }

    $row = DB()->sql_fetchrowset($result);
    $num_rows = DB()->num_rows($result);
    DB()->sql_freeresult($result);

    for ($i = 0; $i < $num_rows; $i++) {
        $attach_config[$row[$i]['config_name']] = trim($row[$i]['config_value']);
    }

    if ($attach_config['upload_dir'][0] == '/' || ($attach_config['upload_dir'][0] != '/' && $attach_config['upload_dir'][1] == ':')) {
        $upload_dir = $attach_config['upload_dir'];
    } else {
        $upload_dir = BB_ROOT . $attach_config['upload_dir'];
    }

    $error = false;

    // Does the target directory exist, is it a directory and writeable
    if (!@file_exists(amod_realpath($upload_dir))) {
        $error = true;
        $error_msg = sprintf($lang['DIRECTORY_DOES_NOT_EXIST'], $attach_config['upload_dir']) . '<br />';
    }

    if (!$error && !is_dir($upload_dir)) {
        $error = true;
        $error_msg = sprintf($lang['DIRECTORY_IS_NOT_A_DIR'], $attach_config['upload_dir']) . '<br />';
    }

    if (!$error) {
        if (!($fp = @fopen($upload_dir . '/0_000000.000', 'wb'))) {
            $error = true;
            $error_msg = sprintf($lang['DIRECTORY_NOT_WRITEABLE'], $attach_config['upload_dir']) . '<br />';
        } else {
            @fclose($fp);
            unlink_attach($upload_dir . '/0_000000.000');
        }
    }

    if (!$error) {
        bb_die($lang['TEST_SETTINGS_SUCCESSFUL'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_ATTACH_CONFIG'], '<a href="admin_attachments.php?mode=manage">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
    }
}

// Management
if ($submit && $mode == 'manage') {
    if (!$error) {
        bb_die($lang['ATTACH_CONFIG_UPDATED'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_ATTACH_CONFIG'], '<a href="admin_attachments.php?mode=manage">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
    }
}

if ($mode == 'manage') {
    $template->assign_vars(array(
        'TPL_ATTACH_MANAGE' => true,
        'S_ATTACH_ACTION' => 'admin_attachments.php?mode=manage',
        'S_FILESIZE' => $select_size_mode,
        'S_FILESIZE_QUOTA' => $select_quota_size_mode,
        'S_FILESIZE_PM' => $select_pm_size_mode,
        'S_DEFAULT_UPLOAD_LIMIT' => default_quota_limit_select('default_upload_quota', (int)trim($new_attach['default_upload_quota'])),
        'S_DEFAULT_PM_LIMIT' => default_quota_limit_select('default_pm_quota', (int)trim($new_attach['default_pm_quota'])),

        'UPLOAD_DIR' => $new_attach['upload_dir'],
        'ATTACHMENT_IMG_PATH' => $new_attach['upload_img'],
        'TOPIC_ICON' => $new_attach['topic_icon'],
        'MAX_FILESIZE' => $new_attach['max_filesize'],
        'ATTACHMENT_QUOTA' => $new_attach['attachment_quota'],
        'MAX_FILESIZE_PM' => $new_attach['max_filesize_pm'],
        'MAX_ATTACHMENTS' => $new_attach['max_attachments'],
        'MAX_ATTACHMENTS_PM' => $new_attach['max_attachments_pm'],
        'DISABLE_MOD_YES' => $new_attach['disable_mod'] !== '0' ? 'checked="checked"' : '',
        'DISABLE_MOD_NO' => $new_attach['disable_mod'] === '0' ? 'checked="checked"' : '',
        'PM_ATTACH_YES' => $new_attach['allow_pm_attach'] !== '0' ? 'checked="checked"' : '',
        'PM_ATTACH_NO' => $new_attach['allow_pm_attach'] === '0' ? 'checked="checked"' : '',
        'DISPLAY_ORDER_ASC' => $new_attach['display_order'] !== '0' ? 'checked="checked"' : '',
        'DISPLAY_ORDER_DESC' => $new_attach['display_order'] === '0' ? 'checked="checked"' : '',
    ));
}

if ($submit && $mode == 'cats') {
    if (!$error) {
        bb_die($lang['ATTACH_CONFIG_UPDATED'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_ATTACH_CONFIG'], '<a href="admin_attachments.php?mode=cats">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
    }
}

if ($mode == 'cats') {
    $s_assigned_group_images = $lang['NONE'];

    $sql = 'SELECT group_name, cat_id FROM ' . BB_EXTENSION_GROUPS . ' WHERE cat_id > 0 ORDER BY cat_id';

    $s_assigned_group_images = array();

    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not get group names from ' . BB_EXTENSION_GROUPS);
    }

    $row = DB()->sql_fetchrowset($result);
    DB()->sql_freeresult($result);

    for ($i = 0, $iMax = count($row); $i < $iMax; $i++) {
        if ($row[$i]['cat_id'] == IMAGE_CAT) {
            $s_assigned_group_images[] = $row[$i]['group_name'];
        }
    }

    $display_inlined_yes = ($new_attach['img_display_inlined'] != '0') ? 'checked="checked"' : '';
    $display_inlined_no = ($new_attach['img_display_inlined'] == '0') ? 'checked="checked"' : '';

    $create_thumbnail_yes = ($new_attach['img_create_thumbnail'] != '0') ? 'checked="checked"' : '';
    $create_thumbnail_no = ($new_attach['img_create_thumbnail'] == '0') ? 'checked="checked"' : '';

    $use_gd2_yes = ($new_attach['use_gd2'] != '0') ? 'checked="checked"' : '';
    $use_gd2_no = ($new_attach['use_gd2'] == '0') ? 'checked="checked"' : '';

    // Check Thumbnail Support
    if (!is_imagick() && !@extension_loaded('gd')) {
        $new_attach['img_create_thumbnail'] = '0';
    } else {
        $template->assign_block_vars('switch_thumbnail_support', array());
    }

    $template->assign_vars(array(
        'TPL_ATTACH_SPECIAL_CATEGORIES' => true,
        'IMAGE_MAX_HEIGHT' => $new_attach['img_max_height'],
        'IMAGE_MAX_WIDTH' => $new_attach['img_max_width'],
        'IMAGE_LINK_HEIGHT' => $new_attach['img_link_height'],
        'IMAGE_LINK_WIDTH' => $new_attach['img_link_width'],
        'IMAGE_MIN_THUMB_FILESIZE' => $new_attach['img_min_thumb_filesize'],
        'IMAGE_IMAGICK_PATH' => $new_attach['img_imagick'],
        'DISPLAY_INLINED_YES' => $display_inlined_yes,
        'DISPLAY_INLINED_NO' => $display_inlined_no,
        'CREATE_THUMBNAIL_YES' => $create_thumbnail_yes,
        'CREATE_THUMBNAIL_NO' => $create_thumbnail_no,
        'USE_GD2_YES' => $use_gd2_yes,
        'USE_GD2_NO' => $use_gd2_no,
        'S_ASSIGNED_GROUP_IMAGES' => implode(', ', $s_assigned_group_images),
        'S_ATTACH_ACTION' => 'admin_attachments.php?mode=cats',
    ));
}

// Check Cat Settings
if ($check_image_cat) {
    // Some tests...
    $attach_config = array();

    $sql = 'SELECT * FROM ' . BB_ATTACH_CONFIG;

    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not find attachment config table #3');
    }

    $row = DB()->sql_fetchrowset($result);
    $num_rows = DB()->num_rows($result);
    DB()->sql_freeresult($result);

    for ($i = 0; $i < $num_rows; $i++) {
        $attach_config[$row[$i]['config_name']] = trim($row[$i]['config_value']);
    }

    if ($attach_config['upload_dir'][0] == '/' || ($attach_config['upload_dir'][0] != '/' && $attach_config['upload_dir'][1] == ':')) {
        $upload_dir = $attach_config['upload_dir'];
    } else {
        $upload_dir = BB_ROOT . $attach_config['upload_dir'];
    }

    $upload_dir = $upload_dir . '/' . THUMB_DIR;

    $error = false;

    // Does the target directory exist, is it a directory and writeable
    if (!@file_exists(amod_realpath($upload_dir))) {
        mkdir($upload_dir, 0755);
        @chmod($upload_dir, 0777);

        if (!@file_exists(amod_realpath($upload_dir))) {
            $error = true;
            $error_msg = sprintf($lang['DIRECTORY_DOES_NOT_EXIST'], $upload_dir) . '<br />';
        }
    }

    if (!$error && !is_dir($upload_dir)) {
        $error = true;
        $error_msg = sprintf($lang['DIRECTORY_IS_NOT_A_DIR'], $upload_dir) . '<br />';
    }

    if (!$error) {
        if (!($fp = @fopen($upload_dir . '/0_000000.000', 'wb'))) {
            $error = true;
            $error_msg = sprintf($lang['DIRECTORY_NOT_WRITEABLE'], $upload_dir) . '<br />';
        } else {
            @fclose($fp);
            @unlink($upload_dir . '/0_000000.000');
        }
    }

    if (!$error) {
        bb_die($lang['TEST_SETTINGS_SUCCESSFUL'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_ATTACH_CONFIG'], '<a href="admin_attachments.php?mode=cats">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
    }
}

// Quota Limit Settings
if ($submit && $mode == 'quota') {
    // Change Quota Limit
    $quota_change_list = get_var('quota_change_list', array(0));
    $quota_desc_list = get_var('quota_desc_list', array(''));
    $filesize_list = get_var('max_filesize_list', array(0));
    $size_select_list = get_var('size_select_list', array(''));

    $allowed_list = array();

    for ($i = 0, $iMax = count($quota_change_list); $i < $iMax; $i++) {
        $filesize_list[$i] = ($size_select_list[$i] == 'kb') ? round($filesize_list[$i] * 1024) : (($size_select_list[$i] == 'mb') ? round($filesize_list[$i] * 1048576) : $filesize_list[$i]);

        $sql = 'UPDATE ' . BB_QUOTA_LIMITS . "
			SET quota_desc = '" . attach_mod_sql_escape($quota_desc_list[$i]) . "', quota_limit = " . (int)$filesize_list[$i] . '
			WHERE quota_limit_id = ' . (int)$quota_change_list[$i];

        if (!DB()->sql_query($sql)) {
            bb_die('Could not update quota limits');
        }
    }

    // Delete Quota Limits
    $quota_id_list = get_var('quota_id_list', array(0));

    $quota_id_sql = implode(', ', $quota_id_list);

    if ($quota_id_sql != '') {
        $sql = 'DELETE FROM ' . BB_QUOTA_LIMITS . ' WHERE quota_limit_id IN (' . $quota_id_sql . ')';

        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not delete quota limits');
        }

        // Delete Quotas linked to this setting
        $sql = 'DELETE FROM ' . BB_QUOTA . ' WHERE quota_limit_id IN (' . $quota_id_sql . ')';

        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not delete quotas');
        }
    }

    // Add Quota Limit ?
    $quota_desc = get_var('quota_description', '');
    $filesize = get_var('add_max_filesize', 0);
    $size_select = get_var('add_size_select', '');
    $add = isset($_POST['add_quota_check']) ? true : false;

    if ($quota_desc != '' && $add) {
        // check Quota Description
        $sql = 'SELECT quota_desc FROM ' . BB_QUOTA_LIMITS;

        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not query quota limits table');
        }

        $row = DB()->sql_fetchrowset($result);
        $num_rows = DB()->num_rows($result);
        DB()->sql_freeresult($result);

        if ($num_rows > 0) {
            for ($i = 0; $i < $num_rows; $i++) {
                if ($row[$i]['quota_desc'] == $quota_desc) {
                    $error = true;
                    if (isset($error_msg)) {
                        $error_msg .= '<br />';
                    }
                    $error_msg .= sprintf($lang['QUOTA_LIMIT_EXIST'], $extension_group);
                }
            }
        }

        if (!$error) {
            $filesize = ($size_select == 'kb') ? round($filesize * 1024) : (($size_select == 'mb') ? round($filesize * 1048576) : $filesize);

            $sql = 'INSERT INTO ' . BB_QUOTA_LIMITS . " (quota_desc, quota_limit)
			VALUES ('" . attach_mod_sql_escape($quota_desc) . "', " . (int)$filesize . ')';

            if (!DB()->sql_query($sql)) {
                bb_die('Could not add quota limit');
            }
        }
    }

    if (!$error) {
        bb_die($lang['ATTACH_CONFIG_UPDATED'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_ATTACH_CONFIG'], '<a href="admin_attachments.php?mode=quota">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
    }
}

if ($mode == 'quota') {
    $max_add_filesize = $attach_config['max_filesize'];
    $size = ($max_add_filesize >= 1048576) ? 'mb' : (($max_add_filesize >= 1024) ? 'kb' : 'b');

    if ($max_add_filesize >= 1048576) {
        $max_add_filesize = round($max_add_filesize / 1048576 * 100) / 100;
    } elseif ($max_add_filesize >= 1024) {
        $max_add_filesize = round($max_add_filesize / 1024 * 100) / 100;
    }

    $template->assign_vars(array(
        'TPL_ATTACH_QUOTA' => true,
        'MAX_FILESIZE' => $max_add_filesize,
        'S_FILESIZE' => size_select('add_size_select', $size),
        'S_ATTACH_ACTION' => 'admin_attachments.php?mode=quota',
    ));

    $sql = 'SELECT * FROM ' . BB_QUOTA_LIMITS . ' ORDER BY quota_limit DESC';

    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not get quota limits #1');
    }

    $rows = DB()->sql_fetchrowset($result);
    DB()->sql_freeresult($result);

    for ($i = 0, $iMax = count($rows); $i < $iMax; $i++) {
        $size_format = ($rows[$i]['quota_limit'] >= 1048576) ? 'mb' : (($rows[$i]['quota_limit'] >= 1024) ? 'kb' : 'b');

        if ($rows[$i]['quota_limit'] >= 1048576) {
            $rows[$i]['quota_limit'] = round($rows[$i]['quota_limit'] / 1048576 * 100) / 100;
        } elseif ($rows[$i]['quota_limit'] >= 1024) {
            $rows[$i]['quota_limit'] = round($rows[$i]['quota_limit'] / 1024 * 100) / 100;
        }

        $template->assign_block_vars('limit_row', array(
            'QUOTA_NAME' => $rows[$i]['quota_desc'],
            'QUOTA_ID' => $rows[$i]['quota_limit_id'],
            'S_FILESIZE' => size_select('size_select_list[]', $size_format),
            'U_VIEW' => "admin_attachments.php?mode=$mode&amp;e_mode=view_quota&amp;quota_id=" . $rows[$i]['quota_limit_id'],
            'MAX_FILESIZE' => $rows[$i]['quota_limit'],
        ));
    }
}

if ($mode == 'quota' && $e_mode == 'view_quota') {
    $quota_id = get_var('quota_id', 0);

    if (!$quota_id) {
        bb_die('Invalid call');
    }

    $template->assign_block_vars('switch_quota_limit_desc', array());

    $sql = 'SELECT * FROM ' . BB_QUOTA_LIMITS . ' WHERE quota_limit_id = ' . (int)$quota_id . ' LIMIT 1';

    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not get quota limits #2');
    }

    $row = DB()->sql_fetchrow($result);
    DB()->sql_freeresult($result);

    $template->assign_vars(array(
        'L_QUOTA_LIMIT_DESC' => $row['quota_desc'],
    ));

    $sql = 'SELECT q.user_id, u.username, q.quota_type
		FROM ' . BB_QUOTA . ' q, ' . BB_USERS . ' u
		WHERE q.quota_limit_id = ' . (int)$quota_id . '
			AND q.user_id <> 0
			AND q.user_id = u.user_id';

    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not get quota limits #3');
    }

    $rows = DB()->sql_fetchrowset($result);
    $num_rows = DB()->num_rows($result);
    DB()->sql_freeresult($result);

    for ($i = 0; $i < $num_rows; $i++) {
        if ($rows[$i]['quota_type'] == QUOTA_UPLOAD_LIMIT) {
            $template->assign_block_vars('users_upload_row', array(
                'USER_ID' => $rows[$i]['user_id'],
                'USERNAME' => $rows[$i]['username'],
            ));
        } elseif ($rows[$i]['quota_type'] == QUOTA_PM_LIMIT) {
            $template->assign_block_vars('users_pm_row', array(
                'USER_ID' => $rows[$i]['user_id'],
                'USERNAME' => $rows[$i]['username'],
            ));
        }
    }

    $sql = 'SELECT q.group_id, g.group_name, q.quota_type
		FROM ' . BB_QUOTA . ' q, ' . BB_GROUPS . ' g
		WHERE q.quota_limit_id = ' . (int)$quota_id . '
			AND q.group_id <> 0
			AND q.group_id = g.group_id';

    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not get quota limits #4');
    }

    $rows = DB()->sql_fetchrowset($result);
    $num_rows = DB()->num_rows($result);
    DB()->sql_freeresult($result);

    for ($i = 0; $i < $num_rows; $i++) {
        if ($rows[$i]['quota_type'] == QUOTA_UPLOAD_LIMIT) {
            $template->assign_block_vars('groups_upload_row', array(
                'GROUP_ID' => $rows[$i]['group_id'],
                'GROUPNAME' => $rows[$i]['group_name'],
            ));
        } elseif ($rows[$i]['quota_type'] == QUOTA_PM_LIMIT) {
            $template->assign_block_vars('groups_pm_row', array(
                'GROUP_ID' => $rows[$i]['group_id'],
                'GROUPNAME' => $rows[$i]['group_name'],
            ));
        }
    }
}

if ($error) {
    $template->assign_vars(array('ERROR_MESSAGE' => $error_msg));
}

print_page('admin_attachments.tpl', 'admin');
