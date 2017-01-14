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
 * @param $forum_id
 * @param $poster_id
 * @return bool
 */
function torrent_auth_check($forum_id, $poster_id)
{
    global $lang, $userdata;

    if (IS_ADMIN || IS_CP_HOLDER || $poster_id == $userdata['user_id']) {
        return true;
    }

    if (IS_MOD) {
        $is_auth = auth(AUTH_MOD, $forum_id, $userdata);
        if ($is_auth['auth_mod']) {
            return true;
        }
    }

    bb_die($lang['NOT_MODERATOR']);
}

/**
 * @param $topic_id
 * @param string $redirect_url
 */
function tracker_unregister($topic_id, $redirect_url = '')
{
    global $lang, $log_action;

    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    $tor = DB()->fetch_row("
		SELECT forum_id, tor_status FROM " . BB_BT_TORRENTS . " WHERE topic_id = " . intval($topic_id) . " LIMIT 1
	");
    $tor_status = isset($tor['tor_status']) ? $tor['tor_status'] : null;

    // удаление файла
    if (defined('IN_AJAX')) {
        if (is_null($tor_status)) {
            return;
        }
    } // обычная разрегистрация
    else {
        if (is_null($tor_status)) {
            bb_die('Торрент не зарегистрирован');
        }
        torrent_auth_check($tor['forum_id'], 0);
    }

    // Remove peers from tracker
    tracker_rm_torrent($topic_id);

    // Delete torrent
    DB()->query("DELETE FROM " . BB_BT_TORRENTS . " WHERE topic_id = $topic_id LIMIT 1");

    // Log action
    if (IS_AM || IS_CP_HOLDER) {
        $log_action->mod('tor_unreg', array(
            'forum_id' => $tor['forum_id'],
            'topic_id' => $topic_id,
            'topic_title' => get_topic_title($topic_id),
        ));
    }

    // Unset DL-type for topic
    if ($di->config->get('bt_unset_dltype_on_tor_unreg') && $topic_id) {
        DB()->query("UPDATE " . BB_TOPICS . " SET tracker_status = 0 WHERE topic_id = $topic_id LIMIT 1");
    }

    // Ocelot
    if ($di->config->get('ocelot.enabled')) {
        if ($row = DB()->fetch_row("SELECT info_hash FROM " . BB_BT_TORRENTS . " WHERE topic_id = $topic_id LIMIT 1")) {
            $info_hash = $row['info_hash'];
        }
        ocelot_update_tracker('delete_torrent', array('info_hash' => rawurlencode($info_hash), 'id' => $topic_id));
    }

    if (defined('IN_AJAX')) {
        return;
    } elseif ($redirect_url) {
        redirect($redirect_url);
    } else {
        set_die_append_msg($tor['forum_id'], $topic_id);
        bb_die($lang['BT_UNREGISTERED']);
    }
}

/**
 * @param $topic_id
 * @param $forum_id
 */
function torrent_cp_close($topic_id, $forum_id)
{
    global $log_action, $userdata;

    DB()->query("
		UPDATE " . BB_BT_TORRENTS . " SET
			tor_status = " . TOR_CLOSED_CPHOLD . ",
			tor_status_time = " . TIMENOW . ",
			tor_status_uid = " . $userdata['user_id'] . "
		WHERE topic_id = $topic_id
		LIMIT 1
	");

    // Remove peers from tracker
    tracker_rm_torrent($topic_id);

    $log_action->mod('tor_cphold_close', array(
        'forum_id' => $forum_id,
        'topic_id' => $topic_id,
        'topic_title' => get_topic_title($topic_id),
    ));

    require_once(INC_DIR . 'functions_admin.php');
    topic_lock_unlock($topic_id, 'lock');
}

/**
 * @param $topic_id
 * @param string $mode
 * @param int $tor_status
 * @param int $reg_time
 * @return bool|mixed|string
 */
function tracker_register($topic_id, $mode = '', $tor_status = TOR_NOT_APPROVED, $reg_time = TIMENOW)
{
    global $lang;

    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    $reg_mode = $mode;

    $sql = "
		SELECT
			t.forum_id, t.topic_poster, t.attach_ext_id, t.tracker_status,
			f.allow_reg_tracker
		FROM " . BB_TOPICS . " t, " . BB_FORUMS . " f
		WHERE t.topic_id = " . intval($topic_id) . "
			AND f.forum_id = t.forum_id
		LIMIT 1
	";
    if (!$tor = DB()->fetch_row($sql)) {
        return torrent_error_exit('Invalid topic_id');
    }
    $forum_id = $tor['forum_id'];
    $poster_id = $tor['topic_poster'];


    if ($tor['attach_ext_id'] != 8) {
        return torrent_error_exit($lang['NOT_TORRENT']);
    }
    if (!$tor['allow_reg_tracker']) {
        return torrent_error_exit($lang['REG_NOT_ALLOWED_IN_THIS_FORUM']);
    }
    if ($tor['tracker_status']) {
        return torrent_error_exit($lang['ALREADY_REG']);
    }

    if ($reg_mode != 'mcp_tor_register') {
        torrent_auth_check($forum_id, $poster_id);
    }

    $filename = get_attach_path($topic_id, 8);

    if (!file_exists($filename)) {
        return torrent_error_exit($lang['NOT_FOUND']);
    }
    if (!$tor_decoded = bdecode_file($filename)) {
        return torrent_error_exit($lang['TORFILE_INVALID']);
    }

    if ($di->config->get('bt_disable_dht')) {
        $tor_decoded['info']['private'] = (int)1;
        $fp = fopen($filename, 'w+');
        fwrite($fp, \Rych\Bencode\Bencode::encode($tor_decoded));
        fclose($fp);
    }

    $info = ($tor_decoded['info']) ? $tor_decoded['info'] : array();

    if (!isset($info['name']) || !isset($info['piece length']) || !isset($info['pieces']) || strlen($info['pieces']) % 20 != 0) {
        return torrent_error_exit($lang['TORFILE_INVALID']);
    }

    $info_hash = pack('H*', sha1(\Rych\Bencode\Bencode::encode($info)));
    $info_hash_sql = rtrim(DB()->escape($info_hash), ' ');
    $info_hash_md5 = md5($info_hash);

    // Ocelot
    if ($di->config->get('ocelot.enabled')) {
        ocelot_update_tracker('add_torrent', array('info_hash' => rawurlencode($info_hash), 'id' => $topic_id, 'freetorrent' => 0));
    }

    if ($row = DB()->fetch_row("SELECT topic_id FROM " . BB_BT_TORRENTS . " WHERE info_hash = '$info_hash_sql' LIMIT 1")) {
        return torrent_error_exit(sprintf($lang['BT_REG_FAIL_SAME_HASH'], TOPIC_URL . $row['topic_id']));
    }

    $totallen = 0;

    if (isset($info['length'])) {
        $totallen = (float)$info['length'];
    } elseif (isset($info['files']) && is_array($info['files'])) {
        foreach ($info['files'] as $fn => $f) {
            $totallen += (float)$f['length'];
        }
    } else {
        return torrent_error_exit($lang['TORFILE_INVALID']);
    }

    $size = sprintf('%.0f', (float)$totallen);

    $columns = ' info_hash,       poster_id,  topic_id,  forum_id,   size,   reg_time,  tor_status';
    $values = "'$info_hash_sql', $poster_id, $topic_id, $forum_id, '$size', $reg_time, $tor_status";

    $sql = "INSERT INTO " . BB_BT_TORRENTS . " ($columns) VALUES ($values)";

    if (!DB()->sql_query($sql)) {
        $sql_error = DB()->sql_error();

        if ($sql_error['code'] == 1062) {
            // Duplicate entry

            return torrent_error_exit($lang['BT_REG_FAIL_SAME_HASH']);
        }
        bb_die('Could not register torrent on tracker');
    }

    // Set topic status
    DB()->query("UPDATE " . BB_TOPICS . " SET tracker_status = 1 WHERE topic_id = $topic_id LIMIT 1");

    // Remove peers from tracker
    tracker_rm_torrent($topic_id);

    if ($reg_mode == 'request' || $reg_mode == 'newtopic') {
        set_die_append_msg($forum_id, $topic_id);
        bb_die(sprintf($lang['BT_REGISTERED'], DOWNLOAD_URL . $topic_id));
    } elseif ($reg_mode == 'mcp_tor_register') {
        return 'OK';
    }

    return true;
}

/**
 * @param $topic_id
 * @return bool
 */
function delete_torrent($topic_id)
{
    tracker_unregister($topic_id);
    delete_attach($topic_id, 8);

    return true;
}

/**
 * @param $topic_id
 * @param $tor_status
 */
function change_tor_status($topic_id, $tor_status)
{
    global $userdata;

    $tor_status = (int)$tor_status;

    $tor = DB()->fetch_row("SELECT forum_id, poster_id FROM " . BB_BT_TORRENTS . " WHERE topic_id = " . intval($topic_id) . " LIMIT 1");

    torrent_auth_check($tor['forum_id'], $tor['poster_id']);

    DB()->query("
		UPDATE " . BB_BT_TORRENTS . " SET
			tor_status = $tor_status,
			checked_user_id = {$userdata['user_id']},
			checked_time = '" . TIMENOW . "'
		WHERE topic_id = $topic_id
		LIMIT 1
	");
}

// Set gold / silver type for torrent
/**
 * @param $topic_id
 * @param $tor_status_gold
 */
function change_tor_type($topic_id, $tor_status_gold)
{
    global $lang;

    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    if (!IS_AM) {
        bb_die($lang['ONLY_FOR_MOD']);
    }

    $tor_status_gold = intval($tor_status_gold);
    $info_hash = null;

    DB()->query("UPDATE " . BB_BT_TORRENTS . " SET tor_type = $tor_status_gold WHERE topic_id = $topic_id LIMIT 1");

    // Ocelot
    if ($di->config->get('ocelot.enabled')) {
        if ($row = DB()->fetch_row("SELECT info_hash FROM " . BB_BT_TORRENTS . " WHERE topic_id = $topic_id LIMIT 1")) {
            $info_hash = $row['info_hash'];
        }
        ocelot_update_tracker('update_torrent', array('info_hash' => rawurlencode($info_hash), 'freetorrent' => $tor_status_gold));
    }
}

/**
 * @param $t_data
 */
function send_torrent_with_passkey($t_data)
{
    global $lang, $tr_cfg, $userdata;

    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    $topic_id = $t_data['topic_id'];
    $poster_id = $t_data['topic_poster'];
    $user_id = $userdata['user_id'];

    // Запрет на скачивание закрытого или незарегистрированного торрента
    $row = DB()->fetch_row("SELECT tor_status FROM " . BB_BT_TORRENTS . " WHERE topic_id = $topic_id LIMIT 1");

    if (!isset($row['tor_status'])) {
        bb_die($lang['PASSKEY_ERR_TOR_NOT_REG']);
    } elseif ($di->config->get('tor_frozen.' . $row['tor_status'])) {
        if (!IS_AM) {
            bb_die("Раздача имеет статус: <b>{$lang['TOR_STATUS_NAME'][$row['tor_status']]}</b><br /><br />Скачивание запрещено");
        } //TODO: перевести
    }

    $passkey_val = '';

    if ($bt_userdata = get_bt_userdata($user_id)) {
        $passkey_val = $bt_userdata['auth_key'];
    }

    if (!$passkey_val) {
        if (!$passkey_val = generate_passkey($user_id)) {
            bb_simple_die('Could not generate passkey');
        } elseif ($di->config->get('ocelot.enabled')) {
            ocelot_update_tracker('add_user', array('id' => $user_id, 'passkey' => $passkey_val));
        }
    }

    // Ratio limit for torrents dl
    $user_ratio = get_bt_ratio($bt_userdata);
    $min_ratio = $di->config->get('bt_min_ratio_allow_dl_tor');

    if ($min_ratio && $user_id != $poster_id && !is_null($user_ratio)) {
        if ($user_ratio < $min_ratio) {
            $dl = DB()->fetch_row("
				SELECT user_status FROM " . BB_BT_DLSTATUS . " WHERE topic_id = $topic_id AND user_id = $user_id LIMIT 1
			");

            if (!isset($dl['user_status']) || $dl['user_status'] != DL_STATUS_COMPLETE) {
                bb_die(sprintf($lang['BT_LOW_RATIO_FOR_DL'], round($user_ratio, 2), "search.php?dlu=$user_id&amp;dlc=1"));
            }
        }
    }

    /* TODO: восстановить
    // лимит количества скачиваний торрент-файлов в день
    if ($user_id != $poster_id && !$t_data['is_am'])
    {
        // лимит
        $daily_dls_limit = 50;

        if (!is_null($user_ratio) && $user_ratio >= 1 && $bt_userdata['u_up_total'] >= 107374182400 )// 100 GB
        {
            $daily_dls_limit = 100;
        }

        // число скачиваний
        $daily_dls_cnt = (int) DB()->fetch_row("SELECT dls_cnt FROM ". BB_USER_DLS_DAILY ." WHERE user_id = $user_id LIMIT 1", 'dls_cnt');

        if ($daily_dls_cnt >= $daily_dls_limit)
        {
            // повторное скачивание
            $can_redownload = DB()->fetch_row("SELECT 1 FROM ". BB_BT_DLS_COUNT ." WHERE topic_id = $topic_id AND user_id = $user_id LIMIT 1");

            if (!$can_redownload)
            {
                bb_log(join("\t", array(date('H:i:s'), $user->ip, $user->id, $topic_id))."\n", 'dls/'.date('m-d') .'-limit');
                set_die_append_msg(null, $topic_id);
                bb_die("Вы уже исчерпали суточный лимит скачиваний торрент-файлов<br /><br />Ваш текущий лимит: $daily_dls_limit в день");
            }
            else
            {
                bb_log(join("\t", array(date('H:i:s'), $user->ip, $user->id, $topic_id))."\n", 'dls/'.date('m-d') .'-redown');
            }
        }

        // счетчик количества скачиваний торрент-файла (для `complete_count` в BB_BT_TORRENTS)
        DB()->query("INSERT IGNORE INTO ". BB_BT_DLS_COUNT ." (topic_id, user_id) VALUES ($topic_id, $user_id)");

        // если файл еще не был скачан этим юзером, увеличиваем счетчик скачиваний
        if (DB()->affected_rows() > 0)
        {
            DB()->query("
                    INSERT IGNORE INTO ". BB_USER_DLS_DAILY ." (user_id, dls_cnt) VALUES ($user_id, 1) ON DUPLICATE KEY UPDATE dls_cnt = dls_cnt + 1
                ");
        }
    }
    */

    $filename = get_attach_path($topic_id, 8);
    if (!file_exists($filename)) {
        bb_simple_die($lang['NOT_FOUND']);
    }
    if (!$tor = bdecode_file($filename)) {
        bb_simple_die($lang['TORFILE_INVALID']);
    }

    // tor cleanup
    unset($tor['codepage']);
    unset($tor['nodes']);

    // Announce URL
    $announce = $di->config->get('ocelot.enabled') ? strval($di->config->get('ocelot.url') . $passkey_val . "/announce") : strval($di->config->get('bt_announce_url') . "?{$di->config->get('passkey_key')}=$passkey_val");

    // Replace original announce url with tracker default
    if ($di->config->get('bt_replace_ann_url') || !isset($tor['announce'])) {
        $tor['announce'] = $announce;
    }

    // Delete all additional urls
    if ($di->config->get('bt_del_addit_ann_urls') || $di->config->get('bt_disable_dht')) {
        unset($tor['announce-list']);
    } elseif (isset($tor['announce-list'])) {
        $tor['announce-list'] = array_merge($tor['announce-list'], [[$announce]]);
    }

    // Add retracker
    if (isset($tr_cfg['retracker']) && $tr_cfg['retracker']) {
        if (bf($userdata['user_opt'], 'user_opt', 'user_retracker') || IS_GUEST) {
            if (!isset($tor['announce-list'])) {
                $tor['announce-list'] = [
                    [$announce],
                    [$tr_cfg['retracker_host']]
                ];
            } else {
                $tor['announce-list'] = array_merge($tor['announce-list'], [[$tr_cfg['retracker_host']]]);
            }
        }
    }

    // Add publisher & topic url
    $publisher_name = $di->config->get('server_name');
    $publisher_url = make_url(TOPIC_URL . $topic_id);

    $tor['publisher'] = strval($publisher_name);
    unset($tor['publisher.utf-8']);

    $tor['publisher-url'] = strval($publisher_url);
    unset($tor['publisher-url.utf-8']);

    $tor['comment'] = strval($publisher_url);
    unset($tor['comment.utf-8']);

    // Send torrent
    $output = \Rych\Bencode\Bencode::encode($tor);
    $dl_fname = '[' . $di->config->get('server_name') . '].t' . $topic_id . '.torrent';

    if (!empty($_COOKIE['explain'])) {
        $out = "attach path: $filename<br /><br />";
        $tor['info']['pieces'] = '[...] ' . strlen($tor['info']['pieces']) . ' bytes';
        $out .= print_r($tor, true);
        bb_die("<pre>$out</pre>");
    }

    header("Content-Type: application/x-bittorrent; name=\"$dl_fname\"");
    header("Content-Disposition: attachment; filename=\"$dl_fname\"");

    bb_exit($output);
}

/**
 * @param $user_id
 * @param bool $force_generate
 * @return bool|string
 */
function generate_passkey($user_id, $force_generate = false)
{
    global $lang;

    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    $user_id = (int)$user_id;

    // Check if user can change passkey
    if (!$force_generate) {
        $sql = "SELECT user_opt FROM " . BB_USERS . " WHERE user_id = $user_id LIMIT 1";

        if (!$result = DB()->sql_query($sql)) {
            bb_die('Could not query userdata for passkey');
        }
        if ($row = DB()->sql_fetchrow($result)) {
            if (bf($row['user_opt'], 'user_opt', 'dis_passkey')) {
                bb_die($lang['NOT_AUTHORISED']);
            }
        }
    }

    // Delete all active user records in tracker
    tracker_rm_user($user_id);

    for ($i = 0; $i < 20; $i++) {
        $passkey_val = make_rand_str(BT_AUTH_KEY_LENGTH);
        $old_passkey = null;

        if ($row = DB()->fetch_row("SELECT auth_key FROM " . BB_BT_USERS . " WHERE user_id = $user_id LIMIT 1")) {
            $old_passkey = $row['auth_key'];
        }

        // Insert new row
        DB()->query("INSERT IGNORE INTO " . BB_BT_USERS . " (user_id, auth_key) VALUES ($user_id, '$passkey_val')");

        if (DB()->affected_rows() == 1) {
            return $passkey_val;
        }
        // Update
        DB()->query("UPDATE IGNORE " . BB_BT_USERS . " SET auth_key = '$passkey_val' WHERE user_id = $user_id LIMIT 1");

        if (DB()->affected_rows() == 1) {
            // Ocelot
            if ($di->config->get('ocelot.enabled')) {
                ocelot_update_tracker('change_passkey', array('oldpasskey' => $old_passkey, 'newpasskey' => $passkey_val));
            }
            return $passkey_val;
        }
    }
    return false;
}

/**
 * @param $message
 * @return mixed
 */
function torrent_error_exit($message)
{
    global $reg_mode, $return_message, $lang;

    if (isset($reg_mode) && $reg_mode == 'mcp_tor_register') {
        return $message;
    }

    $msg = '';

    if (isset($reg_mode) && ($reg_mode == 'request' || $reg_mode == 'newtopic')) {
        if (isset($return_message)) {
            $msg .= $return_message . '<br /><br /><hr /><br />';
        }
        $msg .= '<b>' . $lang['BT_REG_FAIL'] . '</b><br /><br />';
    }

    bb_die($msg . $message);
}

/**
 * @param $topic_id
 * @return mixed
 */
function tracker_rm_torrent($topic_id)
{
    return DB()->sql_query("DELETE FROM " . BB_BT_TRACKER . " WHERE topic_id = " . (int)$topic_id);
}

/**
 * @param $user_id
 * @return mixed
 */
function tracker_rm_user($user_id)
{
    return DB()->sql_query("DELETE FROM " . BB_BT_TRACKER . " WHERE user_id = " . (int)$user_id);
}

/**
 * @param $action
 * @param $updates
 * @return bool
 */
function ocelot_update_tracker($action, $updates)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    $get = $di->config->get('ocelot.secret') . "/update?action=$action";

    foreach ($updates as $key => $value) {
        $get .= "&$key=$value";
    }

    $max_attempts = 3;
    $err = false;

    if (ocelot_send_request($get, $max_attempts, $err) === false) {
        return false;
    }

    return true;
}

/**
 * @param $get
 * @param int $max_attempts
 * @param bool $err
 * @return bool|int
 */
function ocelot_send_request($get, $max_attempts = 1, &$err = false)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    $header = "GET /$get HTTP/1.1\r\nConnection: Close\r\n\r\n";
    $attempts = $sleep = $success = $response = 0;
    $start_time = microtime(true);

    while (!$success && $attempts++ < $max_attempts) {
        if ($sleep) {
            sleep($sleep);
        }

        // Send request
        $file = fsockopen($di->config->get('ocelot.host'), $di->config->get('ocelot.port'), $error_num, $error_string);
        if ($file) {
            if (fwrite($file, $header) === false) {
                $err = "Failed to fwrite()";
                $sleep = 3;
                continue;
            }
        } else {
            $err = "Failed to fsockopen() - $error_num - $error_string";
            $sleep = 6;
            continue;
        }

        // Check for response
        while (!feof($file)) {
            $response .= fread($file, 1024);
        }
        $data_start = strpos($response, "\r\n\r\n") + 4;
        $data_end = strrpos($response, "\n");
        if ($data_end > $data_start) {
            $data = substr($response, $data_start, $data_end - $data_start);
        } else {
            $data = "";
        }
        $status = substr($response, $data_end + 1);
        if ($status == "success") {
            $success = true;
        }
    }

    return $success;
}

// bdecode: based on OpenTracker
/**
 * @param $filename
 * @return null|string
 */
function bdecode_file($filename)
{
    $file_contents = file_get_contents($filename);
    return bdecode($file_contents);
}

/**
 * @param $str
 * @return null|string
 */
function bdecode($str)
{
    $pos = 0;
    return bdecode_r($str, $pos);
}

/**
 * @param $str
 * @param $pos
 * @return null|string
 */
function bdecode_r($str, &$pos)
{
    $strlen = strlen($str);

    if (($pos < 0) || ($pos >= $strlen)) {
        return null;
    } elseif ($str[$pos] == 'i') {
        $pos++;
        $numlen = strspn($str, '-0123456789', $pos);
        $spos = $pos;
        $pos += $numlen;

        if (($pos >= $strlen) || ($str[$pos] != 'e')) {
            return null;
        } else {
            $pos++;
            return floatval(substr($str, $spos, $numlen));
        }
    } elseif ($str[$pos] == 'd') {
        $pos++;
        $ret = array();

        while ($pos < $strlen) {
            if ($str[$pos] == 'e') {
                $pos++;
                return $ret;
            } else {
                $key = bdecode_r($str, $pos);

                if ($key === null) {
                    return null;
                } else {
                    $val = bdecode_r($str, $pos);

                    if ($val === null) {
                        return null;
                    } elseif (!is_array($key)) {
                        $ret[$key] = $val;
                    }
                }
            }
        }
        return null;
    } elseif ($str[$pos] == 'l') {
        $pos++;
        $ret = array();

        while ($pos < $strlen) {
            if ($str[$pos] == 'e') {
                $pos++;
                return $ret;
            } else {
                $val = bdecode_r($str, $pos);

                if ($val === null) {
                    return null;
                } else {
                    $ret[] = $val;
                }
            }
        }
        return null;
    } else {
        $numlen = strspn($str, '0123456789', $pos);
        $spos = $pos;
        $pos += $numlen;

        if (($pos >= $strlen) || ($str[$pos] != ':')) {
            return null;
        } else {
            $vallen = intval(substr($str, $spos, $numlen));
            $pos++;
            $val = substr($str, $pos, $vallen);

            if (strlen($val) != $vallen) {
                return null;
            } else {
                $pos += $vallen;
                return $val;
            }
        }
    }
}
