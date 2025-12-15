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
use TorrentPier\Legacy\BBCode;
use TorrentPier\Sessions;

/**
 * Post Mod Comment Controller
 *
 * Handles moderator comments on posts.
 */
class PostModCommentController
{
    use AjaxResponse;

    protected string $action = 'post_mod_comment';

    public function __construct(
        private readonly BBCode $bbcode,
    ) {
        $this->bbcode->boot();
    }

    /**
     * @throws BindingResolutionException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];

        $postId = (int)($body['post_id'] ?? 0);
        $mcType = (int)($body['mc_type'] ?? 0);
        $mcText = (string)($body['mc_text'] ?? '');

        if ($mcType != 0) {
            $mcText = prepare_message($mcText);
            if (!$mcText) {
                return $this->error(__('EMPTY_MESSAGE'));
            }
        }

        $post = DB()->fetch_row('
            SELECT
                p.post_id, p.poster_id
            FROM ' . BB_POSTS . " p
            WHERE p.post_id = {$postId}
        ");

        if (!$post) {
            return $this->error(__('TOPIC_POST_NOT_EXIST'));
        }

        $data = [
            'mc_comment' => ($mcType) ? $mcText : '',
            'mc_type' => $mcType,
            'mc_user_id' => ($mcType) ? userdata('user_id') : 0,
        ];
        $sqlArgs = DB()->build_array('UPDATE', $data);
        DB()->query('UPDATE ' . BB_POSTS . " SET $sqlArgs WHERE post_id = $postId");

        if ($mcType && $post['poster_id'] != userdata('user_id')) {
            $subject = \sprintf(__('MC_COMMENT_PM_SUBJECT'), __('MC_COMMENT')[$mcType]['type']);
            $message = \sprintf(
                __('MC_COMMENT_PM_MSG'),
                get_username($post['poster_id']),
                make_url(POST_URL . "$postId#$postId"),
                __('MC_COMMENT')[$mcType]['type'],
                $mcText,
            );

            send_pm($post['poster_id'], $subject, $message);
            Sessions::cache_rm_user_sessions($post['poster_id']);
        }

        $mcClass = match ($mcType) {
            1 => 'success',
            2 => 'info',
            3 => 'warning',
            4 => 'danger',
            default => '',
        };

        return $this->response([
            'mc_type' => $mcType,
            'post_id' => $postId,
            'mc_title' => \sprintf(__('MC_COMMENT')[$mcType]['title'], profile_url(userdata())),
            'mc_text' => bbcode2html($mcText),
            'mc_class' => $mcClass,
        ]);
    }
}
