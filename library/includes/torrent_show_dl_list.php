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

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

$show_canceled_in_count_mode = false;
$title_date_format = 'Y-m-d';
$dl_list_sql_limit = 300;     // DL-List overall limit
$max_dl_users_before_overflow = 100;     // for each dl-status
$dl_users_overflow_div_height = '120px';
$dl_users_div_style_normal = 'padding: 0px;';
$dl_users_div_style_overflow = "padding: 6px; height: $dl_users_overflow_div_height; overflow: auto; border: 1px inset;";

$template->assign_vars(array('DL_BUTTONS' => false));

$count_mode = ($bb_cfg['bt_dl_list_only_count'] && !(@$_GET['dl'] === 'names'));

$dl_topic = ($t_data['topic_dl_type'] == TOPIC_DL_TYPE_DL && !($bb_cfg['bt_dl_list_only_1st_page'] && $start));
$show_dl_list = ($dl_topic && ($bb_cfg['bt_show_dl_list'] || ($bb_cfg['allow_dl_list_names_mode'] && @$_GET['dl'] === 'names')));
$show_dl_buttons = ($dl_topic && $bb_cfg['bt_show_dl_list_buttons']);

// link to clear DL-List
$template->assign_vars(array('S_DL_DELETE' => false));
if (($is_auth['auth_mod']) && ($t_data['topic_dl_type'] == TOPIC_DL_TYPE_DL)) {
    $s_dl_delete = "<br /><a href=\"dl_list.php?mode=dl_delete&amp;" . POST_TOPIC_URL . "=$topic_id&amp;sid=" . $userdata['session_id'] . '">' . $lang['DL_LIST_DEL'] . '</a>';
    $template->assign_vars(array('S_DL_DELETE' => $s_dl_delete));
}

$dl_cat = $dl_count = array();

if ($show_dl_list) {
    foreach ($dl_status_css as $i => $desc) {
        $dl_cat[$i] = '';
        $dl_count[$i] = 0;
    }

    if ($count_mode) {
        $sql = "SELECT dl_status AS user_status, users_count AS username
			FROM " . BB_BT_DLSTATUS_SNAP . "
			WHERE topic_id = $topic_id";
    } else {
        $sql = "SELECT d.user_status, d.user_id, DATE_FORMAT(d.last_modified_dlstatus, '%Y-%m-%d') AS last_modified_dlstatus, u.username, u.user_rank
			FROM " . BB_BT_DLSTATUS . " d, " . BB_USERS . " u
			WHERE d.topic_id = $topic_id
				AND d.user_id = u.user_id
				AND d.user_status != " . DL_STATUS_RELEASER . "
			ORDER BY d.user_status /* ASC, d.last_modified_dlstatus DESC */
			LIMIT $dl_list_sql_limit";
    }

    if ($dl_info = DB()->fetch_rowset($sql)) {
        if ($count_mode) {
            $template->assign_block_vars('dl_counts', array());
        } else {
            $template->assign_block_vars('dl_users', array());
        }

        foreach ($dl_info as $rid => $u) {
            $u_link_class = $dl_status_css[$u['user_status']];

            if ($count_mode) {
                $dl_cat[$u['user_status']] = $u['username'];
                $dl_count[$u['user_status']] = $u['username'];
            } else {
                $u_prof_href = ($u['user_id'] == GUEST_UID) ? '#' : "profile.php?mode=viewprofile&amp;u=" . $u['user_id'] . "#torrent";
                $dl_cat[$u['user_status']] .= '<nobr><a class="' . $u_link_class . '" href="' . $u_prof_href . '" title="' . $u['last_modified_dlstatus'] . '">' . profile_url(array('username' => $u['username'], 'user_rank' => $u['user_rank'])) . '</a></nobr>, ';
                $dl_count[$u['user_status']]++;
            }
        }

        foreach ($dl_status_css as $i => $desc) {
            if ($dl_cat[$i] && !$count_mode) {
                $dl_users_div_style = ($dl_count[$i] > $max_dl_users_before_overflow) ? $dl_users_div_style_overflow : $dl_users_div_style_normal;
                $dl_cat[$i][strlen($dl_cat[$i]) - 2] = ' ';
                $dl_cat[$i] = "<span class=$desc>" . $dl_cat[$i] . '</span>';

                $template->assign_block_vars('dl_users.users_row', array(
                    'DL_OPTION_NAME' => $lang[strtoupper($desc)],
                    'DL_OPTION_USERS' => $dl_cat[$i],
                    'DL_COUNT' => $dl_count[$i],
                    'DL_USERS_DIV_STYLE' => $dl_users_div_style,
                ));
            } elseif ($dl_count[$i] && $count_mode) {
                if ($i == DL_STATUS_CANCEL && !$show_canceled_in_count_mode) {
                    continue;
                }
                $template->assign_block_vars('dl_counts.count_row', array(
                    'DL_OPTION_NAME' => $lang[strtoupper($desc)],
                    'DL_OPTION_USERS' => $dl_count[$i],
                ));
            }
        }
    } else {
        $template->assign_block_vars('dl_list_none', array());
    }
}

if ($show_dl_buttons) {
    $template->assign_vars(array(
        'DL_BUTTONS' => true,
        'DL_BUT_WILL' => $bb_cfg['bt_show_dl_but_will'],
        'DL_BUT_DOWN' => $bb_cfg['bt_show_dl_but_down'],
        'DL_BUT_COMPL' => $bb_cfg['bt_show_dl_but_compl'],
        'DL_BUT_CANCEL' => $bb_cfg['bt_show_dl_but_cancel'],
    ));

    $dl_hidden_fields = '
		<input type="hidden" name="sid" value="' . $userdata['session_id'] . '" />
		<input type="hidden" name="' . POST_FORUM_URL . '" value="' . $forum_id . '" />
		<input type="hidden" name="' . POST_TOPIC_URL . '" value="' . $topic_id . '" />
		<input type="hidden" name="mode" value="set_dl_status" />
	';

    $template->assign_vars(array(
        'DL_HIDDEN_FIELDS' => $dl_hidden_fields,
        'S_DL_ACTION' => "dl_list.php?" . POST_TOPIC_URL . "=$topic_id",
    ));
}

$template->assign_vars(array('SHOW_DL_LIST' => $show_dl_list));
unset($dl_info);
