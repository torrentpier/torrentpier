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
use TorrentPier\Legacy\Admin\Common;
use TorrentPier\Sessions;

/**
 * Manage User Controller
 *
 * Handles admin user management operations.
 */
class ManageUserController
{
    use AjaxResponse;

    protected string $action = 'manage_user';

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

        $userId = $this->requireUserId($body);
        if ($userId instanceof ResponseInterface) {
            return $userId;
        }

        switch ($mode) {
            case 'delete_profile':
                if (userdata('user_id') == $userId) {
                    return $this->error(__('USER_DELETE_ME'));
                }
                if (empty($body['confirmed'])) {
                    return $this->promptConfirm(__('USER_DELETE_CONFIRM'));
                }

                if (!\in_array($userId, explode(',', EXCLUDED_USERS))) {
                    Sessions::delete_user_sessions($userId);
                    Common::user_delete($userId);
                    $userId = userdata('user_id');
                    $info = __('USER_DELETED');
                } else {
                    return $this->error(__('USER_DELETE_CSV'));
                }
                break;

            case 'delete_topics':
                if (userdata('user_id') == $userId) {
                    return $this->promptConfirm(__('DELETE_USER_POSTS_ME'));
                }
                if (empty($body['confirmed'])) {
                    return $this->promptConfirm(__('DELETE_USER_ALL_POSTS_CONFIRM'));
                }

                $userTopics = DB()->fetch_rowset('SELECT topic_id FROM ' . BB_TOPICS . " WHERE topic_poster = $userId", 'topic_id');
                Common::topic_delete($userTopics);
                Common::post_delete('user', $userId);
                $info = __('USER_DELETED_POSTS');
                break;

            case 'delete_message':
                if (userdata('user_id') == $userId) {
                    return $this->promptConfirm(__('DELETE_USER_POSTS_ME'));
                }
                if (empty($body['confirmed'])) {
                    return $this->promptConfirm(__('DELETE_USER_POSTS_CONFIRM'));
                }

                Common::post_delete('user', $userId);
                $info = __('USER_DELETED_POSTS');
                break;

            case 'user_activate':
                if (empty($body['confirmed'])) {
                    return $this->promptConfirm(__('DEACTIVATE_CONFIRM'));
                }

                DB()->query('UPDATE ' . BB_USERS . ' SET user_active = 1 WHERE user_id = ' . $userId);
                $info = __('USER_ACTIVATE_ON');
                break;

            case 'user_deactivate':
                if (userdata('user_id') == $userId) {
                    return $this->error(__('USER_DEACTIVATE_ME'));
                }
                if (empty($body['confirmed'])) {
                    return $this->promptConfirm(__('ACTIVATE_CONFIRM'));
                }

                DB()->query('UPDATE ' . BB_USERS . ' SET user_active = 0 WHERE user_id = ' . $userId);
                Sessions::delete_user_sessions($userId);
                $info = __('USER_ACTIVATE_OFF');
                break;

            case '2fa_disable':
                if (userdata('user_id') == $userId) {
                    return $this->error(__('TWO_FACTOR_ADMIN_DISABLE_SELF'));
                }

                if (empty($body['confirmed'])) {
                    return $this->promptConfirm(__('TWO_FACTOR_ADMIN_DISABLE_CONFIRM'));
                }

                two_factor()->disableForUser($userId);
                Sessions::delete_user_sessions($userId);
                $info = __('TWO_FACTOR_DISABLED_SUCCESS');
                break;

            default:
                return $this->error('Invalid mode');
        }

        return $this->response([
            'mode' => $mode,
            'info' => $info,
            'url' => html_entity_decode(make_url(url()->member($userId, get_username($userId)))),
        ]);
    }
}
