<?php

define('IN_AJAX', true);
$ajax = new ajax_common();

require('./common.php');

$ajax->init();

// Handle "board disabled via ON/OFF trigger"
if (file_exists(BB_DISABLED))
{
	$ajax->ajax_die($bb_cfg['board_disabled_msg']);
}

// Load actions required modules
switch ($ajax->action)
{
	case 'view_post':
		require(INC_DIR .'bbcode.php');
	break;

	case 'posts':
	case 'post_mod_comment':
		require(INC_DIR .'bbcode.php');
		require(INC_DIR .'functions_post.php');
		require(INC_DIR .'functions_admin.php');
	break;

	case 'view_torrent':
	case 'mod_action':
	case 'change_tor_status':
	case 'gen_passkey';
		require(BB_ROOT .'attach_mod/attachment_mod.php');
		require(INC_DIR .'functions_torrent.php');
	break;

	case 'change_torrent':
		require(BB_ROOT .'attach_mod/attachment_mod.php');
		require(INC_DIR .'functions_torrent.php');
	break;

	case 'user_register':
		require(INC_DIR .'functions_validate.php');
	break;

	case 'manage_user':
		require(INC_DIR .'functions_admin.php');
	break;

	case 'group_membership':
		require(INC_DIR .'functions_group.php');
	break;
}

// position in $ajax->valid_actions['xxx']
define('AJAX_AUTH', 0);  //  'guest', 'user', 'mod', 'admin'

$user->session_start();
$ajax->exec();

//
// Ajax
//
class ajax_common
{
	var $request  = array();
	var $response = array();

	var $valid_actions = array(
	//   ACTION NAME             AJAX_AUTH
		'edit_user_profile' => array('admin'),
		'change_user_rank'  => array('admin'),
		'change_user_opt'   => array('admin'),
		'manage_user'       => array('admin'),

		'mod_action'        => array('mod'),
        'topic_tpl'         => array('mod'),
        'group_membership'  => array('mod'),
		'post_mod_comment'  => array('mod'),

		'gen_passkey'       => array('user'),
		'change_torrent'    => array('user'),
		'change_tor_status' => array('user'),
		'modify_draft'      => array('user'),

		'view_post'         => array('guest'),
		'view_torrent'      => array('guest'),
		'user_register'     => array('guest'),
		'posts'             => array('guest'),
		'index_data'        => array('guest'),
);

	var $action = null;

	/**
	*  Constructor
	*/
	function ajax_common ()
	{
		ob_start(array(&$this, 'ob_handler'));
		header('Content-Type: text/plain');
	}

	/**
	*  Perform action
	*/
	function exec ()
	{
		global $lang;

		// Exit if we already have errors
		if (!empty($this->response['error_code']))
		{
			$this->send();
		}

		// Check that requested action is valid
		$action = $this->action;

		if (!$action || !is_string($action))
		{
			$this->ajax_die('no action specified');
		}
		else if (!$action_params =& $this->valid_actions[$action])
		{
			$this->ajax_die('invalid action: '. $action);
		}

		// Auth check
		switch ($action_params[AJAX_AUTH])
		{
			// GUEST
			case 'guest':
				break;

			// USER
			case 'user':
				if (IS_GUEST)
				{
					$this->ajax_die($lang['NEED_TO_LOGIN_FIRST']);
				}
				break;

			// MOD
			case 'mod':
				if (!IS_AM)
				{
					$this->ajax_die($lang['ONLY_FOR_MOD']);
				}
				$this->check_admin_session();
				break;

			// ADMIN
			case 'admin':
				if (!IS_ADMIN)
				{
					$this->ajax_die($lang['ONLY_FOR_ADMIN']);
				}
				$this->check_admin_session();
				break;

			default:
				trigger_error("invalid auth type for $action", E_USER_ERROR);
		}

		// Run action
		$this->$action();

		// Send output
		$this->send();
	}

	/**
	*  Exit on error
	*/
	function ajax_die ($error_msg, $error_code = E_AJAX_GENERAL_ERROR)
	{
		$this->response['error_code'] = $error_code;
		$this->response['error_msg'] = $error_msg;

		$this->send();
	}

	/**
	*  Initialization
	*/
	function init ()
	{
		$this->request = $_POST;
		$this->action  =& $this->request['action'];
	}

	/**
	*  Send data
	*/
	function send ()
	{
		$this->response['action'] = $this->action;

		if (DBG_USER && SQL_DEBUG && !empty($_COOKIE['sql_log']))
		{
			$this->response['sql_log'] = get_sql_log();
		}

		// sending output will be handled by $this->ob_handler()
		exit();
	}

	/**
	*  OB Handler
	*/
	function ob_handler ($contents)
	{
		if (DBG_USER)
		{
			if ($contents)
			{
				$this->response['raw_output'] = $contents;
			}
		}

		$response_js = bb_json_encode($this->response);

		if (GZIP_OUTPUT_ALLOWED && !defined('NO_GZIP'))
		{
			if (UA_GZIP_SUPPORTED && strlen($response_js) > 2000)
			{
				header('Content-Encoding: gzip');
				$response_js = gzencode($response_js, 1);
			}
		}

		return $response_js;
	}

	/**
	*  Admin session
	*/
	function check_admin_session ()
	{
		global $user;

		if (!$user->data['session_admin'])
		{
			if (empty($this->request['user_password']))
			{
				$this->prompt_for_password();
			}
			else
			{
				$login_args = array(
					'login_username' => $user->data['username'],
					'login_password' => $_POST['user_password'],
				);
				if (!$user->login($login_args, true))
				{
					$this->ajax_die('Wrong password');
				}
			}
		}
	}

	/**
	*  Prompt for password
	*/
	function prompt_for_password ()
	{
		$this->response['prompt_password'] = 1;
		$this->send();
	}

	/**
	*  Prompt for confirmation
	*/
	function prompt_for_confirm ($confirm_msg)
	{
		if(empty($confirm_msg)) $this->ajax_die('false');

		$this->response['prompt_confirm'] = 1;
		$this->response['confirm_msg'] = $confirm_msg;
		$this->send();
	}

    /**
	*  Verify mod rights
	*/
	function verify_mod_rights ($forum_id)
	{
		global $userdata, $lang;

		$is_auth = auth(AUTH_MOD, $forum_id, $userdata);

		if (!$is_auth['auth_mod'])
		{
			$this->ajax_die($lang['ONLY_FOR_MOD']);
		}
	}

	function edit_user_profile ()
	{
        require(AJAX_DIR .'edit_user_profile.php');
	}

	function change_user_rank ()
	{
		global $datastore, $lang;

		$ranks   = $datastore->get('ranks');
		$rank_id = intval($this->request['rank_id']);

		if (!$user_id = intval($this->request['user_id']) OR !$profiledata = get_userdata($user_id))
		{
			$this->ajax_die("invalid user_id: $user_id");
		}
		if ($rank_id != 0 && !isset($ranks[$rank_id]))
		{
			$this->ajax_die("invalid rank_id: $rank_id");
		}

		DB()->query("UPDATE ". BB_USERS ." SET user_rank = $rank_id WHERE user_id = $user_id LIMIT 1");

		cache_rm_user_sessions($user_id);

		$this->response['html'] = ($rank_id != 0) ? $lang['AWARDED_RANK'] . ' <b> '. $ranks[$rank_id]['rank_title'] .'</b>' : $lang['SHOT_RANK'];
	}

    function change_user_opt ()
	{
		global $bf, $lang;

		$user_id = (int) $this->request['user_id'];
		$new_opt = bb_json_decode($this->request['user_opt']);

		if (!$user_id OR !$u_data = get_userdata($user_id))
		{
			$this->ajax_die('invalid user_id');
		}
		if (!is_array($new_opt))
		{
			$this->ajax_die('invalid new_opt');
		}

		foreach ($bf['user_opt'] as $opt_name => $opt_bit)
		{
			if (isset($new_opt[$opt_name]))
			{
				setbit($u_data['user_opt'], $opt_bit, !empty($new_opt[$opt_name]));
			}
		}

		DB()->query("UPDATE ". BB_USERS ." SET user_opt = {$u_data['user_opt']} WHERE user_id = $user_id LIMIT 1");

        // Удаляем данные из кеша
        cache_rm_user_sessions ($user_id);

		$this->response['resp_html'] = $lang['SAVED'];
	}

	function gen_passkey ()
	{
		global $userdata, $lang;

		$req_uid = (int) $this->request['user_id'];

		if ($req_uid == $userdata['user_id'] || IS_ADMIN)
		{
			if (empty($this->request['confirmed']))
			{
				$this->prompt_for_confirm($lang['BT_GEN_PASSKEY_NEW']);
			}

			if (!$passkey = generate_passkey($req_uid, IS_ADMIN))
			{
				$this->ajax_die('Could not insert passkey');
			}
			tracker_rm_user($req_uid);
			$this->response['passkey'] = $passkey;
		}
		else $this->ajax_die($lang['NOT_AUTHORISED']);
	}

    // User groups membership
	function group_membership ()
	{
		global $lang, $user;

		if (!$user_id = intval($this->request['user_id']) OR !$profiledata = get_userdata($user_id))
		{
			$this->ajax_die("invalid user_id: $user_id");
		}
		if (!$mode = (string) $this->request['mode'])
		{
			$this->ajax_die('invalid mode (empty)');
		}

		switch ($mode)
		{
			case 'get_group_list':
				$sql = "
					SELECT ug.user_pending, g.group_id, g.group_type, g.group_name, g.group_moderator, self.user_id AS can_view
					FROM       ". BB_USER_GROUP ." ug
					INNER JOIN ". BB_GROUPS     ." g ON(g.group_id = ug.group_id AND g.group_single_user = 0)
					 LEFT JOIN ". BB_USER_GROUP ." self ON(self.group_id = g.group_id AND self.user_id = {$user->id} AND self.user_pending = 0)
					WHERE ug.user_id = $user_id
					ORDER BY g.group_name
				";
				$html = array();
				foreach (DB()->fetch_rowset($sql) as $row)
				{
					$class  = ($row['user_pending']) ? 'med' : 'med bold';
					$class .= ($row['group_moderator'] == $user_id) ? ' colorMod' : '';
					$href   = "groupcp.php?g={$row['group_id']}";

					if (IS_ADMIN)
					{
						$href .= "&amp;u=$user_id";
						$link  = '<a href="'. $href .'" class="'. $class .'" target="_blank">'. htmlCHR($row['group_name']) .'</a>';
						$html[] = $link;
					}
					else
					{
						// скрытая группа и сам юзер не является её членом
						if ($row['group_type'] == GROUP_HIDDEN && !$row['can_view'])
						{
							continue;
						}
						if ($row['group_moderator'] == $user->id)
						{
							$class .= ' selfMod';
							$href  .= "&amp;u=$user_id";  // сам юзер модератор этой группы
						}
						$link  = '<a href="'. $href .'" class="'. $class .'" target="_blank">'. htmlCHR($row['group_name']) .'</a>';
						$html[] = $link;
					}
				}
				if ($html)
				{
					$this->response['group_list_html'] = '<ul><li>'. join('</li><li>', $html) .'</li></ul>';
				}
				else
				{
					$this->response['group_list_html'] = $lang['GROUP_LIST_HIDDEN'];
				}
				break;

			default:
				$this->ajax_die("invalid mode: $mode");
		}
	}

	function post_mod_comment ()
	{
		global $lang, $userdata;

		$post_id = (int) $this->request['post_id'];
		$post = DB()->fetch_row("SELECT t.*, f.*, p.*, pt.post_text
			FROM ". BB_TOPICS ." t, ". BB_FORUMS ." f, ". BB_POSTS ." p, ". BB_POSTS_TEXT ." pt
			WHERE p.post_id = $post_id
				AND t.topic_id = p.topic_id
				AND f.forum_id = t.forum_id
				AND p.post_id  = pt.post_id
			LIMIT 1");
		if(!$post) $this->ajax_die('not post');
		$type = (int) $this->request['mc_type'];
		$text = (string) $this->request['mc_text'];
		$text = prepare_message($text);
		if (!$text) $this->ajax_die('no text');
		DB()->query("UPDATE ". BB_POSTS ." SET post_mod_comment = '". DB()->escape($text) ."', post_mod_comment_type = $type, post_mc_mod_id = ". $userdata['user_id'] .", post_mc_mod_name = '". $userdata['username'] ."' WHERE post_id = $post_id LIMIT 1");
		$this->response['type'] = $type;
		$this->response['post_id'] = $post_id;
		if ($type == 0) $this->response['html'] = '';
		else if ($type == 1) $this->response['html'] = '<div class="mcBlock"><table cellspacing="0" cellpadding="0" border="0"><tr><td class="mcTd1C">K</td><td class="mcTd2C">'. profile_url($userdata) .'&nbsp;'. $lang['WROTE'] .':<br /><br />'. bbcode2html($text) .'</td></tr></table></div>';
		else if ($type == 2) $this->response['html'] = '<div class="mcBlock"><table cellspacing="0" cellpadding="0" border="0"><tr><td class="mcTd1W">!</td><td class="mcTd2W">'. profile_url($userdata) .'&nbsp;'. $lang['WROTE'] .':<br /><br />'. bbcode2html($text) .'</td></tr></table></div>';
	}

	function view_post ()
	{
		require(AJAX_DIR .'view_post.php');
	}

	function change_tor_status ()
	{
		require(AJAX_DIR .'change_tor_status.php');
	}

	function change_torrent ()
	{
		require(AJAX_DIR .'change_torrent.php');
	}

	function view_torrent ()
	{
		require(AJAX_DIR .'view_torrent.php');
	}

	function user_register()
    {
		require(AJAX_DIR .'user_register.php');
    }

    function mod_action()
    {
		require(AJAX_DIR .'mod_action.php');
    }

    function posts()
    {
		require(AJAX_DIR .'posts.php');
    }

	function manage_user()
	{
		require(AJAX_DIR .'manage_user.php');
	}

	function topic_tpl()
	{
		require(AJAX_DIR .'topic_tpl.php');
	}

	function index_data()
    {
		require(AJAX_DIR .'index_data.php');
	}

	function modify_draft()
	{
		global $userdata;
		
		//if($bb_cfg['status_of_draft'] || !$bb_cfg['status_of_draft']) $this->ajax_die('Профилактика !!!');
		
		$tid  = (int) $this->request['id_draft'];
		$mode = (int) $this->request['mode'];
		
		$row = DB()->fetch_row("SELECT * FROM " . BB_TOPICS . " WHERE topic_id = {$tid}");

		if(!$row) $this->ajax_die('Нет такого черновика');

		if($row['topic_poster'] != $userdata['user_id'] && !IS_ADMIN) 
		{
			$this->ajax_die('Нельзя удалять чужие черновики');
		}

		print_r($mode);
		
		if(!$mode)
		{
			DB()->query("DELETE FROM ". BB_TOPICS ." WHERE topic_id = {$tid}");
		}
		else 
		{
			DB()->query("UPDATE ". BB_TOPICS ." SET is_draft = 0 WHERE topic_id = {$tid}");
		}

		$this->response['tid'] = $tid;
	}
}
