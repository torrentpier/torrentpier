<?php

define('IN_PROFILE', true);
define('BB_SCRIPT', 'profile');
define('BB_ROOT', './');
require(BB_ROOT . 'common.php');

// Start session management
$user->session_start();

set_die_append_msg();
$mode = request_var('mode', '');

switch ($mode)
{
	case 'viewprofile':
		require(INC_DIR . 'ucp/viewprofile.php');
		break;

	case 'register':
	case 'editprofile':
		if (IS_GUEST && $mode == 'editprofile') login_redirect();

		require(INC_DIR . 'ucp/register.php');
		break;

	case 'sendpassword':
		require(INC_DIR .'ucp/sendpasswd.php');
		break;

	case 'activate':
		require(INC_DIR .'ucp/activate.php');
		break;

	case 'email':
		require(INC_DIR .'ucp/email.php');
		break;

	case 'bonus':
		if (IS_GUEST) login_redirect();

		require(INC_DIR . 'ucp/bonus.php');
		break;

	case 'watch':
		if (IS_GUEST) login_redirect();

		require(INC_DIR . 'ucp/topic_watch.php');
		break;

	default:
		bb_die('Invalid mode');
}