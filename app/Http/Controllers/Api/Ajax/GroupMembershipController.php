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
 * Group Membership Controller
 *
 * Handles group membership list retrieval.
 */
class GroupMembershipController
{
    use AjaxResponse;

    protected string $action = 'group_membership';

    /**
     * @throws BindingResolutionException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];

        $userId = (int)($body['user_id'] ?? 0);
        if (!$userId || !get_userdata($userId)) {
            return $this->error(__('NO_USER_ID_SPECIFIED'));
        }

        $mode = $this->requireMode($body);
        if ($mode instanceof ResponseInterface) {
            return $mode;
        }

        return match ($mode) {
            'get_group_list' => $this->handleGetGroupList($userId),
            default => $this->error("invalid mode: $mode"),
        };
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleGetGroupList(int $userId): ResponseInterface
    {
        $sql = '
            SELECT ug.user_pending, g.group_id, g.group_type, g.group_name, g.group_moderator, self.user_id AS can_view
            FROM       ' . BB_USER_GROUP . ' ug
            INNER JOIN ' . BB_GROUPS . ' g ON(g.group_id = ug.group_id AND g.group_single_user = 0)
             LEFT JOIN ' . BB_USER_GROUP . ' self ON(self.group_id = g.group_id AND self.user_id = ' . user()->id . " AND self.user_pending = 0)
            WHERE ug.user_id = {$userId}
            ORDER BY g.group_name
        ";

        $html = [];
        foreach (DB()->fetch_rowset($sql) as $row) {
            $class = ($row['user_pending']) ? 'med' : 'med bold';
            $class .= ($row['group_moderator'] == $userId) ? ' colorMod' : '';
            $groupName = $row['group_name'];

            if (IS_ADMIN) {
                $href = url()->group($row['group_id'], $groupName, [POST_USERS_URL => $userId]);
            } else {
                // Hidden group and the user himself is not a member of it
                if ($row['group_type'] == GROUP_HIDDEN && !$row['can_view']) {
                    continue;
                }
                $params = [];
                if ($row['group_moderator'] == user()->id) {
                    // The user himself is the moderator of this group
                    $class .= ' selfMod';
                    $params[POST_USERS_URL] = $userId;
                }
                $href = url()->group($row['group_id'], $groupName, $params);
            }
            $link = '<a href="' . $href . '" class="' . $class . '" target="_blank">' . htmlCHR($groupName) . '</a>';
            $html[] = $link;
        }

        $groupListHtml = $html
            ? '<ul><li>' . implode('</li><li>', $html) . '</li></ul>'
            : __('GROUP_LIST_HIDDEN');

        return $this->response([
            'group_list_html' => $groupListHtml,
        ]);
    }
}
