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
 * Avatar Controller
 *
 * Handles avatar management (delete).
 */
class AvatarController
{
    use AjaxResponse;

    protected string $action = 'avatar';

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

        $userId = (int)($body['user_id'] ?? 0);
        if (!$userId || !$uData = get_userdata($userId)) {
            return $this->error(__('NO_USER_ID_SPECIFIED'));
        }

        if (!IS_ADMIN && $userId != user()->id) {
            return $this->error(__('NOT_AUTHORISED'));
        }

        $newExtId = 0;

        switch ($mode) {
            case 'delete':
                delete_avatar($userId, $uData['avatar_ext_id']);
                $response = get_avatar($userId, $newExtId);
                break;
            default:
                return $this->error('Invalid mode: ' . $mode);
        }

        DB()->query('UPDATE ' . BB_USERS . " SET avatar_ext_id = $newExtId WHERE user_id = $userId LIMIT 1");

        Sessions::cache_rm_user_sessions($userId);

        return $this->response([
            'avatar_html' => $response,
        ]);
    }
}
