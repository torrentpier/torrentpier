<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Torrent;

use Arokettu\Bencode\Bencode;
use Arokettu\Bencode\Bencode\Collection;

use Exception;

/**
 * Torrent file sender with passkey injection.
 */
class Sender
{
    /**
     * Send a torrent file with passkey to browser.
     *
     * @param array $t_data Topic data array with topic_id, topic_title, attach_ext_id, tracker_status, topic_poster
     */
    public static function sendWithPasskey(array $t_data): void
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
            $out = "attachment path: $filename<br /><br />";
            $tor['info']['pieces'] = '[...] ' . strlen($tor['info']['pieces']) . ' bytes';
            $out .= print_r($tor, true);
            bb_die("<pre>$out</pre>");
        }

        header("Content-Type: " . TORRENT_MIMETYPE . "; name=\"$dl_fname\"");
        header("Content-Disposition: attachment; filename=\"$dl_fname\"");

        bb_exit($output);
    }
}
