<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Torrent;

use TorrentPier\Attachment;
use TorrentPier\TorrServerAPI;

use Arokettu\Bencode\Bencode;
use Arokettu\Bencode\Bencode\Collection;

use Exception;
use Nette\Database\DriverException;
use Nette\Database\UniqueConstraintViolationException;

/**
 * Torrent registration on the tracker.
 */
class Registry
{
    use HelperTrait;

    /**
     * Register torrent on tracker.
     *
     * @param int $topicId Topic ID
     * @param string $mode Registration mode (request, newtopic, or empty)
     * @param int $torStatus Initial torrent status
     * @param int $regTime Registration timestamp
     * @return bool True on success
     */
    public static function register(int $topicId, string $mode = '', int $torStatus = TOR_NOT_APPROVED, $regTime = TIMENOW): bool
    {
        global $lang, $reg_mode;

        $reg_mode = $mode;

        $torrent = self::getTorrentInfo($topicId);

        $topic_id = $torrent['topic_id'];
        $forum_id = $torrent['forum_id'];
        $poster_id = $torrent['topic_poster'];

        $info_hash = $info_hash_v2 = null;
        $info_hash_sql = $info_hash_v2_sql = $info_hash_where = null;

        if ($torrent['attach_ext_id'] !== TORRENT_EXT_ID) {
            self::errorExit($lang['NOT_TORRENT']);
        }
        if (!$torrent['allow_reg_tracker']) {
            self::errorExit($lang['REG_NOT_ALLOWED_IN_THIS_FORUM']);
        }
        if ($torrent['tracker_status']) {
            self::errorExit($lang['ALREADY_REG']);
        }

        self::checkAuth($forum_id, $torrent['topic_poster']);

        $filename = Attachment::getPath($topic_id);

        if (!is_file($filename)) {
            self::errorExit($lang['ERROR_NO_ATTACHMENT'] . '<br /><br />' . htmlCHR($filename));
        }

        $file_contents = file_get_contents($filename);

        try {
            $tor = Bencode::decode($file_contents, dictType: Collection::ARRAY);
        } catch (Exception $e) {
            self::errorExit(htmlCHR("{$lang['TORFILE_INVALID']}: {$e->getMessage()}"));
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
                self::errorExit($msg);
            }
            unset($announce_urls);
        }

        $info = $tor['info'] ?? [];

        if (!isset($info['name'], $info['piece length'])) {
            self::errorExit($lang['TORFILE_INVALID']);
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
            self::errorExit($lang['BT_V1_ONLY_DISALLOWED']);
        }

        if (config()->get('tracker.disabled_v2_torrents') && !isset($bt_v1) && isset($bt_v2)) {
            self::errorExit($lang['BT_V2_ONLY_DISALLOWED']);
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
                        self::errorExit($lang['TORFILE_INVALID']);
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
                            self::errorExit($lang['TORFILE_INVALID']);
                        }
                    }
                }

                return $size;
            };

            $totallen = (float)$fileTreeSize($info['file tree']);
        } else {
            self::errorExit($lang['TORFILE_INVALID']);
        }

        $size = sprintf('%.0f', (float)$totallen);

        try {
            DB()->table(BB_BT_TORRENTS)->insert([
                'info_hash' => $info_hash,
                'info_hash_v2' => $info_hash_v2,
                'poster_id' => $poster_id,
                'topic_id' => $topic_id,
                'forum_id' => $forum_id,
                'size' => $size,
                'reg_time' => $regTime,
                'tor_status' => $torStatus,
            ]);
        } catch (UniqueConstraintViolationException) {
            self::errorExit($lang['BT_REG_FAIL_SAME_HASH']);
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
     * Unregister torrent from tracker.
     *
     * @param int $topicId Topic ID
     * @param string $mode Unregistration mode
     */
    public static function unregister(int $topicId, string $mode = ''): void
    {
        global $lang;

        $torrent = self::getTorrentInfo($topicId);

        if ($mode == 'request') {
            if (!$torrent['tracker_status']) {
                bb_die($lang['BT_UNREGISTERED_ALREADY']);
            }
            self::checkAuth($torrent['forum_id'], $torrent['topic_poster']);
        }

        // Unset DL-Type for a topic
        if (config()->get('bt_unset_dltype_on_tor_unreg')) {
            DB()->table(BB_TOPICS)
                ->where('topic_id', $topicId)
                ->update(['topic_dl_type' => TOPIC_DL_TYPE_NORMAL]);
        }

        // Remove peers from the tracker
        DB()->table(BB_BT_TRACKER)->where('topic_id', $topicId)->delete();

        // TorrServer integration
        if (config()->get('torr_server.enabled')) {
            $torrServer = new TorrServerAPI();
            $torrServer->removeM3U($topicId);
        }

        // Delete torrent
        DB()->table(BB_BT_TORRENTS)->where('topic_id', $topicId)->delete();

        // Update tracker_status
        DB()->table(BB_TOPICS)
            ->where('topic_id', $topicId)
            ->update(['tracker_status' => 0]);

        if ($mode == 'request') {
            set_die_append_msg($torrent['forum_id'], $topicId);
            bb_die($lang['BT_UNREGISTERED']);
        }
    }

    /**
     * Delete torrent completely.
     *
     * @param int $topicId Topic ID
     * @param string $mode Deletion mode
     */
    public static function delete(int $topicId, string $mode = ''): void
    {
        global $lang, $reg_mode, $log_action;

        $reg_mode = $mode;

        $torrent = self::getTorrentInfo($topicId);

        $forum_id = $torrent['forum_id'];
        $poster_id = $torrent['topic_poster'];

        if ($torrent['attach_ext_id'] !== TORRENT_EXT_ID) {
            bb_die($lang['NOT_TORRENT']);
        }

        self::checkAuth($forum_id, $poster_id);
        self::unregister($topicId);

        // Log action
        $log_action->mod('mod_topic_tor_delete', [
            'forum_id' => $torrent['forum_id'],
            'topic_id' => $torrent['topic_id'],
            'topic_title' => $torrent['topic_title'],
        ]);
    }

}
