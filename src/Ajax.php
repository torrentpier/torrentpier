<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use Exception;

/**
 * Class Ajax
 * @package TorrentPier
 */
class Ajax
{
    public array $request = [];
    public array $response = [];

    public array $valid_actions = [
        // ACTION NAME => [AJAX_AUTH, IN_ADMIN_CP (optional)]
        'edit_user_profile' => ['admin'],
        'change_user_rank' => ['admin'],
        'change_user_opt' => ['admin'],
        'manage_user' => ['admin'],
        'manage_admin' => ['admin', true],
        'sitemap' => ['admin', true],

        'mod_action' => ['mod'],
        'topic_tpl' => ['mod'],
        'group_membership' => ['mod'],
        'post_mod_comment' => ['mod'],

        'avatar' => ['user'],
        'gen_passkey' => ['user'],
        'change_torrent' => ['user'],
        'change_tor_status' => ['user'],
        'manage_group' => ['user'],

        'view_post' => ['guest'],
        'view_torrent' => ['guest'],
        'user_register' => ['guest'],
        'posts' => ['guest'],
        'index_data' => ['guest'],
    ];

    public string $action;

    /**
     * Constructor
     */
    public function __construct()
    {
        ob_start([&$this, 'ob_handler']);
        header('Content-Type: text/plain');
    }

    /**
     * Perform action
     * @throws Exception
     */
    public function exec()
    {
        global $lang, $bb_cfg;

        // Exit if we already have errors
        if (!empty($this->response['error_code'])) {
            $this->send();
        }

        // Check that requested action is valid
        $action = $this->action;

        // Actions params array
        $action_params = null;

        // Actions check
        if (!$action) {
            $this->ajax_die('no action specified');
        } elseif (!$action_params =& $this->valid_actions[$action]) {
            $this->ajax_die('invalid action: ' . $action);
        }

        // Exit if board is disabled via ON/OFF trigger or by admin
        if ($bb_cfg['board_disable'] || file_exists(BB_DISABLED)) {
            if ($action_params[1] !== true) {
                if ($bb_cfg['board_disable']) {
                    $this->ajax_die($lang['BOARD_DISABLE']);
                } elseif (file_exists(BB_DISABLED)) {
                    $this->ajax_die($lang['BOARD_DISABLE_CRON']);
                }
            }
        }

        // Auth check
        switch ($action_params[0]) {
            case 'guest': // GUEST
                break;
            case 'user': // USER
                if (IS_GUEST) {
                    $this->ajax_die($lang['NEED_TO_LOGIN_FIRST']);
                }
                break;
            case 'mod': // MOD
                if (!IS_AM) {
                    $this->ajax_die($lang['ONLY_FOR_MOD']);
                }
                $this->check_admin_session();
                break;
            case 'admin': // ADMIN
                if (!IS_ADMIN) {
                    $this->ajax_die($lang['ONLY_FOR_ADMIN']);
                }
                $this->check_admin_session();
                break;
            case 'super_admin': // SUPER_ADMIN
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
     * Exit on error
     *
     * @param string $error_msg
     * @param int $error_code
     * @throws Exception
     */
    public function ajax_die(string $error_msg, int $error_code = E_AJAX_GENERAL_ERROR)
    {
        $this->response['error_code'] = $error_code;
        $this->response['error_msg'] = $error_msg;

        $this->send();
    }

    /**
     * Initialization
     */
    public function init()
    {
        $this->request = $_POST;
        $this->action =& $this->request['action'];
    }

    /**
     * Send data
     * @throws Exception
     */
    public function send()
    {
        $this->response['action'] = $this->action;

        if (Dev::sql_dbg_enabled()) {
            $this->response['sql_log'] = Dev::get_sql_log();
        }

        // sending output will be handled by $this->ob_handler()
        exit();
    }

    /**
     * OB Handler
     *
     * @param $contents
     * @return string
     * @throws \JsonException
     */
    public function ob_handler($contents): string
    {
        if (APP_DEBUG) {
            if ($contents) {
                $this->response['raw_output'] = $contents;
            }
        }

        $response_js = json_encode($this->response, JSON_THROW_ON_ERROR);

        if (GZIP_OUTPUT_ALLOWED && !\defined('NO_GZIP')) {
            if (UA_GZIP_SUPPORTED && \strlen($response_js) > 2000) {
                header('Content-Encoding: gzip');
                $response_js = gzencode($response_js, 1);
            }
        }

        return $response_js;
    }

    /**
     * Admin session
     * @throws Exception
     */
    public function check_admin_session()
    {
        global $user;

        if (!$user->data['session_admin']) {
            if (empty($this->request['user_password'])) {
                $this->prompt_for_password();
            } else {
                $login_args = [
                    'login_username' => $user->data['username'],
                    'login_password' => $_POST['user_password'],
                ];
                if (!$user->login($login_args, true)) {
                    $this->ajax_die('Wrong password');
                }
            }
        }
    }

    /**
     * Prompt for password
     * @throws Exception
     */
    public function prompt_for_password()
    {
        $this->response['prompt_password'] = 1;
        $this->send();
    }

    /**
     * Prompt for confirmation
     *
     * @param string $confirm_msg
     * @throws Exception
     */
    public function prompt_for_confirm(string $confirm_msg)
    {
        if (empty($confirm_msg)) {
            $this->ajax_die('false');
        }

        $this->response['prompt_confirm'] = 1;
        $this->response['confirm_msg'] = $confirm_msg;
        $this->send();
    }

    /**
     * Verify mod rights
     *
     * @param int|string $forum_id
     * @throws Exception
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
