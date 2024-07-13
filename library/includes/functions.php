<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

function get_path_from_id($id, $ext_id, $base_path, $first_div, $sec_div)
{
    global $bb_cfg;
    $ext = $bb_cfg['file_id_ext'][$ext_id] ?? '';
    return ($base_path ? "$base_path/" : '') . floor($id / $first_div) . '/' . ($id % $sec_div) . '/' . $id . ($ext ? ".$ext" : '');
}

function get_avatar_path($id, $ext_id, $base_path = null, $first_div = 10000, $sec_div = 100)
{
    global $bb_cfg;
    $base_path ??= $bb_cfg['avatars']['upload_path'];
    return get_path_from_id($id, $ext_id, $base_path, $first_div, $sec_div);
}

function get_attach_path($id, $ext_id = '', $base_path = null, $first_div = 10000, $sec_div = 100)
{
    global $bb_cfg;
    $base_path ??= $bb_cfg['attach']['upload_path'];
    return get_path_from_id($id, $ext_id, $base_path, $first_div, $sec_div);
}

function delete_avatar($user_id, $avatar_ext_id)
{
    $avatar_file = $avatar_ext_id ? get_avatar_path($user_id, $avatar_ext_id) : false;
    return ($avatar_file && is_file($avatar_file) && unlink($avatar_file));
}

function get_tracks($type)
{
    static $pattern = '#^a:\d+:{[i:;\d]+}$#';

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
    $tracks = !empty($_COOKIE[$c_name]) ? @unserialize($_COOKIE[$c_name]) : false;
    return $tracks ?: [];
}

/**
 * Returns array with all banned users
 *
 * @param bool $return_as_names
 * @return array
 */
function get_banned_users(bool $return_as_names = false): array
{
    $banned_users = [];

    foreach (DB()->fetch_rowset("SELECT ban_userid FROM " . BB_BANLIST . " WHERE ban_userid != 0") as $user) {
        $banned_users[] = $return_as_names ? get_username($user['ban_userid']) : $user['ban_userid'];
    }

    return $banned_users;
}

function set_tracks($cookie_name, &$tracking_ary, $tracks = null, $val = TIMENOW)
{
    global $tracking_topics, $tracking_forums, $user;

    if (IS_GUEST) {
        return;
    }

    $prev_tracking_ary = $tracking_ary;

    if ($tracks) {
        if (!is_array($tracks)) {
            $tracks = [$tracks => $val];
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

function get_last_read($topic_id = 0, $forum_id = 0)
{
    global $tracking_topics, $tracking_forums, $user;

    $t = $tracking_topics[$topic_id] ?? 0;
    $f = $tracking_forums[$forum_id] ?? 0;
    return max($t, $f, $user->data['user_lastvisit']);
}

function is_unread($ref, $topic_id = 0, $forum_id = 0): bool
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

$bf['forum_perm'] = [
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
];

$bf['user_opt'] = [
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
    'user_empty' => 10, // Запрет на показ рекламы (не используется)
    'dis_topic' => 11, // Запрет на создание новых тем
    'dis_post' => 12, // Запрет на отправку сообщений
    'dis_post_edit' => 13, // Запрет на редактирование сообщений
    'user_dls' => 14, // Скрывать список текущих закачек в профиле
    'user_retracker' => 15, // Добавлять ретрекер к скачиваемым торрентам
];

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

function bf_bit2dec($bf_array_name, $key)
{
    global $bf;
    if (!isset($bf[$bf_array_name][$key])) {
        trigger_error(__FUNCTION__ . ": bitfield '$key' not found", E_USER_ERROR);
    }
    return (1 << $bf[$bf_array_name][$key]);
}

function bf($int, $bf_array_name, $key)
{
    return (bf_bit2dec($bf_array_name, $key) & (int)$int);
}

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
function auth($type, $forum_id, $ug_data, array $f_access = [], $group_perm = UG_PERM_BOTH)
{
    global $lang, $bf, $datastore;

    $is_guest = true;
    $is_admin = false;
    $auth = $auth_fields = $u_access = [];
    $add_auth_type_desc = ($forum_id != AUTH_LIST_ALL);

    // Check forum existence
    if (!forum_exists()) {
        return [];
    }
    if ($add_auth_type_desc && !forum_exists($forum_id)) {
        return [];
    }

    //
    // Get $auth_fields
    //
    if ($type == AUTH_ALL) {
        $auth_fields = array_keys($bf['forum_perm']);
    } elseif ($auth_type = array_search($type, $bf['forum_perm'])) {
        $auth_fields = [$auth_type];
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
        if (!$forums = $datastore->get('cat_forums') and !$datastore->has('cat_forums')) {
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
        $f_access = [$f_access['forum_id'] => $f_access];
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
                $sql = "SELECT aa.forum_id, aa.forum_perm
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

function delta_time($timestamp_1, $timestamp_2 = TIMENOW, $granularity = 'auto')
{
    return $GLOBALS['DeltaTime']->spellDelta($timestamp_1, $timestamp_2, $granularity);
}

function get_select($select, $selected = null, $return_as = 'html', $first_opt = '&raquo;&raquo; Выбрать ')
{
    $select_name = null;
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

function build_select($name, $params, $selected = null, $max_length = HTML_SELECT_MAX_LENGTH, $multiple_size = null, $js = '')
{
    global $html;
    return $html->build_select($name, $params, $selected, $max_length, $multiple_size, $js);
}

function build_checkbox($name, $title, $checked = false, $disabled = false, $class = null, $id = null, $value = 1)
{
    global $html;
    return $html->build_checkbox($name, $title, $checked, $disabled, $class, $id, $value);
}

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
 */
function declension($int, $expressions, $format = '%1$s %2$s')
{
    if (!is_array($expressions)) {
        $expressions = $GLOBALS['lang']['DECLENSION'][strtoupper($expressions)];
    }

    if (count($expressions) < 3) {
        $expressions[2] = $expressions[1];
    }
    $count = (int)$int % 100;

    if ($count >= 5 && $count <= 20) {
        $result = $expressions['2'];
    } else {
        $count %= 10;
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
        $new = null === $value ? '' : $m[1] . urlencode($value);
        $url = str_replace($cur, $new, $url);
    } // добавляем параметр
    elseif (null !== $value) {
        $div = str_contains($url, '?') ? $amp : '?';
        $url = $url . $div . $arg . '=' . urlencode($value);
    }
    return $url . $anchor;
}

/**
 * Returns a size formatted in a more human-friendly format, rounded to the nearest GB, MB, KB..
 */
function humn_size($size, $rounder = null, $min = null, $space = '&nbsp;')
{
    static $sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    static $rounders = [0, 0, 0, 2, 3, 3, 3, 3, 3];

    $size = (float)$size;
    $ext = $sizes[0];
    $rnd = $rounders[0];

    if ($min == 'KB' && $size < 1024) {
        $size /= 1024;
        $ext = 'KB';
        $rounder = 1;
    } else {
        for ($i = 1, $cnt = count($sizes); ($i < $cnt && $size >= 1024); $i++) {
            $size /= 1024;
            $ext = $sizes[$i];
            $rnd = $rounders[$i];
        }
    }
    if (!$rounder) {
        $rounder = $rnd;
    }

    return round($size, $rounder) . $space . $ext;
}

function bt_show_ip($ip, $port = '')
{
    global $bb_cfg;

    if (IS_AM) {
        $ip = \TorrentPier\Helpers\IPHelper::long2ip_extended($ip);
        $ip .= ($port) ? ":$port" : '';
        return $ip;
    }

    return $bb_cfg['bt_show_ip_only_moder'] ? false : \TorrentPier\Helpers\IPHelper::anonymizeIP($ip);
}

function bt_show_port($port)
{
    global $bb_cfg;

    if (IS_AM) {
        return $port;
    }

    return $bb_cfg['bt_show_port_only_moder'] ? false : $port;
}

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

function select_get_val($key, &$val, $options_ary, $default, $num = true)
{
    global $previous_settings;

    if (isset($_REQUEST[$key]) && is_string($_REQUEST[$key])) {
        if (isset($options_ary[$_REQUEST[$key]])) {
            $val = ($num) ? (int)$_REQUEST[$key] : $_REQUEST[$key];
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
 */
function request_var($var_name, $default, $multibyte = false, $cookie = false)
{
    if (!$cookie && isset($_COOKIE[$var_name])) {
        if (!isset($_GET[$var_name], $_POST[$var_name])) {
            return (is_array($default)) ? [] : $default;
        }
        $_REQUEST[$var_name] = $_POST[$var_name] ?? $_GET[$var_name];
    }

    if (!isset($_REQUEST[$var_name]) || (is_array($_REQUEST[$var_name]) && !is_array($default)) || (is_array($default) && !is_array($_REQUEST[$var_name]))) {
        return (is_array($default)) ? [] : $default;
    }

    $var = $_REQUEST[$var_name];
    if (!is_array($default)) {
        $type = gettype($default);
    } else {
        [$key_type, $type] = $default;
        $type = gettype($type);
        $key_type = gettype($key_type);
        if ($type == 'array') {
            reset($default);
            $default = current($default);
            [$sub_key_type, $sub_type] = $default;
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
    }

    $row = DB()->fetch_row("SELECT username FROM " . BB_USERS . " WHERE user_id = '" . DB()->escape($user_id) . "' LIMIT 1");
    return $row['username'];
}

function get_user_id($username)
{
    if (empty($username)) {
        return false;
    }

    if ($row = DB()->fetch_row("SELECT user_id FROM " . BB_USERS . " WHERE username = '" . DB()->escape($username) . "' LIMIT 1")) {
        return $row['user_id'];
    }

    return false;
}

function str_short($text, $max_length, $space = ' ')
{
    if (!empty($max_length) && !empty($text) && (mb_strlen($text, 'UTF-8') > $max_length)) {
        $text = mb_substr($text, 0, $max_length, 'UTF-8');

        if ($last_space_pos = $max_length - (int)strpos(strrev($text), (string)$space)) {
            if ($last_space_pos > round($max_length * 3 / 4)) {
                $last_space_pos--;
                $text = mb_substr($text, 0, $last_space_pos, 'UTF-8');
            }
        }
        $text .= '...';
        $text = preg_replace('!&#?(\w+)?;?(\w{1,5})?\.\.\.$!', '...', $text);
    }

    return $text ?? '';
}

function generate_user_info($row, bool $have_auth = IS_ADMIN): array
{
    global $userdata, $lang, $images, $bb_cfg;

    $from = !empty($row['user_from']) ? render_flag($row['user_from']) : $lang['NOSELECT'];
    $joined = bb_date($row['user_regdate'], 'Y-m-d H:i', false);
    $user_time = !empty($row['user_time']) ? sprintf('%s <span class="signature">(%s)</span>', bb_date($row['user_time']), delta_time($row['user_time'])) : $lang['NOSELECT'];
    $posts = '<a href="search.php?search_author=1&amp;uid=' . $row['user_id'] . '" target="_blank">' . $row['user_posts'] ?: 0 . '</a>';
    $pm = $bb_cfg['text_buttons'] ? '<a class="txtb" href="' . (PM_URL . "?mode=post&amp;" . POST_USERS_URL . "=" . $row['user_id']) . '">' . $lang['SEND_PM_TXTB'] . '</a>' : '<a href="' . (PM_URL . "?mode=post&amp;" . POST_USERS_URL . "=" . $row['user_id']) . '"><img src="' . $images['icon_pm'] . '" alt="' . $lang['SEND_PRIVATE_MESSAGE'] . '" title="' . $lang['SEND_PRIVATE_MESSAGE'] . '" border="0" /></a>';
    $avatar = get_avatar($row['user_id'], $row['avatar_ext_id'], !bf($row['user_opt'], 'user_opt', 'dis_avatar'), 50, 50);

    if (bf($row['user_opt'], 'user_opt', 'user_viewemail') || $have_auth || ($row['user_id'] == $userdata['user_id'])) {
        $email_uri = ($bb_cfg['board_email_form']) ? ("profile.php?mode=email&amp;" . POST_USERS_URL . "=" . $row['user_id']) : 'mailto:' . $row['user_email'];
        $email = '<a class="editable" href="' . $email_uri . '">' . $row['user_email'] . '</a>';
    } else {
        $email = $lang['HIDDEN_USER'];
    }

    if ($row['user_website']) {
        $www = $bb_cfg['text_buttons'] ? '<a class="txtb" href="' . $row['user_website'] . '"  target="_userwww">' . $lang['VISIT_WEBSITE_TXTB'] . '</a>' : '<a class="txtb" href="' . $row['user_website'] . '" target="_userwww"><img src="' . $images['icon_www'] . '" alt="' . $lang['VISIT_WEBSITE'] . '" title="' . $lang['VISIT_WEBSITE'] . '" border="0" /></a>';
    } else {
        $www = $lang['NOSELECT'];
    }

    return [
        'from' => $from,
        'joined' => $joined,
        'joined_raw' => $row['user_regdate'],
        'posts' => $posts,
        'pm' => $pm,
        'avatar' => $avatar,
        'user_time' => $user_time,
        'user_time_raw' => ($row['user_time'] ?? ''),
        'email' => $email,
        'www' => $www
    ];
}

function get_bt_userdata($user_id)
{
    if (!$btu = CACHE('bb_cache')->get('btu_' . $user_id)) {
        $btu = DB()->fetch_row("
			SELECT bt.*, SUM(tr.speed_up) AS speed_up, SUM(tr.speed_down) AS speed_down
			FROM      " . BB_BT_USERS . " bt
			LEFT JOIN " . BB_BT_TRACKER . " tr ON (bt.user_id = tr.user_id)
			WHERE bt.user_id = " . (int)$user_id . "
			GROUP BY bt.user_id
			LIMIT 1
		");

        CACHE('bb_cache')->set('btu_' . $user_id, $btu, 300);
    }

    return $btu;
}

function get_bt_ratio($btu): ?float
{
    return
        (!empty($btu['u_down_total']) && $btu['u_down_total'] > MIN_DL_FOR_RATIO)
            ? round((($btu['u_up_total'] + $btu['u_up_release'] + $btu['u_up_bonus']) / $btu['u_down_total']), 2)
            : null;
}

function show_bt_userdata($user_id): void
{
    global $template;

    if (!$btu = get_bt_userdata($user_id)) {
        return;
    }

    $template->assign_vars([
        'SHOW_BT_USERDATA' => true,
        'UP_TOTAL' => humn_size($btu['u_up_total']),
        'UP_BONUS' => humn_size($btu['u_up_bonus']),
        'RELEASED' => humn_size($btu['u_up_release']),
        'DOWN_TOTAL' => humn_size($btu['u_down_total']),
        'DOWN_TOTAL_BYTES' => $btu['u_down_total'],
        'USER_RATIO' => get_bt_ratio($btu),
        'MIN_DL_FOR_RATIO' => humn_size(MIN_DL_FOR_RATIO),
        'MIN_DL_BYTES' => MIN_DL_FOR_RATIO,
        'AUTH_KEY' => $btu['auth_key'],

        'TD_DL' => humn_size($btu['down_today']),
        'TD_UL' => humn_size($btu['up_today']),
        'TD_REL' => humn_size($btu['up_release_today']),
        'TD_BONUS' => humn_size($btu['up_bonus_today']),
        'TD_POINTS' => $btu['auth_key'] ? $btu['points_today'] : '0.00',

        'YS_DL' => humn_size($btu['down_yesterday']),
        'YS_UL' => humn_size($btu['up_yesterday']),
        'YS_REL' => humn_size($btu['up_release_yesterday']),
        'YS_BONUS' => humn_size($btu['up_bonus_yesterday']),
        'YS_POINTS' => $btu['auth_key'] ? $btu['points_yesterday'] : '0.00',

        'SPEED_UP' => humn_size($btu['speed_up'], 0, 'KB') . '/s',
        'SPEED_DOWN' => humn_size($btu['speed_down'], 0, 'KB') . '/s',
    ]);
}

function get_attachments_dir($cfg = null)
{
    if (!$cfg and !$cfg = $GLOBALS['attach_config']) {
        $cfg = bb_get_config(BB_ATTACH_CONFIG, true, false);
    }

    if ($cfg['upload_dir'][0] == '/' || ($cfg['upload_dir'][0] != '/' && $cfg['upload_dir'][1] == ':')) {
        return $cfg['upload_dir'];
    }

    return BB_ROOT . $cfg['upload_dir'];
}

function bb_get_config($table, $from_db = false, $update_cache = true)
{
    if ($from_db or !$cfg = CACHE('bb_config')->get("config_{$table}")) {
        $cfg = [];
        foreach (DB()->fetch_rowset("SELECT * FROM $table") as $row) {
            $cfg[$row['config_name']] = $row['config_value'];
        }
        if ($update_cache) {
            CACHE('bb_config')->set("config_{$table}", $cfg);
        }
    }
    return $cfg;
}

function bb_update_config($params, $table = BB_CONFIG)
{
    $updates = [];
    foreach ($params as $name => $val) {
        $updates[] = [
            'config_name' => $name,
            'config_value' => $val
        ];
    }
    $updates = DB()->build_array('MULTI_INSERT', $updates);

    DB()->query("REPLACE INTO $table $updates");

    // Update cache
    bb_get_config($table, true, true);
}

function clean_username($username)
{
    $username = mb_substr(htmlspecialchars(str_replace("\'", "'", trim($username))), 0, 25, 'UTF-8');
    $username = rtrim($username, "\\");
    $username = str_replace("'", "\'", $username);

    return $username;
}

/**
 * Get Userdata
 *
 * @param int|string $u
 * @param bool $is_name
 * @param bool $allow_guest
 * @return mixed
 */
function get_userdata(int|string $u, bool $is_name = false, bool $allow_guest = false, bool $profile_view = false)
{
    if (empty($u)) {
        return false;
    }

    if (!$is_name) {
        $u = (int)$u;
        if ($u === GUEST_UID && $allow_guest) {
            if ($u_data = CACHE('bb_cache')->get('guest_userdata')) {
                return $u_data;
            }
        }

        $where_sql = "WHERE user_id = " . $u;
    } else {
        $where_sql = "WHERE username = '" . DB()->escape(clean_username($u)) . "'";
    }

    if ($profile_view) {
        $where_sql = "WHERE user_id = " . (int)$u . " OR username = '" . DB()->escape(clean_username($u)) . "'";
    }

    $exclude_anon_sql = (!$allow_guest) ? "AND user_id != " . GUEST_UID : '';
    $sql = "SELECT * FROM " . BB_USERS . " $where_sql $exclude_anon_sql LIMIT 1";

    if (!$u_data = DB()->fetch_row($sql)) {
        return false;
    }

    if ((int)$u_data['user_id'] === GUEST_UID) {
        CACHE('bb_cache')->set('guest_userdata', $u_data);
    }

    return $u_data;
}

function make_jumpbox(): void
{
    global $datastore, $template, $bb_cfg;

    if (!$bb_cfg['show_jumpbox']) {
        return;
    }

    if (!$jumpbox = $datastore->get('jumpbox') and !$datastore->has('jumpbox')) {
        $datastore->update('jumpbox');
        $jumpbox = $datastore->get('jumpbox');
    }

    $template->assign_vars(['JUMPBOX' => IS_GUEST ? DB()->escape($jumpbox['guest']) : DB()->escape($jumpbox['user'])]);
}

// $mode: array(not_auth_forum1,not_auth_forum2,..) or (string) 'mode'
function get_forum_select($mode = 'guest', $name = POST_FORUM_URL, $selected = null, $max_length = HTML_SELECT_MAX_LENGTH, $multiple_size = null, $js = '', $all_forums_option = null)
{
    global $lang, $datastore;

    if (is_array($mode)) {
        $not_auth_forums_fary = array_flip($mode);
        $mode = 'not_auth_forums';
    }
    if (null === $max_length) {
        $max_length = HTML_SELECT_MAX_LENGTH;
    }
    $select = null === $all_forums_option ? [] : [$lang['ALL_AVAILABLE'] => $all_forums_option];
    if (!$forums = $datastore->get('cat_forums') and !$datastore->has('cat_forums')) {
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

function setup_style()
{
    global $bb_cfg, $template, $userdata;

    // AdminCP works only with default template
    $tpl_dir_name = defined('IN_ADMIN') ? 'default' : basename($bb_cfg['tpl_name']);
    $stylesheet = defined('IN_ADMIN') ? 'main.css' : basename($bb_cfg['stylesheet']);

    if (!IS_GUEST && !empty($userdata['tpl_name'])) {
        foreach ($bb_cfg['templates'] as $folder => $name) {
            if ($userdata['tpl_name'] == $folder) {
                $tpl_dir_name = basename($userdata['tpl_name']);
            }
        }
    }

    $template = new TorrentPier\Legacy\Template(TEMPLATES_DIR . '/' . $tpl_dir_name);
    $css_dir = 'styles/' . basename(TEMPLATES_DIR) . '/' . $tpl_dir_name . '/css/';

    $template->assign_vars([
        'SPACER' => make_url('styles/images/spacer.gif'),
        'STYLESHEET' => make_url($css_dir . $stylesheet),
        'EXT_LINK_NEW_WIN' => $bb_cfg['ext_link_new_win'],
        'TPL_DIR' => make_url($css_dir),
        'SITE_URL' => make_url('/')
    ]);

    require_once TEMPLATES_DIR . '/' . $tpl_dir_name . '/tpl_config.php';

    return ['template_name' => $tpl_dir_name];
}

// Create date / time with format and friendly date
function bb_date($gmepoch, $format = false, $friendly_date = true)
{
    global $bb_cfg, $lang, $userdata;

    $gmepoch = (int)$gmepoch;

    if (!$format) {
        $format = $bb_cfg['default_dateformat'];
    }
    if (empty($lang)) {
        require_once($bb_cfg['default_lang_dir'] . 'main.php');
    }

    if (!defined('IS_GUEST') || IS_GUEST) {
        $tz = $bb_cfg['board_timezone'];
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

    return ($bb_cfg['translate_dates']) ? strtr(strtoupper($date), $lang['DATETIME']) : $date;
}

/**
 * Get user's torrent client string
 *
 * @param string $peer_id
 * @return string
 */
function get_user_torrent_client(string $peer_id): string
{
    global $bb_cfg;
    static $iconExtension = '.png';

    $bestMatch = null;
    $bestMatchLength = 0;
    foreach ($bb_cfg['tor_clients'] as $key => $clientName) {
        if (str_starts_with($peer_id, $key) !== false && strlen($key) > $bestMatchLength) {
            $bestMatch = $clientName;
            $bestMatchLength = strlen($key);
        }
    }

    $clientIconPath = BB_ROOT . 'styles/images/clients/' . $bestMatch . $iconExtension;
    if (!empty($bestMatch) && is_file($clientIconPath)) {
        return '<img class="client_icon" src="' . $clientIconPath . '" alt="' . $bestMatch . '" title="' . $peer_id . '">';
    }

    return $peer_id;
}

/**
 * Returns country flag by country code
 *
 * @param string $code
 * @param bool $showName
 * @return string
 */
function render_flag(string $code, bool $showName = true): string
{
    global $lang;
    static $iconExtension = '.svg';

    if (isset($lang['COUNTRIES'][$code])) {
        if ($code === '0') {
            return ''; // No selected
        } else {
            $flagIconPath = BB_ROOT . 'styles/images/flags/' . $code . $iconExtension;
            if (is_file($flagIconPath)) {
                $countryName = $showName ? '&nbsp;' . str_short($lang['COUNTRIES'][$code], 20) : '';
                return '<span title="' . $lang['COUNTRIES'][$code] . '"><img src="' . $flagIconPath . '" class="poster-flag" alt="' . $code . '">' . $countryName . '</span>';
            }
        }
    }

    return $code;
}

function birthday_age($date)
{
    global $bb_cfg;
    if (!$date) {
        return '';
    }

    $tz = TIMENOW + (3600 * $bb_cfg['board_timezone']);
    return delta_time(strtotime($date, $tz));
}

//
// Pagination routine, generates
// page number sequence
//
function generate_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = true)
{
    global $lang, $template;

    $begin_end = 3;
    $from_middle = 1;

    $total_pages = ceil($num_items / $per_page);
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

    $pagination = false;
    if ($page_string && $total_pages > 1) {
        $pagination = '<a class="menu-root" href="#pg-jump">' . $lang['GOTO_PAGE'] . '</a> :&nbsp;&nbsp;' . $page_string;
        $pagination = str_replace('&amp;start=0', '', $pagination);
    }

    $template->assign_vars([
        'PAGINATION' => $pagination,
        'PAGE_NUMBER' => sprintf($lang['PAGE_OF'], (floor($start_item / $per_page) + 1), ceil($num_items / $per_page)),
        'PG_BASE_URL' => $base_url,
        'PG_PER_PAGE' => $per_page
    ]);

    return $pagination;
}

//
// This does exactly what preg_quote() does in PHP 4-ish
// If you just need the 1-parameter preg_quote call, then don't bother using this.
//
function bb_preg_quote($str, $delimiter)
{
    $text = preg_quote($str);
    $text = str_replace($delimiter, '\\' . $delimiter, $text);

    return $text;
}

function bb_die($msg_text, $status_code = null)
{
    global $ajax, $bb_cfg, $lang, $template, $theme, $userdata, $user;

    if (isset($status_code)) {
        http_response_code($status_code);
    }

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
        require($bb_cfg['default_lang_dir'] . 'main.php');
    }

    // If empty session
    if (empty($userdata)) {
        $userdata = $user->session_start();
    }

    // If the header hasn't been output then do it
    if (!defined('PAGE_HEADER_SENT')) {
        if (empty($template)) {
            $template = new TorrentPier\Legacy\Template(BB_ROOT . "templates/{$bb_cfg['tpl_name']}");
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

    $template->assign_vars([
        'TPL_BB_DIE' => true,
        'MESSAGE_TEXT' => $msg_text
    ]);

    $template->set_filenames(['bb_die' => 'common.tpl']);
    $template->pparse('bb_die');

    require(PAGE_FOOTER);

    exit;
}

function bb_simple_die($txt, $status_code = null)
{
    global $bb_cfg;

    header('Content-Type: text/plain; charset=' . $bb_cfg['charset']);

    if (isset($status_code)) {
        http_response_code($status_code);
    }

    if (!empty($_COOKIE['explain'])) {
        bb_die("bb_simple_die:<br /><br />$txt");
    }

    die($txt);
}

function login_redirect($url = '')
{
    redirect(LOGIN_URL . '?redirect=' . (($url) ?: ($_SERVER['REQUEST_URI'] ?? '/')));
}

function meta_refresh($url, $time = 5)
{
    global $template;

    $template->assign_var('META', '<meta http-equiv="refresh" content="' . $time . ';url=' . $url . '" />');
}

function redirect($url)
{
    global $bb_cfg;

    if (headers_sent($filename, $linenum)) {
        trigger_error("Headers already sent in $filename($linenum)", E_USER_ERROR);
    }

    if (str_contains(urldecode($url), "\n") || str_contains(urldecode($url), "\r") || str_contains(urldecode($url), ';url')) {
        bb_die('Tried to redirect to potentially insecure url');
    }

    $url = trim($url);
    $server_protocol = ($bb_cfg['cookie_secure']) ? 'https://' : 'http://';

    $server_name = preg_replace('#^\/?(.*?)\/?$#', '\1', trim($bb_cfg['server_name']));
    $server_port = ($bb_cfg['server_port'] <> 80) ? ':' . trim($bb_cfg['server_port']) : '';
    $script_name = preg_replace('#^\/?(.*?)\/?$#', '\1', trim($bb_cfg['script_path']));

    if ($script_name) {
        $script_name = "/$script_name";
        $url = preg_replace("#^$script_name#", '', $url);
    }

    $redirect_url = $server_protocol . $server_name . $server_port . $script_name . preg_replace('#^\/?(.*?)\/?$#', '/\1', $url);

    // Behave as per HTTP/1.1 spec for others
    header('Location: ' . $redirect_url, response_code: 301);
    exit;
}

// build a list of the sortable fields or return field name
function get_forum_display_sort_option($selected_row = 0, $action = 'list', $list = 'sort')
{
    global $lang;

    $forum_display_sort = [
        'lang_key' => ['LASTPOST', 'SORT_TOPIC_TITLE', 'SORT_TIME'],
        'fields' => ['t.topic_last_post_time', 't.topic_title', 't.topic_time']
    ];
    $forum_display_order = [
        'lang_key' => ['DESC', 'ASC'],
        'fields' => ['DESC', 'ASC']
    ];

    // get the good list
    $list_name = 'forum_display_' . $list;
    $listrow = ${$list_name};

    // init the result
    $res = '';
    if ($selected_row > count($listrow['lang_key'])) {
        $selected_row = 0;
    }

    // build list
    if ($action == 'list') {
        foreach ($listrow['lang_key'] as $i => $iValue) {
            $selected = ($i == $selected_row) ? ' selected' : '';
            $l_value = $lang[$listrow['lang_key'][$i]] ?? $iValue;
            $res .= '<option value="' . $i . '"' . $selected . '>' . $l_value . '</option>';
        }
    } else {
        // field
        $res = $listrow['fields'][$selected_row];
    }
    return $res;
}

function topic_attachment_image($switch_attachment)
{
    global $is_auth;

    if (!$switch_attachment || !($is_auth['auth_download'] && $is_auth['auth_view'])) {
        return '';
    }
    return '<img src="styles/images/icon_clip.gif" alt="" border="0" /> ';
}

function clear_dl_list($topics_csv)
{
    DB()->query("DELETE FROM " . BB_BT_DLSTATUS . " WHERE topic_id IN($topics_csv)");
    DB()->query("DELETE FROM " . BB_BT_DLSTATUS_SNAP . " WHERE topic_id IN($topics_csv)");
}

// $ids - array(id1,id2,..) or (string) id
function get_id_csv($ids)
{
    $ids = array_values((array)$ids);
    array_deep($ids, 'intval', 'one-dimensional');
    return (string)implode(',', $ids);
}

// $ids - array(id1,id2,..) or (string) id1,id2,..
function get_id_ary($ids)
{
    $ids = is_string($ids) ? explode(',', $ids) : array_values((array)$ids);
    array_deep($ids, 'intval', 'one-dimensional');
    return (array)$ids;
}

function get_topic_title($topic_id)
{
    $row = DB()->fetch_row("
		SELECT topic_title FROM " . BB_TOPICS . " WHERE topic_id = " . (int)$topic_id . "
	");
    return $row['topic_title'];
}

function forum_exists($forum_id = null): bool
{
    if (!isset($forum_id)) {
        return (bool)DB()->fetch_row("SELECT * FROM " . BB_FORUMS . " LIMIT 1");
    }

    return (bool)DB()->fetch_row("SELECT forum_id FROM " . BB_FORUMS . " WHERE forum_id = $forum_id LIMIT 1");
}

function cat_exists($cat_id): bool
{
    return (bool)DB()->fetch_row("SELECT cat_id FROM " . BB_CATEGORIES . " WHERE cat_id = $cat_id LIMIT 1");
}

function get_topic_icon($topic, $is_unread = null)
{
    global $bb_cfg, $images;

    $t_hot = ($topic['topic_replies'] >= $bb_cfg['hot_threshold']);
    $is_unread ??= is_unread($topic['topic_last_post_time'], $topic['topic_id'], $topic['forum_id']);

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
        } elseif ($topic['topic_dl_type'] == TOPIC_DL_TYPE_DL) {
            $folder = ($t_hot) ? $images['folder_dl_hot'] : $images['folder_dl'];
            $folder_new = ($t_hot) ? $images['folder_dl_hot_new'] : $images['folder_dl_new'];
        }

        $folder_image = ($is_unread) ? $folder_new : $folder;
    }

    return $folder_image;
}

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

function print_confirmation($tpl_vars)
{
    global $template, $lang;

    $template->assign_vars([
        'TPL_CONFIRM' => true,
        'CONFIRM_TITLE' => $lang['CONFIRM'],
        'FORM_METHOD' => 'post'
    ]);

    if (!isset($tpl_vars['QUESTION'])) {
        $tpl_vars['QUESTION'] = $lang['QUESTION'];
    }

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

    $template->set_filenames(['body' => $tpl]);
    $template->pparse('body');

    if ($mode !== 'no_footer') {
        require(PAGE_FOOTER);
    }
}

function caching_output($enabled, $mode, $cache_var_name, $ttl = 300)
{
    if (!$enabled || !CACHE('bb_cache')->used) {
        return;
    }

    if ($mode == 'send') {
        if ($cached_contents = CACHE('bb_cache')->get($cache_var_name)) {
            exit($cached_contents);
        }
    } elseif ($mode == 'store') {
        if ($output = ob_get_contents()) {
            CACHE('bb_cache')->set($cache_var_name, $output, $ttl);
        }
    }
}

function clean_title($str, $replace_underscore = false)
{
    $str = ($replace_underscore) ? str_replace('_', ' ', $str) : $str;
    $str = htmlCHR(str_compact($str));
    return $str;
}

function clean_text_match($text, $ltrim_star = true, $die_if_empty = false)
{
    global $bb_cfg, $lang;

    $text = str_compact($text);
    $ltrim_chars = ($ltrim_star) ? ' *-!' : ' ';
    $wrap_with_quotes = preg_match('#^"[^"]+"$#', $text);

    $text = ' ' . str_compact(ltrim($text, $ltrim_chars)) . ' ';

    if ($bb_cfg['search_engine_type'] == 'sphinx') {
        $text = preg_replace('#(?<=\S)\-#u', ' ', $text);                 // "1-2-3" -> "1 2 3"
        $text = preg_replace('#[^0-9a-zA-Zа-яА-ЯёЁ\-_*|]#u', ' ', $text); // допустимые символы (кроме " которые отдельно)
        $text = str_replace(['-', '*'], [' -', '* '], $text);                                // только в начале / конце слова
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

function init_sphinx()
{
    global $sphinx;

    if (!isset($sphinx)) {
        $sphinx = \Sphinx\SphinxClient::create();

        $sphinx->setConnectTimeout(5);
        $sphinx->setRankingMode($sphinx::SPH_RANK_NONE);
        $sphinx->setMatchMode($sphinx::SPH_MATCH_BOOLEAN);
    }

    return $sphinx;
}

function log_sphinx_error($err_type, $err_msg, $query = '')
{
    $ignore_err_txt = [
        'negation on top level',
        'Query word length is less than min prefix length'
    ];
    if (!count($ignore_err_txt) || !preg_match('#' . implode('|', $ignore_err_txt) . '#i', $err_msg)) {
        $orig_query = strtr($_REQUEST['nm'], ["\n" => '\n']);
        bb_log(date('m-d H:i:s') . " | $err_type | $err_msg | $orig_query | $query" . LOG_LF, 'sphinx_error');
    }
}

function get_title_match_topics($title_match_sql, array $forum_ids = [])
{
    global $bb_cfg, $sphinx, $userdata, $title_match, $lang;

    $where_ids = [];
    if ($forum_ids) {
        $forum_ids = array_diff($forum_ids, [0 => 0]);
    }
    $title_match_sql = encode_text_match($title_match_sql);

    if ($bb_cfg['search_engine_type'] == 'sphinx') {
        $sphinx = init_sphinx();

        $where = $title_match ? 'topics' : 'posts';

        $sphinx->setServer($bb_cfg['sphinx_topic_titles_host'], $bb_cfg['sphinx_topic_titles_port']);
        if ($forum_ids) {
            $sphinx->setFilter('forum_id', $forum_ids, false);
        }
        if (preg_match('#^"[^"]+"$#u', $title_match_sql)) {
            $sphinx->setMatchMode($sphinx::SPH_MATCH_PHRASE);
        }
        if ($result = $sphinx->query($title_match_sql, $where, $userdata['username'] . ' (' . CLIENT_IP . ')')) {
            if (!empty($result['matches'])) {
                $where_ids = array_keys($result['matches']);
            }
        } elseif ($error = $sphinx->getLastError()) {
            if (strpos($error, 'errno=110')) {
                bb_die($lang['SEARCH_ERROR']);
            }
            log_sphinx_error('ERR', $error, $title_match_sql);
        }
        if ($warning = $sphinx->getLastWarning()) {
            log_sphinx_error('wrn', $warning, $title_match_sql);
        }
    } elseif ($bb_cfg['search_engine_type'] == 'mysql') {
        $where_forum = ($forum_ids) ? "AND forum_id IN(" . implode(',', $forum_ids) . ")" : '';
        $search_bool_mode = ($bb_cfg['allow_search_in_bool_mode']) ? ' IN BOOLEAN MODE' : '';

        if ($title_match) {
            $where_id = 'topic_id';
            $sql = "SELECT topic_id FROM " . BB_TOPICS . "
					WHERE MATCH (topic_title) AGAINST ('$title_match_sql'$search_bool_mode)
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
    } else {
        bb_die($lang['SEARCH_OFF']);
    }

    return $where_ids;
}

// для более корректного поиска по словам содержащим одиночную кавычку
function encode_text_match($txt)
{
    return str_replace("'", '&#039;', $txt);
}

function decode_text_match($txt)
{
    return str_replace('&#039;', "'", $txt);
}

/**
 * Create magnet link
 *
 * @param string $infohash
 * @param string $infohash_v2
 * @param string $auth_key
 * @param string $name
 *
 * @return string
 */
function create_magnet(string $infohash, string $infohash_v2, string $auth_key, string $name): string
{
    global $bb_cfg, $images, $lang;

    if (!$bb_cfg['magnet_links_enabled']) {
        return false;
    }

    // Only for registered users
    if (IS_GUEST && $bb_cfg['bt_tor_browse_only_reg']) {
        return false;
    }

    $v1_support = !empty($infohash);
    $v2_support = !empty($infohash_v2);

    $magnet = 'magnet:?';

    if ($v1_support) {
        $magnet .= 'xt=urn:btih:' . bin2hex($infohash);
    }

    if ($v2_support) {
        if ($v1_support) {
            $magnet .= '&';
        }
        $magnet .= 'xt=urn:btmh:1220' . bin2hex($infohash_v2);
    }

    return '<a title="' . ($v2_support ? $lang['MAGNET_v2'] : $lang['MAGNET']) . '" href="' . $magnet . '&tr=' . urlencode($bb_cfg['bt_announce_url'] . "?{$bb_cfg['passkey_key']}=$auth_key") . '&dn=' . urlencode($name) . '"><img src="' . ($v2_support ? $images['icon_magnet_v2'] : $images['icon_magnet']) . '" width="12" height="12" border="0" /></a>';
}

function set_die_append_msg($forum_id = null, $topic_id = null, $group_id = null)
{
    global $lang, $template;

    $msg = '';
    $msg .= $topic_id ? '<p class="mrg_10"><a href="' . TOPIC_URL . $topic_id . '">' . $lang['TOPIC_RETURN'] . '</a></p>' : '';
    $msg .= $forum_id ? '<p class="mrg_10"><a href="' . FORUM_URL . $forum_id . '">' . $lang['FORUM_RETURN'] . '</a></p>' : '';
    $msg .= $group_id ? '<p class="mrg_10"><a href="' . GROUP_URL . $group_id . '">' . $lang['GROUP_RETURN'] . '</a></p>' : '';
    $msg .= '<p class="mrg_10"><a href="index.php">' . $lang['INDEX_RETURN'] . '</a></p>';
    $template->assign_var('BB_DIE_APPEND_MSG', $msg);
}

function set_pr_die_append_msg($pr_uid)
{
    global $lang, $template;

    $template->assign_var('BB_DIE_APPEND_MSG', '
		<a href="' . PROFILE_URL . $pr_uid . '" onclick="return post2url(this.href, {after_edit: 1});">' . $lang['PROFILE_RETURN'] . '</a>
		<br /><br />
		<a href="profile.php?mode=editprofile' . (IS_ADMIN ? "&amp;" . POST_USERS_URL . "=$pr_uid" : '') . '" onclick="return post2url(this.href, {after_edit: 1});">' . $lang['PROFILE_EDIT_RETURN'] . '</a>
		<br /><br />
		<a href="index.php">' . $lang['INDEX_RETURN'] . '</a>
	');
}

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
 * Generates link to profile
 *
 * @param array $data
 * @param bool $target_blank
 * @param bool $no_link
 * @return string
 */
function profile_url(array $data, bool $target_blank = false, bool $no_link = false): string
{
    global $bb_cfg, $lang, $datastore;

    if (!$ranks = $datastore->get('ranks') and !$datastore->has('ranks')) {
        $datastore->update('ranks');
        $ranks = $datastore->get('ranks');
    }

    $username = !empty($data['username']) ? $data['username'] : $lang['GUEST'];
    $user_id = !empty($data['user_id']) ? (int)$data['user_id'] : GUEST_UID;
    $user_rank = !empty($data['user_rank']) ? $data['user_rank'] : 0;

    $title = '';
    $style = 'colorUser';
    if (isset($ranks[$user_rank])) {
        $title = $ranks[$user_rank]['rank_title'];
        if ($bb_cfg['color_nick']) {
            $style = $ranks[$user_rank]['rank_style'];
        }
    }

    if (empty($title)) {
        $title = match ($user_id) {
            GUEST_UID => $lang['GUEST'],
            BOT_UID => $username,
            default => $lang['USER'],
        };
    }

    $profile = '<span title="' . $title . '" class="' . $style . '">' . $username . '</span>';
    if (!in_array($user_id, explode(',', EXCLUDED_USERS)) && !$no_link) {
        $target_blank = $target_blank ? ' target="_blank" ' : '';
        $profile = '<a ' . $target_blank . ' href="' . make_url(PROFILE_URL . $user_id) . '">' . $profile . '</a>';
    }

    if (getBanInfo($user_id)) {
        return '<s>' . $profile . '</s>';
    }

    return $profile;
}

function get_avatar($user_id, $ext_id, $allow_avatar = true, $height = '', $width = '')
{
    global $bb_cfg;

    $height = $height ? 'height="' . $height . '"' : '';
    $width = $width ? 'width="' . $width . '"' : '';

    $user_avatar = '<img src="' . make_url($bb_cfg['avatars']['display_path'] . $bb_cfg['avatars']['no_avatar']) . '" alt="' . $user_id . '" ' . $height . ' ' . $width . ' />';

    if ($user_id == BOT_UID && $bb_cfg['avatars']['bot_avatar']) {
        $user_avatar = '<img src="' . make_url($bb_cfg['avatars']['display_path'] . $bb_cfg['avatars']['bot_avatar']) . '" alt="' . $user_id . '" ' . $height . ' ' . $width . ' />';
    } elseif ($allow_avatar && $ext_id) {
        if (is_file(get_avatar_path($user_id, $ext_id))) {
            $user_avatar = '<img src="' . make_url(get_avatar_path($user_id, $ext_id, $bb_cfg['avatars']['display_path'])) . '" alt="' . $user_id . '" ' . $height . ' ' . $width . ' />';
        }
    }

    return $user_avatar;
}

/**
 * Returns gender image
 *
 * @param int $gender
 * @return string|null
 */
function genderImage(int $gender): ?string
{
    global $bb_cfg, $lang, $images;

    if (!$bb_cfg['gender']) {
        return false;
    }

    return match ($gender) {
        MALE => '<img src="' . $images['icon_male'] . '" alt="' . $lang['GENDER_SELECT'][MALE] . '" title="' . $lang['GENDER_SELECT'][MALE] . '" border="0" />',
        FEMALE => '<img src="' . $images['icon_female'] . '" alt="' . $lang['GENDER_SELECT'][FEMALE] . '" title="' . $lang['GENDER_SELECT'][FEMALE] . '" border="0" />',
        default => '<img src="' . $images['icon_nogender'] . '" alt="' . $lang['GENDER_SELECT'][NOGENDER] . '" title="' . $lang['GENDER_SELECT'][NOGENDER] . '" border="0" />',
    };
}

function is_gold($type): string
{
    global $lang, $bb_cfg, $images;

    $type = (int)$type;
    $is_gold = '';

    if (!$bb_cfg['tracker']['gold_silver_enabled']) {
        return $is_gold;
    }

    switch ($type) {
        case TOR_TYPE_GOLD:
            $is_gold = '<img width="16" height="15" src="' . $images['icon_tor_gold'] . '" alt="' . $lang['GOLD'] . '" title="' . $lang['GOLD'] . '" />&nbsp;';
            break;
        case TOR_TYPE_SILVER:
            $is_gold = '<img width="16" height="15" src="' . $images['icon_tor_silver'] . '" alt="' . $lang['SILVER'] . '" title="' . $lang['SILVER'] . '" />&nbsp;';
            break;
        default:
            break;
    }

    return $is_gold;
}

function update_atom($type, $id)
{
    switch ($type) {
        case 'user':
            \TorrentPier\Legacy\Atom::update_user_feed($id, get_username($id));
            break;

        case 'topic':
            $topic_poster = (int)DB()->fetch_row("SELECT topic_poster FROM " . BB_TOPICS . " WHERE topic_id = $id LIMIT 1", 'topic_poster');
            \TorrentPier\Legacy\Atom::update_user_feed($topic_poster, get_username($topic_poster));
            break;
    }
}

function hash_search($hash)
{
    global $lang;

    $hash = htmlCHR(trim($hash));
    $info_hash_where = null;

    if (!isset($hash) || !ctype_xdigit($hash)) {
        bb_die(sprintf($lang['HASH_INVALID'], $hash));
    }

    $info_hash = DB()->escape(pack('H*', $hash));

    // Check info_hash version
    if (mb_strlen($hash, 'UTF-8') == 40) {
        $info_hash_where = "WHERE info_hash = '$info_hash'";
    } elseif (mb_strlen($hash, 'UTF-8') == 64) {
        $info_hash_where = "WHERE info_hash_v2 = '$info_hash'";
    } else {
        bb_die(sprintf($lang['HASH_INVALID'], $hash));
    }

    if ($row = DB()->fetch_row("SELECT topic_id FROM " . BB_BT_TORRENTS . " $info_hash_where")) {
        redirect(TOPIC_URL . $row['topic_id']);
    } else {
        bb_die(sprintf($lang['HASH_NOT_FOUND'], $hash));
    }
}

/**
 * Функция для получения и проверки правильности ответа от Google ReCaptcha.
 *
 * @param $mode
 * @param string $callback
 *
 * @return bool|string
 */
function bb_captcha($mode, $callback = '')
{
    global $bb_cfg, $lang;

    $secret = $bb_cfg['captcha']['secret_key'];
    $public = $bb_cfg['captcha']['public_key'];
    $cp_theme = $bb_cfg['captcha']['theme'] ?? 'light';

    if (!$bb_cfg['captcha']['disabled'] && (!$public || !$secret)) {
        bb_die($lang['CAPTCHA_SETTINGS']);
    }

    $reCaptcha = new \ReCaptcha\ReCaptcha($secret);

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
            $resp = $reCaptcha->verify(
                request_var('g-recaptcha-response', ''),
                $_SERVER["REMOTE_ADDR"]
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

function clean_tor_dirname($dirname)
{
    return str_replace(['[', ']', '<', '>', "'"], ['&#91;', '&#93;', '&lt;', '&gt;', '&#039;'], $dirname);
}

/**
 * Get birthday icon
 *
 * @param $user_birthday
 * @param $user_id
 * @return string
 */
function user_birthday_icon($user_birthday, $user_id): string
{
    global $bb_cfg, $images, $lang;

    $current_date = bb_date(TIMENOW, 'md', false);
    $user_birthday = ($user_id != GUEST_UID && !empty($user_birthday) && $user_birthday != '1900-01-01')
        ? bb_date(strtotime($user_birthday), 'md', false) : false;

    return ($bb_cfg['birthday_enabled'] && $current_date == $user_birthday) ? '<img src="' . $images['icon_birthday'] . '" alt="' . $lang['HAPPY_BIRTHDAY'] . '" title="' . $lang['HAPPY_BIRTHDAY'] . '" border="0" />' : '';
}

/**
 * Returns information about user ban
 *
 * @param int|null $userId
 * @return array|null
 */
function getBanInfo(int $userId = null): ?array
{
    global $datastore;

    // Get bans info from datastore
    if (!$bans = $datastore->get('ban_list') and !$datastore->has('ban_list')) {
        $datastore->update('ban_list');
        $bans = $datastore->get('ban_list');
    }

    if (!isset($userId)) {
        return $bans;
    }

    return $bans[$userId] ?? [];
}

/**
 * Read updater file
 *
 * @return array|bool
 */
function readUpdaterFile(): array|bool
{
    if (!is_file(UPDATER_FILE)) {
        return false;
    }

    $decodedFile = json_decode(file_get_contents(UPDATER_FILE), true);
    return [
        'previous_version' => $decodedFile['previous_version'],
        'latest_version' => $decodedFile['latest_version']
    ];
}

/**
 * IP Geolocation API
 *
 * @param string $ipAddress
 * @param int $port
 * @return array
 */
function infoByIP(string $ipAddress, int $port = 0): array
{
    if (!$data = CACHE('bb_ip2countries')->get($ipAddress . '_' . $port)) {
        $data = [];
        $response = file_get_contents(API_IP_URL . $ipAddress);
        $json = json_decode($response, true);
        if (is_array($json) && !empty($json)) {
            $data = [
                'ipVersion' => $json['ipVersion'],
                'countryCode' => $json['countryCode'],
                'continent' => $json['continent'],
                'continentCode' => $json['continentCode']
            ];
            CACHE('bb_ip2countries')->set($ipAddress . '_' . $port, $data, 1200);
        }
    }

    return $data;
}
