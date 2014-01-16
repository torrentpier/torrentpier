<?php

define('IN_PROFILE', true);
define('BB_SCRIPT', 'profile');
define('BB_ROOT', './');
require(BB_ROOT . 'common.php');

// Start session management
$user->session_start();

$mode = (string) @$_REQUEST['mode'];

switch ($mode)
{
	case 'viewprofile':
		require(INC_DIR . 'ucp/usercp_viewprofile.php');
		break;

	case 'register':
	case 'editprofile':
		if (IS_GUEST && $mode == 'editprofile') login_redirect();

		require(INC_DIR . 'ucp/usercp_register.php');
		break;

	case 'sendpassword':
		require(INC_DIR .'ucp/usercp_sendpasswd.php');
		break;

	case 'activate':
		require(INC_DIR .'ucp/usercp_activate.php');
		break;

	case 'email':
		require(INC_DIR .'ucp/usercp_email.php');
		break;

	case 'bonus':
		if (IS_GUEST) login_redirect();

		require(INC_DIR . 'ucp/usercp_bonus.php');
		break;

	case 'watch':
		if (IS_GUEST) login_redirect();

		require(INC_DIR . 'ucp/usercp_topic_watch.php');
		break;

	default:
		bb_die('Invalid mode');
}