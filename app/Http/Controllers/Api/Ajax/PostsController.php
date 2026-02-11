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
use TorrentPier\Legacy\Admin\Common;
use TorrentPier\Legacy\BBCode;
use TorrentPier\Legacy\Post;
use TorrentPier\Topic\Guard;

/**
 * Posts Controller
 *
 * Handles post operations: delete, reply, view_message, edit, add.
 */
class PostsController
{
    use AjaxResponse;

    protected string $action = 'posts';

    public function __construct(
        private readonly BBCode $bbcode,
    ) {}

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];

        if (!isset($body['type'])) {
            return $this->error('empty type');
        }

        $type = $body['type'];
        $postId = null;
        $topicId = null;
        $post = null;
        $isAuth = null;

        if (isset($body['post_id'])) {
            $postId = (int)$body['post_id'];
            $post = DB()->fetch_row('SELECT
                    t.topic_id, t.topic_title, t.topic_status, t.topic_first_post_id, t.topic_last_post_id, t.forum_id,
                    p.post_id, p.poster_id, p.post_time, p.post_username, p.post_anonymous,
                    pt.post_text,
                    f.allow_anonymous
                FROM ' . BB_TOPICS . ' t, ' . BB_FORUMS . ' f, ' . BB_POSTS . ' p, ' . BB_POSTS_TEXT . " pt
                WHERE p.post_id = {$postId}
                    AND t.topic_id = p.topic_id
                    AND f.forum_id = t.forum_id
                    AND p.post_id  = pt.post_id
                LIMIT 1");

            if (!$post) {
                return $this->error(__('TOPIC_POST_NOT_EXIST'));
            }

            $isAuth = auth(AUTH_ALL, $post['forum_id'], userdata());
            if ($post['topic_status'] == TOPIC_LOCKED && !$isAuth['auth_mod']) {
                return $this->error(__('TOPIC_LOCKED'));
            }
        } elseif (isset($body['topic_id'])) {
            $topicId = (int)$body['topic_id'];
            $post = DB()->fetch_row('SELECT t.topic_id, t.topic_title, t.topic_status, t.forum_id, f.allow_anonymous
                FROM ' . BB_TOPICS . ' t
                LEFT JOIN ' . BB_FORUMS . " f ON f.forum_id = t.forum_id
                WHERE t.topic_id = {$topicId}
                LIMIT 1");

            if (!$post) {
                return $this->error(__('INVALID_TOPIC_ID_DB'));
            }

            $isAuth = auth(AUTH_ALL, $post['forum_id'], userdata());
        }

        return match ($type) {
            'delete' => $this->handleDelete($body, $post, $postId, $isAuth),
            'reply' => $this->handleReply($post, $postId, $isAuth),
            'view_message' => $this->handleViewMessage($body),
            'edit', 'editor' => $this->handleEdit($body, $post, $postId, $isAuth, $type),
            'add' => $this->handleAdd($body, $post, $topicId, $isAuth),
            default => $this->error('empty type'),
        };
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleDelete(array $body, array $post, int $postId, array $isAuth): ResponseInterface
    {
        if ($post['post_id'] != $post['topic_first_post_id']
            && $isAuth['auth_delete']
            && ($isAuth['auth_mod']
                || (Guard::isAuthor($post['poster_id'])
                    && $post['topic_last_post_id'] == $post['post_id']
                    && $post['post_time'] + 3600 * 3 > TIMENOW))
        ) {
            if (empty($body['confirmed'])) {
                return $this->promptConfirm(__('CONFIRM_DELETE'));
            }

            Common::post_delete($postId);

            return $this->response([
                'hide' => true,
                'post_id' => $postId,
            ]);
        }

        return $this->error(\sprintf(__('SORRY_AUTH_DELETE'), strip_tags($isAuth['auth_delete_type'])));
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleReply(array $post, int $postId, array $isAuth): ResponseInterface
    {
        if (bf(userdata('user_opt'), 'user_opt', 'dis_post')) {
            return $this->error(__('RULES_REPLY_CANNOT'));
        }

        if (!$isAuth['auth_reply']) {
            return $this->error(\sprintf(__('SORRY_AUTH_REPLY'), strip_tags($isAuth['auth_reply_type'])));
        }

        if (!empty($post['post_anonymous']) && !IS_AM) {
            $quoteUsername = __('ANONYMOUS');
        } else {
            $quoteUsername = ($post['post_username'] != '') ? $post['post_username'] : get_username($post['poster_id']);
        }
        $message = '[quote="' . $quoteUsername . '"][qpost=' . $post['post_id'] . ']' . $post['post_text'] . "[/quote]\r";

        // Hide user passkey
        $message = preg_replace('#(?<=[?&;]' . config()->get('tracker.passkey_key') . '=)[a-zA-Z0-9]#', 'passkey', $message);
        // Hide sid
        $message = preg_replace('#(?<=[?&;]sid=)[a-zA-Z0-9]#', 'sid', $message);

        $message = censor()->censorString($message);

        if ($post['post_id'] == $post['topic_first_post_id']) {
            $message = '[quote]' . $post['topic_title'] . "[/quote]\r";
        }

        $response = [
            'quote' => true,
            'message' => $message,
        ];

        if (mb_strlen($message, DEFAULT_CHARSET) > 1000) {
            $response['redirect'] = make_url(POSTING_URL . '?mode=quote&' . POST_POST_URL . '=' . $postId);
        }

        return $this->response($response);
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleViewMessage(array $body): ResponseInterface
    {
        $message = (string)($body['message'] ?? '');
        if (!trim($message)) {
            return $this->error(__('EMPTY_MESSAGE'));
        }

        $message = htmlCHR($message, false, ENT_NOQUOTES);

        return $this->response([
            'message_html' => $this->bbcode->toHtml($message),
            'res_id' => $body['res_id'] ?? null,
        ]);
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleEdit(array $body, array $post, int $postId, array $isAuth, string $type): ResponseInterface
    {
        if (bf(userdata('user_opt'), 'user_opt', 'dis_post_edit')) {
            return $this->error(__('POST_EDIT_CANNOT'));
        }

        if ($post['poster_id'] == userdata('user_id')) {
            if (!$isAuth['auth_edit']) {
                return $this->error(\sprintf(__('SORRY_AUTH_EDIT'), strip_tags($isAuth['auth_edit_type'])));
            }
        } elseif (!$isAuth['auth_mod']) {
            return $this->error(__('EDIT_OWN_POSTS'));
        }

        if ($post['topic_status'] == TOPIC_LOCKED && !$isAuth['auth_mod']) {
            return $this->error(__('TOPIC_LOCKED'));
        }

        if ((mb_strlen($post['post_text'], DEFAULT_CHARSET) > 1000) || ($post['topic_first_post_id'] == $postId)) {
            return $this->response([
                'redirect' => make_url(POSTING_URL . '?mode=editpost&' . POST_POST_URL . '=' . $postId),
                'post_id' => $postId,
            ]);
        }

        if ($type === 'editor') {
            return $this->handleEditorSubmit($body, $post, $postId);
        }

        return $this->handleEditorForm($post, $postId);
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleEditorSubmit(array $body, array $post, int $postId): ResponseInterface
    {
        $text = (string)($body['text'] ?? '');
        $text = $this->bbcode->prepareMessage($text);

        if (mb_strlen($text) <= 2) {
            return $this->error(__('EMPTY_MESSAGE'));
        }

        if ($text != $post['post_text']) {
            if (config()->get('forum.max_smilies')) {
                $countSmilies = substr_count($this->bbcode->toHtml($text), '<img class="smile" src="' . config()->get('smilies_path'));
                if ($countSmilies > config()->get('forum.max_smilies')) {
                    return $this->error(\sprintf(__('MAX_SMILIES_PER_POST'), config()->get('forum.max_smilies')));
                }
            }

            DB()->query('UPDATE ' . BB_POSTS_TEXT . " SET post_text = '" . DB()->escape($text) . "' WHERE post_id = $postId LIMIT 1");

            if ($post['topic_last_post_id'] != $post['post_id'] && Guard::isAuthor($post['poster_id'])) {
                DB()->query('UPDATE ' . BB_POSTS . " SET post_edit_time = '" . TIMENOW . "', post_edit_count = post_edit_count + 1 WHERE post_id = $postId LIMIT 1");
            }

            $sText = str_replace('\n', "\n", $text);
            $sTopicTitle = str_replace('\n', "\n", $post['topic_title']);
            add_search_words($postId, stripslashes($sText), stripslashes($sTopicTitle));
            update_post_html([
                'post_id' => $postId,
                'post_text' => $text,
            ]);

            // Manticore [Post update]
            sync_post_to_manticore($postId, $text, $post['topic_title'], $post['topic_id'], $post['forum_id']);
        }

        return $this->response([
            'html' => $this->bbcode->toHtml($text),
            'post_id' => $postId,
        ]);
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleEditorForm(array $post, int $postId): ResponseInterface
    {
        $hiddenForm = '<input type="hidden" name="mode" value="editpost" />';
        $hiddenForm .= '<input type="hidden" name="' . POST_POST_URL . '" value="' . $postId . '" />';
        $hiddenForm .= '<input type="hidden" name="subject" value="' . $post['topic_title'] . '" />';

        $text = '
            <form action="' . POSTING_URL . '" method="post" name="post">
                ' . $hiddenForm . '
                <div class="buttons mrg_4">
                    <input type="button" value="B" name="codeB" title="' . __('BOLD') . '" style="font-weight: bold;" />
                    <input type="button" value="i" name="codeI" title="' . __('ITALIC') . '" style="font-style: italic;" />
                    <input type="button" value="u" name="codeU" title="' . __('UNDERLINE') . '" style="text-decoration: underline;" />
                    <input type="button" value="s" name="codeS" title="' . __('STRIKEOUT') . '" style="text-decoration: line-through;" />&nbsp;&nbsp;
                    <input type="button" value="' . __('QUOTE') . '" name="codeQuote" title="' . __('QUOTE_TITLE') . '" />
                    <input type="button" value="Img" name="codeImg" title="' . __('IMG_TITLE') . '" />
                    <input type="button" value="' . __('URL') . '" name="codeUrl" title="' . __('URL_TITLE') . '" style="text-decoration: underline;" />&nbsp;
                    <input type="button" value="' . __('CODE') . '" name="codeCode" title="' . __('CODE_TITLE') . '" />
                    <input type="button" value="' . __('LIST') . '" name="codeList" title="' . __('LIST_TITLE') . '" />
                    <input type="button" value="1." name="codeOpt" title="' . __('LIST_ITEM') . '" />&nbsp;
                    <input type="button" value="' . __('QUOTE_SEL') . '" name="quoteselected" title="' . __('QUOTE_SELECTED') . '" onclick="bbcode.onclickQuoteSel();" />&nbsp;
                </div>
                <textarea id="message-' . $postId . '" class="editor mrg_4" name="message" rows="18" cols="92">' . $post['post_text'] . '</textarea>
                <div class="mrg_4 tCenter">
                    <input title="Alt+Enter" name="preview" type="submit" value="' . __('PREVIEW') . '">
                    <input type="button" onclick="edit_post(' . $postId . ')" value="' . __('CANCEL') . '">
                    <input type="button" onclick="edit_post(' . $postId . ', \'editor\', $(\'#message-' . $postId . '\').val()); return false;" class="bold" value="' . __('SUBMIT') . '">
                </div><hr/>
                <script type="text/javascript">
                var bbcode = new BBCode("message-' . $postId . '");
                var ctrl = "ctrl";

                bbcode.addTag("codeB", "b", null, "B", ctrl);
                bbcode.addTag("codeI", "i", null, "I", ctrl);
                bbcode.addTag("codeU", "u", null, "U", ctrl);
                bbcode.addTag("codeS", "s", null, "S", ctrl);

                bbcode.addTag("codeQuote", "quote", null, "Q", ctrl);
                bbcode.addTag("codeImg", "img", null, "R", ctrl);
                bbcode.addTag("codeUrl", "url", "/url", "W", ctrl);

                bbcode.addTag("codeCode", "code", null, "K", ctrl);
                bbcode.addTag("codeList",  "list", null, "L", ctrl);
                bbcode.addTag("codeOpt", "*", "", "0", ctrl);
                </script>
            </form>';

        return $this->response([
            'text' => $text,
            'post_id' => $postId,
        ]);
    }

    /**
     * @throws BindingResolutionException
     */
    private function handleAdd(array $body, array $post, ?int $topicId, ?array $isAuth): ResponseInterface
    {
        if (!isset($body['topic_id'])) {
            return $this->error(__('INVALID_TOPIC_ID'));
        }

        if (bf(userdata('user_opt'), 'user_opt', 'dis_post')) {
            return $this->error(__('RULES_REPLY_CANNOT'));
        }

        if (!$isAuth['auth_reply']) {
            return $this->error(\sprintf(__('SORRY_AUTH_REPLY'), strip_tags($isAuth['auth_reply_type'])));
        }

        if ($post['topic_status'] == TOPIC_LOCKED && !$isAuth['auth_mod']) {
            return $this->error(__('TOPIC_LOCKED'));
        }

        $message = (string)($body['message'] ?? '');
        $message = $this->bbcode->prepareMessage($message);

        // Flood control
        $whereSql = IS_GUEST ? "p.poster_ip = '" . USER_IP . "'" : 'p.poster_id = ' . userdata('user_id');
        $sql = 'SELECT MAX(p.post_time) AS last_post_time FROM ' . BB_POSTS . " p WHERE $whereSql";
        $row = DB()->fetch_row($sql);

        if ($row && $row['last_post_time']) {
            if (userdata('user_level') == USER) {
                if ((TIMENOW - $row['last_post_time']) < config()->get('flood_interval')) {
                    return $this->error(__('FLOOD_ERROR'));
                }
            }
        }

        // Double Post Control
        if (!empty($row['last_post_time']) && !IS_AM) {
            $sql = '
                SELECT pt.post_text
                FROM ' . BB_POSTS . ' p, ' . BB_POSTS_TEXT . " pt
                WHERE {$whereSql}
                    AND p.post_time = " . (int)$row['last_post_time'] . '
                    AND pt.post_id = p.post_id
                LIMIT 1
            ';

            if ($lastRow = DB()->fetch_row($sql)) {
                $lastMsg = DB()->escape($lastRow['post_text']);
                if ($lastMsg == $message) {
                    return $this->error(__('DOUBLE_POST_ERROR'));
                }
            }
        }

        if (config()->get('forum.max_smilies')) {
            $countSmilies = substr_count($this->bbcode->toHtml($message), '<img class="smile" src="' . config()->get('smilies_path'));
            if ($countSmilies > config()->get('forum.max_smilies')) {
                return $this->error(\sprintf(__('MAX_SMILIES_PER_POST'), config()->get('forum.max_smilies')));
            }
        }

        // Anonymous posting support
        $anonymousMode = 0;
        if (!IS_GUEST) {
            $forumAllowsAnonymous = !empty($post['allow_anonymous']) || config()->get('forum.allow_anonymous_posting');
            if ($forumAllowsAnonymous && !empty($body['anonymous'])) {
                $anonymousMode = 1;
            }
        }

        DB()->sql_query('INSERT INTO ' . BB_POSTS . " (topic_id, forum_id, poster_id, post_time, poster_ip, post_anonymous) VALUES ($topicId, " . $post['forum_id'] . ', ' . userdata('user_id') . ", '" . TIMENOW . "', '" . USER_IP . "', {$anonymousMode})");
        $postId = DB()->sql_nextid();
        DB()->sql_query('INSERT INTO ' . BB_POSTS_TEXT . " (post_id, post_text) VALUES ($postId, '" . DB()->escape($message) . "')");

        Post::update_post_stats('reply', $post, $post['forum_id'], $topicId, $postId, userdata('user_id'));

        $sMessage = str_replace('\n', "\n", $message);
        $sTopicTitle = str_replace('\n', "\n", $post['topic_title']);
        add_search_words($postId, stripslashes($sMessage), stripslashes($sTopicTitle));
        update_post_html([
            'post_id' => $postId,
            'post_text' => $message,
        ]);

        if (config()->get('mail.notifications.topic_notify')) {
            $notify = !empty($body['notify']);
            Post::user_notification('reply', $post, $post['topic_title'], $post['forum_id'], $topicId, $notify);
        }

        // Manticore [Post create]
        sync_post_to_manticore($postId, $message, $post['topic_title'], $topicId, $post['forum_id']);

        return $this->response([
            'redirect' => make_url(POST_URL . "$postId#$postId"),
        ]);
    }
}
