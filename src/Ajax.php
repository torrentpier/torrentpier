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
        'passkey' => ['user'],
        'change_torrent' => ['user'],
        'change_tor_status' => ['user'],
        'thx' => ['user'],
        'manage_group' => ['user'],
        'callseed' => ['user'],

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
     *
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
            if (!isset($action_params[1]) || $action_params[1] !== true) {
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
    public function ajax_die(string $error_msg, int $error_code = E_AJAX_GENERAL_ERROR): void
    {
        $this->response['error_code'] = $error_code;
        $this->response['error_msg'] = strip_tags(br2nl($error_msg));

        // Get caller info
        if (!empty($_COOKIE['explain'])) {
            $ajax_debug = 'ajax die: ' . $this->debug_find_source();
            $this->response['error_msg'] .= "\n\n" . $ajax_debug;
            $this->response['console_log'] = $ajax_debug;
        }

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
     *
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
     *
     * @throws Exception
     */
    public function check_admin_session()
    {
        global $user, $lang;

        if (!$user->data['session_admin']) {
            if (empty($this->request['user_password'])) {
                $this->prompt_for_password();
            } else {
                $login_args = [
                    'login_username' => $user->data['username'],
                    'login_password' => $_POST['user_password'],
                ];
                if (!$user->login($login_args, true)) {
                    $this->ajax_die($lang['ERROR_LOGIN']);
                }
            }
        }
    }

    /**
     * Prompt for password
     *
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
    public function prompt_for_confirm(string $confirm_msg): void
    {
        if (empty($confirm_msg)) {
            $this->ajax_die('false');
        }

        $this->response['prompt_confirm'] = 1;
        $this->response['confirm_msg'] = strip_tags(br2nl($confirm_msg));
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

    /**
     * Find caller source
     *
     * @param string $mode
     * @return mixed|string
     */
    public function debug_find_source(string $mode = 'all'): mixed
    {
        if (empty($_COOKIE['explain'])) {
            return 'src disabled';
        }
        foreach (debug_backtrace() as $trace) {
            if (!empty($trace['file']) && $trace['file'] !== __FILE__) {
                switch ($mode) {
                    case 'file':
                        return $trace['file'];
                    case 'line':
                        return $trace['line'];
                    case 'all':
                    default:
                        return hide_bb_path($trace['file']) . '(' . $trace['line'] . ')';
                }
            }
        }
        return 'src not found';
    }

    /**
     * Edit user profile actions
     *
     * @return void
     */
    public function edit_user_profile()
    {
        require AJAX_DIR . '/edit_user_profile.php';
    }

    /**
     * Change user rank actions
     *
     * @return void
     */
    public function change_user_rank()
    {
        require AJAX_DIR . '/change_user_rank.php';
    }

    /**
     * Change user opt actions
     *
     * @return void
     */
    public function change_user_opt()
    {
        require AJAX_DIR . '/change_user_opt.php';
    }

    /**
     * Passkey actions
     *
     * @return void
     */
    public function passkey()
    {
        require AJAX_DIR . '/passkey.php';
    }

    /**
     * Group membership actions
     *
     * @return void
     */
    public function group_membership()
    {
        require AJAX_DIR . '/group_membership.php';
    }

    /**
     * Manage group actions
     *
     * @return void
     */
    public function manage_group()
    {
        require AJAX_DIR . '/edit_group_profile.php';
    }

    /**
     * Post moderator comment actions
     *
     * @return void
     */
    public function post_mod_comment()
    {
        require AJAX_DIR . '/post_mod_comment.php';
    }

    /**
     * View post actions
     *
     * @return void
     */
    public function view_post()
    {
        require AJAX_DIR . '/view_post.php';
    }

    /**
     * Change torrent status actions
     *
     * @return void
     */
    public function change_tor_status()
    {
        require AJAX_DIR . '/change_tor_status.php';
    }

    /**
     * Change torrent actions
     *
     * @return void
     */
    public function change_torrent()
    {
        require AJAX_DIR . '/change_torrent.php';
    }

    /**
     * View torrent actions
     *
     * @return void
     */
    public function view_torrent()
    {
        require AJAX_DIR . '/view_torrent.php';
    }

    /**
     * User registration actions
     *
     * @return void
     */
    public function user_register()
    {
        require AJAX_DIR . '/user_register.php';
    }

    /**
     * Moderator actions
     *
     * @return void
     */
    public function mod_action()
    {
        require AJAX_DIR . '/mod_action.php';
    }

    /**
     * Posts actions
     *
     * @return void
     */
    public function posts()
    {
        require AJAX_DIR . '/posts.php';
    }

    /**
     * Manage user actions
     *
     * @return void
     */
    public function manage_user()
    {
        require AJAX_DIR . '/manage_user.php';
    }

    /**
     * Manage admin actions
     *
     * @return void
     */
    public function manage_admin()
    {
        require AJAX_DIR . '/manage_admin.php';
    }

    /**
     * Topic tpl actions
     *
     * @return void
     */
    public function topic_tpl()
    {
        require AJAX_DIR . '/topic_tpl.php';
    }

    /**
     * Index data actions
     *
     * @return void
     */
    public function index_data()
    {
        require AJAX_DIR . '/index_data.php';
    }

    /**
     * Avatar actions
     *
     * @return void
     */
    public function avatar()
    {
        require AJAX_DIR . '/avatar.php';
    }

    /**
     * Sitemap actions
     *
     * @return void
     */
    public function sitemap()
    {
        require AJAX_DIR . '/sitemap.php';
    }

    /**
     * Call seed actions
     *
     * @return void
     */
    public function callseed()
    {
        require AJAX_DIR . '/callseed.php';
    }

    /**
     * Get / Set votes
     *
     *
     * @return void
     */

    public function thx()
    {
        global $bb_cfg, $lang, $userdata;

        if (!$bb_cfg['tor_thank']) $this->ajax_die($lang['DISABLED']);

        $mode = (string) $this->request['mode'];
        $topic_id = (int) $this->request['topic_id'];

        switch($mode)
        {
            case 'add':
                $row = DB()->fetch_row('SELECT * FROM '. BB_THX ." WHERE topic_id = $topic_id  AND user_id = ". $userdata['user_id']);

                if ($row) {
                    $this->ajax_die($lang['LIKE_ALREADY']);
                }

                if (DB()->fetch_row('SELECT poster_id FROM ' . BB_BT_TORRENTS . " WHERE topic_id = $topic_id AND poster_id = " . $userdata['user_id'])) {
                    $this->ajax_die($lang['LIKE_OWN_POST']);
                }

                $columns = 'topic_id, user_id, time';
                $values = "$topic_id, {$userdata['user_id']}, " . TIMENOW;
                DB()->query('INSERT IGNORE INTO ' . BB_THX . " ($columns) VALUES ($values)");
                $this->response['html'] = '<b>' . profile_url($userdata) . ' <i>('.  bb_date(TIMENOW) . ')</i></b>';
                break;

            case 'get':
                $sql = DB()->fetch_rowset('SELECT u.username, u.user_rank, u.user_id, t.* FROM ' . BB_THX . ' t, '. BB_USERS . " u WHERE t.topic_id = $topic_id AND t.user_id = u.user_id");
                $user_list = [];
                foreach ($sql as $row) {
                    $user_list[] = '<b>' . profile_url($row) . ' <i>(' . bb_date($row['time']) . ')</i></b>';
                }
                $thx_list = join(' ', $user_list);
                $this->response['html'] = ($thx_list) ? $thx_list : $lang['NO_LIKES'];
                break;
        }
    }
}
