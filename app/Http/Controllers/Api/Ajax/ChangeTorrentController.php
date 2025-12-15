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
use TorrentPier\Torrent\Moderation;
use TorrentPier\Torrent\Registry;

/**
 * Change Torrent Controller
 *
 * Handles torrent type changes (gold/silver), registration, and deletion.
 */
class ChangeTorrentController
{
    use AjaxResponse;

    protected string $action = 'change_torrent';

    /**
     * @throws BindingResolutionException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];

        if (!isset($body['topic_id'])) {
            return $this->error(__('EMPTY_TOPIC_ID'));
        }
        if (!isset($body['type'])) {
            return $this->error('empty type');
        }

        $topicId = (int)$body['topic_id'];
        $type = (string)$body['type'];

        if (!$torrent = Registry::getTorrentInfo($topicId)) {
            return $this->error(__('INVALID_TOPIC_ID'));
        }

        // Check permissions
        if ($torrent['topic_poster'] == userdata('user_id') && !IS_AM) {
            if (!\in_array($type, ['del_torrent', 'reg', 'unreg'])) {
                return $this->error(__('ONLY_FOR_MOD'));
            }
        } elseif (!IS_AM) {
            return $this->error(__('ONLY_FOR_MOD'));
        }

        $title = '';
        $url = '';

        switch ($type) {
            case 'set_gold':
            case 'set_silver':
            case 'unset_silver_gold':
                if ($type == 'set_silver') {
                    $torType = TOR_TYPE_SILVER;
                    $torTypeLang = __('SILVER');
                } elseif ($type == 'set_gold') {
                    $torType = TOR_TYPE_GOLD;
                    $torTypeLang = __('GOLD');
                } else {
                    $torType = TOR_TYPE_DEFAULT;
                    $torTypeLang = __('UNSET_GOLD_TORRENT') . ' / ' . __('UNSET_SILVER_TORRENT');
                }

                Moderation::changeType($topicId, $torType);

                log_action()->mod('mod_topic_change_tor_type', [
                    'forum_id' => $torrent['forum_id'],
                    'topic_id' => $topicId,
                    'topic_title' => $torrent['topic_title'],
                    'log_msg' => \sprintf(__('TOR_TYPE_LOG_ACTION'), $torTypeLang),
                ]);

                $title = __('CHANGE_TOR_TYPE');
                $url = make_url(TOPIC_URL . $topicId);
                break;

            case 'reg':
                Registry::register($topicId);
                log_action()->mod('mod_topic_tor_register', [
                    'forum_id' => $torrent['forum_id'],
                    'topic_id' => $topicId,
                    'topic_title' => $torrent['topic_title'],
                ]);
                $url = TOPIC_URL . $topicId;
                break;

            case 'unreg':
                Registry::unregister($topicId);
                log_action()->mod('mod_topic_tor_unregister', [
                    'forum_id' => $torrent['forum_id'],
                    'topic_id' => $topicId,
                    'topic_title' => $torrent['topic_title'],
                ]);
                $url = TOPIC_URL . $topicId;
                break;

            case 'del_torrent':
                if (empty($body['confirmed'])) {
                    return $this->promptConfirm(__('DEL_TORRENT'));
                }
                Registry::delete($topicId);
                $url = make_url(TOPIC_URL . $topicId);
                break;

            case 'del_torrent_move_topic':
                if (empty($body['confirmed'])) {
                    return $this->promptConfirm(__('DEL_MOVE_TORRENT'));
                }
                Registry::delete($topicId);
                $url = make_url('modcp?' . POST_TOPIC_URL . "=$topicId&mode=move&sid=" . userdata('session_id'));
                break;
        }

        return $this->response([
            'url' => $url,
            'title' => $title,
        ]);
    }
}
