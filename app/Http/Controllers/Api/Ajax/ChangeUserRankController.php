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
use TorrentPier\Sessions;

/**
 * Change User Rank Controller
 *
 * Handles user rank changes by admin.
 */
class ChangeUserRankController
{
    use AjaxResponse;

    protected string $action = 'change_user_rank';

    /**
     * @throws BindingResolutionException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];

        if (!$ranks = datastore()->get('ranks')) {
            datastore()->update('ranks');
            $ranks = datastore()->get('ranks');
        }

        $rankId = (int)($body['rank_id'] ?? 0);
        $userId = (int)($body['user_id'] ?? 0);

        if (!$userId || !get_userdata($userId)) {
            return $this->error(__('NO_USER_ID_SPECIFIED'));
        }

        if ($rankId != 0 && !isset($ranks[$rankId])) {
            return $this->error("invalid rank_id: $rankId");
        }

        DB()->query('UPDATE ' . BB_USERS . " SET user_rank = $rankId WHERE user_id = $userId LIMIT 1");

        Sessions::cache_rm_user_sessions($userId);

        $userRank = $rankId ? '<span class="' . $ranks[$rankId]['rank_style'] . '">' . $ranks[$rankId]['rank_title'] . '</span>' : '';

        return $this->response([
            'html' => $rankId ? __('AWARDED_RANK') . "<b> $userRank </b>" : __('SHOT_RANK'),
            'rank_name' => $rankId ? $userRank : __('USER'),
        ]);
    }
}
