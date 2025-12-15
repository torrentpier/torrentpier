<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    $module['FORUMS']['MANAGE'] = basename(__FILE__);

    return;
}

require __DIR__ . '/pagestart.php';

$post = request()->post->all();
array_deep($post, 'trim');

$s = '';

$default_forum_auth = [
    'auth_view' => AUTH_ALL,
    'auth_read' => AUTH_ALL,
    'auth_post' => AUTH_REG,
    'auth_reply' => AUTH_REG,
    'auth_edit' => AUTH_REG,
    'auth_delete' => AUTH_REG,
    'auth_sticky' => AUTH_MOD,
    'auth_announce' => AUTH_MOD,
    'auth_vote' => AUTH_REG,
    'auth_pollcreate' => AUTH_REG,
    'auth_attachments' => AUTH_REG,
    'auth_download' => AUTH_REG,
];

$mode = (string)request()->get('mode', '');

$cat_forums = get_cat_forums();

if ($orphan_sf_sql = get_orphan_sf($cat_forums)) {
    fix_orphan_sf($cat_forums, $orphan_sf_sql, true);
}
$forum_parent = $cat_id = 0;
$forumname = '';

if (request()->get('addforum') || request()->get('addcategory')) {
    $mode = request()->get('addforum') ? 'addforum' : 'addcat';

    $addforum = request()->post->all('addforum');
    $forumname_arr = request()->post->all('forumname');
    if ($addforum && $forumname_arr && $mode == 'addforum' && is_array($addforum)) {
        $req_cat_id = array_keys($addforum);
        $cat_id = reset($req_cat_id);
        $forumname = stripslashes($forumname_arr[$cat_id]);
    }
}

$show_main_page = false;

if ($mode) {
    switch ($mode) {
        case 'addforum':
        case 'editforum':
            //
            // Show form to create/modify a forum
            //
            if ($mode == 'editforum') {
                // $newmode determines if we are going to INSERT or UPDATE after posting?

                $l_title = __('EDIT_FORUM');
                $newmode = 'modforum';
                $buttonvalue = __('UPDATE');

                $forum_id = (int)request()->query->get(POST_FORUM_URL, 0);

                $row = get_info('forum', $forum_id);

                $cat_id = $row['cat_id'];
                $forumname = $row['forum_name'];
                $forumdesc = $row['forum_desc'];
                $forumstatus = $row['forum_status'];
                $forum_display_sort = $row['forum_display_sort'];
                $forum_display_order = $row['forum_display_order'];
                $forum_parent = $row['forum_parent'];
                $show_on_index = $row['show_on_index'];
                $prune_days = $row['prune_days'];
                $forum_tpl_id = $row['forum_tpl_id'];
                $allow_reg_tracker = $row['allow_reg_tracker'];
                $allow_porno_topic = $row['allow_porno_topic'];
                $self_moderated = $row['self_moderated'];
            } else {
                $l_title = __('CREATE_FORUM');
                $newmode = 'createforum';
                $buttonvalue = __('CREATE_FORUM');

                $forumdesc = '';
                $forumstatus = FORUM_UNLOCKED;
                $forum_display_sort = 0;
                $forum_display_order = 0;
                $forum_id = '';
                $show_on_index = 1;
                $prune_days = 0;
                $forum_tpl_id = 0;
                $allow_reg_tracker = 0;
                $allow_porno_topic = 0;
                $self_moderated = 0;
            }

            if (request()->has('forum_parent')) {
                $forum_parent = request()->getInt('forum_parent');

                if ($parent = get_forum_data($forum_parent, $cat_forums)) {
                    $cat_id = $parent['cat_id'];
                }
            } elseif (request()->has(POST_CAT_URL)) {
                $cat_id = request()->getInt(POST_CAT_URL);
            }

            $catlist = get_list('category', $cat_id, true);
            $forumlocked = $forumunlocked = '';

            $forumstatus == FORUM_LOCKED ? $forumlocked = 'selected' : $forumunlocked = 'selected';

            $statuslist = '<option value="' . FORUM_UNLOCKED . '" ' . $forumunlocked . '>' . __('STATUS_UNLOCKED') . '</option>\n';
            $statuslist .= '<option value="' . FORUM_LOCKED . '" ' . $forumlocked . '>' . __('STATUS_LOCKED') . '</option>\n';

            $forum_display_sort_list = get_forum_display_sort_option($forum_display_sort, 'list', 'sort');
            $forum_display_order_list = get_forum_display_sort_option($forum_display_order, 'list', 'order');

            $s_hidden_fields = '<input type="hidden" name="mode" value="' . $newmode . '" /><input type="hidden" name="' . POST_FORUM_URL . '" value="' . $forum_id . '" />';

            $s_parent = '<option value="-1">&nbsp;' . __('SF_NO_PARENT') . '</option>\n';
            $sel_forum = ($forum_parent && request()->get('forum_parent') === null) ? $forum_id : $forum_parent;
            $s_parent .= sf_get_list('forum', $cat_forums, $forum_parent, $forum_id, $sel_forum);

            template()->assign_vars([
                'TPL_EDIT_FORUM' => true,

                'S_FORUM_DISPLAY_SORT_LIST' => $forum_display_sort_list,
                'S_FORUM_DISPLAY_ORDER_LIST' => $forum_display_order_list,
                'S_FORUM_ACTION' => 'admin_forums.php',
                'S_HIDDEN_FIELDS' => $s_hidden_fields,
                'S_SUBMIT_VALUE' => $buttonvalue,
                'S_CAT_LIST' => $catlist,
                'S_STATUS_LIST' => $statuslist,

                'SHOW_ON_INDEX' => $show_on_index,
                'S_PARENT_FORUM' => $s_parent,
                'CAT_LIST_CLASS' => $forum_parent ? 'hidden' : '',
                'SHOW_ON_INDEX_CLASS' => (!$forum_parent) ? 'hidden' : '',
                'TPL_SELECT' => get_select('forum_tpl', $forum_tpl_id, 'html', __('TEMPLATE_DISABLE')),
                'ALLOW_REG_TRACKER' => build_select('allow_reg_tracker', [__('DISALLOWED') => 0, __('ALLOWED') => 1], $allow_reg_tracker),
                'ALLOW_PORNO_TOPIC' => build_select('allow_porno_topic', [__('NONE') => 0, __('YES') => 1], $allow_porno_topic),
                'SELF_MODERATED' => build_select('self_moderated', [__('NONE') => 0, __('YES') => 1], $self_moderated),

                'L_FORUM_TITLE' => $l_title,

                'PRUNE_DAYS' => $prune_days,
                'FORUM_NAME' => htmlCHR($forumname),
                'DESCRIPTION' => htmlCHR($forumdesc),
            ]);
            break;

        case 'createforum':
            //
            // Create a forum in the DB
            //
            $cat_id = (int)request()->post->get(POST_CAT_URL, 0);
            $forum_name = (string)request()->post->get('forumname', '');
            $forum_desc = (string)request()->post->get('forumdesc', '');
            $forum_status = (int)request()->post->get('forumstatus', 0);

            $prune_days = (int)request()->post->get('prune_days', 0);

            $forum_parent = (request()->post->get('forum_parent', -1) != -1) ? (int)request()->post->get('forum_parent', -1) : 0;
            $show_on_index = $forum_parent ? (int)request()->post->get('show_on_index', 0) : 1;

            $forum_display_sort = (int)request()->post->get('forum_display_sort', 0);
            $forum_display_order = (int)request()->post->get('forum_display_order', 0);

            $forum_tpl_id = (int)request()->post->get('forum_tpl_select', 0);
            $allow_reg_tracker = (int)request()->post->get('allow_reg_tracker', 0);
            $allow_porno_topic = (int)request()->post->get('allow_porno_topic', 0);
            $self_moderated = (int)request()->post->get('self_moderated', 0);

            if (!$forum_name) {
                bb_die('Can not create a forum without a name');
            }

            if ($forum_parent) {
                if (!$parent = get_forum_data($forum_parent, $cat_forums)) {
                    bb_die('Parent forum with id <b>' . $forum_parent . '</b> not found');
                }

                $cat_id = $parent['cat_id'];
                $forum_parent = $parent['forum_parent'] ?: $parent['forum_id'];
                $forum_order = $parent['forum_order'] + 5;
            } else {
                $max_order = get_max_forum_order($cat_id);
                $forum_order = $max_order + 5;
            }

            // Default permissions of public forum
            $field_sql = $value_sql = '';

            foreach ($default_forum_auth as $field => $value) {
                $field_sql .= ", {$field}";
                $value_sql .= ", {$value}";
            }

            $forum_name_sql = DB()->escape($forum_name);
            $forum_desc_sql = DB()->escape($forum_desc);

            $columns = ' forum_name,   cat_id,   forum_desc,   forum_order,  forum_status,  prune_days,  forum_parent,  show_on_index,  forum_display_sort,  forum_display_order,  forum_tpl_id,  allow_reg_tracker,  allow_porno_topic,  self_moderated' . $field_sql;
            $values = "'{$forum_name_sql}', {$cat_id}, '{$forum_desc_sql}', {$forum_order}, {$forum_status}, {$prune_days}, {$forum_parent}, {$show_on_index}, {$forum_display_sort}, {$forum_display_order}, {$forum_tpl_id}, {$allow_reg_tracker}, {$allow_porno_topic}, {$self_moderated}" . $value_sql;

            DB()->query('INSERT INTO ' . BB_FORUMS . " ({$columns}) VALUES ({$values})");

            renumber_order('forum', $cat_id);
            forum_tree(refresh: true);
            CACHE('bb_cache')->rm();

            bb_die(__('FORUMS_UPDATED') . '<br /><br />' . sprintf(__('CLICK_RETURN_FORUMADMIN'), '<a href="admin_forums.php?' . POST_CAT_URL . '=' . $cat_id . '">', '</a>') . '<br /><br />' . sprintf(__('CLICK_RETURN_ADMIN_INDEX'), '<a href="index.php?pane=right">', '</a>'));

            break;

        case 'modforum':
            //
            // Modify a forum in the DB
            //
            $cat_id = (int)request()->post->get(POST_CAT_URL, 0);
            $forum_id = (int)request()->post->get(POST_FORUM_URL, 0);
            $forum_name = (string)request()->post->get('forumname', '');
            $forum_desc = (string)request()->post->get('forumdesc', '');
            $forum_status = (int)request()->post->get('forumstatus', 0);
            $prune_days = (int)request()->post->get('prune_days', 0);

            $forum_parent = (request()->post->get('forum_parent', -1) != -1) ? (int)request()->post->get('forum_parent', -1) : 0;
            $show_on_index = $forum_parent ? (int)request()->post->get('show_on_index', 0) : 1;

            $forum_display_order = (int)request()->post->get('forum_display_order', 0);
            $forum_display_sort = (int)request()->post->get('forum_display_sort', 0);
            $forum_tpl_id = (int)request()->post->get('forum_tpl_select', 0);
            $allow_reg_tracker = (int)request()->post->get('allow_reg_tracker', 0);
            $allow_porno_topic = (int)request()->post->get('allow_porno_topic', 0);
            $self_moderated = (int)request()->post->get('self_moderated', 0);

            $forum_data = get_forum_data($forum_id, $cat_forums);
            $old_cat_id = $forum_data['cat_id'];
            $forum_order = $forum_data['forum_order'];

            if (!$forum_name) {
                bb_die('Can not modify a forum without a name');
            }

            if ($forum_parent) {
                if (!$parent = get_forum_data($forum_parent, $cat_forums)) {
                    bb_die('Parent forum with id <b>' . $forum_parent . '</b> not found');
                }

                $cat_id = $parent['cat_id'];
                $forum_parent = $parent['forum_parent'] ?: $parent['forum_id'];
                $forum_order = $parent['forum_order'] + 5;

                if ($forum_id == $forum_parent) {
                    bb_die('Ambiguous forum ID. Please select other parent forum');
                }
            } elseif ($cat_id != $old_cat_id) {
                $max_order = get_max_forum_order($cat_id);
                $forum_order = $max_order + 5;
            } elseif ($forum_data['forum_parent']) {
                $old_parent = $forum_data['forum_parent'];
                $forum_order = $cat_forums[$old_cat_id]['f'][$old_parent]['forum_order'] - 5;
            }

            $forum_name_sql = DB()->escape($forum_name);
            $forum_desc_sql = DB()->escape($forum_desc);

            DB()->query('
				UPDATE ' . BB_FORUMS . " SET
					forum_name          = '{$forum_name_sql}',
					cat_id              = {$cat_id},
					forum_desc          = '{$forum_desc_sql}',
					forum_order         = {$forum_order},
					forum_status        = {$forum_status},
					prune_days          = {$prune_days},
					forum_parent        = {$forum_parent},
					show_on_index       = {$show_on_index},
					forum_tpl_id        = {$forum_tpl_id},
					allow_reg_tracker   = {$allow_reg_tracker},
					allow_porno_topic   = {$allow_porno_topic},
					self_moderated      = {$self_moderated},
					forum_display_order = {$forum_display_order},
					forum_display_sort  = {$forum_display_sort}
				WHERE forum_id          = {$forum_id}
			");

            if ($cat_id != $old_cat_id) {
                change_sf_cat($forum_id, $cat_id, $forum_order);
                renumber_order('forum', $cat_id);
            }

            renumber_order('forum', $old_cat_id);

            $cat_forums = get_cat_forums();
            $fix = fix_orphan_sf($cat_forums);
            forum_tree(refresh: true);
            CACHE('bb_cache')->rm();

            $message = __('FORUMS_UPDATED') . '<br /><br />';
            $message .= $fix ? "{$fix}<br /><br />" : '';
            $message .= sprintf(__('CLICK_RETURN_FORUMADMIN'), '<a href="admin_forums.php?' . POST_CAT_URL . '=' . $cat_id . '">', '</a>') . '<br /><br />' . sprintf(__('CLICK_RETURN_ADMIN_INDEX'), '<a href="index.php?pane=right">', '</a>');
            bb_die($message);

            break;

        case 'addcat':
            //
            // Create a category in the DB
            //
            if (!$new_cat_title = trim(request()->post->get('categoryname', ''))) {
                bb_die(__('CATEGORY_NAME_EMPTY'));
            }

            check_name_dup('cat', $new_cat_title);

            $order = DB()->fetch_row('SELECT MAX(cat_order) AS max_order FROM ' . BB_CATEGORIES);

            $args = DB()->build_array('INSERT', [
                'cat_title' => (string)$new_cat_title,
                'cat_order' => (int)$order['max_order'] + 10,
            ]);

            DB()->query('INSERT INTO ' . BB_CATEGORIES . $args);

            forum_tree(refresh: true);
            CACHE('bb_cache')->rm();

            bb_die(__('FORUMS_UPDATED') . '<br /><br />' . sprintf(__('CLICK_RETURN_FORUMADMIN'), '<a href="admin_forums.php">', '</a>') . '<br /><br />' . sprintf(__('CLICK_RETURN_ADMIN_INDEX'), '<a href="index.php?pane=right">', '</a>'));

            break;

        case 'editcat':
            //
            // Show form to edit a category
            //
            $cat_id = (int)request()->query->get(POST_CAT_URL, 0);
            $cat_info = get_info('category', $cat_id);

            $hidden_fields = [
                'mode' => 'modcat',
                POST_CAT_URL => $cat_id,
            ];

            template()->assign_vars([
                'TPL_EDIT_CATEGORY' => true,
                'CAT_TITLE' => htmlCHR($cat_info['cat_title']),
                'S_HIDDEN_FIELDS' => build_hidden_fields($hidden_fields),
                'S_SUBMIT_VALUE' => __('UPDATE'),
                'S_FORUM_ACTION' => 'admin_forums.php',
            ]);

            break;

        case 'modcat':
            //
            // Modify a category in the DB
            //
            if (!$new_cat_title = trim(request()->post->get('cat_title', ''))) {
                bb_die(__('CATEGORY_NAME_EMPTY'));
            }

            $cat_id = (int)request()->post->get(POST_CAT_URL, 0);

            $row = get_info('category', $cat_id);
            $cur_cat_title = $row['cat_title'];

            if ($cur_cat_title && $cur_cat_title !== $new_cat_title) {
                check_name_dup('cat', $new_cat_title);

                $new_cat_title_sql = DB()->escape($new_cat_title);

                DB()->query('
					UPDATE ' . BB_CATEGORIES . " SET
						cat_title = '{$new_cat_title_sql}'
					WHERE cat_id = {$cat_id}
				");
            }

            forum_tree(refresh: true);
            CACHE('bb_cache')->rm();

            bb_die(__('FORUMS_UPDATED') . '<br /><br />' . sprintf(__('CLICK_RETURN_FORUMADMIN'), '<a href="admin_forums.php">', '</a>') . '<br /><br />' . sprintf(__('CLICK_RETURN_ADMIN_INDEX'), '<a href="index.php?pane=right">', '</a>'));

            break;

        case 'deleteforum':
            //
            // Show form to delete a forum
            //
            $forum_id = (int)request()->query->get(POST_FORUM_URL, 0);

            $move_to_options = '<option value="-1">' . __('DELETE_ALL_POSTS') . '</option>';
            $move_to_options .= sf_get_list('forum', $cat_forums, $forum_parent, $forum_id, 0);

            $foruminfo = get_info('forum', $forum_id);

            $hidden_fields = [
                'mode' => 'movedelforum',
                'from_id' => $forum_id,
            ];

            template()->assign_vars([
                'TPL_DELETE_FORUM' => true,

                'WHAT_TO_DELETE' => htmlCHR($foruminfo['forum_name']),
                'DELETE_TITLE' => __('FORUM_DELETE'),
                'CAT_FORUM_NAME' => __('FORUM_NAME'),

                'S_HIDDEN_FIELDS' => build_hidden_fields($hidden_fields),
                'S_FORUM_ACTION' => 'admin_forums.php',
                'MOVE_TO_OPTIONS' => $move_to_options,
                'S_SUBMIT_VALUE' => __('MOVE_AND_DELETE'),
            ]);

            break;

        case 'movedelforum':
            //
            // Move or delete a forum in the DB
            //
            $from_id = (int)request()->post->get('from_id', 0);
            $to_id = (int)request()->post->get('to_id', 0);

            if ($to_id == -1) {
                // Delete everything from forum
                TorrentPier\Legacy\Admin\Common::topic_delete('prune', $from_id, 0, true);
                datastore()->update('stats');
            } else {
                // Move all posts
                $sql = 'SELECT * FROM ' . BB_FORUMS . " WHERE forum_id IN({$from_id}, {$to_id})";
                $result = DB()->query($sql);

                if (DB()->num_rows($result) != 2) {
                    bb_die('Ambiguous forum ID');
                }

                DB()->query('UPDATE ' . BB_TOPICS . " SET forum_id = {$to_id} WHERE forum_id = {$from_id}");
                DB()->query('UPDATE ' . BB_BT_TORRENTS . " SET forum_id = {$to_id} WHERE forum_id = {$from_id}");

                $row = DB()->fetch_row('SELECT MIN(post_id) AS start_id, MAX(post_id) AS finish_id FROM ' . BB_POSTS);
                $start_id = (int)$row['start_id'];
                $finish_id = (int)$row['finish_id'];
                $per_cycle = 10000;
                while (true) {
                    set_time_limit(600);
                    $end_id = $start_id + $per_cycle - 1;
                    DB()->query('
						UPDATE ' . BB_POSTS . " SET forum_id = {$to_id} WHERE post_id BETWEEN {$start_id} AND {$end_id} AND forum_id = {$from_id}
					");
                    if ($end_id > $finish_id) {
                        break;
                    }
                    $start_id += $per_cycle;
                }

                TorrentPier\Legacy\Admin\Common::sync('forum', $to_id);
            }

            DB()->query('DELETE FROM ' . BB_FORUMS . " WHERE forum_id = {$from_id}");
            DB()->query('DELETE FROM ' . BB_AUTH_ACCESS . " WHERE forum_id = {$from_id}");
            DB()->query('DELETE FROM ' . BB_AUTH_ACCESS_SNAP . " WHERE forum_id = {$from_id}");

            $cat_forums = get_cat_forums();
            fix_orphan_sf($cat_forums);
            TorrentPier\Legacy\Group::update_user_level('all');
            forum_tree(refresh: true);
            CACHE('bb_cache')->rm();

            bb_die(__('FORUMS_UPDATED') . '<br /><br />' . sprintf(__('CLICK_RETURN_FORUMADMIN'), '<a href="admin_forums.php">', '</a>') . '<br /><br />' . sprintf(__('CLICK_RETURN_ADMIN_INDEX'), '<a href="index.php?pane=right">', '</a>'));

            break;

        case 'deletecat':
            // Show form to delete a category
            $cat_id = (int)request()->query->get(POST_CAT_URL, 0);
            $catinfo = get_info('category', $cat_id);
            $categories_count = $catinfo['number'];

            if ($categories_count == 1) {
                $row = DB()->fetch_row('SELECT COUNT(*) AS forums_count FROM ' . BB_FORUMS);

                if ($row['forums_count'] > 0) {
                    bb_die(__('MUST_DELETE_FORUMS'));
                } else {
                    template()->assign_var('NOWHERE_TO_MOVE', __('NOWHERE_TO_MOVE'));
                }
            }

            $hidden_fields = [
                'mode' => 'movedelcat',
                'from_id' => $cat_id,
            ];

            template()->assign_vars([
                'TPL_DELETE_FORUM' => true,

                'WHAT_TO_DELETE' => htmlCHR($catinfo['cat_title']),
                'DELETE_TITLE' => __('CATEGORY_DELETE'),
                'CAT_FORUM_NAME' => __('CATEGORY'),

                'S_HIDDEN_FIELDS' => build_hidden_fields($hidden_fields),
                'S_FORUM_ACTION' => 'admin_forums.php',
                'MOVE_TO_OPTIONS' => get_list('category', $cat_id, 0),
                'S_SUBMIT_VALUE' => __('MOVE_AND_DELETE'),
            ]);

            break;

        case 'movedelcat':
            // Move or delete a category in the DB
            $from_id = (int)request()->post->get('from_id', 0);
            $to_id = (int)request()->post->get('to_id', -1);

            if ($to_id === -1) {
                bb_die(__('NOWHERE_TO_MOVE'));
            }

            if ($from_id == $to_id || !cat_exists($from_id) || !cat_exists($to_id)) {
                bb_die('Bad input');
            }

            $order_shear = get_max_forum_order($to_id) + 10;

            DB()->query('
				UPDATE ' . BB_FORUMS . " SET
					cat_id = {$to_id},
					forum_order = forum_order + {$order_shear}
				WHERE cat_id = {$from_id}
			");

            DB()->query('DELETE FROM ' . BB_CATEGORIES . " WHERE cat_id = {$from_id}");

            renumber_order('forum', $to_id);
            $cat_forums = get_cat_forums();
            $fix = fix_orphan_sf($cat_forums);
            forum_tree(refresh: true);
            CACHE('bb_cache')->rm();

            $message = __('FORUMS_UPDATED') . '<br /><br />';
            $message .= $fix ? "{$fix}<br /><br />" : '';
            $message .= sprintf(__('CLICK_RETURN_FORUMADMIN'), '<a href="admin_forums.php">', '</a>') . '<br /><br />' . sprintf(__('CLICK_RETURN_ADMIN_INDEX'), '<a href="index.php?pane=right">', '</a>');
            bb_die($message);

            break;

        case 'forum_order':
            // Change order of forums
            $move = (int)request()->query->get('move', 0);
            $forum_id = (int)request()->query->get(POST_FORUM_URL, 0);

            $forum_info = get_info('forum', $forum_id);
            renumber_order('forum', $forum_info['cat_id']);

            $cat_id = $forum_info['cat_id'];

            $move_down_forum_id = false;
            $forums = $cat_forums[$cat_id]['f_ord'];
            $forum_order = $forum_info['forum_order'];
            $prev_forum = $forums[$forum_order - 10] ?? false;
            $next_forum = $forums[$forum_order + 10] ?? false;

            // move selected forum ($forum_id) UP
            if ($move < 0 && $prev_forum) {
                if ($forum_info['forum_parent'] && $prev_forum['forum_parent'] != $forum_info['forum_parent']) {
                    $show_main_page = true;
                    break;
                }

                if ($move_down_forum_id = get_prev_root_forum_id($forums, $forum_order)) {
                    $move_up_forum_id = $forum_id;
                    $move_down_ord_val = (get_sf_count($forum_id, $cat_forums) + 1) * 10;
                    $move_up_ord_val = ((get_sf_count($move_down_forum_id, $cat_forums) + 1) * 10) + $move_down_ord_val;
                    $move_down_forum_order = $cat_forums[$cat_id]['f'][$move_down_forum_id]['forum_order'];
                }
            } // move selected forum ($forum_id) DOWN
            elseif ($move > 0 && $next_forum) {
                if ($forum_info['forum_parent'] && $next_forum['forum_parent'] != $forum_info['forum_parent']) {
                    $show_main_page = true;
                    break;
                }

                if ($move_up_forum_id = get_next_root_forum_id($forums, $forum_order)) {
                    $move_down_forum_id = $forum_id;
                    $move_down_forum_order = $forum_order;
                    $move_down_ord_val = (get_sf_count($move_up_forum_id, $cat_forums) + 1) * 10;
                    $move_up_ord_val = ((get_sf_count($move_down_forum_id, $cat_forums) + 1) * 10) + $move_down_ord_val;
                }
            } else {
                $show_main_page = true;
                break;
            }

            if ($forum_info['forum_parent']) {
                DB()->query('
					UPDATE ' . BB_FORUMS . " SET
						forum_order = forum_order + {$move}
					WHERE forum_id = {$forum_id}
				");
            } elseif ($move_down_forum_id) {
                DB()->query('
					UPDATE ' . BB_FORUMS . " SET
						forum_order = forum_order + {$move_down_ord_val}
					WHERE cat_id = {$cat_id}
						AND forum_order >= {$move_down_forum_order}
				");
                DB()->query('
					UPDATE ' . BB_FORUMS . " SET
						forum_order = forum_order - {$move_up_ord_val}
					WHERE forum_id = {$move_up_forum_id}
						 OR forum_parent = {$move_up_forum_id}
				");
            }

            renumber_order('forum', $forum_info['cat_id']);
            forum_tree(refresh: true);
            CACHE('bb_cache')->rm();

            $show_main_page = true;
            break;

        case 'cat_order':
            $move = (int)request()->query->get('move', 0);
            $cat_id = (int)request()->query->get(POST_CAT_URL, 0);

            DB()->query('
				UPDATE ' . BB_CATEGORIES . " SET
					cat_order = cat_order + {$move}
				WHERE cat_id = {$cat_id}
			");

            renumber_order('category');
            forum_tree(refresh: true);
            CACHE('bb_cache')->rm();

            $show_main_page = true;
            break;

        case 'forum_sync':
            TorrentPier\Legacy\Admin\Common::sync('forum', (int)request()->query->get(POST_FORUM_URL, 0));
            forum_tree(refresh: true);
            CACHE('bb_cache')->rm();

            $show_main_page = true;
            break;

        default:
            bb_die(__('NO_MODE'));
            break;
    }
}

if (!$mode || $show_main_page) {
    template()->assign_vars([
        'TPL_FORUMS_LIST' => true,

        'S_FORUM_ACTION' => 'admin_forums.php',
        'L_FORUM_TITLE' => __('FORUM_ADMIN_MAIN'),
    ]);

    $sql = 'SELECT cat_id, cat_title, cat_order FROM ' . BB_CATEGORIES . ' ORDER BY cat_order';
    if (!$q_categories = DB()->sql_query($sql)) {
        bb_die('Could not query categories list');
    }

    if ($total_categories = DB()->num_rows($q_categories)) {
        $category_rows = DB()->sql_fetchrowset($q_categories);

        $where_cat_sql = $req_cat_id = '';

        $c = request()->get(POST_CAT_URL);
        if ($c !== null) {
            if ($c !== 'all') {
                $req_cat_id = (int)$c;
                $where_cat_sql = "WHERE cat_id = {$req_cat_id}";
            } else {
                $req_cat_id = 'all';
            }
        } else {
            $where_cat_sql = "WHERE cat_id = '-1'";
        }

        $sql = 'SELECT * FROM ' . BB_FORUMS . " {$where_cat_sql} ORDER BY cat_id, forum_order";
        if (!$q_forums = DB()->sql_query($sql)) {
            bb_die('Could not query forums information');
        }

        if ($total_forums = DB()->num_rows($q_forums)) {
            $forum_rows = DB()->sql_fetchrowset($q_forums);
        }

        // Okay, let's build the index
        $gen_cat = [];

        $bgr_class_1 = 'prow1';
        $bgr_class_2 = 'prow2';
        $bgr_class_over = 'prow3';

        template()->assign_vars([
            'U_ALL_FORUMS' => 'admin_forums.php?' . POST_CAT_URL . '=all',
            'FORUMS_COUNT' => $total_forums,
        ]);

        for ($i = 0; $i < $total_categories; $i++) {
            $cat_id = $category_rows[$i]['cat_id'];

            template()->assign_block_vars('c', [
                'S_ADD_FORUM_SUBMIT' => "addforum[{$cat_id}]",
                'S_ADD_FORUM_NAME' => "forumname[{$cat_id}]",

                'CAT_ID' => $cat_id,
                'CAT_DESC' => htmlCHR($category_rows[$i]['cat_title']),

                'U_CAT_EDIT' => 'admin_forums.php?mode=editcat&amp;' . POST_CAT_URL . "={$cat_id}",
                'U_CAT_DELETE' => 'admin_forums.php?mode=deletecat&amp;' . POST_CAT_URL . "={$cat_id}",
                'U_CAT_MOVE_UP' => 'admin_forums.php?mode=cat_order&amp;move=-15&amp;' . POST_CAT_URL . "={$cat_id}",
                'U_CAT_MOVE_DOWN' => 'admin_forums.php?mode=cat_order&amp;move=15&amp;' . POST_CAT_URL . "={$cat_id}",
                'U_VIEWCAT' => 'admin_forums.php?' . POST_CAT_URL . "={$cat_id}",
                'U_CREATE_FORUM' => 'admin_forums.php?mode=addforum&amp;' . POST_CAT_URL . "={$cat_id}",
            ]);

            for ($j = 0; $j < $total_forums; $j++) {
                $forum_id = $forum_rows[$j]['forum_id'];

                $bgr_class = (!($j % 2)) ? $bgr_class_2 : $bgr_class_1;
                $row_bgr = " class=\"{$bgr_class}\" onmouseover=\"this.className='{$bgr_class_over}';\" onmouseout=\"this.className='{$bgr_class}';\"";

                if ($forum_rows[$j]['cat_id'] == $cat_id) {
                    template()->assign_block_vars('c.f', [
                        'FORUM_NAME' => htmlCHR($forum_rows[$j]['forum_name']),
                        'FORUM_DESC' => htmlCHR($forum_rows[$j]['forum_desc']),
                        'NUM_TOPICS' => $forum_rows[$j]['forum_topics'],
                        'NUM_POSTS' => $forum_rows[$j]['forum_posts'],
                        'PRUNE_DAYS' => !empty($forum_rows[$j]['prune_days']) ? humanTime((TIMENOW - 86400 * $forum_rows[$j]['prune_days']), TIMENOW) : __('DISABLED'),

                        'ORDER' => $forum_rows[$j]['forum_order'],
                        'FORUM_ID' => $forum_rows[$j]['forum_id'],
                        'ROW_BGR' => $row_bgr,

                        'SHOW_ON_INDEX' => (bool)$forum_rows[$j]['show_on_index'],
                        'FORUM_PARENT' => $forum_rows[$j]['forum_parent'],
                        'SF_PAD' => $forum_rows[$j]['forum_parent'] ? ' style="padding-left: 20px;" ' : '',
                        'FORUM_NAME_CLASS' => $forum_rows[$j]['forum_parent'] ? 'genmed' : 'gen',
                        'ADD_SUB_HREF' => !$forum_rows[$j]['forum_parent'] ? "admin_forums.php?mode=addforum&amp;forum_parent={$forum_rows[$j]['forum_id']}" : '',
                        'U_VIEWFORUM' => BB_ROOT . FORUM_URL . $forum_id,
                        'U_FORUM_EDIT' => 'admin_forums.php?mode=editforum&amp;' . POST_FORUM_URL . "={$forum_id}",
                        'U_FORUM_PERM' => 'admin_forumauth.php?' . POST_FORUM_URL . "={$forum_id}",
                        'U_FORUM_DELETE' => 'admin_forums.php?mode=deleteforum&amp;' . POST_FORUM_URL . "={$forum_id}",
                        'U_FORUM_MOVE_UP' => 'admin_forums.php?mode=forum_order&amp;move=-15&amp;' . POST_FORUM_URL . "={$forum_id}&amp;" . POST_CAT_URL . "={$req_cat_id}",
                        'U_FORUM_MOVE_DOWN' => 'admin_forums.php?mode=forum_order&amp;move=15&amp;' . POST_FORUM_URL . "={$forum_id}&amp;" . POST_CAT_URL . "={$req_cat_id}",
                        'U_FORUM_RESYNC' => 'admin_forums.php?mode=forum_sync&amp;' . POST_FORUM_URL . "={$forum_id}",
                    ]);
                }
            }
        }
    }
}

print_page('admin_forums.tpl', 'admin');

/**
 * @return mixed
 */
function get_info($mode, $id)
{
    $table = null;
    $idfield = null;
    switch ($mode) {
        case 'category':
            $table = BB_CATEGORIES;
            $idfield = 'cat_id';
            break;

        case 'forum':
            $table = BB_FORUMS;
            $idfield = 'forum_id';
            break;

        default:
            bb_die('Wrong mode for generating select list #1');
            break;
    }
    $sql = "SELECT count(*) as total FROM {$table}";
    if (!$result = DB()->sql_query($sql)) {
        bb_die('Could not get forum / category information #1');
    }
    $count = DB()->sql_fetchrow($result);
    $count = $count['total'];

    $sql = "SELECT * FROM {$table} WHERE {$idfield} = {$id}";

    if (!$result = DB()->sql_query($sql)) {
        bb_die('Could not get forum / category information #2');
    }

    if (DB()->num_rows($result) != 1) {
        bb_die('Forum / category does not exist or multiple forums / categories with ID ' . $id);
    }

    $return = DB()->sql_fetchrow($result);
    $return['number'] = $count;

    return $return;
}

/**
 * @return string
 */
function get_list($mode, $id, $select)
{
    $table = null;
    $idfield = null;
    $order = null;
    $namefield = null;
    switch ($mode) {
        case 'category':
            $table = BB_CATEGORIES;
            $idfield = 'cat_id';
            $namefield = 'cat_title';
            $order = 'cat_order';
            break;

        case 'forum':
            $table = BB_FORUMS;
            $idfield = 'forum_id';
            $namefield = 'forum_name';
            $order = 'cat_id, forum_order';
            break;

        default:
            bb_die('Wrong mode for generating select list #2');
            break;
    }

    $sql = "SELECT * FROM {$table}";
    if ($select == 0) {
        $sql .= " WHERE {$idfield} <> {$id}";
    }
    $sql .= " ORDER BY {$order}";

    if (!$result = DB()->sql_query($sql)) {
        bb_die('Could not get list of categories / forums #1');
    }

    $catlist = '';

    while ($row = DB()->sql_fetchrow($result)) {
        $s = '';
        if ($row[$idfield] == $id) {
            $s = ' selected';
        }
        $catlist .= '<option value="' . $row[$idfield] . '"' . $s . '>&nbsp;' . str_short(htmlCHR($row[$namefield]), 60) . '</option>\n';
    }

    return $catlist;
}

/**
 * @param int $cat
 */
function renumber_order($mode, $cat = 0)
{
    $table = null;
    $catfield = null;
    $orderfield = null;
    $idfield = null;
    switch ($mode) {
        case 'category':
            $table = BB_CATEGORIES;
            $idfield = 'cat_id';
            $orderfield = 'cat_order';
            $cat = 0;
            break;

        case 'forum':
            $table = BB_FORUMS;
            $idfield = 'forum_id';
            $orderfield = 'forum_order';
            $catfield = 'cat_id';
            break;

        default:
            bb_die('Wrong mode for generating select list #3');
            break;
    }

    $sql = "SELECT * FROM {$table}";
    if ($cat != 0) {
        $sql .= " WHERE {$catfield} = {$cat}";
    }
    $sql .= " ORDER BY {$orderfield} ASC";

    if (!$result = DB()->sql_query($sql)) {
        bb_die('Could not get list of categories / forums #2');
    }

    $i = 10;

    while ($row = DB()->sql_fetchrow($result)) {
        $sql = "UPDATE {$table} SET {$orderfield} = {$i} WHERE {$idfield} = " . $row[$idfield];
        if (!DB()->sql_query($sql)) {
            bb_die('Could not update order fields');
        }
        $i += 10;
    }

    if (!$result = DB()->sql_query($sql)) {
        bb_die('Could not get list of categories / forums #3');
    }
}

/**
 * @param bool $cat_id
 * @return array
 */
function get_cat_forums($cat_id = false)
{
    $forums = [];
    $where_sql = '';

    if ($cat_id = (int)$cat_id) {
        $where_sql = "AND f.cat_id = {$cat_id}";
    }

    $sql = 'SELECT c.cat_title, f.*
		FROM ' . BB_FORUMS . ' f, ' . BB_CATEGORIES . " c
		WHERE f.cat_id = c.cat_id
			{$where_sql}
		ORDER BY c.cat_order, f.cat_id, f.forum_order";

    if (!$result = DB()->sql_query($sql)) {
        bb_die('Could not get list of categories / forums #4');
    }

    if ($rowset = DB()->sql_fetchrowset($result)) {
        foreach ($rowset as $rid => $row) {
            $forums[$row['cat_id']]['cat_title'] = $row['cat_title'];
            $forums[$row['cat_id']]['f'][$row['forum_id']] = $row;
            $forums[$row['cat_id']]['f_ord'][$row['forum_order']] = $row;
        }
    }

    return $forums;
}

function get_sf_count(int $forum_id, array $cat_forums): int
{
    $sf_count = 0;

    foreach ($cat_forums as $cid => $c) {
        foreach ($c['f'] as $fid => $f) {
            if ($f['forum_parent'] == $forum_id) {
                $sf_count++;
            }
        }
    }

    return $sf_count;
}

/**
 * @return bool
 */
function get_prev_root_forum_id($forums, $curr_forum_order)
{
    $i = $curr_forum_order - 10;

    while ($i > 0) {
        if (isset($forums[$i]) && !$forums[$i]['forum_parent']) {
            return $forums[$i]['forum_id'];
        }
        $i -= 10;
    }

    return false;
}

/**
 * @return bool
 */
function get_next_root_forum_id($forums, $curr_forum_order)
{
    $i = $curr_forum_order + 10;
    $limit = (count($forums) * 10) + 10;

    while ($i < $limit) {
        if (isset($forums[$i]) && !$forums[$i]['forum_parent']) {
            return $forums[$i]['forum_id'];
        }
        $i += 10;
    }

    return false;
}

function get_orphan_sf(array $cat_forums): string
{
    $last_root = 0;
    $bad_sf_ary = [];

    foreach ($cat_forums as $cid => $c) {
        foreach ($c['f'] as $fid => $f) {
            if ($f['forum_parent']) {
                if ($f['forum_parent'] != $last_root) {
                    $bad_sf_ary[] = $f['forum_id'];
                }
            } else {
                $last_root = $f['forum_id'];
            }
        }
    }

    return implode(',', $bad_sf_ary);
}

function fix_orphan_sf(array $cat_forums, string $orphan_sf_sql = '', bool $show_mess = false): string
{
    $done_mess = '';

    if (!$orphan_sf_sql) {
        $orphan_sf_sql = get_orphan_sf($cat_forums);
    }

    if ($orphan_sf_sql) {
        $sql = 'UPDATE ' . BB_FORUMS . " SET forum_parent = 0, show_on_index = 1 WHERE forum_id IN({$orphan_sf_sql})";

        if (!DB()->sql_query($sql)) {
            bb_die('Could not change subforums data');
        }

        if ($affectedrows = DB()->affected_rows()) {
            $done_mess = 'Subforums data corrected. <b>' . $affectedrows . '</b> orphan subforum(s) moved to root level.';
        }

        if ($show_mess) {
            $message = $done_mess . '<br /><br />';
            $message .= sprintf(__('CLICK_RETURN_FORUMADMIN'), '<a href="admin_forums.php">', '</a>') . '<br /><br />';
            $message .= sprintf(__('CLICK_RETURN_ADMIN_INDEX'), '<a href="index.php?pane=right">', '</a>');
            bb_die($message);
        }
    }

    return $done_mess;
}

function sf_get_list(string $mode, array $cat_forums, int $forum_parent, int $exclude = 0, int $select = 0): string
{
    $opt = '';

    if ($mode == 'forum') {
        foreach ($cat_forums as $cid => $c) {
            $opt .= '<optgroup label="&nbsp;' . htmlCHR($c['cat_title']) . '">';

            foreach ($c['f'] as $fid => $f) {
                $selected = ($fid == $select) ? HTML_SELECTED : '';
                $disabled = ($fid == $exclude && !$forum_parent) ? HTML_DISABLED : '';
                $style = $disabled ? ' style="color: gray" ' : (($fid == $exclude) ? ' style="color: darkred" ' : '');
                $opt .= '<option value="' . $fid . '" ' . $selected . $disabled . $style . '>' . ($f['forum_parent'] ? HTML_SF_SPACER : '') . str_short(htmlCHR($f['forum_name']), 60) . "&nbsp;</option>\n";
            }

            $opt .= '</optgroup>';
        }
    }

    return $opt;
}

function get_forum_data(int $forum_id, array $cat_forums): array|false
{
    foreach ($cat_forums as $cid => $c) {
        foreach ($c['f'] as $fid => $f) {
            if ($fid == $forum_id) {
                return $f;
            }
        }
    }

    return false;
}

/**
 * @return int
 */
function get_max_forum_order($cat_id)
{
    $row = DB()->fetch_row('
		SELECT MAX(forum_order) AS max_forum_order
		FROM ' . BB_FORUMS . "
		WHERE cat_id = {$cat_id}
	");

    return (int)$row['max_forum_order'];
}

/**
 * @param bool $die_on_error
 * @return mixed
 */
function check_name_dup($mode, $name, $die_on_error = true)
{
    $name_sql = DB()->escape($name);

    if ($mode == 'cat') {
        $what_checked = 'category';
        $sql = 'SELECT cat_id FROM ' . BB_CATEGORIES . " WHERE cat_title = '{$name_sql}'";
    } else {
        $what_checked = 'forum';
        $sql = 'SELECT forum_id FROM ' . BB_FORUMS . " WHERE forum_name = '{$name_sql}'";
    }

    $name_is_dup = DB()->fetch_row($sql);

    if ($name_is_dup && $die_on_error) {
        bb_die('This ' . $what_checked . ' name taken, please choose something else');
    }

    return $name_is_dup;
}

/**
 *  Change subforums cat_id if parent's cat_id was changed
 */
function change_sf_cat($parent_id, $new_cat_id, $order_shear)
{
    DB()->query('
		UPDATE ' . BB_FORUMS . " SET
			cat_id      = {$new_cat_id},
			forum_order = forum_order + {$order_shear}
		WHERE forum_parent = {$parent_id}
	");
}
