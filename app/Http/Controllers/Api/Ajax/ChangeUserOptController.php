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
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TorrentPier\Sessions;

/**
 * Change User Options Controller
 *
 * Handles user bitfield options updates.
 */
class ChangeUserOptController
{
    use AjaxResponse;

    protected string $action = 'change_user_opt';

    /**
     * @throws BindingResolutionException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];

        $userId = (int)($body['user_id'] ?? 0);

        try {
            $newOpt = json_decode($body['user_opt'] ?? '', true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $this->error('invalid new_opt');
        }

        if (!$userId || !$uData = get_userdata($userId)) {
            return $this->error(__('NO_USER_ID_SPECIFIED'));
        }

        if (!\is_array($newOpt)) {
            return $this->error('invalid new_opt');
        }

        foreach (bitfields('user_opt') as $optName => $optBit) {
            if (isset($newOpt[$optName])) {
                setbit($uData['user_opt'], $optBit, !empty($newOpt[$optName]));
            }
        }

        DB()->query('UPDATE ' . BB_USERS . " SET user_opt = {$uData['user_opt']} WHERE user_id = $userId LIMIT 1");

        Sessions::cache_rm_user_sessions($userId);

        return $this->response([
            'resp_html' => __('SAVED'),
        ]);
    }
}
