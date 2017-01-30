<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $lang, $userdata;

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

if (!isset($this->request['type'])) {
    $this->ajax_die('empty type');
}
if (isset($this->request['post_id'])) {
    $post_id = (int)$this->request['post_id'];
    $post = DB()->fetch_row("SELECT t.*, f.*, p.*, pt.post_text
		FROM " . BB_TOPICS . " t, " . BB_FORUMS . " f, " . BB_POSTS . " p, " . BB_POSTS_TEXT . " pt
		WHERE p.post_id = $post_id
			AND t.topic_id = p.topic_id
			AND f.forum_id = t.forum_id
			AND p.post_id  = pt.post_id
		LIMIT 1");
    if (!$post) {
        $this->ajax_die('not post');
    }

    $is_auth = auth(AUTH_ALL, $post['forum_id'], $userdata, $post);
    if ($post['topic_status'] == TOPIC_LOCKED && !$is_auth['auth_mod']) {
        $this->ajax_die($lang['TOPIC_LOCKED']);
    }
} elseif (isset($this->request['topic_id'])) {
    $topic_id = (int)$this->request['topic_id'];
    $post = DB()->fetch_row("SELECT t.*, f.*
			FROM " . BB_TOPICS . " t, " . BB_FORUMS . " f
			WHERE t.topic_id = $topic_id
				AND f.forum_id = t.forum_id
			LIMIT 1");
    if (!$post) {
        $this->ajax_die('not post');
    }

    $is_auth = auth(AUTH_ALL, $post['forum_id'], $userdata, $post);
}

if (!defined('WORD_LIST_OBTAINED')) {
    $orig_word = array();
    $replace_word = array();
    obtain_word_list($orig_word, $replace_word);
    define('WORD_LIST_OBTAINED', true);
}

switch ($this->request['type']) {
    case 'delete':
        if ($post['post_id'] != $post['topic_first_post_id'] && $is_auth['auth_delete'] && ($is_auth['auth_mod'] || ($userdata['user_id'] == $post['poster_id'] && $post['topic_last_post_id'] == $post['post_id'] && $post['post_time'] + 3600 * 3 > TIMENOW))) {
            if (empty($this->request['confirmed'])) {
                $this->prompt_for_confirm($lang['CONFIRM_DELETE']);
            }
            post_delete($post_id);

            // Update atom feed
            update_atom('topic', (int)$this->request['topic_id']);

            $this->response['hide'] = true;
            $this->response['post_id'] = $post_id;
        } else {
            $this->ajax_die(sprintf($lang['SORRY_AUTH_DELETE'], strip_tags($is_auth['auth_delete_type'])));
        }
        break;

    case 'reply':
        if (bf($userdata['user_opt'], 'user_opt', 'dis_post')) {
            $this->ajax_die(strip_tags($lang['RULES_REPLY_CANNOT']));
        } elseif (!$is_auth['auth_reply']) {
            $this->ajax_die(sprintf($lang['SORRY_AUTH_REPLY'], strip_tags($is_auth['auth_reply_type'])));
        }

        $quote_username = ($post['post_username'] != '') ? $post['post_username'] : get_username($post['poster_id']);
        $message = "[quote=\"" . $quote_username . "\"][qpost=" . $post['post_id'] . "]" . $post['post_text'] . "[/quote]\r";

        // hide user passkey
        $message = preg_replace('#(?<=\?uk=)[a-zA-Z0-9]{10}(?=&)#', 'passkey', $message);
        // hide sid
        $message = preg_replace('#(?<=[\?&;]sid=)[a-zA-Z0-9]{12}#', 'sid', $message);

        if (!empty($orig_word)) {
            $message = (!empty($message)) ? preg_replace($orig_word, $replace_word, $message) : '';
        }

        if ($post['post_id'] == $post['topic_first_post_id']) {
            $message = "[quote]" . $post['topic_title'] . "[/quote]\r";
        }
        if (mb_strlen($message, 'UTF-8') > 1000) {
            $this->response['redirect'] = make_url(POSTING_URL . '?mode=quote&p=' . $post_id);
        }

        $this->response['quote'] = true;
        $this->response['message'] = $message;
        break;

    case 'view_message':
        $message = (string)$this->request['message'];
        if (!trim($message)) {
            $this->ajax_die($lang['EMPTY_MESSAGE']);
        }
        $message = htmlCHR($message, false, ENT_NOQUOTES);

        $this->response['message_html'] = bbcode2html($message);
        $this->response['res_id'] = $this->request['res_id'];
        break;

    case 'edit':
    case 'editor':
        if (bf($userdata['user_opt'], 'user_opt', 'dis_post_edit')) {
            $this->ajax_die($lang['POST_EDIT_CANNOT']);
        }
        if ($post['poster_id'] != $userdata['user_id'] && !$is_auth['auth_mod']) {
            $this->ajax_die($lang['EDIT_OWN_POSTS']);
        }
        if ((mb_strlen($post['post_text'], 'UTF-8') > 1000) || $post['attach_ext_id'] || ($post['topic_first_post_id'] == $post_id)) {
            $this->response['redirect'] = make_url(POSTING_URL . '?mode=editpost&p=' . $post_id);
        } elseif ($this->request['type'] == 'editor') {
            $text = (string)$this->request['text'];
            $text = prepare_message($text);

            if (mb_strlen($text) > 2) {
                if ($text != $post['post_text']) {
                    if ($di->config->get('max_smilies')) {
                        $count_smilies = substr_count(bbcode2html($text), '<img class="smile" src="' . $di->config->get('smilies_path'));
                        if ($count_smilies > $di->config->get('max_smilies')) {
                            $this->ajax_die(sprintf($lang['MAX_SMILIES_PER_POST'], $di->config->get('max_smilies')));
                        }
                    }
                    DB()->query("UPDATE " . BB_POSTS_TEXT . " SET post_text = '" . DB()->escape($text) . "' WHERE post_id = $post_id LIMIT 1");
                    if ($post['topic_last_post_id'] != $post['post_id'] && $userdata['user_id'] == $post['poster_id']) {
                        DB()->query("UPDATE " . BB_POSTS . " SET post_edit_time = '" . TIMENOW . "', post_edit_count = post_edit_count + 1 WHERE post_id = $post_id LIMIT 1");
                    }
                    $s_text = str_replace('\n', "\n", $text);
                    $s_topic_title = str_replace('\n', "\n", $post['topic_title']);
                    add_search_words($post_id, stripslashes($s_text), stripslashes($s_topic_title));
                    update_post_html(array(
                        'post_id' => $post_id,
                        'post_text' => $text,
                    ));
                }
            } else {
                $this->ajax_die($lang['EMPTY_MESSAGE']);
            }

            // Update atom feed
            update_atom('topic', (int)$this->request['topic_id']);

            $this->response['html'] = bbcode2html($text);
        } else {
            $is_auth = auth(AUTH_ALL, $post['forum_id'], $userdata, $post);
            if ($post['topic_status'] == TOPIC_LOCKED && !$is_auth['auth_mod']) {
                $this->ajax_die($lang['TOPIC_LOCKED']);
            } elseif (!$is_auth['auth_edit']) {
                $this->ajax_die(sprintf($lang['SORRY_AUTH_EDIT'], strip_tags($is_auth['auth_edit_type'])));
            }

            $hidden_form = '<input type="hidden" name="mode" value="editpost" />';
            $hidden_form .= '<input type="hidden" name="' . POST_POST_URL . '" value="' . $post_id . '" />';
            $hidden_form .= '<input type="hidden" name="subject" value="' . $post['topic_title'] . '" />';

            $this->response['text'] = '
				<form action="' . POSTING_URL . '" method="post" name="post">
					' . $hidden_form . '
					<div class="buttons mrg_4">
						<input type="button" value="B" name="codeB" title="' . $lang['BOLD'] . '" style="font-weight: bold; width: 25px;" />
						<input type="button" value="i" name="codeI" title="' . $lang['ITALIC'] . '" style="width: 25px; font-style: italic;" />
						<input type="button" value="u" name="codeU" title="' . $lang['UNDERLINE'] . '" style="width: 25px; text-decoration: underline;" />
						<input type="button" value="s" name="codeS" title="' . $lang['STRIKEOUT'] . '" style="width: 25px; text-decoration: line-through;" />&nbsp;&nbsp;
						<input type="button" value="' . $lang['QUOTE'] . '" name="codeQuote" title="' . $lang['QUOTE_TITLE'] . '" style="width: 57px;" />
						<input type="button" value="Img" name="codeImg" title="' . $lang['IMG_TITLE'] . '" style="width: 40px;" />
						<input type="button" value="' . $lang['URL'] . '" name="codeUrl" title="' . $lang['URL_TITLE'] . '" style="width: 63px; text-decoration: underline;" />&nbsp;
						<input type="button" value="' . $lang['CODE'] . '" name="codeCode" title="' . $lang['CODE_TITLE'] . '" style="width: 43px;" />
						<input type="button" value="' . $lang['LIST'] . '" name="codeList" title="' . $lang['LIST_TITLE'] . '" style="width: 60px;" />
						<input type="button" value="1." name="codeOpt" title="' . $lang['LIST_ITEM'] . '" style="width: 30px;" />&nbsp;
						<input type="button" value="' . $lang['QUOTE_SEL'] . '" name="quoteselected" title="' . $lang['QUOTE_SELECTED'] . '" onclick="bbcode.onclickQuoteSel();" />&nbsp;
					</div>
					<textarea id="message-' . $post_id . '" class="editor mrg_4" name="message" rows="18" cols="92">' . $post['post_text'] . '</textarea>
					<div class="mrg_4 tCenter">
						<input title="Alt+Enter" name="preview" type="submit" value="' . $lang['PREVIEW'] . '">
						<input type="button" onclick="edit_post(' . $post_id . ');" value="' . $lang['CANCEL'] . '">
						<input type="button" onclick="edit_post(' . $post_id . ', \'editor\', $(\'#message-' . $post_id . '\').val()); return false;" class="bold" value="' . $lang['SUBMIT'] . '">
					</div><hr>
					<script type="text/javascript">
					var bbcode = new BBCode("message-' . $post_id . '");
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
        }
        $this->response['post_id'] = $post_id;
        break;

    case 'add':
        if (!isset($this->request['topic_id'])) {
            $this->ajax_die('empty topic_id');
        }

        if (bf($userdata['user_opt'], 'user_opt', 'dis_post')) {
            $this->ajax_die(strip_tags($lang['RULES_REPLY_CANNOT']));
        } elseif (!$is_auth['auth_reply']) {
            $this->ajax_die(sprintf($lang['SORRY_AUTH_REPLY'], strip_tags($is_auth['auth_reply_type'])));
        }
        if ($post['topic_status'] == TOPIC_LOCKED && !$is_auth['auth_mod']) {
            $this->ajax_die($lang['TOPIC_LOCKED']);
        }

        $message = (string)$this->request['message'];
        $message = prepare_message($message);

        // Flood control
        $where_sql = (IS_GUEST) ? "p.poster_ip = '" . USER_IP . "'" : "p.poster_id = {$userdata['user_id']}";

        $sql = "SELECT MAX(p.post_time) AS last_post_time FROM " . BB_POSTS . " p WHERE $where_sql";
        if (($row = DB()->fetch_row($sql)) && $row['last_post_time']) {
            if ($userdata['user_level'] == USER) {
                if (TIMENOW - $row['last_post_time'] < $di->config->get('flood_interval')) {
                    $this->ajax_die($lang['FLOOD_ERROR']);
                }
            }
        }

        // Double Post Control
        if (!empty($row['last_post_time']) && !IS_AM) {
            $sql = "
				SELECT pt.post_text
				FROM " . BB_POSTS . " p, " . BB_POSTS_TEXT . " pt
				WHERE $where_sql
					AND p.post_time = " . (int)$row['last_post_time'] . "
					AND pt.post_id = p.post_id
				LIMIT 1
			";

            if ($row = DB()->fetch_row($sql)) {
                $last_msg = DB()->escape($row['post_text']);

                if ($last_msg == $message) {
                    $this->ajax_die($lang['DOUBLE_POST_ERROR']);
                }
            }
        }

        if ($di->config->get('max_smilies')) {
            $count_smilies = substr_count(bbcode2html($message), '<img class="smile" src="' . $di->config->get('smilies_path'));
            if ($count_smilies > $di->config->get('max_smilies')) {
                $this->ajax_die(sprintf($lang['MAX_SMILIES_PER_POST'], $di->config->get('max_smilies')));
            }
        }

        DB()->sql_query("INSERT INTO " . BB_POSTS . " (topic_id, forum_id, poster_id, post_time, poster_ip) VALUES ($topic_id, " . $post['forum_id'] . ", " . $userdata['user_id'] . ", '" . TIMENOW . "', '" . USER_IP . "')");
        $post_id = DB()->sql_nextid();
        DB()->sql_query("INSERT INTO " . BB_POSTS_TEXT . " (post_id, post_text) VALUES ($post_id, '" . DB()->escape($message) . "')");

        update_post_stats('reply', $post, $post['forum_id'], $topic_id, $post_id, $userdata['user_id']);

        $s_message = str_replace('\n', "\n", $message);
        $s_topic_title = str_replace('\n', "\n", $post['topic_title']);
        add_search_words($post_id, stripslashes($s_message), stripslashes($s_topic_title));
        update_post_html(array(
            'post_id' => $post_id,
            'post_text' => $message,
        ));

        if ($di->config->get('topic_notify_enabled')) {
            $notify = !empty($this->request['notify']);
            user_notification('reply', $post, $post['topic_title'], $post['forum_id'], $topic_id, $notify);
        }

        // Update atom feed
        update_atom('topic', (int)$this->request['topic_id']);

        $this->response['redirect'] = make_url(POST_URL . "$post_id#$post_id");
        break;

    default:
        $this->ajax_die('empty type');
        break;
}
