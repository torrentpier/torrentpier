<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

use Illuminate\Support\Str;

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

// prepare a posted message for entry into the database
function prepare_message($message)
{
    $message = TorrentPier\Legacy\BBCode::clean_up($message);
    $message = htmlCHR($message, false, ENT_NOQUOTES);

    return $message;
}

// Fill smiley templates (or just the variables) with smileys
// Either in a window or inline
function generate_smilies($mode)
{
    $inline_columns = 4;
    $inline_rows = 7;
    $window_columns = 8;

    if ($mode == 'window' && !defined('SESSION_STARTED')) {
        user()->session_start();
        define('SESSION_STARTED', true);
    }

    $data = datastore()->get('smile_replacements');

    if (isset($data['smile']) && $sql = $data['smile']) {
        $num_smilies = 0;
        $rowset = [];
        foreach ($sql as $row) {
            if (empty($rowset[$row['smile_url']])) {
                $rowset[$row['smile_url']]['code'] = addslashes($row['code']);
                $rowset[$row['smile_url']]['emoticon'] = $row['emoticon'];
                $num_smilies++;
            }
        }

        if ($num_smilies) {
            $smilies_split_row = ($mode == 'inline') ? $inline_columns - 1 : $window_columns - 1;

            $s_colspan = 0;
            $row = 0;
            $col = 0;

            foreach ($rowset as $smile_url => $data) {
                if (!$col) {
                    template()->assign_block_vars('smilies_row', []);
                }

                template()->assign_block_vars('smilies_row.smilies_col', [
                    'SMILEY_CODE' => $data['code'],
                    'SMILEY_IMG' => FORUM_PATH . config()->get('smilies_path') . '/' . $smile_url,
                    'SMILEY_DESC' => $data['emoticon'],
                ]);

                $s_colspan = max($s_colspan, $col + 1);

                if ($col == $smilies_split_row) {
                    if ($mode == 'inline' && $row == $inline_rows - 1) {
                        break;
                    }
                    $col = 0;
                    $row++;
                } else {
                    $col++;
                }
            }

            if ($mode == 'inline' && $num_smilies > $inline_rows * $inline_columns) {
                template()->assign_block_vars('switch_smilies_extra', []);

                template()->assign_vars([
                    'U_MORE_SMILIES' => POSTING_URL . '?mode=smilies',
                ]);
            }

            template()->assign_vars([
                'PAGE_TITLE' => __('EMOTICONS'),
                'S_SMILIES_COLSPAN' => $s_colspan,
            ]);
        }
    }

    if ($mode == 'window') {
        print_page('posting_smilies.tpl', 'simple');
    }
}

// some functions from vB
// #############################################################################
/**
 * Strips away [quote] tags and their contents from the specified string
 *
 * @param string    Text to be stripped of quote tags
 *
 * @return string
 */
function strip_quotes($text)
{
    $lowertext = mb_strtolower($text, DEFAULT_CHARSET);

    // find all [quote tags
    $start_pos = [];
    $curpos = 0;
    do {
        $pos = strpos($lowertext, '[quote', $curpos);
        if ($pos !== false) {
            $start_pos[(string)$pos] = 'start';
            $curpos = $pos + 6;
        }
    } while ($pos !== false);

    if (count($start_pos) == 0) {
        return $text;
    }

    // find all [/quote] tags
    $end_pos = [];
    $curpos = 0;
    do {
        $pos = strpos($lowertext, '[/quote', $curpos);
        if ($pos !== false) {
            $end_pos[(string)$pos] = 'end';
            $curpos = $pos + 8;
        }
    } while ($pos !== false);

    if (count($end_pos) == 0) {
        return $text;
    }

    // merge them together and sort based on position in string
    $pos_list = $start_pos + $end_pos;
    ksort($pos_list);

    do {
        // build a stack that represents when a quote tag is opened
        // and add non-quote text to the new string
        $stack = [];
        $newtext = '[...] ';
        $substr_pos = 0;
        foreach ($pos_list as $pos => $type) {
            $stacksize = count($stack);
            if ($type == 'start') {
                // empty stack, so add from the last close tag or the beginning of the string
                if ($stacksize == 0) {
                    $newtext .= substr($text, $substr_pos, $pos - $substr_pos);
                }
                $stack[] = $pos;
            } else {
                // pop off the latest opened tag
                if ($stacksize) {
                    array_pop($stack);
                    $substr_pos = $pos + 8;
                }
            }
        }

        // add any trailing text
        $newtext .= substr($text, $substr_pos);

        // check to see if there's a stack remaining, remove those points
        // as key points, and repeat. Allows emulation of a non-greedy-type
        // recursion.
        if ($stack) {
            foreach ($stack as $pos) {
                unset($pos_list[(string)$pos]);
            }
        }
    } while ($stack);

    return $newtext;
}

// #############################################################################
/**
 * Strips away bbcode from a given string, leaving plain text
 *
 * @param string    Text to be stripped of bbcode tags
 * @param bool    If true, strip away quote tags AND their contents
 * @param bool    If true, use the fast-and-dirty method rather than the shiny and nice method
 *
 * @return string
 */
function strip_bbcode($message, $stripquotes = true, $fast_and_dirty = false, $showlinks = true)
{
    $find = [];
    $replace = [];

    if ($stripquotes) {
        // [quote=username] and [quote]
        $message = strip_quotes($message);
    }

    // a really quick and rather nasty way of removing bbcode
    if ($fast_and_dirty) {
        // any old thing in square brackets
        $find[] = '#\[.*/?\]#siU';
        $replace = [];

        $message = preg_replace($find, $replace, $message);
    } // the preferable way to remove bbcode
    else {
        // simple links
        $find[] = '#\[(email|url)=("??)(.+)\\2\]\\3\[/\\1\]#siU';
        $replace[] = '\3';

        // named links
        $find[] = '#\[(email|url)=("??)(.+)\\2\](.+)\[/\\1\]#siU';
        $replace[] = ($showlinks ? '\4 (\3)' : '\4');

        // smilies
        $find[] = '#(?<=^|\W)(:\w+?:)(?=$|\W)#';
        $replace[] = '';

        // replace
        $message = preg_replace($find, $replace, $message);

        // strip out all other instances of [x]...[/x]
        while (preg_match('#\[([a-z]+)\s*?(?:[^\]]*?)\](.*?)(\[/\1\])#is', $message, $m)) {
            $message = str_replace($m[0], $m[2], $message);
        }

        $replace = ['[*]', '[hr]', '[br]', '[align=center]', '[align=left]', '[align=right]'];
        $message = str_replace($replace, ' ', $message);
    }

    return $message;
}

function extract_search_words($text)
{
    $max_words_count = config()->get('max_search_words_per_post');
    $min_word_len = max(2, config()->get('search_min_word_len') - 1);
    $max_word_len = config()->get('search_max_word_len');

    $text = ' ' . Str::squish(strip_tags(mb_strtolower($text, DEFAULT_CHARSET))) . ' ';
    $text = str_replace(['&#91;', '&#93;'], ['[', ']'], $text);

    // HTML entities like &nbsp;
    $text = preg_replace('/(\w*?)&#?[0-9a-z]+;(\w*?)/iu', '', $text);
    // Remove URL's       ((www|ftp)\.[\w\#!$%&~/.\-;:=,?@а-яА-Я\[\]+]*?)
    $text = preg_replace('#\b[a-z0-9]+://[\w\#!$%&~/.\-;:=,?@а-яА-Я\[\]+]+(/[0-9a-z\?\.%_\-\+=&/]+)?#u', ' ', $text);
    $text = str_replace(['[url=', '?', '!'], ' ', $text);

    $text = strip_bbcode($text);

    // Filter out characters like ^, $, &, change "it's" to "its"
    $text = preg_replace('#[.,:;]#u', ' ', $text);

    // Trim 1+ spaces to one space and split this string into unique words
    $text = array_unique(explode(' ', Str::squish($text)));

    // short & long words 2
    $text_out = [];
    foreach ($text as $word) {
        if (mb_strlen($word) > $min_word_len && mb_strlen($word) <= $max_word_len) {
            $text_out[] = $word;
        }
    }
    $text = $text_out;

    if (count($text) > $max_words_count) {
        $text = array_splice($text, 0, $max_words_count);
    }

    return $text;
}

function add_search_words($post_id, $post_message, $topic_title = '', $only_return_words = false)
{
    $text = $topic_title . ' ' . $post_message;
    $words = ($text) ? extract_search_words($text) : [];
    $words_csv = implode("\n", $words);

    if ($only_return_words) {
        return $words_csv;
    }

    DB()->query('DELETE FROM ' . BB_POSTS_SEARCH . " WHERE post_id = {$post_id}");

    if ($words_sql = DB()->escape($words_csv)) {
        DB()->query('REPLACE INTO ' . BB_POSTS_SEARCH . " (post_id, search_words) VALUES ({$post_id}, '{$words_sql}')");
    }
}

/**
 * Dirty class removed from here since 2.2.0
 * To add new bbcodes see at src/Legacy/BBCode.php
 */

function bbcode2html($text)
{
    $text = censor()->censorString($text);

    return bbcode()->bbcode2html($text);
}

function get_words_rate($text)
{
    static $wr = null;
    if (!isset($wr)) {
        $wr = new TorrentPier\Legacy\WordsRate;
    }

    return $wr->get_words_rate($text);
}

function hide_passkey($str)
{
    return preg_replace("#\\?{config()->get('passkey_key')}=[a-zA-Z0-9]{" . BT_AUTH_KEY_LENGTH . '}#', "?{config()->get('passkey_key')}=passkey", $str);
}

function get_parsed_post($postrow, $mode = 'full', $return_chars = 600)
{
    if (config()->get('use_posts_cache') && !empty($postrow['post_html'])) {
        return $postrow['post_html'];
    }

    $message = bbcode2html($postrow['post_text']);

    // Posts cache
    if (config()->get('use_posts_cache')) {
        DB()->shutdown['post_html'][] = [
            'post_id' => (int)$postrow['post_id'],
            'post_html' => (string)$message,
        ];
    }

    return $message;
}

function update_post_html($postrow)
{
    DB()->query('DELETE FROM ' . BB_POSTS_HTML . ' WHERE post_id = ' . (int)$postrow['post_id']);
}
