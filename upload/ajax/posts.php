<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $lang, $bb_cfg, $userdata;

if (!isset($this->request['type']))
{
	$this->ajax_die('empty type');
}
if (isset($this->request['post_id']))
{
	$post_id = (int) $this->request['post_id'];
	$post = DB()->fetch_row("SELECT t.*, f.*, p.*, pt.post_text
		FROM ". BB_TOPICS ." t, ". BB_FORUMS ." f, ". BB_POSTS ." p, ". BB_POSTS_TEXT ." pt
		WHERE p.post_id = $post_id
			AND t.topic_id = p.topic_id
			AND f.forum_id = t.forum_id
			AND p.post_id  = pt.post_id
		LIMIT 1");
}

if (!defined('WORD_LIST_OBTAINED'))
{
	$orig_word = array();
	$replace_word = array();
	obtain_word_list($orig_word, $replace_word);
	define('WORD_LIST_OBTAINED', true);
}

switch($this->request['type'])
{	case 'delete';
		if(!$post) $this->ajax_die('not post');

		$is_auth = auth(AUTH_ALL, $post['forum_id'], $userdata, $post);

		if($post['post_id'] != $post['topic_first_post_id'] && ($is_auth['auth_mod'] || ($userdata['user_id'] == $post['poster_id'] && $is_auth['auth_delete'] && $post['topic_last_post_id'] == $post['post_id'] && $post['post_time'] + 3600*3 > TIMENOW)))
		{			if (empty($this->request['confirmed']))
			{
				$this->prompt_for_confirm($lang['CONFIRM_DELETE']);
			}
			post_delete($post_id);
			$this->response['hide']    = true;
			$this->response['post_id'] = $post_id;		}
		else
		{			$this->ajax_die(sprintf($lang['SORRY_AUTH_DELETE'], strip_tags($is_auth['auth_delete_type'])));		}
		break;

	case 'reply';
		if(!$post) $this->ajax_die('not post');

		$is_auth = auth(AUTH_ALL, $post['forum_id'], $userdata, $post);
		if(bf($userdata['user_opt'], 'user_opt', 'allow_post'))
		{
			$this->ajax_die($lang['RULES_REPLY_CANNOT']);
		}
        else if(!$is_auth['auth_reply'])
		{
			$this->ajax_die(sprintf($lang['SORRY_AUTH_REPLY'], strip_tags($is_auth['auth_reply_type'])));
		}

		// Use trim to get rid of spaces placed there by MS-SQL 2000
		$quote_username = (trim($post['post_username']) != '') ? $post['post_username'] : get_username($post['poster_id']);
		$message = '[quote="'. $quote_username .'"]'. $post['post_text'] .'[/quote]';
		// hide user passkey
		$message = preg_replace('#(?<=\?uk=)[a-zA-Z0-9]{10}(?=&)#', 'passkey', $message);
		// hide sid
		$message = preg_replace('#(?<=[\?&;]sid=)[a-zA-Z0-9]{12}#', 'sid', $message);

		if (!empty($orig_word))
		{
			$message = (!empty($message)) ? preg_replace($orig_word, $replace_word, $message) : '';
		}

		if(mb_strlen($message, 'UTF-8') > 1000)
		{			$this->response['redirect'] = make_url('posting.php?mode=quote&p='. $post_id);		}

		$this->response['quote']   = true;
		$this->response['message'] = $message;
		break;

	case 'view_message':
		$message = (string) $this->request['message'];
		if(!trim($message)) $this->ajax_die($lang['EMPTY_MESSAGE']);
		$message = bbcode2html($message);
        $this->response['view_message'] = $message;
		break;

	case 'edit':
    case 'editor':
        if(!$post) $this->ajax_die('not post');

        if(mb_strlen($post['post_text'], 'UTF-8') > 1000)
        {        	$this->response['redirect'] = make_url('posting.php?mode=editpost&p='. $post_id);        }
		else if($this->request['type'] == 'editor')
		{
			$text = (string) $this->request['text'];
			$text = prepare_message($text);

			if(mb_strlen($text) > 2)
			{
				if($text != $post['post_text'])
				{
				    if($bb_cfg['max_smilies'])
				    {
						$count_smilies = substr_count(bbcode2html($text), '<img class="smile" src="'. $bb_cfg['smilies_path']);
						if($count_smilies > $bb_cfg['max_smilies'])
						{
							$this->ajax_die(sprintf($lang['MAX_SMILIES_PER_POST'], $bb_cfg['max_smilies']));
						}
				    }
					DB()->query("UPDATE ". BB_POSTS_TEXT ." SET post_text = '". DB()->escape($text) ."' WHERE post_id = $post_id LIMIT 1");
					add_search_words($post_id, stripslashes($text), stripslashes($post['topic_title']));
				    update_post_html(array(
						'post_id'        => $post_id,
						'post_text'      => $text,
					));
				}
			}
			else $this->ajax_die($lang['EMPTY_MESSAGE']);

			$this->response['html'] = bbcode2html($text);
		}
		else
		{
			$is_auth = auth(AUTH_ALL, $post['forum_id'], $userdata, $post);
			if ($post['topic_status'] == TOPIC_LOCKED && !$is_auth['auth_mod'])
			{
				$this->ajax_die($lang['TOPIC_LOCKED']);
			}
			else if(!$is_auth['auth_edit'])
			{
				$this->ajax_die(sprintf($lang['SORRY_AUTH_EDIT'], strip_tags($is_auth['auth_edit_type'])));
			}

			// Запрет на редактирование раздачи юзером
			if ($post['allow_reg_tracker'] && ($post['topic_first_post_id'] == $post_id) && !IS_AM)
			{
				$tor_status = DB()->fetch_row("SELECT tor_status FROM ". BB_BT_TORRENTS ." WHERE topic_id = {$post['topic_id']} LIMIT 1", 'tor_status');
				if ($tor_status != false)
				{
					// по статусу раздачи
					if (isset($bb_cfg['tor_cannot_edit'][$tor_status]))
					{
						$this->ajax_die("Вы не можете редактировать сообщение со статусом {$lang['tor_status'][$tor_status]}");
					}
					// проверенный, через время
					if ($tor_status == TOR_APPROVED)
					{
						$days_after_last_edit     = $bb_cfg['dis_edit_tor_after_days'];
						$last_edit_time           = max($post['post_time'], $post['post_edit_time']) + 86400*$days_after_last_edit;
						$disallowed_by_forum_perm = in_array($post['forum_id'], $bb_cfg['dis_edit_tor_forums']);
						$disallowed_by_user_opt   = bf($user->opt, 'user_opt', 'dis_edit_release');

						if ($last_edit_time < TIMENOW && ($disallowed_by_forum_perm || $disallowed_by_user_opt))
						{
							$how_msg = ($disallowed_by_user_opt) ? 'Вам запрещено' : 'Вы не можете';
							$this->ajax_die("$how_msg редактировать сообщение со статусом <b>{$lang['tor_status'][$tor_status]}</b> по прошествии $days_after_last_edit дней");
						}
					}
				}
			}

			$this->response['text'] = '
			    <form action="posting.php" method="post" name="post">
					<input type="hidden" name="mode" value="reply" />
					<input type="hidden" name="t" value="'. $post['topic_id'] .'" />
					<div class="buttons mrg_4">
						<input type="button" value=" B " name="codeB" title="Bold (Ctrl+B)" style="font-weight: bold; width: 30px;" />
						<input type="button" value=" i " name="codeI" title="Italic (Ctrl+I)" style="width: 30px; font-style: italic;" />
						<input type="button" value=" u " name="codeU" title="Underline (Ctrl+U)" style="width: 30px; text-decoration: underline;" />
						<input type="button" value=" s " name="codeS" title="Strikeout (Ctrl+S)" style="width: 30px; text-decoration: line-through;" />&nbsp;&nbsp;
						<input type="button" value="Quote" name="codeQuote" title="Quote (Ctrl+Q)" style="width: 50px;" />
						<input type="button" value="Img" name="codeImg" title="Image (Ctrl+R)" style="width: 40px;" />
						<input type="button" value="URL" name="codeUrl" title="URL (Ctrl+W)" style="width: 40px; text-decoration: underline;" /><input type="hidden" name="codeUrl2" />&nbsp;
						<input type="button" value="Code" name="codeCode" title="Code (Ctrl+K)" style="width: 46px;" />
						<input type="button" value="List" name="codeList" title="List (Ctrl+L)" style="width: 46px;" />
						<input type="button" value="1." name="codeOpt" title="List item (Ctrl+0)" style="width: 30px;" />&nbsp;
						<input type="button" value="Quote selected" name="quoteselected" title="{L_QUOTE_SELECTED}" style="width: 100px;" onmouseout="bbcode.refreshSelection(false);" onmouseover="bbcode.refreshSelection(true);" onclick="bbcode.onclickQuoteSel();" />&nbsp;
						<input type="button" value="Translit" name="Translit" title="Перевести выделение из транслита на русский" style="width: 60px;" onclick="transliterate(document.post.message, this);" />
					</div>
					<textarea id="message-'. $post_id .'" class="editor mrg_4" name="message" rows="18" cols="92">'. $post['post_text'] .'</textarea>
					<div class="mrg_4 tCenter">
						<input title="Alt+Enter" type="submit" name="preview" value="'. $lang['PREVIEW'] .'">
						<input type="button" onclick="edit_post('. $post_id .');" value="'. $lang['CANCEL'] .'">
						<input type="button" onclick="edit_post('. $post_id .', \'editor\', $(\'#message-'. $post_id .'\').val()); return false;" class="bold" value="'. $lang['EDIT_POST'] .'">
					</div><hr>
					<script type="text/javascript">
					var bbcode = new BBCode("message-'. $post_id .'");
					var ctrl = "ctrl";

					bbcode.addTag("codeB", "b", null, "B", ctrl);
					bbcode.addTag("codeI", "i", null, "I", ctrl);
					bbcode.addTag("codeU", "u", null, "U", ctrl);
					bbcode.addTag("codeS", "s", null, "S", ctrl);

					bbcode.addTag("codeQuote", "quote", null, "Q", ctrl);
					bbcode.addTag("codeImg", "img", null, "R", ctrl);
					bbcode.addTag("codeUrl", "url", "/url", "", ctrl);
					bbcode.addTag("codeUrl2", "url=", "/url", "W", ctrl);

					bbcode.addTag("codeCode", "code", null, "K", ctrl);
					bbcode.addTag("codeList",  "list", null, "L", ctrl);
					bbcode.addTag("codeOpt", "*", "", "0", ctrl);
					</script>
				</form>';
		}
		$this->response['post_id'] = $post_id;
		break;

	case 'add':
        if (!isset($this->request['topic_id']))
		{
			$this->ajax_die('empty topic_id');
		}
		$topic_id = (int) $this->request['topic_id'];
        $t_data = DB()->fetch_row("SELECT t.*, f.*
			FROM ". BB_TOPICS ." t, ". BB_FORUMS ." f
			WHERE t.topic_id = $topic_id
				AND f.forum_id = t.forum_id
			LIMIT 1");
		if(!$t_data) $this->ajax_die($lang['TOPIC_POST_NOT_EXIST']);

		$is_auth = auth(AUTH_ALL, $t_data['forum_id'], $userdata, $t_data);
		if(bf($userdata['user_opt'], 'user_opt', 'allow_post'))
		{
			$this->ajax_die($lang['RULES_REPLY_CANNOT']);
		}
        else if(!$is_auth['auth_reply'])
		{
			$this->ajax_die(sprintf($lang['SORRY_AUTH_REPLY'], strip_tags($is_auth['auth_reply_type'])));
		}

        // Flood control
		$where_sql = (IS_GUEST) ? "p.poster_ip = '". USER_IP ."'" : "p.poster_id = {$userdata['user_id']}";

		$sql = "SELECT MAX(p.post_time) AS last_post_time FROM ". BB_POSTS ." p WHERE $where_sql";
		if ($row = DB()->fetch_row($sql) AND $row['last_post_time'])
		{
			if ($userdata['user_level'] == USER)
			{
				if (TIMENOW - $row['last_post_time'] < $bb_cfg['flood_interval'])
				{
					$this->ajax_die($lang['FLOOD_ERROR']);
				}
			}
		}

		// Double Post Control
		if (!empty($row['last_post_time']) && !IS_AM)
		{
			$sql = "
				SELECT pt.post_text
				FROM ". BB_POSTS ." p, ". BB_POSTS_TEXT ." pt
				WHERE
						$where_sql
					AND p.post_time = ". (int) $row['last_post_time'] ."
					AND pt.post_id = p.post_id
				LIMIT 1
			";

			if ($row = DB()->fetch_row($sql))
			{
				$last_msg = DB()->escape($row['post_text']);

				if ($last_msg == $post_message)
				{
					$this->ajax_die($lang['DOUBLE_POST_ERROR']);
				}
			}
		}

	    $message = (string) $this->request['message'];
		$message = prepare_message($message);

	    if($bb_cfg['max_smilies'])
	    {
			$count_smilies = substr_count(bbcode2html($message), '<img class="smile" src="'. $bb_cfg['smilies_path']);
			if($count_smilies > $bb_cfg['max_smilies'])
			{
				$this->ajax_die(sprintf($lang['MAX_SMILIES_PER_POST'], $bb_cfg['max_smilies']));
			}
	    }

		DB()->sql_query("INSERT INTO " . BB_POSTS . " (topic_id, forum_id, poster_id, post_time, poster_ip) VALUES ($topic_id, ". $t_data['forum_id'] .", ". $userdata['user_id'] .", '". TIMENOW ."', '". USER_IP ."')");
        $post_id = DB()->sql_nextid();
		DB()->sql_query("INSERT INTO " . BB_POSTS_TEXT . " (post_id, post_text) VALUES ($post_id, '". DB()->escape($message) ."')");

        update_post_stats('reply', $t_data, $t_data['forum_id'], $topic_id, $post_id, $userdata['user_id']);

		add_search_words($post_id, stripslashes($message), stripslashes($t_data['topic_title']));
	    update_post_html(array(
			'post_id'        => $post_id,
			'post_text'      => $message,
		));

		$this->response['redirect'] = make_url(POST_URL . $post_id .'#'. $post_id);
		break;

	default:
		$this->ajax_die('empty type');
		break;}



