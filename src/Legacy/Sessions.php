<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2018 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy;

/**
 * Class Sessions
 * @package TorrentPier\Legacy
 */
class Sessions
{
    /**
     * Check if session cache ignored
     *
     * @return bool
     */
    private static function ignore_cached_userdata()
    {
        return defined('IN_PM') ? true : false;
    }

    /**
     * Get userdata from cache
     *
     * @param int $id
     *
     * @return bool|array
     */
    public static function cache_get_userdata($id)
    {
        if (self::ignore_cached_userdata()) {
            return false;
        }

        return CACHE('session_cache')->get($id);
    }

    /**
     * Set userdata to cache
     *
     * @param array $userdata
     * @param bool $force
     *
     * @return bool
     */
    public static function cache_set_userdata($userdata, $force = false)
    {
        global $bb_cfg;

        if (!$userdata || (self::ignore_cached_userdata() && !$force)) {
            return false;
        }

        $id = ($userdata['user_id'] == GUEST_UID) ? $userdata['session_ip'] : $userdata['session_id'];
        return CACHE('session_cache')->set($id, $userdata, $bb_cfg['session_update_intrv']);
    }

    /**
     * Delete userdata from cache
     *
     * @param array $userdata
     *
     * @return bool
     */
    public static function cache_rm_userdata($userdata)
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
     * @param array|string $user_id
     */
    public static function cache_rm_user_sessions($user_id)
    {
        $user_id = get_id_csv($user_id);

        $rowset = DB()->fetch_rowset("SELECT session_id FROM " . BB_SESSIONS . " WHERE session_user_id IN($user_id)");

        foreach ($rowset as $row) {
            CACHE('session_cache')->rm($row['session_id']);
        }
    }

    /**
     * Update userdata in cache
     *
     * @param array $userdata
     *
     * @return bool
     */
    public static function cache_update_userdata($userdata)
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
    public static function db_update_userdata($userdata, $sql_ary, $data_already_escaped = true)
    {
        if (!$userdata) {
            return false;
        }

        $sql_args = DB()->build_array('UPDATE', $sql_ary, $data_already_escaped);
        DB()->query("UPDATE " . BB_USERS . " SET $sql_args WHERE user_id = {$userdata['user_id']}");

        if (DB()->affected_rows()) {
            self::cache_rm_userdata($userdata);
        }

        return true;
    }

    /**
     * Delete user sessions from cache and database
     *
     * @param array|string $user_id
     */
    public static function delete_user_sessions($user_id)
    {
        self::cache_rm_user_sessions($user_id);

        $user_id = get_id_csv($user_id);
        DB()->query("DELETE FROM " . BB_SESSIONS . " WHERE session_user_id IN($user_id)");
    }

    /**
     * Start user session on page header
     * @deprecated
     *
     * @param string $user_ip
     * @param int $page_id
     * @param bool $req_login
     *
     * @return array
     */
    public static function session_pagestart($user_ip = USER_IP, $page_id = 0, $req_login = false)
    {
        global $user;

        $user->session_start(array('req_login' => $req_login));

        return $user->data;
    }
}
