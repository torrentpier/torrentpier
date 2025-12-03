<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy;

/**
 * Class Group
 * @package TorrentPier\Legacy
 */
class Group
{
    /**
     * Update user access level
     *
     * @param array|int $user_id
     */
    public static function update_user_level($user_id)
    {

        if (\is_array($user_id)) {
            $user_id = implode(',', $user_id);
        }
        $user_groups_in = ($user_id !== 'all') ? "AND ug.user_id IN($user_id)" : '';
        $users_in = ($user_id !== 'all') ? "AND  u.user_id IN($user_id)" : '';

        $tmp_table = 'tmp_levels';

        DB()->query("
		CREATE TEMPORARY TABLE $tmp_table (
			user_id MEDIUMINT NOT NULL DEFAULT '0',
			user_level TINYINT NOT NULL DEFAULT '0',
			PRIMARY KEY (user_id)
		) ENGINE = MEMORY
	");

        DB()->query("
		REPLACE INTO $tmp_table (user_id, user_level)
			SELECT u.user_id, " . USER . "
			FROM " . BB_USERS . " u
			WHERE user_level NOT IN(" . USER . "," . ADMIN . ")
				$users_in
		UNION
			SELECT DISTINCT ug.user_id, " . GROUP_MEMBER . "
			FROM " . BB_GROUPS . " g, " . BB_USER_GROUP . " ug
			WHERE g.group_single_user = 0
				AND ug.group_id = g.group_id
				AND ug.user_pending = 0
					$user_groups_in
		UNION
			SELECT DISTINCT ug.user_id, " . MOD . "
			FROM " . BB_AUTH_ACCESS . " aa, " . BB_USER_GROUP . " ug
			WHERE aa.forum_perm & " . BF_AUTH_MOD . "
				AND ug.group_id = aa.group_id
				AND ug.user_pending = 0
					$user_groups_in
	");

        DB()->query("
		UPDATE " . BB_USERS . " u, $tmp_table lev SET
			u.user_level = lev.user_level
		WHERE lev.user_id = u.user_id
			AND u.user_level NOT IN(" . ADMIN . ")
				$users_in
	");

        DB()->query("DROP TEMPORARY TABLE $tmp_table");

        self::update_user_permissions($user_id);
        self::delete_orphan_usergroups();
        datastore()->update('moderators');
    }

    /**
     * Delete group
     *
     * @param int $group_id
     */
    public static function delete_group($group_id)
    {
        $group_id = (int)$group_id;

        DB()->query("
		DELETE ug, g, aa
		FROM " . BB_USER_GROUP . " ug
		LEFT JOIN " . BB_GROUPS . " g ON(g.group_id = $group_id)
		LEFT JOIN " . BB_AUTH_ACCESS . " aa ON(aa.group_id = $group_id)
		WHERE ug.group_id = $group_id
	");

        DB()->query("UPDATE " . BB_POSTS . " SET attach_rg_sig = 0, poster_rg_id = 0 WHERE poster_rg_id = " . $group_id);

        self::update_user_level('all');
    }

    /**
     * Add user to the group
     *
     * @param int $group_id
     * @param int $user_id
     * @param int $user_pending
     * @param int $user_time
     */
    public static function add_user_into_group($group_id, $user_id, $user_pending = 0, $user_time = TIMENOW)
    {
        $args = DB()->build_array('INSERT', [
            'group_id' => (int)$group_id,
            'user_id' => (int)$user_id,
            'user_pending' => (int)$user_pending,
            'user_time' => (int)$user_time,
        ]);
        DB()->query("REPLACE INTO " . BB_USER_GROUP . $args);

        if (!$user_pending) {
            self::update_user_level($user_id);
        }
    }

    /**
     * Delete user from the group
     *
     * @param int $group_id
     * @param int $user_id
     */
    public static function delete_user_group($group_id, $user_id)
    {
        DB()->query("
		DELETE FROM " . BB_USER_GROUP . "
		WHERE user_id = " . (int)$user_id . "
			AND group_id = " . (int)$group_id . "
	");

        self::update_user_level($user_id);
    }

    /**
     * Create user in the group
     *
     * @param int $user_id
     * @return int
     */
    public static function create_user_group($user_id)
    {
        DB()->query("INSERT INTO " . BB_GROUPS . " (group_single_user) VALUES (1)");

        $group_id = (int)DB()->sql_nextid();
        $user_id = (int)$user_id;

        DB()->query("INSERT INTO " . BB_USER_GROUP . " (user_id, group_id, user_time) VALUES ($user_id, $group_id, " . TIMENOW . ")");

        return $group_id;
    }

    /**
     * Get group information
     *
     * @param string|int $group_id
     * @return array
     */
    public static function get_group_data($group_id)
    {
        if ($group_id === 'all') {
            $sql = "SELECT g.*, u.username AS moderator_name, aa.group_id AS auth_mod
			FROM " . BB_GROUPS . " g
			LEFT JOIN " . BB_USERS . " u ON(g.group_moderator = u.user_id)
			LEFT JOIN " . BB_AUTH_ACCESS . " aa ON(aa.group_id = g.group_id AND aa.forum_perm & " . BF_AUTH_MOD . ")
			WHERE g.group_single_user = 0
			GROUP BY g.group_id
			ORDER BY g.group_name";
        } else {
            $sql = "SELECT g.*, u.username AS moderator_name, aa.group_id AS auth_mod
			FROM " . BB_GROUPS . " g
			LEFT JOIN " . BB_USERS . " u ON(g.group_moderator = u.user_id)
			LEFT JOIN " . BB_AUTH_ACCESS . " aa ON(aa.group_id = g.group_id AND aa.forum_perm & " . BF_AUTH_MOD . ")
			WHERE g.group_id = " . (int)$group_id . "
				AND g.group_single_user = 0
			LIMIT 1";
        }
        $method = ($group_id === 'all') ? 'fetch_rowset' : 'fetch_row';
        return DB()->$method($sql);
    }

    /**
     * Delete user group permissions
     *
     * @param array|string $group_id
     * @param array|string $user_id
     * @param array|string $cat_id
     */
    public static function delete_permissions($group_id = null, $user_id = null, $cat_id = null)
    {
        $group_id = get_id_csv($group_id);
        $user_id = get_id_csv($user_id);
        $cat_id = get_id_csv($cat_id);

        $forums_join_sql = ($cat_id) ? "
		INNER JOIN " . BB_FORUMS . " f ON(a.forum_id = f.forum_id AND f.cat_id IN($cat_id))
	" : '';

        if ($group_id) {
            DB()->query("DELETE a FROM " . BB_AUTH_ACCESS . " a $forums_join_sql WHERE a.group_id IN($group_id)");
        }
        if ($user_id) {
            DB()->query("DELETE a FROM " . BB_AUTH_ACCESS_SNAP . " a $forums_join_sql WHERE a.user_id IN($user_id)");
        }
    }

    /**
     * Save user group permissions
     *
     * @param int $group_id
     * @param array $auth_ary
     */
    public static function store_permissions($group_id, $auth_ary)
    {
        if (empty($auth_ary) || !\is_array($auth_ary)) {
            return;
        }

        $values = [];

        foreach ($auth_ary as $forum_id => $permission) {
            $values[] = [
                'group_id' => (int)$group_id,
                'forum_id' => (int)$forum_id,
                'forum_perm' => (int)$permission,
            ];
        }
        $values = DB()->build_array('MULTI_INSERT', $values);

        DB()->query("INSERT INTO " . BB_AUTH_ACCESS . $values);
    }

    /**
     * Update user group permissions
     *
     * @param string|array $user_id
     */
    private static function update_user_permissions($user_id = 'all')
    {
        if (\is_array($user_id)) {
            $user_id = implode(',', $user_id);
        }
        $delete_in = ($user_id !== 'all') ? " WHERE user_id IN($user_id)" : '';
        $users_in = ($user_id !== 'all') ? "AND ug.user_id IN($user_id)" : '';

        DB()->query("DELETE FROM " . BB_AUTH_ACCESS_SNAP . $delete_in);

        DB()->query("
		INSERT INTO " . BB_AUTH_ACCESS_SNAP . "
			(user_id, forum_id, forum_perm)
		SELECT
			ug.user_id, aa.forum_id, BIT_OR(aa.forum_perm)
		FROM
			" . BB_USER_GROUP . " ug,
			" . BB_GROUPS . " g,
			" . BB_AUTH_ACCESS . " aa
		WHERE
			    ug.user_pending = 0
				$users_in
			AND g.group_id = ug.group_id
			AND aa.group_id = g.group_id
		GROUP BY
			ug.user_id, aa.forum_id
	");
    }

    /**
     * Delete empty groups with no users
     */
    private static function delete_orphan_usergroups()
    {
        // GROUP_SINGLE_USER without AUTH_ACCESS
        DB()->query("
		DELETE g
		FROM " . BB_GROUPS . " g
		LEFT JOIN " . BB_AUTH_ACCESS . " aa USING(group_id)
		WHERE g.group_single_user = 1
			AND aa.group_id IS NULL
	");

        // orphan USER_GROUP (against GROUP table)
        DB()->query("
		DELETE ug
		FROM " . BB_USER_GROUP . " ug
		LEFT JOIN " . BB_GROUPS . " g USING(group_id)
		WHERE g.group_id IS NULL
	");
    }
}
