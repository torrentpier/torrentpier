<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\Ajax;

use App\Http\Controllers\Api\Ajax\Concerns\AjaxResponse;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Index Data Controller
 *
 * Handles various index page data requests.
 */
class IndexDataController
{
    use AjaxResponse;

    protected string $action = 'index_data';

    /**
     * @throws BindingResolutionException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];

        $mode = $this->requireMode($body);
        if ($mode instanceof ResponseInterface) {
            return $mode;
        }

        switch ($mode) {
            case 'birthday_week':
                $html = $this->handleBirthdayWeek();
                break;

            case 'birthday_today':
                $html = $this->handleBirthdayToday();
                break;

            case 'get_forum_mods':
                $html = $this->handleGetForumMods($body);
                break;

            case 'null_ratio':
                return $this->handleNullRatio($body);

            case 'releaser_stats':
                return $this->handleReleaserStats($body);

            case 'get_traf_stats':
                return $this->handleGetTrafStats($body);

            default:
                return $this->error('Invalid mode: ' . $mode);
        }

        return $this->response([
            'html' => $html,
            'mode' => $mode,
        ]);
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleBirthdayWeek(): string
    {
        datastore()->enqueue(['stats']);
        $stats = datastore()->get('stats');

        $users = [];

        if ($stats['birthday_week_list']) {
            foreach ($stats['birthday_week_list'] as $week) {
                $users[] = profile_url($week) . ' <span class="small">(' . birthday_age(date('Y-m-d', strtotime('-1 year', strtotime($week['user_birthday'])))) . ')</span>';
            }

            return \sprintf(__('BIRTHDAY_WEEK'), config()->get('birthday_check_day'), implode(', ', $users));
        }

        return \sprintf(__('NOBIRTHDAY_WEEK'), config()->get('birthday_check_day'));
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleBirthdayToday(): string
    {
        datastore()->enqueue(['stats']);
        $stats = datastore()->get('stats');

        $users = [];

        if ($stats['birthday_today_list']) {
            foreach ($stats['birthday_today_list'] as $today) {
                $users[] = profile_url($today) . ' <span class="small">(' . birthday_age($today['user_birthday']) . ')</span>';
            }

            return __('BIRTHDAY_TODAY') . implode(', ', $users);
        }

        return __('NOBIRTHDAY_TODAY');
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleGetForumMods(array $body): string
    {
        $forumId = (int)($body['forum_id'] ?? 0);

        datastore()->enqueue(['moderators']);

        $moderators = [];
        $mod = datastore()->get('moderators');

        if (isset($mod['mod_users'][$forumId])) {
            foreach ($mod['mod_users'][$forumId] as $userId) {
                $username = $mod['name_users'][$userId];
                $moderators[] = '<a href="' . url()->member($userId, $username) . '">' . $username . '</a>';
            }
        }

        if (isset($mod['mod_groups'][$forumId])) {
            foreach ($mod['mod_groups'][$forumId] as $groupId) {
                $groupName = $mod['name_groups'][$groupId];
                $moderators[] = '<a href="' . url()->group($groupId, $groupName) . '">' . $groupName . '</a>';
            }
        }

        $html = ':&nbsp;';
        $html .= ($moderators) ? implode(', ', $moderators) : __('NONE');
        datastore()->rm('moderators');

        return $html;
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleNullRatio(array $body): ResponseInterface
    {
        if (!config()->get('tracker.ratio_null_enabled') || !RATIO_ENABLED) {
            return $this->error(__('MODULE_OFF'));
        }
        if (empty($body['confirmed'])) {
            return $this->promptConfirm(__('BT_NULL_RATIO_ALERT'));
        }

        $userId = (int)($body['user_id'] ?? 0);
        if (!IS_ADMIN && $userId != userdata('user_id')) {
            return $this->error(__('NOT_AUTHORISED'));
        }

        $btu = get_bt_userdata($userId);
        $ratioNulled = (bool)$btu['ratio_nulled'];
        $userRatio = get_bt_ratio($btu);

        if (($userRatio === null) && !IS_ADMIN) {
            return $this->error(__('BT_NULL_RATIO_NONE'));
        }
        if ($ratioNulled && !IS_ADMIN) {
            return $this->error(__('BT_NULL_RATIO_AGAIN'));
        }
        if (($userRatio >= config()->get('tracker.ratio_to_null')) && !IS_ADMIN) {
            return $this->error(\sprintf(__('BT_NULL_RATIO_NOT_NEEDED'), config()->get('tracker.ratio_to_null')));
        }

        $ratioNulledSql = !IS_ADMIN ? ', ratio_nulled = 1' : '';
        DB()->query('UPDATE ' . BB_BT_USERS . " SET u_up_total = 0, u_down_total = 0, u_up_release = 0, u_up_bonus = 0 $ratioNulledSql WHERE user_id = " . $userId);
        CACHE('bb_cache')->rm('btu_' . $userId);

        return $this->error(__('BT_NULL_RATIO_SUCCESS'));
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleReleaserStats(array $body): ResponseInterface
    {
        if (IS_GUEST) {
            return $this->error(__('NEED_TO_LOGIN_FIRST'));
        }

        $userId = (int)($body['user_id'] ?? 0);

        $sql = '
            SELECT COUNT(tor.poster_id) as total_releases, SUM(tor.size) as total_size, SUM(tor.complete_count) as total_complete
            FROM ' . BB_BT_TORRENTS . ' tor
                LEFT JOIN ' . BB_USERS . ' u ON(u.user_id = tor.poster_id)
                LEFT JOIN ' . BB_BT_USERS . " ut ON(ut.user_id = tor.poster_id)
            WHERE u.user_id = {$userId}
            GROUP BY tor.poster_id
            LIMIT 1
        ";

        $totalReleasesSize = $totalReleases = $totalReleasesCompleted = 0;
        if ($row = DB()->fetch_row($sql)) {
            $totalReleases = $row['total_releases'];
            $totalReleasesSize = $row['total_size'];
            $totalReleasesCompleted = $row['total_complete'];
        }

        $html = '[
            ' . __('RELEASES') . ': <span class="seed bold">' . $totalReleases . '</span> |
            ' . __('RELEASER_STAT_SIZE') . ' <span class="seed bold">' . humn_size($totalReleasesSize) . '</span> |
            ' . __('DOWNLOADED') . ': <span class="seed bold">' . declension((int)$totalReleasesCompleted, 'times') . '</span> ]';

        return $this->response([
            'html' => $html,
            'mode' => 'releaser_stats',
        ]);
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleGetTrafStats(array $body): ResponseInterface
    {
        if (IS_GUEST) {
            return $this->error(__('NEED_TO_LOGIN_FIRST'));
        }

        $userId = (int)($body['user_id'] ?? 0);
        $btu = get_bt_userdata($userId);
        $profiledata = get_userdata($userId);

        $speedUp = ($btu['speed_up']) ? humn_size($btu['speed_up']) . '/s' : '0 KB/s';
        $speedDown = ($btu['speed_down']) ? humn_size($btu['speed_down']) . '/s' : '0 KB/s';

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
                <td colspan="2">' . __('DL_DL_SPEED') . ': ' . $speedDown . '</span></td>
                <td colspan="2">' . __('DL_UL_SPEED') . ': ' . $speedUp . '</span></td>';
        $html .= config()->get('seed_bonus_enabled') ? '<td colspan="1"></td>' : '';
        $html .= '</tr>';

        $responseData = [
            'html' => $html,
            'mode' => 'get_traf_stats',
        ];

        if (RATIO_ENABLED) {
            $userRatio = ($btu['u_down_total'] > MIN_DL_FOR_RATIO) ? '<b class="gen">' . get_bt_ratio($btu) . '</b>' : __('IT_WILL_BE_DOWN') . ' <b>' . humn_size(MIN_DL_FOR_RATIO) . '</b>';
            $responseData['user_ratio'] = '
                <th><a href="' . config()->get('forum.ratio_url_help') . '" class="bold">' . __('USER_RATIO') . '</a>:</th>
                <td>' . $userRatio . '</td>
            ';
        }

        return $this->response($responseData);
    }
}
