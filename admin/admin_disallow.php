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
    $module['USERS']['DISALLOW'] = basename(__FILE__);
    return;
}
require('./pagestart.php');

$message = '';

if (isset($_POST['add_name'])) {
    include(INC_DIR . 'functions_validate.php');

    $disallowed_user = (isset($_POST['disallowed_user'])) ? trim($_POST['disallowed_user']) : trim($_GET['disallowed_user']);

    if ($disallowed_user == '') {
        bb_die($lang['FIELDS_EMPTY']);
    }
    if (!validate_username($disallowed_user)) {
        $message = $lang['DISALLOWED_ALREADY'];
    } else {
        $sql = "INSERT INTO " . BB_DISALLOW . " (disallow_username) VALUES('" . DB()->escape($disallowed_user) . "')";
        $result = DB()->sql_query($sql);
        if (!$result) {
            bb_die('Could not add disallowed user');
        }
        $message = $lang['DISALLOW_SUCCESSFUL'];
    }

    $message .= '<br /><br />' . sprintf($lang['CLICK_RETURN_DISALLOWADMIN'], '<a href="admin_disallow.php">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>');

    bb_die($message);
} elseif (isset($_POST['delete_name'])) {
    $disallowed_id = (isset($_POST['disallowed_id'])) ? intval($_POST['disallowed_id']) : intval($_GET['disallowed_id']);

    $sql = "DELETE FROM " . BB_DISALLOW . " WHERE disallow_id = $disallowed_id";
    $result = DB()->sql_query($sql);
    if (!$result) {
        bb_die('Could not removed disallowed user');
    }

    $message .= $lang['DISALLOWED_DELETED'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_DISALLOWADMIN'], '<a href="admin_disallow.php">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>');

    bb_die($message);
}

/**
 * Grab the current list of disallowed usernames
 */
$sql = "SELECT * FROM " . BB_DISALLOW;
$result = DB()->sql_query($sql);
if (!$result) {
    bb_die('Could not get disallowed users');
}

$disallowed = DB()->sql_fetchrowset($result);

/**
 * Now generate the info for the template, which will be put out no matter what mode we are in
 */
$disallow_select = '<select name="disallowed_id">';

if (count($disallowed) <= 0) {
    $disallow_select .= '<option value="">' . $lang['NO_DISALLOWED'] . '</option>';
} else {
    for ($i = 0; $i < count($disallowed); $i++) {
        $disallow_select .= '<option value="' . $disallowed[$i]['disallow_id'] . '">' . $disallowed[$i]['disallow_username'] . '</option>';
    }
}

$disallow_select .= '</select>';

$template->assign_vars(array(
    'S_DISALLOW_SELECT' => $disallow_select,
    'S_FORM_ACTION' => 'admin_disallow.php',
));

print_page('admin_disallow.tpl', 'admin');
