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
use TorrentPier\Torrent\Moderation;

/**
 * Change Torrent Status Controller
 *
 * Handles torrent status changes and status reply notifications.
 */
class ChangeTorStatusController
{
    use AjaxResponse;

    protected string $action = 'change_tor_status';

    /**
     * @throws BindingResolutionException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];

        $topicId = (int)($body['topic_id'] ?? 0);
        if (!$topicId) {
            return $this->error(__('EMPTY_TOPIC_ID'));
        }

        $mode = $this->requireMode($body);
        if ($mode instanceof ResponseInterface) {
            return $mode;
        }

        $comment = false;
        if (config()->get('tor_comment')) {
            $comment = (string)($body['comment'] ?? '');
        }

        $tor = DB()->fetch_row('
            SELECT
                tor.poster_id, tor.forum_id, tor.topic_id, tor.tor_status, tor.checked_time, tor.checked_user_id, f.cat_id, t.topic_title
            FROM       ' . BB_BT_TORRENTS . ' tor
            INNER JOIN ' . BB_FORUMS . ' f ON(f.forum_id = tor.forum_id)
            INNER JOIN ' . BB_TOPICS . " t ON(t.topic_id = tor.topic_id)
            WHERE tor.topic_id = {$topicId}
            LIMIT 1
        ");

        if (!$tor) {
            return $this->error(__('TORRENT_FAILED'));
        }

        return match ($mode) {
            'status' => $this->handleStatus($body, $topicId, $tor, $comment),
            'status_reply' => $this->handleStatusReply($tor, $comment),
            default => $this->error('Invalid mode: ' . $mode),
        };
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleStatus(array $body, int $topicId, array $tor, string|false $comment): ResponseInterface
    {
        $newStatus = (int)($body['status'] ?? -1);

        // Check status validity
        if (!isset(__('TOR_STATUS_NAME')[$newStatus])) {
            return $this->error(__('TOR_STATUS_FAILED'));
        }

        if (!isset($body['status'])) {
            return $this->error(__('TOR_DONT_CHANGE'));
        }

        if (!IS_AM) {
            return $this->error(__('NOT_MODERATOR'));
        }

        // Error if same status
        if ($tor['tor_status'] == $newStatus) {
            return $this->error(__('TOR_STATUS_DUB'));
        }

        // Prohibition on changing/assigning CH-status by moderator
        if ($newStatus == TOR_CLOSED_CPHOLD && !IS_ADMIN) {
            return $this->error(__('TOR_DONT_CHANGE'));
        }

        // Check rights to change status
        if ($tor['tor_status'] == TOR_CLOSED_CPHOLD) {
            if (!IS_ADMIN) {
                if ($error = $this->verifyModRights($tor['forum_id'])) {
                    return $error;
                }
            }
            DB()->query('UPDATE ' . BB_TOPICS . ' SET topic_status = ' . TOPIC_UNLOCKED . " WHERE topic_id = {$tor['topic_id']} LIMIT 1");
        } else {
            if ($error = $this->verifyModRights($tor['forum_id'])) {
                return $error;
            }
        }

        // Confirmation of status change set by another moderator
        if ($tor['tor_status'] != TOR_NOT_APPROVED && $tor['checked_user_id'] != userdata('user_id') && $tor['checked_time'] + 2 * 3600 > TIMENOW) {
            if (empty($body['confirmed'])) {
                $msg = __('TOR_STATUS_OF') . ' ' . __('TOR_STATUS_NAME')[$tor['tor_status']] . "\n\n";
                $msg .= ($username = get_username($tor['checked_user_id'])) ? __('TOR_STATUS_CHANGED') . html_entity_decode($username) . ', ' . humanTime($tor['checked_time']) . __('TOR_BACK') . "\n\n" : '';
                $msg .= __('PROCEED') . '?';

                return $this->promptConfirm($msg);
            }
        }

        Moderation::changeStatus($topicId, $newStatus);

        // Log action
        $logMsg = \sprintf(__('TOR_STATUS_LOG_ACTION'), config()->get('tracker.tor_icons')[$newStatus] . ' <b> ' . __('TOR_STATUS_NAME')[$newStatus] . '</b>', config()->get('tracker.tor_icons')[$tor['tor_status']] . ' <b> ' . __('TOR_STATUS_NAME')[$tor['tor_status']] . '</b>');
        if ($comment && $comment != __('COMMENT')) {
            $logMsg .= '<br/>' . __('COMMENT') . ": <b>$comment</b>.";
        }
        log_action()->mod('mod_topic_change_tor_status', [
            'forum_id' => $tor['forum_id'],
            'topic_id' => $tor['topic_id'],
            'topic_title' => $tor['topic_title'],
            'log_msg' => $logMsg . '<br/>-------------',
        ]);

        $status = config()->get('tracker.tor_icons')[$newStatus] . ' <b> ' . __('TOR_STATUS_NAME')[$newStatus] . '</b> &middot; ' . profile_url(userdata()) . ' &middot; <i>' . humanTime(TIMENOW) . __('TOR_BACK') . '</i>';

        if (config()->get('tor_comment') && (($comment && $comment != __('COMMENT')) || \in_array($newStatus, config()->get('tracker.tor_reply')))) {
            if ($tor['poster_id'] > 0) {
                $subject = \sprintf(__('TOR_MOD_TITLE'), $tor['topic_title']);
                $message = \sprintf(__('TOR_MOD_MSG'), get_username($tor['poster_id']), make_url(TOPIC_URL . $tor['topic_id']), config()->get('tracker.tor_icons')[$newStatus] . ' ' . __('TOR_STATUS_NAME')[$newStatus]);

                if ($comment && $comment != __('COMMENT')) {
                    $message .= "\n\n[b]" . __('COMMENT') . '[/b]: ' . $comment;
                }

                send_pm($tor['poster_id'], $subject, $message, userdata('user_id'));
                Sessions::cache_rm_user_sessions($tor['poster_id']);
            }
        }

        return $this->response([
            'topic_id' => $topicId,
            'status' => $status,
        ]);
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleStatusReply(array $tor, string|false $comment): ResponseInterface
    {
        if (!config()->get('tor_comment')) {
            return $this->error(__('MODULE_OFF'));
        }

        $subject = \sprintf(__('TOR_AUTH_TITLE'), $tor['topic_title']);
        $message = \sprintf(__('TOR_AUTH_MSG'), get_username($tor['checked_user_id']), make_url(TOPIC_URL . $tor['topic_id']), $tor['topic_title']);

        if ($comment && $comment != __('COMMENT')) {
            $message .= "\n\n[b]" . __('COMMENT') . '[/b]: ' . $comment;
        }

        send_pm($tor['checked_user_id'], $subject, $message, userdata('user_id'));
        Sessions::cache_rm_user_sessions($tor['checked_user_id']);

        return $this->response([
            'topic_id' => $tor['topic_id'],
        ]);
    }
}
