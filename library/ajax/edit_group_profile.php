<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2017 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $userdata, $lang;

if (!$group_id = (int)$this->request['group_id'] or !$group_info = get_group_data($group_id)) {
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

$value_sql = OLD_DB()->escape($value, true);
OLD_DB()->query("UPDATE " . BB_GROUPS . " SET $mode = $value_sql WHERE group_id = $group_id");
