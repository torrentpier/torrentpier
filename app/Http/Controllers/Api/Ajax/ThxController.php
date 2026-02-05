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
 * Thx Controller
 *
 * Handles topic thanks/likes functionality.
 */
class ThxController
{
    use AjaxResponse;

    protected string $action = 'thx';

    private const int CACHE_LIFETIME = 3600;

    /**
     * @throws BindingResolutionException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];

        if (!config()->get('tracker.tor_thank')) {
            return $this->error(__('MODULE_OFF'));
        }

        $mode = $this->requireMode($body);
        if ($mode instanceof ResponseInterface) {
            return $mode;
        }

        $topicId = (int)($body['topic_id'] ?? 0);
        if (!$topicId) {
            return $this->error(__('INVALID_TOPIC_ID'));
        }

        $posterId = (int)($body['poster_id'] ?? 0);
        if (!$posterId) {
            return $this->error(__('NO_USER_ID_SPECIFIED'));
        }

        $thanksCacheKey = 'topic_thanks_' . $topicId;

        return match ($mode) {
            'add' => $this->handleAdd($topicId, $posterId, $thanksCacheKey),
            'get' => $this->handleGet($topicId, $thanksCacheKey),
            default => $this->error('Invalid mode: ' . $mode),
        };
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleAdd(int $topicId, int $posterId, string $thanksCacheKey): ResponseInterface
    {
        if (IS_GUEST) {
            return $this->error(__('NEED_TO_LOGIN_FIRST'));
        }

        if ($posterId == userdata('user_id')) {
            return $this->error(__('LIKE_OWN_POST'));
        }

        $cachedThanks = $this->getThanksList($topicId, $thanksCacheKey);
        if (isset($cachedThanks[userdata('user_id')])) {
            return $this->error(__('LIKE_ALREADY'));
        }

        $columns = 'topic_id, user_id, time';
        $values = "$topicId, " . userdata('user_id') . ', ' . TIMENOW;
        DB()->query('INSERT IGNORE INTO ' . BB_THX . " ($columns) VALUES ($values)");

        $cachedThanks[userdata('user_id')] = [
            'user_id' => userdata('user_id'),
            'username' => userdata('username'),
            'user_rank' => userdata('user_rank'),
            'time' => TIMENOW,
        ];

        // Limit voters per topic
        $torThankLimitPerTopic = (int)config()->get('tracker.tor_thank_limit_per_topic');
        if ($torThankLimitPerTopic > 0) {
            $thanksCount = \count($cachedThanks);
            if ($thanksCount > $torThankLimitPerTopic) {
                $oldestUserId = null;
                foreach ($cachedThanks as $thanksData) {
                    $oldestUserId = $thanksData['user_id'];
                    break;
                }

                if ($oldestUserId) {
                    DB()->query('DELETE FROM ' . BB_THX . " WHERE topic_id = $topicId AND user_id = $oldestUserId LIMIT 1");
                    unset($cachedThanks[$oldestUserId]);
                }
            }
        }

        if (!empty($cachedThanks)) {
            CACHE('bb_cache')->set($thanksCacheKey, $cachedThanks, self::CACHE_LIFETIME);
        }

        return $this->response([
            'mode' => 'add',
        ]);
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleGet(int $topicId, string $thanksCacheKey): ResponseInterface
    {
        if (IS_GUEST && !config()->get('tracker.tor_thanks_list_guests')) {
            return $this->error(__('NEED_TO_LOGIN_FIRST'));
        }

        $cachedThanks = $this->getThanksList($topicId, $thanksCacheKey);
        $userList = [];
        foreach ($cachedThanks as $row) {
            $userList[] = '<b>' . profile_url($row) . ' <i>(' . bb_date($row['time']) . ')</i></b>';
        }

        return $this->response([
            'html' => implode(', ', $userList) ?: __('NO_LIKES'),
            'mode' => 'get',
        ]);
    }

    /**
     * @throws BindingResolutionException
     */
    private function getThanksList(int $topicId, string $thanksCacheKey): array
    {
        if (!$cachedThanks = CACHE('bb_cache')->get($thanksCacheKey)) {
            $cachedThanks = [];
            $sql = DB()->fetch_rowset('SELECT u.username, u.user_rank, u.user_id, thx.* FROM ' . BB_THX . ' thx, ' . BB_USERS . " u WHERE thx.topic_id = $topicId AND thx.user_id = u.user_id");

            foreach ($sql as $row) {
                $cachedThanks[$row['user_id']] = [
                    'user_id' => $row['user_id'],
                    'username' => $row['username'],
                    'user_rank' => $row['user_rank'],
                    'time' => $row['time'],
                ];
            }

            if (!empty($cachedThanks)) {
                CACHE('bb_cache')->set($thanksCacheKey, $cachedThanks, self::CACHE_LIFETIME);
            }
        }

        return $cachedThanks;
    }
}
