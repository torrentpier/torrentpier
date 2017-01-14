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

global $t_data, $poster_id, $is_auth, $dl_link_css, $dl_status_css, $lang, $userdata;

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

$show_peers_limit = 300;
$max_peers_before_overflow = 20;
$peers_div_style_normal = 'padding: 3px;';
$peers_div_style_overflow = "padding: 6px; height: 400px; overflow: auto; border: 1px inset;";

$peers_cnt = $seed_count = $leech_count = 0;
$seeders = $leechers = '';
$tor_info = [];

$template->assign_vars([
    'DL_BUTTONS' => true,
    'LEECH_COUNT' => false,
    'SEED_COUNT' => false,
    'SHOW_CPHOLD_OPT' => (IS_ADMIN || IS_CP_HOLDER),
    'SHOW_RATIO_WARN' => false,
    'TOR_CONTROLS' => false,
    'TOR_SPEED_DOWN' => false,
    'TOR_SPEED_UP' => false,
]);
$page_cfg['show_tor_status_select'] = ($is_auth['auth_mod'] || IS_CP_HOLDER);

// Define show peers mode (count only || full details)
$s_mode = (isset($_GET['spmode']) && $_GET['spmode'] == 'full') ? 'full' : 'count';

$bt_user_id = $userdata['user_id'];
$tor_file_size = humn_size($t_data['filesize']);
$tor_reged = (bool)$t_data['tracker_status'];

$locked = ($t_data['forum_status'] == FORUM_LOCKED || $t_data['topic_status'] == TOPIC_LOCKED);
$tor_auth = ($bt_user_id != GUEST_UID && (($bt_user_id == $poster_id && !$locked) || $is_auth['auth_mod'] || IS_CP_HOLDER));

if ($tor_auth && $t_data['allow_reg_tracker'] && !$tor_reged && !IS_CP_HOLDER) {
    $tr_reg_link = '<a class="txtb" href="#" onclick="ajax.exec({ action: \'change_torrent\', t : ' . $topic_id . ', type: \'reg\'}); return false;">' . $lang['BT_REG_ON_TRACKER'] . '</a>';
} elseif ($is_auth['auth_mod'] && $tor_reged && !IS_CP_HOLDER) {
    $tr_reg_link = '<a class="txtb" href="#" onclick="ajax.exec({ action: \'change_torrent\', t : ' . $topic_id . ', type: \'unreg\'}); return false;">' . $lang['BT_UNREG_FROM_TRACKER'] . '</a>';
} else {
    $tr_reg_link = ($tor_reged) ? $lang['BT_REG_YES'] : $lang['BT_REG_NO'];
}

if ($tor_auth) {
    $template->assign_vars(array(
        'TOR_CONTROLS' => ($is_auth['auth_mod'] || IS_CP_HOLDER),
        'TOR_ACTION' => 'torrent.php',
    ));

    if ($t_data['self_moderated'] || $is_auth['auth_mod']) {
        $template->assign_vars(array('AUTH_MOVE' => true));
    }
}

if (!$tor_reged) {
    $template->assign_vars(array(
        'SHOW_TOR_NOT_REGGED' => true,
        'TRACKER_REG_LINK' => $tr_reg_link,
    ));
} else {
    $tor_info = DB()->fetch_row("SELECT * FROM " . BB_BT_TORRENTS . " WHERE topic_id = $topic_id LIMIT 1");
}

if ($tor_reged && !$tor_info) {
    DB()->query("UPDATE " . BB_TOPICS . " SET tracker_status = 0 WHERE topic_id = $topic_id LIMIT 1");
    bb_die('Torrent status fixed'); // TODO: перевести
}

if ($tor_reged) {
    $tor_size = (int)$tor_info['size'];

    // Magnet link
    $passkey = DB()->fetch_row("SELECT auth_key FROM " . BB_BT_USERS . " WHERE user_id = " . (int)$bt_user_id . " LIMIT 1");
    $tor_magnet = create_magnet($tor_info['info_hash'], $passkey['auth_key'], $userdata['session_logged_in']);

    // ratio limits
    $min_ratio_dl = $di->config->get('bt_min_ratio_allow_dl_tor');
    $min_ratio_warn = $di->config->get('bt_min_ratio_warning');
    $dl_allowed = true;
    $user_ratio = 0;

    if (($min_ratio_dl || $min_ratio_warn) && $bt_user_id != $poster_id) {
        $sql = "SELECT u.*, dl.user_status
			FROM " . BB_BT_USERS . " u
			LEFT JOIN " . BB_BT_DLSTATUS . " dl ON dl.user_id = $bt_user_id AND dl.topic_id = $topic_id
			WHERE u.user_id = $bt_user_id
			LIMIT 1";
    } else {
        $sql = "SELECT user_status
			FROM " . BB_BT_DLSTATUS . "
			WHERE user_id = $bt_user_id
				AND topic_id = $topic_id
			LIMIT 1";
    }

    $bt_userdata = DB()->fetch_row($sql);

    $user_status = isset($bt_userdata['user_status']) ? $bt_userdata['user_status'] : null;

    if (($min_ratio_dl || $min_ratio_warn) && $user_status != DL_STATUS_COMPLETE && $bt_user_id != $poster_id && $tor_info['tor_type'] != TOR_TYPE_GOLD) {
        if (($user_ratio = get_bt_ratio($bt_userdata)) !== null) {
            $dl_allowed = ($user_ratio > $min_ratio_dl);
        }

        if ((isset($user_ratio) && isset($min_ratio_warn) && $user_ratio < $min_ratio_warn && TR_RATING_LIMITS) || ($bt_userdata['u_down_total'] < MIN_DL_FOR_RATIO)) {
            $template->assign_vars(array(
                'SHOW_RATIO_WARN' => true,
                'RATIO_WARN_MSG' => sprintf($lang['BT_RATIO_WARNING_MSG'], $min_ratio_dl, $di->config->get('ratio_url_help')),
            ));
        }
    }

    if (!$dl_allowed) {
        $template->assign_vars(array(
            'TOR_BLOCKED' => true,
            'TOR_BLOCKED_MSG' => sprintf($lang['BT_LOW_RATIO_FOR_DL'], round($user_ratio, 2), "search.php?dlu=$bt_user_id&amp;dlc=1"),
        ));
    } else {
        $template->assign_vars(array(
            'DOWNLOAD_NAME' => '[' . $di->config->get('server_name') . '].t' . $topic_id . '.torrent',
            'TOR_SILVER_GOLD' => $tor_info['tor_type'],

            'TOR_FROZEN' => (!IS_AM) ? ($di->config->get('tor_frozen.' . $tor_info['tor_status']) && !($di->config->get('tor_frozen_author_download.' . $tor_info['tor_status'])) && $userdata['user_id'] == $tor_info['poster_id']) ? true : '' : '',
            'TOR_STATUS_TEXT' => $lang['TOR_STATUS_NAME'][$tor_info['tor_status']],
            'TOR_STATUS_ICON' => $di->config->get('tor_icons.' . $tor_info['tor_status']),
            'TOR_STATUS_BY' => ($tor_info['checked_user_id'] && $is_auth['auth_mod']) ? ('<span title="' . bb_date($tor_info['checked_time']) . '"> &middot; ' . profile_url($tor_info) . ' &middot; <i>' . delta_time($tor_info['checked_time']) . $lang['TOR_BACK'] . '</i></span>') : '',
            'TOR_STATUS_SELECT' => build_select('sel_status', array_flip($lang['TOR_STATUS_NAME']), TOR_APPROVED),
            'TOR_STATUS_REPLY' => $di->config->get('tor_comment') && !IS_GUEST && in_array($tor_info['tor_status'], $di->config->get('tor_reply')) && $userdata['user_id'] == $tor_info['poster_id'] && $t_data['topic_status'] != TOPIC_LOCKED,

            'DL_LINK_CLASS' => (isset($bt_userdata['user_status'])) ? $dl_link_css[$bt_userdata['user_status']] : 'genmed',
            'DL_TITLE_CLASS' => (isset($bt_userdata['user_status'])) ? $dl_status_css[$bt_userdata['user_status']] : 'gen',
            'FILESIZE' => $tor_file_size,
            'MAGNET' => $tor_magnet,
            'HASH' => strtoupper(bin2hex($tor_info['info_hash'])),
            'DOWNLOAD_COUNT' => declension($t_data['attach_dl_cnt'], 'times'),
            'REGED_TIME' => bb_date($tor_info['reg_time']),

            'SHOW_TOR_REGGED' => true,
            'TRACKER_REG_LINK' => $tr_reg_link,
            'AUTH_MOD' => $is_auth['auth_mod'],
            'TOR_SIZE' => humn_size($tor_size),
            'TOR_LONGEVITY' => delta_time($tor_info['reg_time']),
            'TOR_COMPLETED' => declension($tor_info['complete_count'], 'times'),
        ));
    }

    // Show peers
    if ($tor_info['tor_status'] == TOR_CLOSED_CPHOLD) {
        $template->assign_vars(array(
            'TOR_CLOSED_BY_CPHOLD' => true,
            'TOR_CONTROLS' => false,
            'DL_BUTTONS' => false,
            'CPHOLD_UID' => $tor_info['tor_status_uid'],
            'CPHOLD_NAME' => $tor_status_username,
            'CAN_OPEN_CH_RELEASE' => ($is_auth['auth_mod'] || IS_CP_HOLDER),
        ));
    } else {
        // Sorting order in full mode
        if ($s_mode == 'full') {
            $full_mode_order = 'tr.remain';
            $full_mode_sort_dir = 'ASC';

            if (isset($_REQUEST['psortasc'])) {
                $full_mode_sort_dir = 'ASC';
            } elseif (isset($_REQUEST['psortdesc'])) {
                $full_mode_sort_dir = 'DESC';
            }

            if (isset($_REQUEST['porder'])) {
                $peer_orders = array(
                    'name' => 'u.username',
                    'ip' => 'tr.ip',
                    'port' => 'tr.port',
                    'compl' => 'tr.remain',
                    'cup' => 'tr.uploaded',
                    'cdown' => 'tr.downloaded',
                    'sup' => 'tr.speed_up',
                    'sdown' => 'tr.speed_down',
                    'time' => 'tr.update_time',
                );

                foreach ($peer_orders as $get_key => $order_by_value) {
                    if ($_REQUEST['porder'] == $get_key) {
                        $full_mode_order = $order_by_value;
                        break;
                    }
                }
            }
        }
        // SQL for each mode
        if ($s_mode == 'count') {
            $sql = "SELECT seeders, leechers, speed_up, speed_down
				FROM " . BB_BT_TRACKER_SNAP . "
				WHERE topic_id = $topic_id
				LIMIT 1";
        } elseif ($s_mode == 'names') {
            $sql = "SELECT tr.user_id, tr.ip, tr.port, tr.remain, tr.seeder, u.username, u.user_rank
				FROM " . BB_BT_TRACKER . " tr, " . BB_USERS . " u
				WHERE tr.topic_id = $topic_id
					AND u.user_id = tr.user_id
				GROUP BY tr.ip, tr.user_id, tr.port, tr.seeder
				ORDER BY u.username
				LIMIT $show_peers_limit";
        } else {
            $sql = "SELECT
					tr.user_id, tr.ip, tr.port, tr.uploaded, tr.downloaded, tr.remain,
					tr.seeder, tr.releaser, tr.speed_up, tr.speed_down, tr.update_time,
					tr.complete_percent, u.username, u.user_rank
				FROM " . BB_BT_TRACKER . " tr
				LEFT JOIN " . BB_USERS . " u ON u.user_id = tr.user_id
				WHERE tr.topic_id = $topic_id
				GROUP BY tr.ip, tr.user_id, tr.port, tr.seeder
				ORDER BY $full_mode_order $full_mode_sort_dir
				LIMIT $show_peers_limit";
        }

        // Build peers table
        if ($peers = DB()->fetch_rowset($sql)) {
            $peers_cnt = count($peers);

            $cnt = $tr = $sp_up = $sp_down = $sp_up_tot = $sp_down_tot = array();
            $cnt['s'] = $tr['s'] = $sp_up['s'] = $sp_down['s'] = $sp_up_tot['s'] = $sp_down_tot['s'] = 0;
            $cnt['l'] = $tr['l'] = $sp_up['l'] = $sp_down['l'] = $sp_up_tot['l'] = $sp_down_tot['l'] = 0;

            $max_up = $max_down = $max_sp_up = $max_sp_down = array();
            $max_up['s'] = $max_down['s'] = $max_sp_up['s'] = $max_sp_down['s'] = 0;
            $max_up['l'] = $max_down['l'] = $max_sp_up['l'] = $max_sp_down['l'] = 0;
            $max_up_id['s'] = $max_down_id['s'] = $max_sp_up_id['s'] = $max_sp_down_id['s'] = ($peers_cnt + 1);
            $max_up_id['l'] = $max_down_id['l'] = $max_sp_up_id['l'] = $max_sp_down_id['l'] = ($peers_cnt + 1);

            if ($s_mode == 'full') {
                foreach ($peers as $pid => $peer) {
                    $x = ($peer['seeder']) ? 's' : 'l';
                    $cnt[$x]++;
                    $sp_up_tot[$x] += $peer['speed_up'];
                    $sp_down_tot[$x] += $peer['speed_down'];

                    $guest = ($peer['user_id'] == GUEST_UID || is_null($peer['username']));
                    $p_max_up = $peer['uploaded'];
                    $p_max_down = $peer['downloaded'];

                    if ($p_max_up > $max_up[$x]) {
                        $max_up[$x] = $p_max_up;
                        $max_up_id[$x] = $pid;
                    }
                    if ($peer['speed_up'] > $max_sp_up[$x]) {
                        $max_sp_up[$x] = $peer['speed_up'];
                        $max_sp_up_id[$x] = $pid;
                    }
                    if ($p_max_down > $max_down[$x]) {
                        $max_down[$x] = $p_max_down;
                        $max_down_id[$x] = $pid;
                    }
                    if ($peer['speed_down'] > $max_sp_down[$x]) {
                        $max_sp_down[$x] = $peer['speed_down'];
                        $max_sp_down_id[$x] = $pid;
                    }
                }
                $max_down_id['s'] = $max_sp_down_id['s'] = ($peers_cnt + 1);

                if ($cnt['s'] == 1) {
                    $max_up_id['s'] = $max_sp_up_id['s'] = ($peers_cnt + 1);
                }
                if ($cnt['l'] == 1) {
                    $max_up_id['l'] = $max_down_id['l'] = $max_sp_up_id['l'] = $max_sp_down_id['l'] = ($peers_cnt + 1);
                }
            }

            if ($s_mode == 'count') {
                $tmp = array();
                $tmp[0]['seeder'] = $tmp[0]['username'] = $tmp[1]['username'] = 0;
                $tmp[1]['seeder'] = 1;
                $tmp[0]['username'] = (int)$peers[0]['leechers'];
                $tmp[1]['username'] = (int)$peers[0]['seeders'];
                $tor_speed_up = (int)$peers[0]['speed_up'];
                $tor_speed_down = (int)$peers[0]['speed_down'];
                $peers = $tmp;

                $template->assign_vars(array(
                    'TOR_SPEED_UP' => ($tor_speed_up) ? humn_size($tor_speed_up, 0, 'KB') . '/s' : '0 KB/s',
                    'TOR_SPEED_DOWN' => ($tor_speed_down) ? humn_size($tor_speed_down, 0, 'KB') . '/s' : '0 KB/s',
                ));
            }

            foreach ($peers as $pid => $peer) {
                $u_prof_href = ($s_mode == 'count') ? '#' : "profile.php?mode=viewprofile&amp;u=" . $peer['user_id'] . "#torrent";

                // Full details mode
                if ($s_mode == 'full') {
                    $ip = bt_show_ip($peer['ip']);
                    $port = bt_show_port($peer['port']);

                    // peer max/current up/down
                    $p_max_up = $peer['uploaded'];
                    $p_max_down = $peer['downloaded'];
                    $p_cur_up = $peer['uploaded'];
                    $p_cur_down = $peer['downloaded'];

                    if ($peer['seeder']) {
                        $x = 's';
                        $x_row = 'srow';
                        $x_full = 'sfull';

                        if (!defined('SEEDER_EXIST')) {
                            define('SEEDER_EXIST', true);
                            $seed_order_action = "viewtopic.php?" . POST_TOPIC_URL . "=$topic_id&amp;spmode=full#seeders";

                            $template->assign_block_vars("$x_full", array(
                                'SEED_ORD_ACT' => $seed_order_action,
                                'SEEDERS_UP_TOT' => humn_size($sp_up_tot[$x], 0, 'KB') . '/s'
                            ));

                            if ($ip) {
                                $template->assign_block_vars("$x_full.iphead", array());
                            }
                            if ($port !== false) {
                                $template->assign_block_vars("$x_full.porthead", array());
                            }
                        }
                        $compl_perc = ($tor_size) ? round(($p_max_up / $tor_size), 1) : 0;
                    } else {
                        $x = 'l';
                        $x_row = 'lrow';
                        $x_full = 'lfull';

                        if (!defined('LEECHER_EXIST')) {
                            define('LEECHER_EXIST', true);
                            $leech_order_action = "viewtopic.php?" . POST_TOPIC_URL . "=$bt_topic_id&amp;spmode=full#leechers";

                            $template->assign_block_vars("$x_full", array(
                                'LEECH_ORD_ACT' => $leech_order_action,
                                'LEECHERS_UP_TOT' => humn_size($sp_up_tot[$x], 0, 'KB') . '/s',
                                'LEECHERS_DOWN_TOT' => humn_size($sp_down_tot[$x], 0, 'KB') . '/s'
                            ));

                            if ($ip) {
                                $template->assign_block_vars("$x_full.iphead", array());
                            }
                            if ($port !== false) {
                                $template->assign_block_vars("$x_full.porthead", array());
                            }
                        }
                        $compl_size = ($peer['remain'] && $tor_size && $tor_size > $peer['remain']) ? ($tor_size - $peer['remain']) : 0;
                        $compl_perc = ($compl_size) ? floor($compl_size * 100 / $tor_size) : 0;
                    }

                    $rel_sign = (!$guest && $peer['releaser']) ? '&nbsp;<b><sup>&reg;</sup></b>' : '';
                    $name = profile_url($peer) . $rel_sign;
                    $up_tot = ($p_max_up) ? humn_size($p_max_up) : '-';
                    $down_tot = ($p_max_down) ? humn_size($p_max_down) : '-';
                    $up_ratio = ($p_max_down) ? round(($p_max_up / $p_max_down), 2) : '';
                    $sp_up = ($peer['speed_up']) ? humn_size($peer['speed_up'], 0, 'KB') . '/s' : '-';
                    $sp_down = ($peer['speed_down']) ? humn_size($peer['speed_down'], 0, 'KB') . '/s' : '-';

                    $template->assign_block_vars("$x_full.$x_row", array(
                        'NAME' => ($peer['update_time']) ? $name : "<s>$name</s>",
                        'COMPL_PRC' => $compl_perc,
                        'UP_TOTAL' => ($max_up_id[$x] == $pid) ? "<b>$up_tot</b>" : $up_tot,
                        'DOWN_TOTAL' => ($max_down_id[$x] == $pid) ? "<b>$down_tot</b>" : $down_tot,
                        'SPEED_UP' => ($max_sp_up_id[$x] == $pid) ? "<b>$sp_up</b>" : $sp_up,
                        'SPEED_DOWN' => ($max_sp_down_id[$x] == $pid) ? "<b>$sp_down</b>" : $sp_down,
                        'UP_TOTAL_RAW' => $peer['uploaded'],
                        'DOWN_TOTAL_RAW' => $peer['downloaded'],
                        'SPEED_UP_RAW' => $peer['speed_up'],
                        'SPEED_DOWN_RAW' => $peer['speed_down'],
                        'UPD_EXP_TIME' => ($peer['update_time']) ? $lang['DL_UPD'] . bb_date($peer['update_time'], 'd-M-y H:i') . ' &middot; ' . delta_time($peer['update_time']) . $lang['TOR_BACK'] : $lang['DL_STOPPED'],
                        'TOR_RATIO' => ($up_ratio) ? $lang['USER_RATIO'] . "UL/DL: $up_ratio" : '',
                    ));

                    if ($ip) {
                        $template->assign_block_vars("$x_full.$x_row.ip", array('IP' => $ip));
                    }
                    if ($port !== false) {
                        $template->assign_block_vars("$x_full.$x_row.port", array('PORT' => $port));
                    }
                } // Count only & only names modes
                else {
                    if ($peer['seeder']) {
                        $seeders .= '<nobr><a href="' . $u_prof_href . '" class="seedmed">' . $peer['username'] . '</a>,</nobr> ';
                        $seed_count = $peer['username'];
                    } else {
                        $compl_size = ($peer['remain'] && $tor_size && $tor_size > $peer['remain']) ? ($tor_size - $peer['remain']) : 0;
                        $compl_perc = ($compl_size) ? floor($compl_size * 100 / $tor_size) : 0;

                        $leechers .= '<nobr><a href="' . $u_prof_href . '" class="leechmed">' . $peer['username'] . '</a>';
                        $leechers .= ($s_mode == 'names') ? ' [' . $compl_perc . '%]' : '';
                        $leechers .= ',</nobr> ';
                        $leech_count = $peer['username'];
                    }
                }
            }

            if ($s_mode != 'full' && $seeders) {
                $seeders[strlen($seeders) - 9] = ' ';
                $template->assign_vars(array(
                    'SEED_LIST' => $seeders,
                    'SEED_COUNT' => ($seed_count) ? $seed_count : 0,
                ));
            }
            if ($s_mode != 'full' && $leechers) {
                $leechers[strlen($leechers) - 9] = ' ';
                $template->assign_vars(array(
                    'LEECH_LIST' => $leechers,
                    'LEECH_COUNT' => ($leech_count) ? $leech_count : 0,
                ));
            }
        }
        unset($peers);

        // Show "seeder last seen info"
        if (($s_mode == 'count' && !$seed_count) || (!$seeders && !defined('SEEDER_EXIST'))) {
            $last_seen_time = ($tor_info['seeder_last_seen']) ? delta_time($tor_info['seeder_last_seen']) : $lang['NEVER'];

            $template->assign_vars(array(
                'SEEDER_LAST_SEEN' => sprintf($lang['SEEDER_LAST_SEEN'], $last_seen_time),
            ));
        }
    }

    //$template->assign_block_vars('tor_title', array('U_DOWNLOAD_LINK' => $download_link));

    if ($peers_cnt > $max_peers_before_overflow && $s_mode == 'full') {
        $template->assign_vars(array('PEERS_DIV_STYLE' => $peers_div_style_overflow));
        $template->assign_vars(array('PEERS_OVERFLOW' => true));
    } else {
        $template->assign_vars(array('PEERS_DIV_STYLE' => $peers_div_style_normal));
    }
}

if ($di->config->get('bt_allow_spmode_change') && $s_mode != 'full') {
    $template->assign_vars(array(
        'PEERS_FULL_LINK' => true,
        'SPMODE_FULL_HREF' => "viewtopic.php?" . POST_TOPIC_URL . "=$topic_id&amp;spmode=full#seeders",
    ));
}

$template->assign_vars(array(
    'SHOW_DL_LIST_LINK' => (($di->config->get('bt_show_dl_list') || $di->config->get('allow_dl_list_names_mode')) && $t_data['tracker_status']),
    'SHOW_TOR_ACT' => ($tor_reged && (!$di->config->get('tor_no_tor_act.' . $tor_info['tor_status']) || IS_AM)),
    'S_MODE_COUNT' => ($s_mode == 'count'),
    'S_MODE_FULL' => ($s_mode == 'full'),
    'PEER_EXIST' => ($seeders || $leechers || defined('SEEDER_EXIST') || defined('LEECHER_EXIST')),
    'SEED_EXIST' => ($seeders || defined('SEEDER_EXIST')),
    'LEECH_EXIST' => ($leechers || defined('LEECHER_EXIST')),
    'TOR_HELP_LINKS' => $di->config->get('tor_help_links'),
    'CALL_SEED' => ($di->config->get('callseed') && $tor_reged && !$di->config->get('tor_no_tor_act.' . $tor_info['tor_status']) && $seed_count < 3 && $tor_info['call_seed_time'] < (TIMENOW - 86400)),
));
