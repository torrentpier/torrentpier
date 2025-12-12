<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    $module['GENERAL']['WORD_CENSOR'] = basename(__FILE__);
    return;
}

require __DIR__ . '/pagestart.php';

if (!config()->get('use_word_censor')) {
    bb_die('Word censor disabled <br /><br /> (use_word_censor in config.php)');
}

$mode = htmlspecialchars(request()->getString('mode'));

if (request()->has('add')) {
    $mode = 'add';
} elseif (request()->has('save')) {
    $mode = 'save';
}

if ($mode != '') {
    if ($mode == 'edit' || $mode == 'add') {
        $word_id = request()->getInt('id');

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
                bb_die(__('NO_WORD_SELECTED'));
            }
        }

        template()->assign_vars([
            'TPL_ADMIN_WORDS_EDIT' => true,
            'WORD' => $word,
            'REPLACEMENT' => $replacement,
            'S_WORDS_ACTION' => 'admin_words.php',
            'S_HIDDEN_FIELDS' => $s_hidden_fields,
        ]);
    } elseif ($mode == 'save') {
        $word_id = request()->getInt('id');
        $word = trim(request()->getString('word'));
        $replacement = trim(request()->getString('replacement'));

        if ($word == '' || $replacement == '') {
            bb_die(__('MUST_ENTER_WORD'));
        }

        if ($word_id) {
            $sql = 'UPDATE ' . BB_WORDS . "
				SET word = '" . DB()->escape($word) . "', replacement = '" . DB()->escape($replacement) . "'
				WHERE word_id = $word_id";
            $message = __('WORD_UPDATED');
        } else {
            $sql = 'INSERT INTO ' . BB_WORDS . " (word, replacement)
				VALUES ('" . DB()->escape($word) . "', '" . DB()->escape($replacement) . "')";
            $message = __('WORD_ADDED');
        }

        if (!$result = DB()->sql_query($sql)) {
            bb_die('Could not insert data into words table');
        }

        datastore()->update('censor');
        censor()->reload(); // Reload the singleton instance with updated words
        $message .= '<br /><br />' . sprintf(__('CLICK_RETURN_WORDADMIN'), '<a href="admin_words.php">', '</a>') . '<br /><br />' . sprintf(__('CLICK_RETURN_ADMIN_INDEX'), '<a href="index.php?pane=right">', '</a>');

        bb_die($message);
    } elseif ($mode == 'delete') {
        $word_id = request()->getInt('id');

        if ($word_id) {
            $sql = 'DELETE FROM ' . BB_WORDS . " WHERE word_id = $word_id";

            if (!$result = DB()->sql_query($sql)) {
                bb_die('Could not remove data from words table');
            }

            datastore()->update('censor');
            censor()->reload(); // Reload the singleton instance with updated words

            bb_die(__('WORD_REMOVED') . '<br /><br />' . sprintf(__('CLICK_RETURN_WORDADMIN'), '<a href="admin_words.php">', '</a>') . '<br /><br />' . sprintf(__('CLICK_RETURN_ADMIN_INDEX'), '<a href="index.php?pane=right">', '</a>'));
        } else {
            bb_die(__('NO_WORD_SELECTED'));
        }
    }
} else {
    $sql = 'SELECT * FROM ' . BB_WORDS . ' ORDER BY word';
    if (!$result = DB()->sql_query($sql)) {
        bb_die('Could not query words table #2');
    }

    $word_rows = DB()->sql_fetchrowset($result);
    $word_count = count($word_rows);

    template()->assign_vars([
        'TPL_ADMIN_WORDS_LIST' => true,
        'S_WORDS_ACTION' => 'admin_words.php',
        'S_HIDDEN_FIELDS' => ''
    ]);

    for ($i = 0; $i < $word_count; $i++) {
        $word = $word_rows[$i]['word'];
        $replacement = $word_rows[$i]['replacement'];
        $word_id = $word_rows[$i]['word_id'];

        $row_class = !($i % 2) ? 'row1' : 'row2';

        template()->assign_block_vars('words', [
            'ROW_CLASS' => $row_class,
            'WORD' => $word,
            'REPLACEMENT' => $replacement,
            'U_WORD_EDIT' => "admin_words.php?mode=edit&amp;id=$word_id",
            'U_WORD_DELETE' => "admin_words.php?mode=delete&amp;id=$word_id"
        ]);
    }
}

print_page('admin_words.tpl', 'admin');
