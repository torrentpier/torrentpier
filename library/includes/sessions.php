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

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

define('ONLY_NEW_POSTS', 1);
define('ONLY_NEW_TOPICS', 2);

class user_common
{
    /**
     *  Config
     */
    public $cfg = array(
        'req_login' => false,    // requires user to be logged in
        'req_session_admin' => false,    // requires active admin session (for moderation or admin actions)
    );

    /**
     *  PHP-JS exchangeable options (JSON'ized as {USER_OPTIONS_JS} in TPL)
     */
    public $opt_js = array(
        'only_new' => 0,     // show ony new posts or topics
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
    );

    /**
     *  Defaults options for guests
     */
    public $opt_js_guest = array(
        'h_av' => 1,     // hide avatar
        'h_rnk_i' => 1,     // hide rank images
        'h_smile' => 1,     // hide smilies
        'h_sig' => 1,     // hide signatures
    );

    /**
     *  Sessiondata
     */
    public $sessiondata = array(
        'uk' => null,
        'uid' => null,
        'sid' => '',
    );

    /**
     *  Old $userdata
     */
    public $data = array();

    /**
     *  Shortcuts
     */
    public $id = null;

    /**
     *  Constructor
     */
    public function user_common()
    {
        $this->get_sessiondata();
    }

    /**
     *  Start session (restore existent session or create new)
     *
     * @param array $cfg
     *
     * @return array|bool
     */
    public function session_start($cfg = array())
    {
        /** @var \TorrentPier\Di $di */
        $di = \TorrentPier\Di::getInstance();

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

                if ($di->config->get('torhelp_enabled')) {
                    $SQL['SELECT'][] = "th.topic_id_csv AS torhelp";
                    $SQL['LEFT JOIN'][] = BB_BT_TORHELP . " th ON(u.user_id = th.user_id)";
                }

                $userdata_cache_id = $session_id;
            } else {
                $SQL['WHERE'][] = "s.session_ip = '" . USER_IP . "'";
                $SQL['WHERE'][] = "s.session_user_id = " . GUEST_UID;

                $userdata_cache_id = USER_IP;
            }

            if (!$this->data = cache_get_userdata($userdata_cache_id)) {
                $this->data = DB()->fetch_row($SQL);

                if ($this->data && (TIMENOW - $this->data['session_time']) > $di->config->get('session_update_intrv')) {
                    $this->data['session_time'] = TIMENOW;
                    $update_sessions_table = true;
                }

                cache_set_userdata($this->data);
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
                $this->data = array();
            }
        }
        // If we reach here then no (valid) session exists. So we'll create a new one,
        // using the cookie user_id if available to pull basic user prefs.
        if (!$this->data) {
            $login = false;
            $user_id = ($di->config->get('allow_autologin') && $this->sessiondata['uk'] && $this->sessiondata['uid']) ? $this->sessiondata['uid'] : GUEST_UID;

            if ($userdata = get_userdata(intval($user_id), false, true)) {
                if ($userdata['user_id'] != GUEST_UID && $userdata['user_active']) {
                    if (verify_id($this->sessiondata['uk'], LOGIN_KEY_LENGTH) && $this->verify_autologin_id($userdata, true, false)) {
                        $login = ($userdata['autologin_id'] && $this->sessiondata['uk'] === $userdata['autologin_id']);
                    }
                }
            }
            if (!$userdata || ($userdata['user_id'] != GUEST_UID && !$login)) {
                $userdata = get_userdata(GUEST_UID, false, true);
            }

            $this->session_create($userdata, true);
        }

        define('IS_GUEST', (!$this->data['session_logged_in']));
        define('IS_ADMIN', (!IS_GUEST && $this->data['user_level'] == ADMIN));
        define('IS_MOD', (!IS_GUEST && $this->data['user_level'] == MOD));
        define('IS_CP_HOLDER', (!IS_GUEST && $this->data['user_level'] == CP_HOLDER));
        define('IS_GROUP_MEMBER', (!IS_GUEST && $this->data['user_level'] == GROUP_MEMBER));
        define('IS_USER', (!IS_GUEST && $this->data['user_level'] == USER));
        define('IS_SUPER_ADMIN', (IS_ADMIN && $di->config->get('super_admins.' . $this->data['user_id'])));
        define('IS_AM', (IS_ADMIN || IS_MOD));

        $this->set_shortcuts();

        // Redirect guests to login page
        if (IS_GUEST && $this->cfg['req_login']) {
            login_redirect();
        }

        $this->init_userprefs();

        return $this->data;
    }

    /**
     *  Create new session for the given user
     *
     * @param      $userdata
     * @param bool $auto_created
     *
     * @return array
     */
    public function session_create($userdata, $auto_created = false)
    {
        /** @var \TorrentPier\Di $di */
        $di = \TorrentPier\Di::getInstance();

        $this->data = $userdata;
        $session_id = $this->sessiondata['sid'];

        $login = (int)($this->data['user_id'] != GUEST_UID);
        $is_user = ($this->data['user_level'] != ADMIN);
        $user_id = (int)$this->data['user_id'];
        $mod_admin_session = ($this->data['user_level'] == ADMIN || $this->data['user_level'] == MOD);

        // Initial ban check against user_id or IP address
        if ($is_user) {
            preg_match('#(..)(..)(..)(..)#', USER_IP, $ip);

            $where_sql = "ban_ip IN('" . USER_IP . "', '$ip[1]$ip[2]$ip[3]ff', '$ip[1]$ip[2]ffff', '$ip[1]ffffff')";
            $where_sql .= ($login) ? " OR ban_userid = $user_id" : '';

            $sql = "SELECT ban_id FROM " . BB_BANLIST . " WHERE $where_sql LIMIT 1";

            if (DB()->fetch_row($sql)) {
                header('Location: https://torrentpier.me/banned/');
            }
        }

        // Create new session
        for ($i = 0, $max_try = 5; $i <= $max_try; $i++) {
            $session_id = make_rand_str(SID_LENGTH);

            $args = DB()->build_array('INSERT', array(
                'session_id' => (string)$session_id,
                'session_user_id' => (int)$user_id,
                'session_start' => (int)TIMENOW,
                'session_time' => (int)TIMENOW,
                'session_ip' => (string)USER_IP,
                'session_logged_in' => (int)$login,
                'session_admin' => (int)$mod_admin_session,
            ));
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
            } elseif ($session_time < (TIMENOW - $di->config->get('last_visit_update_intrv'))) {
                $last_visit = max($session_time, (TIMENOW - 86400 * $di->config->get('max_last_visit_days')));
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

                bb_setcookie(COOKIE_TOPIC, '');
                bb_setcookie(COOKIE_FORUM, '');

                $this->data['user_lastvisit'] = $last_visit;
            }
            if (!empty($_POST['autologin']) && $di->config->get('allow_autologin')) {
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

        cache_set_userdata($this->data);

        return $this->data;
    }

    /**
     *  Initialize sessiondata stored in cookies
     *
     * @param bool $update_lastvisit
     * @param bool $set_cookie
     */
    public function session_end($update_lastvisit = false, $set_cookie = true)
    {
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

                DB()->query("
					DELETE FROM " . BB_SESSIONS . "
					WHERE session_user_id = '{$this->data['user_id']}'
				");
            }
        }

        if ($set_cookie) {
            $this->set_session_cookies(GUEST_UID);
        }
    }

    /**
     *  Login
     *
     * @param      $args
     * @param bool $mod_admin_login
     *
     * @return array
     */
    public function login($args, $mod_admin_login = false)
    {
        $username = !empty($args['login_username']) ? clean_username($args['login_username']) : '';
        $password = !empty($args['login_password']) ? $args['login_password'] : '';

        if ($username && $password) {
            $username_sql = str_replace("\\'", "''", $username);
            $password_sql = md5(md5($password));

            $sql = "
				SELECT *
				FROM " . BB_USERS . "
				WHERE username = '$username_sql'
				  AND user_password = '$password_sql'
				  AND user_active = 1
				  AND user_id != " . GUEST_UID . "
				LIMIT 1
			";

            if ($userdata = DB()->fetch_row($sql)) {
                if (!$userdata['username'] || !$userdata['user_password'] || $userdata['user_id'] == GUEST_UID || md5(md5($password)) !== $userdata['user_password'] || !$userdata['user_active']) {
                    trigger_error('invalid userdata', E_USER_ERROR);
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
                    cache_update_userdata($this->data);

                    return $this->data;
                } elseif ($new_session_userdata = $this->session_create($userdata, false)) {
                    // Removing guest sessions from this IP
                    DB()->query("
						DELETE FROM " . BB_SESSIONS . "
						WHERE session_ip = '" . USER_IP . "'
							AND session_user_id = " . GUEST_UID . "
					");

                    return $new_session_userdata;
                } else {
                    trigger_error("Could not start session : login", E_USER_ERROR);
                }
            }
        }

        return array();
    }

    /**
     *  Initialize sessiondata stored in cookies
     */
    public function get_sessiondata()
    {
        $sd_resv = !empty($_COOKIE[COOKIE_DATA]) ? unserialize($_COOKIE[COOKIE_DATA]) : array();

        // autologin_id
        if (!empty($sd_resv['uk']) && verify_id($sd_resv['uk'], LOGIN_KEY_LENGTH)) {
            $this->sessiondata['uk'] = $sd_resv['uk'];
        }
        // user_id
        if (!empty($sd_resv['uid'])) {
            $this->sessiondata['uid'] = intval($sd_resv['uid']);
        }
        // sid
        if (!empty($sd_resv['sid']) && verify_id($sd_resv['sid'], SID_LENGTH)) {
            $this->sessiondata['sid'] = $sd_resv['sid'];
        }
    }

    /**
     *  Store sessiondata in cookies
     *
     * @param $user_id
     */
    public function set_session_cookies($user_id)
    {
        /** @var \TorrentPier\Di $di */
        $di = \TorrentPier\Di::getInstance();

        if ($user_id == GUEST_UID) {
            $delete_cookies = array(
                COOKIE_DATA,
                COOKIE_DBG,
                'torhelp',
                'explain',
                'sql_log',
                'sql_log_full',
            );

            foreach ($delete_cookies as $cookie) {
                if (isset($_COOKIE[$cookie])) {
                    bb_setcookie($cookie, '', COOKIE_EXPIRED);
                }
            }
        } else {
            $c_sdata_resv = !empty($_COOKIE[COOKIE_DATA]) ? $_COOKIE[COOKIE_DATA] : null;
            $c_sdata_curr = ($this->sessiondata) ? serialize($this->sessiondata) : '';

            if ($c_sdata_curr !== $c_sdata_resv) {
                bb_setcookie(COOKIE_DATA, $c_sdata_curr, COOKIE_PERSIST, true);
            }
            if ($di->config->get('dbg_users.' . $this->data['user_id']) && !isset($_COOKIE[COOKIE_DBG])) {
                bb_setcookie(COOKIE_DBG, 1, COOKIE_SESSION);
            }
        }
    }

    /**
     *  Verify autologin_id
     *
     * @param      $userdata
     * @param bool $expire_check
     * @param bool $create_new
     *
     * @return bool|string
     */
    public function verify_autologin_id($userdata, $expire_check = false, $create_new = true)
    {
        /** @var \TorrentPier\Di $di */
        $di = \TorrentPier\Di::getInstance();

        $autologin_id = $userdata['autologin_id'];

        if ($expire_check) {
            if ($create_new && !$autologin_id) {
                return $this->create_autologin_id($userdata);
            } elseif ($autologin_id && $userdata['user_session_time'] && $di->config->get('max_autologin_time')) {
                if (TIMENOW - $userdata['user_session_time'] > $di->config->get('max_autologin_time') * 86400) {
                    return $this->create_autologin_id($userdata, $create_new);
                }
            }
        }

        return verify_id($autologin_id, LOGIN_KEY_LENGTH);
    }

    /**
     *  Create autologin_id
     *
     * @param      $userdata
     * @param bool $create_new
     *
     * @return string
     */
    public function create_autologin_id($userdata, $create_new = true)
    {
        $autologin_id = ($create_new) ? make_rand_str(LOGIN_KEY_LENGTH) : '';

        DB()->query("
			UPDATE " . BB_USERS . " SET
				autologin_id = '$autologin_id'
			WHERE user_id = " . (int)$userdata['user_id'] . "
			LIMIT 1
		");

        return $autologin_id;
    }

    /**
     *  Set shortcuts
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
     *  Initialise user settings
     */
    public function init_userprefs()
    {
        global $theme, $lang, $DeltaTime;

        /** @var \TorrentPier\Di $di */
        $di = \TorrentPier\Di::getInstance();

        if (defined('LANG_DIR')) {
            return;
        }  // prevent multiple calling

        define('DEFAULT_LANG_DIR', LANG_ROOT_DIR . $di->config->get('default_lang') . '/');
        define('ENGLISH_LANG_DIR', LANG_ROOT_DIR . 'en/');

        if ($this->data['user_id'] != GUEST_UID) {
            if ($this->data['user_lang'] && $this->data['user_lang'] != $di->config->get('default_lang')) {
                define('LANG_DIR', LANG_ROOT_DIR . basename($this->data['user_lang']) . '/');
            }
        }

        $this->data['user_lang'] = $di->config->get('default_lang');
        $this->data['user_timezone'] = $di->config->get('board_timezone');

        if (!defined('LANG_DIR')) {
            define('LANG_DIR', DEFAULT_LANG_DIR);
        }

        require(LANG_DIR . 'main.php');
        setlocale(LC_ALL, $di->config->get('lang.' . $this->data['user_lang'] . '.locale'));

        $theme = setup_style();
        $DeltaTime = new Date_Delta();

        // Handle marking posts read
        if (!IS_GUEST && !empty($_COOKIE[COOKIE_MARK])) {
            $this->mark_read($_COOKIE[COOKIE_MARK]);
        }

        $this->load_opt_js();
    }

    /**
     *  Mark read
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
            db_update_userdata($this->data, array(
                'user_session_time' => $this->data['session_time'],
                'user_lastvisit' => $this->data['user_lastvisit'],
            ));

            // Delete cookies
            bb_setcookie(COOKIE_TOPIC, '');
            bb_setcookie(COOKIE_FORUM, '');
            bb_setcookie(COOKIE_MARK, '');
        }
    }

    /**
     *  Load misc options
     */
    public function load_opt_js()
    {
        if (IS_GUEST) {
            $this->opt_js = array_merge($this->opt_js, $this->opt_js_guest);
        } elseif (!empty($_COOKIE['opt_js'])) {
            $opt_js = \Zend\Json\Json::decode($_COOKIE['opt_js'], \Zend\Json\Json::TYPE_ARRAY);

            if (is_array($opt_js)) {
                $this->opt_js = array_merge($this->opt_js, $opt_js);
            }
        }
    }

    /**
     *  Get not auth forums
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

        if (!$forums = $datastore->get('cat_forums')) {
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

        $auth_field_match = array(
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
        );

        $not_auth_forums = array();
        $auth_field = $auth_field_match[$auth_type];
        $is_auth_ary = auth($auth_type, AUTH_LIST_ALL, $this->data);

        foreach ($is_auth_ary as $forum_id => $is_auth) {
            if (!$is_auth[$auth_field]) {
                $not_auth_forums[] = $forum_id;
            }
        }

        return join(',', $not_auth_forums);
    }

    /**
     *  Get excluded forums
     *
     * @param        $auth_type
     * @param string $return_as
     *
     * @return array|string
     */
    public function get_excluded_forums($auth_type, $return_as = 'csv')
    {
        $excluded = array();

        if ($not_auth = $this->get_not_auth_forums($auth_type)) {
            $excluded[] = $not_auth;
        }

        if (bf($this->opt, 'user_opt', 'user_porn_forums')) {
            global $datastore;

            if (!$forums = $datastore->get('cat_forums')) {
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

        switch ($return_as) {
            case   'csv':
                return join(',', $excluded);
            case 'array':
                return $excluded;
            case  'flip':
                return array_flip(explode(',', $excluded));
        }
    }
}

//
// userdata cache
//
/**
 * @return bool
 */
function ignore_cached_userdata()
{
    return (defined('IN_PM')) ? true : false;
}

/**
 * @param $id
 * @return bool|mixed|null
 */
function cache_get_userdata($id)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    /** @var \TorrentPier\Cache\Adapter $cache */
    $cache = $di->cache;

    if (ignore_cached_userdata()) {
        return false;
    }

    return $cache->get($id);
}

/**
 * @param $userdata
 * @param bool $force
 * @return bool
 */
function cache_set_userdata($userdata, $force = false)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    /** @var \TorrentPier\Cache\Adapter $cache */
    $cache = $di->cache;

    if (!$userdata || (ignore_cached_userdata() && !$force)) {
        return false;
    }

    $id = ($userdata['user_id'] == GUEST_UID) ? $userdata['session_ip'] : $userdata['session_id'];

    return $cache->set($id, $userdata, $di->config->get('session_update_intrv'));
}

/**
 * @param $userdata
 * @return bool
 */
function cache_rm_userdata($userdata)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    /** @var \TorrentPier\Cache\Adapter $cache */
    $cache = $di->cache;

    if (!$userdata) {
        return false;
    }

    $id = ($userdata['user_id'] == GUEST_UID) ? $userdata['session_ip'] : $userdata['session_id'];

    return $cache->delete($id);
}

// $user_id - array(id1,id2,..) or (string) id
/**
 * @param $user_id
 */
function cache_rm_user_sessions($user_id)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    /** @var \TorrentPier\Cache\Adapter $cache */
    $cache = $di->cache;

    $user_id = get_id_csv($user_id);

    $rowset = DB()->fetch_rowset("
		SELECT session_id FROM " . BB_SESSIONS . " WHERE session_user_id IN($user_id)
	");

    foreach ($rowset as $row) {
        $cache->delete($row['session_id']);
    }
}

/**
 * @param $userdata
 * @return bool
 */
function cache_update_userdata($userdata)
{
    return cache_set_userdata($userdata, true);
}

/**
 * @param $userdata
 * @param $sql_ary
 * @param bool $data_already_escaped
 * @return bool
 */
function db_update_userdata($userdata, $sql_ary, $data_already_escaped = true)
{
    if (!$userdata) {
        return false;
    }

    $sql_args = DB()->build_array('UPDATE', $sql_ary, $data_already_escaped);
    DB()->query("UPDATE " . BB_USERS . " SET $sql_args WHERE user_id = {$userdata['user_id']}");

    if (DB()->affected_rows()) {
        cache_rm_userdata($userdata);
    }

    return true;
}

// $user_id - array(id1,id2,..) or (string) id
/**
 * @param $user_id
 */
function delete_user_sessions($user_id)
{
    cache_rm_user_sessions($user_id);

    $user_id = get_id_csv($user_id);
    DB()->query("DELETE FROM " . BB_SESSIONS . " WHERE session_user_id IN($user_id)");
}
