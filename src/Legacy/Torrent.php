<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy;

use TorrentPier\TorrServerAPI;

use Arokettu\Bencode\Bencode;
use Arokettu\Bencode\Bencode\Collection;

use Exception;
use Nette\Database\DriverException;
use Nette\Database\UniqueConstraintViolationException;

/**
 * Class Torrent
 * @package TorrentPier\Legacy
 */
class Torrent
{
    /**
     * Get torrent info by topic id
     *
     * @param int $topic_id
     *
     * @return array
     */
    public static function get_torrent_info(int $topic_id): array
    {
        global $lang;

        $row = DB()->table(BB_TOPICS)
            ->select('topic_id, topic_first_post_id, topic_title, topic_poster, forum_id, tracker_status, attach_ext_id')
            ->where('topic_id', $topic_id)
            ->fetch();

        if (!$row) {
            bb_die($lang['INVALID_TOPIC_ID']);
        }

        // Convert to array and add allow_reg_tracker from related bb_forums
        $t_data = $row->toArray();
        $t_data['allow_reg_tracker'] = $row->ref(BB_FORUMS, 'forum_id')?->allow_reg_tracker ?? 0;

        return $t_data;
    }

    /**
     * Check that the user has access to torrent download
     * @param int $forum_id
     * @param int $poster_id
     * @return void
     */
    private static function torrent_auth_check(int $forum_id, int $poster_id): void
    {
        global $userdata, $lang;

        if (IS_ADMIN) {
            return;
        }

        $is_auth = auth(AUTH_ALL, $forum_id, $userdata);

        if ($poster_id != $userdata['user_id'] && !$is_auth['auth_mod']) {
            bb_die($lang['NOT_MODERATOR']);
        } elseif (!$is_auth['auth_view'] || !$is_auth['auth_attachments']) {
            bb_die(sprintf($lang['SORRY_AUTH_READ'], $is_auth['auth_read_type']));
        }
    }

    /**
     * Unregister torrent from tracker
     *
     * @param int $topic_id
     * @param string $mode
     */
    public static function tracker_unregister(int $topic_id, string $mode = ''): void
    {
        global $lang;

        $torrent = self::get_torrent_info($topic_id);

        if ($mode == 'request') {
            if (!$torrent['tracker_status']) {
                bb_die($lang['BT_UNREGISTERED_ALREADY']);
            }
            self::torrent_auth_check($torrent['forum_id'], $torrent['topic_poster']);
        }

        // Unset DL-Type for a topic
        if (config()->get('bt_unset_dltype_on_tor_unreg')) {
            DB()->table(BB_TOPICS)
                ->where('topic_id', $topic_id)
                ->update(['topic_dl_type' => TOPIC_DL_TYPE_NORMAL]);
        }

        // Remove peers from the tracker
        DB()->table(BB_BT_TRACKER)->where('topic_id', $topic_id)->delete();

        // TorrServer integration
        if (config()->get('torr_server.enabled')) {
            $torrServer = new TorrServerAPI();
            $torrServer->removeM3U($topic_id);
        }

        // Delete torrent
        DB()->table(BB_BT_TORRENTS)->where('topic_id', $topic_id)->delete();

        // Update tracker_status
        DB()->table(BB_TOPICS)
            ->where('topic_id', $topic_id)
            ->update(['tracker_status' => 0]);

        if ($mode == 'request') {
            set_die_append_msg($torrent['forum_id'], $topic_id);
            bb_die($lang['BT_UNREGISTERED']);
        }
    }

    /**
     * Delete torrent from tracker
     *
     * @param int $topic_id
     * @param string $mode
     */
    public static function delete_torrent(int $topic_id, string $mode = ''): void
    {
        global $lang, $reg_mode, $log_action;

        $reg_mode = $mode;

        $torrent = self::get_torrent_info($topic_id);

        $forum_id = $torrent['forum_id'];
        $poster_id = $torrent['topic_poster'];

        if ($torrent['attach_ext_id'] !== TORRENT_EXT_ID) {
            bb_die($lang['NOT_TORRENT']);
        }

        self::torrent_auth_check($forum_id, $poster_id);
        self::tracker_unregister($topic_id);

        // Log action
        $log_action->mod('mod_topic_tor_delete', [
            'forum_id' => $torrent['forum_id'],
            'topic_id' => $torrent['topic_id'],
            'topic_title' => $torrent['topic_title'],
        ]);
    }

    /**
     * Change torrent status
     *
     * @param int $topic_id
     * @param int $new_tor_status
     */
    public static function change_tor_status(int $topic_id, int $new_tor_status): void
    {
        global $userdata;

        $torrent = self::get_torrent_info($topic_id);
        self::torrent_auth_check($torrent['forum_id'], $torrent['topic_poster']);

        DB()->table(BB_BT_TORRENTS)
            ->where('topic_id', $topic_id)
            ->update([
                'tor_status' => $new_tor_status,
                'checked_user_id' => $userdata['user_id'],
                'checked_time' => TIMENOW,
            ]);
    }

    /**
     * Set freeleech type for torrent
     *
     * @param int $topic_id
     * @param int $tor_status_gold
     */
    public static function change_tor_type(int $topic_id, int $tor_status_gold): void
    {
        global $lang;

        self::get_torrent_info($topic_id); // validates topic exists

        if (!IS_AM) {
            bb_die($lang['ONLY_FOR_MOD']);
        }

        DB()->table(BB_BT_TORRENTS)
            ->where('topic_id', $topic_id)
            ->update(['tor_type' => $tor_status_gold]);
    }

    /**
     * Register torrent on tracker
     *
     * @param int $topic_id
     * @param string $mode
     * @param int $tor_status
     * @param int $reg_time
     *
     * @return bool
     */
    public static function tracker_register(int $topic_id, string $mode = '', int $tor_status = TOR_NOT_APPROVED, $reg_time = TIMENOW): bool
    {
        global $lang, $reg_mode;

        $reg_mode = $mode;

        $torrent = self::get_torrent_info($topic_id);

        $post_id = $torrent['topic_first_post_id'];
        $topic_id = $torrent['topic_id'];
        $forum_id = $torrent['forum_id'];
        $poster_id = $torrent['topic_poster'];

        $info_hash = $info_hash_v2 = null;
        $info_hash_sql = $info_hash_v2_sql = $info_hash_where = null;

        if ($torrent['attach_ext_id'] !== TORRENT_EXT_ID) {
            self::torrent_error_exit($lang['NOT_TORRENT']);
        }
        if (!$torrent['allow_reg_tracker']) {
            self::torrent_error_exit($lang['REG_NOT_ALLOWED_IN_THIS_FORUM']);
        }
        if ($torrent['tracker_status']) {
            self::torrent_error_exit($lang['ALREADY_REG']);
        }

        self::torrent_auth_check($forum_id, $torrent['topic_poster']);

        $filename = get_attach_path($topic_id);

        if (!is_file($filename)) {
            self::torrent_error_exit($lang['ERROR_NO_ATTACHMENT'] . '<br /><br />' . htmlCHR($filename));
        }

        $file_contents = file_get_contents($filename);

        try {
            $tor = Bencode::decode($file_contents, dictType: Collection::ARRAY);
        } catch (Exception $e) {
            self::torrent_error_exit(htmlCHR("{$lang['TORFILE_INVALID']}: {$e->getMessage()}"));
        }

        if (config()->get('bt_disable_dht')) {
            $tor['info']['private'] = 1;
            $fp = fopen($filename, 'wb+');
            fwrite($fp, Bencode::encode($tor));
            fclose($fp);
        }

        if (config()->get('bt_check_announce_url')) {
            $announce_urls = [];
            include INC_DIR . '/torrent_announce_urls.php';

            $ann = $tor['announce'] ?? '';
            $announce_urls['main_url'] = config()->get('bt_announce_url');

            if (!$ann || !in_array($ann, $announce_urls)) {
                $msg = sprintf($lang['INVALID_ANN_URL'], htmlspecialchars($ann), $announce_urls['main_url']);
                self::torrent_error_exit($msg);
            }
            unset($announce_urls);
        }

        $info = $tor['info'] ?? [];

        if (!isset($info['name'], $info['piece length'])) {
            self::torrent_error_exit($lang['TORFILE_INVALID']);
        }

        // Check if torrent contains info_hash v2 or v1
        if (isset($info['meta version'], $info['file tree'])) {
            if ($info['meta version'] === 2 && is_array($info['file tree'])) {
                $bt_v2 = true;
            }
        }

        if (isset($info['pieces'])) {
            $bt_v1 = true;
        }

        if (config()->get('tracker.disabled_v1_torrents') && isset($bt_v1) && !isset($bt_v2)) {
            self::torrent_error_exit($lang['BT_V1_ONLY_DISALLOWED']);
        }

        if (config()->get('tracker.disabled_v2_torrents') && !isset($bt_v1) && isset($bt_v2)) {
            self::torrent_error_exit($lang['BT_V2_ONLY_DISALLOWED']);
        }

        // Getting info_hash v1
        if (isset($bt_v1)) {
            $info_hash = hash('sha1', Bencode::encode($info), true);
            $info_hash_sql = rtrim(DB()->escape($info_hash), ' ');
            $info_hash_where = "WHERE info_hash = '$info_hash_sql'";
        }

        // Getting info_hash v2
        if (isset($bt_v2)) {
            $info_hash_v2 = hash('sha256', Bencode::encode($info), true);
            $info_hash_v2_sql = rtrim(DB()->escape($info_hash_v2), ' ');
            $info_hash_where = "WHERE info_hash_v2 = '$info_hash_v2_sql'";
        }

        // TorrServer integration
        if (config()->get('torr_server.enabled')) {
            $torrServer = new TorrServerAPI();
            if ($torrServer->uploadTorrent($filename, TORRENT_MIMETYPE)) {
                $torrServer->saveM3U($topic_id, bin2hex($info_hash ?? $info_hash_v2));
            }
        }

        if ($row = DB()->fetch_row("SELECT topic_id FROM " . BB_BT_TORRENTS . " $info_hash_where LIMIT 1")) {
            $msg = sprintf($lang['BT_REG_FAIL_SAME_HASH'], TOPIC_URL . $row['topic_id']);
            set_die_append_msg($forum_id, $topic_id);
            bb_die($msg);
        }

        $totallen = 0;

        if (isset($info['length'])) {
            $totallen = (float)$info['length'];
        } elseif (isset($bt_v1, $info['files']) && !isset($bt_v2) && is_array($info['files'])) {
            foreach ($info['files'] as $fn => $f) {
                // Exclude padding files
                if (!isset($f['attr']) || $f['attr'] !== 'p') {
                    if (isset($f['length']) && is_numeric($f['length'])) {
                        $totallen += $f['length'];
                    } else {
                        self::torrent_error_exit($lang['TORFILE_INVALID']);
                    }
                }
            }
            $totallen = (float)$totallen;
        } elseif (isset($bt_v2)) {
            $fileTreeSize = function (array $array, string $name = '') use (&$fileTreeSize) {
                global $lang;

                $size = 0;
                foreach ($array as $key => $value) {
                    if (!isset($value[''])) {
                        $size += $fileTreeSize($value);
                    } else {
                        if (isset($value['']['length']) && is_numeric($value['']['length'])) {
                            $size += $value['']['length'];
                        } else {
                            self::torrent_error_exit($lang['TORFILE_INVALID']);
                        }
                    }
                }

                return $size;
            };

            $totallen = (float)$fileTreeSize($info['file tree']);
        } else {
            self::torrent_error_exit($lang['TORFILE_INVALID']);
        }

        $size = sprintf('%.0f', (float)$totallen);

        try {
            DB()->table(BB_BT_TORRENTS)->insert([
                'info_hash' => $info_hash,
                'info_hash_v2' => $info_hash_v2,
                'post_id' => $post_id,
                'poster_id' => $poster_id,
                'topic_id' => $topic_id,
                'forum_id' => $forum_id,
                'size' => $size,
                'reg_time' => $reg_time,
                'tor_status' => $tor_status,
            ]);
        } catch (UniqueConstraintViolationException) {
            self::torrent_error_exit($lang['BT_REG_FAIL_SAME_HASH']);
        } catch (DriverException) {
            bb_die($lang['BT_REG_FAIL']);
        }

        // Update tracker status
        DB()->table(BB_TOPICS)
            ->where('topic_id', $topic_id)
            ->update(['tracker_status' => 1]);

        // Set DL-Type for a topic
        if (config()->get('bt_set_dltype_on_tor_reg')) {
            DB()->table(BB_TOPICS)
                ->where('topic_id', $topic_id)
                ->update(['topic_dl_type' => TOPIC_DL_TYPE_DL]);
        }

        // Bump topic
        if (config()->get('tracker.tor_topic_up')) {
            DB()->query("UPDATE " . BB_TOPICS . " SET topic_last_post_time = GREATEST(topic_last_post_time, " . (TIMENOW - 3 * 86400) . ") WHERE topic_id = $topic_id");
        }

        if ($reg_mode == 'request' || $reg_mode == 'newtopic') {
            set_die_append_msg($forum_id, $topic_id);
            bb_die(sprintf($lang['BT_REGISTERED'], DL_URL . $topic_id));
        }

        return true;
    }

    /**
     * Set the passkey and send torrent to the browser
     *
     * @param array $t_data Topic data array with topic_id, topic_title, attach_ext_id, tracker_status, topic_poster
     */
    public static function send_torrent_with_passkey(array $t_data): void
    {
        global $userdata, $lang;

        $topic_id = $t_data['topic_id'];
        $topic_title = $t_data['topic_title'];
        $filename = get_attach_path($topic_id);

        if (!config()->get('bt_add_auth_key') || $t_data['attach_ext_id'] !== TORRENT_EXT_ID || !$size = @filesize($filename)) {
            return;
        }

        $passkey_val = '';
        $user_id = $userdata['user_id'];

        if (!$passkey_key = config()->get('passkey_key')) {
            bb_die('Could not add passkey (wrong config passkey_key)');
        }

        if (!$t_data['tracker_status']) {
            bb_die($lang['PASSKEY_ERR_TOR_NOT_REG']);
        }

        if (bf($userdata['user_opt'], 'user_opt', 'dis_passkey') && !IS_GUEST) {
            bb_die($lang['DISALLOWED']);
        }

        if ($bt_userdata = get_bt_userdata($user_id)) {
            $passkey_val = $bt_userdata['auth_key'];
        }

        // Ratio limits
        $min_ratio = config()->get('bt_min_ratio_allow_dl_tor');

        if ($min_ratio && $user_id != $t_data['topic_poster'] && ($user_ratio = get_bt_ratio($bt_userdata)) !== null) {
            if ($user_ratio < $min_ratio) {
                $dl = DB()->table(BB_BT_DLSTATUS)
                    ->select('user_status')
                    ->where('topic_id', $topic_id)
                    ->where('user_id', $user_id)
                    ->fetch();

                if (!$dl || $dl['user_status'] != DL_STATUS_COMPLETE) {
                    bb_die(sprintf($lang['BT_LOW_RATIO_FOR_DL'], round($user_ratio, 2), "search.php?dlu=$user_id&amp;dlc=1"));
                }
            }
        }

        // Torrent decoding
        $file_contents = file_get_contents($filename);
        try {
            $tor = Bencode::decode($file_contents, dictType: Collection::ARRAY);
        } catch (Exception $e) {
            bb_die(htmlCHR("{$lang['TORFILE_INVALID']}: {$e->getMessage()}"));
        }

        // Get tracker announcer
        $announce_url = config()->get('bt_announce_url') . "?$passkey_key=$passkey_val";

        // Replace the original announce url with tracker default
        if (config()->get('bt_replace_ann_url') || !isset($tor['announce'])) {
            $tor['announce'] = $announce_url;
        }

        // Creating / cleaning announce-list
        if (!isset($tor['announce-list']) || !is_array($tor['announce-list']) || config()->get('bt_del_addit_ann_urls') || config()->get('bt_disable_dht')) {
            $tor['announce-list'] = [];
        }

        // Get additional announce urls
        $additional_announce_urls = $announce_urls_add = [];
        include INC_DIR . '/torrent_announce_urls.php';

        foreach ($additional_announce_urls as $additional_announce_url) {
            $announce_urls_add[] = [$additional_announce_url];
        }
        unset($additional_announce_urls);

        // Adding additional announce urls (If present)
        if (!empty($announce_urls_add)) {
            $tor['announce-list'] = array_merge($tor['announce-list'], $announce_urls_add);
        }

        // Add retracker
        if (!empty(config()->get('tracker.retracker_host')) && config()->get('tracker.retracker')) {
            if (bf($userdata['user_opt'], 'user_opt', 'user_retracker') || IS_GUEST) {
                $tor['announce-list'] = array_merge($tor['announce-list'], [[config()->get('tracker.retracker_host')]]);
            }
        }

        // Adding tracker announcer to announce-list
        if (!empty($tor['announce-list'])) {
            if (config()->get('bt_replace_ann_url')) {
                // Adding tracker announcer as the main announcer (At start)
                array_unshift($tor['announce-list'], [$announce_url]);
            } else {
                // Adding torrent announcer (At start)
                array_unshift($tor['announce-list'], [$tor['announce']]);

                // Adding tracker announcer (At the end)
                if ($tor['announce'] != $announce_url) {
                    $tor['announce-list'] = array_merge($tor['announce-list'], [[$announce_url]]);
                }
            }
        }

        // Preparing announce-list
        if (empty($tor['announce-list'])) {
            // Remove the announce-list if empty
            unset($tor['announce-list']);
        } else {
            // Normalizing announce-list
            $tor['announce-list'] = array_values(array_unique($tor['announce-list'], SORT_REGULAR));
        }

        // Add publisher and topic url
        $publisher_name = config()->get('server_name');
        $publisher_url = make_url(TOPIC_URL . $topic_id);

        $tor['publisher'] = $publisher_name;
        unset($tor['publisher.utf-8']);

        $tor['publisher-url'] = $publisher_url;
        unset($tor['publisher-url.utf-8']);

        $tor['comment'] = $publisher_url;
        unset($tor['comment.utf-8']);

        // Send torrent
        $output = Bencode::encode($tor);

        if (config()->get('tracker.use_old_torrent_name_format')) {
            $dl_fname = '[' . config()->get('server_name') . '].t' . $topic_id . '.' . TORRENT_EXT;
        } else {
            $dl_fname = html_ent_decode($topic_title) . ' [' . config()->get('server_name') . '-' . $topic_id . ']' . '.' . TORRENT_EXT;
        }

        if (!empty($_COOKIE['explain'])) {
            $out = "attach path: $filename<br /><br />";
            $tor['info']['pieces'] = '[...] ' . strlen($tor['info']['pieces']) . ' bytes';
            $out .= print_r($tor, true);
            bb_die("<pre>$out</pre>");
        }

        header("Content-Type: " . TORRENT_MIMETYPE . "; name=\"$dl_fname\"");
        header("Content-Disposition: attachment; filename=\"$dl_fname\"");

        bb_exit($output);
    }

    /**
     * @param $user_id
     * @param bool $force_generate
     * @return false|string
     * @throws Exception
     */
    public static function generate_passkey($user_id, bool $force_generate = false): false|string
    {
        global $lang;

        $user_id = (int)$user_id;

        // Check if user can change passkey
        if (!$force_generate) {
            $user = DB()->table(BB_USERS)
                ->select('user_opt')
                ->where('user_id', $user_id)
                ->fetch();

            if ($user && bf($user['user_opt'], 'user_opt', 'dis_passkey')) {
                bb_die($lang['NOT_AUTHORISED']);
            }
        }

        $passkey_val = make_rand_str(BT_AUTH_KEY_LENGTH);
        $old_passkey = self::getPasskey($user_id);

        if (!$old_passkey) {
            // Create the first passkey
            DB()->query("INSERT IGNORE INTO " . BB_BT_USERS . " (user_id, auth_key) VALUES ($user_id, '$passkey_val')");
        } else {
            // Update exists passkey
            DB()->query("UPDATE IGNORE " . BB_BT_USERS . " SET auth_key = '$passkey_val' WHERE user_id = $user_id LIMIT 1");
        }

        if (DB()->affected_rows() == 1) {
            return $passkey_val;
        }

        return false;
    }

    /**
     * Remove user from tracker
     *
     * @param int $user_id
     *
     * @return int Number of deleted rows
     */
    public static function tracker_rm_user(int $user_id): int
    {
        return DB()->table(BB_BT_TRACKER)->where('user_id', $user_id)->delete();
    }

    /**
     * Exit with error
     *
     * @param string $message
     *
     * @return void
     */
    private static function torrent_error_exit(string $message): void
    {
        global $reg_mode, $return_message, $lang;

        $msg = '';

        if (isset($reg_mode) && ($reg_mode == 'request' || $reg_mode == 'newtopic')) {
            if (isset($return_message)) {
                $msg .= $return_message . '<br /><br /><hr/><br />';
            }
            $msg .= '<b>' . $lang['BT_REG_FAIL'] . '</b><br /><br />';
        }

        bb_die($msg . $message);
    }

    /**
     * Returns the user passkey, false otherwise
     *
     * @param int|string $user_id
     * @return bool|string
     */
    public static function getPasskey(int|string $user_id): bool|string
    {
        $bt_userdata = get_bt_userdata($user_id);
        if (isset($bt_userdata['auth_key'])) {
            return $bt_userdata['auth_key'];
        }

        return false;
    }
}
