<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

/**
 * Class Sessions
 * @package TorrentPier
 */
class Sessions
{
    /**
     * Get userdata from cache
     *
     * @param string $id
     *
     * @return bool|array
     */
    public static function cache_get_userdata(string $id): bool|array
    {
        if (self::ignore_cached_userdata()) {
            return false;
        }

        return CACHE('session_cache')->get($id);
    }

    /**
     * Set userdata to cache
     *
     * @param array|null $userdata
     * @param bool $force
     *
     * @return bool
     */
    public static function cache_set_userdata(?array $userdata, bool $force = false): bool
    {
        if (!$userdata || (self::ignore_cached_userdata() && !$force)) {
            return false;
        }

        $id = ($userdata['user_id'] == GUEST_UID) ? $userdata['session_ip'] : $userdata['session_id'];

        return CACHE('session_cache')->set($id, $userdata, config()->get('session_update_intrv'));
    }

    /**
     * Delete userdata from cache
     *
     * @param array $userdata
     *
     * @return bool
     */
    public static function cache_rm_userdata(array $userdata): bool
    {
        if (!$userdata) {
            return false;
        }

        $id = ($userdata['user_id'] == GUEST_UID) ? $userdata['session_ip'] : $userdata['session_id'];

        return CACHE('session_cache')->rm($id);
    }

    /**
     * Delete user sessions from cache
     *
     * @param int|string $user_id
     */
    public static function cache_rm_user_sessions(int|string $user_id): void
    {
        $user_id = get_id_csv(explode(',', (string)$user_id));

        $rowset = DB()->fetch_rowset('SELECT session_id FROM ' . BB_SESSIONS . " WHERE session_user_id IN({$user_id})");

        foreach ($rowset as $row) {
            CACHE('session_cache')->rm($row['session_id']);
        }
    }

    /**
     * Update userdata in cache
     *
     * @param array|null $userdata
     *
     * @return bool
     */
    public static function cache_update_userdata(?array $userdata): bool
    {
        return self::cache_set_userdata($userdata, true);
    }

    /**
     * Update userdata in database
     *
     * @param array $userdata
     * @param array $sql_ary
     * @param bool $data_already_escaped
     *
     * @return bool
     */
    public static function db_update_userdata(array $userdata, array $sql_ary, bool $data_already_escaped = true): bool
    {
        if (!$userdata) {
            return false;
        }

        $sql_args = DB()->build_array('UPDATE', $sql_ary, $data_already_escaped);
        DB()->query('UPDATE ' . BB_USERS . " SET {$sql_args} WHERE user_id = {$userdata['user_id']}");

        if (DB()->affected_rows()) {
            self::cache_rm_userdata($userdata);
        }

        return true;
    }

    /**
     * Delete user sessions from cache and database
     *
     * @param int|string $user_id
     */
    public static function delete_user_sessions(int|string $user_id): void
    {
        self::cache_rm_user_sessions($user_id);

        $user_id = get_id_csv(explode(',', (string)$user_id));
        DB()->query('DELETE FROM ' . BB_SESSIONS . " WHERE session_user_id IN({$user_id})");
    }

    /**
     * Check if session cache ignored
     *
     * @return bool
     */
    private static function ignore_cached_userdata(): bool
    {
        return \defined('IN_PM');
    }
}
