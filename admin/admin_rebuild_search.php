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

if (!empty($setmodules)) {
    if (IS_SUPER_ADMIN) {
        $module['GENERAL']['REBUILD_SEARCH_INDEX'] = basename(__FILE__);
    }
    return;
}
require __DIR__ . '/pagestart.php';

if (!IS_SUPER_ADMIN) {
    bb_die($lang['NOT_ADMIN']);
}

require INC_DIR . '/bbcode.php';

define('REBUILD_SEARCH_ABORTED', 0);  // when the user aborted the processing
define('REBUILD_SEARCH_PROCESSED', 1);  // when a batch of posts has been processed
define('REBUILD_SEARCH_COMPLETED', 2);  // when all the db posts have been processed

//
// Define initial vars
//
$def_post_limit = 50;
$def_refresh_rate = 3;
$def_time_limit = ($sys_time_limit = ini_get('max_execution_time')) ? $sys_time_limit : 30;

$last_session_data = get_rebuild_session_details('last', 'all');
$last_session_id = (int)$last_session_data['rebuild_session_id'];
$max_post_id = get_latest_post_id();
$start_time = TIMENOW;

$mode = isset($_REQUEST['mode']) ? (string)$_REQUEST['mode'] : '';

// check if the user has choosen to stop processing
if (isset($_REQUEST['cancel_button'])) {
    // update the rebuild_status
    if ($last_session_id) {
        DB()->query('
			UPDATE ' . BB_SEARCH_REBUILD . ' SET
				rebuild_session_status = ' . REBUILD_SEARCH_ABORTED . "
			WHERE rebuild_session_id = $last_session_id
		");
    }

    bb_die(sprintf($lang['REBUILD_SEARCH_ABORTED'], $last_session_data['end_post_id']) . '<br /><br />' . sprintf($lang['CLICK_RETURN_REBUILD_SEARCH'], '<a href="admin_rebuild_search.php">', '</a>'));
}

// from which post to start processing
$start = isset($_REQUEST['start']) ? abs((int)$_REQUEST['start']) : 0;

// get the total number of posts in the db
$total_posts = get_total_posts();

// clear the search tables and clear mode (delete or truncate)
$clear_search = isset($_REQUEST['clear_search']) ? (int)$_REQUEST['clear_search'] : 0;

// get the number of total/session posts already processed
$total_posts_processed = ($start != 0) ? get_total_posts('before', $last_session_data['end_post_id']) : 0;
$session_posts_processed = ($mode == 'refresh') ? get_processed_posts('session') : 0;

// find how many posts aren't processed
$total_posts_processing = $total_posts - $total_posts_processed;

// how many posts to process in this session
$session_posts_processing = isset($_REQUEST['session_posts_processing']) ? (int)$_REQUEST['session_posts_processing'] : null;
if (null !== $session_posts_processing) {
    if ($mode == 'submit') {
        // check if we passed over total_posts just after submitting
        if ($session_posts_processing + $total_posts_processed > $total_posts) {
            $session_posts_processing = $total_posts - $total_posts_processed;
        }
    }
    // correct it when posts are deleted during processing
    $session_posts_processing = ($session_posts_processing > $total_posts) ? $total_posts : $session_posts_processing;
} else {
    // if we have finished, get all the posts, else only the remaining
    $session_posts_processing = (!$total_posts_processing) ? $total_posts : $total_posts_processing;
}

// how many posts to process per cycle
$post_limit = isset($_REQUEST['post_limit']) ? (int)$_REQUEST['post_limit'] : $def_post_limit;

// correct the post_limit when we pass over it
if ($session_posts_processed + $post_limit > $session_posts_processing) {
    $post_limit = $session_posts_processing - $session_posts_processed;
}

// how much time to wait per cycle
if (isset($_REQUEST['time_limit'])) {
    $time_limit = (int)$_REQUEST['time_limit'];
} else {
    $time_limit = $def_time_limit;
    $time_limit_explain = $lang['TIME_LIMIT_EXPLAIN'];

    // check for webserver timeout (IE returns null)
    if (isset($_SERVER['HTTP_KEEP_ALIVE'])) {
        // get webserver timeout
        $webserver_timeout = (int)$_SERVER['HTTP_KEEP_ALIVE'];
        $time_limit_explain .= '<br />' . sprintf($lang['TIME_LIMIT_EXPLAIN_WEBSERVER'], $webserver_timeout);

        if ($time_limit > $webserver_timeout) {
            $time_limit = $webserver_timeout;
        }
    }
}

// how much time to wait between page refreshes
$refresh_rate = isset($_REQUEST['refresh_rate']) ? (int)$_REQUEST['refresh_rate'] : $def_refresh_rate;

// check if the user gave wrong input
if ($mode == 'submit') {
    if (($session_posts_processing || $post_limit || $refresh_rate || $time_limit) <= 0) {
        bb_die($lang['WRONG_INPUT'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_REBUILD_SEARCH'], '<a href="admin_rebuild_search.php">', '</a>'));
    }
}

// Increase maximum execution time in case of a lot of posts, but don't complain about it if it isn't allowed.
set_time_limit($time_limit + 20);

// check if we are should start processing
if ($mode == 'submit' || $mode == 'refresh') {
    // check if we are in the beginning of processing
    if ($start == 0) {
        $last_session_data = get_empty_last_session_data();
        clear_search_tables($clear_search);
    }

    // get the db sizes
    list($search_data_size, $search_index_size, $search_tables_size) = get_db_sizes();

    // get the post subject/text of each post
    $result = DB()->query("
		SELECT
			pt.post_id, pt.post_text,
			IF(p.post_id = t.topic_first_post_id, t.topic_title, '') AS post_subject
		FROM
			" . BB_POSTS_TEXT . ' pt,
			' . BB_POSTS . ' p,
			' . BB_TOPICS . ' t
		WHERE p.post_id = pt.post_id
			AND t.topic_id = p.topic_id
			AND p.poster_id NOT IN(' . BOT_UID . ")
			AND pt.post_id >= $start
		ORDER BY pt.post_id ASC
		LIMIT $post_limit
	");

    $expire_time = $start_time + $time_limit - 5;
    $start_post_id = $end_post_id = $num_rows = 0;
    $timer_expired = false;
    $words_sql = array();

    while ($row = DB()->fetch_next($result) and !$timer_expired) {
        set_time_limit(600);
        $start_post_id = ($num_rows == 0) ? $row['post_id'] : $start_post_id;
        $end_post_id = $row['post_id'];

        // Get search words
        $s_post_text = str_replace('\n', "\n", $row['post_text']);
        $s_post_subject = str_replace('\n', "\n", $row['post_subject']);
        $words_sql[] = array(
            'post_id' => (int)$row['post_id'],
            'search_words' => add_search_words($row['post_id'], stripslashes($s_post_text), stripslashes($s_post_subject), true),
        );

        $timer_expired = (TIMENOW > $expire_time);
        $num_rows++;
    }

    // Store search words
    if ($words_sql) {
        DB()->query('REPLACE INTO ' . BB_POSTS_SEARCH . DB()->build_array('MULTI_INSERT', $words_sql));
    }

    // find how much time the last cycle took
    $last_cycle_time = (int)(TIMENOW - $start_time);

    // check if we had any data
    if ($num_rows != 0) {
        if ($mode == 'submit') {
            // insert a new session entry
            $args = DB()->build_array('INSERT', array(
                'end_post_id' => (int)$end_post_id,
                'end_time' => (int)TIMENOW,
                'last_cycle_time' => (int)$last_cycle_time,
                'session_time' => (int)$last_cycle_time,
                'session_posts' => (int)$num_rows,
                'session_cycles' => (int)1,
                'start_post_id' => (int)$start_post_id,
                'start_time' => (int)$start_time,
                'search_size' => (int)$search_tables_size,
                'rebuild_session_status' => REBUILD_SEARCH_PROCESSED,
            ));
            DB()->query('REPLACE INTO ' . BB_SEARCH_REBUILD . $args);
        } else {
            // refresh

            // update the last session entry
            DB()->query('
				UPDATE ' . BB_SEARCH_REBUILD . " SET
					end_post_id     = $end_post_id,
					end_time        = " . TIMENOW . ",
					last_cycle_time = $last_cycle_time,
					session_time    = session_time + $last_cycle_time,
					session_posts   = session_posts + $num_rows,
					session_cycles  = session_cycles + 1,
					rebuild_session_status = " . REBUILD_SEARCH_PROCESSED . "
				WHERE rebuild_session_id = $last_session_id
			");
        }
    }

    $last_session_data = get_rebuild_session_details('last', 'all');
    $template->assign_vars(array('TPL_REBUILD_SEARCH_PROGRESS' => true));

    $processing_messages = '';
    $processing_messages .= $timer_expired ? sprintf($lang['TIMER_EXPIRED'], TIMENOW - $start_time) : '';
    $processing_messages .= ($start == 0 && $clear_search) ? $lang['CLEARED_SEARCH_TABLES'] : '';

    // check if we have reached the end of our post processing
    $session_posts_processed = get_processed_posts('session');
    $total_posts_processed = get_total_posts('before', $last_session_data['end_post_id']);
    $total_posts = get_total_posts();

    if ($session_posts_processed < $session_posts_processing && $total_posts_processed < $total_posts) {
        $form_parameters = '&start=' . ($end_post_id + 1);
        $form_parameters .= '&session_posts_processing=' . $session_posts_processing;
        $form_parameters .= '&post_limit=' . $post_limit;
        $form_parameters .= '&time_limit=' . $time_limit;
        $form_parameters .= '&refresh_rate=' . $refresh_rate;

        $form_action = 'admin_rebuild_search.php' . '?mode=refresh' . $form_parameters;
        $next_button = $lang['NEXT'];
        $progress_bar_img = $images['progress_bar'];

        $processing_messages .= sprintf($lang['PROCESSING_NEXT_POSTS'], $post_limit);

        meta_refresh($form_action, $refresh_rate);

        // create the meta tag for refresh
        $template->assign_vars(array(
            'CANCEL_BUTTON' => true,
        ));
    } else {
        // end of processing

        $form_action = 'admin_rebuild_search.php';
        $next_button = $lang['FINISHED'];
        $progress_bar_img = $images['progress_bar_full'];

        $processing_messages .= ($session_posts_processed < $session_posts_processing) ? sprintf($lang['DELETED_POSTS'], $session_posts_processing - $session_posts_processed) : '';
        $processing_messages .= ($total_posts_processed == $total_posts) ? $lang['ALL_POSTS_PROCESSED'] : $lang['ALL_SESSION_POSTS_PROCESSED'];

        // if we have processed all the db posts we need to update the rebuild_status
        DB()->query('UPDATE ' . BB_SEARCH_REBUILD . ' SET
				rebuild_session_status = ' . REBUILD_SEARCH_COMPLETED . "
			WHERE rebuild_session_id = $last_session_id
				AND end_post_id = $max_post_id
		");

        // optimize all search tables when finished
        $table_ary = array(BB_POSTS_SEARCH);

        foreach ($table_ary as $table) {
            DB()->query("ANALYZE  TABLE $table");
            DB()->query("OPTIMIZE TABLE $table");
        }

        $processing_messages .= '<br />' . $lang['ALL_TABLES_OPTIMIZED'];
    }

    // calculate the percent
    if ($session_posts_processing > 0) {
        $session_percent = ($session_posts_processed / $session_posts_processing) * 100;
    } else {
        $session_percent = 100;
    }
    if ($total_posts > 0) {
        $total_percent = ($total_posts_processed / $total_posts) * 100;
    } else {
        $total_percent = 100;
    }

    // get the db sizes
    list($search_data_size, $search_index_size, $search_tables_size) = get_db_sizes();

    // calculate the final (estimated) values
    $final_search_tables_size = '';

    if ($search_tables_size) {
        $start_search_tables_size = $last_session_data['search_size'];
        $final_search_tables_size = $start_search_tables_size + round(($search_tables_size - $start_search_tables_size) * (100 / $session_percent));
    }

    // calculate various times
    $session_time = $last_session_data['session_time'];
    if ($last_session_data['session_cycles'] > 0) {
        $session_average_cycle_time = round($session_time / $last_session_data['session_cycles']);
    } else {
        $session_average_cycle_time = 0;
    }
    $session_estimated_time = round($session_time * (100 / $session_percent)) - $session_time;

    // create the percent boxes
    create_percent_box('session', create_percent_color($session_percent), $session_percent);
    create_percent_box('total', create_percent_color($total_percent), $total_percent);

    $template->assign_vars(array(
        'L_NEXT' => $next_button,
        'L_TIME_LAST_POSTS_ADMIN' => sprintf($lang['TIME_LAST_POSTS'], $num_rows),

        'PROCESSING_POSTS' => sprintf($lang['PROCESSED_POST_IDS'], $start_post_id, $end_post_id),
        'PROCESSING_MESSAGES' => $processing_messages,
        'PROGRESS_BAR_IMG' => $progress_bar_img,

        'SESSION_DETAILS' => sprintf($lang['PROCESS_DETAILS'], $session_posts_processed - $num_rows + 1, $session_posts_processed, $session_posts_processing),
        'SESSION_PERCENT' => sprintf($lang['PERCENT_COMPLETED'], round($session_percent, 2)),

        'TOTAL_DETAILS' => sprintf($lang['PROCESS_DETAILS'], $total_posts_processed - $num_rows + 1, $total_posts_processed, $total_posts),
        'TOTAL_PERCENT' => sprintf($lang['PERCENT_COMPLETED'], round($total_percent, 2)),

        'LAST_CYCLE_TIME' => delta_time(TIMENOW),
        'SESSION_TIME' => delta_time($last_session_data['start_time']),
        'SESSION_AVERAGE_CYCLE_TIME' => delta_time((int)$session_average_cycle_time, 0),
        'SESSION_ESTIMATED_TIME' => delta_time((int)$session_estimated_time, 0),

        'SEARCH_TABLES_SIZE' => humn_size($search_tables_size),
        'FINAL_SEARCH_TABLES_SIZE' => humn_size($final_search_tables_size),
        'SEARCH_DATA_SIZE' => humn_size($search_data_size),
        'SEARCH_INDEX_SIZE' => humn_size($search_index_size),

        'START_POST' => $last_session_data['start_post_id'],
        'POST_LIMIT' => $num_rows,
        'TIME_LIMIT' => $time_limit,
        'REFRESH_RATE' => $refresh_rate,

        'S_REBUILD_SEARCH_ACTION' => $form_action,
    ));
} else {// show the input page
    // create the page
    // used only with the select input
    $post_limit_hidden = ($def_post_limit > $total_posts) ? $total_posts : $def_post_limit;

    $s_hidden_fields = '<input type="hidden" name="post_limit_stored" value="' . $post_limit_hidden . '" />';
    $s_hidden_fields .= '<input type="hidden" name="total_posts_stored" value="' . $total_posts . '" />';

    $next_start_post_id = 0;
    $last_saved_processing = '';
    $clear_search_disabled = '';

    if ($last_session_data['rebuild_session_id']) {
        $last_saved_post_id = $last_session_data['end_post_id'];
        $next_start_post_id = $last_saved_post_id + 1;
        $last_saved_date = bb_date($last_session_data['end_time']);

        // check our last status
        if ($last_session_data['rebuild_session_status'] == REBUILD_SEARCH_PROCESSED) {
            $last_saved_processing = sprintf($lang['INFO_PROCESSING_STOPPED'], $last_saved_post_id, $total_posts_processed, $last_saved_date);
            $clear_search_disabled = 'disabled="disabled"';

            $template->assign_block_vars('start_select_input', array());
        } elseif ($last_session_data['rebuild_session_status'] == REBUILD_SEARCH_ABORTED) {
            $last_saved_processing = sprintf($lang['INFO_PROCESSING_ABORTED'], $last_saved_post_id, $total_posts_processed, $last_saved_date);
            // check if the interrupted cycle has finished
            if (TIMENOW - $last_session_data['end_time'] < $last_session_data['last_cycle_time']) {
                $last_saved_processing .= '<br />' . $lang['INFO_PROCESSING_ABORTED_SOON'];
            }
            $clear_search_disabled = 'disabled="disabled"';

            $template->assign_block_vars('start_select_input', array());
        } else {
            // when finished

            if ($last_session_data['end_post_id'] < $max_post_id) {
                $last_saved_processing = sprintf($lang['INFO_PROCESSING_FINISHED_NEW'], $last_saved_post_id, $total_posts_processed, $last_saved_date, $total_posts - $total_posts_processed);
                $clear_search_disabled = 'disabled="disabled"';

                $template->assign_block_vars('start_select_input', array());
            } else {
                $last_saved_processing = sprintf($lang['INFO_PROCESSING_FINISHED'], $total_posts, $last_saved_date);

                $template->assign_block_vars('start_text_input', array());
            }
        }

        $template->assign_block_vars('last_saved_info', array());
    } else {
        $template->assign_block_vars('start_text_input', array());
    }

    // create the output of page
    $template->assign_vars(array(
        'TPL_REBUILD_SEARCH_MAIN' => true,

        'L_TIME_LIMIT_EXPLAIN' => $time_limit_explain,

        'NEXT_START_POST_ID' => $next_start_post_id,
        'CLEAR_SEARCH_DISABLED' => $clear_search_disabled,
        'SESSION_POSTS_PROCESSING' => $session_posts_processing,
        'POST_LIMIT' => $post_limit,
        'REFRESH_RATE' => $refresh_rate,
        'TIME_LIMIT' => $time_limit,

        'LAST_SAVED_PROCESSING' => $last_saved_processing,

        'SESSION_ID' => $userdata['session_id'],

        'S_HIDDEN_FIELDS' => $s_hidden_fields,
        'S_REBUILD_SEARCH_ACTION' => 'admin_rebuild_search.php?mode=submit',
    ));
}

print_page('admin_rebuild_search.tpl', 'admin');

//
// Functions
//
function get_db_sizes()
{
    $search_data_size = $search_index_size = 0;
    $search_table_like = DB()->escape(BB_POSTS_SEARCH);

    $sql = 'SHOW TABLE STATUS FROM `' . DB()->selected_db . "` LIKE '$search_table_like'";

    foreach (DB()->fetch_rowset($sql) as $row) {
        $search_data_size += $row['Data_length'];
        $search_index_size += $row['Index_length'];
    }

    return array($search_data_size, $search_index_size, $search_data_size + $search_index_size);
}

// get the latest post_id in the forum
function get_latest_post_id()
{
    $row = DB()->fetch_row('SELECT MAX(post_id) as post_id FROM ' . BB_POSTS_TEXT);

    return (int)$row['post_id'];
}

function get_empty_last_session_data()
{
    return array(
        'rebuild_session_id' => 0,
        'start_post_id' => 0,
        'end_post_id' => 0,
        'start_time' => 0,
        'end_time' => 0,
        'last_cycle_time' => 0,
        'session_time' => 0,
        'session_posts' => 0,
        'session_cycles' => 0,
        'search_size' => 0,
        'rebuild_session_status' => REBUILD_SEARCH_COMPLETED,
    );
}

// get some or all of the rebuild details of a specific session or of the last session
// $id is the id or the 'last' id
// $details is one of the fields or 'all' of them
function get_rebuild_session_details($id, $details = 'all')
{
    $session_details = get_empty_last_session_data();

    if ($id != 'last') {
        $sql = 'SELECT * FROM ' . BB_SEARCH_REBUILD . " WHERE rebuild_session_id = $id";
    } else {
        $sql = 'SELECT * FROM ' . BB_SEARCH_REBUILD . ' ORDER BY rebuild_session_id DESC LIMIT 1';
    }

    if ($row = DB()->fetch_row($sql)) {
        $session_details = ($details == 'all') ? $row : $row[$details];
    }

    return $session_details;
}

// get the number of processed posts in the last session or in all sessions
// 'total' to get the sum of posts of all sessions
// 'session' to get the posts of the last session
function get_processed_posts($mode = 'session')
{
    global $last_session_data;

    if ($mode == 'total') {
        $sql = 'SELECT SUM(session_posts) as posts FROM ' . BB_SEARCH_REBUILD;
        $row = DB()->fetch_row($sql);
    } else {
        $row['posts'] = $last_session_data['session_posts'];
    }

    return (int)$row['posts'];
}

// how many posts are in the db before or after a specific post_id
// after/before require and the post_id
function get_total_posts($mode = 'after', $post_id = 0)
{
    if ($post_id) {
        $sql = 'SELECT COUNT(post_id) as total_posts FROM ' . BB_POSTS_TEXT . '
			WHERE post_id ' . (($mode == 'after') ? '>= ' : '<= ') . (int)$post_id;
    } else {
        $sql = 'SELECT COUNT(*) as total_posts FROM ' . BB_POSTS_TEXT;
    }

    $row = DB()->fetch_row($sql);
    $totalPosts = (int)$row['total_posts'];

    if ($totalPosts < 0) {
        return 0;
    }

    return $totalPosts;
}

function clear_search_tables($mode = '')
{
    DB()->query('DELETE FROM ' . BB_SEARCH_REBUILD);

    if ($mode) {
        $table_ary = array(BB_POSTS_SEARCH);

        foreach ($table_ary as $table) {
            $sql = (($mode == 1) ? 'DELETE FROM ' : 'TRUNCATE TABLE ') . $table;
            DB()->query($sql);
        }
    }
}

// Create the percent color
// We use an array with the color percent limits.
// One color stays constantly at FF when the percent is between its limits
// and we adjust the other 2 accordingly to percent, from 200 to 0.
// We limit the result to 200, in order to avoid white (255).
function create_percent_color($percent)
{
    $percent_ary = array(
        'r' => array(86, 100),
        'g' => array(0, 50),
        'b' => array(51, 85),
    );

    foreach ($percent_ary as $key => $value) {
        if ($percent <= $value[1]) {
            $percent_color = create_color($key, round(200 - ($percent - $value[0]) * (200 / ($value[1] - $value[0]))));
            break;
        }
    }

    return $percent_color;
}

// create the hex representation of color
function create_color($mode, $code)
{
    return (($mode == 'r') ? 'FF' : sprintf('%02X', $code)) . (($mode == 'g') ? 'FF' : sprintf('%02X', $code)) . (($mode == 'b') ? 'FF' : sprintf('%02X', $code));
}

// create the percent bar & box
function create_percent_box($box, $percent_color, $percent_width)
{
    global $template;

    if ($box == 'session') {
        $template->assign_vars(array(
            'SESSION_PERCENT_BOX' => true,
            'SESSION_PERCENT_COLOR' => $percent_color,
            'SESSION_PERCENT_WIDTH' => round($percent_width),
        ));
    } else {
        $template->assign_vars(array(
            'TOTAL_PERCENT_BOX' => true,
            'TOTAL_PERCENT_COLOR' => $percent_color,
            'TOTAL_PERCENT_WIDTH' => round($percent_width),
        ));
    }
}
