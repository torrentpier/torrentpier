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

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $userdata, $lang;

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

if (!($group_id = intval($this->request['group_id'])) || !($group_info = get_group_data($group_id))) {
    $this->ajax_die($lang['NO_GROUP_ID_SPECIFIED']);
}
if (!$mode = (string)$this->request['mode']) {
    $this->ajax_die('No mode specified');
}

$value = $this->request['value'] = (string)(isset($this->request['value'])) ? $this->request['value'] : 0;

if (!IS_ADMIN && $userdata['user_id'] != $group_info['group_moderator']) {
    $this->ajax_die($lang['ONLY_FOR_MOD']);
}

switch ($mode) {
    case 'group_name':
    case 'group_signature':
    case 'group_description':
        $value = htmlCHR($value, false, ENT_NOQUOTES);
        $this->response['new_value'] = $value;
        break;

    case 'group_type':
        $this->response['new_value'] = $value;
        break;

    case 'release_group':
        $this->response['new_value'] = $value;
        break;

    case 'delete_avatar':
        delete_avatar(GROUP_AVATAR_MASK . $group_id, $group_info['avatar_ext_id']);
        $value = 0;
        $mode = 'avatar_ext_id';
        $this->response['act'] = $value;
        break;

    default:
        $this->ajax_die('Unknown mode');
}

$value_sql = DB()->escape($value, true);
DB()->query("UPDATE " . BB_GROUPS . " SET $mode = $value_sql WHERE group_id = $group_id LIMIT 1");
