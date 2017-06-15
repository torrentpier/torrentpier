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
    $module['USERS']['SEARCH'] = basename(__FILE__);
    return;
}
require __DIR__ . '/pagestart.php';

array_deep($_POST, 'trim');

require INC_DIR . '/functions_selects.php';

$total_sql = '';

if (!isset($_REQUEST['dosearch'])) {
    $sql = 'SELECT group_id, group_name
				FROM ' . BB_GROUPS . '
					WHERE group_single_user = 0
						ORDER BY group_name ASC';

    if (!$result = DB()->sql_query($sql)) {
        bb_die('Could not select group data #1');
    }

    $group_list = '';

    if (DB()->num_rows($result) != 0) {
        $template->assign_block_vars('groups_exist', array());

        while ($row = DB()->sql_fetchrow($result)) {
            $group_list .= '<option value="' . $row['group_id'] . '">' . strip_tags(htmlspecialchars($row['group_name'])) . '</option>';
        }
    }

    $sql = 'SELECT * FROM ' . BB_RANKS . ' WHERE rank_special = 1 ORDER BY rank_title';
    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not obtain ranks data');
    }
    $rank_select_box = '';
    if (DB()->num_rows($result) != 0) {
        $template->assign_block_vars('ranks_exist', array());
        while ($row = DB()->sql_fetchrow($result)) {
            $rank = $row['rank_title'];
            $rank_id = $row['rank_id'];
            $rank_select_box .= '<option value="' . $rank_id . '">' . $rank . '</option>';
        }
    }

    $language_list = language_select('', 'language_type');
    $timezone_list = tz_select('', 'timezone_type');

    $sql = 'SELECT f.forum_id, f.forum_name, f.forum_parent, c.cat_id, c.cat_title
				FROM ( ' . BB_FORUMS . ' AS f INNER JOIN ' . BB_CATEGORIES . ' AS c ON c.cat_id = f.cat_id )
				ORDER BY c.cat_order, f.forum_order ASC';

    if (!$result = DB()->sql_query($sql)) {
        bb_die('Could not select forum data');
    }

    $forums = array();

    if (DB()->num_rows($result) != 0) {
        $template->assign_block_vars('forums_exist', array());

        $last_cat_id = -1;
        $forums_list = '';

        while ($row = DB()->sql_fetchrow($result)) {
            if ($row['cat_id'] != $last_cat_id) {
                $forums_list .= '<optgroup label="' . htmlCHR($row['cat_title']) . '">';
                $last_cat_id = $row['cat_id'];
            }

            $forums_list .= '<option value="' . $row['forum_id'] . '">' . ($row['forum_parent'] ? HTML_SF_SPACER : '') . htmlCHR($row['forum_name']) . '</option>';
        }
    }

    $lastvisited = array(1, 7, 14, 30, 60, 120, 365, 500, 730, 1000);
    $lastvisited_list = '';

    foreach ($lastvisited as $days) {
        $lastvisited_list .= '<option value="' . $days . '">' . $days . ' ' . (($days > 1) ? $lang['DAYS'] : $lang['DAY']) . '</option>';
    }

    $template->assign_vars(array(
        'TPL_ADMIN_USER_SEARCH_MAIN' => true,

        'YEAR' => date('Y'),
        'MONTH' => date('m'),
        'DAY' => date('d'),
        'GROUP_LIST' => $group_list,
        'RANK_SELECT_BOX' => $rank_select_box,
        'LANGUAGE_LIST' => $language_list,
        'TIMEZONE_LIST' => $timezone_list,
        'FORUMS_LIST' => $forums_list,
        'LASTVISITED_LIST' => $lastvisited_list,

        'S_SEARCH_ACTION' => 'admin_user_search.php',
    ));
} else {
    $mode = '';

    // validate mode
    if (isset($_REQUEST['search_username'])) {
        $mode = 'search_username';
    } elseif (isset($_REQUEST['search_email'])) {
        $mode = 'search_email';
    } elseif (isset($_REQUEST['search_ip'])) {
        $mode = 'search_ip';
    } elseif (isset($_REQUEST['search_joindate'])) {
        $mode = 'search_joindate';
    } elseif (isset($_REQUEST['search_group'])) {
        $mode = 'search_group';
    } elseif (isset($_REQUEST['search_rank'])) {
        $mode = 'search_rank';
    } elseif (isset($_REQUEST['search_postcount'])) {
        $mode = 'search_postcount';
    } elseif (isset($_REQUEST['search_userfield'])) {
        $mode = 'search_userfield';
    } elseif (isset($_REQUEST['search_lastvisited'])) {
        $mode = 'search_lastvisited';
    } elseif (isset($_REQUEST['search_language'])) {
        $mode = 'search_language';
    } elseif (isset($_REQUEST['search_timezone'])) {
        $mode = 'search_timezone';
    } elseif (isset($_REQUEST['search_moderators'])) {
        $mode = 'search_moderators';
    } elseif (isset($_REQUEST['search_misc'])) {
        $mode = 'search_misc';
    }

    // validate fields (that they exist)
    switch ($mode) {
        case 'search_username':
            $username = $_REQUEST['username'];
            if (!$username) {
                bb_die($lang['SEARCH_INVALID_USERNAME']);
            }
            break;

        case 'search_email':
            $email = $_REQUEST['email'];
            if (!$email) {
                bb_die($lang['SEARCH_INVALID_EMAIL']);
            }
            break;

        case 'search_ip':
            $ip_address = $_REQUEST['ip_address'];
            if (!$ip_address) {
                bb_die($lang['SEARCH_INVALID_IP']);
            }
            break;

        case 'search_joindate':
            $date_type = $_REQUEST['date_type'];
            $date_day = $_REQUEST['date_day'];
            $date_month = $_REQUEST['date_month'];
            $date_year = $_REQUEST['date_year'];
            if (!($date_type || $date_day || $date_month || $date_year)) {
                bb_die($lang['SEARCH_INVALID_DATE']);
            }
            break;

        case 'search_group':
            $group_id = $_REQUEST['group_id'];
            if (!$group_id) {
                bb_die($lang['SEARCH_INVALID_GROUP']);
            }
            break;

        case 'search_rank':
            $rank_id = $_REQUEST['rank_id'];
            if (!$rank_id) {
                bb_die($lang['SEARCH_INVALID_RANK']);
            }
            break;

        case 'search_postcount':
            $postcount_type = $_REQUEST['postcount_type'];
            $postcount_value = $_REQUEST['postcount_value'];
            if (!$postcount_type || (!$postcount_value && $postcount_value != 0)) {
                bb_die($lang['SEARCH_INVALID_POSTCOUNT']);
            }
            break;

        case 'search_userfield':
            $userfield_type = $_REQUEST['userfield_type'];
            $userfield_value = $_REQUEST['userfield_value'];
            if (!$userfield_type || !$userfield_value) {
                bb_die($lang['SEARCH_INVALID_USERFIELD']);
            }
            break;

        case 'search_lastvisited':
            $lastvisited_days = $_REQUEST['lastvisited_days'];
            $lastvisited_type = $_REQUEST['lastvisited_type'];
            if (!$lastvisited_days || !$lastvisited_type) {
                bb_die($lang['SEARCH_INVALID_LASTVISITED']);
            }
            break;

        case 'search_language':
            $language_type = $_REQUEST['language_type'];
            if (!$language_type) {
                bb_die($lang['SEARCH_INVALID_LANGUAGE']);
            }
            break;

        case 'search_timezone':
            $timezone_type = $_REQUEST['timezone_type'];
            if (!$timezone_type && $timezone_type != 0) {
                bb_die($lang['SEARCH_INVALID_TIMEZONE']);
            }
            break;

        case 'search_moderators':
            $moderators_forum = $_REQUEST['moderators_forum'];
            if (!$moderators_forum) {
                bb_die($lang['SEARCH_INVALID_MODERATORS']);
            }
            break;

        case 'search_misc':
            $misc = $_REQUEST['misc'];
            if (!$misc) {
                bb_die($lang['SEARCH_INVALID']);
            }
            break;

        default:
            bb_die('Invalid mode');
    }

    $base_url = 'admin_user_search.php?dosearch=true';

    $select_sql = 'SELECT u.user_id, u.username, u.user_rank, u.user_email, u.user_posts, u.user_regdate, u.user_level, u.user_active, u.user_lastvisit FROM ' . BB_USERS . ' AS u';

    $lower_b = 'LOWER(';
    $lower_e = ')';

    // validate data & prepare sql
    switch ($mode) {
        case 'search_username':
            $base_url .= '&search_username=true&username=' . rawurlencode(stripslashes($username));

            $text = sprintf($lang['SEARCH_FOR_USERNAME'], strip_tags(htmlspecialchars(stripslashes($username))));

            $username = preg_replace('/\*/', '%', trim(strip_tags(strtolower($username))));

            if (false !== strpos($username, '%')) {
                $op = 'LIKE';
            } else {
                $op = '=';
            }

            if ($username == '') {
                bb_die($lang['SEARCH_INVALID_USERNAME']);
            }

            $total_sql .= 'SELECT COUNT(user_id) AS total FROM ' . BB_USERS . " WHERE {$lower_b}username{$lower_e} $op '" . DB()->escape($username) . "' AND user_id <> " . GUEST_UID;
            $select_sql .= "	WHERE {$lower_b}u.username{$lower_e} $op '" . DB()->escape($username) . "' AND u.user_id <> " . GUEST_UID;
            break;

        case 'search_email':
            $base_url .= '&search_email=true&email=' . rawurlencode(stripslashes($email));

            $text = sprintf($lang['SEARCH_FOR_EMAIL'], strip_tags(htmlspecialchars(stripslashes($email))));

            $email = preg_replace('/\*/', '%', trim(strip_tags(strtolower($email))));

            if (false !== strpos($email, '%')) {
                $op = 'LIKE';
            } else {
                $op = '=';
            }

            if ($email == '') {
                bb_die($lang['SEARCH_INVALID_EMAIL']);
            }

            $total_sql .= 'SELECT COUNT(user_id) AS total FROM ' . BB_USERS . " WHERE {$lower_b}user_email{$lower_e} $op '" . DB()->escape($email) . "' AND user_id <> " . GUEST_UID;
            $select_sql .= "	WHERE {$lower_b}u.user_email{$lower_e} $op '" . DB()->escape($email) . "' AND u.user_id <> " . GUEST_UID;
            break;

        case 'search_ip':
            $base_url .= '&search_ip=true&ip_address=' . rawurlencode(stripslashes($ip_address));

            $ip_address = trim($ip_address);

            $text = sprintf($lang['SEARCH_FOR_IP'], strip_tags(htmlspecialchars(stripslashes($ip_address))));

            unset($users);
            $users = [];

            if (Longman\IPTools\Ip::isValid($ip_address)) {
                $ip = encode_ip($ip_address);
                $users[] = $ip;
            } else {
                bb_die($lang['SEARCH_INVALID_IP']);
            }

            $ip_in_sql = $ip_like_sql = $ip_like_sql_flylast = $ip_like_sql_flyreg = '';

            foreach ($users as $address) {
                $ip_in_sql .= ($ip_in_sql == '') ? "'$address'" : ", '$address'";
            }

            $where_sql = '';
            $where_sql .= ($ip_in_sql != '') ? "poster_ip IN ($ip_in_sql)" : '';
            $where_sql .= ($ip_like_sql != '') ? ($where_sql != '') ? " OR $ip_like_sql" : "$ip_like_sql" : '';

            if (!$where_sql) {
                bb_die('invalid request');
            }

            // start search
            $no_result_search = false;
            $ip_users_sql = '';
            $sql = 'SELECT poster_id FROM ' . BB_POSTS . ' WHERE poster_id <> ' . GUEST_UID . " AND ($where_sql) GROUP BY poster_id";

            if (!$result = DB()->sql_query($sql)) {
                bb_die('Could not count users #1');
            }

            if (DB()->num_rows($result) == 0) {
                $no_result_search = true;
            } else {
                $total_pages['total'] = DB()->num_rows($result);
                $total_sql = null;
                $ip_users_sql = '';
                while ($row = DB()->sql_fetchrow($result)) {
                    $ip_users_sql .= ($ip_users_sql == '') ? $row['poster_id'] : ', ' . $row['poster_id'];
                }
            }
            $where_sql = '';
            $where_sql .= ($ip_in_sql != '') ? "user_last_ip IN ($ip_in_sql)" : '';
            $where_sql .= ($ip_like_sql_flylast != '') ? ($where_sql != '') ? " OR $ip_like_sql_flylast" : "$ip_like_sql_flylast" : '';
            $sql = 'SELECT user_id FROM ' . BB_USERS . ' WHERE user_id <> ' . GUEST_UID . " AND ($where_sql) GROUP BY user_id";
            if (!$result = DB()->sql_query($sql)) {
                bb_die('Could not count users #2');
            }
            if (DB()->num_rows($result) != 0) {
                if ($no_result_search == true) {
                    $no_result_search = false;
                }
                $total_pages['total'] = DB()->num_rows($result);
                $total_sql = null;
                while ($row = DB()->sql_fetchrow($result)) {
                    $ip_users_sql .= ($ip_users_sql == '') ? $row['user_id'] : ', ' . $row['user_id'];
                }
            }
            $where_sql = '';
            $where_sql .= ($ip_in_sql != '') ? "user_reg_ip IN ($ip_in_sql)" : '';
            $where_sql .= ($ip_like_sql_flyreg != '') ? ($where_sql != '') ? " OR $ip_like_sql_flyreg" : "$ip_like_sql_flyreg" : '';
            $sql = 'SELECT user_id FROM ' . BB_USERS . ' WHERE user_id <> ' . GUEST_UID . " AND ($where_sql) GROUP BY user_id";
            if (!$result = DB()->sql_query($sql)) {
                bb_die('Could not count users #3');
            }
            if (DB()->num_rows($result) != 0) {
                if ($no_result_search == true) {
                    $no_result_search = false;
                }
                $total_pages['total'] = DB()->num_rows($result);
                $total_sql = null;
                while ($row = DB()->sql_fetchrow($result)) {
                    $ip_users_sql .= ($ip_users_sql == '') ? $row['user_id'] : ', ' . $row['user_id'];
                }
            }
            if ($no_result_search == true) {
                bb_die($lang['SEARCH_NO_RESULTS']);
            }

            $select_sql .= "	WHERE u.user_id IN ($ip_users_sql)";
            break;

        case 'search_joindate':
            $base_url .= '&search_joindate=true&date_type=' . rawurlencode($date_type) . '&date_day=' . rawurlencode($date_day) . '&date_month=' . rawurlencode($date_month) . '&date_year=' . rawurlencode(stripslashes($date_year));

            $date_type = strtolower(trim($date_type));

            if ($date_type != 'before' && $date_type != 'after') {
                bb_die($lang['SEARCH_INVALID_DATE']);
            }

            $date_day = (int)$date_day;

            if (!preg_match('/^([1-9]|[0-2][0-9]|3[0-1])$/', $date_day)) {
                bb_die($lang['SEARCH_INVALID_DAY']);
            }

            $date_month = (int)$date_month;

            if (!preg_match('/^(0?[1-9]|1[0-2])$/', $date_month)) {
                bb_die($lang['SEARCH_INVALID_MONTH']);
            }

            $date_year = (int)$date_year;

            if (!preg_match('/^(20[0-9]{2}|19[0-9]{2})$/', $date_year)) {
                bb_die($lang['SEARCH_INVALID_YEAR']);
            }

            $text = sprintf($lang['SEARCH_FOR_DATE'], strip_tags(htmlspecialchars(stripslashes($date_type))), $date_year, $date_month, $date_day);

            $time = mktime(0, 0, 0, $date_month, $date_day, $date_year);

            if ($date_type == 'before') {
                $arg = '<';
            } else {
                $arg = '>';
            }

            $total_sql .= 'SELECT COUNT(user_id) AS total FROM ' . BB_USERS . " WHERE user_regdate $arg $time AND user_id <> " . GUEST_UID;
            $select_sql .= "	WHERE u.user_regdate $arg $time AND u.user_id <> " . GUEST_UID;
            break;

        case 'search_group':
            $group_id = (int)$group_id;

            $base_url .= '&search_group=true&group_id=' . rawurlencode($group_id);

            if (!$group_id) {
                bb_die($lang['SEARCH_INVALID_GROUP']);
            }

            $sql = 'SELECT group_name FROM ' . BB_GROUPS . " WHERE group_id = $group_id AND group_single_user = 0";

            if (!$result = DB()->sql_query($sql)) {
                bb_die('Could not select group data #2');
            }

            if (DB()->num_rows($result) == 0) {
                bb_die($lang['SEARCH_INVALID_GROUP']);
            }

            $group_name = DB()->sql_fetchrow($result);

            $text = sprintf($lang['SEARCH_FOR_GROUP'], strip_tags(htmlspecialchars($group_name['group_name'])));

            $total_sql .= 'SELECT COUNT(u.user_id) AS total
							FROM ' . BB_USERS . ' AS u, ' . BB_USER_GROUP . " AS ug
								WHERE u.user_id = ug.user_id
										AND ug.group_id = $group_id
										AND u.user_id <> " . GUEST_UID;

            $select_sql .= ', ' . BB_USER_GROUP . " AS ug
								WHERE u.user_id = ug.user_id
										AND ug.group_id = $group_id
										AND u.user_id <> " . GUEST_UID;
            break;

        case 'search_rank':
            $rank_id = (int)$rank_id;

            $base_url .= '&search_rank=true&rank_id=' . rawurlencode($rank_id);

            if (!$rank_id) {
                bb_die($lang['SEARCH_INVALID_RANK']);
            }

            $sql = 'SELECT rank_title FROM ' . BB_RANKS . " WHERE rank_id = $rank_id AND rank_special = 1";

            if (!$result = DB()->sql_query($sql)) {
                bb_die('Could not select rank data');
            }

            if (DB()->num_rows($result) == 0) {
                bb_die($lang['SEARCH_INVALID_RANK']);
            }

            $rank_title = DB()->sql_fetchrow($result);

            $text = sprintf($lang['SEARCH_FOR_RANK'], strip_tags(htmlspecialchars($rank_title['rank_title'])));

            $total_sql .= 'SELECT COUNT(user_id) AS total
							FROM ' . BB_USERS . "
								WHERE user_rank = $rank_id
									AND user_id <> " . GUEST_UID;

            $select_sql .= "	WHERE u.user_rank = $rank_id
									AND u.user_id <> " . GUEST_UID;
            break;

        case 'search_postcount':
            $postcount_type = strtolower(trim($postcount_type));
            $postcount_value = strtolower(trim($postcount_value));

            $base_url .= '&search_postcount=true&postcount_type=' . rawurlencode($postcount_type) . '&postcount_value=' . rawurlencode(stripslashes($postcount_value));

            switch ($postcount_type) {
                case 'greater':
                    $postcount_value = (int)$postcount_value;

                    $text = sprintf($lang['SEARCH_FOR_POSTCOUNT_GREATER'], $postcount_value);

                    $total_sql .= 'SELECT COUNT(user_id) AS total
									FROM ' . BB_USERS . "
										WHERE user_posts > $postcount_value
											AND user_id <> " . GUEST_UID;

                    $select_sql .= "	WHERE u.user_posts > $postcount_value
											AND u.user_id <> " . GUEST_UID;
                    break;
                case 'lesser':
                    $postcount_value = (int)$postcount_value;

                    $text = sprintf($lang['SEARCH_FOR_POSTCOUNT_LESSER'], $postcount_value);

                    $total_sql .= 'SELECT COUNT(user_id) AS total
									FROM ' . BB_USERS . "
										WHERE user_posts < $postcount_value
											AND user_id <> " . GUEST_UID;

                    $select_sql .= "	WHERE u.user_posts < $postcount_value
											AND u.user_id <> " . GUEST_UID;
                    break;
                case 'equals':
                    // looking for a -
                    if (false !== strpos($postcount_value, '-')) {
                        $range = preg_split('/[-\s]+/', $postcount_value);

                        $range_begin = (int)$range[0];
                        $range_end = (int)$range[1];

                        if ($range_begin > $range_end) {
                            bb_die($lang['SEARCH_INVALID_POSTCOUNT']);
                        }

                        $text = sprintf($lang['SEARCH_FOR_POSTCOUNT_RANGE'], $range_begin, $range_end);

                        $total_sql .= 'SELECT COUNT(user_id) AS total
										FROM ' . BB_USERS . "
											WHERE user_posts >= $range_begin
												AND user_posts <= $range_end
												AND user_id <> " . GUEST_UID;

                        $select_sql .= "	WHERE u.user_posts >= $range_begin
												AND u.user_posts <= $range_end
												AND u.user_id <> " . GUEST_UID;
                    } else {
                        $postcount_value = (int)$postcount_value;

                        $text = sprintf($lang['SEARCH_FOR_POSTCOUNT_EQUALS'], $postcount_value);

                        $total_sql .= 'SELECT COUNT(user_id) AS total
										FROM ' . BB_USERS . "
											WHERE user_posts = $postcount_value
												AND user_id <> " . GUEST_UID;

                        $select_sql .= "	WHERE u.user_posts = $postcount_value
												AND u.user_id <> " . GUEST_UID;
                    }
                    break;
                default:
                    bb_die($lang['SEARCH_INVALID']);
            }
            break;

        case 'search_userfield':
            $base_url .= '&search_userfield=true&userfield_type=' . rawurlencode($userfield_type) . '&userfield_value=' . rawurlencode(stripslashes($userfield_value));

            $text = strip_tags(htmlspecialchars(stripslashes($userfield_value)));

            $userfield_value = preg_replace('/\*/', '%', trim(strip_tags(strtolower($userfield_value))));

            if (false !== strpos($userfield_value, '%')) {
                $op = 'LIKE';
            } else {
                $op = '=';
            }

            if ($userfield_value == '') {
                bb_die($lang['SEARCH_INVALID_USERFIELD']);
            }

            $userfield_type = strtolower(trim($userfield_type));

            switch ($userfield_type) {
                case 'icq':
                    $text = sprintf($lang['SEARCH_FOR_USERFIELD_ICQ'], $text);
                    $field = 'user_icq';
                    break;
                case 'skype':
                    $text = sprintf($lang['SEARCH_FOR_USERFIELD_SKYPE'], $text);
                    $field = 'user_skype';
                    break;
                case 'twitter':
                    $text = sprintf($lang['SEARCH_FOR_USERFIELD_TWITTER'], $text);
                    $field = 'user_twitter';
                    break;
                case 'website':
                    $text = sprintf($lang['SEARCH_FOR_USERFIELD_WEBSITE'], $text);
                    $field = 'user_website';
                    break;
                case 'location':
                    $text = sprintf($lang['SEARCH_FOR_USERFIELD_LOCATION'], $text);
                    $field = 'user_from';
                    break;
                case 'interests':
                    $text = sprintf($lang['SEARCH_FOR_USERFIELD_INTERESTS'], $text);
                    $field = 'user_interests';
                    break;
                case 'occupation':
                    $text = sprintf($lang['SEARCH_FOR_USERFIELD_OCCUPATION'], $text);
                    $field = 'user_occ';
                    break;
                default:
                    bb_die($lang['SEARCH_INVALID']);
            }

            $total_sql .= 'SELECT COUNT(user_id) AS total
							FROM ' . BB_USERS . "
								WHERE {$lower_b}$field{$lower_e} $op '" . DB()->escape($userfield_value) . "'
									AND user_id <> " . GUEST_UID;

            $select_sql .= "	WHERE {$lower_b}u.$field{$lower_e} $op '" . DB()->escape($userfield_value) . "'
									AND u.user_id <> " . GUEST_UID;
            break;

        case 'search_lastvisited':
            $lastvisited_type = strtolower(trim($lastvisited_type));
            $lastvisited_days = (int)$lastvisited_days;

            $base_url .= '&search_lastvisited=true&lastvisited_type=' . rawurlencode(stripslashes($lastvisited_type)) . '&lastvisited_days=' . rawurlencode($lastvisited_days);

            $lastvisited_seconds = (TIMENOW - ((($lastvisited_days * 24) * 60) * 60));

            switch ($lastvisited_type) {
                case 'in':
                    $text = sprintf($lang['SEARCH_FOR_LASTVISITED_INTHELAST'], $lastvisited_days, (($lastvisited_days > 1) ? $lang['DAYS'] : $lang['DAY']));

                    $total_sql .= 'SELECT COUNT(user_id) AS total
									FROM ' . BB_USERS . "
										WHERE user_lastvisit >= $lastvisited_seconds
											AND user_id <> " . GUEST_UID;

                    $select_sql .= "	WHERE u.user_lastvisit >= $lastvisited_seconds
											AND u.user_id <> " . GUEST_UID;
                    break;
                case 'after':
                    $text = sprintf($lang['SEARCH_FOR_LASTVISITED_AFTERTHELAST'], $lastvisited_days, (($lastvisited_days > 1) ? $lang['DAYS'] : $lang['DAY']));

                    $total_sql .= 'SELECT COUNT(user_id) AS total
									FROM ' . BB_USERS . "
										WHERE user_lastvisit < $lastvisited_seconds
											AND user_id <> " . GUEST_UID;

                    $select_sql .= "	WHERE u.user_lastvisit < $lastvisited_seconds
											AND u.user_id <> " . GUEST_UID;

                    break;
                default:
                    bb_die($lang['SEARCH_INVALID_LASTVISITED']);
            }
            break;

        case 'search_language':
            $base_url .= '&search_language=true&language_type=' . rawurlencode(stripslashes($language_type));

            $language_type = strtolower(trim(stripslashes($language_type)));

            if ($language_type == '') {
                bb_die($lang['SEARCH_INVALID_LANGUAGE']);
            }

            $text = sprintf($lang['SEARCH_FOR_LANGUAGE'], strip_tags(htmlspecialchars($language_type)));

            $total_sql .= 'SELECT COUNT(user_id) AS total
							FROM ' . BB_USERS . "
								WHERE user_lang = '" . DB()->escape($language_type) . "'
									AND user_id <> " . GUEST_UID;

            $select_sql .= "	WHERE u.user_lang = '" . DB()->escape($language_type) . "'
									AND u.user_id <> " . GUEST_UID;
            break;

        case 'search_timezone':
            $base_url .= '&search_timezone=true&timezone_type=' . rawurlencode(stripslashes($timezone_type));
            $text = sprintf($lang['SEARCH_FOR_TIMEZONE'], strip_tags(htmlspecialchars(stripslashes($timezone_type))));

            $timezone_type = (int)$timezone_type;

            $total_sql .= 'SELECT COUNT(user_id) AS total
							FROM ' . BB_USERS . "
								WHERE user_timezone = $timezone_type
									AND user_id <> " . GUEST_UID;

            $select_sql .= "	WHERE u.user_timezone = $timezone_type
									AND u.user_id <> " . GUEST_UID;
            break;

        case 'search_moderators':
            $base_url .= '&search_moderators=true&moderators_forum=' . rawurlencode(stripslashes($moderators_forum));
            $moderators_forum = (int)$moderators_forum;

            $sql = 'SELECT forum_name FROM ' . BB_FORUMS . ' WHERE forum_id = ' . $moderators_forum;

            if (!$result = DB()->sql_query($sql)) {
                bb_die('Could not select forum data');
            }

            if (DB()->num_rows($result) == 0) {
                bb_die($lang['SEARCH_INVALID_MODERATORS']);
            }

            $forum_name = DB()->sql_fetchrow($result);

            $text = sprintf($lang['SEARCH_FOR_MODERATORS'], htmlCHR($forum_name['forum_name']));

            $total_sql .= 'SELECT COUNT(DISTINCT u.user_id) AS total
							FROM ' . BB_USERS . ' AS u, ' . BB_GROUPS . ' AS g, ' . BB_USER_GROUP . ' AS ug, ' . BB_AUTH_ACCESS . ' AS aa
								WHERE u.user_id = ug.user_id
									AND ug.group_id = g.group_id
									AND	g.group_id = aa.group_id
									AND aa.forum_id = ' . $moderators_forum . '
									AND aa.forum_perm & ' . BF_AUTH_MOD . '
									AND u.user_id <> ' . GUEST_UID;

            $select_sql .= ', ' . BB_GROUPS . ' AS g, ' . BB_USER_GROUP . ' AS ug, ' . BB_AUTH_ACCESS . ' AS aa
								WHERE u.user_id = ug.user_id
									AND ug.group_id = g.group_id
									AND	g.group_id = aa.group_id
									AND aa.forum_id = ' . $moderators_forum . '
									AND aa.forum_perm & ' . BF_AUTH_MOD . '
									AND u.user_id <> ' . GUEST_UID . '
								GROUP BY u.user_id, u.username, u.user_email, u.user_posts, u.user_regdate, u.user_level, u.user_active, u.user_lastvisit';
            break;

        case 'search_misc':
        default:
            $misc = strtolower(trim($misc));

            $base_url .= '&search_misc=true&misc=' . rawurlencode(stripslashes($misc));

            switch ($misc) {
                case 'admins':
                    $text = $lang['SEARCH_FOR_ADMINS'];

                    $total_sql .= 'SELECT COUNT(user_id) AS total
									FROM ' . BB_USERS . '
										WHERE user_level = ' . ADMIN . '
											AND user_id <> ' . GUEST_UID;

                    $select_sql .= '	WHERE u.user_level = ' . ADMIN . '
											AND u.user_id <> ' . GUEST_UID;
                    break;
                case 'mods':
                    $text = $lang['SEARCH_FOR_MODS'];

                    $total_sql .= 'SELECT COUNT(user_id) AS total
									FROM ' . BB_USERS . '
										WHERE user_level = ' . MOD . '
											AND user_id <> ' . GUEST_UID;

                    $select_sql .= '	WHERE u.user_level = ' . MOD . '
											AND u.user_id <> ' . GUEST_UID;
                    break;
                case 'banned':
                    $text = $lang['SEARCH_FOR_BANNED'];

                    $total_sql .= 'SELECT COUNT(u.user_id) AS total
									FROM ' . BB_USERS . ' AS u, ' . BB_BANLIST . ' AS b
										WHERE u.user_id = b.ban_userid
											AND u.user_id <> ' . GUEST_UID;

                    $select_sql .= ', ' . BB_BANLIST . ' AS b
										WHERE u.user_id = b.ban_userid
											AND u.user_id <> ' . GUEST_UID;

                    break;
                case 'disabled':
                    $text = $lang['SEARCH_FOR_DISABLED'];

                    $total_sql .= 'SELECT COUNT(user_id) AS total
									FROM ' . BB_USERS . '
										WHERE user_active = 0
											AND user_id <> ' . GUEST_UID;

                    $select_sql .= '	WHERE u.user_active = 0
											AND u.user_id <> ' . GUEST_UID;

                    break;
                default:
                    bb_die($lang['SEARCH_INVALID']);
            }
    }

    $select_sql .= '	ORDER BY ';

    if (isset($_GET['sort'])) {
        switch (strtolower($_GET['sort'])) {
            case 'regdate':
                $sort = 'regdate';
                $select_sql .= 'u.user_regdate';
                break;

            case 'posts':
                $sort = 'posts';
                $select_sql .= 'u.user_posts';
                break;

            case 'user_email':
                $sort = 'user_email';
                $select_sql .= 'u.user_email';
                break;

            case 'lastvisit':
                $sort = 'lastvisit';
                $select_sql .= 'u.user_lastvisit';
                break;

            case 'username':
                $sort = 'username';
                $select_sql .= 'u.username';
        }
    } else {
        $sort = 'username';
        $select_sql .= 'u.username';
    }

    if (isset($_GET['order'])) {
        $o_order = 'ASC';
        $order = 'DESC';
    } else {
        $o_order = 'DESC';
        $order = 'ASC';
    }

    $select_sql .= " $order";

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 0;

    if ($page < 1) {
        $page = 1;
    }

    if ($page == 1) {
        $offset = 0;
    } else {
        $offset = (($page - 1) * $bb_cfg['topics_per_page']);
    }

    $limit = "LIMIT $offset, " . $bb_cfg['topics_per_page'];

    $select_sql .= " $limit";

    if (null !== $total_sql) {
        if (!$result = DB()->sql_query($total_sql)) {
            bb_die('Could not count users');
        }

        $total_pages = DB()->sql_fetchrow($result);

        if ($total_pages['total'] == 0) {
            bb_die($lang['SEARCH_NO_RESULTS']);
        }
    }
    $num_pages = ceil($total_pages['total'] / $bb_cfg['topics_per_page']);

    $pagination = '';

    if ($page > 1) {
        $pagination .= '<a href="' . $base_url . '&sort=' . $sort . '&order=' . $order . '&page=' . ($page - 1) . '">' . $lang['PREVIOUS'] . '</a>';
    }
    if ($page < $num_pages) {
        $pagination .= ($pagination == '') ? '<a href="' . $base_url . '&sort=' . $sort . '&order=' . $order . '&page=' . ($page + 1) . '">' . $lang['NEXT'] . '</a>' : ' | <a href="' . $base_url . '&sort=' . $sort . '&order=' . $order . '&page=' . ($page + 1) . '">' . $lang['NEXT'] . '</a>';
    }
    if ($num_pages > 2) {
        $pagination .= '&nbsp;&nbsp;<input type="text" name="page" maxlength="5" size="2" class="post" />&nbsp;<input type="submit" name="submit" value="' . $lang['GO'] . '" class="post" />';
    }
    $template->assign_vars(array(
        'TPL_ADMIN_USER_SEARCH_RESULTS' => true,

        'PAGE_NUMBER' => sprintf($lang['PAGE_OF'], $page, $num_pages),
        'PAGINATION' => $pagination,
        'NEW_SEARCH' => sprintf($lang['SEARCH_USERS_NEW'], $text, $total_pages['total'], 'admin_user_search.php'),

        'U_USERNAME' => ($sort == 'username') ? "$base_url&sort=$sort&order=$o_order" : "$base_url&sort=username&order=$order",
        'U_EMAIL' => ($sort == 'user_email') ? "$base_url&sort=$sort&order=$o_order" : "$base_url&sort=user_email&order=$order",
        'U_POSTS' => ($sort == 'posts') ? "$base_url&sort=$sort&order=$o_order" : "$base_url&sort=posts&order=$order",
        'U_JOINDATE' => ($sort == 'regdate') ? "$base_url&sort=$sort&order=$o_order" : "$base_url&sort=regdate&order=$order",
        'U_LASTVISIT' => ($sort == 'lastvisit') ? "$base_url&sort=$sort&order=$o_order" : "$base_url&sort=lastvisit&order=$order",

        'S_POST_ACTION' => "$base_url&sort=$sort&order=$order"
    ));

    if (!$result = DB()->sql_query($select_sql)) {
        bb_die('Could not select user data');
    }

    $rowset = DB()->sql_fetchrowset($result);

    $users_sql = '';

    foreach ($rowset as $array) {
        $users_sql .= ($users_sql == '') ? $array['user_id'] : ', ' . $array['user_id'];
    }

    $sql = 'SELECT ban_userid AS user_id FROM ' . BB_BANLIST . " WHERE ban_userid IN ($users_sql)";

    if (!$result = DB()->sql_query($sql)) {
        bb_die('Could not select banned data');
    }

    unset($banned);

    $banned = array();

    while ($row = DB()->sql_fetchrow($result)) {
        $banned[$row['user_id']] = true;
    }

    for ($i = 0, $iMax = count($rowset); $i < $iMax; $i++) {
        $row_class = !($i % 2) ? 'row1' : 'row2';

        $template->assign_block_vars('userrow', array(
            'ROW_CLASS' => $row_class,
            'USER' => profile_url($rowset[$i]),
            'EMAIL' => $rowset[$i]['user_email'],
            'JOINDATE' => bb_date($rowset[$i]['user_regdate']),
            'LASTVISIT' => bb_date($rowset[$i]['user_lastvisit']),
            'POSTS' => $rowset[$i]['user_posts'],
            'BAN' => (!isset($banned[$rowset[$i]['user_id']])) ? $lang['NOT_BANNED'] : $lang['BANNED'],
            'ABLED' => $rowset[$i]['user_active'] ? $lang['ENABLED'] : $lang['DISABLED'],

            'U_VIEWPOSTS' => "../search.php?search_author=1&amp;uid={$rowset[$i]['user_id']}",
            'U_MANAGE' => '../profile.php?mode=editprofile&' . POST_USERS_URL . '=' . $rowset[$i]['user_id'] . '&admin=1',
            'U_PERMISSIONS' => 'admin_ug_auth.php?mode=user&' . POST_USERS_URL . '=' . $rowset[$i]['user_id'],
        ));
    }
}

print_page('admin_user_search.tpl', 'admin');
