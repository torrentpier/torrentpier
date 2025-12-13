<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

/*
 * ===========================================================================
 * Refactor to Modern Controller
 * ===========================================================================
 * Target: Convert to PSR-7 controller with constructor dependency injection
 *
 * Dependencies to inject:
 * - TorrentPier\Config (configuration access)
 * - TorrentPier\Database\Database (database operations)
 * - TorrentPier\Legacy\User (user session and profile data)
 * - TorrentPier\Http\Request (HTTP request handling)
 * - TorrentPier\Legacy\Templates (template rendering)
 *
 * Target namespace: TorrentPier\Http\Controllers
 * Target class: ProfileController
 *
 * Key refactoring tasks:
 * 1. Extract procedural code into controller methods (view, edit, avatar, etc.)
 * 2. Replace global function calls with injected dependencies
 * 3. Implement PSR-7 request/response handling
 * 4. Extract business logic into ProfileService
 * 5. Add proper validation for profile updates
 * 6. Implement avatar upload handling via UploadService
 * ===========================================================================
 */

define('IN_PROFILE', true);

set_die_append_msg();
$mode = request()->getString('mode', 'viewprofile');

if ($mode === 'register') {
    page_cfg('allow_robots', false);
}

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
