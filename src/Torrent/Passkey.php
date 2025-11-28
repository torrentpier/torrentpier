<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Torrent;

use Exception;

/**
 * Passkey management for torrent downloads.
 */
class Passkey
{
    /**
     * Generate a new passkey for the user.
     *
     * @param int $userId User ID
     * @param bool $forceGenerate Force generation even if the user has disabled passkey
     * @return string|false New passkey value or false on failure
     * @throws Exception
     */
    public static function generate(int $userId, bool $forceGenerate = false): string|false
    {
        global $lang;

        // Check if user can change passkey
        if (!$forceGenerate) {
            $user = DB()->table(BB_USERS)
                ->select('user_opt')
                ->where('user_id', $userId)
                ->fetch();

            if ($user && bf($user['user_opt'], 'user_opt', 'dis_passkey')) {
                bb_die($lang['NOT_AUTHORISED']);
            }
        }

        $passkey_val = make_rand_str(BT_AUTH_KEY_LENGTH);
        $old_passkey = self::get($userId);

        if (!$old_passkey) {
            // Create the first passkey
            DB()->query("INSERT IGNORE INTO " . BB_BT_USERS . " (user_id, auth_key) VALUES ($userId, '$passkey_val')");
        } else {
            // Update exists passkey
            DB()->query("UPDATE IGNORE " . BB_BT_USERS . " SET auth_key = '$passkey_val' WHERE user_id = $userId LIMIT 1");
        }

        if (DB()->affected_rows() == 1) {
            return $passkey_val;
        }

        return false;
    }

    /**
     * Get user passkey.
     *
     * @param int|string $userId User ID
     * @return string|false Passkey value or false if not found
     */
    public static function get(int|string $userId): string|false
    {
        $bt_userdata = get_bt_userdata($userId);
        if (isset($bt_userdata['auth_key'])) {
            return $bt_userdata['auth_key'];
        }

        return false;
    }
}
