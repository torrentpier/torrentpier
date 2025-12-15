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
use TorrentPier\Helpers\IPHelper;
use TorrentPier\Torrent\Moderation;

/**
 * Mod Action Controller
 *
 * Handles moderator actions like batch status changes, topic title editing, and IP lookup.
 */
class ModActionController
{
    use AjaxResponse;

    protected string $action = 'mod_action';

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

        return match ($mode) {
            'tor_status' => $this->handleTorStatus($body),
            'edit_topic_title' => $this->handleEditTopicTitle($body),
            'profile_ip' => $this->handleProfileIp($body),
            default => $this->error('Invalid mode: ' . $mode),
        };
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleTorStatus(array $body): ResponseInterface
    {
        $topics = (string)($body['topic_ids'] ?? '');
        $status = (int)($body['status'] ?? 0);

        if (!isset(__('TOR_STATUS_NAME')[$status])) {
            return $this->error(__('TOR_STATUS_FAILED'));
        }

        $topicIdsList = explode(',', $topics);

        foreach ($topicIdsList as $topicId) {
            $topicId = (int)$topicId;
            if (!$topicId) {
                continue;
            }

            $tor = DB()->fetch_row('
                SELECT
                    tor.forum_id, tor.topic_id, t.topic_title, tor.tor_status
                FROM       ' . BB_BT_TORRENTS . ' tor
                INNER JOIN ' . BB_TOPICS . " t ON(t.topic_id = tor.topic_id)
                WHERE tor.topic_id = $topicId LIMIT 1");

            if (!$tor) {
                return $this->error(__('TORRENT_FAILED'));
            }

            Moderation::changeStatus($topicId, $status);

            // Log action
            $logMsg = \sprintf(
                __('TOR_STATUS_LOG_ACTION'),
                config()->get('tor_icons')[$status] . ' <b> ' . __('TOR_STATUS_NAME')[$status] . '</b>',
                config()->get('tor_icons')[$tor['tor_status']] . ' <b> ' . __('TOR_STATUS_NAME')[$tor['tor_status']] . '</b>',
            );
            log_action()->mod('mod_topic_change_tor_status', [
                'forum_id' => $tor['forum_id'],
                'topic_id' => $tor['topic_id'],
                'topic_title' => $tor['topic_title'],
                'log_msg' => $logMsg . '<br/>-------------',
            ]);
        }

        return $this->response([
            'status' => config()->get('tor_icons')[$status],
            'topics' => $topicIdsList,
        ]);
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleEditTopicTitle(array $body): ResponseInterface
    {
        $topicId = (int)($body['topic_id'] ?? 0);
        $oldTitle = get_topic_title($topicId);
        $newTitle = clean_title((string)($body['topic_title'] ?? ''));

        if (!$topicId) {
            return $this->error(__('INVALID_TOPIC_ID'));
        }
        if ($newTitle === '') {
            return $this->error(__('DONT_MESSAGE_TITLE'));
        }

        $tData = DB()->fetch_row('SELECT forum_id FROM ' . BB_TOPICS . " WHERE topic_id = $topicId LIMIT 1");
        if (!$tData) {
            return $this->error(__('INVALID_TOPIC_ID_DB'));
        }

        if ($error = $this->verifyModRights($tData['forum_id'])) {
            return $error;
        }

        $topicTitleSql = DB()->escape($newTitle);
        DB()->query('UPDATE ' . BB_TOPICS . " SET topic_title = '$topicTitleSql' WHERE topic_id = $topicId LIMIT 1");

        // Manticore [Update topic title]
        sync_topic_to_manticore($topicId, topic_title: $newTitle);

        // Update the news cache on the index page
        $newsForums = array_flip(explode(',', config()->get('latest_news_forum_id')));
        if (isset($newsForums[$tData['forum_id']]) && config()->get('show_latest_news')) {
            datastore()->enqueue(['latest_news']);
            datastore()->update('latest_news');
        }

        $netForums = array_flip(explode(',', config()->get('network_news_forum_id')));
        if (isset($netForums[$tData['forum_id']]) && config()->get('show_network_news')) {
            datastore()->enqueue(['network_news']);
            datastore()->update('network_news');
        }

        // Log action
        log_action()->mod('mod_topic_renamed', [
            'forum_id' => $tData['forum_id'],
            'topic_id' => $topicId,
            'topic_id_new' => $topicId,
            'topic_title' => $oldTitle,
            'topic_title_new' => $newTitle,
        ]);

        return $this->response([
            'topic_id' => $topicId,
            'topic_title' => $newTitle,
        ]);
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleProfileIp(array $body): ResponseInterface
    {
        $userId = (int)($body['user_id'] ?? 0);
        $profiledata = get_userdata($userId);

        if (!$userId) {
            return $this->error(__('NO_USER_ID_SPECIFIED'));
        }

        $regIp = DB()->fetch_rowset('SELECT username, user_id, user_rank FROM ' . BB_USERS . "
            WHERE user_reg_ip = '{$profiledata['user_reg_ip']}'
                AND user_reg_ip != 0
                AND user_id != {$profiledata['user_id']}
            ORDER BY username");

        $lastIp = DB()->fetch_rowset('SELECT username, user_id, user_rank FROM ' . BB_USERS . "
            WHERE user_last_ip = '{$profiledata['user_last_ip']}'
                AND user_last_ip != 0
                AND user_id != {$profiledata['user_id']}");

        $linkRegIp = $linkLastIp = '';

        if (!empty($regIp)) {
            $linkRegIp .= __('OTHER_IP') . '&nbsp';
            foreach ($regIp as $row) {
                $linkRegIp .= profile_url($row) . ', ';
            }
            $linkRegIp = rtrim($linkRegIp, ', ');
        }

        if (!empty($lastIp)) {
            $linkLastIp .= __('OTHER_IP') . '&nbsp';
            foreach ($lastIp as $row) {
                $linkLastIp .= profile_url($row) . ', ';
            }
            $linkLastIp = rtrim($linkLastIp, ', ');
        }

        if ($profiledata['user_level'] == ADMIN && !IS_ADMIN) {
            $regIpDisplay = $lastIpDisplay = __('HIDDEN');
        } elseif ($profiledata['user_level'] == MOD && !IS_AM) {
            $regIpDisplay = $lastIpDisplay = __('HIDDEN');
        } else {
            $userRegIp = IPHelper::decode($profiledata['user_reg_ip']);
            $userLastIp = IPHelper::decode($profiledata['user_last_ip']);
            $regIpDisplay = '<a href="' . config()->get('whois_info') . $userRegIp . '" class="gen" target="_blank">' . $userRegIp . '</a>';
            $lastIpDisplay = '<a href="' . config()->get('whois_info') . $userLastIp . '" class="gen" target="_blank">' . $userLastIp . '</a>';
        }

        $ipListHtml = '
            <br /><table class="mod_ip bCenter borderless" cellspacing="1">
                <tr class="row5" >
                    <td>' . __('REG_IP') . '</td>
                    <td class="tCenter">' . $regIpDisplay . '</td>
                    <td><div>' . $linkRegIp . '</div></td>
                </tr>
                <tr class="row4">
                    <td>' . __('LAST_IP') . '</td>
                    <td class="tCenter">' . $lastIpDisplay . '</td>
                    <td><div>' . $linkLastIp . '</div></td>
                </tr>
            </table><br />
        ';

        return $this->response([
            'ip_list_html' => $ipListHtml,
        ]);
    }
}
