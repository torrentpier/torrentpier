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
 * Callseed Controller
 *
 * Sends PM notifications to users who downloaded a torrent to ask them to seed.
 */
class CallseedController
{
    use AjaxResponse;

    protected string $action = 'callseed';

    /**
     * @throws BindingResolutionException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];

        if (!config()->get('callseed')) {
            return $this->error(__('MODULE_OFF'));
        }

        $topicId = (int)($body['topic_id'] ?? 0);
        if (!$topicId) {
            return $this->error(__('INVALID_TOPIC_ID'));
        }

        if (!$tData = $this->getTopicInfo($topicId)) {
            return $this->error(__('INVALID_TOPIC_ID_DB'));
        }

        if ($tData['seeders'] >= 3) {
            return $this->error(\sprintf(__('CALLSEED_HAVE_SEED'), $tData['seeders']));
        }

        if ($tData['call_seed_time'] >= (TIMENOW - 86400)) {
            $timeLeft = humanTime($tData['call_seed_time'] + 86400, TIMENOW);

            return $this->error(\sprintf(__('CALLSEED_MSG_SPAM'), $timeLeft));
        }

        if (isset(config()->get('tracker.tor_no_tor_act')[$tData['tor_status']])) {
            return $this->error(__('NOT_AVAILABLE'));
        }

        $bannedUsers = ($getBannedUsers = get_banned_users()) ? (', ' . implode(', ', $getBannedUsers)) : '';

        $userList = DB()->fetch_rowset('
            SELECT DISTINCT dl.user_id, u.user_opt, tr.user_id as active_dl
            FROM ' . BB_BT_DLSTATUS . ' dl
            LEFT JOIN ' . BB_USERS . ' u  ON(u.user_id = dl.user_id)
            LEFT JOIN ' . BB_BT_TRACKER . " tr ON(tr.user_id = dl.user_id)
            WHERE dl.topic_id = {$topicId}
                AND dl.user_status IN (" . DL_STATUS_COMPLETE . ', ' . DL_STATUS_DOWN . ')
                AND dl.user_id NOT IN (' . userdata('user_id') . ', ' . EXCLUDED_USERS . $bannedUsers . ')
                AND u.user_active = 1
            GROUP BY dl.user_id
        ');

        $subject = \sprintf(__('CALLSEED_SUBJECT'), $tData['topic_title']);
        $message = \sprintf(__('CALLSEED_TEXT'), make_url(TOPIC_URL . $topicId . '/'), $tData['topic_title'], make_url(DL_URL . $topicId . '/'));

        if ($userList) {
            foreach ($userList as $row) {
                if (!empty($row['active_dl'])) {
                    continue;
                }

                if (bf($row['user_opt'], 'user_opt', 'user_callseed')) {
                    send_pm($row['user_id'], $subject, $message);
                }
            }
        } else {
            send_pm($tData['poster_id'], $subject, $message);
        }

        DB()->query('UPDATE ' . BB_BT_TORRENTS . ' SET call_seed_time = ' . TIMENOW . " WHERE topic_id = $topicId LIMIT 1");

        return $this->response([
            'response' => __('CALLSEED_MSG_OK'),
        ]);
    }

    /**
     * @throws BindingResolutionException
     */
    private function getTopicInfo(int $topicId): array|false
    {
        $sql = '
            SELECT
                tor.poster_id, tor.forum_id, tor.call_seed_time, tor.tor_status,
                t.topic_title, sn.seeders
            FROM      ' . BB_BT_TORRENTS . ' tor
            LEFT JOIN ' . BB_TOPICS . ' t  USING(topic_id)
            LEFT JOIN ' . BB_BT_TRACKER_SNAP . " sn USING(topic_id)
            WHERE tor.topic_id = {$topicId}
        ";

        return DB()->fetch_row($sql) ?: false;
    }
}
