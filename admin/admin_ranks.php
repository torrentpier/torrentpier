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
    $module['USERS']['RANKS'] = basename(__FILE__);
    return;
}
require('./pagestart.php');

$_POST['special_rank'] = 1;
$_POST['min_posts'] = -1;

if (isset($_GET['mode']) || isset($_POST['mode'])) {
    $mode = isset($_GET['mode']) ? $_GET['mode'] : $_POST['mode'];
} else {
    //
    // These could be entered via a form button
    //
    if (isset($_POST['add'])) {
        $mode = 'add';
    } elseif (isset($_POST['save'])) {
        $mode = 'save';
    } else {
        $mode = '';
    }
}

if ($mode != '') {
    if ($mode == 'edit' || $mode == 'add') {
        //
        // They want to add a new rank, show the form.
        //
        $rank_id = (isset($_GET['id'])) ? intval($_GET['id']) : 0;

        $s_hidden_fields = '';

        if ($mode == 'edit') {
            if (empty($rank_id)) {
                bb_die($lang['MUST_SELECT_RANK']);
            }

            $sql = "SELECT * FROM " . BB_RANKS . " WHERE rank_id = $rank_id";
            if (!$result = DB()->sql_query($sql)) {
                bb_die('Could not obtain ranks data #1');
            }

            $rank_info = DB()->sql_fetchrow($result);
            $s_hidden_fields .= '<input type="hidden" name="id" value="' . $rank_id . '" />';
        } else {
            $rank_info['rank_special'] = 0;
        }

        $s_hidden_fields .= '<input type="hidden" name="mode" value="save" />';

        $rank_is_special = !empty($rank_info['rank_special']) ? HTML_CHECKED : '';
        $rank_is_not_special = empty($rank_info['rank_special']) ? HTML_CHECKED : '';

        $template->assign_vars(array(
            'TPL_RANKS_EDIT' => true,

            'RANK' => !empty($rank_info['rank_title']) ? $rank_info['rank_title'] : '',
            'SPECIAL_RANK' => $rank_is_special,
            'NOT_SPECIAL_RANK' => $rank_is_not_special,
            'MINIMUM' => ($rank_is_special) ? '' : $rank_info['rank_min'],
            'IMAGE' => !empty($rank_info['rank_image']) ? $rank_info['rank_image'] : 'styles/images/ranks/rank_image.png',
            'STYLE' => !empty($rank_info['rank_style']) ? $rank_info['rank_style'] : '',
            'IMAGE_DISPLAY' => !empty($rank_info['rank_image']) ? '<img src="../' . $rank_info['rank_image'] . '" />' : '',

            'S_RANK_ACTION' => "admin_ranks.php",
            'S_HIDDEN_FIELDS' => $s_hidden_fields,
        ));
    } elseif ($mode == 'save') {
        //
        // Ok, they sent us our info, let's update it.
        //

        $rank_id = (isset($_POST['id'])) ? intval($_POST['id']) : 0;
        $rank_title = (isset($_POST['title'])) ? trim($_POST['title']) : '';
        $rank_style = (isset($_POST['style'])) ? trim($_POST['style']) : '';
        $special_rank = ($_POST['special_rank'] == 1) ? true : 0;
        $min_posts = (isset($_POST['min_posts'])) ? intval($_POST['min_posts']) : -1;
        $rank_image = ((isset($_POST['rank_image']))) ? trim($_POST['rank_image']) : '';

        if ($rank_title == '') {
            bb_die($lang['MUST_SELECT_RANK']);
        }

        if ($special_rank == 1) {
            $max_posts = -1;
            $min_posts = -1;
        }

        //
        // The rank image has to be a jpg, gif or png
        //
        if ($rank_image != '') {
            if (!preg_match('/(\.gif|\.png|\.jpg)$/is', $rank_image)) {
                $rank_image = '';
            }
        }

        if ($rank_id) {
            if (!$special_rank) {
                $sql = "UPDATE " . BB_USERS . " SET user_rank = 0 WHERE user_rank = $rank_id";
                if (!$result = DB()->sql_query($sql)) {
                    bb_die($lang['NO_UPDATE_RANKS']);
                }
            }
            $sql = "UPDATE " . BB_RANKS . "
				SET rank_title = '" . DB()->escape($rank_title) . "',
					rank_special = $special_rank,
					rank_min = $min_posts,
					rank_image = '" . DB()->escape($rank_image) . "',
					rank_style = '" . DB()->escape($rank_style) . "'
				WHERE rank_id = $rank_id";

            $message = $lang['RANK_UPDATED'];
        } else {
            $sql = "INSERT INTO " . BB_RANKS . " (rank_title, rank_special, rank_min, rank_image, rank_style)
				VALUES ('" . DB()->escape($rank_title) . "', $special_rank, $min_posts, '" . DB()->escape($rank_image) . "', '" . DB()->escape($rank_style) . "')";

            $message = $lang['RANK_ADDED'];
        }

        if (!$result = DB()->sql_query($sql)) {
            bb_die('Could not update / insert into ranks table');
        }

        $message .= '<br /><br />' . sprintf($lang['CLICK_RETURN_RANKADMIN'], '<a href="admin_ranks.php">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>');

        $datastore->update('ranks');

        bb_die($message);
    } elseif ($mode == 'delete') {
        //
        // Ok, they want to delete their rank
        //

        if (isset($_POST['id']) || isset($_GET['id'])) {
            $rank_id = (isset($_POST['id'])) ? intval($_POST['id']) : intval($_GET['id']);
        } else {
            $rank_id = 0;
        }

        if ($rank_id) {
            $sql = "DELETE FROM " . BB_RANKS . " WHERE rank_id = $rank_id";

            if (!$result = DB()->sql_query($sql)) {
                bb_die('Could not delete rank data');
            }

            $sql = "UPDATE " . BB_USERS . " SET user_rank = 0 WHERE user_rank = $rank_id";
            if (!$result = DB()->sql_query($sql)) {
                bb_die($lang['NO_UPDATE_RANKS']);
            }

            $datastore->update('ranks');

            bb_die($lang['RANK_REMOVED'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_RANKADMIN'], '<a href="admin_ranks.php">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
        } else {
            bb_die($lang['MUST_SELECT_RANK']);
        }
    } else {
        bb_die('Invalid mode');
    }
} else {
    //
    // Show the default page
    //
    $sql = "SELECT * FROM " . BB_RANKS . " ORDER BY rank_min, rank_title";
    if (!$result = DB()->sql_query($sql)) {
        bb_die('Could not obtain ranks data #2');
    }
    $rank_count = DB()->num_rows($result);
    $rank_rows = DB()->sql_fetchrowset($result);

    $template->assign_vars(array(
        'TPL_RANKS_LIST' => true,
        'S_RANKS_ACTION' => "admin_ranks.php",
    ));

    for ($i = 0; $i < $rank_count; $i++) {
        $rank = $rank_rows[$i]['rank_title'];
        $special_rank = $rank_rows[$i]['rank_special'];
        $rank_id = $rank_rows[$i]['rank_id'];
        $rank_min = $rank_rows[$i]['rank_min'];

        if ($special_rank == 1) {
            $rank_min = $rank_max = '-';
        }

        $row_class = !($i % 2) ? 'row1' : 'row2';

        $rank_is_special = ($special_rank) ? $lang['YES'] : $lang['NO'];

        $template->assign_block_vars('ranks', array(
            'ROW_CLASS' => $row_class,
            'RANK' => $rank,
            'STYLE' => $rank_rows[$i]['rank_style'],
            'IMAGE_DISPLAY' => ($rank_rows[$i]['rank_image']) ? '<img src="../' . $rank_rows[$i]['rank_image'] . '" />' : '',
            'SPECIAL_RANK' => $rank_is_special,
            'RANK_MIN' => $rank_min,

            'U_RANK_EDIT' => "admin_ranks.php?mode=edit&amp;id=$rank_id",
            'U_RANK_DELETE' => "admin_ranks.php?mode=delete&amp;id=$rank_id",
        ));
    }
}

print_page('admin_ranks.tpl', 'admin');
