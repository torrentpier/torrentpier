<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy\Admin;

use TorrentPier\Legacy\Post;

/**
 * Class Common
 * @package TorrentPier\Legacy\Admin
 */
class Common
{
    /**
     * Run synchronization for all forums
     */
    public static function sync_all_forums()
    {
        foreach (DB()->fetch_rowset("SELECT forum_id FROM " . BB_FORUMS) as $row) {
            self::sync('forum', $row['forum_id']);
        }
    }

    /**
     * Forum synchronization
     *
     * @param string $type
     * @param array|string $id
     */
    public static function sync($type, $id)
    {
        switch ($type) {
            case 'forum':

                if (!$forum_csv = get_id_csv($id)) {
                    break;
                }
                // sync posts
                $tmp_sync_forums = 'tmp_sync_forums';

                DB()->query("
				CREATE TEMPORARY TABLE $tmp_sync_forums (
					forum_id           SMALLINT  UNSIGNED NOT NULL DEFAULT '0',
					forum_last_post_id INT       UNSIGNED NOT NULL DEFAULT '0',
					forum_posts        MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
					forum_topics       MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
					PRIMARY KEY (forum_id)
				) ENGINE = MEMORY
			");
                DB()->add_shutdown_query("DROP TEMPORARY TABLE IF EXISTS $tmp_sync_forums");

                // init values with zeros
                $forum_ary = explode(',', $forum_csv);
                DB()->query("REPLACE INTO $tmp_sync_forums (forum_id) VALUES(" . implode('),(', $forum_ary) . ")");

                DB()->query("
				REPLACE INTO $tmp_sync_forums
					(forum_id, forum_last_post_id, forum_posts, forum_topics)
				SELECT
					forum_id,
					MAX(topic_last_post_id),
					SUM(topic_replies) + COUNT(topic_id),
					COUNT(topic_id)
				FROM " . BB_TOPICS . "
				WHERE forum_id IN($forum_csv)
				GROUP BY forum_id
			");

                DB()->query("
				UPDATE
					$tmp_sync_forums tmp, " . BB_FORUMS . " f
				SET
					f.forum_last_post_id = tmp.forum_last_post_id,
					f.forum_posts        = tmp.forum_posts,
					f.forum_topics       = tmp.forum_topics
				WHERE
					f.forum_id = tmp.forum_id
			");

                DB()->query("DROP TEMPORARY TABLE $tmp_sync_forums");

                break;

            case 'topic':

                $all_topics = ($id === 'all');

                if (!$all_topics and !$topic_csv = get_id_csv($id)) {
                    break;
                }

                // Check for left-overs after deleted posts
                DB()->query("DELETE FROM " . BB_TOPICS . " WHERE topic_first_post_id NOT IN (SELECT post_id FROM " . BB_POSTS . ")");

                $tmp_sync_topics = 'tmp_sync_topics';

                DB()->query("
				CREATE TEMPORARY TABLE $tmp_sync_topics (
					topic_id             INT UNSIGNED NOT NULL DEFAULT '0',
					total_posts          INT UNSIGNED NOT NULL DEFAULT '0',
					topic_first_post_id  INT UNSIGNED NOT NULL DEFAULT '0',
					topic_last_post_id   INT UNSIGNED NOT NULL DEFAULT '0',
					topic_last_post_time INT UNSIGNED NOT NULL DEFAULT '0',
					topic_attachment     INT UNSIGNED NOT NULL DEFAULT '0',
					PRIMARY KEY (topic_id)
				) ENGINE = MEMORY
			");
                DB()->add_shutdown_query("DROP TEMPORARY TABLE IF EXISTS $tmp_sync_topics");

                $where_sql = (!$all_topics) ? "AND t.topic_id IN($topic_csv)" : '';

                DB()->query("
				INSERT INTO $tmp_sync_topics
				SELECT
					t.topic_id,
					COUNT(p.post_id) AS total_posts,
					MIN(p.post_id) AS topic_first_post_id,
					MAX(p.post_id) AS topic_last_post_id,
					MAX(p.post_time) AS topic_last_post_time,
					IF(t.attach_ext_id > 0, 1, 0) AS topic_attachment
				FROM      " . BB_TOPICS . " t
				LEFT JOIN " . BB_POSTS . " p ON(p.topic_id = t.topic_id)
				WHERE t.topic_status != " . TOPIC_MOVED . "
					$where_sql
				GROUP BY t.topic_id
			");

                DB()->query("
				UPDATE
					$tmp_sync_topics tmp, " . BB_TOPICS . " t
				SET
					t.topic_replies        = tmp.total_posts - 1,
					t.topic_first_post_id  = tmp.topic_first_post_id,
					t.topic_last_post_id   = tmp.topic_last_post_id,
					t.topic_last_post_time = tmp.topic_last_post_time,
					t.topic_attachment     = tmp.topic_attachment
				WHERE
					t.topic_id = tmp.topic_id
			");

                if ($topics = DB()->fetch_rowset("SELECT topic_id FROM " . $tmp_sync_topics . " WHERE total_posts = 0", 'topic_id')) {
                    self::topic_delete($topics);
                }

                DB()->query("DROP TEMPORARY TABLE $tmp_sync_topics");

                break;

            case 'user_posts':

                $all_users = ($id === 'all');

                if (!$all_users and !$user_csv = get_id_csv($id)) {
                    break;
                }

                $tmp_user_posts = 'tmp_sync_user_posts';

                DB()->query("
				CREATE TEMPORARY TABLE $tmp_user_posts (
					user_id    INT NOT NULL DEFAULT '0',
					user_posts MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
					PRIMARY KEY (user_id)
				) ENGINE = MEMORY
			");
                DB()->add_shutdown_query("DROP TEMPORARY TABLE IF EXISTS $tmp_user_posts");

                // Set posts count = 0 and then update to real count
                $where_user_sql = (!$all_users) ? "AND user_id IN($user_csv)" : "AND user_posts != 0";
                $where_post_sql = (!$all_users) ? "AND poster_id IN($user_csv)" : '';

                DB()->query("
				REPLACE INTO $tmp_user_posts
					SELECT user_id, 0
					FROM " . BB_USERS . "
					WHERE user_id != " . GUEST_UID . "
						$where_user_sql
				UNION
					SELECT poster_id, COUNT(*)
					FROM " . BB_POSTS . "
					WHERE poster_id != " . GUEST_UID . "
						$where_post_sql
					GROUP BY poster_id
			");

                DB()->query("
				UPDATE
					$tmp_user_posts tmp, " . BB_USERS . " u
				SET
					u.user_posts = tmp.user_posts
				WHERE
					u.user_id = tmp.user_id
			");

                DB()->query("DROP TEMPORARY TABLE $tmp_user_posts");

                break;
        }
    }

    /**
     * Topic deletion
     *
     * @param $mode_or_topic_id
     * @param $forum_id
     * @param $prune_time
     * @param $prune_all
     *
     * @return bool|int
     */
    public static function topic_delete($mode_or_topic_id, $forum_id = null, $prune_time = 0, $prune_all = false)
    {
        global $lang, $log_action;

        $topic_csv = [];
        $prune = ($mode_or_topic_id === 'prune');

        if (!$prune and !$topic_csv = get_id_csv($mode_or_topic_id)) {
            return false;
        }

        $log_topics = $sync_forums = [];

        if ($prune) {
            $sync_forums[$forum_id] = true;
        } else {
            $where_sql = ($forum_csv = get_id_csv($forum_id)) ? "AND forum_id IN($forum_csv)" : '';

            $sql = "
			SELECT topic_id, forum_id, topic_title, topic_status
			FROM " . BB_TOPICS . "
			WHERE topic_id IN($topic_csv)
				$where_sql
		";

            $topic_csv = [];

            foreach (DB()->fetch_rowset($sql) as $row) {
                /** @noinspection UnsupportedStringOffsetOperationsInspection */
                $topic_csv[] = $row['topic_id'];
                $log_topics[] = $row;
                $sync_forums[$row['forum_id']] = true;
            }

            if (!$topic_csv = get_id_csv($topic_csv)) {
                return false;
            }
        }

        // Get topics to delete
        $tmp_delete_topics = 'tmp_delete_topics';

        DB()->query("
		CREATE TEMPORARY TABLE $tmp_delete_topics (
			topic_id INT UNSIGNED NOT NULL DEFAULT '0',
			PRIMARY KEY (topic_id)
		) ENGINE = MEMORY
	");
        DB()->add_shutdown_query("DROP TEMPORARY TABLE IF EXISTS $tmp_delete_topics");

        $where_sql = ($prune) ? "forum_id = $forum_id" : "topic_id IN($topic_csv)";
        $where_sql .= ($prune && $prune_time) ? " AND topic_last_post_time < $prune_time" : '';
        $where_sql .= ($prune && !$prune_all) ? " AND topic_type NOT IN(" . POST_ANNOUNCE . "," . POST_STICKY . ")" : '';

        DB()->query("INSERT INTO $tmp_delete_topics SELECT topic_id FROM " . BB_TOPICS . " WHERE $where_sql");

        // Get topics count
        $row = DB()->fetch_row("SELECT COUNT(*) AS topics_count FROM $tmp_delete_topics");

        if (!$deleted_topics_count = $row['topics_count']) {
            DB()->query("DROP TEMPORARY TABLE $tmp_delete_topics");
            return 0;
        }

        // Get all topic IDs to delete from Manticore
        $manticore_topics = DB()->fetch_rowset("SELECT topic_id FROM $tmp_delete_topics");
        foreach ($manticore_topics as $topic_row) {
            sync_topic_to_manticore($topic_row['topic_id'], action: 'delete');
        }

        // Get all post IDs from topics to delete from Manticore
        $manticore_posts = DB()->fetch_rowset("
            SELECT p.post_id
            FROM $tmp_delete_topics del, " . BB_POSTS . " p
            WHERE p.topic_id = del.topic_id
        ");
        foreach ($manticore_posts as $post_row) {
            sync_post_to_manticore($post_row['post_id'], action: 'delete');
        }

        // Update user posts count
        $tmp_user_posts = 'tmp_user_posts';

        DB()->query("
		CREATE TEMPORARY TABLE $tmp_user_posts (
			user_id    INT NOT NULL DEFAULT '0',
			user_posts MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
			PRIMARY KEY (user_id)
		) ENGINE = MEMORY
	");
        DB()->add_shutdown_query("DROP TEMPORARY TABLE IF EXISTS $tmp_user_posts");

        DB()->query("
		INSERT INTO $tmp_user_posts
			SELECT p.poster_id, COUNT(p.post_id)
			FROM " . $tmp_delete_topics . " del, " . BB_POSTS . " p
			WHERE p.topic_id = del.topic_id
				AND p.poster_id != " . GUEST_UID . "
			GROUP BY p.poster_id
	");

        // Get array for atom update
        $atom_csv = [];
        foreach (DB()->fetch_rowset('SELECT user_id FROM ' . $tmp_user_posts) as $at) {
            $atom_csv[] = $at['user_id'];
        }

        DB()->query("
		UPDATE
			$tmp_user_posts tmp, " . BB_USERS . " u
		SET
			u.user_posts = u.user_posts - tmp.user_posts
		WHERE
			u.user_id = tmp.user_id
	");

        DB()->query("DROP TEMPORARY TABLE $tmp_user_posts");

        // Delete votes
        DB()->query("
		DELETE pv, pu
		FROM      " . $tmp_delete_topics . " del
		LEFT JOIN " . BB_POLL_VOTES . " pv USING(topic_id)
		LEFT JOIN " . BB_POLL_USERS . " pu USING(topic_id)
	");

        // Delete torrent files (from disk)
        $result = DB()->query("
			SELECT del.topic_id
			FROM " . $tmp_delete_topics . " del
			INNER JOIN " . BB_TOPICS . " t ON(t.topic_id = del.topic_id)
			WHERE t.attach_ext_id > 0
		");

        while ($row = DB()->fetch_next($result)) {
            $file_path = get_attach_path($row['topic_id']);
            if (is_file($file_path)) {
                @unlink($file_path);
            }
            // TorrServer integration
            if (config()->get('torr_server.enabled')) {
                $torrServer = new \TorrentPier\TorrServerAPI();
                $torrServer->removeM3U($row['topic_id']);
            }
        }
        unset($row, $result);

        // Delete posts, posts_text (from DB)
        DB()->query("
		DELETE p, pt, ps, ph
		FROM      " . $tmp_delete_topics . " del
		LEFT JOIN " . BB_POSTS . " p  ON(p.topic_id = del.topic_id)
		LEFT JOIN " . BB_POSTS_TEXT . " pt ON(pt.post_id = p.post_id)
		LEFT JOIN " . BB_POSTS_HTML . " ph ON(ph.post_id = p.post_id)
		LEFT JOIN " . BB_POSTS_SEARCH . " ps ON(ps.post_id = p.post_id)
	");

        // Delete topics, topics watch
        DB()->query("
		DELETE t, tw
		FROM      " . $tmp_delete_topics . " del
		LEFT JOIN " . BB_TOPICS . " t  USING(topic_id)
		LEFT JOIN " . BB_TOPICS_WATCH . " tw USING(topic_id)
	");

        // Delete topic moved stubs
        DB()->query("
		DELETE t
		FROM " . $tmp_delete_topics . " del, " . BB_TOPICS . " t
		WHERE t.topic_moved_id = del.topic_id
	");

        // Delete torrents
        DB()->query("
		DELETE tor, tr, dl
		FROM      " . $tmp_delete_topics . " del
		LEFT JOIN " . BB_BT_TORRENTS . " tor USING(topic_id)
		LEFT JOIN " . BB_BT_TRACKER . " tr  USING(topic_id)
		LEFT JOIN " . BB_BT_DLSTATUS . " dl  USING(topic_id)
	");

        // Log action
        if ($prune) {
            // TODO
        } else {
            foreach ($log_topics as $row) {
                if ($row['topic_status'] == TOPIC_MOVED) {
                    $row['topic_title'] = '<i>' . $lang['TOPIC_MOVED'] . '</i> ' . $row['topic_title'];
                }

                $log_action->mod('mod_topic_delete', [
                    'forum_id' => $row['forum_id'],
                    'topic_id' => $row['topic_id'],
                    'topic_title' => $row['topic_title'],
                ]);
            }
        }

        // Sync
        self::sync('forum', array_keys($sync_forums));

        DB()->query("DROP TEMPORARY TABLE $tmp_delete_topics");

        return $deleted_topics_count;
    }

    /**
     * Topic movement
     *
     * @param $topic_id
     * @param $to_forum_id
     * @param $from_forum_id
     * @param bool $leave_shadow
     * @param bool $insert_bot_msg
     * @param string $reason_move
     * @return bool
     */
    public static function topic_move($topic_id, $to_forum_id, $from_forum_id = null, bool $leave_shadow = false, bool $insert_bot_msg = false, string $reason_move = ''): bool
    {
        global $log_action;

        $to_forum_id = (int)$to_forum_id;

        // Verify input params
        if (!$topic_csv = get_id_csv($topic_id)) {
            return false;
        }
        if (!forum_exists($to_forum_id)) {
            return false;
        }
        if ($from_forum_id && (!forum_exists($from_forum_id) || $to_forum_id == $from_forum_id)) {
            return false;
        }

        // Get topics info
        $where_sql = ($forum_csv = get_id_csv($from_forum_id)) ? "AND forum_id IN($forum_csv)" : '';

        $sql = "SELECT * FROM " . BB_TOPICS . " WHERE topic_id IN($topic_csv) AND topic_status != " . TOPIC_MOVED . " $where_sql";

        $topics = [];
        $sync_forums = [$to_forum_id => true];

        foreach (DB()->fetch_rowset($sql) as $row) {
            if ($row['forum_id'] != $to_forum_id) {
                $topics[$row['topic_id']] = $row;
                $sync_forums[$row['forum_id']] = true;
            }
        }

        if (!$topics or !$topic_csv = get_id_csv(array_keys($topics))) {
            return false;
        }

        // Insert topic in the old forum that indicates that the topic has moved
        if ($leave_shadow) {
            $shadows = [];

            foreach ($topics as $topic_id => $row) {
                $shadows[] = [
                    'forum_id' => $row['forum_id'],
                    'topic_title' => $row['topic_title'],
                    'topic_poster' => $row['topic_poster'],
                    'topic_time' => TIMENOW,
                    'topic_status' => TOPIC_MOVED,
                    'topic_type' => POST_NORMAL,
                    'topic_vote' => $row['topic_vote'],
                    'topic_views' => $row['topic_views'],
                    'topic_replies' => $row['topic_replies'],
                    'topic_first_post_id' => $row['topic_first_post_id'],
                    'topic_last_post_id' => $row['topic_last_post_id'],
                    'topic_moved_id' => $topic_id,
                    'topic_last_post_time' => $row['topic_last_post_time'],
                ];
            }
            if ($sql_args = DB()->build_array('MULTI_INSERT', $shadows)) {
                DB()->query("INSERT INTO " . BB_TOPICS . $sql_args);
            }
        }

        // Get all posts in moved topics for Manticore update (before DB update)
        $all_posts_for_manticore = DB()->fetch_rowset("
            SELECT post_id
            FROM " . BB_POSTS . "
            WHERE topic_id IN($topic_csv)
        ");

        DB()->query("UPDATE " . BB_TOPICS . " SET forum_id = $to_forum_id WHERE topic_id IN($topic_csv)");
        DB()->query("UPDATE " . BB_POSTS . " SET forum_id = $to_forum_id WHERE topic_id IN($topic_csv)");
        DB()->query("UPDATE " . BB_BT_TORRENTS . " SET forum_id = $to_forum_id WHERE topic_id IN($topic_csv)");

        // Bot
        if ($insert_bot_msg) {
            foreach ($topics as $topic_id => $row) {
                Post::insert_post('after_move', $topic_id, $to_forum_id, $row['forum_id'], reason_move: $reason_move);
            }
            self::sync('topic', array_keys($topics));
        }

        // Sync
        self::sync('forum', array_keys($sync_forums));

        foreach ($topics as $topic_id => $row) {
            // Manticore [Update topic]
            sync_topic_to_manticore($topic_id, forum_id: $to_forum_id);

            // Log action
            $log_action->mod('mod_topic_move', [
                'forum_id' => $row['forum_id'],
                'forum_id_new' => $to_forum_id,
                'topic_id' => $topic_id,
                'topic_title' => $row['topic_title'],
            ]);
        }

        // Manticore [Update all posts in moved topics]
        foreach ($all_posts_for_manticore as $post_row) {
            sync_post_to_manticore($post_row['post_id'], forum_id: $to_forum_id);
        }

        return true;
    }

    /**
     * Post deletion
     *
     * @param $mode_or_post_id
     * @param $user_id
     * @param bool $exclude_first
     *
     * @return bool|int
     */
    public static function post_delete($mode_or_post_id, $user_id = null, bool $exclude_first = true)
    {
        global $log_action;

        $del_user_posts = ($mode_or_post_id === 'user'); // Delete all user posts

        // Get required params
        if ($del_user_posts) {
            if (!$user_csv = get_id_csv($user_id)) {
                return false;
            }
        } else {
            if (!$post_csv = get_id_csv($mode_or_post_id)) {
                return false;
            }

            // Filter for header messages
            if ($exclude_first) {
                $sql = "SELECT topic_first_post_id FROM " . BB_TOPICS . " WHERE topic_first_post_id IN($post_csv)";

                if ($first_posts = DB()->fetch_rowset($sql, 'topic_first_post_id')) {
                    $posts_without_first = array_diff(explode(',', $post_csv), $first_posts);

                    if (!$post_csv = get_id_csv($posts_without_first)) {
                        return false;
                    }
                }
            }
        }

        // Collect data for logs, sync..
        $log_topics = $sync_forums = $sync_topics = $sync_users = [];

        if ($del_user_posts) {
            $sync_topics = DB()->fetch_rowset("SELECT DISTINCT topic_id FROM " . BB_POSTS . " WHERE poster_id IN($user_csv)", 'topic_id');

            if ($topic_csv = get_id_csv($sync_topics)) {
                foreach (DB()->fetch_rowset("SELECT DISTINCT forum_id FROM " . BB_TOPICS . " WHERE topic_id IN($topic_csv)") as $row) {
                    $sync_forums[$row['forum_id']] = true;
                }
            }
            $sync_users = explode(',', $user_csv);
        } else {
            $sql = "
			SELECT p.topic_id, p.forum_id, t.topic_title
			FROM " . BB_POSTS . " p, " . BB_TOPICS . " t
			WHERE p.post_id IN($post_csv)
				AND t.topic_id = p.topic_id
			GROUP BY t.topic_id
		";

            foreach (DB()->fetch_rowset($sql) as $row) {
                $log_topics[] = $row;
                $sync_topics[] = $row['topic_id'];
                $sync_forums[$row['forum_id']] = true;
            }

            $sync_users = DB()->fetch_rowset("SELECT DISTINCT poster_id FROM " . BB_POSTS . " WHERE post_id IN($post_csv)", 'poster_id');
        }

        // Get all post_id for deleting
        $tmp_delete_posts = 'tmp_delete_posts';

        DB()->query("
		CREATE TEMPORARY TABLE $tmp_delete_posts (
			post_id INT UNSIGNED NOT NULL DEFAULT '0',
			PRIMARY KEY (post_id)
		) ENGINE = MEMORY
	");
        DB()->add_shutdown_query("DROP TEMPORARY TABLE IF EXISTS $tmp_delete_posts");

        if ($del_user_posts) {
            $where_sql = "poster_id IN($user_csv)";

            $exclude_posts_ary = [];
            foreach (DB()->fetch_rowset("SELECT topic_first_post_id FROM " . BB_TOPICS . " WHERE topic_poster IN($user_csv)") as $row) {
                $exclude_posts_ary[] = $row['topic_first_post_id'];
            }
            if ($exclude_posts_csv = get_id_csv($exclude_posts_ary)) {
                $where_sql .= " AND post_id NOT IN($exclude_posts_csv)";
            }
        } else {
            $where_sql = "post_id IN($post_csv)";
        }

        DB()->query("INSERT INTO $tmp_delete_posts SELECT post_id FROM " . BB_POSTS . " WHERE $where_sql");

        // Deleted posts count
        $row = DB()->fetch_row("SELECT COUNT(*) AS posts_count FROM $tmp_delete_posts");

        if (!$deleted_posts_count = $row['posts_count']) {
            DB()->query("DROP TEMPORARY TABLE $tmp_delete_posts");
            return 0;
        }

        // Delete from Manticore RT index before physical deletion
        $manticore_posts = DB()->fetch_rowset("SELECT post_id FROM $tmp_delete_posts");
        foreach ($manticore_posts as $post_row) {
            sync_post_to_manticore($post_row['post_id'], action: 'delete');
        }

        // Delete torrent files when the first post is deleted (from disk)
        // Note: Torrent files are associated with topics via topic_id, not posts
        // When a first post is deleted, the topic is usually deleted too (handled in topic_delete)
        // Here we handle orphan cases where torrents might remain

        // Delete posts, posts_text (from DB)
        // Note: Torrents are linked to topics via topic_id, not posts - they are deleted in topic_delete()
        DB()->query("
		DELETE p, pt, ps, ph
		FROM      " . $tmp_delete_posts . " del
		LEFT JOIN " . BB_POSTS . " p   ON(p.post_id  = del.post_id)
		LEFT JOIN " . BB_POSTS_TEXT . " pt  ON(pt.post_id  = del.post_id)
		LEFT JOIN " . BB_POSTS_HTML . " ph  ON(ph.post_id  = del.post_id)
		LEFT JOIN " . BB_POSTS_SEARCH . " ps  ON(ps.post_id  = del.post_id)
	");

        // Log action
        if ($del_user_posts) {
            $log_action->admin('mod_post_delete', ['log_msg' => 'user: ' . self::get_usernames_for_log($user_id) . "<br />posts: $deleted_posts_count"]);
        } elseif (!\defined('IN_CRON')) {
            foreach ($log_topics as $row) {
                $log_action->mod('mod_post_delete', ['forum_id' => $row['forum_id'], 'topic_id' => $row['topic_id'], 'topic_title' => $row['topic_title']]);
            }
        }

        // Sync
        self::sync('topic', $sync_topics);
        self::sync('forum', array_keys($sync_forums));
        self::sync('user_posts', $sync_users);

        DB()->query("DROP TEMPORARY TABLE $tmp_delete_posts");

        return $deleted_posts_count;
    }

    /**
     * User deletion
     *
     * @param array|string $user_id
     * @param bool $delete_posts
     *
     * @return bool
     */
    public static function user_delete($user_id, $delete_posts = false)
    {
        global $log_action;

        if (!$user_csv = get_id_csv($user_id)) {
            return false;
        }
        if (!$user_id = DB()->fetch_rowset("SELECT user_id FROM " . BB_USERS . " WHERE user_id IN($user_csv)", 'user_id')) {
            return false;
        }
        $user_csv = get_id_csv($user_id);

        // LOG
        $log_action->admin('adm_user_delete', ['log_msg' => self::get_usernames_for_log($user_id)]);

        // Avatar
        $result = DB()->query("SELECT user_id, avatar_ext_id FROM " . BB_USERS . " WHERE avatar_ext_id > 0 AND user_id IN($user_csv)");

        while ($row = DB()->fetch_next($result)) {
            delete_avatar($row['user_id'], $row['avatar_ext_id']);
        }

        if ($delete_posts) {
            self::post_delete('user', $user_id);
        } else {
            DB()->query("UPDATE " . BB_POSTS . " SET poster_id = " . DELETED . " WHERE poster_id IN($user_csv)");
        }

        DB()->query("UPDATE " . BB_GROUPS . " SET group_moderator = 2 WHERE group_single_user = 0 AND group_moderator IN($user_csv)");
        DB()->query("UPDATE " . BB_TOPICS . " SET topic_poster = " . DELETED . " WHERE topic_poster IN($user_csv)");
        DB()->query("UPDATE " . BB_BT_TORRENTS . " SET poster_id = " . DELETED . " WHERE poster_id IN($user_csv)");

        DB()->query("
		DELETE ug, g, a
		FROM " . BB_USER_GROUP . " ug
		LEFT JOIN " . BB_GROUPS . " g   ON(g.group_id = ug.group_id AND g.group_single_user = 1)
		LEFT JOIN " . BB_AUTH_ACCESS . " a   ON(a.group_id = g.group_id)
		WHERE ug.user_id IN($user_csv)
	");

        DB()->query("
		DELETE u, ban, pu, s, tw, asn
		FROM " . BB_USERS . " u
		LEFT JOIN " . BB_BANLIST . " ban ON(ban.ban_userid = u.user_id)
		LEFT JOIN " . BB_POLL_USERS . " pu  ON(pu.user_id = u.user_id)
		LEFT JOIN " . BB_SESSIONS . " s   ON(s.session_user_id = u.user_id)
		LEFT JOIN " . BB_TOPICS_WATCH . " tw  ON(tw.user_id = u.user_id)
		LEFT JOIN " . BB_AUTH_ACCESS_SNAP . " asn ON(asn.user_id = u.user_id)
		WHERE u.user_id IN($user_csv)
	");

        DB()->query("
		DELETE btu, tr
		FROM " . BB_BT_USERS . " btu
		LEFT JOIN " . BB_BT_TRACKER . " tr  ON(tr.user_id = btu.user_id)
		WHERE btu.user_id IN($user_csv)
	");

        // PM
        DB()->query("
		DELETE pm, pmt
		FROM " . BB_PRIVMSGS . " pm
		LEFT JOIN " . BB_PRIVMSGS_TEXT . " pmt ON(pmt.privmsgs_text_id = pm.privmsgs_id)
		WHERE pm.privmsgs_from_userid IN($user_csv)
			AND pm.privmsgs_type IN(" . PRIVMSGS_SENT_MAIL . ',' . PRIVMSGS_SAVED_OUT_MAIL . ")
	");

        DB()->query("
		DELETE pm, pmt
		FROM " . BB_PRIVMSGS . " pm
		LEFT JOIN " . BB_PRIVMSGS_TEXT . " pmt ON(pmt.privmsgs_text_id = pm.privmsgs_id)
		WHERE pm.privmsgs_to_userid IN($user_csv)
			AND pm.privmsgs_type IN(" . PRIVMSGS_READ_MAIL . ',' . PRIVMSGS_SAVED_IN_MAIL . ")
	");

        DB()->query("UPDATE " . BB_PRIVMSGS . " SET privmsgs_from_userid = " . DELETED . " WHERE privmsgs_from_userid IN($user_csv)");
        DB()->query("UPDATE " . BB_PRIVMSGS . " SET privmsgs_to_userid = " . DELETED . " WHERE privmsgs_to_userid IN($user_csv)");

        // Delete user feed / manticore data
        foreach (explode(',', $user_csv) as $user_id) {
            // Manticore [User Delete]
            sync_user_to_manticore($user_id, action: 'delete');
        }

        return true;
    }

    /**
     * Get user names for logger
     *
     * @param array|string $user_id
     *
     * @return string
     */
    private static function get_usernames_for_log($user_id)
    {
        $users_log_msg = [];

        if ($user_csv = get_id_csv($user_id)) {
            $sql = "SELECT user_id, username FROM " . BB_USERS . " WHERE user_id IN($user_csv)";

            foreach (DB()->fetch_rowset($sql) as $row) {
                $users_log_msg[] = "<b>$row[username]</b> [$row[user_id]]";
            }
        }

        return implode(', ', $users_log_msg);
    }
}
