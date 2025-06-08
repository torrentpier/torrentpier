<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy;

use TorrentPier\Emailer;
use TorrentPier\Legacy\Admin\Common;
use TorrentPier\Validate;

/**
 * Class Post
 * @package TorrentPier\Legacy
 */
class Post
{
    /**
     * Prepare message for posting
     *
     * @param string $mode
     * @param array $post_data
     * @param string $error_msg
     * @param string $username
     * @param string $subject
     * @param string $message
     */
    public static function prepare_post(&$mode, &$post_data, &$error_msg, &$username, &$subject, &$message)
    {
        global $bb_cfg, $user, $userdata, $lang;

        // Check username
        if (!empty($username)) {
            $username = clean_username($username);

            if (!$userdata['session_logged_in'] || ($userdata['session_logged_in'] && $username != $user->name)) {
                if ($err = Validate::username($username)) {
                    $error_msg .= $err;
                }
            } else {
                $username = '';
            }
        }

        // Check subject
        if (!empty($subject)) {
            $subject = str_replace('&amp;', '&', $subject);
        } elseif ($mode == 'newtopic' || ($mode == 'editpost' && $post_data['first_post'])) {
            $error_msg .= (!empty($error_msg)) ? '<br />' . $lang['EMPTY_SUBJECT'] : $lang['EMPTY_SUBJECT'];
        }

        // Check message
        if (!empty($message)) {
        } elseif ($mode != 'delete') {
            $error_msg .= (!empty($error_msg)) ? '<br />' . $lang['EMPTY_MESSAGE'] : $lang['EMPTY_MESSAGE'];
        }

        // Check smilies limit
        if ($bb_cfg['max_smilies']) {
            $count_smilies = substr_count(bbcode2html($message), '<img class="smile" src="' . $bb_cfg['smilies_path']);
            if ($count_smilies > $bb_cfg['max_smilies']) {
                $to_many_smilies = sprintf($lang['MAX_SMILIES_PER_POST'], $bb_cfg['max_smilies']);
                $error_msg .= (!empty($error_msg)) ? '<br />' . $to_many_smilies : $to_many_smilies;
            }
        }

        if (IS_GUEST && !$bb_cfg['captcha']['disabled'] && !bb_captcha('check')) {
            $error_msg .= (!empty($error_msg)) ? '<br />' . $lang['CAPTCHA_WRONG'] : $lang['CAPTCHA_WRONG'];
        }
    }

    /**
     * Post new topic/reply or edit existing post/poll
     *
     * @param string $mode
     * @param array $post_data
     * @param string $message
     * @param string $meta
     * @param int $forum_id
     * @param int $topic_id
     * @param int $post_id
     * @param int $topic_type
     * @param string $post_username
     * @param string $post_subject
     * @param string $post_message
     * @param bool $update_post_time
     * @param int $poster_rg_id
     * @param int $attach_rg_sig
     * @param int $robots_indexing
     *
     * @return string
     */
    public static function submit_post($mode, &$post_data, &$message, &$meta, &$forum_id, &$topic_id, &$post_id, &$topic_type, $post_username, $post_subject, $post_message, $update_post_time, $poster_rg_id, $attach_rg_sig, $robots_indexing)
    {
        global $userdata, $post_info, $is_auth, $bb_cfg, $lang, $datastore;

        $current_time = TIMENOW;

        // Flood control
        $row = null;
        $where_sql = IS_GUEST ? "p.poster_ip = '" . USER_IP . "'" : "p.poster_id = {$userdata['user_id']}";

        if ($mode == 'newtopic' || $mode == 'reply') {
            $sql = "SELECT MAX(p.post_time) AS last_post_time FROM " . BB_POSTS . " p WHERE $where_sql";
            if ($row = DB()->fetch_row($sql) and $row['last_post_time']) {
                if ($userdata['user_level'] == USER) {
                    if ((TIMENOW - $row['last_post_time']) < $bb_cfg['flood_interval']) {
                        bb_die($lang['FLOOD_ERROR']);
                    }
                }
            }
        }

        // Double Post Control
        if ($mode != 'editpost' && !empty($row['last_post_time']) && !IS_AM) {
            $sql = "
			SELECT pt.post_text
			FROM " . BB_POSTS . " p, " . BB_POSTS_TEXT . " pt
			WHERE
					$where_sql
				AND p.post_time = " . (int)$row['last_post_time'] . "
				AND pt.post_id = p.post_id
			LIMIT 1
		";

            if ($row = DB()->fetch_row($sql)) {
                $last_msg = DB()->escape($row['post_text']);

                if ($last_msg == $post_message) {
                    bb_die($lang['DOUBLE_POST_ERROR']);
                }
            }
        }

        if ($mode == 'newtopic' || ($mode == 'editpost' && $post_data['first_post'])) {
            $topic_dl_type = (isset($_POST['topic_dl_type']) && ($post_info['allow_reg_tracker'] || $is_auth['auth_mod'])) ? TOPIC_DL_TYPE_DL : TOPIC_DL_TYPE_NORMAL;

            $sql_insert = "
			INSERT INTO
				" . BB_TOPICS . " (topic_title, topic_poster, topic_time, forum_id, topic_status, topic_type, topic_dl_type, topic_allow_robots)
			VALUES
				('$post_subject', " . $userdata['user_id'] . ", $current_time, $forum_id, " . TOPIC_UNLOCKED . ", $topic_type, $topic_dl_type, $robots_indexing)
		";

            $sql_update = "
			UPDATE
				" . BB_TOPICS . "
			SET
				topic_title = '$post_subject',
				topic_type = $topic_type,
				topic_dl_type = $topic_dl_type,
				topic_allow_robots = $robots_indexing
			WHERE
				topic_id = $topic_id
		";

            $sql = ($mode != 'editpost') ? $sql_insert : $sql_update;

            if (!DB()->sql_query($sql)) {
                bb_die('Error in posting #1');
            }

            if ($mode == 'newtopic') {
                $topic_id = DB()->sql_nextid();
            }
        }

        $edited_sql = ($mode == 'editpost' && !$post_data['last_post'] && $post_data['poster_post']) ? ", post_edit_time = $current_time, post_edit_count = post_edit_count + 1" : "";

        if ($update_post_time && $mode == 'editpost' && $post_data['last_post'] && !$post_data['first_post']) {
            $edited_sql .= ", post_time = $current_time ";
            //lpt
            DB()->sql_query("UPDATE " . BB_TOPICS . " SET topic_last_post_time = $current_time WHERE topic_id = $topic_id LIMIT 1");
        }

        $sql = ($mode != 'editpost') ? "INSERT INTO " . BB_POSTS . " (topic_id, forum_id, poster_id, post_username, post_time, poster_ip, poster_rg_id, attach_rg_sig) VALUES ($topic_id, $forum_id, " . $userdata['user_id'] . ", '$post_username', $current_time, '" . USER_IP . "', $poster_rg_id, $attach_rg_sig)" : "UPDATE " . BB_POSTS . " SET post_username = '$post_username'" . $edited_sql . ", poster_rg_id = $poster_rg_id, attach_rg_sig = $attach_rg_sig WHERE post_id = $post_id";
        if (!DB()->sql_query($sql)) {
            bb_die('Error in posting #2');
        }

        if ($mode != 'editpost') {
            $post_id = DB()->sql_nextid();
        }

        $sql = ($mode != 'editpost') ? "INSERT INTO " . BB_POSTS_TEXT . " (post_id, post_text) VALUES ($post_id, '$post_message')" : "UPDATE " . BB_POSTS_TEXT . " SET post_text = '$post_message' WHERE post_id = $post_id";
        if (!DB()->sql_query($sql)) {
            bb_die('Error in posting #3');
        }

        if ($userdata['user_id'] != BOT_UID) {
            $s_post_message = str_replace('\n', "\n", $post_message);
            $s_post_subject = str_replace('\n', "\n", $post_subject);
            add_search_words($post_id, stripslashes($s_post_message), stripslashes($s_post_subject));
        }

        update_post_html(['post_id' => $post_id, 'post_text' => $post_message]);

        // Updating news cache on index page
        if ($bb_cfg['show_latest_news']) {
            $news_forums = array_flip(explode(',', $bb_cfg['latest_news_forum_id']));
            if (isset($news_forums[$forum_id]) && $bb_cfg['show_latest_news'] && $mode == 'newtopic') {
                $datastore->enqueue([
                    'latest_news'
                ]);
                $datastore->update('latest_news');
            }
        }

        if ($bb_cfg['show_network_news']) {
            $net_forums = array_flip(explode(',', $bb_cfg['network_news_forum_id']));
            if (isset($net_forums[$forum_id]) && $bb_cfg['show_network_news'] && $mode == 'newtopic') {
                $datastore->enqueue([
                    'network_news'
                ]);
                $datastore->update('network_news');
            }
        }

        meta_refresh(POST_URL . "$post_id#$post_id");
        set_die_append_msg($forum_id, $topic_id);

        return $mode;
    }

    /**
     * Update post stats and details
     *
     * @param string $mode
     * @param array $post_data
     * @param int $forum_id
     * @param int $topic_id
     * @param int $post_id
     * @param int $user_id
     */
    public static function update_post_stats($mode, $post_data, $forum_id, $topic_id, $post_id, $user_id)
    {
        $sign = ($mode == 'delete') ? '- 1' : '+ 1';
        $forum_update_sql = "forum_posts = forum_posts $sign";
        $topic_update_sql = '';

        if ($mode == 'delete') {
            if ($post_data['last_post']) {
                if ($post_data['first_post']) {
                    $forum_update_sql .= ', forum_topics = forum_topics - 1';
                } else {
                    $topic_update_sql .= 'topic_replies = topic_replies - 1';

                    $sql = "SELECT MAX(post_id) AS last_post_id, MAX(post_time) AS topic_last_post_time
					FROM " . BB_POSTS . "
					WHERE topic_id = $topic_id";
                    if (!($result = DB()->sql_query($sql))) {
                        bb_die('Error in deleting post #1');
                    }

                    if ($row = DB()->sql_fetchrow($result)) {
                        $topic_update_sql .= ", topic_last_post_id = {$row['last_post_id']}, topic_last_post_time = {$row['topic_last_post_time']}";
                    }
                }

                if ($post_data['last_topic']) {
                    $sql = "SELECT MAX(post_id) AS last_post_id
					FROM " . BB_POSTS . "
					WHERE forum_id = $forum_id";
                    if (!($result = DB()->sql_query($sql))) {
                        bb_die('Error in deleting post #2');
                    }

                    if ($row = DB()->sql_fetchrow($result)) {
                        $forum_update_sql .= ($row['last_post_id']) ? ', forum_last_post_id = ' . $row['last_post_id'] : ', forum_last_post_id = 0';
                    }
                }
            } elseif ($post_data['first_post']) {
                $sql = "SELECT MIN(post_id) AS first_post_id FROM " . BB_POSTS . " WHERE topic_id = $topic_id";
                if (!($result = DB()->sql_query($sql))) {
                    bb_die('Error in deleting post #3');
                }

                if ($row = DB()->sql_fetchrow($result)) {
                    $topic_update_sql .= 'topic_replies = topic_replies - 1, topic_first_post_id = ' . $row['first_post_id'];
                }
            } else {
                $topic_update_sql .= 'topic_replies = topic_replies - 1';
            }
        } else {
            $forum_update_sql .= ", forum_last_post_id = $post_id" . (($mode == 'newtopic') ? ", forum_topics = forum_topics $sign" : "");
            $topic_update_sql = "topic_last_post_id = $post_id, topic_last_post_time = " . TIMENOW . (($mode == 'reply') ? ", topic_replies = topic_replies $sign" : ", topic_first_post_id = $post_id");
        }

        $sql = "UPDATE " . BB_FORUMS . " SET $forum_update_sql WHERE forum_id = $forum_id";
        if (!DB()->sql_query($sql)) {
            bb_die('Error in posting #4');
        }

        if ($topic_update_sql != '') {
            $sql = "UPDATE " . BB_TOPICS . " SET $topic_update_sql WHERE topic_id = $topic_id";
            if (!DB()->sql_query($sql)) {
                bb_die('Error in posting #5');
            }
        }

        $sql = "UPDATE " . BB_USERS . " SET user_posts = user_posts $sign WHERE user_id = $user_id";
        if (!DB()->sql_query($sql)) {
            bb_die('Error in posting #6');
        }
    }

    /**
     * Delete post
     *
     * @param string $mode
     * @param array $post_data
     * @param string $message
     * @param string $meta
     * @param int $forum_id
     * @param int $topic_id
     * @param int $post_id
     */
    public static function delete_post($mode, $post_data, &$message, &$meta, $forum_id, $topic_id, $post_id)
    {
        global $lang;

        $message = $lang['DELETED'];
        Common::post_delete($post_id);

        set_die_append_msg($forum_id, $topic_id);
    }

    /**
     * Handle user notification on new post
     *
     * @param string $mode
     * @param array $post_data
     * @param string $topic_title
     * @param int $forum_id
     * @param int $topic_id
     * @param bool $notify_user
     */
    public static function user_notification($mode, &$post_data, &$topic_title, &$forum_id, &$topic_id, &$notify_user)
    {
        global $bb_cfg, $lang, $userdata, $wordCensor;

        if (!$bb_cfg['topic_notify_enabled']) {
            return;
        }

        if ($mode != 'delete') {
            if ($mode == 'reply') {
                $update_watched_sql = [];
                $banned_users = ($get_banned_users = get_banned_users()) ? (', ' . implode(', ', $get_banned_users)) : '';

                $watch_list = DB()->fetch_rowset("SELECT u.username, u.user_id, u.user_email, u.user_lang
				FROM " . BB_TOPICS_WATCH . " tw, " . BB_USERS . " u
				WHERE tw.topic_id = $topic_id
					AND tw.user_id NOT IN ({$userdata['user_id']}, " . EXCLUDED_USERS . $banned_users . ")
					AND tw.notify_status = " . TOPIC_WATCH_NOTIFIED . "
					AND u.user_id = tw.user_id
					AND u.user_active = 1
				ORDER BY u.user_id
			");

                if ($watch_list) {
                    $topic_title = $wordCensor->censorString($topic_title);

                    $u_topic = make_url(TOPIC_URL . $topic_id . '&view=newest#newest');
                    $unwatch_topic = make_url(TOPIC_URL . "$topic_id&unwatch=topic");

                    foreach ($watch_list as $row) {
                        // Sending email
                        $emailer = new Emailer();

                        $emailer->set_to($row['user_email'], $row['username']);
                        $emailer->set_subject(sprintf($lang['EMAILER_SUBJECT']['TOPIC_NOTIFY'], $topic_title));

                        $emailer->set_template('topic_notify', $row['user_lang']);
                        $emailer->assign_vars([
                            'TOPIC_TITLE' => html_entity_decode($topic_title),
                            'SITENAME' => $bb_cfg['sitename'],
                            'USERNAME' => $row['username'],
                            'U_TOPIC' => $u_topic,
                            'U_STOP_WATCHING_TOPIC' => $unwatch_topic,
                        ]);

                        $emailer->send();

                        $update_watched_sql[] = $row['user_id'];
                    }
                    $update_watched_sql = implode(',', $update_watched_sql);
                }

                if ($update_watched_sql) {
                    DB()->query("UPDATE " . BB_TOPICS_WATCH . "
					SET notify_status = " . TOPIC_WATCH_UNNOTIFIED . "
					WHERE topic_id = $topic_id
						AND user_id IN ($update_watched_sql)
				");
                }
            }

            $topic_watch = DB()->fetch_row("SELECT topic_id FROM " . BB_TOPICS_WATCH . " WHERE topic_id = $topic_id AND user_id = {$userdata['user_id']}", 'topic_id');

            if (!$notify_user && !empty($topic_watch)) {
                DB()->query("DELETE FROM " . BB_TOPICS_WATCH . " WHERE topic_id = $topic_id AND user_id = {$userdata['user_id']}");
            } elseif ($notify_user && empty($topic_watch)) {
                DB()->query("
				INSERT INTO " . BB_TOPICS_WATCH . " (user_id, topic_id, notify_status)
				VALUES (" . $userdata['user_id'] . ", $topic_id, " . TOPIC_WATCH_NOTIFIED . ")
			");
            }
        }
    }

    /**
     * Insert post to the existing thread
     *
     * @param string $mode
     * @param int|string $topic_id
     * @param int|string|null $forum_id
     * @param int|string|null $old_forum_id
     * @param int|string|null $new_topic_id
     * @param string $new_topic_title
     * @param int|string|null $old_topic_id
     * @param string $reason_move
     */
    public static function insert_post(string $mode, int|string $topic_id, null|int|string $forum_id = null, null|int|string $old_forum_id = null, null|int|string $new_topic_id = null, string $new_topic_title = '', null|int|string $old_topic_id = null, string $reason_move = ''): void
    {
        global $userdata, $lang;

        if (!$topic_id) {
            return;
        }

        $post_username = '';
        $post_time = TIMENOW;

        $poster_id = BOT_UID;
        $poster_ip = '0';

        if ($mode == 'after_move') {
            if (!$forum_id || !$old_forum_id) {
                return;
            }

            $sql = "SELECT forum_id, forum_name
			FROM " . BB_FORUMS . "
			WHERE forum_id IN($forum_id, $old_forum_id)";

            $forum_names = [];
            foreach (DB()->fetch_rowset($sql) as $row) {
                $forum_names[$row['forum_id']] = htmlCHR($row['forum_name']);
            }
            if (!$forum_names) {
                return;
            }

            $reason_move = !empty($reason_move) ? htmlCHR($reason_move) : $lang['NOSELECT'];
            $post_text = sprintf($lang['BOT_TOPIC_MOVED_FROM_TO'], '[url=' . make_url(FORUM_URL . $old_forum_id) . ']' . $forum_names[$old_forum_id] . '[/url]', '[url=' . make_url(FORUM_URL . $forum_id) . ']' . $forum_names[$forum_id] . '[/url]', $reason_move, profile_url($userdata));
        } elseif ($mode == 'after_split_to_old') {
            $post_text = sprintf($lang['BOT_MESS_SPLITS'], '[url=' . make_url(TOPIC_URL . $new_topic_id) . ']' . htmlCHR($new_topic_title) . '[/url]', profile_url($userdata));
        } elseif ($mode == 'after_split_to_new') {
            $sql = "SELECT t.topic_title, p.post_time
			FROM " . BB_TOPICS . " t, " . BB_POSTS . " p
			WHERE t.topic_id = $old_topic_id
				AND p.post_id = t.topic_first_post_id";

            if ($row = DB()->fetch_row($sql)) {
                $post_time = $row['post_time'] - 1;

                $post_text = sprintf($lang['BOT_TOPIC_SPLITS'], '[url=' . make_url(TOPIC_URL . $old_topic_id) . ']' . $row['topic_title'] . '[/url]', profile_url($userdata));
            } else {
                return;
            }
        } else {
            return;
        }

        $post_columns = 'topic_id,  forum_id,  poster_id,   post_username,   post_time,   poster_ip';
        $post_values = "$topic_id, $forum_id, $poster_id, '$post_username', $post_time, '$poster_ip'";

        DB()->query("INSERT INTO " . BB_POSTS . " ($post_columns) VALUES ($post_values)");

        $post_id = DB()->sql_nextid();
        $post_text = DB()->escape($post_text);

        $post_text_columns = 'post_id,    post_text';
        $post_text_values = "$post_id, '$post_text'";

        DB()->query("INSERT INTO " . BB_POSTS_TEXT . " ($post_text_columns) VALUES ($post_text_values)");

        DB()->query("UPDATE " . BB_USERS . " SET user_posts = user_posts + 1 WHERE user_id = $poster_id");
    }

    /**
     * Preview topic with posts content
     *
     * @param $topic_id
     */
    public static function topic_review($topic_id)
    {
        global $bb_cfg, $template;

        // Fetch posts data
        $review_posts = DB()->fetch_rowset("
		SELECT
			p.*, h.post_html, IF(h.post_html IS NULL, pt.post_text, NULL) AS post_text,
			IF(p.poster_id = " . GUEST_UID . ", p.post_username, u.username) AS username, u.user_rank
		FROM      " . BB_POSTS . " p
		LEFT JOIN " . BB_USERS . " u  ON(u.user_id = p.poster_id)
		LEFT JOIN " . BB_POSTS_TEXT . " pt ON(pt.post_id = p.post_id)
		LEFT JOIN " . BB_POSTS_HTML . " h  ON(h.post_id = p.post_id)
		WHERE p.topic_id = " . (int)$topic_id . "
		ORDER BY p.post_time DESC
		LIMIT " . $bb_cfg['posts_per_page'] . "
	");

        // Topic posts block
        foreach ($review_posts as $i => $post) {
            $template->assign_block_vars('review', [
                'ROW_CLASS' => !($i % 2) ? 'row1' : 'row2',
                'POSTER' => profile_url($post),
                'POSTER_NAME_JS' => addslashes($post['username']),
                'POST_ID' => $post['post_id'],
                'POST_DATE' => bb_date($post['post_time'], $bb_cfg['post_date_format']),
                'IS_UNREAD' => is_unread($post['post_time'], $topic_id, $post['forum_id']),
                'MESSAGE' => get_parsed_post($post),
            ]);
        }

        $template->assign_vars([
            'TPL_TOPIC_REVIEW' => (bool)$review_posts,
        ]);
    }
}
