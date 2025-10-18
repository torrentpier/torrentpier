<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

/**
 * Sort multi-dimensional array
 *
 * @param array $sort_array
 * @param string|int $key
 * @param int $sort_order
 * @return array
 */
function sort_multi_array(array $sort_array, $key, int $sort_order = SORT_ASC): array
{
    $keys = array_column($sort_array, $key);
    array_multisort($keys, $sort_order, $sort_array);

    return $sort_array;
}

/**
 * Returns size of the upload directory in human readable format
 *
 * @return string
 */
function get_formatted_dirsize()
{
    global $lang, $upload_dir;

    $upload_dir_size = 0;

    if ($dirname = opendir($upload_dir)) {
        while ($file = readdir($dirname)) {
            if (
                $file != 'index.php' &&
                $file != '.htaccess' &&
                !is_dir($upload_dir . '/' . $file) &&
                !is_link($upload_dir . '/' . $file)
            ) {
                $upload_dir_size += filesize($upload_dir . '/' . $file);
            }
        }
        closedir($dirname);
    } else {
        return $lang['NOT_AVAILABLE'];
    }

    return humn_size($upload_dir_size);
}

/**
 * Build SQL statement for the search feature
 *
 * @param $order_by
 * @param $total_rows
 * @return array
 */
function search_attachments($order_by, &$total_rows)
{
    global $lang;

    $where_sql = [];

    // Author name search
    $search_author = get_var('search_author', '');
    if ($search_author) {
        // Bring in line with 2.0.x expected username
        $search_author = addslashes(html_entity_decode($search_author));
        $search_author = stripslashes(clean_username($search_author));

        // Prepare for directly going into sql query
        $search_author = str_replace('*', '%', DB()->escape($search_author));

        // We need the post_id's, because we want to query the Attachment Table
        $sql = 'SELECT user_id FROM ' . BB_USERS . " WHERE username LIKE '$search_author'";
        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not obtain list of matching users (searching for: ' . $search_author . ')');
        }

        $matching_userids = '';
        if ($row = DB()->sql_fetchrow($result)) {
            do {
                $matching_userids .= (($matching_userids != '') ? ', ' : '') . $row['user_id'];
            } while ($row = DB()->sql_fetchrow($result));

            DB()->sql_freeresult($result);
        } else {
            bb_die($lang['NO_ATTACH_SEARCH_MATCH']);
        }

        $where_sql[] = ' (t.user_id_1 IN (' . $matching_userids . ')) ';
    }

    // Search Keyword
    $search_keyword_fname = get_var('search_keyword_fname', '');
    if ($search_keyword_fname) {
        $match_word = str_replace('*', '%', $search_keyword_fname);
        $where_sql[] = " (a.real_filename LIKE '" . DB()->escape($match_word) . "') ";
    }

    $search_keyword_comment = get_var('search_keyword_comment', '');
    if ($search_keyword_comment) {
        $match_word = str_replace('*', '%', $search_keyword_comment);
        $where_sql[] = " (a.comment LIKE '" . DB()->escape($match_word) . "') ";
    }

    // Search Download Count
    $search_count_smaller = get_var('search_count_smaller', '');
    $search_count_greater = get_var('search_count_greater', '');
    if ($search_count_smaller != '') {
        $where_sql[] = ' (a.download_count < ' . (int)$search_count_smaller . ') ';
    } elseif ($search_count_greater != '') {
        $where_sql[] = ' (a.download_count > ' . (int)$search_count_greater . ') ';
    }

    // Search Filesize
    $search_size_smaller = get_var('search_size_smaller', '');
    $search_size_greater = get_var('search_size_greater', '');
    if ($search_size_smaller != '') {
        $where_sql[] = ' (a.filesize < ' . (int)$search_size_smaller . ') ';
    } elseif ($search_size_greater != '') {
        $where_sql[] = ' (a.filesize > ' . (int)$search_size_greater . ') ';
    }

    // Search Attachment Time
    $search_days_greater = get_var('search_days_greater', '');
    if ($search_days_greater) {
        $where_sql[] = ' (a.filetime < ' . (TIMENOW - ((int)$search_days_greater * 86400)) . ') ';
    }

    // Search Forum
    $search_forum = get_var('search_forum', '');
    if ($search_forum) {
        $where_sql[] = ' (p.forum_id = ' . (int)$search_forum . ') ';
    }

    $sql = 'SELECT a.*, t.post_id, p.post_time, p.topic_id
		FROM ' . BB_ATTACHMENTS . ' t, ' . BB_ATTACHMENTS_DESC . ' a, ' . BB_POSTS . ' p WHERE ';

    if (count($where_sql) > 0) {
        $sql .= implode('AND', $where_sql) . ' AND ';
    }

    $sql .= 't.post_id = p.post_id AND a.attach_id = t.attach_id ';

    $total_rows_sql = $sql;

    $sql .= $order_by;

    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not query attachments #1');
    }

    $attachments = DB()->sql_fetchrowset($result);
    $num_attach = DB()->num_rows($result);
    DB()->sql_freeresult($result);

    if ($num_attach == 0) {
        bb_die($lang['NO_ATTACH_SEARCH_MATCH']);
    }

    if (!($result = DB()->sql_query($total_rows_sql))) {
        bb_die('Could not query attachments #2');
    }

    $total_rows = DB()->num_rows($result);
    DB()->sql_freeresult($result);

    return $attachments;
}
