<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy\Common;

use TorrentPier\Legacy\DateDelta;
use TorrentPier\Legacy\Torrent;
use TorrentPier\Sessions;

use Exception;

/**
 * Class User
 * @package TorrentPier\Legacy\Common
 */
class User
{
    /**
     * Config
     *
     * @var array
     */
    public $cfg = [
        'req_login' => false,    // requires user to be logged in
        'req_session_admin' => false,    // requires active admin session (for moderation or admin actions)
    ];

    /**
     * PHP-JS exchangeable options (JSON'ized as {USER_OPTIONS_JS} in TPL)
     *
     * @var array
     */
    public $opt_js = [
        'only_new' => 0,     // show ony new posts or topics
        'h_from' => 0,     // hide from
        'h_av' => 0,     // hide avatar
        'h_rnk_i' => 0,     // hide rank images
        'h_post_i' => 0,     // hide post images
        'h_smile' => 0,     // hide smilies
        'h_sig' => 0,     // hide signatures
        'sp_op' => 0,     // show spoiler opened
        'tr_t_ax' => 0,     // ajax open topics
        'tr_t_t' => 0,     // show time of the creation topics
        'hl_tr' => 1,     // show cursor in tracker.php
        'i_aft_l' => 0,     // show images only after full loading
        'h_tsp' => 0,     // show released title {...}
    ];

    /**
     * Defaults options for guests
     *
     * @var array
     */
    public $opt_js_guest = [
        'h_av' => 1,     // hide avatar
        'h_rnk_i' => 1,     // hide rank images
        'h_sig' => 1,     // hide signatures
    ];

    /**
     * Sessiondata
     *
     * @var array
     */
    public $sessiondata = [
        'uk' => null,
        'uid' => null,
        'sid' => '',
    ];

    /**
     * Old $userdata
     *
     * @var array
     */
    public $data = [];

    /**
     * Shortcuts
     *
     * @var
     */
    public $id;
    public $ip;
    public $lastvisit;
    public $regdate;
    public $opt;
    public $name;
    public $active;
    public $level;

    /**
     * User constructor
     */
    public function __construct()
    {
        $this->get_sessiondata();
    }

    /**
     * Start session (restore existent session or create new)
     *
     * @param array $cfg
     *
     * @return array|bool
     */
    public function session_start(array $cfg = [])
    {
        global $bb_cfg, $lang;

        $update_sessions_table = false;
        $this->cfg = array_merge($this->cfg, $cfg);

        $session_id = $this->sessiondata['sid'];

        // Does a session exist?
        if ($session_id || !$this->sessiondata['uk']) {
            $SQL = DB()->get_empty_sql_array();

            $SQL['SELECT'][] = "u.*, s.*";

            $SQL['FROM'][] = BB_SESSIONS . " s";
            $SQL['INNER JOIN'][] = BB_USERS . " u ON(u.user_id = s.session_user_id)";

            if ($session_id) {
                $SQL['WHERE'][] = "s.session_id = '$session_id'";

                if ($bb_cfg['torhelp_enabled']) {
                    $SQL['SELECT'][] = "th.topic_id_csv AS torhelp";
                    $SQL['LEFT JOIN'][] = BB_BT_TORHELP . " th ON(u.user_id = th.user_id)";
                }

                $userdata_cache_id = $session_id;
            } else {
                $SQL['WHERE'][] = "s.session_ip = '" . USER_IP . "'";
                $SQL['WHERE'][] = "s.session_user_id = " . GUEST_UID;

                $userdata_cache_id = USER_IP;
            }

            if (!$this->data = Sessions::cache_get_userdata($userdata_cache_id)) {
                $this->data = DB()->fetch_row($SQL);

                if ($this->data && (TIMENOW - $this->data['session_time']) > $bb_cfg['session_update_intrv']) {
                    $this->data['session_time'] = TIMENOW;
                    $update_sessions_table = true;
                }

                Sessions::cache_set_userdata($this->data);
            }
        }

        // Did the session exist in the DB?
        if ($this->data) {
            // Do not check IP assuming equivalence, if IPv4 we'll check only first 24
            // bits ... I've been told (by vHiker) this should alleviate problems with
            // load balanced et al proxies while retaining some reliance on IP security.
            $ip_check_s = substr($this->data['session_ip'], 0, 6);
            $ip_check_u = substr(USER_IP, 0, 6);

            if ($ip_check_s == $ip_check_u) {
                if ($this->data['user_id'] != GUEST_UID && defined('IN_ADMIN')) {
                    define('SID_GET', "sid={$this->data['session_id']}");
                }
                $session_id = $this->sessiondata['sid'] = $this->data['session_id'];

                // Only update session a minute or so after last update
                if ($update_sessions_table) {
                    DB()->query("
						UPDATE " . BB_SESSIONS . " SET
							session_time = " . TIMENOW . "
						WHERE session_id = '$session_id'
						LIMIT 1
					");
                }
                $this->set_session_cookies($this->data['user_id']);
            } else {
                $this->data = [];
            }
        }
        // If we reach here then no (valid) session exists. So we'll create a new one,
        // using the cookie user_id if available to pull basic user prefs.
        if (!$this->data) {
            $login = false;
            $user_id = ($bb_cfg['allow_autologin'] && $this->sessiondata['uk'] && $this->sessiondata['uid']) ? $this->sessiondata['uid'] : GUEST_UID;

            if ($userdata = get_userdata((int)$user_id, false, true)) {
                if ($userdata['user_id'] != GUEST_UID && $userdata['user_active']) {
                    if (verify_id($this->sessiondata['uk'], LOGIN_KEY_LENGTH) && $this->verify_autologin_id($userdata, true, false)) {
                        $login = ($userdata['autologin_id'] && $this->sessiondata['uk'] === $userdata['autologin_id']);
                    }
                }
            }
            if (!$userdata || ((int)$userdata['user_id'] !== GUEST_UID && !$login)) {
                $userdata = get_userdata(GUEST_UID, false, true);
            }

            $this->session_create($userdata, true);
        }

        define('IS_GUEST', !$this->data['session_logged_in']);
        define('IS_ADMIN', !IS_GUEST && (int)$this->data['user_level'] === ADMIN);
        define('IS_MOD', !IS_GUEST && (int)$this->data['user_level'] === MOD);
        define('IS_GROUP_MEMBER', !IS_GUEST && (int)$this->data['user_level'] === GROUP_MEMBER);
        define('IS_USER', !IS_GUEST && (int)$this->data['user_level'] === USER);
        define('IS_SUPER_ADMIN', IS_ADMIN && isset($bb_cfg['super_admins'][$this->data['user_id']]));
        define('IS_AM', IS_ADMIN || IS_MOD);

        $this->set_shortcuts();

        // Redirect guests to login page
        if (IS_GUEST && $this->cfg['req_login']) {
            login_redirect();
        }

        $this->init_userprefs();

        // Initial ban check
        if ($banInfo = getBanInfo((int)$this->id)) {
            $this->session_end();
            if (!empty($banInfo['ban_reason'])) {
                bb_die($lang['YOU_BEEN_BANNED'] . '<br><br>' . $lang['REASON'] . ':&nbsp;' . '<b>' . $banInfo['ban_reason'] . '</b>');
            } else {
                bb_die($lang['YOU_BEEN_BANNED']);
            }
        }

        // Merge additional flags with country flags
        if ($bb_cfg['enable_additional_flags']) {
            $lang['COUNTRIES'] = array_merge($lang['COUNTRIES'], $lang['ADDITIONAL_FLAGS']);
        }

        return $this->data;
    }

    /**
     * Create new session for the given user
     *
     * @param array $userdata
     * @param bool $auto_created
     *
     * @return array
     */
    public function session_create(array $userdata, bool $auto_created = false): array
    {
        global $bb_cfg;

        $this->data = $userdata;
        $session_id = $this->sessiondata['sid'];

        $login = ((int)$this->data['user_id'] !== GUEST_UID);
        $user_id = (int)$this->data['user_id'];
        $mod_admin_session = ((int)$this->data['user_level'] === ADMIN || (int)$this->data['user_level'] === MOD);

        // Generate passkey
        if (!Torrent::getPasskey($this->data['user_id'])) {
            if (!Torrent::generate_passkey($this->data['user_id'], true)) {
                bb_simple_die('Could not generate passkey');
            }
        }

        // Create new session
        for ($i = 0, $max_try = 5; $i <= $max_try; $i++) {
            $session_id = make_rand_str(SID_LENGTH);

            $args = DB()->build_array('INSERT', [
                'session_id' => (string)$session_id,
                'session_user_id' => (int)$user_id,
                'session_start' => (int)TIMENOW,
                'session_time' => (int)TIMENOW,
                'session_ip' => (string)USER_IP,
                'session_logged_in' => (int)$login,
                'session_admin' => (int)$mod_admin_session,
            ]);
            $sql = "INSERT INTO " . BB_SESSIONS . $args;

            if (DB()->query($sql)) {
                break;
            }
            if ($i == $max_try) {
                trigger_error('Error creating new session', E_USER_ERROR);
            }
        }
        // Update last visit for logged in users
        if ($login) {
            $last_visit = $this->data['user_lastvisit'];

            if (!$session_time = $this->data['user_session_time']) {
                $last_visit = TIMENOW;
                define('FIRST_LOGON', true);
            } elseif ($session_time < (TIMENOW - $bb_cfg['last_visit_update_intrv'])) {
                $last_visit = max($session_time, (TIMENOW - 86400 * $bb_cfg['max_last_visit_days']));
            }

            if ($last_visit != $this->data['user_lastvisit']) {
                DB()->query("
					UPDATE " . BB_USERS . " SET
						user_session_time = " . TIMENOW . ",
						user_lastvisit = $last_visit,
						user_last_ip = '" . USER_IP . "',
						user_reg_ip = IF(user_reg_ip = '', '" . USER_IP . "', user_reg_ip)
					WHERE user_id = $user_id
					LIMIT 1
				");

                bb_setcookie(COOKIE_TOPIC, null);
                bb_setcookie(COOKIE_FORUM, null);

                $this->data['user_lastvisit'] = $last_visit;
            }
            if (!empty($_POST['autologin']) && $bb_cfg['allow_autologin']) {
                if (!$auto_created) {
                    $this->verify_autologin_id($this->data, true, true);
                }
                $this->sessiondata['uk'] = $this->data['autologin_id'];
            }
            $this->sessiondata['uid'] = $user_id;
            $this->sessiondata['sid'] = $session_id;
        }
        $this->data['session_id'] = $session_id;
        $this->data['session_ip'] = USER_IP;
        $this->data['session_user_id'] = $user_id;
        $this->data['session_logged_in'] = $login;
        $this->data['session_start'] = TIMENOW;
        $this->data['session_time'] = TIMENOW;
        $this->data['session_admin'] = $mod_admin_session;

        $this->set_session_cookies($user_id);

        if ($login && (defined('IN_ADMIN') || $mod_admin_session)) {
            define('SID_GET', "sid=$session_id");
        }

        Sessions::cache_set_userdata($this->data);

        return $this->data;
    }

    /**
     * Initialize sessiondata stored in cookies
     *
     * @param bool $update_lastvisit
     * @param bool $set_cookie
     */
    public function session_end(bool $update_lastvisit = false, bool $set_cookie = true)
    {
        Sessions::cache_rm_userdata($this->data);
        DB()->query("
			DELETE FROM " . BB_SESSIONS . "
			WHERE session_id = '{$this->data['session_id']}'
		");

        if (!IS_GUEST) {
            if ($update_lastvisit) {
                DB()->query("
					UPDATE " . BB_USERS . " SET
						user_session_time = " . TIMENOW . ",
						user_lastvisit = " . TIMENOW . ",
						user_last_ip = '" . USER_IP . "',
						user_reg_ip = IF(user_reg_ip = '', '" . USER_IP . "', user_reg_ip)
					WHERE user_id = {$this->data['user_id']}
					LIMIT 1
				");
            }

            if (isset($_REQUEST['reset_autologin'])) {
                $this->create_autologin_id($this->data, false);

                Sessions::delete_user_sessions($this->data['user_id']);
            }
        }

        if ($set_cookie) {
            $this->set_session_cookies(GUEST_UID);
        }
    }

    /**
     * Login
     *
     * @param array $args
     * @param bool $mod_admin_login
     *
     * @return array
     */
    public function login(array $args, bool $mod_admin_login = false): array
    {
        $username = !empty($args['login_username']) ? clean_username($args['login_username']) : '';
        $password = !empty($args['login_password']) ? $args['login_password'] : '';

        if ($username && $password) {
            $username_sql = str_replace("\\'", "''", $username);

            $sql = "
				SELECT *
				FROM " . BB_USERS . "
				WHERE username = '$username_sql'
				  AND user_active = 1
				  AND user_id != " . GUEST_UID . "
				LIMIT 1
			";

            if ($userdata = DB()->fetch_row($sql)) {
                if (!$userdata['username'] || !$userdata['user_password'] || ($userdata['user_id'] == GUEST_UID) || !$userdata['user_active']) {
                    trigger_error('invalid userdata', E_USER_ERROR);
                }

                // Check password
                if (!$this->checkPassword($password, $userdata)) {
                    return [];
                }

                // Start mod/admin session
                if ($mod_admin_login) {
                    DB()->query("
						UPDATE " . BB_SESSIONS . " SET
							session_admin = " . $this->data['user_level'] . "
						WHERE session_user_id = " . $this->data['user_id'] . "
							AND session_id = '" . $this->data['session_id'] . "'
					");
                    $this->data['session_admin'] = $this->data['user_level'];
                    Sessions::cache_update_userdata($this->data);

                    return $this->data;
                }

                if ($new_session_userdata = $this->session_create($userdata, false)) {
                    // Removing guest sessions from this IP
                    DB()->query("
						DELETE FROM " . BB_SESSIONS . "
						WHERE session_ip = '" . USER_IP . "'
							AND session_user_id = " . GUEST_UID . "
					");

                    return $new_session_userdata;
                }

                trigger_error("Could not start session : login", E_USER_ERROR);
            }
        }

        return [];
    }

    /**
     * Initialize sessiondata stored in cookies
     */
    public function get_sessiondata()
    {
        $sd_resv = !empty($_COOKIE[COOKIE_DATA]) ? unserialize($_COOKIE[COOKIE_DATA], ['allowed_classes' => false]) : [];

        // autologin_id
        if (!empty($sd_resv['uk']) && verify_id($sd_resv['uk'], LOGIN_KEY_LENGTH)) {
            $this->sessiondata['uk'] = $sd_resv['uk'];
        }
        // user_id
        if (!empty($sd_resv['uid'])) {
            $this->sessiondata['uid'] = (int)$sd_resv['uid'];
        }
        // sid
        if (!empty($sd_resv['sid']) && verify_id($sd_resv['sid'], SID_LENGTH)) {
            $this->sessiondata['sid'] = $sd_resv['sid'];
        }
    }

    /**
     * Store sessiondata in cookies
     *
     * @param $user_id
     */
    public function set_session_cookies($user_id)
    {
        if ($user_id == GUEST_UID) {
            $delete_cookies = [COOKIE_DATA, 'torhelp'];

            foreach ($delete_cookies as $cookie) {
                if (isset($_COOKIE[$cookie])) {
                    bb_setcookie($cookie, null);
                }
            }
        } else {
            $c_sdata_resv = !empty($_COOKIE[COOKIE_DATA]) ? $_COOKIE[COOKIE_DATA] : null;
            $c_sdata_curr = ($this->sessiondata) ? serialize($this->sessiondata) : '';

            if ($c_sdata_curr !== $c_sdata_resv) {
                bb_setcookie(COOKIE_DATA, $c_sdata_curr, httponly: true);
            }
        }
    }

    /**
     * Verify autologin_id
     *
     * @param $userdata
     * @param bool $expire_check
     * @param bool $create_new
     *
     * @return bool|string
     */
    public function verify_autologin_id($userdata, bool $expire_check = false, bool $create_new = true): bool|string
    {
        global $bb_cfg;

        $autologin_id = $userdata['autologin_id'];

        if ($expire_check) {
            if ($create_new && !$autologin_id) {
                return $this->create_autologin_id($userdata);
            }

            if ($autologin_id && $userdata['user_session_time'] && $bb_cfg['max_autologin_time']) {
                if (TIMENOW - $userdata['user_session_time'] > $bb_cfg['max_autologin_time'] * 86400) {
                    return $this->create_autologin_id($userdata, $create_new);
                }
            }
        }

        return verify_id($autologin_id, LOGIN_KEY_LENGTH);
    }

    /**
     * Create autologin_id
     *
     * @param array $userdata
     * @param bool $create_new
     *
     * @return string
     * @throws Exception
     */
    public function create_autologin_id(array $userdata, bool $create_new = true): string
    {
        $autologin_id = $create_new ? make_rand_str(LOGIN_KEY_LENGTH) : '';

        DB()->query("
			UPDATE " . BB_USERS . " SET
				autologin_id = '$autologin_id'
			WHERE user_id = " . (int)$userdata['user_id'] . "
			LIMIT 1
		");

        return $autologin_id;
    }

    /**
     * Set shortcuts
     */
    public function set_shortcuts()
    {
        $this->id =& $this->data['user_id'];
        $this->active =& $this->data['user_active'];
        $this->name =& $this->data['username'];
        $this->lastvisit =& $this->data['user_lastvisit'];
        $this->regdate =& $this->data['user_regdate'];
        $this->level =& $this->data['user_level'];
        $this->opt =& $this->data['user_opt'];
        $this->ip = CLIENT_IP;
    }

    /**
     * Initialise user settings
     */
    public function init_userprefs()
    {
        global $bb_cfg, $theme, $source_lang, $DeltaTime;

        if (defined('LANG_DIR')) {
            return;
        }  // prevent multiple calling

        if (IS_GUEST && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) { // Apply browser language
            $http_accept_language = locale_get_primary_language(locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']));
            if (isset($bb_cfg['lang'][$http_accept_language])) {
                $bb_cfg['default_lang'] = $http_accept_language;
            }
        }

        define('DEFAULT_LANG_DIR', LANG_ROOT_DIR . '/' . $bb_cfg['default_lang'] . '/');
        define('SOURCE_LANG_DIR', LANG_ROOT_DIR . '/source/');

        if ($this->data['user_id'] != GUEST_UID) {
            if ($this->data['user_lang'] && $this->data['user_lang'] != $bb_cfg['default_lang']) {
                $bb_cfg['default_lang'] = basename($this->data['user_lang']);
                define('LANG_DIR', LANG_ROOT_DIR . '/' . $bb_cfg['default_lang'] . '/');
            }

            if (isset($this->data['user_timezone'])) {
                $bb_cfg['board_timezone'] = $this->data['user_timezone'];
            }
        }

        $this->data['user_lang'] = $bb_cfg['default_lang'];
        $this->data['user_timezone'] = $bb_cfg['board_timezone'];

        if (!defined('LANG_DIR')) {
            define('LANG_DIR', DEFAULT_LANG_DIR);
        }

        /** Temporary place source language to the global */
        $lang = [];
        require(SOURCE_LANG_DIR . 'main.php');
        $source_lang = $lang;
        unset($lang);

        /** Place user language to the global */
        global $lang;
        require(LANG_DIR . 'main.php');
        setlocale(LC_ALL, $bb_cfg['lang'][$this->data['user_lang']]['locale'] ?? 'en_US.UTF-8');
        $lang += $source_lang;

        $theme = setup_style();
        $DeltaTime = new DateDelta();

        // Handle marking posts read
        if (!IS_GUEST && !empty($_COOKIE[COOKIE_MARK])) {
            $this->mark_read($_COOKIE[COOKIE_MARK]);
        }

        $this->load_opt_js();
    }

    /**
     * Mark read
     *
     * @param $type
     */
    public function mark_read($type)
    {
        if ($type === 'all_forums') {
            // Update session time
            DB()->query("
				UPDATE " . BB_SESSIONS . " SET
					session_time = " . TIMENOW . "
				WHERE session_id = '{$this->data['session_id']}'
				LIMIT 1
			");

            // Update userdata
            $this->data['session_time'] = TIMENOW;
            $this->data['user_lastvisit'] = TIMENOW;

            // Update lastvisit
            Sessions::db_update_userdata($this->data, [
                'user_session_time' => $this->data['session_time'],
                'user_lastvisit' => $this->data['user_lastvisit'],
            ]);

            // Delete cookies
            bb_setcookie(COOKIE_TOPIC, null);
            bb_setcookie(COOKIE_FORUM, null);
            bb_setcookie(COOKIE_MARK, null);
        }
    }

    /**
     * Load misc options
     */
    public function load_opt_js()
    {
        if (IS_GUEST) {
            $this->opt_js = array_merge($this->opt_js, $this->opt_js_guest);
        } elseif (!empty($_COOKIE['opt_js'])) {
            $opt_js = json_decode($_COOKIE['opt_js'], true, 512, JSON_THROW_ON_ERROR);

            if (is_array($opt_js)) {
                $this->opt_js = array_merge($this->opt_js, $opt_js);
            }
        }
    }

    /**
     * Get not auth forums
     *
     * @param $auth_type
     *
     * @return string
     */
    public function get_not_auth_forums($auth_type)
    {
        global $datastore;

        if (IS_ADMIN) {
            return '';
        }

        if (!$forums = $datastore->get('cat_forums') and !$datastore->has('cat_forums')) {
            $datastore->update('cat_forums');
            $forums = $datastore->get('cat_forums');
        }

        if ($auth_type == AUTH_VIEW) {
            if (IS_GUEST) {
                return $forums['not_auth_forums']['guest_view'];
            }
        }
        if ($auth_type == AUTH_READ) {
            if (IS_GUEST) {
                return $forums['not_auth_forums']['guest_read'];
            }
        }

        $auth_field_match = [
            AUTH_VIEW => 'auth_view',
            AUTH_READ => 'auth_read',
            AUTH_POST => 'auth_post',
            AUTH_REPLY => 'auth_reply',
            AUTH_EDIT => 'auth_edit',
            AUTH_DELETE => 'auth_delete',
            AUTH_STICKY => 'auth_sticky',
            AUTH_ANNOUNCE => 'auth_announce',
            AUTH_VOTE => 'auth_vote',
            AUTH_POLLCREATE => 'auth_pollcreate',
            AUTH_ATTACH => 'auth_attachments',
            AUTH_DOWNLOAD => 'auth_download',
        ];

        $not_auth_forums = [];
        $auth_field = $auth_field_match[$auth_type];
        $is_auth_ary = auth($auth_type, AUTH_LIST_ALL, $this->data);

        foreach ($is_auth_ary as $forum_id => $is_auth) {
            if (!$is_auth[$auth_field]) {
                $not_auth_forums[] = $forum_id;
            }
        }

        return implode(',', $not_auth_forums);
    }

    /**
     * Get excluded forums
     *
     * @param $auth_type
     * @param string $return_as
     *
     * @return array|string
     */
    public function get_excluded_forums($auth_type, string $return_as = 'csv')
    {
        $excluded = [];

        if ($not_auth = $this->get_not_auth_forums($auth_type)) {
            $excluded[] = $not_auth;
        }

        if (bf($this->opt, 'user_opt', 'user_porn_forums')) {
            global $datastore;

            if (!$forums = $datastore->get('cat_forums') and !$datastore->has('cat_forums')) {
                $datastore->update('cat_forums');
                $forums = $datastore->get('cat_forums');
            }

            if (isset($forums['forum'])) {
                foreach ($forums['forum'] as $key => $row) {
                    if ($row['allow_porno_topic']) {
                        $excluded[] = $row['forum_id'];
                    }
                }
            }
        }

        return match ($return_as) {
            'csv' => implode(',', $excluded),
            'flip_csv' => implode(',', array_flip($excluded)),
            'array' => $excluded,
            'flip' => array_flip($excluded),
            default => [],
        };
    }

    /**
     * Check entered password
     *
     * @param string $enteredPassword
     * @param array $userdata
     * @return bool
     */
    public function checkPassword(string $enteredPassword, array $userdata): bool
    {
        global $bb_cfg;

        if (password_verify($enteredPassword, $userdata['user_password'])) {
            if (password_needs_rehash($userdata['user_password'], $bb_cfg['password_hash_options']['algo'], $bb_cfg['password_hash_options']['options'])) {
                // Update password_hash
                DB()->query("UPDATE " . BB_USERS . " SET user_password = '" . $this->password_hash($enteredPassword) . "' WHERE user_id = '" . $userdata['user_id'] . "' AND user_password = '" . $userdata['user_password'] . "' LIMIT 1");
            }

            return true;
        }

        return false;
    }

    /**
     * Create password_hash
     *
     * @param string $enteredPassword
     * @return string
     */
    public function password_hash(string $enteredPassword): string
    {
        global $bb_cfg;

        return password_hash($enteredPassword, $bb_cfg['password_hash_options']['algo'], $bb_cfg['password_hash_options']['options']);
    }
}
