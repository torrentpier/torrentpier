<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    $filename = basename(__FILE__);
    $module['ATTACHMENTS']['MANAGE'] = $filename . '?mode=manage';
    $module['ATTACHMENTS']['SPECIAL_CATEGORIES'] = $filename . '?mode=cats';
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
$pm_size = request_var('pm_size', '');

$submit = isset($_POST['submit']);
$check_upload = isset($_POST['settings']);
$check_image_cat = isset($_POST['cat_settings']);

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

    if (!$pm_size && !$submit && $config_name == 'max_filesize_pm') {
        $pm_size = ($attach_config[$config_name] >= 1048576) ? 'mb' : (($attach_config[$config_name] >= 1024) ? 'kb' : 'b');
    }

    if (!$submit && ($config_name == 'max_filesize' || $config_name == 'max_filesize_pm')) {
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
				SET	config_value = '" . DB()->escape($new_attach[$config_name]) . "'
				WHERE config_name = '" . DB()->escape($config_name) . "'";
        } else {
            $sql = 'UPDATE ' . BB_ATTACH_CONFIG . "
				SET	config_value = '" . DB()->escape($new_attach[$config_name]) . "'
				WHERE config_name = '" . DB()->escape($config_name) . "'";
        }

        if (!DB()->sql_query($sql)) {
            bb_die('Failed to update attachment configuration for ' . $config_name);
        }

        if ($config_name == 'max_filesize' || $config_name == 'max_filesize_pm') {
            $new_attach[$config_name] = $old;
        }
    }
}
DB()->sql_freeresult($result);

// Clear cached config
CACHE('bb_cache')->rm('attach_config');

$select_size_mode = size_select('size', $size);
$select_pm_size_mode = size_select('pm_size', $pm_size);

// Check Settings
if ($check_upload) {
    // Some tests...
    $attach_config = [];

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
    if (!@file_exists(realpath($upload_dir))) {
        $error = true;
        $error_msg = sprintf($lang['DIRECTORY_DOES_NOT_EXIST'], $attach_config['upload_dir']) . '<br />';
    }

    if (!$error && !is_dir($upload_dir)) {
        $error = true;
        $error_msg = sprintf($lang['DIRECTORY_IS_NOT_A_DIR'], $attach_config['upload_dir']) . '<br />';
    }

    if (!$error) {
        if (!($fp = @fopen($upload_dir . '/0_000000.000', 'wb+'))) {
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
        'S_FILESIZE_PM' => $select_pm_size_mode,
        'UPLOAD_DIR' => $new_attach['upload_dir'],
        'ATTACHMENT_IMG_PATH' => $new_attach['upload_img'],
        'TOPIC_ICON' => $new_attach['topic_icon'],
        'MAX_FILESIZE' => $new_attach['max_filesize'],
        'MAX_FILESIZE_PM' => $new_attach['max_filesize_pm'],
        'MAX_ATTACHMENTS' => $new_attach['max_attachments'],
        'MAX_ATTACHMENTS_PM' => $new_attach['max_attachments_pm'],
        'DISABLE_MOD_YES' => $new_attach['disable_mod'] !== '0' ? 'checked' : '',
        'DISABLE_MOD_NO' => $new_attach['disable_mod'] === '0' ? 'checked' : '',
        'PM_ATTACH_YES' => $new_attach['allow_pm_attach'] !== '0' ? 'checked' : '',
        'PM_ATTACH_NO' => $new_attach['allow_pm_attach'] === '0' ? 'checked' : '',
        'DISPLAY_ORDER_ASC' => $new_attach['display_order'] !== '0' ? 'checked' : '',
        'DISPLAY_ORDER_DESC' => $new_attach['display_order'] === '0' ? 'checked' : '',
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

    $s_assigned_group_images = [];

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

    $display_inlined_yes = ($new_attach['img_display_inlined'] != '0') ? 'checked' : '';
    $display_inlined_no = ($new_attach['img_display_inlined'] == '0') ? 'checked' : '';

    $create_thumbnail_yes = ($new_attach['img_create_thumbnail'] != '0') ? 'checked' : '';
    $create_thumbnail_no = ($new_attach['img_create_thumbnail'] == '0') ? 'checked' : '';

    // Check Thumbnail Support
    if (!extension_loaded('gd')) {
        $new_attach['img_create_thumbnail'] = '0';
    } else {
        $template->assign_block_vars('switch_thumbnail_support', []);
    }

    $template->assign_vars(array(
        'TPL_ATTACH_SPECIAL_CATEGORIES' => true,
        'IMAGE_MAX_HEIGHT' => $new_attach['img_max_height'],
        'IMAGE_MAX_WIDTH' => $new_attach['img_max_width'],
        'IMAGE_LINK_HEIGHT' => $new_attach['img_link_height'],
        'IMAGE_LINK_WIDTH' => $new_attach['img_link_width'],
        'IMAGE_MIN_THUMB_FILESIZE' => $new_attach['img_min_thumb_filesize'],
        'DISPLAY_INLINED_YES' => $display_inlined_yes,
        'DISPLAY_INLINED_NO' => $display_inlined_no,
        'CREATE_THUMBNAIL_YES' => $create_thumbnail_yes,
        'CREATE_THUMBNAIL_NO' => $create_thumbnail_no,
        'S_ASSIGNED_GROUP_IMAGES' => implode(', ', $s_assigned_group_images),
        'S_ATTACH_ACTION' => 'admin_attachments.php?mode=cats',
    ));
}

// Check Cat Settings
if ($check_image_cat) {
    // Some tests...
    $attach_config = [];

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
    if (!@file_exists(realpath($upload_dir))) {
        if (!bb_mkdir($upload_dir) && !is_dir($upload_dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $upload_dir));
        }

        if (!@file_exists(realpath($upload_dir))) {
            $error = true;
            $error_msg = sprintf($lang['DIRECTORY_DOES_NOT_EXIST'], $upload_dir) . '<br />';
        }
    }

    if (!$error && !is_dir($upload_dir)) {
        $error = true;
        $error_msg = sprintf($lang['DIRECTORY_IS_NOT_A_DIR'], $upload_dir) . '<br />';
    }

    if (!$error) {
        if (!($fp = @fopen($upload_dir . '/0_000000.000', 'wb+'))) {
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

if ($error) {
    $template->assign_vars(array('ERROR_MESSAGE' => $error_msg));
}

print_page('admin_attachments.tpl', 'admin');
