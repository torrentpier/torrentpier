<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

use App\Models\User;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use TorrentPier\Data\Countries;
use TorrentPier\Data\FileExtensions;
use TorrentPier\Data\TorrentClients;

function get_path_from_id($id, $ext_id, $base_path, $first_div, $sec_div)
{
    $ext = FileExtensions::getExtension($ext_id) ?? '';

    return ($base_path ? "{$base_path}/" : '') . floor($id / $first_div) . '/' . ($id % $sec_div) . '/' . $id . ($ext ? ".{$ext}" : '');
}

function get_avatar_path($id, $ext_id, $base_path = null, $first_div = 10000, $sec_div = 100)
{
    $base_path ??= config()->get('avatars.user.upload_path');

    return get_path_from_id($id, $ext_id, $base_path, $first_div, $sec_div);
}

function get_attach_path($id, $ext_id = null, $base_path = null, $first_div = 10000, $sec_div = 100)
{
    $ext_id ??= TORRENT_EXT_ID;
    $base_path ??= config()->get('tracker.attach.upload_path');

    return get_path_from_id($id, $ext_id, $base_path, $first_div, $sec_div);
}

/**
 * @throws BindingResolutionException
 */
function delete_avatar($user_id, $avatar_ext_id)
{
    $avatar_file = $avatar_ext_id ? get_avatar_path($user_id, $avatar_ext_id) : false;

    return $avatar_file && files()->isFile($avatar_file) && files()->delete($avatar_file);
}

/**
 * Returns array with all banned users
 */
function get_banned_users(bool $return_as_names = false): array
{
    $banned_users = [];

    foreach (DB()->fetch_rowset('SELECT ban_userid FROM ' . BB_BANLIST . ' WHERE ban_userid != 0') as $user) {
        $banned_users[] = $return_as_names ? get_username($user['ban_userid']) : $user['ban_userid'];
    }

    return $banned_users;
}

function set_tracks($cookie_name, &$tracking_ary, $tracks = null, $val = TIMENOW)
{
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

            if ($val > max($curr_track_val, user()->data['user_lastvisit'])) {
                $tracking_ary[$key] = $val;
            } elseif ($curr_track_val < user()->data['user_lastvisit']) {
                unset($tracking_ary[$key]);
            }
        }
    }

    $overflow = count(tracking_topics()) + count(tracking_forums()) - COOKIE_MAX_TRACKS;

    if ($overflow > 0) {
        arsort($tracking_ary);
        for ($i = 0; $i < $overflow; $i++) {
            array_pop($tracking_ary);
        }
    }

    if (array_diff($tracking_ary, $prev_tracking_ary)) {
        bb_setcookie($cookie_name, json_encode($tracking_ary));
    }
}

function get_last_read($topic_id = 0, $forum_id = 0)
{
    $topic_id = (int)$topic_id;
    $forum_id = (int)$forum_id;
    $t = $topic_id ? (tracking_topics()[$topic_id] ?? 0) : 0;
    $f = $forum_id ? (tracking_forums()[$forum_id] ?? 0) : 0;

    return max($t, $f, user()->data['user_lastvisit']);
}

function is_unread($ref, $topic_id = 0, $forum_id = 0): bool
{
    return !IS_GUEST && $ref > get_last_read($topic_id, $forum_id);
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

function bit2dec($bit_num)
{
    if (is_array($bit_num)) {
        $dec = 0;
        foreach ($bit_num as $bit) {
            $dec |= (1 << $bit);
        }

        return $dec;
    }

    return 1 << $bit_num;
}

function bf_bit2dec($bf_array_name, $key)
{
    $bf = bitfields($bf_array_name);
    if (!isset($bf[$key])) {
        throw new RuntimeException(__FUNCTION__ . ": bitfield '{$key}' not found");
    }

    return 1 << $bf[$key];
}

function bf($int, $bf_array_name, $key)
{
    return bf_bit2dec($bf_array_name, $key) & (int)$int;
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

    If available you can send an array (either one or two-dimensional) containing the
    forum auth levels, this will prevent the auth function having to do its own
    lookup
*/
function auth($type, $forum_id, $ug_data, array $f_access = [], $group_perm = UG_PERM_BOTH)
{
    $is_guest = true;
    $is_admin = false;
    $auth = $auth_fields = $u_access = [];
    $add_auth_type_desc = ($forum_id != AUTH_LIST_ALL);

    // Check forum existence
    if ($add_auth_type_desc && !forum_exists($forum_id)) {
        return [];
    }

    //
    // Get $auth_fields
    //
    if ($type == AUTH_ALL) {
        $auth_fields = array_keys(bitfields('forum_perm'));
    } elseif ($auth_type = array_search($type, bitfields('forum_perm'))) {
        $auth_fields = [$auth_type];
    }

    if (empty($auth_fields)) {
        throw new RuntimeException(__FUNCTION__ . '(): empty $auth_fields');
    }

    //
    // Get $f_access
    //
    // If f_access has been passed, or auth is needed to return an array of forums
    // then we need to pull the auth information on the given forum (or all forums)
    if (empty($f_access)) {
        $forums = forum_tree();

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
        throw new RuntimeException(__FUNCTION__ . '(): empty $f_access');
    }

    //
    // Get user or group permissions
    //
    $forum_match_sql = ($forum_id != AUTH_LIST_ALL) ? 'AND aa.forum_id = ' . (int)$forum_id : '';

    // GROUP mode
    if (!empty($ug_data['group_id'])) {
        $is_guest = false;
        $is_admin = false;

        $sql = 'SELECT aa.forum_id, aa.forum_perm
			FROM ' . BB_AUTH_ACCESS . ' aa
			WHERE aa.group_id = ' . (int)$ug_data['group_id'] . "
				{$forum_match_sql}";

        foreach (DB()->fetch_rowset($sql) as $row) {
            $u_access[$row['forum_id']] = $row['forum_perm'];
        }
    } // USER mode
    elseif (!empty($ug_data['user_id'])) {
        $is_guest = empty($ug_data['session_logged_in']);
        $is_admin = (!$is_guest && $ug_data['user_level'] == ADMIN);

        if ($group_perm != UG_PERM_BOTH) {
            $group_single_user = ($group_perm == UG_PERM_USER_ONLY) ? 1 : 0;

            $sql = '
				SELECT
					aa.forum_id, BIT_OR(aa.forum_perm) AS forum_perm
				FROM
					' . BB_USER_GROUP . ' ug,
					' . BB_GROUPS . ' g,
					' . BB_AUTH_ACCESS . ' aa
				WHERE
					    ug.user_id = ' . (int)$ug_data['user_id'] . "
					AND ug.user_pending = 0
					AND g.group_id = ug.group_id
					AND g.group_single_user = {$group_single_user}
					AND aa.group_id = g.group_id
						{$forum_match_sql}
					GROUP BY aa.forum_id
			";

            foreach (DB()->fetch_rowset($sql) as $row) {
                $u_access[$row['forum_id']] = $row['forum_perm'];
            }
        } else {
            if (!$is_guest && !$is_admin) {
                $sql = 'SELECT aa.forum_id, aa.forum_perm
					FROM ' . BB_AUTH_ACCESS_SNAP . ' aa
					WHERE aa.user_id = ' . (int)$ug_data['user_id'] . "
						{$forum_match_sql}";

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
                $auth[$f_id][$auth_type . '_type'] = &__('AUTH_TYPES')[$f_data[$auth_type]];
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

function get_select($select, $selected = null, $return_as = 'html', $first_opt = '&raquo;&raquo; Выбрать ')
{
    $select_name = null;
    $select_ary = [];

    switch ($select) {
        case 'groups':
            $sql = 'SELECT group_id, group_name FROM ' . BB_GROUPS . ' WHERE group_single_user = 0 ORDER BY group_name';
            foreach (DB()->fetch_rowset($sql) as $row) {
                $select_ary[$row['group_name']] = $row['group_id'];
            }
            $select_name = 'g';
            break;

        case 'forum_tpl':
            $sql = 'SELECT tpl_id, tpl_name FROM ' . BB_TOPIC_TPL . ' ORDER BY tpl_name';
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
    return html()->build_select($name, $params, $selected, $max_length, $multiple_size, $js);
}

function build_checkbox($name, $title, $checked = false, $disabled = false, $class = null, $id = null, $value = 1)
{
    return html()->build_checkbox($name, $title, $checked, $disabled, $class, $id, $value);
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
        $expressions = lang()->get('DECLENSION.' . strtoupper($expressions));
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

    // separate URL and ANCHOR
    $anchor = '';
    if (preg_match('/(.*)(#.*)/s', $url, $m)) {
        $url = $m[1];
        $anchor = $m[2];
    }
    // replace the parameter if it exists
    if (preg_match("/((\\?|&|&amp;){$arg}=)[^&]*/s", $url, $m)) {
        $cur = $m[0];
        $new = $value === null ? '' : $m[1] . urlencode($value);
        $url = str_replace($cur, $new, $url);
    } // add a parameter
    elseif ($value !== null) {
        $div = str_contains($url, '?') ? $amp : '?';
        $url = $url . $div . $arg . '=' . urlencode($value);
    }

    return $url . $anchor;
}

/**
 * Format bytes to human-readable size string
 *
 * Note: Cannot use Number::fileSize() because it doesn't support:
 * - $min parameter (minimum unit, e.g., 'KB' for speeds)
 * - Custom rounding strategy per unit
 * - Non-breaking space (&nbsp;) separator
 *
 * @param int|float|null $size Size in bytes (null treated as 0)
 * @param int|null $rounder Decimal places (auto-detected if null)
 * @param string|null $min Minimum unit ('KB' to avoid showing bytes)
 * @param string $space Separator between number and unit
 * @return string Formatted size string
 */
function humn_size(int|float|null $size, ?int $rounder = null, ?string $min = null, string $space = '&nbsp;'): string
{
    static $sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    static $rounders = [0, 0, 0, 2, 3, 3, 3, 3, 3];

    $size = (float)($size ?? 0);
    $ext = $sizes[0];
    $rnd = $rounders[0];

    if ($min === 'KB' && $size < 1024) {
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

    $rounder ??= $rnd;

    return round($size, $rounder) . $space . $ext;
}

function bt_show_ip($ip, $port = '')
{
    if (IS_AM) {
        $ip = TorrentPier\Helpers\IPHelper::decode($ip);

        if (!empty($port)) {
            if (TorrentPier\Helpers\IPHelper::isValidv6($ip)) {
                // Wrap IPv6 address in square brackets
                $ip = "[{$ip}]:{$port}";
            } else {
                $ip = "{$ip}:{$port}";
            }
        }

        return $ip;
    }

    return config()->get('bt_show_ip_only_moder') ? false : TorrentPier\Helpers\IPHelper::anonymizeIP($ip);
}

function bt_show_port($port)
{
    if (IS_AM) {
        return $port;
    }

    return config()->get('bt_show_port_only_moder') ? false : $port;
}

function checkbox_get_val(&$key, &$val, $default = 1, $on = 1, $off = 0, ?array $previous_settings = null, $search_id = null)
{
    $requestValue = request()->get($key);
    if (is_string($requestValue)) {
        $val = (int)$requestValue;
    } elseif (!request()->has($key) && request()->has('prev_' . $key)) {
        $val = $off;
    } elseif (isset($previous_settings[$key]) && (!IS_GUEST || !empty($search_id))) {
        $val = ($previous_settings[$key]) ? $on : $off;
    } else {
        $val = $default;
    }
}

function select_get_val($key, &$val, $options_ary, $default, $num = true, ?array $previous_settings = null)
{
    $requestValue = request()->get($key);
    if (is_string($requestValue)) {
        if (isset($options_ary[$requestValue])) {
            $val = ($num) ? (int)$requestValue : $requestValue;
        }
    } elseif (isset($previous_settings[$key])) {
        $val = $previous_settings[$key];
    } else {
        $val = $default;
    }
}

function get_username($user_id)
{
    if (empty($user_id)) {
        return is_array($user_id) ? [] : false;
    }
    if (is_array($user_id)) {
        $usernames = [];
        foreach (DB()->fetch_rowset('SELECT user_id, username FROM ' . BB_USERS . ' WHERE user_id IN(' . get_id_csv($user_id) . ')') as $row) {
            $usernames[$row['user_id']] = $row['username'];
        }

        return $usernames;
    }

    $row = DB()->fetch_row('SELECT username FROM ' . BB_USERS . " WHERE user_id = '" . DB()->escape($user_id) . "' LIMIT 1");

    return $row ? $row['username'] : false;
}

function get_user_id($username)
{
    if (empty($username)) {
        return false;
    }

    if ($row = DB()->fetch_row('SELECT user_id FROM ' . BB_USERS . " WHERE username = '" . DB()->escape($username) . "' LIMIT 1")) {
        return $row['user_id'];
    }

    return false;
}

/**
 * Truncate string to specified length with ellipsis
 *
 * @deprecated Use Str::limit() directly: Str::limit($text, $max_length, '...')
 * @see Str::limit
 */
function str_short($text, $max_length): string
{
    if (empty($max_length) || empty($text)) {
        return $text ?? '';
    }

    return Str::limit($text, $max_length);
}

function generate_user_info($row, bool $have_auth = IS_ADMIN): array
{
    $from = !empty($row['user_from']) ? render_flag($row['user_from'], false) : __('NOSELECT');
    $joined = bb_date($row['user_regdate'], 'Y-m-d H:i', false);
    $user_time = !empty($row['user_time']) ? sprintf('%s <span class="signature">(%s)</span>', bb_date($row['user_time']), humanTime($row['user_time'])) : __('NOSELECT');
    $posts = '<a href="' . FORUM_PATH . 'search?search_author=1&amp;uid=' . $row['user_id'] . '" target="_blank">' . ($row['user_posts'] ?: 0) . '</a>';
    $pm = '<a class="txtb" href="' . (PM_URL . '?mode=post&amp;' . POST_USERS_URL . '=' . $row['user_id']) . '">' . __('SEND_PM_SHORT') . '</a>';
    $avatar = get_avatar($row['user_id'], $row['avatar_ext_id'], !bf($row['user_opt'], 'user_opt', 'dis_avatar'), 50, 50);

    if (bf($row['user_opt'], 'user_opt', 'user_viewemail') || $have_auth || ($row['user_id'] == userdata('user_id'))) {
        $email_uri = (config()->get('mail.board_email_form')) ? url()->memberEmail($row['user_id'], $row['username']) : 'mailto:' . $row['user_email'];
        $email = '<a class="editable" href="' . $email_uri . '">' . $row['user_email'] . '</a>';
    } else {
        $email = __('HIDDEN_USER');
    }

    if ($row['user_website']) {
        $www = '<a class="txtb" href="' . $row['user_website'] . '"  target="_userwww">[ ' . __('WEBSITE_SHORT') . ' ]</a>';
    } else {
        $www = __('NOSELECT');
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
        'www' => $www,
    ];
}

function get_bt_userdata($user_id)
{
    if (!$btu = CACHE('bb_cache')->get('btu_' . $user_id)) {
        $btu = DB()->fetch_row('
			SELECT bt.*, SUM(tr.speed_up) AS speed_up, SUM(tr.speed_down) AS speed_down
			FROM      ' . BB_BT_USERS . ' bt
			LEFT JOIN ' . BB_BT_TRACKER . ' tr ON (bt.user_id = tr.user_id)
			WHERE bt.user_id = ' . (int)$user_id . '
			GROUP BY bt.user_id
			LIMIT 1
		');

        if ($btu) {
            CACHE('bb_cache')->set('btu_' . $user_id, $btu, 300);
        }
    }

    return $btu;
}

function show_bt_userdata($user_id): void
{
    if (!SHOW_BT_STATS) {
        return;
    }

    if (!$btu = get_bt_userdata($user_id)) {
        return;
    }

    template()->assign_vars([
        'SHOW_BT_USERDATA' => true,
        'UP_TOTAL' => humn_size($btu['u_up_total']),
        'UP_BONUS' => humn_size($btu['u_up_bonus']),
        'RELEASED' => humn_size($btu['u_up_release']),
        'DOWN_TOTAL' => humn_size($btu['u_down_total']),
        'DOWN_TOTAL_BYTES' => $btu['u_down_total'],
        'USER_RATIO' => get_bt_ratio($btu),
        'MIN_DL_FOR_RATIO' => humn_size((int)MIN_DL_FOR_RATIO),
        'MIN_DL_BYTES' => (int)MIN_DL_FOR_RATIO,
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

        'SPEED_UP' => humn_size($btu['speed_up'], min: 'KB') . '/s',
        'SPEED_DOWN' => humn_size($btu['speed_down'], min: 'KB') . '/s',
    ]);
}

function bb_get_config($table, $from_db = false, $update_cache = true)
{
    return config()->loadFromDatabase($table, $from_db, $update_cache);
}

function bb_update_config($params, $table = BB_CONFIG)
{
    config()->updateDatabase($params, $table);
}

function clean_username($username)
{
    $username = mb_substr(htmlspecialchars(str_replace("\\'", "'", trim($username))), 0, 25, DEFAULT_CHARSET);
    $username = rtrim($username, '\\');
    $username = str_replace("'", "\\'", $username);

    return $username;
}

/**
 * Get Userdata
 */
function get_userdata(int|string $u, bool $is_name = false, bool $allow_guest = false): array|false
{
    if (empty($u)) {
        return false;
    }

    if (!$is_name && (int)$u === GUEST_UID && $allow_guest) {
        if ($cached = CACHE('bb_cache')->get('guest_userdata')) {
            return $cached;
        }
    }

    $user = $is_name
        ? User::where('username', clean_username($u))->first()
        : User::find($u);

    if (!$user || (!$allow_guest && $user->user_id === GUEST_UID)) {
        return false;
    }

    $userData = $user->makeVisible($user->getHidden())->toArray();

    if ($user->user_id === GUEST_UID) {
        CACHE('bb_cache')->set('guest_userdata', $userData);
    }

    return $userData;
}

function make_jumpbox(): void
{
    if (!config()->get('forum.show_jumpbox')) {
        return;
    }

    if (!$jumpbox = datastore()->get('jumpbox')) {
        datastore()->update('jumpbox');
        $jumpbox = datastore()->get('jumpbox');
    }

    template()->assign_vars(['JUMPBOX' => IS_GUEST ? DB()->escape($jumpbox['guest']) : DB()->escape($jumpbox['user'])]);
}

// $mode: array(not_auth_forum1,not_auth_forum2,..) or (string) 'mode'
function get_forum_select($mode = 'guest', $name = POST_FORUM_URL, $selected = null, $max_length = HTML_SELECT_MAX_LENGTH, $multiple_size = null, $js = '', $all_forums_option = null)
{
    if (is_array($mode)) {
        $not_auth_forums_fary = array_flip($mode);
        $mode = 'not_auth_forums';
    }
    if ($max_length === null) {
        $max_length = HTML_SELECT_MAX_LENGTH;
    }
    $select = $all_forums_option === null ? [] : [__('ALL_AVAILABLE') => $all_forums_option];
    $forums = forum_tree();

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
                throw new RuntimeException(__FUNCTION__ . ": invalid mode '{$mode}'");
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
 * @throws BindingResolutionException
 */
function setup_style(): void
{
    once(function () {
        // AdminCP works only with the default template
        $tpl_dir_name = defined('IN_ADMIN') ? 'default' : basename(config()->get('templates.default'));
        $stylesheet = defined('IN_ADMIN') ? 'main.css' : basename(config()->get('templates.stylesheet'));

        if (!IS_GUEST && !empty(userdata('tpl_name'))) {
            foreach (config()->get('templates.available') as $folder => $name) {
                if (userdata('tpl_name') == $folder) {
                    $tpl_dir_name = basename(userdata('tpl_name'));
                }
            }
        }

        template(TEMPLATES_DIR . '/' . $tpl_dir_name);

        template()->assign_vars([
            'BB_ROOT' => BB_ROOT,
            'SPACER' => make_url('assets/images/spacer.gif'),
            'STYLESHEET' => asset_url('css/' . $stylesheet, 'css'),
            'EXT_LINK_NEW_WIN' => config()->get('forum.ext_link_new_win'),
            'TPL_DIR' => make_url('assets/css/'),
            'SITE_URL' => make_url('/'),
            'ASSETS_URL' => make_url('assets/'),
        ]);
    });
}

/**
 * Format timestamp with timezone and locale support
 *
 * @param int $timestamp Unix timestamp
 * @param string|false $format Date format (false = use default)
 * @param bool $friendly_date Show "Today"/"Yesterday"
 * @return string Formatted date string
 */
function bb_date(int $timestamp, string|false $format = false, bool $friendly_date = true): string
{
    // Determine timezone offset
    if (!defined('IS_GUEST') || IS_GUEST) {
        $tz = (float)config()->get('localization.board_timezone', 0);
    } else {
        $tz = (float)(userdata('user_timezone') ?? 0);
    }

    // Determine user locale
    $locale = userdata('user_lang') ?? config()->get('localization.default_lang', 'en');

    // Prepare labels for "today" and "yesterday"
    $labels = [
        'today' => __('DATETIME.TODAY', 'Today'),
        'yesterday' => __('DATETIME.YESTERDAY', 'Yesterday'),
    ];

    // Use TimeHelper for actual formatting
    return TorrentPier\Helpers\TimeHelper::formatDate(
        $timestamp,
        $format,
        $friendly_date,
        $tz,
        $locale,
        $labels,
    );
}

/**
 * Get user's torrent client string
 * @throws BindingResolutionException
 */
function get_user_torrent_client(string $peer_id): string
{
    static $iconExtension = '.png';

    $bestMatch = TorrentClients::getName($peer_id);

    $clientIconFile = IMAGES_DIR . '/clients/' . $bestMatch . $iconExtension;
    if (!empty($bestMatch) && files()->isFile($clientIconFile)) {
        $clientIconUrl = image_url('clients/' . $bestMatch . $iconExtension);

        return '<img class="client_icon" src="' . $clientIconUrl . '" alt="' . $bestMatch . '" title="' . $peer_id . '">';
    }

    return $peer_id;
}

/**
 * Returns country flag by country code
 * @throws BindingResolutionException
 */
function render_flag(string $code, bool $showName = true): string
{
    static $iconExtension = '.svg';

    if ($code === '0') {
        return ''; // No selected
    }

    if (!Countries::exists($code)) {
        return $code;
    }

    $flagIconFile = IMAGES_DIR . '/flags/' . $code . $iconExtension;
    if (!files()->isFile($flagIconFile)) {
        return $code;
    }

    $flagIconUrl = image_url('flags/' . $code . $iconExtension);
    $langName = Countries::getName($code);
    $countryName = $showName ? '&nbsp;' . str_short($langName, 20) : '';

    return '<span title="' . $langName . '"><img src="' . $flagIconUrl . '" class="poster-flag" alt="' . $code . '">' . $countryName . '</span>';
}

function birthday_age($date)
{
    return TorrentPier\Helpers\TimeHelper::birthdayAge($date);
}

/**
 * Format registration time intervals
 * Takes an array of restricted hours and returns a formatted string of allowed hours
 *
 * @param array $restricted_hours Array of hours when registration is restricted
 * @return array ['intervals' => '09:00-10:59', 'current_time' => '15:30']
 */
function format_registration_intervals(array $restricted_hours): array
{
    // Validate and filter restricted hours to be within 0-23
    $restricted_hours = array_filter($restricted_hours, fn ($h) => is_int($h) && $h >= 0 && $h <= 23);

    // Use board timezone for the current time
    $tz = config()->get('localization.board_timezone');
    $current_time = gmdate('H:i', TIMENOW + (3600 * $tz));

    // Calculate allowed hours (0-23 minus restricted)
    $all_hours = range(0, 23);
    $allowed_hours = array_diff($all_hours, $restricted_hours);
    sort($allowed_hours);

    if (empty($allowed_hours)) {
        return [
            'intervals' => '',
            'current_time' => $current_time,
        ];
    }

    // Group consecutive hours into intervals
    $intervals = [];
    $start = $end = null;

    foreach ($allowed_hours as $hour) {
        if ($start === null) {
            $start = $end = $hour;
        } elseif ($hour === $end + 1) {
            $end = $hour;
        } else {
            // End of an interval, save it
            $intervals[] = sprintf('%02d:00-%02d:59', $start, $end);
            $start = $end = $hour;
        }
    }

    // Add the last interval
    if ($start !== null) {
        $intervals[] = sprintf('%02d:00-%02d:59', $start, $end);
    }

    return [
        'intervals' => implode(', ', $intervals),
        'current_time' => $current_time,
    ];
}

//
// Pagination routine, generates
// page number sequence
//
function generate_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = true)
{
    $begin_end = 3;
    $from_middle = 1;

    $total_pages = ceil($num_items / $per_page);
    $on_page = floor($start_item / $per_page) + 1;

    $query_separator = '&amp;';
    if (!str_contains($base_url, '?')) {
        $query_separator = '?';
    }

    $page_string = '';
    if ($total_pages > ((2 * ($begin_end + $from_middle)) + 2)) {
        $init_page_max = ($total_pages > $begin_end) ? $begin_end : $total_pages;
        for ($i = 1; $i < $init_page_max + 1; $i++) {
            $page_string .= ($i == $on_page) ? '<b>' . $i . '</b>' : '<a href="' . $base_url . "{$query_separator}start=" . (($i - 1) * $per_page) . '">' . $i . '</a>';
            if ($i < $init_page_max) {
                $page_string .= ', ';
            }
        }
        if ($total_pages > $begin_end) {
            if ($on_page > 1 && $on_page < $total_pages) {
                $page_string .= ($on_page > ($begin_end + $from_middle + 1)) ? ' ... ' : ', ';

                $init_page_min = ($on_page > ($begin_end + $from_middle)) ? $on_page : ($begin_end + $from_middle + 1);

                $init_page_max = ($on_page < $total_pages - ($begin_end + $from_middle)) ? $on_page : $total_pages - ($begin_end + $from_middle);

                for ($i = $init_page_min - $from_middle; $i < $init_page_max + ($from_middle + 1); $i++) {
                    $page_string .= ($i == $on_page) ? '<b>' . $i . '</b>' : '<a href="' . $base_url . "{$query_separator}start=" . (($i - 1) * $per_page) . '">' . $i . '</a>';
                    if ($i < $init_page_max + $from_middle) {
                        $page_string .= ', ';
                    }
                }
                $page_string .= ($on_page < $total_pages - ($begin_end + $from_middle)) ? ' ... ' : ', ';
            } else {
                $page_string .= '&nbsp;...&nbsp;';
            }
            for ($i = $total_pages - ($begin_end - 1); $i < $total_pages + 1; $i++) {
                $page_string .= ($i == $on_page) ? '<b>' . $i . '</b>' : '<a href="' . $base_url . "{$query_separator}start=" . (($i - 1) * $per_page) . '">' . $i . '</a>';
                if ($i < $total_pages) {
                    $page_string .= ', ';
                }
            }
        }
    } else {
        for ($i = 1; $i < $total_pages + 1; $i++) {
            $page_string .= ($i == $on_page) ? '<b>' . $i . '</b>' : '<a href="' . $base_url . "{$query_separator}start=" . (($i - 1) * $per_page) . '">' . $i . '</a>';
            if ($i < $total_pages) {
                $page_string .= ', ';
            }
        }
    }

    if ($add_prevnext_text) {
        if ($on_page > 1) {
            $page_string = ' <a href="' . $base_url . "{$query_separator}start=" . (($on_page - 2) * $per_page) . '">' . __('PREVIOUS_PAGE') . '</a>&nbsp;&nbsp;' . $page_string;
            $meta_prev_link = FULL_URL . $base_url . "{$query_separator}start=" . (($on_page - 2) * $per_page);
        }

        if ($on_page < $total_pages) {
            $page_string .= '&nbsp;&nbsp;<a href="' . $base_url . "{$query_separator}start=" . ($on_page * $per_page) . '">' . __('NEXT_PAGE') . '</a>';
            $meta_next_link = FULL_URL . $base_url . "{$query_separator}start=" . ($on_page * $per_page);
        }
    }

    $pagination = false;
    if ($page_string && $total_pages > 1) {
        $pagination = '<a class="menu-root" href="#pg-jump">' . __('GOTO_PAGE') . '</a> :&nbsp;&nbsp;' . $page_string;
        $pagination = str_replace("{$query_separator}start=0", '', $pagination);
    }

    template()->assign_vars([
        'PAGINATION' => $pagination,
        'PAGE_NUMBER' => sprintf(__('PAGE_OF'), (floor($start_item / $per_page) + 1), ceil($num_items / $per_page)),
        'PG_BASE_URL' => $base_url,
        'PG_PER_PAGE' => $per_page,
        // Assign meta
        'META_PREV_PAGE' => $meta_prev_link ?? '',
        'META_NEXT_PAGE' => $meta_next_link ?? '',
    ]);

    return $pagination;
}

/**
 * @throws BindingResolutionException
 * @throws JsonException
 */
function bb_die($msgText, $statusCode = null): void
{
    $statusCode ??= 500;
    http_response_code($statusCode);

    // Detect API requests and return JSON response
    $isApiRequest = str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')
        || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

    if ($isApiRequest) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'error_code' => $statusCode,
            'error_msg' => strip_tags(br2nl($msgText)),
        ], JSON_THROW_ON_ERROR);
        exit;
    }

    // Check for recursive calls - fall back to simple output
    if (defined('HAS_DIED')) {
        bb_simple_die($msgText, $statusCode);
    }
    define('HAS_DIED', 1);
    define('DISABLE_CACHING_OUTPUT', true);

    // Try to initialize session/language, but don't fail if DB is broken
    try {
        lang()->initializeLanguage();

        if (empty(userdata()) && !defined('SESSION_STARTED')) {
            user()->session_start();
            define('SESSION_STARTED', true);
        }
    } catch (Throwable $e) {
        // DB or other critical failure - fall back to simple output
        bb_simple_die($msgText, $statusCode);
    }

    // If the header hasn't been output then do it
    if (!defined('PAGE_HEADER_SENT')) {
        setup_style();
        require PAGE_HEADER;
    }

    // Check for lang variable
    if ($translated = __($msgText)) {
        $msgText = $translated;
    }

    template()->assign_vars([
        'TPL_BB_DIE' => true,
        'MESSAGE_TEXT' => $msgText,
    ]);

    template()->set_filenames(['bb_die' => 'common.twig']);
    template()->pparse('bb_die');

    require PAGE_FOOTER;

    exit;
}

function bb_simple_die($txt, $status_code = null)
{
    TorrentPier\Http\Response::text($txt, $status_code ?? 200)->send();
    exit;
}

/**
 * @throws BindingResolutionException
 */
function login_redirect($url = '')
{
    redirect(LOGIN_URL . '?redirect=' . (($url) ?: request()->getRequestUri()));
}

function meta_refresh($url, $time = 5)
{
    template()->assign_var('META', '<meta http-equiv="refresh" content="' . $time . ';url=' . $url . '" />');
}

function redirect($url)
{
    if (headers_sent($filename, $linenum)) {
        throw new RuntimeException(__FUNCTION__ . ": Headers already sent in {$filename}({$linenum})");
    }

    if (str_contains(urldecode($url), "\n") || str_contains(urldecode($url), "\r") || str_contains(urldecode($url), ';url')) {
        bb_die('Tried to redirect to potentially insecure url');
    }

    $url = trim($url);
    $server_protocol = request()->isSecure() ? 'https://' : 'http://';

    $server_name = preg_replace('#^\/?(.*?)\/?$#', '\1', trim(config()->get('app.server_name')));
    $port = (int)config()->get('app.server_port');
    $server_port = ($port > 0 && !in_array($port, [80, 443], true)) ? ':' . $port : '';
    $script_name = preg_replace('#^\/?(.*?)\/?$#', '\1', trim(config()->get('app.script_path')));

    if ($script_name) {
        $script_name = "/{$script_name}";
        $url = preg_replace("#^{$script_name}#", '', $url);
    }

    $redirect_url = $server_protocol . $server_name . $server_port . $script_name . preg_replace('#^\/?(.*?)\/?$#', '/\1', $url);

    // Send redirect response with no-cache headers
    $response = TorrentPier\Http\Response::redirect($redirect_url);
    TorrentPier\Http\Response::noCache($response)->send();
    exit;
}

// build a list of the sortable fields or return field name
function get_forum_display_sort_option($selected_row = 0, $action = 'list', $list = 'sort')
{
    $forum_display_sort = [
        'lang_key' => ['LASTPOST', 'SORT_TOPIC_TITLE', 'SORT_TIME'],
        'fields' => ['t.topic_last_post_time', 't.topic_title', 't.topic_time'],
    ];
    $forum_display_order = [
        'lang_key' => ['DESC', 'ASC'],
        'fields' => ['DESC', 'ASC'],
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
            $l_value = __($listrow['lang_key'][$i]) ?: $iValue;
            $res .= '<option value="' . $i . '"' . $selected . '>' . $l_value . '</option>';
        }
    } else {
        // field
        $res = $listrow['fields'][$selected_row];
    }

    return $res;
}

function clear_dl_list($topics_csv)
{
    DB()->query('DELETE FROM ' . BB_BT_DLSTATUS . " WHERE topic_id IN({$topics_csv})");
    DB()->query('DELETE FROM ' . BB_BT_DLSTATUS_SNAP . " WHERE topic_id IN({$topics_csv})");
}

/**
 * Convert array or single ID to a comma-separated string of integers
 *
 * @param array|int|string $ids Array of IDs or single ID
 * @return string Comma-separated string of integers
 * @deprecated Use collect($ids)->flatten()->map('intval')->implode(',') directly
 */
function get_id_csv(array|int|string $ids): string
{
    return new Collection(Arr::wrap($ids))->flatten()->map('intval')->implode(',');
}

/**
 * Convert a comma-separated string or array to an array of integers
 *
 * @param array|string $ids Comma-separated string or array of IDs
 * @return array<int> Array of integers
 * @deprecated Use collect(explode(',', $ids))->map('intval')->values()->all() directly
 */
function get_id_ary(array|string $ids): array
{
    $ids = is_string($ids) ? explode(',', $ids) : Arr::wrap($ids);

    return new Collection($ids)->flatten()->map('intval')->values()->all();
}

function get_topic_title($topic_id)
{
    $row = DB()->fetch_row('
		SELECT topic_title FROM ' . BB_TOPICS . ' WHERE topic_id = ' . (int)$topic_id . '
	');

    return $row['topic_title'];
}

function forum_exists($forum_id = null): bool
{
    if ($forum_id === null) {
        return (bool)DB()->fetch_row('SELECT 1 FROM ' . BB_FORUMS . ' LIMIT 1');
    }

    $forum_id = (int)$forum_id;

    return (bool)DB()->fetch_row('SELECT 1 FROM ' . BB_FORUMS . " WHERE forum_id = {$forum_id} LIMIT 1");
}

function cat_exists($cat_id): bool
{
    $cat_id = (int)$cat_id;

    return (bool)DB()->fetch_row('SELECT 1 FROM ' . BB_CATEGORIES . " WHERE cat_id = {$cat_id} LIMIT 1");
}

function get_topic_icon($topic, $is_unread = null)
{
    $t_hot = ($topic['topic_replies'] >= config()->get('hot_threshold'));
    $is_unread ??= is_unread($topic['topic_last_post_time'], $topic['topic_id'], $topic['forum_id']);

    if ($topic['topic_status'] == TOPIC_MOVED) {
        $folder_image = theme_images('folder');
    } else {
        $folder = ($t_hot) ? theme_images('folder_hot') : theme_images('folder');
        $folder_new = ($t_hot) ? theme_images('folder_hot_new') : theme_images('folder_new');

        if ($topic['topic_type'] == POST_ANNOUNCE) {
            $folder = theme_images('folder_announce');
            $folder_new = theme_images('folder_announce_new');
        } elseif ($topic['topic_type'] == POST_STICKY) {
            $folder = theme_images('folder_sticky');
            $folder_new = theme_images('folder_sticky_new');
        } elseif ($topic['topic_status'] == TOPIC_LOCKED) {
            $folder = theme_images('folder_locked');
            $folder_new = theme_images('folder_locked_new');
        } elseif ($topic['topic_dl_type'] == TOPIC_DL_TYPE_DL) {
            $folder = ($t_hot) ? theme_images('folder_dl_hot') : theme_images('folder_dl');
            $folder_new = ($t_hot) ? theme_images('folder_dl_hot_new') : theme_images('folder_dl_new');
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
        // Use ? or & depending on whether URL already has query string
        $separator = str_contains($url, '?') ? '&amp;' : '?';

        for ($j = 0, $page = 1; $j < $replies; $j += $per_page, $page++) {
            $href = ($j) ? "{$url}{$separator}start={$j}" : $url;
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

function print_confirmation($tpl_vars): void
{
    template()->assign_vars([
        'TPL_CONFIRM' => true,
        'CONFIRM_TITLE' => __('CONFIRM'),
        'FORM_METHOD' => 'post',
    ]);

    if (!isset($tpl_vars['QUESTION'])) {
        $tpl_vars['QUESTION'] = __('QUESTION');
    }

    template()->assign_vars($tpl_vars);

    print_page('common.twig');
}

/**
 *  $args = array(
 *            'tpl' => 'template file name',
 *            'simple' => true, // use simple header
 *          );
 *       OR (string) 'template_file_name'
 *
 *  $type = '' (common forum page)
 *          'admin' (adminCP page)
 *          'simple' (simple page without a common header)
 *
 *  $mode = 'no_header'
 *          'no_footer'
 *
 *  $variables = [] - variables to pass directly to the template (for .twig files)
 *                    These are merged with existing template variables
 */
function print_page($args, $type = '', $mode = '', array $variables = [])
{
    $tpl = (is_array($args) && !empty($args['tpl'])) ? $args['tpl'] : $args;
    $tpl = ($type === 'admin') ? ADMIN_TPL_DIR . $tpl : $tpl;

    if ((is_array($args) && !empty($args['simple'])) || $type === 'simple') {
        simple_header(true);
    }

    // Assign variables BEFORE the header so PAGE_TITLE is available
    if (!empty($variables)) {
        template()->assign_vars($variables, true);
    }

    if ($mode !== 'no_header') {
        require PAGE_HEADER;
    }

    template()->set_filenames(['body' => $tpl]);
    template()->pparse('body');

    if ($mode !== 'no_footer') {
        require PAGE_FOOTER;
    }
}

function caching_output($enabled, $mode, $cache_var_name, $ttl = 300)
{
    if (!$enabled || !CACHE('bb_cache')->used) {
        return;
    }

    if ($mode == 'send') {
        if ($cached_contents = CACHE('bb_cache')->get($cache_var_name)) {
            bb_exit($cached_contents);
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
    $str = htmlCHR(Str::squish($str));

    return $str;
}

function clean_text_match(?string $text, bool $ltrim_star = true, bool $die_if_empty = false): string
{
    $text = Str::squish($text);
    $ltrim_chars = ($ltrim_star) ? ' *-!' : ' ';
    $text = ' ' . Str::squish(ltrim($text, $ltrim_chars)) . ' ';

    if (config()->get('forum.search_engine_type') == 'manticore') {
        $text = strip_tags($text);
        $text = Str::squish($text);
        $text = trim($text);
    } else {
        $text = DB()->escape(trim($text));
    }

    if (!$text && $die_if_empty) {
        bb_die(__('NO_SEARCH_MATCH'));
    }

    return $text;
}

function get_title_match_topics(string $title_match_sql, array $forum_ids = [], bool $title_only = true): ?array
{
    $where_ids = [];
    if ($forum_ids) {
        $forum_ids = array_diff($forum_ids, [0 => 0]);
    }

    if (config()->get('forum.search_engine_type') == 'manticore') {
        try {
            $manticore = manticore();
            $index = $title_only ? 'topics_rt' : 'posts_rt';
            $result = $manticore->search($title_match_sql, $index, $forum_ids);

            if (!empty($result['matches'])) {
                $where_ids = array_keys($result['matches']);
            }
        } catch (Exception $e) {
            bb_log('Manticore search error: ' . $e->getMessage() . LOG_LF, 'manticore_errors');

            // Fallback to MySQL if needed
            if (config()->get('forum.search_fallback_to_mysql')) {
                get_title_match_topics_mysql($title_match_sql, $forum_ids, $where_ids, $title_only);
            }
        }
    } else {
        get_title_match_topics_mysql($title_match_sql, $forum_ids, $where_ids, $title_only);
    }

    return $where_ids;
}

function get_title_match_topics_mysql(string $title_match_sql, array $forum_ids, array &$where_ids, bool $title_only = true): void
{
    $where_forum = $forum_ids ? 'AND forum_id IN(' . implode(',', $forum_ids) . ')' : '';
    $search_bool_mode = config()->get('forum.allow_search_in_bool_mode') ? ' IN BOOLEAN MODE' : '';

    if ($title_only) {
        // topics
        $sql = 'SELECT topic_id FROM ' . BB_TOPICS . "
                WHERE MATCH (topic_title) AGAINST ('{$title_match_sql}'{$search_bool_mode})
                {$where_forum}";
        foreach (DB()->fetch_rowset($sql) as $row) {
            $where_ids[] = $row['topic_id'];
        }
    } else {
        // posts
        $sql = 'SELECT p.post_id FROM ' . BB_POSTS . ' p, ' . BB_POSTS_SEARCH . " ps
            WHERE ps.post_id = p.post_id
                AND MATCH (ps.search_words) AGAINST ('{$title_match_sql}'{$search_bool_mode})
                {$where_forum}";
        foreach (DB()->fetch_rowset($sql) as $row) {
            $where_ids[] = $row['post_id'];
        }
    }
}

/**
 * Sync topics to manticore
 */
function sync_topic_to_manticore($topic_id, $topic_title = null, $forum_id = null, string $action = 'upsert'): void
{
    if (config()->get('forum.search_engine_type') !== 'manticore') {
        return;
    }

    try {
        $manticore = manticore();

        if ($action === 'delete') {
            $manticore->deleteTopic($topic_id);
        } else {
            $manticore->upsertTopic($topic_id, $topic_title, $forum_id);
        }
    } catch (Exception $e) {
        bb_log('Failed to sync topic to Manticore: ' . $e->getMessage() . LOG_LF, 'manticore_errors');
    }
}

/**
 * Sync posts to manticore
 */
function sync_post_to_manticore($post_id, $post_text = null, $topic_title = null, $topic_id = null, $forum_id = null, string $action = 'upsert'): void
{
    if (config()->get('forum.search_engine_type') !== 'manticore') {
        return;
    }

    try {
        $manticore = manticore();

        if ($action === 'delete') {
            $manticore->deletePost($post_id);
        } else {
            $manticore->upsertPost($post_id, $post_text, $topic_title, $topic_id, $forum_id);
        }
    } catch (Exception $e) {
        bb_log('Failed to sync post to Manticore: ' . $e->getMessage() . LOG_LF, 'manticore_errors');
    }
}

/**
 * Sync users to manticore
 */
function sync_user_to_manticore($user_id, ?string $username = null, string $action = 'upsert'): void
{
    if (config()->get('forum.search_engine_type') !== 'manticore') {
        return;
    }

    try {
        $manticore = manticore();

        if ($action === 'delete') {
            $manticore->deleteUser($user_id);
        } else {
            $manticore->upsertUser($user_id, $username);
        }
    } catch (Exception $e) {
        bb_log('Failed to sync user to Manticore: ' . $e->getMessage() . LOG_LF, 'manticore_errors');
    }
}

/**
 * Create magnet link
 *
 * @param string $infohash (xt=urn:btih)
 * @param string $infohash_v2 (xt=urn:btmh:1220)
 * @param string $auth_key (tr)
 * @param string $name (dn)
 * @param int|string $length (xl)
 */
function create_magnet(?string $infohash, ?string $infohash_v2, string $auth_key, string $name, int|string $length = 0): string
{
    if (!config()->get('magnet_links_enabled')) {
        return false;
    }

    // Only for registered users
    if (!config()->get('magnet_links_for_guests') && IS_GUEST) {
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

    $length = (int)$length;
    if ($length > 0) {
        $magnet .= '&xl=' . $length;
    }

    $icon = $v2_support ? theme_images('icon_magnet_v2') : theme_images('icon_magnet');
    $title = $v2_support ? __('MAGNET_v2') : __('MAGNET');

    return '<a title="' . $title . '" href="' . $magnet . '&tr=' . urlencode(config()->get('bt_announce_url') . '?' . config()->get('tracker.passkey_key') . "={$auth_key}") . '&dn=' . urlencode($name) . '"><img src="' . $icon . '" width="12" height="12" border="0" /></a>';
}

function set_die_append_msg($forum_id = null, $topic_id = null, $group_id = null)
{
    $msg = '';
    $msg .= $topic_id ? '<p class="mrg_10"><a href="' . TOPIC_URL . $topic_id . '">' . __('TOPIC_RETURN') . '</a></p>' : '';
    $msg .= $forum_id ? '<p class="mrg_10"><a href="' . FORUM_URL . $forum_id . '">' . __('FORUM_RETURN') . '</a></p>' : '';
    if ($group_id) {
        $groupName = DB()->table('bb_groups')->get($group_id)?->group_name ?? '';
        $msg .= '<p class="mrg_10"><a href="' . url()->group($group_id, $groupName) . '">' . __('GROUP_RETURN') . '</a></p>';
    }
    $msg .= '<p class="mrg_10"><a href="' . FORUM_PATH . '">' . __('INDEX_RETURN') . '</a></p>';
    template()->assign_var('BB_DIE_APPEND_MSG', $msg);
}

function set_pr_die_append_msg($pr_uid, $pr_username = null)
{
    $pr_username ??= get_username($pr_uid);
    template()->assign_var('BB_DIE_APPEND_MSG', '
		<a href="' . url()->member($pr_uid, $pr_username) . '" onclick="return post2url(this.href, {after_edit: 1});">' . __('PROFILE_RETURN') . '</a>
		<br /><br />
		<a href="' . SETTINGS_URL . (IS_ADMIN ? '?' . POST_USERS_URL . "={$pr_uid}" : '') . '" onclick="return post2url(this.href, {after_edit: 1});">' . __('PROFILE_EDIT_RETURN') . '</a>
		<br /><br />
		<a href="' . FORUM_PATH . '">' . __('INDEX_RETURN') . '</a>
	');
}

function send_pm($user_id, $subject, $message, $poster_id = BOT_UID)
{
    $subject = DB()->escape($subject);
    $message = DB()->escape($message);

    if ($poster_id == BOT_UID) {
        $poster_ip = '0';
    } elseif ($row = DB()->fetch_row('SELECT user_reg_ip FROM ' . BB_USERS . " WHERE user_id = {$poster_id}")) {
        $poster_ip = $row['user_reg_ip'];
    } else {
        $poster_id = userdata('user_id');
        $poster_ip = USER_IP;
    }

    DB()->query('INSERT INTO ' . BB_PRIVMSGS . ' (privmsgs_type, privmsgs_subject, privmsgs_from_userid, privmsgs_to_userid, privmsgs_date, privmsgs_ip) VALUES (' . PRIVMSGS_NEW_MAIL . ", '{$subject}', {$poster_id}, {$user_id}, " . TIMENOW . ", '{$poster_ip}')");
    $pm_id = DB()->sql_nextid();

    DB()->query('INSERT INTO ' . BB_PRIVMSGS_TEXT . " (privmsgs_text_id, privmsgs_text) VALUES ({$pm_id}, '{$message}')");
    DB()->query('UPDATE ' . BB_USERS . ' SET user_new_privmsg = user_new_privmsg + 1, user_last_privmsg = ' . TIMENOW . ", user_newest_pm_id = {$pm_id} WHERE user_id = {$user_id}");
}

/**
 * Generates link to profile
 */
function profile_url(array $data, bool $target_blank = false, bool $no_link = false): string
{
    if (!$ranks = datastore()->get('ranks')) {
        datastore()->update('ranks');
        $ranks = datastore()->get('ranks');
    }

    $username = !empty($data['username']) ? $data['username'] : __('GUEST');
    // Use display_username for display, username for URL (allows truncated display with correct URL)
    $display_username = !empty($data['display_username']) ? $data['display_username'] : $username;
    $user_id = !empty($data['user_id']) ? (int)$data['user_id'] : GUEST_UID;
    $user_rank = !empty($data['user_rank']) ? $data['user_rank'] : 0;

    $title = '';
    $style = 'colorUser';
    if (isset($ranks[$user_rank])) {
        $title = $ranks[$user_rank]['rank_title'];
        if (config()->get('forum.color_nick')) {
            $style = $ranks[$user_rank]['rank_style'];
        }
    }

    if (empty($title)) {
        $title = match ($user_id) {
            GUEST_UID => __('GUEST'),
            BOT_UID => $display_username,
            default => __('USER'),
        };
    }

    $profile = '<span title="' . htmlCHR($title) . '" class="' . $style . '">' . htmlCHR($display_username) . '</span>';
    if (!in_array($user_id, explode(',', EXCLUDED_USERS)) && !$no_link) {
        $target_blank = $target_blank ? ' target="_blank" ' : '';
        $profile = '<a ' . $target_blank . ' href="' . make_url(url()->member($user_id, $username)) . '">' . $profile . '</a>';
    }

    if (getBanInfo($user_id)) {
        return '<s>' . $profile . '</s>';
    }

    return $profile;
}

/**
 * @throws BindingResolutionException
 */
function get_avatar($user_id, $ext_id, $allow_avatar = true, $height = '', $width = '')
{
    $height = $height ? 'height="' . $height . '"' : '';
    $width = $width ? 'width="' . $width . '"' : '';

    $user_avatar = '<img src="' . make_url(config()->get('avatars.user.display_path') . config()->get('avatars.user.no_avatar')) . '" alt="' . $user_id . '" ' . $height . ' ' . $width . ' />';

    if ($user_id == BOT_UID && config()->get('avatars.user.bot_avatar')) {
        $user_avatar = '<img src="' . make_url(config()->get('avatars.user.display_path') . config()->get('avatars.user.bot_avatar')) . '" alt="' . $user_id . '" ' . $height . ' ' . $width . ' />';
    } elseif ($allow_avatar && $ext_id) {
        if (files()->isFile(get_avatar_path($user_id, $ext_id))) {
            $user_avatar = '<img src="' . make_url(get_avatar_path($user_id, $ext_id, config()->get('avatars.user.display_path'))) . '" alt="' . $user_id . '" ' . $height . ' ' . $width . ' />';
        }
    }

    return $user_avatar;
}

/**
 * Returns gender image
 */
function genderImage(int $gender): ?string
{
    if (!config()->get('gender')) {
        return false;
    }

    return match ($gender) {
        MALE => '<img src="' . theme_images('icon_male') . '" alt="' . __('GENDER_SELECT')[MALE] . '" title="' . __('GENDER_SELECT')[MALE] . '" border="0" />',
        FEMALE => '<img src="' . theme_images('icon_female') . '" alt="' . __('GENDER_SELECT')[FEMALE] . '" title="' . __('GENDER_SELECT')[FEMALE] . '" border="0" />',
        default => '<img src="' . theme_images('icon_nogender') . '" alt="' . __('GENDER_SELECT')[NOGENDER] . '" title="' . __('GENDER_SELECT')[NOGENDER] . '" border="0" />',
    };
}

function is_gold($type): string
{
    $type = (int)$type;
    $is_gold = '';

    if (!config()->get('tracker.gold_silver_enabled')) {
        return $is_gold;
    }

    switch ($type) {
        case TOR_TYPE_GOLD:
            $is_gold = '<img width="16" height="15" src="' . theme_images('icon_tor_gold') . '" alt="' . __('GOLD') . '" title="' . __('GOLD') . '" />&nbsp;';
            break;
        case TOR_TYPE_SILVER:
            $is_gold = '<img width="16" height="15" src="' . theme_images('icon_tor_silver') . '" alt="' . __('SILVER') . '" title="' . __('SILVER') . '" />&nbsp;';
            break;
        default:
            break;
    }

    return $is_gold;
}

function hash_search($hash)
{
    $hash = htmlCHR(trim($hash));
    $info_hash_where = null;

    if (!isset($hash) || !ctype_xdigit($hash)) {
        bb_die(sprintf(__('HASH_INVALID'), $hash));
    }

    $info_hash = DB()->escape(pack('H*', $hash));

    // Check info_hash version
    if (mb_strlen($hash, DEFAULT_CHARSET) == 40) {
        $info_hash_where = "WHERE info_hash = '{$info_hash}'";
    } elseif (mb_strlen($hash, DEFAULT_CHARSET) == 64) {
        $info_hash_where = "WHERE info_hash_v2 = '{$info_hash}'";
    } else {
        bb_die(sprintf(__('HASH_INVALID'), $hash));
    }

    if ($row = DB()->fetch_row('SELECT topic_id FROM ' . BB_BT_TORRENTS . " {$info_hash_where}")) {
        redirect(TOPIC_URL . $row['topic_id']);
    } else {
        bb_die(sprintf(__('HASH_NOT_FOUND'), $hash));
    }
}

/**
 * Function for checking captcha answer
 */
function bb_captcha(string $mode): bool|string
{
    $settings = config()->get('forum.captcha');
    $settings['language'] = config()->get('localization.default_lang');

    // Checking captcha settings
    if (!$settings['disabled'] && $settings['service'] !== 'text') {
        if (empty($settings['public_key']) || empty($settings['secret_key'])) {
            bb_die(__('CAPTCHA_SETTINGS'));
        }
    }

    // Selecting captcha service
    $captchaClasses = [
        'googleV2' => TorrentPier\Captcha\GoogleCaptchaV2::class,
        'googleV3' => TorrentPier\Captcha\GoogleCaptchaV3::class,
        'hCaptcha' => TorrentPier\Captcha\HCaptcha::class,
        'yandex' => TorrentPier\Captcha\YandexSmartCaptcha::class,
        'cloudflare' => TorrentPier\Captcha\CloudflareTurnstileCaptcha::class,
        'text' => TorrentPier\Captcha\TextCaptcha::class,
    ];
    if (!isset($captchaClasses[$settings['service']])) {
        bb_die(sprintf('Captcha service (%s) not supported', $settings['service']));
    }
    $captchaClass = $captchaClasses[$settings['service']];
    $captcha = new $captchaClass($settings);

    // Selection mode
    if (isset($captcha)) {
        switch ($mode) {
            case 'get':
            case 'check':
                return $captcha->{$mode}();
            default:
                bb_die(sprintf('Invalid mode: %s', $mode));
        }
    }

    return false;
}

/**
 * Escape special characters in the torrent directory name for safe HTML output
 *
 * Note: Cannot use htmlspecialchars() as it doesn't escape square brackets
 *
 * @param string $dirname Directory name to escape
 * @return string Escaped directory name safe for HTML
 */
function clean_tor_dirname(string $dirname): string
{
    return str_replace(['[', ']', '<', '>', "'"], ['&#91;', '&#93;', '&lt;', '&gt;', '&#039;'], $dirname);
}

/**
 * Get birthday icon
 */
function user_birthday_icon($user_birthday, $user_id): string
{
    $current_date = bb_date(TIMENOW, 'md', false);
    $user_birthday = ($user_id != GUEST_UID && !empty($user_birthday) && !str_starts_with((string)$user_birthday, '1900-01-01'))
        ? bb_date(strtotime((string)$user_birthday), 'md', false) : false;

    if (config()->get('birthday_enabled') && $current_date == $user_birthday) {
        return '<img src="' . theme_images('icon_birthday') . '" alt="' . __('HAPPY_BIRTHDAY') . '" title="' . __('HAPPY_BIRTHDAY') . '" border="0" />';
    }

    return '';
}

/**
 * Returns information about user ban
 */
function getBanInfo(?int $userId = null): ?array
{
    // Get bans info from datastore
    $bans = datastore()->get('ban_list');

    if (!isset($userId)) {
        return $bans;
    }

    return $bans[$userId] ?? [];
}

/**
 * Read updater file
 * @throws BindingResolutionException
 */
function readUpdaterFile(): array|bool
{
    if (!files()->isFile(UPDATER_FILE)) {
        return false;
    }

    return json_decode(files()->get(UPDATER_FILE), true);
}

/**
 * IP Geolocation API
 */
function infoByIP(string $ipAddress, int $port = 0): array
{
    if (!config()->get('services.ip2country.enabled')) {
        return [];
    }

    $ipAddress = TorrentPier\Helpers\IPHelper::decode($ipAddress);
    $cacheName = hash('xxh128', ($ipAddress . '_' . $port));

    if (!$data = CACHE('bb_ip2countries')->get($cacheName)) {
        $data = [];
        $svc = parse_url((string)config()->get('services.ip2country.endpoint'), PHP_URL_HOST) ?: 'ip2country';

        try {
            $requestOptions = [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ];

            // Add authorization header if API token is configured
            if (!empty(config()->get('services.ip2country.api_token'))) {
                $requestOptions['headers']['Authorization'] = 'Bearer ' . config()->get('services.ip2country.api_token');
            }

            $response = httpClient()->get(
                config()->get('services.ip2country.endpoint') . $ipAddress,
                $requestOptions,
            );

            if ($response->getStatusCode() === 200) {
                $json = json_decode((string)$response->getBody(), true);

                if (is_array($json) && !empty($json)) {
                    $data = [
                        'ipVersion' => $json['ipVersion'] ?? null,
                        'countryCode' => $json['countryCode'] ?? null,
                        'continent' => $json['continent'] ?? null,
                        'continentCode' => $json['continentCode'] ?? null,
                    ];
                }
            } else {
                bb_log("[{$svc}] Failed to get IP info for: {$ipAddress} (HTTP {$response->getStatusCode()})" . LOG_LF);
            }
        } catch (Exception $e) {
            bb_log("[{$svc}] " . $e->getMessage() . LOG_LF);
        }

        if (empty($data)) {
            $data = [
                'response' => false,
                'timestamp' => TIMENOW,
            ];
        }
        CACHE('bb_ip2countries')->set($cacheName, $data, 1200);
    }

    return $data;
}

/**
 * Check user registration data against spam providers
 */
function spam_check_user(string $username, string $email, string $ip): TorrentPier\Spam\SpamResult
{
    if (!config()->get('spam.enabled')) {
        return TorrentPier\Spam\SpamResult::allowed();
    }

    try {
        static $checker = null;

        if ($checker === null) {
            $providers = [];
            $spamConfig = config()->getSection('spam');

            // BannedUsers — always first, local + fast
            $cfg = $spamConfig['providers']['banned_users'] ?? [];
            if (!empty($cfg['enabled'])) {
                $providers[] = new TorrentPier\Spam\Provider\BannedUsersProvider($cfg);
            }

            // SpamPhrases
            $cfg = $spamConfig['providers']['spam_phrases'] ?? [];
            if (!empty($cfg['enabled'])) {
                $providers[] = new TorrentPier\Spam\Provider\SpamPhraseProvider($cfg);
            }

            // StopForumSpam
            $cfg = $spamConfig['providers']['stop_forum_spam'] ?? [];
            if (!empty($cfg['enabled'])) {
                $providers[] = new TorrentPier\Spam\Provider\StopForumSpamProvider($cfg);
            }

            // Project Honey Pot
            $cfg = $spamConfig['providers']['project_honeypot'] ?? [];
            if (!empty($cfg['enabled'])) {
                $providers[] = new TorrentPier\Spam\Provider\ProjectHoneyPotProvider($cfg);
            }

            // DNS Blacklists
            $cfg = $spamConfig['providers']['dns_blacklist'] ?? [];
            if (!empty($cfg['enabled'])) {
                $providers[] = new TorrentPier\Spam\Provider\DnsBlacklistProvider($cfg);
            }

            $shortCircuit = (bool)($spamConfig['short_circuit'] ?? true);
            $checker = new TorrentPier\Spam\Checker\UserChecker($providers, $shortCircuit);
        }

        return $checker->check($username, $email, $ip);
    } catch (Throwable) {
        return TorrentPier\Spam\SpamResult::allowed();
    }
}

/**
 * Check content against spam providers
 */
function spam_check_content(int $userId, string $message, array $extra = []): TorrentPier\Spam\SpamResult
{
    if (!config()->get('spam.enabled')) {
        return TorrentPier\Spam\SpamResult::allowed();
    }

    try {
        static $checker = null;

        if ($checker === null) {
            $providers = [];
            $spamConfig = config()->getSection('spam');

            // SpamPhrases (also a content provider)
            $cfg = $spamConfig['providers']['spam_phrases'] ?? [];
            if (!empty($cfg['enabled'])) {
                $providers[] = new TorrentPier\Spam\Provider\SpamPhraseProvider($cfg);
            }

            // Akismet
            $cfg = $spamConfig['providers']['akismet'] ?? [];
            if (!empty($cfg['enabled'])) {
                $providers[] = new TorrentPier\Spam\Provider\AkismetProvider($cfg);
            }

            $shortCircuit = (bool)($spamConfig['short_circuit'] ?? true);
            $checker = new TorrentPier\Spam\Checker\ContentChecker($providers, $shortCircuit);
        }

        return $checker->check($userId, $message, $extra);
    } catch (Throwable) {
        return TorrentPier\Spam\SpamResult::allowed();
    }
}
