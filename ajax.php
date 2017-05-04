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

define('BB_SCRIPT', 'ajax');
define('IN_AJAX', true);

$ajax = new ajax_common();

require __DIR__ . '/common.php';

$ajax->init();

// Init userdata
$user->session_start();

// Exit if board is disabled via ON/OFF trigger or by admin
if ($ajax->action != 'manage_admin') {
    if ($bb_cfg['board_disable']) {
        $ajax->ajax_die($lang['BOARD_DISABLE']);
    } elseif (file_exists(BB_DISABLED)) {
        $ajax->ajax_die($lang['BOARD_DISABLE_CRON']);
    }
}

// Load actions required modules
switch ($ajax->action) {
    case 'view_post':
        require INC_DIR . '/bbcode.php';
        break;

    case 'posts':
    case 'post_mod_comment':
        require INC_DIR . '/bbcode.php';
        require INC_DIR . '/functions_post.php';
        require INC_DIR . '/functions_admin.php';
        break;

    case 'view_torrent':
    case 'mod_action':
    case 'change_tor_status':
    case 'gen_passkey':
        require ATTACH_DIR . '/attachment_mod.php';
        require INC_DIR . '/functions_torrent.php';
        break;

    case 'change_torrent':
        require ATTACH_DIR . '/attachment_mod.php';
        require INC_DIR . '/functions_torrent.php';
        break;

    case 'user_register':
        require INC_DIR . '/functions_validate.php';
        break;

    case 'manage_user':
    case 'manage_admin':
        require INC_DIR . '/functions_admin.php';
        break;

    case 'group_membership':
    case 'manage_group':
        require INC_DIR . '/functions_group.php';
        break;

    case 'sitemap':
        require CLASS_DIR . '/sitemap.php';
        break;
}

// Position in $ajax->valid_actions['xxx']
define('AJAX_AUTH', 0); // 'guest', 'user', 'mod', 'admin', 'super_admin'

$ajax->exec();

//
// Ajax
//
class ajax_common
{
    public $request = array();
    public $response = array();

    public $valid_actions = array(
        // ACTION NAME         AJAX_AUTH
        'edit_user_profile' => array('admin'),
        'change_user_rank' => array('admin'),
        'change_user_opt' => array('admin'),
        'manage_user' => array('admin'),
        'manage_admin' => array('admin'),
        'sitemap' => array('admin'),

        'mod_action' => array('mod'),
        'topic_tpl' => array('mod'),
        'group_membership' => array('mod'),
        'post_mod_comment' => array('mod'),

        'avatar' => array('user'),
        'gen_passkey' => array('user'),
        'change_torrent' => array('user'),
        'change_tor_status' => array('user'),
        'manage_group' => array('user'),

        'view_post' => array('guest'),
        'view_torrent' => array('guest'),
        'user_register' => array('guest'),
        'posts' => array('guest'),
        'index_data' => array('guest'),
    );

    public $action;

    /**
     *  Constructor
     */
    public function __construct()
    {
        ob_start(array(&$this, 'ob_handler'));
        header('Content-Type: text/plain');
    }

    /**
     *  Perform action
     */
    public function exec()
    {
        global $lang;

        // Exit if we already have errors
        if (!empty($this->response['error_code'])) {
            $this->send();
        }

        // Check that requested action is valid
        $action = $this->action;

        if (!$action || !is_string($action)) {
            $this->ajax_die('no action specified');
        } elseif (!$action_params =& $this->valid_actions[$action]) {
            $this->ajax_die('invalid action: ' . $action);
        }

        // Auth check
        switch ($action_params[AJAX_AUTH]) {
            // GUEST
            case 'guest':
                break;

            // USER
            case 'user':
                if (IS_GUEST) {
                    $this->ajax_die($lang['NEED_TO_LOGIN_FIRST']);
                }
                break;

            // MOD
            case 'mod':
                if (!IS_AM) {
                    $this->ajax_die($lang['ONLY_FOR_MOD']);
                }
                $this->check_admin_session();
                break;

            // ADMIN
            case 'admin':
                if (!IS_ADMIN) {
                    $this->ajax_die($lang['ONLY_FOR_ADMIN']);
                }
                $this->check_admin_session();
                break;

            // SUPER_ADMIN
            case 'super_admin':
                if (!IS_SUPER_ADMIN) {
                    $this->ajax_die($lang['ONLY_FOR_SUPER_ADMIN']);
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
     * @param $error_msg
     * @param int $error_code
     */
    public function ajax_die($error_msg, $error_code = E_AJAX_GENERAL_ERROR)
    {
        $this->response['error_code'] = $error_code;
        $this->response['error_msg'] = $error_msg;

        $this->send();
    }

    /**
     *  Initialization
     */
    public function init()
    {
        $this->request = $_POST;
        $this->action =& $this->request['action'];
    }

    /**
     *  Send data
     */
    public function send()
    {
        $this->response['action'] = $this->action;

        if (DBG_USER && SQL_DEBUG && !empty($_COOKIE['sql_log'])) {
            $this->response['sql_log'] = get_sql_log();
        }

        // sending output will be handled by $this->ob_handler()
        exit();
    }

    /**
     *  OB Handler
     * @param $contents
     * @return string
     */
    public function ob_handler($contents): string
    {
        if (DBG_USER) {
            if ($contents) {
                $this->response['raw_output'] = $contents;
            }
        }

        $response_js = json_encode($this->response);

        if (GZIP_OUTPUT_ALLOWED && !defined('NO_GZIP')) {
            if (UA_GZIP_SUPPORTED && strlen($response_js) > 2000) {
                header('Content-Encoding: gzip');
                $response_js = gzencode($response_js, 1);
            }
        }

        return $response_js;
    }

    /**
     *  Admin session
     */
    public function check_admin_session()
    {
        global $user;

        if (!$user->data['session_admin']) {
            if (empty($this->request['user_password'])) {
                $this->prompt_for_password();
            } else {
                $login_args = array(
                    'login_username' => $user->data['username'],
                    'login_password' => $_POST['user_password'],
                );
                if (!$user->login($login_args, true)) {
                    $this->ajax_die('Wrong password');
                }
            }
        }
    }

    /**
     *  Prompt for password
     */
    public function prompt_for_password()
    {
        $this->response['prompt_password'] = 1;
        $this->send();
    }

    /**
     *  Prompt for confirmation
     * @param $confirm_msg
     */
    public function prompt_for_confirm($confirm_msg)
    {
        if (empty($confirm_msg)) {
            $this->ajax_die('false');
        }

        $this->response['prompt_confirm'] = 1;
        $this->response['confirm_msg'] = $confirm_msg;
        $this->send();
    }

    /**
     *  Verify mod rights
     * @param $forum_id
     */
    public function verify_mod_rights($forum_id)
    {
        global $userdata, $lang;

        $is_auth = auth(AUTH_MOD, $forum_id, $userdata);

        if (!$is_auth['auth_mod']) {
            $this->ajax_die($lang['ONLY_FOR_MOD']);
        }
    }

    public function edit_user_profile()
    {
        require AJAX_DIR . '/edit_user_profile.php';
    }

    public function change_user_rank()
    {
        require AJAX_DIR . '/change_user_rank.php';
    }

    public function change_user_opt()
    {
        require AJAX_DIR . '/change_user_opt.php';
    }

    public function gen_passkey()
    {
        require AJAX_DIR . '/gen_passkey.php';
    }

    public function group_membership()
    {
        require AJAX_DIR . '/group_membership.php';
    }

    public function manage_group()
    {
        require AJAX_DIR . '/edit_group_profile.php';
    }

    public function post_mod_comment()
    {
        require AJAX_DIR . '/post_mod_comment.php';
    }

    public function view_post()
    {
        require AJAX_DIR . '/view_post.php';
    }

    public function change_tor_status()
    {
        require AJAX_DIR . '/change_tor_status.php';
    }

    public function change_torrent()
    {
        require AJAX_DIR . '/change_torrent.php';
    }

    public function view_torrent()
    {
        require AJAX_DIR . '/view_torrent.php';
    }

    public function user_register()
    {
        require AJAX_DIR . '/user_register.php';
    }

    public function mod_action()
    {
        require AJAX_DIR . '/mod_action.php';
    }

    public function posts()
    {
        require AJAX_DIR . '/posts.php';
    }

    public function manage_user()
    {
        require AJAX_DIR . '/manage_user.php';
    }

    public function manage_admin()
    {
        require AJAX_DIR . '/manage_admin.php';
    }

    public function topic_tpl()
    {
        require AJAX_DIR . '/topic_tpl.php';
    }

    public function index_data()
    {
        require AJAX_DIR . '/index_data.php';
    }

    public function avatar()
    {
        require AJAX_DIR . '/avatar.php';
    }

    public function sitemap()
    {
        require AJAX_DIR . '/sitemap.php';
    }
}
