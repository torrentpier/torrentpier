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

global $userdata, $datastore;

$mode = (string)$this->request['mode'];
$html = '';

switch ($mode) {
    case 'birthday_week':
        $stats = $datastore->get('stats');
        $datastore->enqueue(array(
            'stats',
        ));

        if ($stats['birthday_week_list']) {
            foreach ($stats['birthday_week_list'] as $week) {
                $html[] = profile_url($week) . ' <span class="small">(' . birthday_age($week['user_birthday']) . ')</span>';
            }
            $html = sprintf(trans('messages.BIRTHDAY_WEEK'), config('tp.birthday_check_day'), implode(', ', $html));
        } else {
            $html = sprintf(trans('messages.NOBIRTHDAY_WEEK'), config('tp.birthday_check_day'));
        }
        break;

    case 'birthday_today':
        $stats = $datastore->get('stats');
        $datastore->enqueue(array(
            'stats',
        ));

        if ($stats['birthday_today_list']) {
            foreach ($stats['birthday_today_list'] as $today) {
                $html[] = profile_url($today) . ' <span class="small">(' . birthday_age($today['user_birthday']) . ')</span>';
            }
            $html = trans('messages.BIRTHDAY_TODAY') . implode(', ', $html);
        } else {
            $html = trans('messages.NOBIRTHDAY_TODAY');
        }
        break;

    case 'get_forum_mods':
        $forum_id = (int)$this->request['forum_id'];

        $datastore->enqueue(array(
            'moderators',
            'cat_forums',
        ));

        $moderators = array();
        $mod = $datastore->get('moderators');

        if (isset($mod['mod_users'][$forum_id])) {
            foreach ($mod['mod_users'][$forum_id] as $user_id) {
                $moderators[] = '<a href="' . PROFILE_URL . $user_id . '">' . $mod['name_users'][$user_id] . '</a>';
            }
        }

        if (isset($mod['mod_groups'][$forum_id])) {
            foreach ($mod['mod_groups'][$forum_id] as $group_id) {
                $moderators[] = '<a href="' . "group.php?" . POST_GROUPS_URL . "=" . $group_id . '">' . $mod['name_groups'][$group_id] . '</a>';
            }
        }

        $html = ':&nbsp;';
        $html .= $moderators ? implode(', ', $moderators) : trans('messages.NONE');
        unset($moderators, $mod);
        $datastore->rm('moderators');
        break;

    case 'change_tz':
        $tz = (int)$this->request['tz'];
        if ($tz < -12) {
            $tz = -12;
        }
        if ($tz > 13) {
            $tz = 13;
        }
        if ($tz != config('tp.board_timezone')) {
            // Set current user timezone
            OLD_DB()->query("UPDATE " . BB_USERS . " SET user_timezone = $tz WHERE user_id = " . $userdata['user_id']);
            config(['tp.board_timezone' => $tz]);
            cache_rm_user_sessions($userdata['user_id']);
        }
        break;

    case 'get_traf_stats':
        $user_id = (int)$this->request['user_id'];
        $btu = get_bt_userdata($user_id);
        $profiledata = get_userdata($user_id);

        $speed_up = $btu['speed_up'] ? humn_size($btu['speed_up']) . '/s' : '0 KB/s';
        $speed_down = $btu['speed_down'] ? humn_size($btu['speed_down']) . '/s' : '0 KB/s';
        $user_ratio = ($btu['u_down_total'] > MIN_DL_FOR_RATIO) ? '<b class="gen">' . get_bt_ratio($btu) . '</b>' : trans('messages.IT_WILL_BE_DOWN') . ' <b>' . humn_size(MIN_DL_FOR_RATIO) . '</b>';

        $html = '
			<tr class="row3">
				<th style="padding: 0;"></th>
				<th>' . trans('messages.DOWNLOADED') . '</th>
				<th>' . trans('messages.UPLOADED') . '</th>
				<th>' . trans('messages.RELEASED') . '</th>
				<th>' . trans('messages.BONUS') . '</th>';
        $html .= config('tp.seed_bonus_enabled') ? '<th>' . trans('messages.SEED_BONUS') . '</th>' : '';
        $html .= '</tr>
			<tr class="row1">
				<td>' . trans('messages.TOTAL_TRAF') . '</td>
				<td id="u_down_total"><span class="editable bold leechmed">' . humn_size($btu['u_down_total']) . '</span></td>
				<td id="u_up_total"><span class="editable bold seedmed">' . humn_size($btu['u_up_total']) . '</span></td>
				<td id="u_up_release"><span class="editable bold seedmed">' . humn_size($btu['u_up_release']) . '</span></td>
				<td id="u_up_bonus"><span class="editable bold seedmed">' . humn_size($btu['u_up_bonus']) . '</span></td>';
        $html .= config('tp.seed_bonus_enabled') ? '<td id="user_points"><span class="editable bold points">' . $profiledata['user_points'] . '</b></td>' : '';
        $html .= '</tr>
			<tr class="row5">
				<td colspan="1">' . trans('messages.MAX_SPEED') . '</td>
				<td colspan="2">' . trans('messages.DL_DL_SPEED') . ': ' . $speed_down . '</span></td>
				<td colspan="2">' . trans('messages.DL_UL_SPEED') . ': ' . $speed_up . '</span></td>';
        $html .= config('tp.seed_bonus_enabled') ? '<td colspan="1"></td>' : '';
        $html .= '</tr>';

        $this->response['user_ratio'] = '
			<th><a href="' . config('tp.ratio_url_help') . '" class="bold">' . trans('messages.USER_RATIO') . '</a>:</th>
			<td>' . $user_ratio . '</td>
		';
        break;
}

$this->response['html'] = $html;
$this->response['mode'] = $mode;
