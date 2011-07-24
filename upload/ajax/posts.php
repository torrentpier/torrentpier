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
		if(!$post) bb_die('not post');

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
		{			bb_die(sprintf($lang['SORRY_AUTH_DELETE'], strip_tags($is_auth['auth_delete_type'])));		}
		break;

	case 'quote';
		if(!$post) bb_die('not post');
		if(bf($userdata['user_opt'], 'user_opt', 'allow_post'))
		{
			bb_die($lang['RULES_REPLY_CANNOT']);
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

	case 'add':
		$this->ajax_die('off');
		break;

	default:
		$this->ajax_die('empty type');
		break;}



