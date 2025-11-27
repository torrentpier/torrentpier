<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

/**
 * All Attachment Functions needed everywhere
 */

/**
 * base64todec function
 */
function base64_unpack($string)
{
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ+-';
    $base = strlen($chars);

    $length = strlen($string);
    $number = 0;

    for ($i = 1; $i <= $length; $i++) {
        $pos = $length - $i;
        $operand = strpos($chars, (string)$string[$pos]);
        $exponent = $base ** ($i - 1);
        $decValue = $operand * $exponent;
        $number += $decValue;
    }

    return $number;
}

/**
 * Used for determining if Forum ID is authed, please use this Function on all Posting Screens
 */
function is_forum_authed($auth_cache, $check_forum_id)
{
    $one_char_encoding = '#';
    $two_char_encoding = '.';

    if (trim($auth_cache) == '') {
        return true;
    }

    $auth = [];
    $auth_len = 1;

    for ($pos = 0, $posMax = strlen($auth_cache); $pos < $posMax; $pos += $auth_len) {
        $forum_auth = $auth_cache[$pos];
        if ($forum_auth == $one_char_encoding) {
            $auth_len = 1;
            continue;
        }

        if ($forum_auth == $two_char_encoding) {
            $auth_len = 2;
            $pos--;
            continue;
        }

        $forum_auth = substr($auth_cache, $pos, $auth_len);
        $forum_id = (int)base64_unpack($forum_auth);
        if ($forum_id == $check_forum_id) {
            return true;
        }
    }
    return false;
}

/**
 * get all attachments from a post (could be an post array too)
 */
function get_attachments_from_post($post_id_array)
{
    global $attach_config;

    $attachments = [];

    if (!is_array($post_id_array)) {
        if (empty($post_id_array)) {
            return $attachments;
        }

        $post_id = (int)$post_id_array;

        $post_id_array = [];
        $post_id_array[] = $post_id;
    }

    $post_id_array = implode(', ', array_map('\intval', $post_id_array));

    if ($post_id_array == '') {
        return $attachments;
    }

    $sql = 'SELECT a.post_id, d.*
		FROM ' . BB_ATTACHMENTS . ' a, ' . BB_ATTACHMENTS_DESC . " d
		WHERE a.post_id IN ($post_id_array)
			AND a.attach_id = d.attach_id
		ORDER BY d.filetime ASC";

    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not get attachment informations for post number ' . $post_id_array);
    }

    $num_rows = DB()->num_rows($result);
    $attachments = DB()->sql_fetchrowset($result);
    DB()->sql_freeresult($result);

    if ($num_rows == 0) {
        return [];
    }

    return $attachments;
}



/**
 * _set_var
 *
 * Set variable, used by {@link get_var the get_var function}
 *
 * @private
 */
function _set_var(&$result, $var, $type, $multibyte = false)
{
    settype($var, $type);
    $result = $var;

    if ($type == 'string') {
        $result = trim(str_replace(["\r\n", "\r", '\xFF'], ["\n", "\n", ' '], $result));
        // 2.0.x is doing addslashes on all variables
        $result = stripslashes($result);
        if ($multibyte) {
            $result = preg_replace('#&amp;(\#[0-9]+;)#', '&\1', $result);
        }
    }
}

/**
 * Used to get passed variable
 *
 * @param $var_name
 * @param $default
 * @param bool $multibyte
 * @return array|string
 */
function get_var($var_name, $default, $multibyte = false)
{
    $type = null;
    if (!isset($_REQUEST[$var_name]) ||
        (is_array($_REQUEST[$var_name]) && !is_array($default)) ||
        (is_array($default) && !is_array($_REQUEST[$var_name]))) {
        return (is_array($default)) ? [] : $default;
    }

    $var = $_REQUEST[$var_name];

    if (!is_array($default)) {
        $type = gettype($default);
        $key_type = null;
    } else {
        foreach ($default as $key_type => $type) {
            $key_type = gettype($key_type);
            $type = gettype($type);
        }
    }

    if (is_array($var)) {
        $_var = $var;
        $var = [];

        foreach ($_var as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $_k => $_v) {
                    _set_var($k, $k, $key_type);
                    _set_var($_k, $_k, $key_type);
                    _set_var($var[$k][$_k], $_v, $type, $multibyte);
                }
            } else {
                _set_var($k, $k, $key_type);
                _set_var($var[$k], $v, $type, $multibyte);
            }
        }
    } else {
        _set_var($var, $var, $type, $multibyte);
    }

    return $var;
}
