<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy\Common;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;
use TorrentPier\Sessions;

/**
 * Class User
 * @package TorrentPier\Legacy\Common
 */
class User
{
    /**
     * Config
     */
    public array $cfg = [
        'req_login' => false,    // requires user to be logged in
        'req_session_admin' => false,    // requires active admin session (for moderation or admin actions)
    ];

    /**
     * PHP-JS exchangeable options (JSON'ized as {USER_OPTIONS_JS} in TPL)
     */
    public array $opt_js = [
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
     */
    public array $opt_js_guest = [
        'h_av' => 1,     // hide avatar
        'h_rnk_i' => 1,     // hide rank images
        'h_sig' => 1,     // hide signatures
    ];

    /**
     * Sessiondata
     */
    public array $sessiondata = [
        'uk' => null,
        'uid' => null,
        'sid' => '',
    ];

    /**
     * Old $userdata
     */
    public array $data = [];

    /**
     * Shortcuts
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
     *
     * @throws BindingResolutionException
     * @return array|bool
     */
    public function session_start(array $cfg = [])
    {
        // Merge configuration
        $this->cfg = array_merge($this->cfg, $cfg);

        // If the session already started, just apply req_login check
        if (\defined('SESSION_STARTED')) {
            if (IS_GUEST && $this->cfg['req_login']) {
                login_redirect();
            }

            return $this->data;
        }

        $update_sessions_table = false;

        $session_id = $this->sessiondata['sid'];

        // Does a session exist?
        if ($session_id || !$this->sessiondata['uk']) {
            $SQL = DB()->get_empty_sql_array();

            $SQL['SELECT'][] = 'u.*, s.*';

            $SQL['FROM'][] = BB_SESSIONS . ' s';
            $SQL['INNER JOIN'][] = BB_USERS . ' u ON(u.user_id = s.session_user_id)';

            if ($session_id) {
                $SQL['WHERE'][] = "s.session_id = '{$session_id}'";

                if (config()->get('tracker.torhelp_enabled')) {
                    $SQL['SELECT'][] = 'th.topic_id_csv AS torhelp';
                    $SQL['LEFT JOIN'][] = BB_BT_TORHELP . ' th ON(u.user_id = th.user_id)';
                }

                $userdata_cache_id = $session_id;
            } else {
                $SQL['WHERE'][] = "s.session_ip = '" . USER_IP . "'";
                $SQL['WHERE'][] = 's.session_user_id = ' . GUEST_UID;

                $userdata_cache_id = USER_IP;
            }

            $cachedData = Sessions::cache_get_userdata($userdata_cache_id);
            if ($cachedData) {
                $this->data = $cachedData;
            } else {
                $rowData = DB()->fetch_row($SQL);
                if ($rowData) {
                    $this->data = $rowData;

                    if ((TIMENOW - $this->data['session_time']) > config()->get('auth.sessions.update_interval')) {
                        $this->data['session_time'] = TIMENOW;
                        $update_sessions_table = true;
                    }

                    Sessions::cache_set_userdata($this->data);
                }
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
                if ($this->data['user_id'] != GUEST_UID && \defined('IN_ADMIN')) {
                    \define('SID_GET', "sid={$this->data['session_id']}");
                }
                $session_id = $this->sessiondata['sid'] = $this->data['session_id'];

                // Only update session a minute or so after last update
                if ($update_sessions_table) {
                    DB()->query('
						UPDATE ' . BB_SESSIONS . ' SET
							session_time = ' . $this->data['session_time'] . "
						WHERE session_id = '{$session_id}'
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
            $user_id = (config()->get('allow_autologin') && $this->sessiondata['uk'] && $this->sessiondata['uid']) ? $this->sessiondata['uid'] : GUEST_UID;

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

            // Users with 2FA enabled cannot autologin — force login flow
            if ($login && !empty($userdata['totp_enabled']) && config()->get('auth.two_factor.enabled')) {
                $userdata = get_userdata(GUEST_UID, false, true);
                $login = false;
                $this->set_session_cookies(GUEST_UID);
            }

            $this->session_create($userdata, true);
        }

        \define('IS_GUEST', !$this->data['session_logged_in']);
        \define('IS_ADMIN', !IS_GUEST && (int)$this->data['user_level'] === ADMIN);
        \define('IS_MOD', !IS_GUEST && (int)$this->data['user_level'] === MOD);
        \define('IS_GROUP_MEMBER', !IS_GUEST && (int)$this->data['user_level'] === GROUP_MEMBER);
        \define('IS_USER', !IS_GUEST && (int)$this->data['user_level'] === USER);
        \define('IS_SUPER_ADMIN', IS_ADMIN && isset(config()->get('auth.super_admins')[$this->data['user_id']]));
        \define('IS_AM', IS_ADMIN || IS_MOD);
        \define('IS_PREMIUM', !IS_GUEST && isset(config()->get('auth.premium_users')[$this->data['user_id']]));

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
                bb_die(__('YOU_BEEN_BANNED') . '<br/><br/>' . __('REASON') . ':&nbsp;<b>' . $banInfo['ban_reason'] . '</b>');
            } else {
                bb_die(__('YOU_BEEN_BANNED'));
            }
        }

        return $this->data;
    }

    /**
     * Create new session for the given user
     */
    public function session_create(array $userdata, bool $auto_created = false): array
    {
        $this->data = $userdata;
        $session_id = $this->sessiondata['sid'];

        $login = ((int)$this->data['user_id'] !== GUEST_UID);
        $user_id = (int)$this->data['user_id'];
        $mod_admin_session = ((int)$this->data['user_level'] === ADMIN || (int)$this->data['user_level'] === MOD);

        // Create new session
        for ($i = 0, $max_try = 5; $i <= $max_try; $i++) {
            $session_id = Str::random(SID_LENGTH);

            $args = DB()->build_array('INSERT', [
                'session_id' => (string)$session_id,
                'session_user_id' => (int)$user_id,
                'session_start' => (int)TIMENOW,
                'session_time' => (int)TIMENOW,
                'session_ip' => (string)USER_IP,
                'session_logged_in' => (int)$login,
                'session_admin' => (int)$mod_admin_session,
            ]);
            $sql = 'INSERT INTO ' . BB_SESSIONS . $args;

            if (DB()->query($sql)) {
                break;
            }
            if ($i == $max_try) {
                throw new RuntimeException('Error creating new session');
            }
        }
        // Update last visit for logged in users
        if ($login) {
            $last_visit = $this->data['user_lastvisit'];

            if (!$session_time = $this->data['user_session_time']) {
                $last_visit = TIMENOW;
                \define('FIRST_LOGON', true);
            } elseif ($session_time < (TIMENOW - config()->get('auth.sessions.last_visit_update_interval'))) {
                $last_visit = max($session_time, (TIMENOW - 86400 * config()->get('auth.sessions.max_last_visit_days')));
            }

            if ($last_visit != $this->data['user_lastvisit']) {
                DB()->query('
					UPDATE ' . BB_USERS . ' SET
						user_session_time = ' . TIMENOW . ",
						user_lastvisit = {$last_visit},
						user_last_ip = '" . USER_IP . "',
						user_reg_ip = IF(user_reg_ip = '', '" . USER_IP . "', user_reg_ip)
					WHERE user_id = {$user_id}
					LIMIT 1
				");

                bb_setcookie(COOKIE_TOPIC, '');
                bb_setcookie(COOKIE_FORUM, '');

                $this->data['user_lastvisit'] = $last_visit;
            }
            if (request()->post->has('autologin') && config()->get('allow_autologin')) {
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

        if ($login && (\defined('IN_ADMIN') || $mod_admin_session)) {
            \define('SID_GET', "sid={$session_id}");
        }

        Sessions::cache_set_userdata($this->data);

        return $this->data;
    }

    /**
     * Initialize sessiondata stored in cookies
     */
    public function session_end(bool $update_lastvisit = false, bool $set_cookie = true)
    {
        if ($this->data && \is_array($this->data)) {
            Sessions::cache_rm_userdata($this->data);
            DB()->query('
                DELETE FROM ' . BB_SESSIONS . "
                WHERE session_id = '{$this->data['session_id']}'
            ");
        }

        if (!IS_GUEST) {
            if ($update_lastvisit) {
                DB()->query('
					UPDATE ' . BB_USERS . ' SET
						user_session_time = ' . TIMENOW . ',
						user_lastvisit = ' . TIMENOW . ",
						user_last_ip = '" . USER_IP . "',
						user_reg_ip = IF(user_reg_ip = '', '" . USER_IP . "', user_reg_ip)
					WHERE user_id = {$this->data['user_id']}
					LIMIT 1
				");
            }

            if (request()->has('reset_autologin')) {
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
     */
    public function login(array $args, bool $mod_admin_login = false): array
    {
        $username = !empty($args['login_username']) ? clean_username($args['login_username']) : '';
        $password = !empty($args['login_password']) ? $args['login_password'] : '';

        if ($username && $password) {
            $username_sql = str_replace("\\'", "''", $username);

            $sql = '
				SELECT *
				FROM ' . BB_USERS . "
				WHERE username = '{$username_sql}'
				  AND user_active = 1
				  AND user_id != " . GUEST_UID . '
				LIMIT 1
			';

            if ($userdata = DB()->fetch_row($sql)) {
                if (!$userdata['username'] || !$userdata['user_password'] || ($userdata['user_id'] == GUEST_UID) || !$userdata['user_active']) {
                    throw new RuntimeException('invalid userdata');
                }

                // Check password
                if (!$this->checkPassword($password, $userdata)) {
                    return [];
                }

                // Check if 2FA is enabled
                if (config()->get('auth.two_factor.enabled') && two_factor()->isEnabled($userdata)) {
                    $pendingToken = Str::random(32);
                    CACHE('bb_cache')->set('2fa_pending_' . $pendingToken, [
                        'user_id' => $userdata['user_id'],
                        'mod_admin_login' => $mod_admin_login,
                        'ip' => USER_IP,
                    ], config()->get('auth.two_factor.pending_ttl'));
                    return ['2fa_required' => true, '2fa_token' => $pendingToken];
                }

                // Start mod/admin session
                if ($mod_admin_login) {
                    DB()->query('
						UPDATE ' . BB_SESSIONS . ' SET
							session_admin = ' . $this->data['user_level'] . '
						WHERE session_user_id = ' . $this->data['user_id'] . "
							AND session_id = '" . $this->data['session_id'] . "'
					");
                    $this->data['session_admin'] = $this->data['user_level'];
                    Sessions::cache_update_userdata($this->data);

                    return $this->data;
                }

                if ($new_session_userdata = $this->session_create($userdata, false)) {
                    // Removing guest sessions from this IP
                    DB()->query('
						DELETE FROM ' . BB_SESSIONS . "
						WHERE session_ip = '" . USER_IP . "'
							AND session_user_id = " . GUEST_UID . '
					');

                    return $new_session_userdata;
                }

                throw new RuntimeException('Could not start session : login');
            }
        }

        return [];
    }

    /**
     * Initialize sessiondata stored in cookies
     */
    public function get_sessiondata()
    {
        $sd_resv = !empty($_COOKIE[COOKIE_DATA]) ? json_decode($_COOKIE[COOKIE_DATA], true) : [];

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
     */
    public function set_session_cookies($user_id)
    {
        if ($user_id == GUEST_UID) {
            // Clean up session cookies on logout
            foreach ([COOKIE_DATA, 'torhelp'] as $cookie) {
                if (isset($_COOKIE[$cookie])) {
                    bb_setcookie($cookie, '', COOKIE_EXPIRED, true);
                }
            }
        } else {
            // Set bb_data (session) cookie
            $c_sdata_resv = !empty($_COOKIE[COOKIE_DATA]) ? $_COOKIE[COOKIE_DATA] : null;
            $c_sdata_curr = ($this->sessiondata) ? json_encode($this->sessiondata) : '';

            if ($c_sdata_curr !== $c_sdata_resv) {
                bb_setcookie(COOKIE_DATA, $c_sdata_curr, COOKIE_PERSIST, true);
            }
        }
    }

    /**
     * Verify autologin_id
     */
    public function verify_autologin_id($userdata, bool $expire_check = false, bool $create_new = true): bool|string
    {
        $autologin_id = $userdata['autologin_id'];

        if ($expire_check) {
            if ($create_new && !$autologin_id) {
                return $this->create_autologin_id($userdata);
            }

            if ($autologin_id && $userdata['user_session_time'] && config()->get('max_autologin_time')) {
                if (TIMENOW - $userdata['user_session_time'] > config()->get('max_autologin_time') * 86400) {
                    return $this->create_autologin_id($userdata, $create_new);
                }
            }
        }

        return verify_id($autologin_id, LOGIN_KEY_LENGTH);
    }

    /**
     * Create autologin_id
     *
     *
     * @throws Exception
     */
    public function create_autologin_id(array $userdata, bool $create_new = true): string
    {
        $autologin_id = $create_new ? Str::random(LOGIN_KEY_LENGTH) : '';

        DB()->query('
			UPDATE ' . BB_USERS . " SET
				autologin_id = '{$autologin_id}'
			WHERE user_id = " . (int)$userdata['user_id'] . '
			LIMIT 1
		');

        return $autologin_id;
    }

    /**
     * Set shortcuts
     */
    public function set_shortcuts()
    {
        $this->id = &$this->data['user_id'];
        $this->active = &$this->data['user_active'];
        $this->name = &$this->data['username'];
        $this->lastvisit = &$this->data['user_lastvisit'];
        $this->regdate = &$this->data['user_regdate'];
        $this->level = &$this->data['user_level'];
        $this->opt = &$this->data['user_opt'];
        $this->ip = CLIENT_IP;
    }

    /**
     * Initialise user settings
     * @throws BindingResolutionException|JsonException
     */
    public function init_userprefs()
    {
        if (\defined('LANG_DIR')) {
            return;
        }  // prevent multiple calling

        // Apply browser language
        $acceptLanguage = request()->headers->get('Accept-Language');
        if (config()->get('localization.auto_language_detection') && IS_GUEST && $acceptLanguage) {
            $http_accept_language = locale_get_primary_language(locale_accept_from_http($acceptLanguage));
            if (isset(config()->get('localization.languages')[$http_accept_language])) {
                config()->set('localization.default_lang', $http_accept_language);
            }
        }

        \define('SOURCE_LANG_DIR', LANG_ROOT_DIR . 'source/');

        // Determine language directory with fallback to source
        $defaultLangPath = LANG_ROOT_DIR . config()->get('localization.default_lang') . '/';
        \define('DEFAULT_LANG_DIR', files()->isDirectory($defaultLangPath) ? $defaultLangPath : SOURCE_LANG_DIR);

        if ($this->data['user_id'] != GUEST_UID) {
            if ($this->data['user_lang'] && $this->data['user_lang'] != config()->get('localization.default_lang')) {
                config()->set('localization.default_lang', basename($this->data['user_lang']));
                $userLangPath = LANG_ROOT_DIR . config()->get('localization.default_lang') . '/';
                if (files()->isDirectory($userLangPath)) {
                    \define('LANG_DIR', $userLangPath);
                }
            }

            if (isset($this->data['user_timezone'])) {
                config()->set('localization.board_timezone', $this->data['user_timezone']);
            }
        }

        $this->data['user_lang'] = config()->get('localization.default_lang');
        $this->data['user_timezone'] = config()->get('localization.board_timezone');

        if (!\defined('LANG_DIR')) {
            \define('LANG_DIR', DEFAULT_LANG_DIR);
        }

        // Initialize Language singleton with user preferences
        lang()->initializeLanguage($this->data['user_lang']);

        setup_style();

        // Handle marking posts read
        if (!IS_GUEST && !empty($_COOKIE[COOKIE_MARK])) {
            $this->mark_read($_COOKIE[COOKIE_MARK]);
        }

        $this->load_opt_js();
    }

    /**
     * Mark read
     */
    public function mark_read($type)
    {
        if ($type === 'all_forums') {
            // Update session time
            DB()->query('
				UPDATE ' . BB_SESSIONS . ' SET
					session_time = ' . TIMENOW . "
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
            bb_setcookie(COOKIE_TOPIC, '');
            bb_setcookie(COOKIE_FORUM, '');
            bb_setcookie(COOKIE_MARK, '');
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

            if (\is_array($opt_js)) {
                $this->opt_js = array_merge($this->opt_js, $opt_js);
            }
        }
    }

    /**
     * Get not auth forums
     *
     *
     * @throws BindingResolutionException
     * @return string
     */
    public function get_not_auth_forums($auth_type)
    {
        if (IS_ADMIN) {
            return '';
        }

        $forums = forum_tree();

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
            $forums = forum_tree();

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
     */
    public function checkPassword(string $enteredPassword, array $userdata): bool
    {
        if (password_verify($enteredPassword, $userdata['user_password'])) {
            if (password_needs_rehash($userdata['user_password'], config()->get('auth.password.hash_options.algo'), config()->get('auth.password.hash_options.options'))) {
                // Update password_hash
                DB()->query('UPDATE ' . BB_USERS . " SET user_password = '" . $this->password_hash($enteredPassword) . "' WHERE user_id = '" . $userdata['user_id'] . "' AND user_password = '" . $userdata['user_password'] . "' LIMIT 1");
            }

            return true;
        }
        if (hash('md5', hash('md5', $enteredPassword)) === $userdata['user_password']) {
            // Update old md5 password
            DB()->query('UPDATE ' . BB_USERS . " SET user_password = '" . $this->password_hash($enteredPassword) . "' WHERE user_id = '" . $userdata['user_id'] . "' AND user_password = '" . $userdata['user_password'] . "' LIMIT 1");

            return true;
        }

        return false;
    }

    /**
     * Create password_hash
     */
    public function password_hash(string $enteredPassword): string
    {
        return password_hash($enteredPassword, config()->get('auth.password.hash_options.algo'), config()->get('auth.password.hash_options.options'));
    }
}
