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

/**
 * Class Torrent
 * @package TorrentPier\Legacy
 */
class Torrent
{
    /**
     * Get torrent info by attach id
     *
     * @param int $attach_id
     *
     * @return array
     */
    public static function get_torrent_info($attach_id)
    {
        global $lang;

        $attach_id = (int)$attach_id;

        $sql = "
		SELECT
			a.post_id, d.physical_filename, d.extension, d.tracker_status, d.mimetype,
			t.topic_first_post_id, t.topic_title,
			p.poster_id, p.topic_id, p.forum_id,
			f.allow_reg_tracker
		FROM
			" . BB_ATTACHMENTS . " a,
			" . BB_ATTACHMENTS_DESC . " d,
			" . BB_POSTS . " p,
			" . BB_TOPICS . " t,
			" . BB_FORUMS . " f
		WHERE
			    a.attach_id = $attach_id
			AND d.attach_id = $attach_id
			AND p.post_id = a.post_id
			AND t.topic_id = p.topic_id
			AND f.forum_id = p.forum_id
		LIMIT 1
	";

        if (!$torrent = DB()->fetch_row($sql)) {
            bb_die($lang['INVALID_ATTACH_ID']);
        }

        return $torrent;
    }

    /**
     * Check that user has access to torrent download
     *
     * @param int $forum_id
     * @param int $poster_id
     *
     * @return bool|string
     */
    private static function torrent_auth_check($forum_id, $poster_id)
    {
        global $userdata, $lang, $attach_config;

        if (IS_ADMIN) {
            return true;
        }

        $is_auth = auth(AUTH_ALL, $forum_id, $userdata);

        if ($poster_id != $userdata['user_id'] && !$is_auth['auth_mod']) {
            bb_die($lang['NOT_MODERATOR']);
        } elseif (!$is_auth['auth_view'] || !$is_auth['auth_attachments'] || $attach_config['disable_mod']) {
            bb_die(sprintf($lang['SORRY_AUTH_READ'], $is_auth['auth_read_type']));
        }
        return $is_auth;
    }

    /**
     * Unregister torrent from tracker
     *
     * @param int $attach_id
     * @param string $mode
     */
    public static function tracker_unregister($attach_id, $mode = '')
    {
        global $lang;

        $attach_id = (int)$attach_id;
        $post_id = $topic_id = $topic_title = $forum_id = null;

        // Get torrent info
        if ($torrent = self::get_torrent_info($attach_id)) {
            $post_id = $torrent['post_id'];
            $topic_id = $torrent['topic_id'];
            $forum_id = $torrent['forum_id'];
            $topic_title = $torrent['topic_title'];
        }

        if ($mode == 'request') {
            if (!$torrent) {
                bb_die($lang['TOR_NOT_FOUND']);
            }
            if (!$torrent['tracker_status']) {
                bb_die($lang['BT_UNREGISTERED_ALREADY']);
            }
            self::torrent_auth_check($forum_id, $torrent['poster_id']);
        }

        if (!$topic_id) {
            $sql = "SELECT topic_id FROM " . BB_BT_TORRENTS . " WHERE attach_id = $attach_id";

            if (!$result = DB()->sql_query($sql)) {
                bb_die('Could not query torrent information');
            }
            if ($row = DB()->sql_fetchrow($result)) {
                $topic_id = $row['topic_id'];
            }
        }

        // Unset DL-Type for topic
        if (config()->get('bt_unset_dltype_on_tor_unreg') && $topic_id) {
            $sql = "UPDATE " . BB_TOPICS . " SET topic_dl_type = " . TOPIC_DL_TYPE_NORMAL . " WHERE topic_id = $topic_id";

            if (!$result = DB()->sql_query($sql)) {
                bb_die('Could not update topics table #1');
            }
        }

        // Remove peers from tracker
        $sql = "DELETE FROM " . BB_BT_TRACKER . " WHERE topic_id = $topic_id";

        if (!DB()->sql_query($sql)) {
            bb_die('Could not delete peers');
        }

        // TorrServer integration
        if (config()->get('torr_server.enabled')) {
            $torrServer = new TorrServerAPI();
            $torrServer->removeM3U($attach_id);
        }

        // Delete torrent
        $sql = "DELETE FROM " . BB_BT_TORRENTS . " WHERE attach_id = $attach_id";

        if (!DB()->sql_query($sql)) {
            bb_die('Could not delete torrent from torrents table');
        }

        // Update tracker_status
        $sql = "UPDATE " . BB_ATTACHMENTS_DESC . " SET tracker_status = 0 WHERE attach_id = $attach_id";

        if (!DB()->sql_query($sql)) {
            bb_die('Could not update torrent status #1');
        }

        if ($mode == 'request') {
            set_die_append_msg($forum_id, $topic_id);
            bb_die($lang['BT_UNREGISTERED']);
        }
    }

    /**
     * Delete torrent from tracker
     *
     * @param int $attach_id
     * @param string $mode
     */
    public static function delete_torrent($attach_id, $mode = '')
    {
        global $lang, $reg_mode, $topic_id, $log_action;

        $attach_id = (int)$attach_id;
        $reg_mode = $mode;

        if (!$torrent = self::get_torrent_info($attach_id)) {
            bb_die($lang['TOR_NOT_FOUND']);
        }

        $topic_id = $torrent['topic_id'];
        $forum_id = $torrent['forum_id'];
        $poster_id = $torrent['poster_id'];

        if ($torrent['extension'] !== TORRENT_EXT) {
            bb_die($lang['NOT_TORRENT']);
        }

        self::torrent_auth_check($forum_id, $poster_id);
        self::tracker_unregister($attach_id);
        delete_attachment(0, $attach_id);

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
     * @param int $attach_id
     * @param int $new_tor_status
     */
    public static function change_tor_status($attach_id, $new_tor_status)
    {
        global $topic_id, $userdata, $lang;

        $attach_id = (int)$attach_id;
        $new_tor_status = (int)$new_tor_status;

        if (!$torrent = self::get_torrent_info($attach_id)) {
            bb_die($lang['TOR_NOT_FOUND']);
        }

        $topic_id = $torrent['topic_id'];

        self::torrent_auth_check($torrent['forum_id'], $torrent['poster_id']);

        DB()->query("
		UPDATE " . BB_BT_TORRENTS . " SET
			tor_status = $new_tor_status,
			checked_user_id = {$userdata['user_id']},
			checked_time = '" . TIMENOW . "'
		WHERE attach_id = $attach_id
		LIMIT 1
	");
    }

    /**
     * Set freeleech type for torrent
     *
     * @param int $attach_id
     * @param int $tor_status_gold
     */
    public static function change_tor_type($attach_id, $tor_status_gold)
    {
        global $topic_id, $lang;

        if (!$torrent = self::get_torrent_info($attach_id)) {
            bb_die($lang['TOR_NOT_FOUND']);
        }

        if (!IS_AM) {
            bb_die($lang['ONLY_FOR_MOD']);
        }

        $topic_id = $torrent['topic_id'];
        $tor_status_gold = (int)$tor_status_gold;

        DB()->query("UPDATE " . BB_BT_TORRENTS . " SET tor_type = $tor_status_gold WHERE topic_id = $topic_id");
    }

    /**
     * Register torrent on tracker
     *
     * @param int $attach_id
     * @param string $mode
     * @param int $tor_status
     * @param int $reg_time
     *
     * @return bool
     */
    public static function tracker_register($attach_id, $mode = '', $tor_status = TOR_NOT_APPROVED, $reg_time = TIMENOW)
    {
        global $lang, $reg_mode;

        $attach_id = (int)$attach_id;
        $reg_mode = $mode;

        if (!$torrent = self::get_torrent_info($attach_id)) {
            bb_die($lang['TOR_NOT_FOUND']);
        }

        $post_id = $torrent['post_id'];
        $topic_id = $torrent['topic_id'];
        $forum_id = $torrent['forum_id'];
        $poster_id = $torrent['poster_id'];

        $info_hash = $info_hash_v2 = null;
        $info_hash_sql = $info_hash_v2_sql = $info_hash_where = null;
        $v2_hash = null;

        if ($torrent['extension'] !== TORRENT_EXT) {
            self::torrent_error_exit($lang['NOT_TORRENT']);
        }
        if (!$torrent['allow_reg_tracker']) {
            self::torrent_error_exit($lang['REG_NOT_ALLOWED_IN_THIS_FORUM']);
        }
        if ($post_id != $torrent['topic_first_post_id']) {
            self::torrent_error_exit($lang['ALLOWED_ONLY_1ST_POST_REG']);
        }
        if ($torrent['tracker_status']) {
            self::torrent_error_exit($lang['ALREADY_REG']);
        }
        if ($this_topic_torrents = self::get_registered_torrents($topic_id, 'topic')) {
            self::torrent_error_exit($lang['ONLY_1_TOR_PER_TOPIC']);
        }

        self::torrent_auth_check($forum_id, $torrent['poster_id']);

        $filename = get_attachments_dir() . '/' . $torrent['physical_filename'];

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
            $tor['info']['private'] = (int)1;
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
            if ($torrServer->uploadTorrent($filename, $torrent['mimetype'])) {
                $torrServer->saveM3U($attach_id, bin2hex($info_hash ?? $info_hash_v2));
            }
        }

        if ($row = DB()->fetch_row("SELECT topic_id FROM " . BB_BT_TORRENTS . " $info_hash_where LIMIT 1")) {
            $msg = sprintf($lang['BT_REG_FAIL_SAME_HASH'], TOPIC_URL . $row['topic_id']);
            bb_die($msg);
            set_die_append_msg($forum_id, $topic_id);
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

        $columns = 'info_hash, info_hash_v2, post_id, poster_id, topic_id, forum_id, attach_id, size, reg_time, tor_status';
        $values = "'$info_hash_sql', '$info_hash_v2_sql', $post_id, $poster_id, $topic_id, $forum_id, $attach_id, '$size', $reg_time, $tor_status";

        $sql = "INSERT INTO " . BB_BT_TORRENTS . " ($columns) VALUES ($values)";

        if (!DB()->sql_query($sql)) {
            $sql_error = DB()->sql_error();

            // Duplicate entry
            if ($sql_error['code'] == 1062) {
                self::torrent_error_exit($lang['BT_REG_FAIL_SAME_HASH']);
            }
            bb_die($lang['BT_REG_FAIL']);
        }

        // update tracker status for this attachment
        $sql = 'UPDATE ' . BB_ATTACHMENTS_DESC . " SET tracker_status = 1 WHERE attach_id = $attach_id";

        if (!DB()->sql_query($sql)) {
            bb_die('Could not update torrent status #2');
        }

        // set DL-Type for topic
        if (config()->get('bt_set_dltype_on_tor_reg')) {
            $sql = 'UPDATE ' . BB_TOPICS . ' SET topic_dl_type = ' . TOPIC_DL_TYPE_DL . " WHERE topic_id = $topic_id";

            if (!$result = DB()->sql_query($sql)) {
                bb_die('Could not update topics table #2');
            }
        }

        if (config()->get('tracker.tor_topic_up')) {
            DB()->query("UPDATE " . BB_TOPICS . " SET topic_last_post_time = GREATEST(topic_last_post_time, " . (TIMENOW - 3 * 86400) . ") WHERE topic_id = $topic_id");
        }

        if ($reg_mode == 'request' || $reg_mode == 'newtopic') {
            set_die_append_msg($forum_id, $topic_id);
            bb_die(sprintf($lang['BT_REGISTERED'], DL_URL . $attach_id));
        }

        return true;
    }

    /**
     * Register torrent on tracker using topic-based storage
     *
     * @param int $topic_id Topic ID
     * @param string $mode Registration mode ('newtopic', 'request', etc.)
     * @param int $tor_status Initial torrent status
     * @param int $reg_time Registration time
     * @return bool
     */
    public static function tracker_register_by_topic(int $topic_id, string $mode = '', int $tor_status = TOR_NOT_APPROVED, int $reg_time = TIMENOW): bool
    {
        global $lang, $reg_mode, $userdata;

        $reg_mode = $mode;

        // Get topic info
        $topic = DB()->fetch_row("
            SELECT t.topic_id, t.topic_title, t.topic_poster, t.topic_first_post_id, t.forum_id, t.attach_ext_id,
                   f.allow_reg_tracker
            FROM " . BB_TOPICS . " t
            JOIN " . BB_FORUMS . " f ON f.forum_id = t.forum_id
            WHERE t.topic_id = $topic_id
            LIMIT 1
        ");

        if (!$topic) {
            bb_die($lang['TOR_NOT_FOUND']);
        }

        $forum_id = (int)$topic['forum_id'];
        $poster_id = (int)$topic['topic_poster'];
        $post_id = (int)$topic['topic_first_post_id'];

        // Validation
        if ($topic['attach_ext_id'] != 8) {
            self::torrent_error_exit($lang['NOT_TORRENT']);
        }
        if (!$topic['allow_reg_tracker']) {
            self::torrent_error_exit($lang['REG_NOT_ALLOWED_IN_THIS_FORUM']);
        }
        if (DB()->fetch_row("SELECT topic_id FROM " . BB_BT_TORRENTS . " WHERE topic_id = $topic_id LIMIT 1")) {
            self::torrent_error_exit($lang['ALREADY_REG']);
        }

        self::torrent_auth_check($forum_id, $poster_id);

        // Get torrent file from topic-based storage
        $filename = get_attach_path($topic_id);

        if (!is_file($filename)) {
            self::torrent_error_exit($lang['ERROR_NO_ATTACHMENT'] . '<br /><br />' . htmlCHR($filename));
        }

        $file_contents = file_get_contents($filename);

        try {
            $tor = Bencode::decode($file_contents, dictType: Collection::ARRAY);
        } catch (\Exception $e) {
            self::torrent_error_exit(htmlCHR("{$lang['TORFILE_INVALID']}: {$e->getMessage()}"));
        }

        // Set private flag if DHT is disabled
        if (config()->get('bt_disable_dht')) {
            $tor['info']['private'] = (int)1;
            $fp = fopen($filename, 'wb+');
            fwrite($fp, Bencode::encode($tor));
            fclose($fp);
        }

        // Announce URL check
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

        // Detect BitTorrent version
        $bt_v1 = isset($info['pieces']);
        $bt_v2 = isset($info['meta version'], $info['file tree']) && $info['meta version'] === 2 && is_array($info['file tree']);

        if (config()->get('tracker.disabled_v1_torrents') && $bt_v1 && !$bt_v2) {
            self::torrent_error_exit($lang['BT_V1_ONLY_DISALLOWED']);
        }

        if (config()->get('tracker.disabled_v2_torrents') && !$bt_v1 && $bt_v2) {
            self::torrent_error_exit($lang['BT_V2_ONLY_DISALLOWED']);
        }

        $info_hash_sql = $info_hash_v2_sql = '';
        $info_hash_where = '';
        $info_hash = $info_hash_v2 = null;

        // Calculate info_hash v1
        if ($bt_v1) {
            $info_hash = hash('sha1', Bencode::encode($info), true);
            $info_hash_sql = rtrim(DB()->escape($info_hash), ' ');
            $info_hash_where = "WHERE info_hash = '$info_hash_sql'";
        }

        // Calculate info_hash v2
        if ($bt_v2) {
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

        // Check for duplicate hash
        if ($row = DB()->fetch_row("SELECT topic_id FROM " . BB_BT_TORRENTS . " $info_hash_where LIMIT 1")) {
            $msg = sprintf($lang['BT_REG_FAIL_SAME_HASH'], TOPIC_URL . $row['topic_id']);
            set_die_append_msg($forum_id, $topic_id);
            bb_die($msg);
        }

        // Calculate total size
        $totallen = self::calculate_torrent_size($info, $bt_v1, $bt_v2);
        $size = sprintf('%.0f', (float)$totallen);

        // Insert into tracker table
        $columns = 'info_hash, info_hash_v2, post_id, poster_id, topic_id, forum_id, attach_id, size, reg_time, tor_status';
        $values = "'$info_hash_sql', '$info_hash_v2_sql', $post_id, $poster_id, $topic_id, $forum_id, 0, '$size', $reg_time, $tor_status";

        $sql = "INSERT INTO " . BB_BT_TORRENTS . " ($columns) VALUES ($values)";

        if (!DB()->sql_query($sql)) {
            $sql_error = DB()->sql_error();
            if ($sql_error['code'] == 1062) {
                self::torrent_error_exit($lang['BT_REG_FAIL_SAME_HASH']);
            }
            bb_die($lang['BT_REG_FAIL']);
        }

        // Set DL-Type for topic
        if (config()->get('bt_set_dltype_on_tor_reg')) {
            DB()->query('UPDATE ' . BB_TOPICS . ' SET topic_dl_type = ' . TOPIC_DL_TYPE_DL . " WHERE topic_id = $topic_id");
        }

        // Topic bump
        if (config()->get('tracker.tor_topic_up')) {
            DB()->query("UPDATE " . BB_TOPICS . " SET topic_last_post_time = GREATEST(topic_last_post_time, " . (TIMENOW - 3 * 86400) . ") WHERE topic_id = $topic_id");
        }

        if ($reg_mode == 'request' || $reg_mode == 'newtopic') {
            set_die_append_msg($forum_id, $topic_id);
            bb_die(sprintf($lang['BT_REGISTERED'], 'dl.php?t=' . $topic_id));
        }

        return true;
    }

    /**
     * Calculate total torrent size from info dict
     *
     * @param array $info Torrent info dictionary
     * @param bool $bt_v1 Is BitTorrent v1
     * @param bool $bt_v2 Is BitTorrent v2
     * @return float Total size in bytes
     */
    private static function calculate_torrent_size(array $info, bool $bt_v1, bool $bt_v2): float
    {
        global $lang;

        if (isset($info['length'])) {
            return (float)$info['length'];
        }

        if ($bt_v1 && !$bt_v2 && isset($info['files']) && is_array($info['files'])) {
            $totallen = 0;
            foreach ($info['files'] as $f) {
                if (!isset($f['attr']) || $f['attr'] !== 'p') {
                    if (isset($f['length']) && is_numeric($f['length'])) {
                        $totallen += $f['length'];
                    } else {
                        self::torrent_error_exit($lang['TORFILE_INVALID']);
                    }
                }
            }
            return (float)$totallen;
        }

        if ($bt_v2 && isset($info['file tree'])) {
            $fileTreeSize = function (array $array) use (&$fileTreeSize): float {
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
            return $fileTreeSize($info['file tree']);
        }

        self::torrent_error_exit($lang['TORFILE_INVALID']);
        return 0;
    }

    /**
     * Set passkey and send torrent to the browser
     *
     * @param string $filename
     */
    public static function send_torrent_with_passkey($filename)
    {
        global $attachment, $auth_pages, $userdata, $lang;

        if (!config()->get('bt_add_auth_key') || $attachment['extension'] !== TORRENT_EXT || !$size = @filesize($filename)) {
            return;
        }

        $post_id = $poster_id = $passkey_val = '';
        $user_id = $userdata['user_id'];
        $attach_id = $attachment['attach_id'];

        if (!$passkey_key = config()->get('passkey_key')) {
            bb_die('Could not add passkey (wrong config passkey_key)');
        }

        // Get $post_id & $poster_id
        foreach ($auth_pages as $rid => $row) {
            if ($row['attach_id'] == $attach_id) {
                $post_id = $row['post_id'];
                $poster_id = $row['user_id_1'];
                break;
            }
        }

        // Get $topic_id
        $topic_id_sql = 'SELECT p.topic_id, t.topic_title
                 FROM ' . BB_POSTS . ' p
                 JOIN ' . BB_TOPICS . ' t ON p.topic_id = t.topic_id
                 WHERE p.post_id = ' . (int)$post_id;

        if (!($topic_id_result = DB()->sql_query($topic_id_sql))) {
            bb_die('Could not query post information');
        }
        $topic_id_row = DB()->sql_fetchrow($topic_id_result);
        $topic_id = $topic_id_row['topic_id'];
        $topic_title = $topic_id_row['topic_title'];

        if (!$attachment['tracker_status']) {
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

        if ($min_ratio && $user_id != $poster_id && ($user_ratio = get_bt_ratio($bt_userdata)) !== null) {
            if ($user_ratio < $min_ratio && $post_id) {
                $dl = DB()->fetch_row("
				SELECT dl.user_status
				FROM " . BB_POSTS . " p
				LEFT JOIN " . BB_BT_DLSTATUS . " dl ON dl.topic_id = p.topic_id AND dl.user_id = $user_id
				WHERE p.post_id = $post_id
				LIMIT 1
			");

                if (!isset($dl['user_status']) || $dl['user_status'] != DL_STATUS_COMPLETE) {
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

        // Replace original announce url with tracker default
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
                // Adding tracker announcer as main announcer (At start)
                array_unshift($tor['announce-list'], [$announce_url]);
            } else {
                // Adding torrent announcer (At start)
                array_unshift($tor['announce-list'], [$tor['announce']]);

                // Adding tracker announcer (At end)
                if ($tor['announce'] != $announce_url) {
                    $tor['announce-list'] = array_merge($tor['announce-list'], [[$announce_url]]);
                }
            }
        }

        // Preparing announce-list
        if (empty($tor['announce-list'])) {
            // Remove announce-list if empty
            unset($tor['announce-list']);
        } else {
            // Normalizing announce-list
            $tor['announce-list'] = array_values(array_unique($tor['announce-list'], SORT_REGULAR));
        }

        // Add publisher & topic url
        $publisher_name = config()->get('server_name');
        $publisher_url = make_url(TOPIC_URL . $topic_id);

        $tor['publisher'] = (string)$publisher_name;
        unset($tor['publisher.utf-8']);

        $tor['publisher-url'] = (string)$publisher_url;
        unset($tor['publisher-url.utf-8']);

        $tor['comment'] = (string)$publisher_url;
        unset($tor['comment.utf-8']);

        // Send torrent
        $output = Bencode::encode($tor);

        $real_filename = clean_filename(basename($attachment['real_filename']));
        if (config()->get('tracker.use_real_filename')) {
            $dl_fname = $real_filename;
        } else {
            if (config()->get('tracker.use_old_torrent_name_format')) {
                $dl_fname = '[' . config()->get('server_name') . '].t' . $topic_id . '.' . TORRENT_EXT;
            } else {
                $dl_fname = html_ent_decode($topic_title) . ' [' . config()->get('server_name') . '-' . $topic_id . ']' . '.' . TORRENT_EXT;
            }
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
     * Send torrent file with passkey using topic-based storage
     *
     * @param array $topic_data Topic data with topic_id, topic_title, poster_id, tor_status
     */
    public static function send_torrent_with_passkey_by_topic(array $topic_data): void
    {
        global $userdata, $lang;

        $topic_id = (int)$topic_data['topic_id'];
        $topic_title = $topic_data['topic_title'];
        $poster_id = (int)$topic_data['poster_id'];

        $filename = get_attach_path($topic_id);
        if (!$size = @filesize($filename)) {
            bb_die($lang['ERROR_NO_ATTACHMENT'] . ' [HDD]');
        }

        if (!config()->get('bt_add_auth_key')) {
            // Just send the file as-is
            header("Content-Type: " . TORRENT_MIMETYPE . "; name=\"t-$topic_id.torrent\"");
            header("Content-Disposition: attachment; filename=\"t-$topic_id.torrent\"");
            readfile($filename);
            exit;
        }

        $user_id = $userdata['user_id'];
        $passkey_val = '';

        if (!$passkey_key = config()->get('passkey_key')) {
            bb_die('Could not add passkey (wrong config passkey_key)');
        }

        // Check if torrent is registered on tracker
        if (!$topic_data['tor_status']) {
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

        if ($min_ratio && $user_id != $poster_id && ($user_ratio = get_bt_ratio($bt_userdata)) !== null) {
            if ($user_ratio < $min_ratio) {
                $dl = DB()->fetch_row("
                    SELECT user_status
                    FROM " . BB_BT_DLSTATUS . "
                    WHERE topic_id = $topic_id AND user_id = $user_id
                    LIMIT 1
                ");

                if (!isset($dl['user_status']) || $dl['user_status'] != DL_STATUS_COMPLETE) {
                    bb_die(sprintf($lang['BT_LOW_RATIO_FOR_DL'], round($user_ratio, 2), "search.php?dlu=$user_id&amp;dlc=1"));
                }
            }
        }

        // Torrent decoding
        $file_contents = file_get_contents($filename);
        try {
            $tor = Bencode::decode($file_contents, dictType: Collection::ARRAY);
        } catch (\Exception $e) {
            bb_die(htmlCHR("{$lang['TORFILE_INVALID']}: {$e->getMessage()}"));
        }

        // Get tracker announcer
        $announce_url = config()->get('bt_announce_url') . "?$passkey_key=$passkey_val";

        // Replace original announce url with tracker default
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
                array_unshift($tor['announce-list'], [$announce_url]);
            } else {
                array_unshift($tor['announce-list'], [$tor['announce']]);
                if ($tor['announce'] != $announce_url) {
                    $tor['announce-list'] = array_merge($tor['announce-list'], [[$announce_url]]);
                }
            }
        }

        // Preparing announce-list
        if (empty($tor['announce-list'])) {
            unset($tor['announce-list']);
        } else {
            $tor['announce-list'] = array_values(array_unique($tor['announce-list'], SORT_REGULAR));
        }

        // Add publisher & topic url
        $publisher_name = config()->get('server_name');
        $publisher_url = make_url(TOPIC_URL . $topic_id);

        $tor['publisher'] = (string)$publisher_name;
        unset($tor['publisher.utf-8']);

        $tor['publisher-url'] = (string)$publisher_url;
        unset($tor['publisher-url.utf-8']);

        $tor['comment'] = (string)$publisher_url;
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
     * Generate and save passkey for user
     *
     * @param int|string $user_id
     * @param bool $force_generate
     *
     * @return bool|string
     */
    public static function generate_passkey($user_id, bool $force_generate = false)
    {
        global $lang;

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

        $passkey_val = make_rand_str(BT_AUTH_KEY_LENGTH);
        $old_passkey = self::getPasskey($user_id);

        if (!$old_passkey) {
            // Create first passkey
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
     * @return bool
     */
    public static function tracker_rm_user($user_id)
    {
        return DB()->sql_query("DELETE FROM " . BB_BT_TRACKER . " WHERE user_id = " . (int)$user_id);
    }

    /**
     * Search and return registered torrents
     *
     * @param int $id
     * @param string $mode
     *
     * @return bool|array
     */
    private static function get_registered_torrents($id, $mode)
    {
        $field = ($mode == 'topic') ? 'topic_id' : 'post_id';

        $sql = "SELECT topic_id FROM " . BB_BT_TORRENTS . " WHERE $field = $id LIMIT 1";

        if (!$result = DB()->sql_query($sql)) {
            bb_die('Could not query torrent id');
        }

        if ($rowset = @DB()->sql_fetchrowset($result)) {
            return $rowset;
        }

        return false;
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

    /**
     * Get torrent info by topic_id (topic-based storage)
     *
     * @param int $topic_id
     * @return array|null
     */
    public static function get_torrent_info_by_topic(int $topic_id): ?array
    {
        $topic_id = (int)$topic_id;

        $sql = "
            SELECT
                t.topic_id, t.topic_title, t.topic_first_post_id, t.topic_poster, t.forum_id,
                t.topic_attachment, t.attach_ext_id,
                tor.topic_id as tor_topic_id, tor.tor_status, tor.tor_type,
                tor.poster_id, tor.checked_user_id, tor.checked_time,
                f.allow_reg_tracker
            FROM " . BB_TOPICS . " t
            LEFT JOIN " . BB_BT_TORRENTS . " tor ON tor.topic_id = t.topic_id
            JOIN " . BB_FORUMS . " f ON f.forum_id = t.forum_id
            WHERE t.topic_id = $topic_id
            LIMIT 1
        ";

        return DB()->fetch_row($sql) ?: null;
    }

    /**
     * Unregister torrent from tracker by topic_id (topic-based storage)
     *
     * @param int $topic_id
     * @param string $mode
     */
    public static function tracker_unregister_by_topic(int $topic_id, string $mode = ''): void
    {
        global $lang;

        $topic_id = (int)$topic_id;

        // Get topic info
        $topic = self::get_torrent_info_by_topic($topic_id);

        if ($mode == 'request') {
            if (!$topic) {
                bb_die($lang['TOR_NOT_FOUND']);
            }
            if (!$topic['tor_topic_id']) {
                bb_die($lang['BT_UNREGISTERED_ALREADY']);
            }
            self::torrent_auth_check($topic['forum_id'], $topic['topic_poster']);
        }

        // Unset DL-Type for topic
        if (config()->get('bt_unset_dltype_on_tor_unreg')) {
            DB()->query("UPDATE " . BB_TOPICS . " SET topic_dl_type = " . TOPIC_DL_TYPE_NORMAL . " WHERE topic_id = $topic_id");
        }

        // Remove peers from tracker
        DB()->query("DELETE FROM " . BB_BT_TRACKER . " WHERE topic_id = $topic_id");

        // TorrServer integration
        if (config()->get('torr_server.enabled')) {
            $torrServer = new TorrServerAPI();
            $torrServer->removeM3U($topic_id);
        }

        // Delete torrent from tracker table
        DB()->query("DELETE FROM " . BB_BT_TORRENTS . " WHERE topic_id = $topic_id");

        if ($mode == 'request') {
            set_die_append_msg($topic['forum_id'], $topic_id);
            bb_die($lang['BT_UNREGISTERED']);
        }
    }

    /**
     * Delete torrent by topic_id (topic-based storage)
     *
     * @param int $topic_id
     * @param string $mode
     */
    public static function delete_torrent_by_topic(int $topic_id, string $mode = ''): void
    {
        global $lang, $log_action;

        $topic_id = (int)$topic_id;

        $topic = self::get_torrent_info_by_topic($topic_id);
        if (!$topic) {
            bb_die($lang['TOR_NOT_FOUND']);
        }

        if ($topic['attach_ext_id'] != 8) {
            bb_die($lang['NOT_TORRENT']);
        }

        self::torrent_auth_check($topic['forum_id'], $topic['topic_poster']);

        // Unregister from tracker
        self::tracker_unregister_by_topic($topic_id);

        // Delete the torrent file
        TorrentUpload::delete($topic_id);

        // Clear topic attachment info
        TorrentUpload::clearTopicAttachment($topic_id);

        // Log action
        $log_action->mod('mod_topic_tor_delete', [
            'forum_id' => $topic['forum_id'],
            'topic_id' => $topic_id,
            'topic_title' => $topic['topic_title'],
        ]);
    }

    /**
     * Change torrent status by topic_id (topic-based storage)
     *
     * @param int $topic_id
     * @param int $new_tor_status
     */
    public static function change_tor_status_by_topic(int $topic_id, int $new_tor_status): void
    {
        global $userdata, $lang;

        $topic_id = (int)$topic_id;
        $new_tor_status = (int)$new_tor_status;

        $topic = self::get_torrent_info_by_topic($topic_id);
        if (!$topic || !$topic['tor_topic_id']) {
            bb_die($lang['TOR_NOT_FOUND']);
        }

        self::torrent_auth_check($topic['forum_id'], $topic['topic_poster']);

        DB()->query("
            UPDATE " . BB_BT_TORRENTS . " SET
                tor_status = $new_tor_status,
                checked_user_id = {$userdata['user_id']},
                checked_time = '" . TIMENOW . "'
            WHERE topic_id = $topic_id
            LIMIT 1
        ");
    }

    /**
     * Set freeleech type for torrent by topic_id (topic-based storage)
     *
     * @param int $topic_id
     * @param int $tor_status_gold
     */
    public static function change_tor_type_by_topic(int $topic_id, int $tor_status_gold): void
    {
        global $lang;

        $topic_id = (int)$topic_id;

        $topic = self::get_torrent_info_by_topic($topic_id);
        if (!$topic || !$topic['tor_topic_id']) {
            bb_die($lang['TOR_NOT_FOUND']);
        }

        if (!IS_AM) {
            bb_die($lang['ONLY_FOR_MOD']);
        }

        $tor_status_gold = (int)$tor_status_gold;

        DB()->query("UPDATE " . BB_BT_TORRENTS . " SET tor_type = $tor_status_gold WHERE topic_id = $topic_id");
    }
}
