<?php

define('IN_AJAX', true);
$ajax =& new ajax_common();

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
	case 'view_message':
		require(INC_DIR .'bbcode.php');
	break;

	case 'view_torrent':
	case 'mod_action':
	case 'change_tor_status':
	case 'gen_passkey';
		require(INC_DIR .'functions_torrent.php');
	break;

    case 'change_torrent':
        require(BB_ROOT .'attach_mod/attachment_mod.php');
		require(INC_DIR .'functions_torrent.php');
	break;

	case 'user_register':
	    require(INC_DIR .'functions_validate.php');
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

		'change_torrent'    => array('mod'),
		'change_tor_status' => array('mod'),
		'mod_action'        => array('mod'),

        'gen_passkey'       => array('user'),

		'view_post'         => array('guest'),
		'view_message'      => array('guest'),
        'view_torrent'      => array('guest'),
        'user_register'     => array('guest'),
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
				if (!(IS_MOD || IS_ADMIN))
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
		global $lang;
		if(!empty($confirm_msg)) $confirm_msg = $lang['CONFIRM'];

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
		global $datastore;

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

		$this->response['html'] = ($rank_id != 0) ? 'Присвоено звание <b>'. $ranks[$rank_id]['rank_title'] .'</b>' : 'Звание снято';
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

	function view_post ()
	{
		require(AJAX_DIR .'view_post.php');
	}

	function view_message ()
	{
		global $lang;

		$message = (string) $this->request['message'];
		if(!trim($message)) $this->ajax_die($lang['EMPTY_MESSAGE']);
		$message = bbcode2html($message);
        $this->response['html'] = $message;
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

}
