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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TorrentPier\Legacy\BBCode;

/**
 * View Post Controller
 *
 * Returns post content (HTML or raw text) for AJAX display.
 */
class ViewPostController
{
    use AjaxResponse;

    protected string $action = 'view_post';

    public function __construct(
        private readonly BBCode $bbcode,
    ) {}

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];

        $postId = isset($body['post_id']) ? (int)$body['post_id'] : null;
        $topicId = isset($body['topic_id']) ? (int)$body['topic_id'] : null;
        $returnText = config()->get('show_post_bbcode_button.enabled')
            && isset($body['return_text'])
            && $body['return_text'];

        if ($postId === null) {
            $postId = DB()->fetch_row(
                'SELECT topic_first_post_id FROM ' . BB_TOPICS . " WHERE topic_id = $topicId",
                'topic_first_post_id',
            );
        }

        $postTextSql = $returnText
            ? 'pt.post_text,'
            : 'IF(h.post_html IS NULL, pt.post_text, NULL) AS post_text,';

        $sql = "
            SELECT
                p.*,
                h.post_html, {$postTextSql}
                f.auth_read
            FROM " . BB_POSTS . ' p
            INNER JOIN ' . BB_POSTS_TEXT . ' pt ON(pt.post_id = p.post_id)
            LEFT JOIN ' . BB_POSTS_HTML . ' h ON(h.post_id = pt.post_id)
            INNER JOIN ' . BB_FORUMS . " f ON(f.forum_id = p.forum_id)
            WHERE p.post_id = {$postId}
            LIMIT 1
        ";

        if (!$postData = DB()->fetch_row($sql)) {
            return $this->error(__('TOPIC_POST_NOT_EXIST'));
        }

        // Auth check
        if ($postData['auth_read'] == AUTH_REG) {
            if (IS_GUEST) {
                return $this->error(__('NEED_TO_LOGIN_FIRST'));
            }
        } elseif ($postData['auth_read'] != AUTH_ALL) {
            $isAuth = auth(AUTH_READ, $postData['forum_id'], user()->data, $postData);
            if (!$isAuth['auth_read']) {
                return $this->error(__('TOPIC_POST_NOT_EXIST'));
            }
        }

        $response = [
            'post_id' => $postId,
            'topic_id' => $topicId,
        ];

        if ($returnText) {
            $response['post_text'] = $postData['post_text'];
        } else {
            $response['post_html'] = $this->bbcode->getParsedPost($postData);
        }

        return $this->response($response);
    }
}
