<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('IN_PROFILE', true);
define('BB_SCRIPT', 'profile');
define('BB_ROOT', './');
require __DIR__ . '/common.php';

// Start session management
$user->session_start();

set_die_append_msg();
$mode = request_var('mode', '');

switch ($mode) {
    case 'viewprofile':
        require UCP_DIR . '/viewprofile.php';
        break;

    case 'register':
    case 'editprofile':
        if (IS_GUEST && $mode == 'editprofile') {
            login_redirect();
        }
        require UCP_DIR . '/register.php';
        break;

    case 'sendpassword':
        require UCP_DIR . '/sendpasswd.php';
        break;

    case 'activate':
        require UCP_DIR . '/activate.php';
        break;

    case 'email':
        require UCP_DIR . '/email.php';
        break;

    case 'bonus':
        if (IS_GUEST) {
            login_redirect();
        }
        require UCP_DIR . '/bonus.php';
        break;

    case 'watch':
        if (IS_GUEST) {
            login_redirect();
        }
        require UCP_DIR . '/topic_watch.php';
        break;

    default:
        bb_die('Invalid mode');
}
