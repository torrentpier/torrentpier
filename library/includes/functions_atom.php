<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

// Максимум записей = 50
// [Обновлено] - если дата изменения первого поста топика не старее недели (?) или в топике новые сообщения не старее недели (?)

/**
 * @param $forum_id
 * @param $forum_data
 * @return bool
 */
function update_forum_feed($forum_id, $forum_data)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    $file_path = $di->config->get('atom.path') . '/f/' . $forum_id . '.atom';
    $select_tor_sql = $join_tor_sql = '';
    if ($forum_id == 0) {
        $forum_data['atom_forum_name'] = 'Общая по всем разделам';
    }
    if ($forum_id > 0 && $forum_data['atom_tr_allowed']) {
        $select_tor_sql = ', tor.size AS tor_size, tor.tor_status';
        $join_tor_sql = "LEFT JOIN " . BB_BT_TORRENTS . " tor ON(t.topic_id = tor.topic_id)";
    }
    if ($forum_id == 0) {
        $sql = "
			SELECT
				t.topic_id, t.topic_title, t.topic_status,
				u1.username AS first_username,
				p1.post_time AS topic_first_post_time, p1.post_edit_time AS topic_first_post_edit_time,
				p2.post_time AS topic_last_post_time, p2.post_edit_time AS topic_last_post_edit_time,
				tor.size AS tor_size, tor.tor_status
			FROM      " . BB_BT_TORRENTS . " tor
			LEFT JOIN " . BB_TOPICS . " t   ON(tor.topic_id = t.topic_id)
			LEFT JOIN " . BB_USERS . " u1  ON(t.topic_poster = u1.user_id)
			LEFT JOIN " . BB_POSTS . " p1  ON(t.topic_first_post_id = p1.post_id)
			LEFT JOIN " . BB_POSTS . " p2  ON(t.topic_last_post_id = p2.post_id)
			ORDER BY t.topic_last_post_time DESC
			LIMIT 100
		";
    } elseif ($forum_id > 0) {
        $sql = "
			SELECT
				t.topic_id, t.topic_title, t.topic_status,
				u1.username AS first_username,
				p1.post_time AS topic_first_post_time, p1.post_edit_time AS topic_first_post_edit_time,
				p2.post_time AS topic_last_post_time, p2.post_edit_time AS topic_last_post_edit_time
				$select_tor_sql
			FROM      " . BB_TOPICS . " t
			LEFT JOIN " . BB_USERS . " u1  ON(t.topic_poster = u1.user_id)
			LEFT JOIN " . BB_POSTS . " p1  ON(t.topic_first_post_id = p1.post_id)
			LEFT JOIN " . BB_POSTS . " p2  ON(t.topic_last_post_id = p2.post_id)
				$join_tor_sql
			WHERE t.forum_id = $forum_id
			ORDER BY t.topic_last_post_time DESC
			LIMIT 50
		";
    }
    $topics_tmp = DB()->fetch_rowset($sql);
    $topics = array();
    foreach ($topics_tmp as $topic) {
        if (isset($topic['topic_status'])) {
            if ($topic['topic_status'] == TOPIC_MOVED) {
                continue;
            }
        }
        if (isset($topic['tor_status'])) {
            if ($di->config->get('tor_frozen.' . $topic['tor_status'])) {
                continue;
            }
        }
        $topics[] = $topic;
    }
    if (!count($topics)) {
        unlink($file_path);
        return false;
    }
    if (create_atom($file_path, 'f', $forum_id, htmlCHR($forum_data['atom_forum_name']), $topics)) {
        return true;
    } else {
        return false;
    }
}

/**
 * @param $user_id
 * @param $username
 * @return bool
 */
function update_user_feed($user_id, $username)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    $file_path = $di->config->get('atom.path') . '/u/' . floor($user_id / 5000) . '/' . ($user_id % 100) . '/' . $user_id . '.atom';
    $sql = "
		SELECT
			t.topic_id, t.topic_title, t.topic_status,
			u1.username AS first_username,
			p1.post_time AS topic_first_post_time, p1.post_edit_time AS topic_first_post_edit_time,
			p2.post_time AS topic_last_post_time, p2.post_edit_time AS topic_last_post_edit_time,
			tor.size AS tor_size, tor.tor_status
		FROM      " . BB_TOPICS . " t
		LEFT JOIN " . BB_USERS . " u1  ON(t.topic_poster = u1.user_id)
		LEFT JOIN " . BB_POSTS . " p1  ON(t.topic_first_post_id = p1.post_id)
		LEFT JOIN " . BB_POSTS . " p2  ON(t.topic_last_post_id = p2.post_id)
		LEFT JOIN " . BB_BT_TORRENTS . " tor ON(t.topic_id = tor.topic_id)
		WHERE t.topic_poster = $user_id
		ORDER BY t.topic_last_post_time DESC
		LIMIT 50
	";
    $topics_tmp = DB()->fetch_rowset($sql);
    $topics = array();
    foreach ($topics_tmp as $topic) {
        if (isset($topic['topic_status'])) {
            if ($topic['topic_status'] == TOPIC_MOVED) {
                continue;
            }
        }
        if (isset($topic['tor_status'])) {
            if ($di->config->get('tor_frozen.' . $topic['tor_status'])) {
                continue;
            }
        }
        $topics[] = $topic;
    }
    if (!count($topics)) {
        unlink($file_path);
        return false;
    }
    if (create_atom($file_path, 'u', $user_id, wbr($username), $topics)) {
        return true;
    } else {
        return false;
    }
}

/**
 * @param $file_path
 * @param $mode
 * @param $id
 * @param $title
 * @param $topics
 * @return bool
 */
function create_atom($file_path, $mode, $id, $title, $topics)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    $dir = dirname($file_path);
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
    $atom = "";
    $atom .= "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
    $atom .= "<feed xmlns=\"http://www.w3.org/2005/Atom\" xml:base=\"http://" . $di->config->get('server_name') . $di->config->get('script_path') . "\">\n";
    $atom .= "<title>$title</title>\n";
    $atom .= "<updated>" . $date . "T$time+00:00</updated>\n";
    $atom .= "<id>tag:rto.feed,2000:/$mode/$id</id>\n";
    $atom .= "<link href=\"http://" . $di->config->get('server_name') . $di->config->get('script_path') . "\" />\n";
    foreach ($topics as $topic) {
        $topic_id = $topic['topic_id'];
        $tor_size = '';
        if (isset($topic['tor_size'])) {
            $tor_size = str_replace('&nbsp;', ' ', ' [' . humn_size($topic['tor_size']) . ']');
        }
        $topic_title = $topic['topic_title'];
        $orig_word = array();
        $replacement_word = array();
        obtain_word_list($orig_word, $replacement_word);
        if (count($orig_word)) {
            $topic_title = preg_replace($orig_word, $replacement_word, $topic_title);
        }
        $topic_title = wbr($topic_title);
        $author_name = ($topic['first_username']) ? wbr($topic['first_username']) : 'Гость';
        $last_time = $topic['topic_last_post_time'];
        if ($topic['topic_last_post_edit_time']) {
            $last_time = $topic['topic_last_post_edit_time'];
        }
        $date = bb_date($last_time, 'Y-m-d', 0);
        $time = bb_date($last_time, 'H:i:s', 0);
        $updated = '';
        $checktime = TIMENOW - 604800; // неделя (week)
        if ($topic['topic_first_post_edit_time'] && $topic['topic_first_post_edit_time'] > $checktime) {
            $updated = '[Обновлено] ';
        }
        $atom .= "<entry>\n";
        $atom .= "	<title type=\"html\"><![CDATA[$updated$topic_title$tor_size]]></title>\n";
        $atom .= "	<author>\n";
        $atom .= "		<name>$author_name</name>\n";
        $atom .= "	</author>\n";
        $atom .= "	<updated>" . $date . "T$time+00:00</updated>\n";
        $atom .= "	<id>tag:rto.feed," . $date . ":/t/$topic_id</id>\n";
        $atom .= "	<link href=\"viewtopic.php?t=$topic_id\" />\n";
        $atom .= "</entry>\n";
    }
    $atom .= "</feed>";
    unlink($file_path);
    $fp = fopen($file_path, "w");
    fwrite($fp, $atom);
    fclose($fp);
    return true;
}
