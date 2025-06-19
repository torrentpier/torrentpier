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
 * A simple dectobase64 function
 */
function base64_pack($number)
{
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ+-';
    $base = strlen($chars);

    if ($number > 4096) {
        return;
    }

    if ($number < $base) {
        return $chars[$number];
    }

    $hexval = '';

    while ($number > 0) {
        $remainder = $number % $base;

        if ($remainder < $base) {
            $hexval = $chars[$remainder] . $hexval;
        }

        $number = floor($number / $base);
    }

    return $hexval;
}

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
 * Per Forum based Extension Group Permissions (Encode Number) -> Theoretically up to 158 Forums saveable. :)
 * We are using a base of 64, but splitting it to one-char and two-char numbers. :)
 */
function auth_pack($auth_array)
{
    $one_char_encoding = '#';
    $two_char_encoding = '.';
    $one_char = $two_char = false;
    $auth_cache = '';

    foreach ($auth_array as $i => $iValue) {
        $val = base64_pack((int)$auth_array[$i]);
        if (strlen($val) == 1 && !$one_char) {
            $auth_cache .= $one_char_encoding;
            $one_char = true;
        } elseif (strlen($val) == 2 && !$two_char) {
            $auth_cache .= $two_char_encoding;
            $two_char = true;
        }

        $auth_cache .= $val;
    }

    return $auth_cache;
}

/**
 * Reverse the auth_pack process
 */
function auth_unpack($auth_cache)
{
    $one_char_encoding = '#';
    $two_char_encoding = '.';

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
        $forum_id = base64_unpack($forum_auth);
        $auth[] = (int)$forum_id;
    }
    return $auth;
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
 * Deletes an Attachment
 */
function unlink_attach($filename, $mode = false)
{
    global $upload_dir, $attach_config;

    $filename = basename($filename);

    if ($mode == MODE_THUMBNAIL) {
        $filename = $upload_dir . '/' . THUMB_DIR . '/t_' . $filename;
    } else {
        $filename = $upload_dir . '/' . $filename;
    }

    return @unlink($filename);
}

/**
 * Physical Filename stored already ?
 */
function physical_filename_already_stored($filename)
{
    if ($filename == '') {
        return false;
    }

    $filename = basename($filename);

    $sql = 'SELECT attach_id
		FROM ' . BB_ATTACHMENTS_DESC . "
		WHERE physical_filename = '" . DB()->escape($filename) . "'
		LIMIT 1";

    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not get attachment information for filename: ' . htmlspecialchars($filename));
    }
    $num_rows = DB()->num_rows($result);
    DB()->sql_freeresult($result);

    return $num_rows != 0;
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

    $display_order = ((int)$attach_config['display_order'] == 0) ? 'DESC' : 'ASC';

    $sql = 'SELECT a.post_id, d.*
		FROM ' . BB_ATTACHMENTS . ' a, ' . BB_ATTACHMENTS_DESC . " d
		WHERE a.post_id IN ($post_id_array)
			AND a.attach_id = d.attach_id
		ORDER BY d.filetime $display_order";

    try {
        $attachments = DB()->fetch_rowset($sql);
        return $attachments ?: [];
    } catch (Exception $e) {
        error_log('get_attachments_from_post error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get allowed Extensions and their respective Values
 */
function get_extension_informations()
{
    return $GLOBALS['datastore']->get('attach_extensions');
}

//
// Sync Topic
//
function attachment_sync_topic($topics)
{
    if (is_array($topics)) {
        $topics = implode(',', $topics);
    }
    $posts_without_attach = $topics_without_attach = [];

    // Check orphan post_attachment markers
    $sql = "SELECT p.post_id
		FROM " . BB_POSTS . " p
		LEFT JOIN " . BB_ATTACHMENTS . " a USING(post_id)
		WHERE p.topic_id IN($topics)
			AND p.post_attachment = 1
			AND a.post_id IS NULL";

    if ($rowset = DB()->fetch_rowset($sql)) {
        foreach ($rowset as $row) {
            $posts_without_attach[] = $row['post_id'];
        }
        if ($posts_sql = implode(',', $posts_without_attach)) {
            DB()->query("UPDATE " . BB_POSTS . " SET post_attachment = 0 WHERE post_id IN($posts_sql)");
        }
    }

    // Update missing topic_attachment markers
    DB()->query("
		UPDATE " . BB_TOPICS . " t, " . BB_POSTS . " p SET
			t.topic_attachment = 1
		WHERE p.topic_id IN($topics)
			AND p.post_attachment = 1
			AND p.topic_id = t.topic_id
	");

    // Fix orphan topic_attachment markers
    $sql = "SELECT t.topic_id
		FROM " . BB_POSTS . " p, " . BB_TOPICS . " t
		WHERE t.topic_id = p.topic_id
			AND t.topic_id IN($topics)
			AND t.topic_attachment = 1
		GROUP BY p.topic_id
		HAVING SUM(p.post_attachment) = 0";

    if ($rowset = DB()->fetch_rowset($sql)) {
        foreach ($rowset as $row) {
            $topics_without_attach[] = $row['topic_id'];
        }
        if ($topics_sql = implode(',', $topics_without_attach)) {
            DB()->query("UPDATE " . BB_TOPICS . " SET topic_attachment = 0 WHERE topic_id IN($topics_sql)");
        }
    }
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
