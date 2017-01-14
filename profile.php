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

define('IN_PROFILE', true);
define('BB_SCRIPT', 'profile');
define('BB_ROOT', './');
require(BB_ROOT . 'common.php');

// Start session management
$user->session_start();

set_die_append_msg();
$mode = request_var('mode', '');

switch ($mode) {
    case 'viewprofile':
        require(UCP_DIR . 'viewprofile.php');
        break;

    case 'register':
    case 'editprofile':
        if (IS_GUEST && $mode == 'editprofile') {
            login_redirect();
        }
        require(UCP_DIR . 'register.php');
        break;

    case 'sendpassword':
        require(UCP_DIR . 'sendpasswd.php');
        break;

    case 'activate':
        require(UCP_DIR . 'activate.php');
        break;

    case 'email':
        require(UCP_DIR . 'email.php');
        break;

    case 'bonus':
        if (IS_GUEST) {
            login_redirect();
        }
        require(UCP_DIR . 'bonus.php');
        break;

    case 'watch':
        if (IS_GUEST) {
            login_redirect();
        }
        require(UCP_DIR . 'topic_watch.php');
        break;

    default:
        bb_die('Invalid mode');
}
