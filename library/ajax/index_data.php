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

if (!$mode = (string)$this->request['mode']) {
    $this->ajax_die('invalid mode (empty)');
}

$html = '';
switch ($mode) {
    case 'birthday_week':
        datastore()->enqueue([
            'stats'
        ]);
        $stats = datastore()->get('stats');

        $users = [];

        if ($stats['birthday_week_list']) {
            foreach ($stats['birthday_week_list'] as $week) {
                $users[] = profile_url($week) . ' <span class="small">(' . birthday_age(date('Y-m-d', strtotime('-1 year', strtotime($week['user_birthday'])))) . ')</span>';
            }
            $html = sprintf(__('BIRTHDAY_WEEK'), config()->get('birthday_check_day'), implode(', ', $users));
        } else {
            $html = sprintf(__('NOBIRTHDAY_WEEK'), config()->get('birthday_check_day'));
        }
        break;

    case 'birthday_today':
        datastore()->enqueue([
            'stats'
        ]);
        $stats = datastore()->get('stats');

        $users = [];

        if ($stats['birthday_today_list']) {
            foreach ($stats['birthday_today_list'] as $today) {
                $users[] = profile_url($today) . ' <span class="small">(' . birthday_age($today['user_birthday']) . ')</span>';
            }
            $html = __('BIRTHDAY_TODAY') . implode(', ', $users);
        } else {
            $html = __('NOBIRTHDAY_TODAY');
        }
        break;

    case 'get_forum_mods':
        $forum_id = (int)$this->request['forum_id'];

        datastore()->enqueue([
            'moderators'
        ]);

        $moderators = [];
        $mod = datastore()->get('moderators');

        if (isset($mod['mod_users'][$forum_id])) {
            foreach ($mod['mod_users'][$forum_id] as $user_id) {
                $username = $mod['name_users'][$user_id];
                $moderators[] = '<a href="' . url()->member($user_id, $username) . '">' . $username . '</a>';
            }
        }

        if (isset($mod['mod_groups'][$forum_id])) {
            foreach ($mod['mod_groups'][$forum_id] as $group_id) {
                $groupName = $mod['name_groups'][$group_id];
                $moderators[] = '<a href="' . url()->group($group_id, $groupName) . '">' . $groupName . '</a>';
            }
        }

        $html = ':&nbsp;';
        $html .= ($moderators) ? implode(', ', $moderators) : __('NONE');
        unset($moderators, $mod);
        datastore()->rm('moderators');
        break;

    case 'null_ratio':
        if (!config()->get('ratio_null_enabled') || !RATIO_ENABLED) {
            $this->ajax_die(__('MODULE_OFF'));
        }
        if (empty($this->request['confirmed'])) {
            $this->prompt_for_confirm(__('BT_NULL_RATIO_ALERT'));
        }

        $user_id = (int)$this->request['user_id'];
        if (!IS_ADMIN && $user_id != userdata('user_id')) {
            $this->ajax_die(__('NOT_AUTHORISED'));
        }

        $btu = get_bt_userdata($user_id);
        $ratio_nulled = (bool)$btu['ratio_nulled'];
        $user_ratio = get_bt_ratio($btu);

        if (($user_ratio === null) && !IS_ADMIN) {
            $this->ajax_die(__('BT_NULL_RATIO_NONE'));
        }
        if ($ratio_nulled && !IS_ADMIN) {
            $this->ajax_die(__('BT_NULL_RATIO_AGAIN'));
        }
        if (($user_ratio >= config()->get('ratio_to_null')) && !IS_ADMIN) {
            $this->ajax_die(sprintf(__('BT_NULL_RATIO_NOT_NEEDED'), config()->get('ratio_to_null')));
        }

        $ratio_nulled_sql = !IS_ADMIN ? ', ratio_nulled = 1' : '';
        DB()->query("UPDATE " . BB_BT_USERS . " SET u_up_total = 0, u_down_total = 0, u_up_release = 0, u_up_bonus = 0 $ratio_nulled_sql WHERE user_id = " . $user_id);
        CACHE('bb_cache')->rm('btu_' . $user_id);
        $this->ajax_die(__('BT_NULL_RATIO_SUCCESS'));
        break;

    case 'releaser_stats':
        if (IS_GUEST) {
            $this->ajax_die(__('NEED_TO_LOGIN_FIRST'));
        }

        $user_id = (int)$this->request['user_id'];

        $sql = "
				SELECT COUNT(tor.poster_id) as total_releases, SUM(tor.size) as total_size, SUM(tor.complete_count) as total_complete
				FROM " . BB_BT_TORRENTS . " tor
					LEFT JOIN " . BB_USERS . " u ON(u.user_id = tor.poster_id)
					LEFT JOIN " . BB_BT_USERS . " ut ON(ut.user_id = tor.poster_id)
				WHERE u.user_id = $user_id
				GROUP BY tor.poster_id
				LIMIT 1
			";

        $total_releases_size = $total_releases = $total_releases_completed = 0;
        if ($row = DB()->fetch_row($sql)) {
            $total_releases = $row['total_releases'];
            $total_releases_size = $row['total_size'];
            $total_releases_completed = $row['total_complete'];
        }

        $html = '[
            ' . __('RELEASES') . ': <span class="seed bold">' . $total_releases . '</span> |
            ' . __('RELEASER_STAT_SIZE') . ' <span class="seed bold">' . humn_size($total_releases_size) . '</span> |
            ' . __('DOWNLOADED') . ': <span class="seed bold">' . declension((int)$total_releases_completed, 'times') . '</span> ]';
        break;

    case 'get_traf_stats':
        if (IS_GUEST) {
            $this->ajax_die(__('NEED_TO_LOGIN_FIRST'));
        }

        $user_id = (int)$this->request['user_id'];
        $btu = get_bt_userdata($user_id);
        $profiledata = get_userdata($user_id);

        $speed_up = ($btu['speed_up']) ? humn_size($btu['speed_up']) . '/s' : '0 KB/s';
        $speed_down = ($btu['speed_down']) ? humn_size($btu['speed_down']) . '/s' : '0 KB/s';

        $html = '
			<tr class="row3">
				<th style="padding: 0;"></th>
				<th>' . __('DOWNLOADED') . '</th>
				<th>' . __('UPLOADED') . '</th>
				<th>' . __('RELEASED') . '</th>
				<th>' . __('BONUS') . '</th>';
        $html .= config()->get('seed_bonus_enabled') ? '<th>' . __('SEED_BONUS') . '</th>' : '';
        $html .= '</tr>
			<tr class="row1">
				<td>' . __('TOTAL_TRAF') . '</td>
				<td id="u_down_total"><span class="editable bold leechmed">' . humn_size($btu['u_down_total']) . '</span></td>
				<td id="u_up_total"><span class="editable bold seedmed">' . humn_size($btu['u_up_total']) . '</span></td>
				<td id="u_up_release"><span class="editable bold seedmed">' . humn_size($btu['u_up_release']) . '</span></td>
				<td id="u_up_bonus"><span class="editable bold seedmed">' . humn_size($btu['u_up_bonus']) . '</span></td>';
        $html .= config()->get('seed_bonus_enabled') ? '<td id="user_points"><span class="editable bold points">' . $profiledata['user_points'] . '</b></td>' : '';
        $html .= '</tr>
			<tr class="row5">
				<td colspan="1">' . __('MAX_SPEED') . '</td>
				<td colspan="2">' . __('DL_DL_SPEED') . ': ' . $speed_down . '</span></td>
				<td colspan="2">' . __('DL_UL_SPEED') . ': ' . $speed_up . '</span></td>';
        $html .= config()->get('seed_bonus_enabled') ? '<td colspan="1"></td>' : '';
        $html .= '</tr>';

        if (RATIO_ENABLED) {
            $user_ratio = ($btu['u_down_total'] > MIN_DL_FOR_RATIO) ? '<b class="gen">' . get_bt_ratio($btu) . '</b>' : __('IT_WILL_BE_DOWN') . ' <b>' . humn_size(MIN_DL_FOR_RATIO) . '</b>';
            $this->response['user_ratio'] = '
			    <th><a href="' . config()->get('ratio_url_help') . '" class="bold">' . __('USER_RATIO') . '</a>:</th>
			    <td>' . $user_ratio . '</td>
		    ';
        }
        break;

    default:
        $this->ajax_die('Invalid mode: ' . $mode);
}

$this->response['html'] = $html;
$this->response['mode'] = $mode;
