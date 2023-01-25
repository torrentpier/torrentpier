<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    $module['GENERAL']['WORD_CENSOR'] = basename(__FILE__);
    return;
}

require __DIR__ . '/pagestart.php';

if (!$bb_cfg['use_word_censor']) {
    bb_die('Word censor disabled <br /><br /> ($bb_cfg[\'use_word_censor\'] in config.php)');
}

$mode = request_var('mode', '');
$mode = htmlspecialchars($mode);

if (isset($_POST['add'])) {
    $mode = 'add';
} elseif (isset($_POST['save'])) {
    $mode = 'save';
}

if ($mode != '') {
    if ($mode == 'edit' || $mode == 'add') {
        $word_id = (int)request_var('id', 0);

        $s_hidden_fields = $word = $replacement = '';

        if ($mode == 'edit') {
            if ($word_id) {
                $sql = 'SELECT * FROM ' . BB_WORDS . " WHERE word_id = $word_id";
                if (!$result = DB()->sql_query($sql)) {
                    bb_die('Could not query words table #1');
                }

                $word_info = DB()->sql_fetchrow($result);
                $s_hidden_fields .= '<input type="hidden" name="id" value="' . $word_id . '" />';
                $word = $word_info['word'];
                $replacement = $word_info['replacement'];
            } else {
                bb_die($lang['NO_WORD_SELECTED']);
            }
        }

        $template->assign_vars(array(
            'TPL_ADMIN_WORDS_EDIT' => true,
            'WORD' => $word,
            'REPLACEMENT' => $replacement,
            'S_WORDS_ACTION' => 'admin_words.php',
            'S_HIDDEN_FIELDS' => $s_hidden_fields,
        ));
    } elseif ($mode == 'save') {
        $word_id = (int)request_var('id', 0);
        $word = trim(request_var('word', ''));
        $replacement = trim(request_var('replacement', ''));

        if ($word == '' || $replacement == '') {
            bb_die($lang['MUST_ENTER_WORD']);
        }

        if ($word_id) {
            $sql = 'UPDATE ' . BB_WORDS . "
				SET word = '" . DB()->escape($word) . "', replacement = '" . DB()->escape($replacement) . "'
				WHERE word_id = $word_id";
            $message = $lang['WORD_UPDATED'];
        } else {
            $sql = 'INSERT INTO ' . BB_WORDS . " (word, replacement)
				VALUES ('" . DB()->escape($word) . "', '" . DB()->escape($replacement) . "')";
            $message = $lang['WORD_ADDED'];
        }

        if (!$result = DB()->sql_query($sql)) {
            bb_die('Could not insert data into words table');
        }

        CACHE('bb_cache')->rm('censored');
        $message .= '<br /><br />' . sprintf($lang['CLICK_RETURN_WORDADMIN'], '<a href="admin_words.php">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>');

        bb_die($message);
    } elseif ($mode == 'delete') {
        $word_id = (int)request_var('id', 0);

        if ($word_id) {
            $sql = 'DELETE FROM ' . BB_WORDS . " WHERE word_id = $word_id";

            if (!$result = DB()->sql_query($sql)) {
                bb_die('Could not remove data from words table');
            }

            CACHE('bb_cache')->rm('censored');

            bb_die($lang['WORD_REMOVED'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_WORDADMIN'], '<a href="admin_words.php">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
        } else {
            bb_die($lang['NO_WORD_SELECTED']);
        }
    }
} else {
    $sql = 'SELECT * FROM ' . BB_WORDS . ' ORDER BY word';
    if (!$result = DB()->sql_query($sql)) {
        bb_die('Could not query words table #2');
    }

    $word_rows = DB()->sql_fetchrowset($result);
    $word_count = count($word_rows);

    $template->assign_vars(array(
        'TPL_ADMIN_WORDS_LIST' => true,
        'S_WORDS_ACTION' => 'admin_words.php',
        'S_HIDDEN_FIELDS' => '',
    ));

    for ($i = 0; $i < $word_count; $i++) {
        $word = $word_rows[$i]['word'];
        $replacement = $word_rows[$i]['replacement'];
        $word_id = $word_rows[$i]['word_id'];

        $row_class = !($i % 2) ? 'row1' : 'row2';

        $template->assign_block_vars('words', array(
            'ROW_CLASS' => $row_class,
            'WORD' => $word,
            'REPLACEMENT' => $replacement,
            'U_WORD_EDIT' => "admin_words.php?mode=edit&amp;id=$word_id",
            'U_WORD_DELETE' => "admin_words.php?mode=delete&amp;id=$word_id",
        ));
    }
}

print_page('admin_words.tpl', 'admin');
