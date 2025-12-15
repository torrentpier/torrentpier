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
use TorrentPier\Legacy\Group;

/**
 * Manage Group Controller
 *
 * Handles group profile editing.
 */
class ManageGroupController
{
    use AjaxResponse;

    protected string $action = 'manage_group';

    /**
     * @throws BindingResolutionException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];

        $groupId = (int)($body['group_id'] ?? 0);
        if (!$groupId || !$groupInfo = Group::get_group_data($groupId)) {
            return $this->error(__('NO_GROUP_ID_SPECIFIED'));
        }

        $mode = $this->requireMode($body);
        if ($mode instanceof ResponseInterface) {
            return $mode;
        }

        $value = (string)($body['value'] ?? '0');

        if (!IS_ADMIN && userdata('user_id') != $groupInfo['group_moderator']) {
            return $this->error(__('ONLY_FOR_MOD'));
        }

        $responseData = [];

        switch ($mode) {
            case 'group_name':
            case 'group_signature':
            case 'group_description':
                $value = htmlCHR($value, false, ENT_NOQUOTES);
                $responseData['new_value'] = $value;
                break;

            case 'release_group':
            case 'group_type':
                $responseData['new_value'] = $value;
                break;

            case 'delete_avatar':
                delete_avatar(GROUP_AVATAR_MASK . $groupId, $groupInfo['avatar_ext_id']);
                $value = '0';
                $mode = 'avatar_ext_id';
                $responseData['remove_avatar'] = get_avatar(GROUP_AVATAR_MASK . $groupId, (int)$value);
                break;

            default:
                return $this->error('Unknown mode');
        }

        $valueSql = DB()->escape($value, true);
        DB()->query('UPDATE ' . BB_GROUPS . " SET $mode = $valueSql WHERE group_id = $groupId LIMIT 1");

        return $this->response($responseData);
    }
}
