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

/**
 * @param $id
 * @param $ext_id
 * @param $base_path
 * @param $first_div
 * @param $sec_div
 * @return string
 */
function get_path_from_id($id, $ext_id, $base_path, $first_div, $sec_div)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    $ext = $di->config->get('file_id_ext.' . $ext_id) ? $di->config->get('file_id_ext.' . $ext_id) : '';

    return ($base_path ? "$base_path/" : '') . floor($id / $first_div) . '/' . ($id % $sec_div) . '/' . $id . ($ext ? ".$ext" : '');
}

/**
 * @param $id
 * @param $ext_id
 * @param null $base_path
 * @param int $first_div
 * @param int $sec_div
 * @return string
 */
function get_avatar_path($id, $ext_id, $base_path = null, $first_div = 10000, $sec_div = 100)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    $base_path = isset($base_path) ? $base_path : $di->config->get('avatars.upload_path');

    return get_path_from_id($id, $ext_id, $base_path, $first_div, $sec_div);
}

/**
 * @param $id
 * @param string $ext_id
 * @param null $base_path
 * @param int $first_div
 * @param int $sec_div
 * @return string
 */
function get_attach_path($id, $ext_id = '', $base_path = null, $first_div = 10000, $sec_div = 100)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    $base_path = isset($base_path) ? $base_path : $di->config->get('attach.upload_path');

    return get_path_from_id($id, $ext_id, $base_path, $first_div, $sec_div);
}

/**
 * @param $user_id
 * @param $avatar_ext_id
 * @return bool
 */
function delete_avatar($user_id, $avatar_ext_id)
{
    $avatar_file = ($avatar_ext_id) ? get_avatar_path($user_id, $avatar_ext_id) : '';
    return ($avatar_file && file_exists($avatar_file)) ? unlink($avatar_file) : false;
}

/**
 * @param $topic_id
 * @param $attach_ext_id
 * @return bool
 */
function delete_attach($topic_id, $attach_ext_id)
{
    $attach_file = ($attach_ext_id) ? get_attach_path($topic_id, $attach_ext_id) : '';
    return ($attach_file && file_exists($attach_file)) ? unlink($attach_file) : false;
}

/**
 * @param $type
 * @return array|bool|mixed
 */
function get_tracks($type)
{
    $c_name = '';

    switch ($type) {
        case 'topic':
            $c_name = COOKIE_TOPIC;
            break;
        case 'forum':
            $c_name = COOKIE_FORUM;
            break;
        case 'pm':
            $c_name = COOKIE_PM;
            break;
        default:
            trigger_error(__FUNCTION__ . ": invalid type '$type'", E_USER_ERROR);
    }
    $tracks = !empty($_COOKIE[$c_name]) ? unserialize($_COOKIE[$c_name]) : false;
    return ($tracks) ? $tracks : [];
}

/**
 * @param $cookie_name
 * @param $tracking_ary
 * @param null $tracks
 * @param int $val
 */
function set_tracks($cookie_name, &$tracking_ary, $tracks = null, $val = TIMENOW)
{
    global $tracking_topics, $tracking_forums, $user;

    if (IS_GUEST) {
        return;
    }

    $prev_tracking_ary = $tracking_ary;

    if ($tracks) {
        if (!is_array($tracks)) {
            $tracks = array($tracks => $val);
        }
        foreach ($tracks as $key => $val) {
            $key = (int)$key;
            $val++;
            $curr_track_val = !empty($tracking_ary[$key]) ? $tracking_ary[$key] : 0;

            if ($val > max($curr_track_val, $user->data['user_lastvisit'])) {
                $tracking_ary[$key] = $val;
            } elseif ($curr_track_val < $user->data['user_lastvisit']) {
                unset($tracking_ary[$key]);
            }
        }
    }

    $overflow = count($tracking_topics) + count($tracking_forums) - COOKIE_MAX_TRACKS;

    if ($overflow > 0) {
        arsort($tracking_ary);
        for ($i = 0; $i < $overflow; $i++) {
            array_pop($tracking_ary);
        }
    }

    if (array_diff($tracking_ary, $prev_tracking_ary)) {
        bb_setcookie($cookie_name, serialize($tracking_ary));
    }
}

/**
 * @param int $topic_id
 * @param int $forum_id
 * @return mixed
 */
function get_last_read($topic_id = 0, $forum_id = 0)
{
    global $tracking_topics, $tracking_forums, $user;

    $t = isset($tracking_topics[$topic_id]) ? $tracking_topics[$topic_id] : 0;
    $f = isset($tracking_forums[$forum_id]) ? $tracking_forums[$forum_id] : 0;
    return max($t, $f, $user->data['user_lastvisit']);
}

/**
 * @param $ref
 * @param int $topic_id
 * @param int $forum_id
 * @return bool
 */
function is_unread($ref, $topic_id = 0, $forum_id = 0)
{
    return (!IS_GUEST && $ref > get_last_read($topic_id, $forum_id));
}

//
// Auth
//
define('AUTH_LIST_ALL', 0);

// forum's ACL types (bb_forums: auth_view, auth_read... values)
define('AUTH_REG', 1);
define('AUTH_ACL', 2);
define('AUTH_ADMIN', 5);

// forum_perm bitfields - backward compatible with auth($type)
define('AUTH_ALL', 0);
define('AUTH_VIEW', 1);
define('AUTH_READ', 2);
define('AUTH_MOD', 3);
define('AUTH_POST', 4);
define('AUTH_REPLY', 5);
define('AUTH_EDIT', 6);
define('AUTH_DELETE', 7);
define('AUTH_STICKY', 8);
define('AUTH_ANNOUNCE', 9);
define('AUTH_VOTE', 10);
define('AUTH_POLLCREATE', 11);
define('AUTH_ATTACH', 12);
define('AUTH_DOWNLOAD', 13);

define('BF_AUTH_MOD', bit2dec(AUTH_MOD));

// When defining user permissions, take into account:
define('UG_PERM_BOTH', 1);  // both user and group
define('UG_PERM_USER_ONLY', 2);  // only personal user permissions
define('UG_PERM_GROUP_ONLY', 3);  // only group permissions

$bf['forum_perm'] = array(
    'auth_view' => AUTH_VIEW,
    'auth_read' => AUTH_READ,
    'auth_mod' => AUTH_MOD,
    'auth_post' => AUTH_POST,
    'auth_reply' => AUTH_REPLY,
    'auth_edit' => AUTH_EDIT,
    'auth_delete' => AUTH_DELETE,
    'auth_sticky' => AUTH_STICKY,
    'auth_announce' => AUTH_ANNOUNCE,
    'auth_vote' => AUTH_VOTE,
    'auth_pollcreate' => AUTH_POLLCREATE,
    'auth_attachments' => AUTH_ATTACH,
    'auth_download' => AUTH_DOWNLOAD,
);

$bf['user_opt'] = array(
#   'dis_opt_name'       =>     ЗАПРЕТЫ используемые администраторами для пользователей
#   'user_opt_name'      =>     НАСТРОЙКИ используемые пользователями
    'user_viewemail' => 0,  // Показывать e-mail
    'dis_sig' => 1,  // Запрет на подпись
    'dis_avatar' => 2,  // Запрет на аватар
    'dis_pm' => 3,  // Запрет на отправку ЛС
    'user_viewonline' => 4,  // Скрывать пребывание пользователя
    'user_notify' => 5,  // Сообщать об ответах в отслеживаемых темах
    'user_notify_pm' => 6,  // Сообщать о новых ЛС
    'dis_passkey' => 7,  // Запрет на добавление passkey, он же запрет на скачивание торрентов
    'user_porn_forums' => 8,  // Скрывать контент 18+
    'user_callseed' => 9,  // Позвать скачавших
    'user_empty' => 10, // TODO: не используется
    'dis_topic' => 11, // Запрет на создание новых тем
    'dis_post' => 12, // Запрет на отправку сообщений
    'dis_post_edit' => 13, // Запрет на редактирование сообщений
    'user_dls' => 14, // Скрывать список текущих закачек в профиле
    'user_retracker' => 15, // Добавлять ретрекер к скачиваемым торрентам
);

/**
 * @param $bit_num
 * @return int
 */
function bit2dec($bit_num)
{
    if (is_array($bit_num)) {
        $dec = 0;
        foreach ($bit_num as $bit) {
            $dec |= (1 << $bit);
        }
        return $dec;
    }
    return (1 << $bit_num);
}

/**
 * @param $bf_array_name
 * @param $key
 * @return int
 */
function bf_bit2dec($bf_array_name, $key)
{
    global $bf;
    if (!isset($bf[$bf_array_name][$key])) {
        trigger_error(__FUNCTION__ . ": bitfield '$key' not found", E_USER_ERROR);
    }
    return (1 << $bf[$bf_array_name][$key]);
}

/**
 * @param $int
 * @param $bf_array_name
 * @param $key
 * @return int
 */
function bf($int, $bf_array_name, $key)
{
    return (bf_bit2dec($bf_array_name, $key) & (int)$int);
}

/**
 * @param $int
 * @param $bit_num
 * @param $on
 * @return int
 */
function setbit(&$int, $bit_num, $on)
{
    return ($on) ? $int |= (1 << $bit_num) : $int &= ~(1 << $bit_num);
}

/*
    $type's accepted (pre-pend with AUTH_):
    VIEW, READ, POST, REPLY, EDIT, DELETE, STICKY, ANNOUNCE, VOTE, POLLCREATE

    Possible options ($type/forum_id combinations):

    * If you include a type and forum_id then a specific lookup will be done and
    the single result returned

    * If you set type to AUTH_ALL and specify a forum_id an array of all auth types
    will be returned

    * If you provide a forum_id a specific lookup on that forum will be done

    * If you set forum_id to AUTH_LIST_ALL and specify a type an array listing the
    results for all forums will be returned

    * If you set forum_id to AUTH_LIST_ALL and type to AUTH_ALL a multidimensional
    array containing the auth permissions for all types and all forums for that
    user is returned

    All results are returned as associative arrays, even when a single auth type is
    specified.

    If available you can send an array (either one or two dimensional) containing the
    forum auth levels, this will prevent the auth function having to do its own
    lookup
*/
/**
 * @param $type
 * @param $forum_id
 * @param $ug_data
 * @param array $f_access
 * @param int $group_perm
 * @return array|mixed
 */
function auth($type, $forum_id, $ug_data, $f_access = [], $group_perm = UG_PERM_BOTH)
{
    global $lang, $bf, $datastore;

    $is_guest = true;
    $is_admin = false;
    $auth = $auth_fields = $u_access = [];
    $add_auth_type_desc = ($forum_id != AUTH_LIST_ALL);

    //
    // Get $auth_fields
    //
    if ($type == AUTH_ALL) {
        $auth_fields = array_keys($bf['forum_perm']);
    } elseif ($auth_type = array_search($type, $bf['forum_perm'])) {
        $auth_fields = array($auth_type);
    }

    if (empty($auth_fields)) {
        trigger_error(__FUNCTION__ . '(): empty $auth_fields', E_USER_ERROR);
    }

    //
    // Get $f_access
    //
    // If f_access has been passed, or auth is needed to return an array of forums
    // then we need to pull the auth information on the given forum (or all forums)
    if (empty($f_access)) {
        if (!$forums = $datastore->get('cat_forums')) {
            $datastore->update('cat_forums');
            $forums = $datastore->get('cat_forums');
        }

        if ($forum_id == AUTH_LIST_ALL) {
            $f_access = $forums['f'];
        } elseif (isset($forums['f'][$forum_id])) {
            $f_access[$forum_id] = $forums['f'][$forum_id];
        }
    } elseif (isset($f_access['forum_id'])) {
        // Change passed $f_access format for later using in foreach()
        $f_access = array($f_access['forum_id'] => $f_access);
    }

    if (empty($f_access)) {
        trigger_error(__FUNCTION__ . '(): empty $f_access', E_USER_ERROR);
    }

    //
    // Get user or group permissions
    //
    $forum_match_sql = ($forum_id != AUTH_LIST_ALL) ? "AND aa.forum_id = " . (int)$forum_id : '';

    // GROUP mode
    if (!empty($ug_data['group_id'])) {
        $is_guest = false;
        $is_admin = false;

        $sql = "SELECT aa.forum_id, aa.forum_perm
			FROM " . BB_AUTH_ACCESS . " aa
			WHERE aa.group_id = " . (int)$ug_data['group_id'] . "
				$forum_match_sql";

        foreach (DB()->fetch_rowset($sql) as $row) {
            $u_access[$row['forum_id']] = $row['forum_perm'];
        }
    } // USER mode
    elseif (!empty($ug_data['user_id'])) {
        $is_guest = empty($ug_data['session_logged_in']);
        $is_admin = (!$is_guest && $ug_data['user_level'] == ADMIN);

        if ($group_perm != UG_PERM_BOTH) {
            $group_single_user = ($group_perm == UG_PERM_USER_ONLY) ? 1 : 0;

            $sql = "
				SELECT
					aa.forum_id, BIT_OR(aa.forum_perm) AS forum_perm
				FROM
					" . BB_USER_GROUP . " ug,
					" . BB_GROUPS . " g,
					" . BB_AUTH_ACCESS . " aa
				WHERE
					    ug.user_id = " . (int)$ug_data['user_id'] . "
					AND ug.user_pending = 0
					AND g.group_id = ug.group_id
					AND g.group_single_user = $group_single_user
					AND aa.group_id = g.group_id
						$forum_match_sql
					GROUP BY aa.forum_id
			";

            foreach (DB()->fetch_rowset($sql) as $row) {
                $u_access[$row['forum_id']] = $row['forum_perm'];
            }
        } else {
            if (!$is_guest && !$is_admin) {
                $sql = "SELECT SQL_CACHE aa.forum_id, aa.forum_perm
					FROM " . BB_AUTH_ACCESS_SNAP . " aa
					WHERE aa.user_id = " . (int)$ug_data['user_id'] . "
						$forum_match_sql";

                foreach (DB()->fetch_rowset($sql) as $row) {
                    $u_access[$row['forum_id']] = $row['forum_perm'];
                }
            }
        }
    }

    // If the user is logged on and the forum type is either ALL or REG then the user has access
    //
    // If the type if ACL, MOD or ADMIN then we need to see if the user has specific permissions
    // to do whatever it is they want to do ... to do this we pull relevant information for the
    // user (and any groups they belong to)
    //
    // Now we compare the users access level against the forums. We assume here that a moderator
    // and admin automatically have access to an ACL forum, similarly we assume admins meet an
    // auth requirement of MOD
    //
    foreach ($f_access as $f_id => $f_data) {
        $auth[$f_id]['auth_mod'] = auth_check('forum_perm', 'auth_mod', $u_access, $f_id, $is_admin);

        foreach ($auth_fields as $auth_type) {
            if (!isset($f_data[$auth_type])) {
                continue;
            }
            switch ($f_data[$auth_type]) {
                case AUTH_ALL:
                    $auth[$f_id][$auth_type] = true;
                    break;

                case AUTH_REG:
                    $auth[$f_id][$auth_type] = !$is_guest;
                    break;

                case AUTH_ACL:
                    $auth[$f_id][$auth_type] = (auth_check('forum_perm', $auth_type, $u_access, $f_id, $is_admin) || $auth[$f_id]['auth_mod']);
                    break;

                case AUTH_MOD:
                    $auth[$f_id][$auth_type] = $auth[$f_id]['auth_mod'];
                    break;

                case AUTH_ADMIN:
                    $auth[$f_id][$auth_type] = $is_admin;
                    break;

                default:
                    $auth[$f_id][$auth_type] = false;
            }
            if ($add_auth_type_desc) {
                $auth[$f_id][$auth_type . '_type'] =& $lang['AUTH_TYPES'][$f_data[$auth_type]];
            }
        }
    }

    return ($forum_id == AUTH_LIST_ALL) ? $auth : $auth[$forum_id];
}

/**
 * @param $bf_ary
 * @param $bf_key
 * @param $perm_ary
 * @param $perm_key
 * @param bool $is_admin
 * @return bool|int
 */
function auth_check($bf_ary, $bf_key, $perm_ary, $perm_key, $is_admin = false)
{
    if ($is_admin) {
        return true;
    }
    if (!isset($perm_ary[$perm_key])) {
        return false;
    }

    return bf($perm_ary[$perm_key], $bf_ary, $bf_key);
}

class Date_Delta
{
    public $auto_granularity = array(
        60 => 'seconds',   // set granularity to "seconds" if delta less then 1 minute
        10800 => 'minutes',   // 3 hours
        259200 => 'hours',     // 3 days
        31363200 => 'mday',      // 12 months
        311040000 => 'mon',       // 10 years
    );
    public $intervals = [];
    public $format = '';

    /**
     * Date_Delta constructor.
     */
    public function Date_Delta()
    {
        global $lang;

        $this->intervals = $lang['DELTA_TIME']['INTERVALS'];
        $this->format = $lang['DELTA_TIME']['FORMAT'];
    }

    /**
     * Makes the spellable phrase.
     *
     * @param $first
     * @param $last
     * @param string $from
     * @return bool|string
     */
    public function spellDelta($first, $last, $from = 'auto')
    {
        if ($last < $first) {
            $old_first = $first;
            $first = $last;
            $last = $old_first;
        }

        if ($from == 'auto') {
            $from = 'year';
            $diff = $last - $first;
            foreach ($this->auto_granularity as $seconds_count => $granule) {
                if ($diff < $seconds_count) {
                    $from = $granule;
                    break;
                }
            }
        }

        // Solve data delta.
        $delta = $this->getDelta($first, $last);
        if (empty($delta)) {
            return false;
        }

        // Make spellable phrase.
        $parts = [];
        $intervals = $GLOBALS['lang']['DELTA_TIME']['INTERVALS'];

        foreach (array_reverse($delta) as $k => $n) {
            if (!$n) {
                if ($k == $from) {
                    if (!$parts) {
                        $parts[] = declension($n, $this->intervals[$k], $this->format);
                    }
                    break;
                }
                continue;
            }
            $parts[] = declension($n, $this->intervals[$k], $this->format);
            if ($k == $from) {
                break;
            }
        }
        return join(' ', $parts);
    }

    /**
     * Returns the associative array with date deltas.
     *
     * @param $first
     * @param $last
     * @return array
     */
    public function getDelta($first, $last)
    {
        if ($last < $first) {
            return [];
        }

        // Solve H:M:S part.
        $hms = ($last - $first) % (3600 * 24);
        $delta['seconds'] = $hms % 60;
        $delta['minutes'] = floor($hms / 60) % 60;
        $delta['hours'] = floor($hms / 3600) % 60;

        // Now work only with date, delta time = 0.
        $last -= $hms;
        $f = getdate($first);
        $l = getdate($last); // the same daytime as $first!

        $dYear = $dMon = $dDay = 0;

        // Delta day. Is negative, month overlapping.
        $dDay += $l['mday'] - $f['mday'];
        if ($dDay < 0) {
            $monlen = $this->monthLength(date('Y', $first), date('m', $first));
            $dDay += $monlen;
            $dMon--;
        }
        $delta['mday'] = $dDay;

        // Delta month. If negative, year overlapping.
        $dMon += $l['mon'] - $f['mon'];
        if ($dMon < 0) {
            $dMon += 12;
            $dYear--;
        }
        $delta['mon'] = $dMon;

        // Delta year.
        $dYear += $l['year'] - $f['year'];
        $delta['year'] = $dYear;

        return $delta;
    }

    /**
     * Returns the length (in days) of the specified month.
     *
     * @param $year
     * @param $mon
     * @return int
     */
    public function monthLength($year, $mon)
    {
        $l = 28;
        while (checkdate($mon, $l + 1, $year)) {
            $l++;
        }
        return $l;
    }
}

/**
 * @param $timestamp_1
 * @param int $timestamp_2
 * @param string $granularity
 * @return mixed
 */
function delta_time($timestamp_1, $timestamp_2 = TIMENOW, $granularity = 'auto')
{
    return $GLOBALS['DeltaTime']->spellDelta($timestamp_1, $timestamp_2, $granularity);
}

/**
 * @param $select
 * @param null $selected
 * @param string $return_as
 * @param string $first_opt
 * @return array|string
 */
function get_select($select, $selected = null, $return_as = 'html', $first_opt = '&raquo;&raquo; Выбрать ')
{
    $select_name = '';
    $select_ary = [];

    switch ($select) {
        case 'groups':
            $sql = "SELECT group_id, group_name FROM " . BB_GROUPS . " WHERE group_single_user = 0 ORDER BY group_name";
            foreach (DB()->fetch_rowset($sql) as $row) {
                $select_ary[$row['group_name']] = $row['group_id'];
            }
            $select_name = 'g';
            break;

        case 'forum_tpl':
            $sql = "SELECT tpl_id, tpl_name FROM " . BB_TOPIC_TPL . " ORDER BY tpl_name";
            $select_ary[$first_opt] = 0;
            foreach (DB()->fetch_rowset($sql) as $row) {
                $select_ary[$row['tpl_name']] = $row['tpl_id'];
            }
            $select_name = 'forum_tpl_select';
            break;
    }

    return ($return_as == 'html') ? build_select($select_name, $select_ary, $selected) : $select_ary;
}

class html_common
{
    public $options = '';
    public $attr = [];
    public $cur_attr = null;
    public $max_length = HTML_SELECT_MAX_LENGTH;
    public $selected = [];

    /**
     * @param $name
     * @param $params
     * @param null $selected
     * @param int $max_length
     * @param null $multiple_size
     * @param string $js
     * @return string
     */
    public function build_select($name, $params, $selected = null, $max_length = HTML_SELECT_MAX_LENGTH, $multiple_size = null, $js = '')
    {
        if (empty($params)) {
            return '';
        }

        $this->options = '';
        $this->selected = array_flip((array)$selected);
        $this->max_length = $max_length;

        $this->attr = [];
        $this->cur_attr =& $this->attr;

        if (isset($params['__attributes'])) {
            $this->attr = $params['__attributes'];
            unset($params['__attributes']);
        }

        $this->_build_select_rec($params);

        $select_params = ($js) ? " $js" : '';
        $select_params .= ($multiple_size) ? ' multiple="multiple" size="' . $multiple_size . '"' : '';
        $select_params .= ' name="' . htmlCHR($name) . '"';
        $select_params .= ' id="' . htmlCHR($name) . '"';

        return "\n<select $select_params>\n" . $this->options . "</select>\n";
    }

    /**
     * @param $params
     */
    public function _build_select_rec($params)
    {
        foreach ($params as $opt_name => $opt_val) {
            $opt_name = rtrim($opt_name);

            if (is_array($opt_val)) {
                $this->cur_attr =& $this->cur_attr[$opt_name];

                $label = htmlCHR(str_short($opt_name, $this->max_length));

                $this->options .= "\t<optgroup label=\"&nbsp;" . $label . "\">\n";
                $this->_build_select_rec($opt_val);
                $this->options .= "\t</optgroup>\n";

                $this->cur_attr =& $this->attr;
            } else {
                $text = htmlCHR(str_short($opt_name, $this->max_length));
                $value = ' value="' . htmlCHR($opt_val) . '"';

                $class = isset($this->cur_attr[$opt_name]['class']) ? ' class="' . $this->cur_attr[$opt_name]['class'] . '"' : '';
                $style = isset($this->cur_attr[$opt_name]['style']) ? ' style="' . $this->cur_attr[$opt_name]['style'] . '"' : '';

                $selected = isset($this->selected[$opt_val]) ? HTML_SELECTED : '';
                $disabled = isset($this->cur_attr[$opt_name]['disabled']) ? HTML_DISABLED : '';

                $this->options .= "\t\t<option" . $class . $style . $selected . $disabled . $value . '>&nbsp;' . $text . "&nbsp;</option>\n";
            }
        }
    }

    /**
     * @param $array
     * @param string $ul
     * @param string $li
     * @return string
     */
    public function array2html($array, $ul = 'ul', $li = 'li')
    {
        $this->out = '';
        $this->_array2html_rec($array, $ul, $li);
        return "<$ul class=\"tree-root\">{$this->out}</$ul>";
    }

    /**
     * @param $array
     * @param $ul
     * @param $li
     */
    public function _array2html_rec($array, $ul, $li)
    {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $this->out .= "<$li><span class=\"b\">$k</span><$ul>";
                $this->_array2html_rec($v, $ul, $li);
                $this->out .= "</$ul></$li>";
            } else {
                $this->out .= "<$li><span>$v</span></$li>";
            }
        }
    }

    // all arguments should be already htmlspecialchar()d (if needed)
    /**
     * @param $name
     * @param $title
     * @param bool $checked
     * @param bool $disabled
     * @param null $class
     * @param null $id
     * @param int $value
     * @return string
     */
    public function build_checkbox($name, $title, $checked = false, $disabled = false, $class = null, $id = null, $value = 1)
    {
        $name = ' name="' . $name . '" ';
        $value = ' value="' . $value . '" ';
        $title = ($class) ? '<span class="' . $class . '">' . $title . '</span>' : $title;
        $id = ($id) ? " id=\"$id\" " : '';
        $checked = ($checked) ? HTML_CHECKED : '';
        $disabled = ($disabled) ? HTML_DISABLED : '';

        return '<label><input type="checkbox" ' . $id . $name . $value . $checked . $disabled . ' />&nbsp;' . $title . '&nbsp;</label>';
    }
}

/**
 * @param $name
 * @param $params
 * @param null $selected
 * @param int $max_length
 * @param null $multiple_size
 * @param string $js
 * @return string
 */
function build_select($name, $params, $selected = null, $max_length = HTML_SELECT_MAX_LENGTH, $multiple_size = null, $js = '')
{
    global $html;
    return $html->build_select($name, $params, $selected, $max_length, $multiple_size, $js);
}

/**
 * @param $name
 * @param $title
 * @param bool $checked
 * @param bool $disabled
 * @param null $class
 * @param null $id
 * @param int $value
 * @return string
 */
function build_checkbox($name, $title, $checked = false, $disabled = false, $class = null, $id = null, $value = 1)
{
    global $html;
    return $html->build_checkbox($name, $title, $checked, $disabled, $class, $id, $value);
}

/**
 * @param $str
 * @param bool $double
 * @param bool $single
 * @return mixed
 */
function replace_quote($str, $double = true, $single = true)
{
    if ($double) {
        $str = str_replace('"', '&quot;', $str);
    }
    if ($single) {
        $str = str_replace("'", '&#039;', $str);
    }
    return $str;
}

/**
 * Build simple hidden fields from array
 *
 * @param $fields_ary
 *
 * @return string
 */
function build_hidden_fields($fields_ary)
{
    $out = "\n";

    foreach ($fields_ary as $name => $val) {
        if (is_array($val)) {
            foreach ($val as $ary_key => $ary_val) {
                $out .= '<input type="hidden" name="' . $name . '[' . $ary_key . ']" value="' . $ary_val . "\" />\n";
            }
        } else {
            $out .= '<input type="hidden" name="' . $name . '" value="' . $val . "\" />\n";
        }
    }

    return $out;
}

/**
 * Choost russian word declension based on numeric [from dklab.ru]
 * Example for $expressions: array("ответ", "ответа", "ответов")
 *
 * @param        $int
 * @param        $expressions
 * @param string $format
 *
 * @return string
 */
function declension($int, $expressions, $format = '%1$s %2$s')
{
    if (!is_array($expressions)) {
        $expressions = $GLOBALS['lang']['DECLENSION'][strtoupper($expressions)];
    }

    if (count($expressions) < 3) {
        $expressions[2] = $expressions[1];
    }
    $count = intval($int) % 100;

    if ($count >= 5 && $count <= 20) {
        $result = $expressions['2'];
    } else {
        $count = $count % 10;
        if ($count == 1) {
            $result = $expressions['0'];
        } elseif ($count >= 2 && $count <= 4) {
            $result = $expressions['1'];
        } else {
            $result = $expressions['2'];
        }
    }

    return ($format) ? sprintf($format, $int, $result) : $result;
}

// http://forum.dklab.ru/php/advises/UrlreplaceargChangesValueOfParameterInUrl.html
/**
 * @param $url
 * @param $arg
 * @param $value
 * @param string $amp
 * @return string
 */
function url_arg($url, $arg, $value, $amp = '&amp;')
{
    $arg = preg_quote($arg, '/');

    // разделяем URL и ANCHOR
    $anchor = '';
    if (preg_match('/(.*)(#.*)/s', $url, $m)) {
        $url = $m[1];
        $anchor = $m[2];
    }
    // заменяем параметр, если он существует
    if (preg_match("/((\?|&|&amp;)$arg=)[^&]*/s", $url, $m)) {
        $cur = $m[0];
        $new = is_null($value) ? '' : $m[1] . urlencode($value);
        $url = str_replace($cur, $new, $url);
    } // добавляем параметр
    elseif (!is_null($value)) {
        $div = (strpos($url, '?') !== false) ? $amp : '?';
        $url = $url . $div . $arg . '=' . urlencode($value);
    }
    return $url . $anchor;
}

/**
 * Adds commas between every group of thousands
 *
 * @param $number
 *
 * @return string
 */
function commify($number)
{
    return number_format($number);
}

/**
 * Returns a size formatted in a more human-friendly format, rounded to the nearest GB, MB, KB..
 *
 * @param        $size
 * @param null $rounder
 * @param null $min
 * @param string $space
 *
 * @return string
 */
function humn_size($size, $rounder = null, $min = null, $space = '&nbsp;')
{
    static $sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    static $rounders = [0, 0, 0, 2, 3, 3, 3, 3, 3];

    $size = (float)$size;
    $ext = $sizes[0];
    $rnd = $rounders[0];

    if ($min == 'KB' && $size < 1024) {
        $size = $size / 1024;
        $ext = 'KB';
        $rounder = 1;
    } else {
        for ($i = 1, $cnt = count($sizes); ($i < $cnt && $size >= 1024); $i++) {
            $size = $size / 1024;
            $ext = $sizes[$i];
            $rnd = $rounders[$i];
        }
    }
    if (!$rounder) {
        $rounder = $rnd;
    }

    return round($size, $rounder) . $space . $ext;
}

/**
 * @param $ip
 * @param string $port
 * @return bool|string
 */
function bt_show_ip($ip, $port = '')
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    if (IS_AM) {
        $ip = decode_ip($ip);
        $ip .= ($port) ? ":$port" : '';
        return $ip;
    } else {
        return ($di->config->get('bt_show_ip_only_moder')) ? false : decode_ip_xx($ip);
    }
}

/**
 * @param $port
 * @return bool
 */
function bt_show_port($port)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    if (IS_AM) {
        return $port;
    } else {
        return ($di->config->get('bt_show_port_only_moder')) ? false : $port;
    }
}

/**
 * @param $ip
 * @return string
 */
function decode_ip_xx($ip)
{
    $h = explode('.', chunk_split($ip, 2, '.'));
    return hexdec($h[0]) . '.' . hexdec($h[1]) . '.' . hexdec($h[2]) . '.xx';
}

/**
 * @param $key
 * @param $val
 * @param int $default
 * @param int $on
 * @param int $off
 */
function checkbox_get_val(&$key, &$val, $default = 1, $on = 1, $off = 0)
{
    global $previous_settings, $search_id;

    if (isset($_REQUEST[$key]) && is_string($_REQUEST[$key])) {
        $val = (int)$_REQUEST[$key];
    } elseif (!isset($_REQUEST[$key]) && isset($_REQUEST['prev_' . $key])) {
        $val = $off;
    } elseif (isset($previous_settings[$key]) && (!IS_GUEST || !empty($search_id))) {
        $val = ($previous_settings[$key]) ? $on : $off;
    } else {
        $val = $default;
    }
}

/**
 * @param $key
 * @param $val
 * @param $options_ary
 * @param $default
 * @param bool $num
 */
function select_get_val($key, &$val, $options_ary, $default, $num = true)
{
    global $previous_settings;

    if (isset($_REQUEST[$key]) && is_string($_REQUEST[$key])) {
        if (isset($options_ary[$_REQUEST[$key]])) {
            $val = ($num) ? intval($_REQUEST[$key]) : $_REQUEST[$key];
        }
    } elseif (isset($previous_settings[$key])) {
        $val = $previous_settings[$key];
    } else {
        $val = $default;
    }
}

/**
 * set_var
 *
 * Set variable, used by {@link request_var the request_var function}
 *
 * @access private
 *
 * @param      $result
 * @param      $var
 * @param      $type
 * @param bool $multibyte
 * @param bool $strip
 */
function set_var(&$result, $var, $type, $multibyte = false, $strip = true)
{
    settype($var, $type);
    $result = $var;

    if ($type == 'string') {
        $result = trim(htmlspecialchars(str_replace(["\r\n", "\r"], ["\n", "\n"], $result)));

        if (!empty($result)) {
            // Make sure multibyte characters are wellformed
            if ($multibyte) {
                if (!preg_match('/^./u', $result)) {
                    $result = '';
                }
            }
        }

        $result = ($strip) ? stripslashes($result) : $result;
    }
}

/**
 * request_var
 *
 * Used to get passed variable
 *
 * @param      $var_name
 * @param      $default
 * @param bool $multibyte
 * @param bool $cookie
 *
 * @return array
 */
function request_var($var_name, $default, $multibyte = false, $cookie = false)
{
    $key_type = $sub_key_type = $sub_type = '';

    if (!$cookie && isset($_COOKIE[$var_name])) {
        if (!isset($_GET[$var_name]) && !isset($_POST[$var_name])) {
            return (is_array($default)) ? [] : $default;
        }
        $_REQUEST[$var_name] = isset($_POST[$var_name]) ? $_POST[$var_name] : $_GET[$var_name];
    }

    if (!isset($_REQUEST[$var_name]) || (is_array($_REQUEST[$var_name]) && !is_array($default)) || (is_array($default) && !is_array($_REQUEST[$var_name]))) {
        return (is_array($default)) ? [] : $default;
    }

    $var = $_REQUEST[$var_name];
    if (!is_array($default)) {
        $type = gettype($default);
    } else {
        list($key_type, $type) = each($default);
        $type = gettype($type);
        $key_type = gettype($key_type);
        if ($type == 'array') {
            reset($default);
            $default = current($default);
            list($sub_key_type, $sub_type) = each($default);
            $sub_type = gettype($sub_type);
            $sub_type = ($sub_type == 'array') ? 'NULL' : $sub_type;
            $sub_key_type = gettype($sub_key_type);
        }
    }

    if (is_array($var)) {
        $_var = $var;
        $var = [];

        foreach ($_var as $k => $v) {
            set_var($k, $k, $key_type);
            if ($type == 'array' && is_array($v)) {
                foreach ($v as $_k => $_v) {
                    if (is_array($_v)) {
                        $_v = null;
                    }
                    set_var($_k, $_k, $sub_key_type);
                    set_var($var[$k][$_k], $_v, $sub_type, $multibyte);
                }
            } else {
                if ($type == 'array' || is_array($v)) {
                    $v = null;
                }
                set_var($var[$k], $v, $type, $multibyte);
            }
        }
    } else {
        set_var($var, $var, $type, $multibyte);
    }

    return $var;
}

/**
 * @param $user_id
 * @return array|bool
 */
function get_username($user_id)
{
    if (empty($user_id)) {
        return is_array($user_id) ? [] : false;
    }
    if (is_array($user_id)) {
        $usernames = [];
        foreach (DB()->fetch_rowset("SELECT user_id, username FROM " . BB_USERS . " WHERE user_id IN(" . get_id_csv($user_id) . ")") as $row) {
            $usernames[$row['user_id']] = $row['username'];
        }
        return $usernames;
    } else {
        $row = DB()->fetch_row("SELECT username FROM " . BB_USERS . " WHERE user_id = $user_id LIMIT 1");
        return $row['username'];
    }
}

/**
 * @param $username
 * @return bool
 */
function get_user_id($username)
{
    if (empty($username)) {
        return false;
    }
    $row = DB()->fetch_row("SELECT user_id FROM " . BB_USERS . " WHERE username = '" . DB()->escape($username) . "' LIMIT 1");
    return $row['user_id'];
}

/**
 * @param $text
 * @param $max_length
 * @param string $space
 * @return mixed|string
 */
function str_short($text, $max_length, $space = ' ')
{
    if ($max_length && mb_strlen($text, 'UTF-8') > $max_length) {
        $text = mb_substr($text, 0, $max_length, 'UTF-8');

        if ($last_space_pos = $max_length - intval(strpos(strrev($text), $space))) {
            if ($last_space_pos > round($max_length * 3 / 4)) {
                $last_space_pos--;
                $text = mb_substr($text, 0, $last_space_pos, 'UTF-8');
            }
        }
        $text .= '...';
        $text = preg_replace('!&#?(\w+)?;?(\w{1,5})?\.\.\.$!', '...', $text);
    }

    return $text;
}

/**
 * @param $text
 * @param int $max_word_length
 * @return mixed
 */
function wbr($text, $max_word_length = HTML_WBR_LENGTH)
{
    return preg_replace("/([\w\->;:.,~!?(){}@#$%^*\/\\\\]{" . $max_word_length . "})/ui", '$1<wbr>', $text);
}

/**
 * @param $user_id
 * @return mixed|null
 */
function get_bt_userdata($user_id)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    /** @var \TorrentPier\Cache\Adapter $cache */
    $cache = $di->cache;

    if (!$cache->has('btu_' . $user_id)) {
        $btu = DB()->fetch_row("
			SELECT bt.*, SUM(tr.speed_up) AS speed_up, SUM(tr.speed_down) AS speed_down
			FROM      " . BB_BT_USERS . " bt
			LEFT JOIN " . BB_BT_TRACKER . " tr ON (bt.user_id = tr.user_id)
			WHERE bt.user_id = " . (int)$user_id . "
			GROUP BY bt.user_id
			LIMIT 1
		");
        $cache->set('btu_' . $user_id, $btu, 300);
    }

    $btu = $cache->get('btu_' . $user_id);

    return $btu;
}

/**
 * @param $btu
 * @return float|null
 */
function get_bt_ratio($btu)
{
    return
        (!empty($btu['u_down_total']) && $btu['u_down_total'] > MIN_DL_FOR_RATIO)
            ? round((($btu['u_up_total'] + $btu['u_up_release'] + $btu['u_up_bonus']) / $btu['u_down_total']), 2)
            : null;
}

/**
 * @param $user_id
 */
function show_bt_userdata($user_id)
{
    global $lang, $template;

    $btu = get_bt_userdata($user_id);

    $template->assign_vars(array(
        'SHOW_BT_USERDATA' => true,
        'UP_TOTAL' => humn_size($btu['u_up_total']),
        'UP_BONUS' => humn_size($btu['u_up_bonus']),
        'RELEASED' => humn_size($btu['u_up_release']),
        'DOWN_TOTAL' => humn_size($btu['u_down_total']),
        'DOWN_TOTAL_BYTES' => $btu['u_down_total'],
        'USER_RATIO' => get_bt_ratio($btu),
        'MIN_DL_FOR_RATIO' => humn_size(MIN_DL_FOR_RATIO),
        'MIN_DL_BYTES' => MIN_DL_FOR_RATIO,
        'AUTH_KEY' => ($btu['auth_key']) ? $btu['auth_key'] : $lang['NONE'],

        'TD_DL' => humn_size($btu['down_today']),
        'TD_UL' => humn_size($btu['up_today']),
        'TD_REL' => humn_size($btu['up_release_today']),
        'TD_BONUS' => humn_size($btu['up_bonus_today']),
        'TD_POINTS' => ($btu['auth_key']) ? $btu['points_today'] : '0.00',

        'YS_DL' => humn_size($btu['down_yesterday']),
        'YS_UL' => humn_size($btu['up_yesterday']),
        'YS_REL' => humn_size($btu['up_release_yesterday']),
        'YS_BONUS' => humn_size($btu['up_bonus_yesterday']),
        'YS_POINTS' => ($btu['auth_key']) ? $btu['points_yesterday'] : '0.00',

        'SPEED_UP' => humn_size($btu['speed_up'], 0, 'KB') . '/s',
        'SPEED_DOWN' => humn_size($btu['speed_down'], 0, 'KB') . '/s',
    ));
}

/**
 * @param $table
 * @param bool $from_db
 * @param bool $update_cache
 * @return array|mixed|null
 */
function bb_get_config($table, $from_db = false, $update_cache = true)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    /** @var \TorrentPier\Cache\Adapter $cache */
    $cache = $di->cache;

    $cfg = [];
    if (!$cache->has('config_' . $table)) {
        if ($from_db) {
            foreach (DB()->fetch_rowset("SELECT * FROM $table") as $row) {
                $cfg[$row['config_name']] = $row['config_value'];
            }
            if ($update_cache) {
                $cache->set('config_' . $table, $cfg);
            }
        }
    }

    $cfg = $cache->get('config_' . $table);

    return $cfg;
}

/**
 * @param $params
 * @param string $table
 */
function bb_update_config($params, $table = BB_CONFIG)
{
    $updates = [];
    foreach ($params as $name => $val) {
        $updates[] = array(
            'config_name' => $name,
            'config_value' => $val,
        );
    }
    $updates = DB()->build_array('MULTI_INSERT', $updates);

    DB()->query("REPLACE INTO $table $updates");

    // Update cache
    bb_get_config($table, true, true);
}

/**
 * @param $mode
 * @return bool
 */
function get_db_stat($mode)
{
    switch ($mode) {
        case 'usercount':
            $sql = "SELECT COUNT(user_id) AS total FROM " . BB_USERS;
            break;

        case 'newestuser':
            $sql = "SELECT user_id, username FROM " . BB_USERS . " WHERE user_id <> " . GUEST_UID . " ORDER BY user_id DESC LIMIT 1";
            break;

        case 'postcount':
        case 'topiccount':
            $sql = "SELECT SUM(forum_topics) AS topic_total, SUM(forum_posts) AS post_total FROM " . BB_FORUMS;
            break;
    }

    if (!($result = DB()->sql_query($sql))) {
        return false;
    }

    $row = DB()->sql_fetchrow($result);

    switch ($mode) {
        case 'usercount':
            return $row['total'];
            break;
        case 'newestuser':
            return $row;
            break;
        case 'postcount':
            return $row['post_total'];
            break;
        case 'topiccount':
            return $row['topic_total'];
            break;
    }

    return false;
}

/**
 * @param $username
 * @return mixed|string
 */
function clean_username($username)
{
    $username = mb_substr(htmlspecialchars(str_replace("\'", "'", trim($username))), 0, 25, 'UTF-8');
    $username = bb_rtrim($username, "\\");
    $username = str_replace("'", "\'", $username);

    return $username;
}

/**
 * @param $str
 * @param bool $charlist
 * @return string
 */
function bb_ltrim($str, $charlist = false)
{
    if ($charlist === false) {
        return ltrim($str);
    }

    $str = ltrim($str, $charlist);

    return $str;
}

/**
 * @param $str
 * @param bool $charlist
 * @return string
 */
function bb_rtrim($str, $charlist = false)
{
    if ($charlist === false) {
        return rtrim($str);
    }

    $str = rtrim($str, $charlist);

    return $str;
}

// Get Userdata, $u can be username or user_id. If $force_name is true, the username will be forced.
/**
 * @param $u
 * @param bool $force_name
 * @param bool $allow_guest
 * @return array|bool|mixed|null
 */
function get_userdata($u, $force_name = false, $allow_guest = false)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    /** @var \TorrentPier\Cache\Adapter $cache */
    $cache = $di->cache;

    if (!$u) {
        return false;
    }

    if (intval($u) == GUEST_UID && $allow_guest) {
        if ($u_data = $cache->get('guest_userdata')) {
            return $u_data;
        }
    }

    $u_data = [];
    $name_search = false;
    $exclude_anon_sql = (!$allow_guest) ? "AND user_id != " . GUEST_UID : '';

    if ($force_name || !is_numeric($u)) {
        $name_search = true;
        $where_sql = "WHERE username = '" . DB()->escape(clean_username($u)) . "'";
    } else {
        $where_sql = "WHERE user_id = " . (int)$u;
    }

    $sql = "SELECT * FROM " . BB_USERS . " $where_sql $exclude_anon_sql LIMIT 1";

    if (!$u_data = DB()->fetch_row($sql)) {
        if (!is_int($u) && !$name_search) {
            $where_sql = "WHERE username = '" . DB()->escape(clean_username($u)) . "'";
            $sql = "SELECT * FROM " . BB_USERS . " $where_sql $exclude_anon_sql LIMIT 1";
            $u_data = DB()->fetch_row($sql);
        }
    }

    if ($u_data['user_id'] == GUEST_UID) {
        $cache->set('guest_userdata', $u_data);
    }

    return $u_data;
}

/**
 * @param int $selected
 */
function make_jumpbox($selected = 0)
{
    global $datastore, $template;

    if (!$jumpbox = $datastore->get('jumpbox')) {
        $datastore->update('jumpbox');
        $jumpbox = $datastore->get('jumpbox');
    }

    $template->assign_vars(array(
        'JUMPBOX' => (IS_GUEST) ? $jumpbox['guest'] : $jumpbox['user'],
    ));
}

// $mode: array(not_auth_forum1,not_auth_forum2,..) or (string) 'mode'
/**
 * @param string $mode
 * @param string $name
 * @param null $selected
 * @param int $max_length
 * @param null $multiple_size
 * @param string $js
 * @param null $all_forums_option
 * @return string
 */
function get_forum_select($mode = 'guest', $name = POST_FORUM_URL, $selected = null, $max_length = HTML_SELECT_MAX_LENGTH, $multiple_size = null, $js = '', $all_forums_option = null)
{
    global $lang, $datastore;

    if (is_array($mode)) {
        $not_auth_forums_fary = array_flip($mode);
        $mode = 'not_auth_forums';
    }
    if (is_null($max_length)) {
        $max_length = HTML_SELECT_MAX_LENGTH;
    }
    $select = is_null($all_forums_option) ? [] : array($lang['ALL_AVAILABLE'] => $all_forums_option);
    if (!$forums = $datastore->get('cat_forums')) {
        $datastore->update('cat_forums');
        $forums = $datastore->get('cat_forums');
    }

    foreach ($forums['f'] as $fid => $f) {
        switch ($mode) {
            case 'guest':
                if ($f['auth_view'] != AUTH_ALL) {
                    continue 2;
                }
                break;

            case 'user':
                if ($f['auth_view'] != AUTH_ALL && $f['auth_view'] != AUTH_REG) {
                    continue 2;
                }
                break;

            case 'not_auth_forums':
                if (isset($not_auth_forums_fary[$f['forum_id']])) {
                    continue 2;
                }
                break;

            case 'admin':
                break;

            default:
                trigger_error(__FUNCTION__ . ": invalid mode '$mode'", E_USER_ERROR);
        }
        $cat_title = $forums['c'][$f['cat_id']]['cat_title'];
        $f_name = ($f['forum_parent']) ? ' |- ' : '';
        $f_name .= $f['forum_name'];

        while (isset($select[$cat_title][$f_name])) {
            $f_name .= ' ';
        }

        $select[$cat_title][$f_name] = $fid;

        if (!$f['forum_parent']) {
            $class = 'root_forum';
            $class .= isset($f['subforums']) ? ' has_sf' : '';
            $select['__attributes'][$cat_title][$f_name]['class'] = $class;
        }
    }

    return build_select($name, $select, $selected, $max_length, $multiple_size, $js);
}

/**
 * @return array
 */
function setup_style()
{
    global $template, $userdata;

    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    // AdminCP works only with default template
    $tpl_dir_name = defined('IN_ADMIN') ? 'default' : basename($di->config->get('tpl_name'));
    $stylesheet = defined('IN_ADMIN') ? 'main.css' : basename($di->config->get('stylesheet'));

    if (!IS_GUEST && !empty($userdata['tpl_name'])) {
        foreach ($di->config->get('templates') as $folder => $name) {
            if ($userdata['tpl_name'] == $folder) {
                $tpl_dir_name = basename($userdata['tpl_name']);
            }
        }
    }

    $template = new Template(TEMPLATES_DIR . $tpl_dir_name);
    $css_dir = 'styles/' . basename(TEMPLATES_DIR) . '/' . $tpl_dir_name . '/css/';

    $template->assign_vars(array(
        'BB_ROOT' => BB_ROOT,
        'SPACER' => make_url('styles/images/spacer.gif'),
        'STYLESHEET' => make_url($css_dir . $stylesheet),
        'EXT_LINK_NEW_WIN' => $di->config->get('ext_link_new_win'),
        'TPL_DIR' => make_url($css_dir),
        'SITE_URL' => make_url('/'),
    ));

    require(TEMPLATES_DIR . $tpl_dir_name . '/tpl_config.php');

    $theme = array('template_name' => $tpl_dir_name);

    return $theme;
}

// Create date / time with format and friendly date
/**
 * @param $gmepoch
 * @param bool $format
 * @param bool $friendly_date
 * @return false|string
 */
function bb_date($gmepoch, $format = false, $friendly_date = true)
{
    global $lang, $userdata;

    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    if (!$format) {
        $format = $di->config->get('default_dateformat');
    }
    if (empty($lang)) {
        require_once($di->config->get('default_lang_dir') . 'main.php');
    }

    if (empty($userdata['session_logged_in'])) {
        $tz = $di->config->get('board_timezone');
    } else {
        $tz = $userdata['user_timezone'];
    }

    $date = gmdate($format, $gmepoch + (3600 * $tz));

    if ($friendly_date) {
        $time_format = ' H:i';

        $today = gmdate('d', TIMENOW + (3600 * $tz));
        $month = gmdate('m', TIMENOW + (3600 * $tz));
        $year = gmdate('Y', TIMENOW + (3600 * $tz));

        $date_today = gmdate('d', $gmepoch + (3600 * $tz));
        $date_month = gmdate('m', $gmepoch + (3600 * $tz));
        $date_year = gmdate('Y', $gmepoch + (3600 * $tz));

        if ($date_today == $today && $date_month == $month && $date_year == $year) {
            $date = 'today' . gmdate($time_format, $gmepoch + (3600 * $tz));
        } elseif ($today != 1 && $date_today == ($today - 1) && $date_month == $month && $date_year == $year) {
            $date = 'yesterday' . gmdate($time_format, $gmepoch + (3600 * $tz));
        } elseif ($today == 1 && $month != 1) {
            $yesterday = date('t', mktime(0, 0, 0, ($month - 1), 1, $year));
            if ($date_today == $yesterday && $date_month == ($month - 1) && $date_year == $year) {
                $date = 'yesterday' . gmdate($time_format, $gmepoch + (3600 * $tz));
            }
        } elseif ($today == 1 && $month == 1) {
            $yesterday = date('t', mktime(0, 0, 0, 12, 1, ($year - 1)));
            if ($date_today == $yesterday && $date_month == 12 && $date_year == ($year - 1)) {
                $date = 'yesterday' . gmdate($time_format, $gmepoch + (3600 * $tz));
            }
        }
    }

    return ($di->config->get('translate_dates')) ? strtr(strtoupper($date), $lang['DATETIME']) : $date;
}

/**
 * @param $date
 * @return bool|mixed
 */
function birthday_age($date)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    if (!$date) {
        return false;
    }

    $tz = TIMENOW + (3600 * $di->config->get('board_timezone'));

    return delta_time(strtotime($date, $tz));
}

//
// Pagination routine, generates
// page number sequence
//
/**
 * @param $base_url
 * @param $num_items
 * @param $per_page
 * @param $start_item
 * @param bool $add_prevnext_text
 * @return mixed|string
 */
function generate_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = true)
{
    global $lang, $template;

// Pagination Mod
    $begin_end = 3;
    $from_middle = 1;
    /*
        By default, $begin_end is 3, and $from_middle is 1, so on page 6 in a 12 page view, it will look like this:

        a, d = $begin_end = 3
        b, c = $from_middle = 1

     "begin"        "middle"           "end"
        |              |                 |
        |     a     b  |  c     d        |
        |     |     |  |  |     |        |
        v     v     v  v  v     v        v
        1, 2, 3 ... 5, 6, 7 ... 10, 11, 12

        Change $begin_end and $from_middle to suit your needs appropriately
    */

    $total_pages = ceil($num_items / $per_page);

    if ($total_pages == 1 || $num_items == 0) {
        return '';
    }

    $on_page = floor($start_item / $per_page) + 1;

    $page_string = '';
    if ($total_pages > ((2 * ($begin_end + $from_middle)) + 2)) {
        $init_page_max = ($total_pages > $begin_end) ? $begin_end : $total_pages;
        for ($i = 1; $i < $init_page_max + 1; $i++) {
            $page_string .= ($i == $on_page) ? '<b>' . $i . '</b>' : '<a href="' . $base_url . "&amp;start=" . (($i - 1) * $per_page) . '">' . $i . '</a>';
            if ($i < $init_page_max) {
                $page_string .= ", ";
            }
        }
        if ($total_pages > $begin_end) {
            if ($on_page > 1 && $on_page < $total_pages) {
                $page_string .= ($on_page > ($begin_end + $from_middle + 1)) ? ' ... ' : ', ';

                $init_page_min = ($on_page > ($begin_end + $from_middle)) ? $on_page : ($begin_end + $from_middle + 1);

                $init_page_max = ($on_page < $total_pages - ($begin_end + $from_middle)) ? $on_page : $total_pages - ($begin_end + $from_middle);

                for ($i = $init_page_min - $from_middle; $i < $init_page_max + ($from_middle + 1); $i++) {
                    $page_string .= ($i == $on_page) ? '<b>' . $i . '</b>' : '<a href="' . $base_url . "&amp;start=" . (($i - 1) * $per_page) . '">' . $i . '</a>';
                    if ($i < $init_page_max + $from_middle) {
                        $page_string .= ', ';
                    }
                }
                $page_string .= ($on_page < $total_pages - ($begin_end + $from_middle)) ? ' ... ' : ', ';
            } else {
                $page_string .= '&nbsp;...&nbsp;';
            }
            for ($i = $total_pages - ($begin_end - 1); $i < $total_pages + 1; $i++) {
                $page_string .= ($i == $on_page) ? '<b>' . $i . '</b>' : '<a href="' . $base_url . "&amp;start=" . (($i - 1) * $per_page) . '">' . $i . '</a>';
                if ($i < $total_pages) {
                    $page_string .= ", ";
                }
            }
        }
    } else {
        for ($i = 1; $i < $total_pages + 1; $i++) {
            $page_string .= ($i == $on_page) ? '<b>' . $i . '</b>' : '<a href="' . $base_url . "&amp;start=" . (($i - 1) * $per_page) . '">' . $i . '</a>';
            if ($i < $total_pages) {
                $page_string .= ', ';
            }
        }
    }

    if ($add_prevnext_text) {
        if ($on_page > 1) {
            $page_string = ' <a href="' . $base_url . "&amp;start=" . (($on_page - 2) * $per_page) . '">' . $lang['PREVIOUS_PAGE'] . '</a>&nbsp;&nbsp;' . $page_string;
        }

        if ($on_page < $total_pages) {
            $page_string .= '&nbsp;&nbsp;<a href="' . $base_url . "&amp;start=" . ($on_page * $per_page) . '">' . $lang['NEXT_PAGE'] . '</a>';
        }
    }

    $pagination = ($page_string) ? '<a class="menu-root" href="#pg-jump">' . $lang['GOTO_PAGE'] . '</a> :&nbsp;&nbsp;' . $page_string : '';
    $pagination = str_replace('&amp;start=0', '', $pagination);

    $template->assign_vars(array(
        'PAGINATION' => $pagination,
        'PAGE_NUMBER' => sprintf($lang['PAGE_OF'], (floor($start_item / $per_page) + 1), ceil($num_items / $per_page)),
        'PG_BASE_URL' => $base_url,
        'PG_PER_PAGE' => $per_page,
    ));

    return $pagination;
}

//
// Obtain list of naughty words and build preg style replacement arrays for use by the
// calling script, note that the vars are passed as references this just makes it easier
// to return both sets of arrays
//
/**
 * @param $orig_word
 * @param $replacement_word
 * @return bool
 */
function obtain_word_list(&$orig_word, &$replacement_word)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    /** @var \TorrentPier\Cache\Adapter $cache */
    $cache = $di->cache;

    if (!$di->config->get('use_word_censor')) {
        return false;
    }

    if (!$cache->has('censored')) {
        $sql = DB()->fetch_rowset("SELECT word, replacement FROM " . BB_WORDS);
        if (!$sql) {
            $sql = [['word' => 1, 'replacement' => 1]];
        }
        $cache->set('censored', $sql, 7200);
    }

    $sql = $cache->get('censored');

    foreach ($sql as $row) {
        $orig_word[] = '#(?<![\p{Nd}\p{L}_])(' . str_replace('\*', '[\p{Nd}\p{L}_]*?', preg_quote($row['word'], '#')) . ')(?![\p{Nd}\p{L}_])#iu';
        $replacement_word[] = $row['replacement'];
    }

    return true;
}

/**
 * @param $msg_text
 */
function bb_die($msg_text)
{
    global $ajax, $lang, $template, $theme, $user;

    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    if (defined('IN_AJAX')) {
        $ajax->ajax_die($msg_text);
    }

    // Check
    if (defined('HAS_DIED')) {
        trigger_error(__FUNCTION__ . ' was called multiple times', E_USER_ERROR);
    }
    define('HAS_DIED', 1);
    define('DISABLE_CACHING_OUTPUT', true);

    // If empty lang
    if (empty($lang)) {
        require($di->config->get('default_lang_dir') . 'main.php');
    }

    // If empty session
    if (empty($user)) {
        $user->session_start();
    }

    // If the header hasn't been output then do it
    if (!defined('PAGE_HEADER_SENT')) {
        if (empty($template)) {
            $template = new Template(BB_ROOT . "templates/{$di->config->get('tpl_name')}");
        }
        if (empty($theme)) {
            $theme = setup_style();
        }
        require(PAGE_HEADER);
    }

    // Check for lang variable
    if (!empty($lang[$msg_text])) {
        $msg_text = $lang[$msg_text];
    }

    $template->assign_vars(array(
        'TPL_BB_DIE' => true,
        'MESSAGE_TEXT' => $msg_text,
    ));

    $template->set_filenames(array('bb_die' => 'common.tpl'));
    $template->pparse('bb_die');

    require(PAGE_FOOTER);

    exit;
}

/**
 * @param $txt
 */
function bb_simple_die($txt)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    if (!empty($_COOKIE['explain'])) {
        bb_die("bb_simple_die:<br /><br />$txt");
    }

    header('Content-Type: text/plain; charset=' . $di->config->get('charset'));
    die($txt);
}

/**
 * @param $path
 * @return string
 */
function bb_realpath($path)
{
    return (!function_exists('realpath') || !realpath(INC_DIR . 'functions.php')) ? $path : realpath($path);
}

/**
 * @param string $url
 */
function login_redirect($url = '')
{
    redirect(LOGIN_URL . '?redirect=' . (($url) ? $url : (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/')));
}

/**
 * @param $url
 * @param int $time
 */
function meta_refresh($url, $time = 5)
{
    global $template;

    $template->assign_var('META', '<meta http-equiv="refresh" content="' . $time . ';url=' . $url . '" />');
}

/**
 * @param $url
 */
function redirect($url)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    if (headers_sent($filename, $linenum)) {
        trigger_error("Headers already sent in $filename($linenum)", E_USER_ERROR);
    }

    if (strstr(urldecode($url), "\n") || strstr(urldecode($url), "\r") || strstr(urldecode($url), ';url')) {
        bb_die('Tried to redirect to potentially insecure url');
    }

    $url = trim($url);
    $server_protocol = ($di->config->get('cookie_secure')) ? 'https://' : 'http://';

    $server_name = preg_replace('#^\/?(.*?)\/?$#', '\1', trim($di->config->get('server_name')));
    $server_port = ($di->config->get('server_port') <> 80) ? ':' . trim($di->config->get('server_port')) : '';
    $script_name = preg_replace('#^\/?(.*?)\/?$#', '\1', trim($di->config->get('script_path')));

    if ($script_name) {
        $script_name = "/$script_name";
        $url = preg_replace("#^$script_name#", '', $url);
    }

    $redirect_url = $server_protocol . $server_name . $server_port . $script_name . preg_replace('#^\/?(.*?)\/?$#', '/\1', $url);

    // Behave as per HTTP/1.1 spec for others
    header('Location: ' . $redirect_url);
    exit;
}

// build a list of the sortable fields or return field name
/**
 * @param int $selected_row
 * @param string $action
 * @param string $list
 * @return string
 */
function get_forum_display_sort_option($selected_row = 0, $action = 'list', $list = 'sort')
{
    global $lang;

    $forum_display_sort = array(
        'lang_key' => array('LASTPOST', 'SORT_TOPIC_TITLE', 'SORT_TIME'),
        'fields' => array('t.topic_last_post_time', 't.topic_title', 't.topic_time'),
    );
    $forum_display_order = array(
        'lang_key' => array('DESC', 'ASC'),
        'fields' => array('DESC', 'ASC'),
    );

    // get the good list
    $list_name = 'forum_display_' . $list;
    $listrow = $$list_name;

    // init the result
    $res = '';
    if ($selected_row > count($listrow['lang_key'])) {
        $selected_row = 0;
    }

    // build list
    if ($action == 'list') {
        for ($i = 0; $i < count($listrow['lang_key']); $i++) {
            $selected = ($i == $selected_row) ? ' selected="selected"' : '';
            $l_value = (isset($lang[$listrow['lang_key'][$i]])) ? $lang[$listrow['lang_key'][$i]] : $listrow['lang_key'][$i];
            $res .= '<option value="' . $i . '"' . $selected . '>' . $l_value . '</option>';
        }
    } else {
        // field
        $res = $listrow['fields'][$selected_row];
    }
    return $res;
}

/**
 * @param $topics_csv
 */
function clear_dl_list($topics_csv)
{
    DB()->query("DELETE FROM " . BB_BT_DLSTATUS . " WHERE topic_id IN($topics_csv)");
    DB()->query("DELETE FROM " . BB_BT_DLSTATUS_SNAP . " WHERE topic_id IN($topics_csv)");
}

// $ids - array(id1,id2,..) or (string) id
/**
 * @param $ids
 * @return string
 */
function get_id_csv($ids)
{
    $ids = array_values((array)$ids);
    array_deep($ids, 'intval', 'one-dimensional');
    return (string)join(',', $ids);
}

// $ids - array(id1,id2,..) or (string) id1,id2,..
/**
 * @param $ids
 * @return array
 */
function get_id_ary($ids)
{
    $ids = is_string($ids) ? explode(',', $ids) : array_values((array)$ids);
    array_deep($ids, 'intval', 'one-dimensional');
    return (array)$ids;
}

/**
 * @param $topic_id
 * @return mixed
 */
function get_topic_title($topic_id)
{
    $row = DB()->fetch_row("
		SELECT topic_title FROM " . BB_TOPICS . " WHERE topic_id = " . (int)$topic_id . "
	");
    return $row['topic_title'];
}

/**
 * @param $forum_id
 * @return mixed
 */
function forum_exists($forum_id)
{
    return DB()->fetch_row("SELECT forum_id FROM " . BB_FORUMS . " WHERE forum_id = $forum_id LIMIT 1");
}

/**
 * @param $cat_id
 * @return mixed
 */
function cat_exists($cat_id)
{
    return DB()->fetch_row("SELECT cat_id FROM " . BB_CATEGORIES . " WHERE cat_id = $cat_id LIMIT 1");
}

//
// Action Log
//
class log_action
{
    public $log_type = [
        #    LOG_TYPE_NAME   LOG_TYPE_ID
        'mod_topic_delete' => 1,
        'mod_topic_move' => 2,
        'mod_topic_lock' => 3,
        'mod_topic_unlock' => 4,
        'mod_post_delete' => 5,
        'mod_topic_split' => 6,
        'adm_user_delete' => 7,
        'adm_user_ban' => 8,
        'adm_user_unban' => 9,
        'tor_status_changed' => 10,
        'att_delete' => 11,
        'tor_unreg' => 12,
        'tor_cphold_close' => 13,
        'adm_ban_ip' => 14,
        'adm_ban_email' => 15,
        'adm_ban_name' => 16,
    ];
    public $log_type_select = [];
    public $log_disabled = false;

    /**
     *
     */
    public function init()
    {
        global $lang;

        foreach ($lang['LOG_ACTION']['LOG_TYPE'] as $log_type => $log_desc) {
            $this->log_type_select[strip_tags($log_desc)] = $this->log_type[$log_type];
        }
    }

    /**
     * @param $type_name
     * @param array $args
     */
    public function mod($type_name, $args = array())
    {
        global $userdata;

        if (empty($this->log_type)) {
            $this->init();
        }
        if ($this->log_disabled) {
            return;
        }

        $forum_id =& $args['forum_id'];
        $forum_id_new =& $args['forum_id_new'];
        $topic_id =& $args['topic_id'];
        $topic_id_new =& $args['topic_id_new'];
        $topic_title =& $args['topic_title'];
        $topic_title_new =& $args['topic_title_new'];
        $log_msg =& $args['log_msg'];

        if (!empty($userdata)) {
            $user_id = $userdata['user_id'];
            $session_ip = $userdata['session_ip'];
        } else {
            $user_id = '';
            $session_ip = '';
        }

        $sql_ary = array(
            'log_type_id' => (int)$this->log_type["$type_name"],
            'log_user_id' => (int)$user_id,
            'log_user_ip' => (string)$session_ip,
            'log_forum_id' => (int)$forum_id,
            'log_forum_id_new' => (int)$forum_id_new,
            'log_topic_id' => (int)$topic_id,
            'log_topic_id_new' => (int)$topic_id_new,
            'log_topic_title' => (string)$topic_title,
            'log_topic_title_new' => (string)$topic_title_new,
            'log_time' => (int)TIMENOW,
            'log_msg' => (string)$log_msg,
        );
        $sql_args = DB()->build_array('INSERT', $sql_ary);

        DB()->query("INSERT INTO " . BB_LOG . " $sql_args");
    }

    /**
     * @param $type_name
     * @param array $args
     */
    public function admin($type_name, $args = array())
    {
        $this->mod($type_name, $args);
    }
}

/**
 * @param $topic
 * @param null $is_unread
 * @return mixed
 */
function get_topic_icon($topic, $is_unread = null)
{
    global $images;

    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    $t_hot = ($topic['topic_replies'] >= $di->config->get('hot_threshold'));
    $is_unread = is_null($is_unread) ? is_unread($topic['topic_last_post_time'], $topic['topic_id'], $topic['forum_id']) : $is_unread;

    if ($topic['topic_status'] == TOPIC_MOVED) {
        $folder_image = $images['folder'];
    } else {
        $folder = ($t_hot) ? $images['folder_hot'] : $images['folder'];
        $folder_new = ($t_hot) ? $images['folder_hot_new'] : $images['folder_new'];

        if ($topic['topic_type'] == POST_ANNOUNCE) {
            $folder = $images['folder_announce'];
            $folder_new = $images['folder_announce_new'];
        } elseif ($topic['topic_type'] == POST_STICKY) {
            $folder = $images['folder_sticky'];
            $folder_new = $images['folder_sticky_new'];
        } elseif ($topic['topic_status'] == TOPIC_LOCKED) {
            $folder = $images['folder_locked'];
            $folder_new = $images['folder_locked_new'];
        } elseif (isset($topic['tracker_status'])) {
            $folder = ($t_hot) ? $images['folder_dl_hot'] : $images['folder_dl'];
            $folder_new = ($t_hot) ? $images['folder_dl_hot_new'] : $images['folder_dl_new'];
        }

        $folder_image = ($is_unread) ? $folder_new : $folder;
    }

    return $folder_image;
}

/**
 * @param $url
 * @param $replies
 * @param $per_page
 * @return string
 */
function build_topic_pagination($url, $replies, $per_page)
{
    $pg = '';

    if (++$replies > $per_page) {
        $total_pages = ceil($replies / $per_page);

        for ($j = 0, $page = 1; $j < $replies; $j += $per_page, $page++) {
            $href = ($j) ? "$url&amp;start=$j" : $url;
            $pg .= '<a href="' . $href . '" class="topicPG">' . $page . '</a>';

            if ($page == 1 && $total_pages > 3) {
                $pg .= ' .. ';
                $page = $total_pages - 2;
                $j += ($total_pages - 3) * $per_page;
            } elseif ($page < $total_pages) {
                $pg .= ', ';
            }
        }
    }

    return $pg;
}

//
// Poll
//
/**
 * @param $topic_id
 * @return array|bool|mixed
 */
function get_poll_data_items_js($topic_id)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    /** @var \TorrentPier\Cache\Adapter $cache */
    $cache = $di->cache;

    if (!$topic_id_csv = get_id_csv($topic_id)) {
        return is_array($topic_id) ? [] : false;
    }
    $items = [];

    if (!$cache->has('poll_' . $topic_id)) {
        $poll_data = DB()->fetch_rowset("
			SELECT topic_id, vote_id, vote_text, vote_result
			FROM " . BB_POLL_VOTES . "
			WHERE topic_id IN($topic_id_csv)
			ORDER BY topic_id, vote_id
		");
        $cache->set('poll_' . $topic_id, $poll_data);
    }

    $poll_data = $cache->get('poll_' . $topic_id);

    foreach ($poll_data as $row) {
        $opt_text_for_js = htmlCHR($row['vote_text']);
        $opt_result_for_js = (int)$row['vote_result'];

        $items[$row['topic_id']][$row['vote_id']] = array($opt_text_for_js, $opt_result_for_js);
    }
    foreach ($items as $k => $v) {
        $items[$k] = \Zend\Json\Json::encode($v);
    }

    return is_array($topic_id) ? $items : $items[$topic_id];
}

/**
 * @param $t_data
 * @return bool
 */
function poll_is_active($t_data)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    return ($t_data['topic_vote'] == 1 && $t_data['topic_time'] > TIMENOW - $di->config->get('poll_max_days') * 86400);
}

/**
 * @param $tpl_vars
 */
function print_confirmation($tpl_vars)
{
    global $template, $lang;

    $template->assign_vars(array(
        'TPL_CONFIRM' => true,
        'CONFIRM_TITLE' => $lang['CONFIRM'],
        'FORM_METHOD' => 'post',
    ));
    $template->assign_vars($tpl_vars);

    print_page('common.tpl');
}

/**
 *  $args = array(
 *            'tpl'    => 'template file name',
 *            'simple' => $gen_simple_header,
 *          );
 *       OR (string) 'template_file_name'
 *
 *  $type = ''        (common forum page)
 *          'admin'   (adminCP page)
 *          'simple'  (simple page without common header)
 *
 *  $mode = 'no_header'
 *          'no_footer'
 *
 * @param        $args
 * @param string $type
 * @param string $mode
 */
function print_page($args, $type = '', $mode = '')
{
    global $template, $gen_simple_header;

    $tpl = (is_array($args) && !empty($args['tpl'])) ? $args['tpl'] : $args;
    $tpl = ($type === 'admin') ? ADMIN_TPL_DIR . $tpl : $tpl;

    $gen_simple_header = (is_array($args) && !empty($args['simple']) or $type === 'simple') ? true : $gen_simple_header;

    if ($mode !== 'no_header') {
        require(PAGE_HEADER);
    }

    $template->set_filenames(array('body' => $tpl));
    $template->pparse('body');

    if ($mode !== 'no_footer') {
        require(PAGE_FOOTER);
    }
}

/**
 * @param $str
 * @param bool $replace_underscore
 * @return mixed|string
 */
function clean_title($str, $replace_underscore = false)
{
    $str = ($replace_underscore) ? str_replace('_', ' ', $str) : $str;
    $str = htmlCHR(str_compact($str));
    return $str;
}

/**
 * @param $text
 * @param bool $ltrim_star
 * @param bool $remove_stopwords
 * @param bool $die_if_empty
 * @return mixed|string
 */
function clean_text_match($text, $ltrim_star = true, $remove_stopwords = false, $die_if_empty = false)
{
    global $lang;

    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    $text = str_compact($text);
    $ltrim_chars = ($ltrim_star) ? ' *-!' : ' ';
    $wrap_with_quotes = preg_match('#^"[^"]+"$#', $text);

    $text = ' ' . str_compact(ltrim($text, $ltrim_chars)) . ' ';

    if ($remove_stopwords) {
        $text = remove_stopwords($text);
    }

    if ($di->config->get('sphinx_enabled')) {
        $text = preg_replace('#(?<=\S)\-#u', ' ', $text);                 // "1-2-3" -> "1 2 3"
        $text = preg_replace('#[^0-9a-zA-Zа-яА-ЯёЁ\-_*|]#u', ' ', $text); // допустимые символы (кроме " которые отдельно)
        $text = str_replace('-', ' -', $text);                            // - только в начале слова
        $text = str_replace('*', '* ', $text);                            // * только в конце слова
        $text = preg_replace('#\s*\|\s*#u', '|', $text);                  // "| " -> "|"
        $text = preg_replace('#\|+#u', ' | ', $text);                     // "||" -> "|"
        $text = preg_replace('#(?<=\s)[\-*]+\s#u', ' ', $text);           // одиночные " - ", " * "
        $text = trim($text, ' -|');
        $text = str_compact($text);
        $text_match_sql = ($wrap_with_quotes && $text != '') ? '"' . $text . '"' : $text;
    } else {
        $text_match_sql = DB()->escape(trim($text));
    }

    if (!$text_match_sql && $die_if_empty) {
        bb_die($lang['NO_SEARCH_MATCH']);
    }

    return $text_match_sql;
}

/**
 *
 */
function init_sphinx()
{
    global $sphinx;

    if (!isset($sphinx)) {
        if (!class_exists('SphinxClient')) {
            require(INC_DIR . 'api/sphinx.php');
        }
        $sphinx = new SphinxClient();

        $sphinx->SetConnectTimeout(5);
        $sphinx->SetRankingMode(SPH_RANK_NONE);
        $sphinx->SetMatchMode(SPH_MATCH_BOOLEAN);
    }
}

/**
 * @param $err_type
 * @param $err_msg
 * @param string $query
 */
function log_sphinx_error($err_type, $err_msg, $query = '')
{
    $ignore_err_txt = array(
        'negation on top level',
        'Query word length is less than min prefix length',
    );
    if (!count($ignore_err_txt) || !preg_match('#' . join('|', $ignore_err_txt) . '#i', $err_msg)) {
        $orig_query = strtr($_REQUEST['nm'], array("\n" => '\n'));
        bb_log(date('m-d H:i:s') . " | $err_type | $err_msg | $orig_query | $query" . LOG_LF, 'sphinx_error');
    }
}

/**
 * @param $search
 * @return array
 */
function get_title_match_topics($search)
{
    global $sphinx, $userdata, $lang;

    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    $where_ids = [];
    $forum_ids = (isset($search['ids']) && is_array($search['ids'])) ? array_diff($search['ids'], array(0 => 0)) : '';
    $title_match_sql = encode_text_match($search['query']);

    if ($di->config->get('sphinx_enabled')) {
        init_sphinx();

        $where = (isset($search['topic_match'])) ? 'topics' : 'posts';

        $sphinx->SetServer($di->config->get('sphinx_topic_titles_host'), $di->config->get('sphinx_topic_titles_port'));
        if ($forum_ids) {
            $sphinx->SetFilter('forum_id', $forum_ids, false);
        }
        if (preg_match('#^"[^"]+"$#u', $title_match_sql)) {
            $sphinx->SetMatchMode(SPH_MATCH_PHRASE);
        }
        if ($result = $sphinx->Query($title_match_sql, $where, $userdata['username'] . ' (' . CLIENT_IP . ')')) {
            if (!empty($result['matches'])) {
                $where_ids = array_keys($result['matches']);
            }
        } elseif ($error = $sphinx->GetLastError()) {
            if (strpos($error, 'errno=110')) {
                bb_die($lang['SEARCH_ERROR']);
            }
            log_sphinx_error('ERR', $error, $title_match_sql);
        }
        if ($warning = $sphinx->GetLastWarning()) {
            log_sphinx_error('wrn', $warning, $title_match_sql);
        }
    } else {
        $where_forum = ($forum_ids) ? "AND forum_id IN(" . join(',', $forum_ids) . ")" : '';
        $search_bool_mode = ($di->config->get('allow_search_in_bool_mode')) ? ' IN BOOLEAN MODE' : '';

        if (isset($search['topic_match'])) {
            $where_id = 'topic_id';
            $sql = "SELECT topic_id FROM " . BB_TOPICS . "
					WHERE MATCH (topic_title) AGAINST ('$title_match_sql'$search_bool_mode) OR topic_title LIKE '%$title_match_sql%'
					$where_forum";
        } else {
            $where_id = 'post_id';
            $sql = "SELECT p.post_id FROM " . BB_POSTS . " p, " . BB_POSTS_SEARCH . " ps
					WHERE ps.post_id = p.post_id
					AND MATCH (ps.search_words) AGAINST ('$title_match_sql'$search_bool_mode)
					$where_forum";
        }

        foreach (DB()->fetch_rowset($sql) as $row) {
            $where_ids[] = $row[$where_id];
        }
    }

    return $where_ids;
}

// для более корректного поиска по словам содержащим одиночную кавычку
/**
 * @param $txt
 * @return mixed
 */
function encode_text_match($txt)
{
    return str_replace("'", '&#039;', $txt);
}

/**
 * @param $txt
 * @return mixed
 */
function decode_text_match($txt)
{
    return str_replace('&#039;', "'", $txt);
}

/**
 * @param $text
 * @return mixed
 */
function remove_stopwords($text)
{
    static $stopwords = null;

    if (is_null($stopwords)) {
        $stopwords = explode(' ', str_compact(file_get_contents(LANG_DIR . 'search_stopwords.txt')));
        array_deep($stopwords, 'pad_with_space');
    }

    return ($stopwords) ? str_replace($stopwords, ' ', $text) : $text;
}

/**
 * @param $str
 * @return string
 */
function pad_with_space($str)
{
    return ($str) ? " $str " : $str;
}

/**
 * @param $infohash
 * @param $auth_key
 * @param $logged_in
 * @return string
 */
function create_magnet($infohash, $auth_key, $logged_in)
{
    global $images;

    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    $passkey_url = ((!$logged_in || isset($_GET['no_passkey'])) && $di->config->get('bt_tor_browse_only_reg')) ? '' : "?{$di->config->get('passkey_key')}=$auth_key";

    if ($di->config->get('ocelot.enabled')) {
        return '<a href="magnet:?xt=urn:btih:' . bin2hex($infohash) . '&tr=' . urlencode($di->config->get('ocelot.url') . $auth_key . "/announce") . '"><img src="' . $images['icon_magnet'] . '" width="12" height="12" border="0" /></a>';
    } else {
        return '<a href="magnet:?xt=urn:btih:' . bin2hex($infohash) . '&tr=' . urlencode($di->config->get('bt_announce_url') . $passkey_url) . '"><img src="' . $images['icon_magnet'] . '" width="12" height="12" border="0" /></a>';
    }
}

/**
 * @param null $forum_id
 * @param null $topic_id
 * @param null $group_id
 */
function set_die_append_msg($forum_id = null, $topic_id = null, $group_id = null)
{
    global $lang, $template;

    $msg = '';
    $msg .= ($topic_id) ? '<p class="mrg_10"><a href="' . TOPIC_URL . $topic_id . '">' . $lang['TOPIC_RETURN'] . '</a></p>' : '';
    $msg .= ($forum_id) ? '<p class="mrg_10"><a href="' . FORUM_URL . $forum_id . '">' . $lang['FORUM_RETURN'] . '</a></p>' : '';
    $msg .= ($group_id) ? '<p class="mrg_10"><a href="' . GROUP_URL . $group_id . '">' . $lang['GROUP_RETURN'] . '</a></p>' : '';
    $msg .= '<p class="mrg_10"><a href="index.php">' . $lang['INDEX_RETURN'] . '</a></p>';
    $template->assign_var('BB_DIE_APPEND_MSG', $msg);
}

/**
 * @param $pr_uid
 */
function set_pr_die_append_msg($pr_uid)
{
    global $lang, $template;

    $template->assign_var('BB_DIE_APPEND_MSG', '
		<a href="' . PROFILE_URL . $pr_uid . '" onclick="return post2url(this.href, {after_edit: 1});">' . $lang['PROFILE_RETURN'] . '</a>
		<br /><br />
		<a href="profile.php?mode=editprofile' . (IS_ADMIN ? "&amp;u=$pr_uid" : '') . '" onclick="return post2url(this.href, {after_edit: 1});">' . $lang['PROFILE_EDIT_RETURN'] . '</a>
		<br /><br />
		<a href="index.php">' . $lang['INDEX_RETURN'] . '</a>
	');
}

/**
 * @param $user_id
 * @param $subject
 * @param $message
 * @param int $poster_id
 */
function send_pm($user_id, $subject, $message, $poster_id = BOT_UID)
{
    global $userdata;

    $subject = DB()->escape($subject);
    $message = DB()->escape($message);

    if ($poster_id == BOT_UID) {
        $poster_ip = '7f000001';
    } elseif ($row = DB()->fetch_row("SELECT user_reg_ip FROM " . BB_USERS . " WHERE user_id = $poster_id")) {
        $poster_ip = $row['user_reg_ip'];
    } else {
        $poster_id = $userdata['user_id'];
        $poster_ip = USER_IP;
    }

    DB()->query("INSERT INTO " . BB_PRIVMSGS . " (privmsgs_type, privmsgs_subject, privmsgs_from_userid, privmsgs_to_userid, privmsgs_date, privmsgs_ip) VALUES (" . PRIVMSGS_NEW_MAIL . ", '$subject', {$poster_id}, $user_id, " . TIMENOW . ", '$poster_ip')");
    $pm_id = DB()->sql_nextid();

    DB()->query("INSERT INTO " . BB_PRIVMSGS_TEXT . " (privmsgs_text_id, privmsgs_text) VALUES ($pm_id, '$message')");
    DB()->query("UPDATE " . BB_USERS . " SET user_new_privmsg = user_new_privmsg + 1, user_last_privmsg = " . TIMENOW . ", user_newest_pm_id = $pm_id WHERE user_id = $user_id");
}

/**
 * @param $data
 * @return string
 */
function profile_url($data)
{
    global $lang, $datastore;

    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    if (!$ranks = $datastore->get('ranks')) {
        $datastore->update('ranks');
        $ranks = $datastore->get('ranks');
    }

    $user_rank = !empty($data['user_rank']) ? $data['user_rank'] : 0;

    if (isset($ranks[$user_rank])) {
        $title = $ranks[$user_rank]['rank_title'];
        $style = $ranks[$user_rank]['rank_style'];
    }
    if (empty($title)) {
        $title = $lang['USER'];
    }
    if (empty($style)) {
        $style = 'colorUser';
    }

    if (!$di->config->get('color_nick')) {
        $style = '';
    }

    $username = !empty($data['username']) ? $data['username'] : $lang['GUEST'];
    $user_id = (!empty($data['user_id']) && $username != $lang['GUEST']) ? $data['user_id'] : GUEST_UID;

    $profile = '<span title="' . $title . '" class="' . $style . '">' . $username . '</span>';

    if (!in_array($user_id, array('', GUEST_UID, BOT_UID)) && $username) {
        $profile = '<a href="' . make_url(PROFILE_URL . $user_id) . '">' . $profile . '</a>';
    }

    return $profile;
}

/**
 * @param $user_id
 * @param $ext_id
 * @param bool $allow_avatar
 * @param bool $size
 * @param string $height
 * @param string $width
 * @return string
 */
function get_avatar($user_id, $ext_id, $allow_avatar = true, $size = true, $height = '', $width = '')
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    if ($size) {
        // TODO размеры: s, m, l + кеширование
    }

    $height = ($height != '') ? 'height="' . $height . '"' : '';
    $width = ($width != '') ? 'width="' . $width . '"' : '';

    $user_avatar = '<img src="' . make_url($di->config->get('avatars.upload_path') . $di->config->get('avatars.no_avatar')) . '" alt="' . $user_id . '" ' . $height . ' ' . $width . ' />';

    if ($user_id == BOT_UID && $di->config->get('avatars.bot_avatar')) {
        $user_avatar = '<img src="' . make_url($di->config->get('avatars.upload_path') . $di->config->get('avatars.bot_avatar')) . '" alt="' . $user_id . '" ' . $height . ' ' . $width . ' />';
    } elseif ($allow_avatar && $ext_id) {
        if (file_exists(get_avatar_path($user_id, $ext_id))) {
            $user_avatar = '<img src="' . make_url(get_avatar_path($user_id, $ext_id)) . '" alt="' . $user_id . '" ' . $height . ' ' . $width . ' />';
        }
    }

    return $user_avatar;
}

/**
 * @param $gender
 * @return string
 */
function gender_image($gender)
{
    global $lang, $images;

    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    if (!$di->config->get('gender')) {
        $user_gender = '';
        return $user_gender;
    } else {
        switch ($gender) {
            case MALE:
                $user_gender = '<img src="' . $images['icon_male'] . '" alt="' . $lang['GENDER_SELECT'][MALE] . '" title="' . $lang['GENDER_SELECT'][MALE] . '" border="0" />';
                break;
            case FEMALE:
                $user_gender = '<img src="' . $images['icon_female'] . '" alt="' . $lang['GENDER_SELECT'][FEMALE] . '" title="' . $lang['GENDER_SELECT'][FEMALE] . '" border="0" />';
                break;
            default:
                $user_gender = '<img src="' . $images['icon_nogender'] . '" alt="' . $lang['GENDER_SELECT'][NOGENDER] . '" title="' . $lang['GENDER_SELECT'][NOGENDER] . '" border="0" />';
                break;
        }
    }

    return $user_gender;
}

/**
 * @param $type
 * @return string
 */
function is_gold($type)
{
    global $lang, $tr_cfg;

    if (!$tr_cfg['gold_silver_enabled']) {
        $is_gold = '';
        return $is_gold;
    } else {
        switch ($type) {
            case TOR_TYPE_GOLD:
                $is_gold = '<img src="styles/images/tor_gold.gif" width="16" height="15" title="' . $lang['GOLD'] . '" />&nbsp;';
                break;
            case TOR_TYPE_SILVER:
                $is_gold = '<img src="styles/images/tor_silver.gif" width="16" height="15" title="' . $lang['SILVER'] . '" />&nbsp;';
                break;
            default:
                $is_gold = '';
                break;
        }
    }

    return $is_gold;
}

/**
 * @param $type
 * @param $id
 */
function update_atom($type, $id)
{
    require_once(INC_DIR . 'functions_atom.php');

    switch ($type) {
        case 'user':
            update_user_feed($id, get_username($id));
            break;

        case 'topic':
            $topic_poster = (int)DB()->fetch_row("SELECT topic_poster FROM " . BB_TOPICS . " WHERE topic_id = $id LIMIT 1", 'topic_poster');
            update_user_feed($topic_poster, get_username($topic_poster));
            break;
    }
}

/**
 * @param $hash
 */
function hash_search($hash)
{
    global $lang;

    $hash = htmlCHR(trim($hash));

    if (!isset($hash) || mb_strlen($hash, 'UTF-8') != 40) {
        bb_die(sprintf($lang['HASH_INVALID'], $hash));
    }

    $info_hash = DB()->escape(pack("H*", $hash));

    if ($row = DB()->fetch_row("SELECT topic_id FROM " . BB_BT_TORRENTS . " WHERE info_hash = '$info_hash'")) {
        redirect(TOPIC_URL . $row['topic_id']);
    } else {
        bb_die(sprintf($lang['HASH_NOT_FOUND'], $hash));
    }
}

/**
 * @param $mode
 * @param string $callback
 * @return bool|string
 */
function bb_captcha($mode, $callback = '')
{
    global $lang;

    $di = \TorrentPier\Di::getInstance();

    $secret = $di->config->get('captcha.secret_key');
    $public = $di->config->get('captcha.public_key');
    $cp_theme = $di->config->get('captcha.theme');

    if (!$public && !$secret) {
        bb_die($lang['CAPTCHA_SETTINGS']);
    }

    switch ($mode) {
        case 'get':
            return "
				<script type=\"text/javascript\">
					var onloadCallback = function() {
						grecaptcha.render('tp-captcha', {
							'sitekey'  : '" . $public . "',
							'theme'    : '" . $cp_theme . "',
							'callback' : '" . $callback . "'
						});
					};
				</script>
				<div id=\"tp-captcha\"></div>
				<script src=\"https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit\" async defer></script>";
            break;

        case 'check':
            $resp = $di->captcha->verify(
                $di->request->get('g-recaptcha-response', ''),
                $di->request->server->get('REMOTE_ADDR')
            );
            if ($resp->isSuccess()) {
                return true;
            }
            break;

        default:
            bb_simple_die(__FUNCTION__ . ": invalid mode '$mode'");
    }
    return false;
}
