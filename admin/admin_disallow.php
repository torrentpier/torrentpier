<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    $module['USERS']['DISALLOW'] = basename(__FILE__);
    return;
}

require __DIR__ . '/pagestart.php';

$message = '';

if (isset($_POST['add_name'])) {
    $disallowed_user = isset($_POST['disallowed_user']) ? trim($_POST['disallowed_user']) : trim($_GET['disallowed_user']);

    if ($disallowed_user == '') {
        bb_die($lang['FIELDS_EMPTY']);
    }
    if (\TorrentPier\Validate::username($disallowed_user)) {
        $message = $lang['DISALLOWED_ALREADY'];
    } else {
        $sql = 'INSERT INTO ' . BB_DISALLOW . " (disallow_username) VALUES('" . DB()->escape($disallowed_user) . "')";
        $result = DB()->sql_query($sql);
        if (!$result) {
            bb_die('Could not add disallowed user');
        }
        $message = $lang['DISALLOW_SUCCESSFUL'];
    }

    $message .= '<br /><br />' . sprintf($lang['CLICK_RETURN_DISALLOWADMIN'], '<a href="admin_disallow.php">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>');

    bb_die($message);
} elseif (isset($_POST['delete_name'])) {
    $disallowed_id = isset($_POST['disallowed_id']) ? (int)$_POST['disallowed_id'] : (int)$_GET['disallowed_id'];

    if (!empty($disallowed_id)) {
        $sql = 'DELETE FROM ' . BB_DISALLOW . " WHERE disallow_id = $disallowed_id";
        $result = DB()->sql_query($sql);
        if (!$result) {
            bb_die('Could not removed disallowed user');
        }

        $message .= $lang['DISALLOWED_DELETED'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_DISALLOWADMIN'], '<a href="admin_disallow.php">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>');

        bb_die($message);
    }
}

/**
 * Grab the current list of disallowed usernames
 */
$sql = 'SELECT * FROM ' . BB_DISALLOW;
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
    for ($i = 0, $iMax = count($disallowed); $i < $iMax; $i++) {
        $disallow_select .= '<option value="' . $disallowed[$i]['disallow_id'] . '">' . $disallowed[$i]['disallow_username'] . '</option>';
    }
}

$disallow_select .= '</select>';

$template->assign_vars([
    'S_DISALLOW_SELECT' => $disallow_select,
    'S_FORM_ACTION' => 'admin_disallow.php',
]);

print_page('admin_disallow.tpl', 'admin');
