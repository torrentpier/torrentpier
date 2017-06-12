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
    $module['ATTACHMENTS']['EXTENSION_CONTROL'] = $filename . '?mode=extensions';
    $module['ATTACHMENTS']['EXTENSION_GROUP_MANAGE'] = $filename . '?mode=groups';
    return;
}

require __DIR__ . '/pagestart.php';

function update_attach_extensions()
{
    $GLOBALS['datastore']->update('attach_extensions');
}

register_shutdown_function('update_attach_extensions');

if (($attach_config['upload_dir'][0] == '/') || (($attach_config['upload_dir'][0] != '/') && ($attach_config['upload_dir'][1] == ':'))) {
    $upload_dir = $attach_config['upload_dir'];
} else {
    $upload_dir = BB_ROOT . $attach_config['upload_dir'];
}

include ATTACH_DIR . '/includes/functions_selects.php';

// Init Vars
$types_download = array(INLINE_LINK, PHYSICAL_LINK);
$modes_download = array('inline', 'physical');

$types_category = array(IMAGE_CAT);
$modes_category = array($lang['CATEGORY_IMAGES']);

$size = get_var('size', '');
$mode = get_var('mode', '');
$e_mode = get_var('e_mode', '');

$error = false;
$add_forum = isset($_POST['add_forum']) ? true : false;
$delete_forum = isset($_POST['del_forum']) ? true : false;
$submit = isset($_POST['submit']) ? true : false;

// Get Attachment Config
$attach_config = array();

$sql = 'SELECT * FROM ' . BB_ATTACH_CONFIG;

if (!($result = DB()->sql_query($sql))) {
    bb_die('Could not query attachment information');
}

while ($row = DB()->sql_fetchrow($result)) {
    $attach_config[$row['config_name']] = trim($row['config_value']);
}
DB()->sql_freeresult($result);

// Extension Management
if ($submit && $mode == 'extensions') {
    // Change Extensions ?
    $extension_change_list = get_var('extension_change_list', array(0));
    $extension_explain_list = get_var('extension_explain_list', array(''));
    $group_select_list = get_var('group_select', array(0));

    // Generate correct Change List
    $extensions = array();

    for ($i = 0, $iMax = count($extension_change_list); $i < $iMax; $i++) {
        $extensions['_' . $extension_change_list[$i]]['comment'] = $extension_explain_list[$i];
        $extensions['_' . $extension_change_list[$i]]['group_id'] = (int)$group_select_list[$i];
    }

    $sql = 'SELECT * FROM ' . BB_EXTENSIONS . ' ORDER BY ext_id';
    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not get extension informations #1');
    }

    $num_rows = DB()->num_rows($result);
    $extension_row = DB()->sql_fetchrowset($result);
    DB()->sql_freeresult($result);

    if ($num_rows > 0) {
        for ($i = 0, $iMax = count($extension_row); $i < $iMax; $i++) {
            if ($extension_row[$i]['comment'] != $extensions['_' . $extension_row[$i]['ext_id']]['comment'] || (int)$extension_row[$i]['group_id'] != (int)$extensions['_' . $extension_row[$i]['ext_id']]['group_id']) {
                $sql_ary = array(
                    'comment' => (string)$extensions['_' . $extension_row[$i]['ext_id']]['comment'],
                    'group_id' => (int)$extensions['_' . $extension_row[$i]['ext_id']]['group_id']
                );

                $sql = 'UPDATE ' . BB_EXTENSIONS . ' SET ' . attach_mod_sql_build_array('UPDATE', $sql_ary) . '
					WHERE ext_id = ' . (int)$extension_row[$i]['ext_id'];

                if (!DB()->sql_query($sql)) {
                    bb_die('Could not update extension informations');
                }
            }
        }
    }

    // Delete Extension?
    $extension_id_list = get_var('extension_id_list', array(0));

    $extension_id_sql = implode(', ', $extension_id_list);

    if ($extension_id_sql != '') {
        $sql = 'DELETE FROM ' . BB_EXTENSIONS . ' WHERE ext_id IN (' . $extension_id_sql . ')';

        if (!$result = DB()->sql_query($sql)) {
            bb_die('Could not delete extensions');
        }
    }

    // Add Extension ?
    $extension = get_var('add_extension', '');
    $extension_explain = get_var('add_extension_explain', '');
    $extension_group = get_var('add_group_select', 0);
    $add = isset($_POST['add_extension_check']) ? true : false;

    if ($extension != '' && $add) {
        $template->assign_vars(array(
            'ADD_EXTENSION' => $extension,
            'ADD_EXTENSION_EXPLAIN' => $extension_explain,
        ));

        if (!$error) {
            // check extension
            $sql = 'SELECT extension FROM ' . BB_EXTENSIONS;

            if (!($result = DB()->sql_query($sql))) {
                bb_die('Could not query extensions');
            }

            $row = DB()->sql_fetchrowset($result);
            $num_rows = DB()->num_rows($result);
            DB()->sql_freeresult($result);

            if ($num_rows > 0) {
                for ($i = 0; $i < $num_rows; $i++) {
                    if (strtolower(trim($row[$i]['extension'])) == strtolower(trim($extension))) {
                        $error = true;
                        if (isset($error_msg)) {
                            $error_msg .= '<br />';
                        }
                        $error_msg .= sprintf($lang['EXTENSION_EXIST'], strtolower(trim($extension)));
                    }
                }
            }

            if (!$error) {
                $sql_ary = array(
                    'group_id' => (int)$extension_group,
                    'extension' => (string)strtolower($extension),
                    'comment' => (string)$extension_explain
                );

                $sql = 'INSERT INTO ' . BB_EXTENSIONS . ' ' . attach_mod_sql_build_array('INSERT', $sql_ary);

                if (!DB()->sql_query($sql)) {
                    bb_die('Could not add extension');
                }
            }
        }
    }

    if (!$error) {
        bb_die($lang['ATTACH_CONFIG_UPDATED'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_ATTACH_CONFIG'], '<a href="admin_extensions.php?mode=extensions">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
    }
}

if ($mode == 'extensions') {
    // Extensions
    $template->assign_vars(array(
        'TPL_ATTACH_EXTENSIONS' => true,
        'S_CANCEL_ACTION' => 'admin_extensions.php?mode=extensions',
        'S_ATTACH_ACTION' => 'admin_extensions.php?mode=extensions',
    ));

    if ($submit) {
        $template->assign_vars(array(
                'S_ADD_GROUP_SELECT' => group_select('add_group_select', $extension_group))
        );
    } else {
        $template->assign_vars(array(
                'S_ADD_GROUP_SELECT' => group_select('add_group_select'))
        );
    }

    $sql = 'SELECT * FROM ' . BB_EXTENSIONS . ' ORDER BY group_id';

    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not get extension informations #2');
    }

    $extension_row = DB()->sql_fetchrowset($result);
    $num_extension_row = DB()->num_rows($result);
    DB()->sql_freeresult($result);

    if ($num_extension_row > 0) {
        $extension_row = sort_multi_array($extension_row, 'group_name', 'ASC');

        for ($i = 0; $i < $num_extension_row; $i++) {
            if ($submit) {
                $template->assign_block_vars('extension_row', array(
                        'EXT_ID' => $extension_row[$i]['ext_id'],
                        'EXTENSION' => $extension_row[$i]['extension'],
                        'EXTENSION_EXPLAIN' => $extension_explain_list[$i],
                        'S_GROUP_SELECT' => group_select('group_select[]', $group_select_list[$i]))
                );
            } else {
                $template->assign_block_vars('extension_row', array(
                        'EXT_ID' => $extension_row[$i]['ext_id'],
                        'EXTENSION' => $extension_row[$i]['extension'],
                        'EXTENSION_EXPLAIN' => $extension_row[$i]['comment'],
                        'S_GROUP_SELECT' => group_select('group_select[]', $extension_row[$i]['group_id']))
                );
            }
        }
    }
}

// Extension Groups
if ($submit && $mode == 'groups') {
    // Change Extension Groups ?
    $group_change_list = get_var('group_change_list', array(0));
    $extension_group_list = get_var('extension_group_list', array(''));
    $group_allowed_list = get_var('allowed_list', array(0));
    $download_mode_list = get_var('download_mode_list', array(0));
    $category_list = get_var('category_list', array(0));
    $upload_icon_list = get_var('upload_icon_list', array(''));
    $filesize_list = get_var('max_filesize_list', array(0));
    $size_select_list = get_var('size_select_list', array(''));

    $allowed_list = array();

    for ($i = 0, $iMax = count($group_allowed_list); $i < $iMax; $i++) {
        for ($j = 0, $jMax = count($group_change_list); $j < $jMax; $j++) {
            if ($group_allowed_list[$i] == $group_change_list[$j]) {
                $allowed_list[$j] = 1;
            }
        }
    }

    for ($i = 0, $iMax = count($group_change_list); $i < $iMax; $i++) {
        $allowed = isset($allowed_list[$i]) ? 1 : 0;

        $filesize_list[$i] = ($size_select_list[$i] == 'kb') ? round($filesize_list[$i] * 1024) : (($size_select_list[$i] == 'mb') ? round($filesize_list[$i] * 1048576) : $filesize_list[$i]);

        $sql_ary = array(
            'group_name' => (string)$extension_group_list[$i],
            'cat_id' => (int)$category_list[$i],
            'allow_group' => (int)$allowed,
            'download_mode' => (int)$download_mode_list[$i],
            'upload_icon' => (string)$upload_icon_list[$i],
            'max_filesize' => (int)$filesize_list[$i]
        );

        $sql = 'UPDATE ' . BB_EXTENSION_GROUPS . ' SET ' . attach_mod_sql_build_array('UPDATE', $sql_ary) . '
			WHERE group_id = ' . (int)$group_change_list[$i];

        if (!DB()->sql_query($sql)) {
            bb_die('Could not update extension groups informations');
        }
    }

    // Delete Extension Groups
    $group_id_list = get_var('group_id_list', array(0));

    $group_id_sql = implode(', ', $group_id_list);

    if ($group_id_sql != '') {
        $sql = 'DELETE
		FROM ' . BB_EXTENSION_GROUPS . '
		WHERE group_id IN (' . $group_id_sql . ')';

        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not delete extension groups');
        }

        // Set corresponding Extensions to a pending Group
        $sql = 'UPDATE ' . BB_EXTENSIONS . '
			SET group_id = 0
			WHERE group_id IN (' . $group_id_sql . ')';

        if (!$result = DB()->sql_query($sql)) {
            bb_die('Could not assign extensions to pending group');
        }
    }

    // Add Extensions?
    $extension_group = get_var('add_extension_group', '');
    $download_mode = get_var('add_download_mode', 0);
    $cat_id = get_var('add_category', 0);
    $upload_icon = get_var('add_upload_icon', '');
    $filesize = get_var('add_max_filesize', 0);
    $size_select = get_var('add_size_select', '');

    $is_allowed = isset($_POST['add_allowed']) ? 1 : 0;
    $add = isset($_POST['add_extension_group_check']) ? true : false;

    if ($extension_group != '' && $add) {
        // check Extension Group
        $sql = 'SELECT group_name FROM ' . BB_EXTENSION_GROUPS;

        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not query extension groups table');
        }

        $row = DB()->sql_fetchrowset($result);
        $num_rows = DB()->num_rows($result);
        DB()->sql_freeresult($result);

        if ($num_rows > 0) {
            for ($i = 0; $i < $num_rows; $i++) {
                if ($row[$i]['group_name'] == $extension_group) {
                    $error = true;
                    if (isset($error_msg)) {
                        $error_msg .= '<br />';
                    }
                    $error_msg .= sprintf($lang['EXTENSION_GROUP_EXIST'], $extension_group);
                }
            }
        }

        if (!$error) {
            $filesize = ($size_select == 'kb') ? round($filesize * 1024) : (($size_select == 'mb') ? round($filesize * 1048576) : $filesize);

            $sql_ary = array(
                'group_name' => (string)$extension_group,
                'cat_id' => (int)$cat_id,
                'allow_group' => (int)$is_allowed,
                'download_mode' => (int)$download_mode,
                'upload_icon' => (string)$upload_icon,
                'max_filesize' => (int)$filesize,
                'forum_permissions' => ''
            );

            $sql = 'INSERT INTO ' . BB_EXTENSION_GROUPS . ' ' . attach_mod_sql_build_array('INSERT', $sql_ary);

            if (!DB()->sql_query($sql)) {
                bb_die('Could not add extension group');
            }
        }
    }

    if (!$error) {
        bb_die($lang['ATTACH_CONFIG_UPDATED'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_ATTACH_CONFIG'], '<a href="admin_extensions.php?mode=groups">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
    }
}

if ($mode == 'groups') {
    // Extension Groups
    if (!$size && !$submit) {
        $max_add_filesize = $attach_config['max_filesize'];

        $size = ($max_add_filesize >= 1048576) ? 'mb' : (($max_add_filesize >= 1024) ? 'kb' : 'b');
    }

    if ($max_add_filesize >= 1048576) {
        $max_add_filesize = round($max_add_filesize / 1048576 * 100) / 100;
    } elseif ($max_add_filesize >= 1024) {
        $max_add_filesize = round($max_add_filesize / 1024 * 100) / 100;
    }

    $viewgroup = get_var(POST_GROUPS_URL, 0);

    $template->assign_vars(array(
        'TPL_ATTACH_EXTENSION_GROUPS' => true,
        'ADD_GROUP_NAME' => isset($extension_group) ? $extension_group : '',
        'MAX_FILESIZE' => $max_add_filesize,
        'S_FILESIZE' => size_select('add_size_select', $size),
        'S_ADD_DOWNLOAD_MODE' => download_select('add_download_mode'),
        'S_SELECT_CAT' => category_select('add_category'),
        'S_CANCEL_ACTION' => 'admin_extensions.php?mode=groups',
        'S_ATTACH_ACTION' => 'admin_extensions.php?mode=groups',
    ));

    $sql = 'SELECT * FROM ' . BB_EXTENSION_GROUPS;

    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not get extension group informations');
    }

    $extension_group = DB()->sql_fetchrowset($result);
    $num_extension_group = DB()->num_rows($result);
    DB()->sql_freeresult($result);

    for ($i = 0; $i < $num_extension_group; $i++) {
        // Format the filesize
        if (!$extension_group[$i]['max_filesize']) {
            $extension_group[$i]['max_filesize'] = $attach_config['max_filesize'];
        }

        $size_format = ($extension_group[$i]['max_filesize'] >= 1048576) ? 'mb' : (($extension_group[$i]['max_filesize'] >= 1024) ? 'kb' : 'b');

        if ($extension_group[$i]['max_filesize'] >= 1048576) {
            $extension_group[$i]['max_filesize'] = round($extension_group[$i]['max_filesize'] / 1048576 * 100) / 100;
        } elseif ($extension_group[$i]['max_filesize'] >= 1024) {
            $extension_group[$i]['max_filesize'] = round($extension_group[$i]['max_filesize'] / 1024 * 100) / 100;
        }

        $s_allowed = ($extension_group[$i]['allow_group'] == 1) ? 'checked="checked"' : '';

        $template->assign_block_vars('grouprow', array(
            'GROUP_ID' => $extension_group[$i]['group_id'],
            'EXTENSION_GROUP' => $extension_group[$i]['group_name'],
            'UPLOAD_ICON' => $extension_group[$i]['upload_icon'],

            'S_ALLOW_SELECTED' => $s_allowed,
            'S_SELECT_CAT' => category_select('category_list[]', $extension_group[$i]['group_id']),
            'S_DOWNLOAD_MODE' => download_select('download_mode_list[]', $extension_group[$i]['group_id']),
            'S_FILESIZE' => size_select('size_select_list[]', $size_format),

            'MAX_FILESIZE' => $extension_group[$i]['max_filesize'],
            'CAT_BOX' => ($viewgroup == $extension_group[$i]['group_id']) ? '-' : '+',
            'U_VIEWGROUP' => ($viewgroup == $extension_group[$i]['group_id']) ? 'admin_extensions.php?mode=groups' : 'admin_extensions.php?mode=groups&' . POST_GROUPS_URL . '=' . $extension_group[$i]['group_id'],
            'U_FORUM_PERMISSIONS' => "admin_extensions.php?mode=$mode&amp;e_mode=perm&amp;e_group=" . $extension_group[$i]['group_id'],
        ));

        if ($viewgroup && $viewgroup == $extension_group[$i]['group_id']) {
            $sql = 'SELECT comment, extension FROM ' . BB_EXTENSIONS . ' WHERE group_id = ' . (int)$viewgroup;

            if (!$result = DB()->sql_query($sql)) {
                bb_die('Could not get extension informations #3');
            }

            $extension = DB()->sql_fetchrowset($result);
            $num_extension = DB()->num_rows($result);
            DB()->sql_freeresult($result);

            for ($j = 0; $j < $num_extension; $j++) {
                $template->assign_block_vars('grouprow.extensionrow', array(
                        'EXPLANATION' => $extension[$j]['comment'],
                        'EXTENSION' => $extension[$j]['extension'])
                );
            }
        }
    }
}

if ($e_mode == 'perm') {
    $group = get_var('e_group', 0);

    if (isset($_POST['close_perm'])) {
        $e_mode = '';
    }
}

// Add Forums
if ($add_forum && $e_mode == 'perm' && $group) {
    $add_forums_list = get_var('entries', array(0));
    $add_all_forums = false;

    for ($i = 0, $iMax = count($add_forums_list); $i < $iMax; $i++) {
        if ($add_forums_list[$i] == 0) {
            $add_all_forums = true;
        }
    }

    // If we add ALL FORUMS, we are able to overwrite the Permissions
    if ($add_all_forums) {
        $sql = 'UPDATE ' . BB_EXTENSION_GROUPS . " SET forum_permissions = '' WHERE group_id = " . (int)$group;
        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not update permissions #1');
        }
    }

    // Else we have to add Permissions
    if (!$add_all_forums) {
        $sql = 'SELECT forum_permissions
			FROM ' . BB_EXTENSION_GROUPS . '
			WHERE group_id = ' . (int)$group . '
			LIMIT 1';

        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not get group permissions from ' . BB_EXTENSION_GROUPS);
        }

        $row = DB()->sql_fetchrow($result);
        DB()->sql_freeresult($result);

        if (trim($row['forum_permissions']) == '') {
            $auth_p = array();
        } else {
            $auth_p = auth_unpack($row['forum_permissions']);
        }

        // Generate array for Auth_Pack, do not add doubled forums
        for ($i = 0, $iMax = count($add_forums_list); $i < $iMax; $i++) {
            if (!in_array($add_forums_list[$i], $auth_p)) {
                $auth_p[] = $add_forums_list[$i];
            }
        }

        $auth_bitstream = auth_pack($auth_p);

        $sql = 'UPDATE ' . BB_EXTENSION_GROUPS . " SET forum_permissions = '" . attach_mod_sql_escape($auth_bitstream) . "' WHERE group_id = " . (int)$group;

        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not update permissions #2');
        }
    }
}

// Delete Forums
if ($delete_forum && $e_mode == 'perm' && $group) {
    $delete_forums_list = get_var('entries', array(0));

    // Get the current Forums
    $sql = 'SELECT forum_permissions
		FROM ' . BB_EXTENSION_GROUPS . '
		WHERE group_id = ' . (int)$group . '
		LIMIT 1';

    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not get group permissions from ' . BB_EXTENSION_GROUPS);
    }

    $row = DB()->sql_fetchrow($result);
    DB()->sql_freeresult($result);

    $auth_p2 = auth_unpack(trim($row['forum_permissions']));
    $auth_p = array();

    // Generate array for Auth_Pack, delete the chosen ones
    for ($i = 0, $iMax = count($auth_p2); $i < $iMax; $i++) {
        if (!in_array($auth_p2[$i], $delete_forums_list)) {
            $auth_p[] = $auth_p2[$i];
        }
    }

    $auth_bitstream = (count($auth_p) > 0) ? auth_pack($auth_p) : '';

    $sql = 'UPDATE ' . BB_EXTENSION_GROUPS . " SET forum_permissions = '" . attach_mod_sql_escape($auth_bitstream) . "' WHERE group_id = " . (int)$group;

    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not update permissions #3');
    }
}

// Display the Group Permissions Box for configuring it
if ($e_mode == 'perm' && $group) {
    $sql = 'SELECT group_name, forum_permissions
		FROM ' . BB_EXTENSION_GROUPS . '
		WHERE group_id = ' . (int)$group . '
		LIMIT 1';

    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not get group name from ' . BB_EXTENSION_GROUPS);
    }

    $row = DB()->sql_fetchrow($result);
    DB()->sql_freeresult($result);

    $group_name = $row['group_name'];
    $allowed_forums = trim($row['forum_permissions']);

    $forum_perm = array();

    if ($allowed_forums == '') {
        $forum_perm[0]['forum_id'] = 0;
        $forum_perm[0]['forum_name'] = $lang['PERM_ALL_FORUMS'];
    } else {
        $forum_p = array();
        $act_id = 0;
        $forum_p = auth_unpack($allowed_forums);
        $sql = 'SELECT forum_id, forum_name FROM ' . BB_FORUMS . ' WHERE forum_id IN (' . implode(', ', $forum_p) . ')';
        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not get forum names');
        }

        while ($row = DB()->sql_fetchrow($result)) {
            $forum_perm[$act_id]['forum_id'] = $row['forum_id'];
            $forum_perm[$act_id]['forum_name'] = $row['forum_name'];
            $act_id++;
        }
    }

    for ($i = 0, $iMax = count($forum_perm); $i < $iMax; $i++) {
        $template->assign_block_vars('allow_option_values', array(
                'VALUE' => $forum_perm[$i]['forum_id'],
                'OPTION' => htmlCHR($forum_perm[$i]['forum_name']))
        );
    }

    $template->assign_vars(array(
        'TPL_ATTACH_EXTENSION_GROUPS_PERMISSIONS' => true,
        'L_GROUP_PERMISSIONS_TITLE' => sprintf($lang['GROUP_PERMISSIONS_TITLE_ADMIN'], trim($group_name)),
        'A_PERM_ACTION' => "admin_extensions.php?mode=groups&amp;e_mode=perm&amp;e_group=$group",
    ));

    $forum_option_values = array(0 => $lang['PERM_ALL_FORUMS']);

    $sql = 'SELECT forum_id, forum_name FROM ' . BB_FORUMS;

    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not get forums #1');
    }

    while ($row = DB()->sql_fetchrow($result)) {
        $forum_option_values[(int)$row['forum_id']] = $row['forum_name'];
    }
    DB()->sql_freeresult($result);

    foreach ($forum_option_values as $value => $option) {
        $template->assign_block_vars('forum_option_values', array(
                'VALUE' => $value,
                'OPTION' => htmlCHR($option))
        );
    }

    $empty_perm_forums = array();

    $sql = 'SELECT forum_id, forum_name FROM ' . BB_FORUMS . ' WHERE auth_attachments < ' . AUTH_ADMIN;

    if (!($f_result = DB()->sql_query($sql))) {
        bb_die('Could not get forums #2');
    }

    while ($row = DB()->sql_fetchrow($f_result)) {
        $forum_id = $row['forum_id'];

        $sql = 'SELECT forum_permissions
		FROM ' . BB_EXTENSION_GROUPS . '
		WHERE allow_group = 1
		ORDER BY group_name ASC';

        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not query extension groups');
        }

        $rows = DB()->sql_fetchrowset($result);
        $num_rows = DB()->num_rows($result);
        DB()->sql_freeresult($result);

        $found_forum = false;

        for ($i = 0; $i < $num_rows; $i++) {
            $allowed_forums = auth_unpack(trim($rows[$i]['forum_permissions']));
            if (in_array($forum_id, $allowed_forums) || trim($rows[$i]['forum_permissions']) == '') {
                $found_forum = true;
                break;
            }
        }

        if (!$found_forum) {
            $empty_perm_forums[$forum_id] = $row['forum_name'];
        }
    }
    DB()->sql_freeresult($f_result);

    $message = '';

    foreach ($empty_perm_forums as $forum_id => $forum_name) {
        $message .= ($message == '') ? $forum_name : '<br />' . $forum_name;
    }

    if (count($empty_perm_forums) > 0) {
        $template->assign_vars(array('ERROR_MESSAGE' => $lang['NOTE_ADMIN_EMPTY_GROUP_PERMISSIONS'] . $message));
    }
}

if ($error) {
    $template->assign_vars(array('ERROR_MESSAGE' => $error_msg));
}

print_page('admin_extensions.tpl', 'admin');
