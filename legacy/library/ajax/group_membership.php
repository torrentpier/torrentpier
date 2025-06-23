<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $lang, $user;

if (!$user_id = (int)$this->request['user_id'] or !$profiledata = get_userdata($user_id)) {
    $this->ajax_die($lang['NO_USER_ID_SPECIFIED']);
}

if (!$mode = (string)$this->request['mode']) {
    $this->ajax_die('invalid mode (empty)');
}

switch ($mode) {
    case 'get_group_list':
        $sql = "
					SELECT ug.user_pending, g.group_id, g.group_type, g.group_name, g.group_moderator, self.user_id AS can_view
					FROM       " . BB_USER_GROUP . " ug
					INNER JOIN " . BB_GROUPS . " g ON(g.group_id = ug.group_id AND g.group_single_user = 0)
					 LEFT JOIN " . BB_USER_GROUP . " self ON(self.group_id = g.group_id AND self.user_id = {$user->id} AND self.user_pending = 0)
					WHERE ug.user_id = $user_id
					ORDER BY g.group_name
				";
        $html = [];
        foreach (DB()->fetch_rowset($sql) as $row) {
            $class = ($row['user_pending']) ? 'med' : 'med bold';
            $class .= ($row['group_moderator'] == $user_id) ? ' colorMod' : '';
            $href = GROUP_URL . $row['group_id'];

            if (IS_ADMIN) {
                $href .= "&amp;" . POST_USERS_URL . "=$user_id";
                $link = '<a href="' . $href . '" class="' . $class . '" target="_blank">' . htmlCHR($row['group_name']) . '</a>';
                $html[] = $link;
            } else {
                // hidden group and the user himself is not a member of it
                if ($row['group_type'] == GROUP_HIDDEN && !$row['can_view']) {
                    continue;
                }
                if ($row['group_moderator'] == $user->id) {
                    // the user himself is the moderator of this group
                    $class .= ' selfMod';
                    $href .= "&amp;" . POST_USERS_URL . "=$user_id";
                }
                $link = '<a href="' . $href . '" class="' . $class . '" target="_blank">' . htmlCHR($row['group_name']) . '</a>';
                $html[] = $link;
            }
        }
        if ($html) {
            $this->response['group_list_html'] = '<ul><li>' . implode('</li><li>', $html) . '</li></ul>';
        } else {
            $this->response['group_list_html'] = $lang['GROUP_LIST_HIDDEN'];
        }
        break;

    default:
        $this->ajax_die("invalid mode: $mode");
}
