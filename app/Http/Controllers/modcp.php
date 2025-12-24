<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

/*
 * ===========================================================================
 * Refactor to Modern Controller
 * ===========================================================================
 * Target: Convert to PSR-7 controller with constructor dependency injection
 *
 * Dependencies to inject:
 * - TorrentPier\Config (configuration access)
 * - TorrentPier\Database\Database (database operations)
 * - TorrentPier\Legacy\User (user session and moderator permissions)
 * - TorrentPier\Http\Request (HTTP request handling)
 * - TorrentPier\Legacy\Templates (template rendering)
 * - TorrentPier\Legacy\BBCode (BBCode parsing)
 *
 * Target namespace: TorrentPier\Http\Controllers\Moderation
 * Target class: ModerationController
 *
 * Key refactoring tasks:
 * 1. Extract procedural code into controller methods (move, lock, split, etc.)
 * 2. Replace global function calls with injected dependencies
 * 3. Implement PSR-7 request/response handling
 * 4. Extract business logic into ModerationService
 * 5. Add proper authorization middleware
 * 6. Add audit logging for moderation actions
 * ===========================================================================
 */

//
// Functions
//
function return_msg_mcp(string $status_msg, int $topic_id, array $req_topics, int $forum_id, string $mode): string
{
    if (count($req_topics) == 1) {
        $topic_id = reset($req_topics);
    }

    $message = $status_msg;
    $message .= '<br /><br />';

    if ($topic_id && $mode != 'delete') {
        $message .= sprintf(__('CLICK_RETURN_TOPIC'), '<a href="' . TOPIC_URL . $topic_id . '">', '</a>');
        $message .= '<br /><br />';
    } elseif (count($req_topics) != 1) {
        $message .= sprintf(__('CLICK_RETURN_MODCP'), '<a href="' . FORUM_URL . "{$forum_id}&amp;mod=1" . '">', '</a>');
        $message .= '<br /><br />';
    }

    $message .= sprintf(__('CLICK_RETURN_FORUM'), '<a href="' . FORUM_URL . $forum_id . '">', '</a>');

    return $message;
}

function validate_topics($forum_id, &$req_topics, &$topic_titles)
{
    $valid_topics = $valid_titles = [];

    if ($topic_csv = get_id_csv($req_topics)) {
        $sql = 'SELECT topic_id, topic_title FROM ' . BB_TOPICS . " WHERE topic_id IN({$topic_csv}) AND forum_id = {$forum_id}";

        foreach (DB()->fetch_rowset($sql) as $row) {
            $valid_topics[] = $row['topic_id'];
            $valid_titles[] = htmlspecialchars($row['topic_title']);
        }
    }

    $req_topics = $valid_topics;
    $topic_titles = $valid_titles;
}

/**
 * @return bool
 */
function validate_mode_condition($request_index, $mod_action = '')
{
    if (!$mod_action) {
        $mod_action = $request_index;
    }

    return request()->has($request_index) || (request()->post->has('mod_action') && request()->post->get('mod_action') === $mod_action);
}

// Obtain initial vars
$forum_id = request()->getInt(POST_FORUM_URL, 0);
$topic_id = request()->getInt(POST_TOPIC_URL, 0);
$post_id = request()->getInt(POST_POST_URL, 0);

$start = request()->has('start') ? abs(request()->getInt('start')) : 0;
$confirmed = request()->post->has('confirm');

$mode = $topic_title = '';

if (request()->has('mode')) {
    $mode = request()->getString('mode');
} else {
    if (validate_mode_condition('delete', 'topic_delete')) {
        $mode = 'delete';
    } elseif (validate_mode_condition('move', 'topic_move')) {
        $mode = 'move';
    } elseif (validate_mode_condition('lock', 'topic_lock')) {
        $mode = 'lock';
    } elseif (validate_mode_condition('unlock', 'topic_unlock')) {
        $mode = 'unlock';
    } elseif (validate_mode_condition('post_pin')) {
        $mode = 'post_pin';
    } elseif (validate_mode_condition('post_unpin')) {
        $mode = 'post_unpin';
    }
}

// Obtain relevant data
if ($topic_id) {
    $sql = '
		SELECT
			f.forum_id, f.forum_name, f.forum_topics, f.self_moderated,
			t.topic_first_post_id, t.topic_poster
		FROM ' . BB_TOPICS . ' t, ' . BB_FORUMS . " f
		WHERE t.topic_id = {$topic_id}
			AND f.forum_id = t.forum_id
		LIMIT 1
	";

    if (!$topic_row = DB()->fetch_row($sql)) {
        bb_die(__('INVALID_TOPIC_ID_DB'));
    }

    $forum_id = $topic_row['forum_id'];
    $forum_name = $topic_row['forum_name'];
    $forum_topics = (!$topic_row['forum_topics']) ? 1 : $topic_row['forum_topics'];
} elseif ($forum_id) {
    $sql = 'SELECT forum_name, forum_topics FROM ' . BB_FORUMS . " WHERE forum_id = {$forum_id} LIMIT 1";

    if (!$topic_row = DB()->fetch_row($sql)) {
        bb_die(__('FORUM_NOT_EXIST'));
    }

    $forum_name = $topic_row['forum_name'];
    $forum_topics = (!$topic_row['forum_topics']) ? 1 : $topic_row['forum_topics'];
} else {
    bb_die('Invalid request');
}

// Check if user did or did not confirm. If they did not, forward them to the last page they were on
if (request()->post->has('cancel') || IS_GUEST) {
    $redirect = 'index.php';

    if ($topic_id || $forum_id) {
        $redirect = ($topic_id) ? TOPIC_URL . $topic_id : FORUM_URL . $forum_id;
    }
    redirect($redirect);
}

// Start auth check
$is_auth = auth(AUTH_ALL, $forum_id, userdata());
$is_moderator = (IS_AM);

if ($mode == 'ip') {
    // Moderator can view IP in all forums
    $is_auth['auth_mod'] = $is_moderator;
} elseif ($mode == 'move' && !$is_auth['auth_mod']) {
    // User can move his own topic if this forum is "self_moderated"
    if ($topic_id && $topic_row['self_moderated'] && $topic_row['topic_poster'] == userdata('user_id')) {
        $is_auth['auth_mod'] = true;

        request()->post->set('insert_bot_msg', 1);
        request()->post->remove('topic_id_list');
        request()->post->remove('move_leave_shadow');
    }
}

// Exit if user not authorized
if (!$is_auth['auth_mod']) {
    bb_die(__('NOT_MODERATOR'));
}

// Redirect to login page if not admin session
if ($is_moderator && !userdata('session_admin')) {
    $redirect = request()->post->get('redirect') ?? request()->getRequestUri();
    redirect(LOGIN_URL . "?redirect={$redirect}&admin=1");
}

//
// Get required vars
//
$req_topics = $topic_csv = $topic_titles = $hidden_fields = [];

switch ($mode) {
    case 'delete':
    case 'move':
    case 'lock':
    case 'unlock':
    case 'set_download':
    case 'unset_download':
    case 'post_pin':
    case 'post_unpin':

        if (!request()->post->has('topic_id_list') && empty($topic_id)) {
            bb_die(__('NONE_SELECTED'));
        }

        $req_topics = request()->post->get('topic_id_list') ?? $topic_id;
        validate_topics($forum_id, $req_topics, $topic_titles);

        if (!$req_topics || !($topic_csv = get_id_csv($req_topics))) {
            bb_die(__('NONE_SELECTED'));
        }

        $hidden_fields = [
            'sid' => userdata('session_id'),
            'mode' => $mode,
            POST_FORUM_URL => $forum_id,
            POST_TOPIC_URL => $topic_id,
        ];
        foreach ($req_topics as $req_topic_id) {
            $hidden_fields['topic_id_list'][] = $req_topic_id;
        }

        break;
}

//
// Perform action or show confirm message
//
switch ($mode) {
    case 'delete':

        if (!$is_auth['auth_delete']) {
            bb_die(sprintf(__('SORRY_AUTH_DELETE'), $is_auth['auth_delete_type']));
        }

        if ($confirmed) {
            $result = TorrentPier\Legacy\Admin\Common::topic_delete($req_topics, $forum_id);

            //Обновление кеша новостей на главной
            $news_forums = array_flip(explode(',', config()->get('latest_news_forum_id')));
            if (isset($news_forums[$forum_id]) && config()->get('show_latest_news') && $result) {
                datastore()->enqueue([
                    'latest_news',
                ]);
                datastore()->update('latest_news');
            }

            $net_forums = array_flip(explode(',', config()->get('network_news_forum_id')));
            if (isset($net_forums[$forum_id]) && config()->get('show_network_news') && $result) {
                datastore()->enqueue([
                    'network_news',
                ]);
                datastore()->update('network_news');
            }

            $msg = $result ? __('TOPICS_REMOVED') : __('NO_TOPICS_REMOVED');
            bb_die(return_msg_mcp($msg, $topic_id, $req_topics, $forum_id, $mode));
        } else {
            print_confirmation([
                'QUESTION' => __('CONFIRM_DELETE_TOPIC'),
                'ITEMS_LIST' => implode("\n</li>\n<li>\n", $topic_titles),
                'FORM_ACTION' => 'modcp',
                'HIDDEN_FIELDS' => build_hidden_fields($hidden_fields),
            ]);
        }
        break;

    case 'move':

        if ($confirmed) {
            $new_forum_id = request()->post->getInt('new_forum');
            $result = TorrentPier\Legacy\Admin\Common::topic_move($req_topics, $new_forum_id, $forum_id, request()->post->has('move_leave_shadow'), request()->post->has('insert_bot_msg'), request()->post->get('reason_move_bot'));

            //Обновление кеша новостей на главной
            $news_forums = array_flip(explode(',', config()->get('latest_news_forum_id')));
            if ((isset($news_forums[$forum_id]) || isset($news_forums[$new_forum_id])) && config()->get('show_latest_news') && $result) {
                datastore()->enqueue([
                    'latest_news',
                ]);
                datastore()->update('latest_news');
            }

            $net_forums = array_flip(explode(',', config()->get('network_news_forum_id')));
            if ((isset($net_forums[$forum_id]) || isset($net_forums[$new_forum_id])) && config()->get('show_network_news') && $result) {
                datastore()->enqueue([
                    'network_news',
                ]);
                datastore()->update('network_news');
            }

            $msg = $result ? __('TOPICS_MOVED') : __('NO_TOPICS_MOVED');
            bb_die(return_msg_mcp($msg, $topic_id, $req_topics, $forum_id, $mode));
        } else {
            if (IS_ADMIN) {
                $forum_select_mode = 'admin';
            } else {
                $not_auth_forums_csv = user()->get_not_auth_forums(AUTH_VIEW);
                $forum_select_mode = explode(',', $not_auth_forums_csv);
            }

            $forum_select = get_forum_select($forum_select_mode, 'new_forum', $forum_id);

            template()->assign_vars([
                'TPL_MODCP_MOVE' => true,
                'SHOW_LEAVESHADOW' => $is_moderator,
                'SHOW_BOT_OPTIONS' => $is_moderator,

                'MESSAGE_TITLE' => __('CONFIRM'),
                'MESSAGE_TEXT' => __('CONFIRM_MOVE_TOPIC'),
                'TOPIC_TITLES' => implode("\n</li>\n<li>\n", $topic_titles),

                'S_FORUM_SELECT' => $forum_select,
                'S_MODCP_ACTION' => FORUM_PATH . 'modcp',
                'S_HIDDEN_FIELDS' => build_hidden_fields($hidden_fields),
            ]);

            template()->set_filenames(['body' => 'modcp.twig']);
        }
        break;

    case 'lock':
    case 'unlock':
        $lock = ($mode == 'lock');
        $new_topic_status = ($lock) ? TOPIC_LOCKED : TOPIC_UNLOCKED;

        $sql = '
			SELECT topic_id, topic_title
			FROM ' . BB_TOPICS . "
			WHERE topic_id IN({$topic_csv})
				AND forum_id = {$forum_id}
				AND topic_status != " . TOPIC_MOVED . "
				AND topic_status != {$new_topic_status}
		";

        $topic_csv = [];

        foreach (DB()->fetch_rowset($sql) as $row) {
            $topic_csv[] = $row['topic_id'];
            $log_topics[$row['topic_id']] = $row['topic_title'];
        }

        if (!$topic_csv = get_id_csv($topic_csv)) {
            bb_die(__('NONE_SELECTED'));
        }

        DB()->query('
			UPDATE ' . BB_TOPICS . " SET
				topic_status = {$new_topic_status}
			WHERE topic_id IN({$topic_csv})
		");

        // Log action
        $type = ($lock) ? 'mod_topic_lock' : 'mod_topic_unlock';

        foreach ($log_topics as $topic_id => $topic_title) {
            log_action()->mod($type, [
                'forum_id' => $forum_id,
                'topic_id' => $topic_id,
                'topic_title' => $topic_title,
            ]);
        }

        $msg = ($lock) ? __('TOPICS_LOCKED') : __('TOPICS_UNLOCKED');
        bb_die(return_msg_mcp($msg, $topic_id, $req_topics, $forum_id, $mode));

        break;

        // Set or unset topics DL-type
    case 'set_download':
    case 'unset_download':
        $set_download = ($mode == 'set_download');
        $new_dl_type = $set_download ? TOPIC_DL_TYPE_DL : TOPIC_DL_TYPE_NORMAL;

        DB()->query('
			UPDATE ' . BB_TOPICS . " SET
				topic_dl_type = {$new_dl_type}
			WHERE topic_id IN({$topic_csv})
				AND forum_id = {$forum_id}
				AND topic_moved_id = 0
		");

        if ($mode == 'unset_download') {
            clear_dl_list($topic_csv);
        }

        // Log action
        $type = ($set_download) ? 'mod_topic_set_downloaded' : 'mod_topic_unset_downloaded';

        log_action()->mod($type, [
            'forum_id' => $forum_id,
            'topic_id' => $topic_id,
            'topic_title' => get_topic_title($topic_id),
        ]);

        $msg = ($set_download) ? __('TOPICS_DOWN_SETS') : __('TOPICS_DOWN_UNSETS');
        bb_die(return_msg_mcp($msg, $topic_id, $req_topics, $forum_id, $mode));

        break;

    case 'split':
        //mpd
        $delete_posts = request()->post->has('delete_posts');
        $split = (request()->post->has('split_type_all') || request()->post->has('split_type_beyond'));
        $posts = request()->post->get('post_id_list', []);
        $start = 0;
        $topic_first_post_id = $topic_row['topic_first_post_id'] ?? '';

        $post_id_sql = $req_post_id_sql = [];

        if (($split || $delete_posts) && ($posts && $topic_id && $forum_id && $topic_first_post_id) && $confirmed) {
            foreach ($posts as $post_id) {
                if ($pid = (int)$post_id) {
                    $req_post_id_sql[] = $pid;
                }
            }
            if ($req_post_id_sql = implode(',', $req_post_id_sql)) {
                $sql = 'SELECT post_id
					FROM ' . BB_POSTS . "
					WHERE post_id IN({$req_post_id_sql})
						AND post_id != {$topic_first_post_id}
						AND topic_id = {$topic_id}
						AND forum_id = {$forum_id}";

                if (!$result = DB()->sql_query($sql)) {
                    bb_die('Could not get post id information');
                }
                if ($rowset = DB()->sql_fetchrowset($result)) {
                    foreach ($rowset as $rid => $row) {
                        $post_id_sql[] = $row['post_id'];
                    }
                    $post_id_sql = implode(',', $post_id_sql);
                }
            }
        }

        if ($post_id_sql && $split) {
            //mpd end

            $sql = 'SELECT post_id, poster_id, topic_id, post_time
				FROM ' . BB_POSTS . "
				WHERE post_id IN ({$post_id_sql})
				ORDER BY post_time ASC";
            if (!($result = DB()->sql_query($sql))) {
                bb_die('Could not get post information');
            }

            if ($row = DB()->sql_fetchrow($result)) {
                $first_poster = $row['poster_id'];
                $topic_id = $row['topic_id'];
                $post_time = $row['post_time'];

                $user_id_sql = '';
                $post_id_sql = '';
                do {
                    $user_id_sql .= (($user_id_sql != '') ? ', ' : '') . (int)$row['poster_id'];
                    $post_id_sql .= (($post_id_sql != '') ? ', ' : '') . (int)$row['post_id'];
                } while ($row = DB()->sql_fetchrow($result));

                $post_subject = clean_title(request()->post->get('subject'));
                if (empty($post_subject)) {
                    bb_die(__('EMPTY_SUBJECT'));
                }

                $new_forum_id = request()->post->getInt('new_forum_id');
                $topic_time = TIMENOW;

                $sql = 'SELECT forum_id FROM ' . BB_FORUMS . ' WHERE forum_id = ' . $new_forum_id;
                if (!($result = DB()->sql_query($sql))) {
                    bb_die('Could not select from forums table');
                }

                if (!DB()->sql_fetchrow($result)) {
                    bb_die('New forum does not exist');
                }

                DB()->sql_freeresult($result);

                $first_post_id = min(explode(',', $post_id_sql));

                $sql = 'INSERT INTO ' . BB_TOPICS . " (topic_title, topic_poster, topic_time, forum_id, topic_status, topic_type, topic_first_post_id)
					VALUES ('" . DB()->escape($post_subject) . "', {$first_poster}, " . $topic_time . ", {$new_forum_id}, " . TOPIC_UNLOCKED . ', ' . POST_NORMAL . ", {$first_post_id})";
                if (!(DB()->sql_query($sql))) {
                    bb_die('Could not insert new topic');
                }

                $new_topic_id = DB()->sql_nextid();

                // Update topic watch table, switch users whose posts
                // have moved, over to watching the new topic
                $sql = 'UPDATE ' . BB_TOPICS_WATCH . "
					SET topic_id = {$new_topic_id}
					WHERE topic_id = {$topic_id}
						AND user_id IN ({$user_id_sql})";
                if (!DB()->sql_query($sql)) {
                    bb_die('Could not update topics watch table');
                }

                $sql_where = (request()->post->has('split_type_beyond')) ? " post_time >= {$post_time} AND topic_id = {$topic_id}" : "post_id IN ({$post_id_sql})";

                $sql = 'UPDATE ' . BB_POSTS . " SET topic_id = {$new_topic_id}, forum_id = {$new_forum_id} WHERE {$sql_where}";
                if (!DB()->sql_query($sql)) {
                    bb_die('Could not update posts table');
                }

                //bot
                if (request()->post->has('after_split_to_old')) {
                    TorrentPier\Legacy\Post::insert_post('after_split_to_old', $topic_id, $forum_id, '', $new_topic_id, trim(request()->post->get('subject')));
                }
                if (request()->post->has('after_split_to_new')) {
                    TorrentPier\Legacy\Post::insert_post('after_split_to_new', $new_topic_id, $new_forum_id, $forum_id, $new_topic_id, '', $topic_id);
                }

                TorrentPier\Legacy\Admin\Common::sync('topic', [$topic_id, $new_topic_id]);
                TorrentPier\Legacy\Admin\Common::sync('forum', [$forum_id, $new_forum_id]);

                //bot
                $message = __('TOPIC_SPLIT') . '<br /><br /><a href="' . TOPIC_URL . "{$topic_id}/?sid=" . userdata('session_id') . '">' . __('TOPIC_SPLIT_OLD') . '</a>';
                $message .= ' &nbsp;::&nbsp; <a href="' . TOPIC_URL . "{$new_topic_id}/?sid=" . userdata('session_id') . '">' . __('TOPIC_SPLIT_NEW') . '</a>';

                // Log action
                log_action()->mod('mod_topic_split', [
                    'forum_id' => $forum_id,
                    'forum_id_new' => $new_forum_id,
                    'topic_id' => $topic_id,
                    'topic_title' => get_topic_title($topic_id),
                    'topic_id_new' => $new_topic_id,
                    'topic_title_new' => htmlCHR(request()->post->get('subject')),
                ]);

                bb_die($message);
            }
        } elseif ($post_id_sql && $delete_posts) {
            if (!$is_auth['auth_delete']) {
                bb_die(sprintf(__('SORRY_AUTH_DELETE'), $is_auth['auth_delete_type']));
            }

            // Delete posts
            $result = TorrentPier\Legacy\Admin\Common::post_delete(explode(',', $post_id_sql));

            $msg = $result ? __('DELETE_POSTS_SUCCESFULLY') : __('NO_POSTS_REMOVED');
            bb_die(return_msg_mcp($msg, $topic_id, $req_topics, $forum_id, $mode));
        } else {
            $sql = 'SELECT u.username, u.user_rank, p.*, pt.post_text, p.post_username
				FROM ' . BB_POSTS . ' p, ' . BB_USERS . ' u, ' . BB_POSTS_TEXT . " pt
				WHERE p.topic_id = {$topic_id}
					AND p.poster_id = u.user_id
					AND p.post_id = pt.post_id
					AND p.poster_id != " . BOT_UID . '
				ORDER BY p.post_time ASC';
            if (!($result = DB()->sql_query($sql))) {
                bb_die('Could not get topic / post information');
            }

            $s_hidden_fields = '<input type="hidden" name="sid" value="' . userdata('session_id') . '" /><input type="hidden" name="' . POST_FORUM_URL . '" value="' . $forum_id . '" /><input type="hidden" name="' . POST_TOPIC_URL . '" value="' . $topic_id . '" /><input type="hidden" name="mode" value="split" />';

            if (($total_posts = DB()->num_rows($result)) > 0) {
                $postrow = DB()->sql_fetchrowset($result);

                $postrowList = [];

                for ($i = 0; $i < $total_posts; $i++) {
                    $post_id = $postrow[$i]['post_id'];
                    $poster_id = $postrow[$i]['poster_id'];
                    $poster = $postrow[$i]['username'];
                    $poster_rank = $postrow[$i]['user_rank'];

                    $post_date = bb_date($postrow[$i]['post_time'], config()->get('post_date_format'));

                    $message = $postrow[$i]['post_text'];

                    //
                    // If the board has HTML off but the post has HTML
                    // on then we process it, else leave it alone
                    //
                    $message = bbcode()->toHtml($message);

                    $row_class = !($i % 2) ? 'row1' : 'row2';
                    $postrowList[] = [
                        'ROW_CLASS' => $row_class,
                        'POSTER_NAME' => profile_url(['username' => $poster, 'user_id' => $poster_id, 'user_rank' => $poster_rank]),
                        'POST_DATE' => $post_date,
                        'MESSAGE' => $message,
                        'CHECKBOX' => defined('BEGIN_CHECKBOX'),
                        'POST_ID' => $post_id,
                        'ROW_ID' => $i,
                        'CB_ID' => 'cb_' . $i,
                    ];

                    if ($post_id == $topic_first_post_id) {
                        define('BEGIN_CHECKBOX', true);
                    }
                }

                template()->assign_vars([
                    'FORUM_NAME' => htmlCHR($forum_name),
                    'U_VIEW_FORUM' => FORUM_URL . $forum_id,
                    'S_SPLIT_ACTION' => FORUM_PATH . 'modcp',
                    'S_HIDDEN_FIELDS' => $s_hidden_fields,
                    'S_FORUM_SELECT' => get_forum_select('admin', 'new_forum_id', $forum_id),
                    'POSTROW' => $postrowList,
                ]);
            }
        }
        template()->set_filenames(['body' => 'modcp_split.twig']);
        break;

    case 'ip':
        $anon = GUEST_UID;

        $rdns_ip_num = request()->query->get('rdns', '');

        if (!$post_id) {
            bb_die(__('NO_SUCH_POST'));
        }

        // Look up relevant data for this post
        $sql = 'SELECT * FROM ' . BB_POSTS . " WHERE post_id = {$post_id} AND forum_id = {$forum_id}";
        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not get poster IP information');
        }

        if (!($post_row = DB()->sql_fetchrow($result))) {
            bb_die(__('NO_SUCH_POST'));
        }

        $no_lookup = false;
        if (!$ip_this_post = TorrentPier\Helpers\IPHelper::decode($post_row['poster_ip'])) {
            $ip_this_post = __('NOT_AVAILABLE');
            $no_lookup = true;
        }

        $ip_this_post = ($rdns_ip_num == $ip_this_post) ? gethostbyaddr($ip_this_post) : $ip_this_post;

        $poster_id = $post_row['poster_id'];

        template()->assign_vars([
            'TPL_MODCP_IP' => true,
            'IP' => $ip_this_post,
            'U_LOOKUP_IP' => !$no_lookup ? 'modcp?mode=ip&amp;' . POST_POST_URL . "={$post_id}&amp;" . POST_TOPIC_URL . "={$topic_id}&amp;rdns={$ip_this_post}&amp;sid=" . userdata('session_id') : '',
        ]);
        unset($no_lookup);

        //
        // Get other IP's this user has posted under
        //
        $where_sql = ($poster_id == $anon) ? "post_username = '{$post_row['post_username']}'" : "poster_id = {$poster_id}";

        $sql = 'SELECT poster_ip, COUNT(*) AS postings FROM ' . BB_POSTS . " WHERE {$where_sql} GROUP BY poster_ip ORDER BY postings DESC LIMIT 100";
        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not get IP information for this user');
        }

        $iprowList = [];
        if ($row = DB()->sql_fetchrow($result)) {
            $i = 0;
            do {
                if ($row['poster_ip'] == $post_row['poster_ip']) {
                    template()->assign_vars(['POSTS' => $row['postings']]);
                    continue;
                }

                $no_lookup = false;
                if (!$ip = TorrentPier\Helpers\IPHelper::decode($row['poster_ip'])) {
                    $ip = __('NOT_AVAILABLE');
                    $no_lookup = true;
                }
                $ip = ($rdns_ip_num == $ip || $rdns_ip_num == 'all') ? gethostbyaddr($ip) : $ip;

                $iprowList[] = [
                    'ROW_CLASS' => !($i % 2) ? 'row4' : 'row5',
                    'IP' => $ip,
                    'POSTS' => $row['postings'],
                    'U_LOOKUP_IP' => !$no_lookup ? 'modcp?mode=ip&amp;' . POST_POST_URL . "={$post_id}&amp;" . POST_TOPIC_URL . "={$topic_id}&amp;rdns=" . $ip . '&amp;sid=' . userdata('session_id') : '',
                ];
                unset($no_lookup);

                $i++;
            } while ($row = DB()->sql_fetchrow($result));
        }

        //
        // Get other users who've posted under this IP
        //
        $sql = "SELECT
				u.user_id, u.user_rank,
				IF(u.user_id = {$anon}, p.post_username, u.username) AS username,
				COUNT(*) as postings
			FROM " . BB_USERS . ' u, ' . BB_POSTS . " p
			WHERE p.poster_id = u.user_id
				AND p.poster_ip = '" . $post_row['poster_ip'] . "'
			GROUP BY u.user_id, p.post_username
			ORDER BY postings DESC
			LIMIT 100";
        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not get posters information based on IP');
        }

        $userrowList = [];
        if ($row = DB()->sql_fetchrow($result)) {
            $i = 0;
            do {
                $userrowList[] = [
                    'ROW_CLASS' => !($i % 2) ? 'row4' : 'row5',
                    'USERNAME' => profile_url($row),
                    'POSTS' => $row['postings'],
                    'U_SEARCHPOSTS' => FORUM_PATH . "search?search_author=1&amp;uid={$row['user_id']}",
                ];

                $i++;
            } while ($row = DB()->sql_fetchrow($result));
        }

        template()->assign_vars([
            'IPROW' => $iprowList,
            'USERROW' => $userrowList,
        ]);

        template()->set_filenames(['body' => 'modcp.twig']);
        break;

    case 'post_pin':
    case 'post_unpin':
        $pin = ($mode == 'post_pin');
        $new_topic_status = $pin ? 1 : 0;

        if (count((array)$topic_csv)) {
            $sql = '
				SELECT topic_id, topic_title
				FROM ' . BB_TOPICS . "
				WHERE topic_id IN({$topic_csv})
					AND forum_id = {$forum_id}
					AND topic_show_first_post != " . TOPIC_MOVED . "
					AND topic_show_first_post != {$new_topic_status}
			";

            $topic_csv = [];

            foreach (DB()->fetch_rowset($sql) as $row) {
                $topic_csv[] = $row['topic_id'];
                $log_topics[$row['topic_id']] = $row['topic_title'];
            }

            if (!$topic_csv = get_id_csv($topic_csv)) {
                bb_die(__('NONE_SELECTED'));
            }

            DB()->query('
				UPDATE ' . BB_TOPICS . " SET
					topic_show_first_post = {$new_topic_status}
				WHERE topic_id IN({$topic_csv})
			");

            // Log action
            $type = $pin ? 'mod_post_pin' : 'mod_post_unpin';

            foreach ($log_topics as $topic_id => $topic_title) {
                log_action()->mod($type, [
                    'forum_id' => $forum_id,
                    'topic_id' => $topic_id,
                    'topic_title' => $topic_title,
                ]);
            }

            $msg = $pin ? __('POST_PINNED') : __('POST_UNPINNED');
            bb_die(return_msg_mcp($msg, $topic_id, $req_topics, $forum_id, $mode));
        } elseif ($topic_id) {
            $sql = '
				SELECT topic_id, topic_title
				FROM ' . BB_TOPICS . "
				WHERE topic_id = {$topic_id}
					AND forum_id = {$forum_id}
					AND topic_show_first_post != " . TOPIC_MOVED . "
					AND topic_show_first_post != {$new_topic_status}
				LIMIT 1
			";

            $topic_csv = [];

            foreach (DB()->fetch_rowset($sql) as $row) {
                $topic_csv[] = $row['topic_id'];
                $log_topics[$row['topic_id']] = $row['topic_title'];
            }

            if (!$topic_csv = get_id_csv($topic_csv)) {
                bb_die(__('NONE_SELECTED'));
            }

            DB()->query('
				UPDATE ' . BB_TOPICS . " SET
					topic_show_first_post = {$new_topic_status}
				WHERE topic_id IN({$topic_csv})
			");

            // Log action
            $type = $pin ? 'mod_post_pin' : 'mod_post_unpin';

            foreach ($log_topics as $topic_id => $topic_title) {
                log_action()->mod($type, [
                    'forum_id' => $forum_id,
                    'topic_id' => $topic_id,
                    'topic_title' => $topic_title,
                ]);
            }

            $msg = $pin ? __('POST_PINNED') : __('POST_UNPINNED');
            bb_die(return_msg_mcp($msg, $topic_id, $req_topics, $forum_id, $mode));
        }
        break;

    default:
        bb_die('invalid action');
        break;
}

template()->assign_vars(['PAGE_TITLE' => __('MOD_CP')]);

require PAGE_HEADER;

template()->pparse('body');

require PAGE_FOOTER;
