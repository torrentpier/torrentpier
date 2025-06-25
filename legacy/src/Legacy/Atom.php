<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine.
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 *
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 *
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy;

/**
 * Class Atom.
 */
class Atom
{
    /**
     * Update forum feed.
     *
     * @param int   $forum_id
     * @param array $forum_data
     *
     * @return bool
     */
    public static function update_forum_feed($forum_id, $forum_data)
    {
        global $lang, $datastore;
        $sql = null;
        $file_path = config()->get('atom.path').'/f/'.$forum_id.'.atom';
        $select_tor_sql = $join_tor_sql = '';

        if (!$forums = $datastore->get('cat_forums')) {
            $datastore->update('cat_forums');
            $forums = $datastore->get('cat_forums');
        }
        $not_forums_id = $forums['not_auth_forums']['guest_view'];

        if ($forum_id == 0) {
            $forum_data['forum_name'] = $lang['ATOM_GLOBAL_FEED'] ?? config()->get('server_name');
        }
        if ($forum_id > 0 && $forum_data['allow_reg_tracker']) {
            $select_tor_sql = ', tor.size AS tor_size, tor.tor_status, tor.attach_id';
            $join_tor_sql = 'LEFT JOIN '.BB_BT_TORRENTS.' tor ON(t.topic_id = tor.topic_id)';
        }
        if ($forum_id == 0) {
            $sql = '
			SELECT
				t.topic_id, t.topic_title, t.topic_status,
				u1.username AS first_username,
				p1.post_time AS topic_first_post_time, p1.post_edit_time AS topic_first_post_edit_time,
				p2.post_time AS topic_last_post_time, p2.post_edit_time AS topic_last_post_edit_time,
				tor.size AS tor_size, tor.tor_status, tor.attach_id,
                pt.post_html
			FROM      '.BB_BT_TORRENTS.' tor
			LEFT JOIN '.BB_TOPICS.' t   ON(tor.topic_id = t.topic_id)
			LEFT JOIN '.BB_USERS.' u1  ON(t.topic_poster = u1.user_id)
			LEFT JOIN '.BB_POSTS.' p1  ON(t.topic_first_post_id = p1.post_id)
			LEFT JOIN '.BB_POSTS.' p2  ON(t.topic_last_post_id = p2.post_id)
            LEFT JOIN '.BB_POSTS_HTML.' pt ON(p1.post_id = pt.post_id)
			ORDER BY t.topic_last_post_time DESC
			LIMIT 100
		';
        } elseif ($forum_id > 0) {
            $sql = "
			SELECT
				t.topic_id, t.topic_title, t.topic_status,
				u1.username AS first_username,
				p1.post_time AS topic_first_post_time, p1.post_edit_time AS topic_first_post_edit_time,
				p2.post_time AS topic_last_post_time, p2.post_edit_time AS topic_last_post_edit_time,
                pt.post_html
				$select_tor_sql
			FROM      ".BB_TOPICS.' t
			LEFT JOIN '.BB_USERS.' u1  ON(t.topic_poster = u1.user_id)
			LEFT JOIN '.BB_POSTS.' p1  ON(t.topic_first_post_id = p1.post_id)
			LEFT JOIN '.BB_POSTS.' p2  ON(t.topic_last_post_id = p2.post_id)
            LEFT JOIN '.BB_POSTS_HTML." pt ON(p1.post_id = pt.post_id)
				$join_tor_sql
			WHERE t.forum_id = $forum_id
			ORDER BY t.topic_last_post_time DESC
			LIMIT 50
		";
        }
        $topics_tmp = DB()->fetch_rowset($sql);
        $topics = [];
        foreach ($topics_tmp as $topic) {
            if (in_array($topic['topic_id'], explode(',', $not_forums_id))) {
                continue;
            }
            if (isset($topic['topic_status'])) {
                if ($topic['topic_status'] == TOPIC_MOVED) {
                    continue;
                }
            }
            if (isset($topic['tor_status'])) {
                if (isset(config()->get('tor_frozen')[$topic['tor_status']])) {
                    continue;
                }
            }
            $topics[] = $topic;
        }
        if (!\count($topics)) {
            @unlink($file_path);

            return false;
        }
        if (self::create_atom($file_path, 'f', $forum_id, htmlCHR($forum_data['forum_name']), $topics)) {
            return true;
        }

        return false;
    }

    /**
     * Update user feed.
     *
     * @param int    $user_id
     * @param string $username
     *
     * @return bool
     */
    public static function update_user_feed($user_id, $username)
    {
        global $lang, $datastore;
        $file_path = config()->get('atom.path').'/u/'.floor($user_id / 5000).'/'.($user_id % 100).'/'.$user_id.'.atom';
        $sql = '
		SELECT
			t.topic_id, t.topic_title, t.topic_status,
			u1.username AS first_username,
			p1.post_time AS topic_first_post_time, p1.post_edit_time AS topic_first_post_edit_time,
			p2.post_time AS topic_last_post_time, p2.post_edit_time AS topic_last_post_edit_time,
			tor.size AS tor_size, tor.tor_status, tor.attach_id,
            pt.post_html
		FROM      '.BB_TOPICS.' t
		LEFT JOIN '.BB_USERS.' u1  ON(t.topic_poster = u1.user_id)
		LEFT JOIN '.BB_POSTS.' p1  ON(t.topic_first_post_id = p1.post_id)
		LEFT JOIN '.BB_POSTS.' p2  ON(t.topic_last_post_id = p2.post_id)
        LEFT JOIN '.BB_POSTS_HTML.' pt ON(p1.post_id = pt.post_id)
		LEFT JOIN '.BB_BT_TORRENTS." tor ON(t.topic_id = tor.topic_id)
		WHERE t.topic_poster = $user_id
		ORDER BY t.topic_last_post_time DESC
		LIMIT 50
	";
        $topics_tmp = DB()->fetch_rowset($sql);
        $topics = [];
        foreach ($topics_tmp as $topic) {
            if (isset($topic['topic_status'])) {
                if ($topic['topic_status'] == TOPIC_MOVED) {
                    continue;
                }
            }
            if (isset($topic['tor_status'])) {
                if (isset(config()->get('tor_frozen')[$topic['tor_status']])) {
                    continue;
                }
            }
            $topics[] = $topic;
        }
        if (!\count($topics)) {
            @unlink($file_path);

            return false;
        }
        if (self::create_atom($file_path, 'u', $user_id, $username, $topics)) {
            return true;
        }

        return false;
    }

    /**
     * Create and save atom feed.
     *
     * @param string $file_path
     * @param string $mode
     * @param int    $id
     * @param string $title
     * @param array  $topics
     *
     * @return bool
     */
    private static function create_atom($file_path, $mode, $id, $title, $topics)
    {
        global $lang;
        $date = null;
        $time = null;
        $dir = \dirname($file_path);
        if (!file_exists($dir)) {
            if (!bb_mkdir($dir)) {
                return false;
            }
        }
        foreach ($topics as $topic) {
            $last_time = $topic['topic_last_post_time'];
            if ($topic['topic_last_post_edit_time']) {
                $last_time = $topic['topic_last_post_edit_time'];
            }
            $date = bb_date($last_time, 'Y-m-d', 0);
            $time = bb_date($last_time, 'H:i:s', 0);
            break;
        }
        $atom = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
        $atom .= '<feed xmlns="http://www.w3.org/2005/Atom" xml:base="'.FULL_URL."\">\n";
        $atom .= "<title>$title</title>\n";
        $atom .= '<updated>'.$date."T$time+00:00</updated>\n";
        $atom .= "<id>tag:rto.feed,2000:/$mode/$id</id>\n";
        $atom .= '<link href="'.FULL_URL."\" />\n";
        foreach ($topics as $topic) {
            $topic_id = $topic['topic_id'];
            $tor_size = '';
            if (isset($topic['tor_size'])) {
                $tor_size = str_replace('&nbsp;', ' ', ' ['.humn_size($topic['tor_size']).']');
            }
            $tor_status = '';
            if (isset($topic['tor_status'])) {
                $tor_status = " ({$lang['TOR_STATUS_NAME'][$topic['tor_status']]})";
            }
            $topic_title = censor()->censorString($topic['topic_title']);
            $author_name = $topic['first_username'] ?: $lang['GUEST'];
            $last_time = $topic['topic_last_post_time'];
            if ($topic['topic_last_post_edit_time']) {
                $last_time = $topic['topic_last_post_edit_time'];
            }
            $date = bb_date($last_time, 'Y-m-d', 0);
            $time = bb_date($last_time, 'H:i:s', 0);
            $updated = '';
            $checktime = TIMENOW - 604800; // 1 week
            if ($topic['topic_first_post_edit_time'] && $topic['topic_first_post_edit_time'] > $checktime) {
                $updated = '['.$lang['ATOM_UPDATED'].'] ';
            }
            $atom .= "<entry>\n";
            $atom .= "	<title type=\"html\"><![CDATA[$updated$topic_title$tor_status$tor_size]]></title>\n";
            $atom .= "	<author>\n";
            $atom .= "		<name>$author_name</name>\n";
            $atom .= "	</author>\n";
            $atom .= '	<updated>'.$date."T$time+00:00</updated>\n";
            $atom .= '	<id>tag:rto.feed,'.$date.":/t/$topic_id</id>\n";
            if (config()->get('atom.direct_down') && isset($topic['attach_id'])) {
                $atom .= '	<link href="'.DL_URL.$topic['attach_id']."\" />\n";
            } else {
                $atom .= '	<link href="'.TOPIC_URL.$topic_id."\" />\n";
            }

            if (config()->get('atom.direct_view')) {
                $atom .= '	<description>'.$topic['post_html']."\n\nNews URL: ".FULL_URL.TOPIC_URL.$topic_id."</description>\n";
            }

            $atom .= "</entry>\n";
        }
        $atom .= '</feed>';
        @unlink($file_path);
        $fp = fopen($file_path, 'wb+');
        fwrite($fp, $atom);
        fclose($fp);

        return true;
    }
}
