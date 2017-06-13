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

$datastore->enqueue(array(
    'smile_replacements',
    'cat_forums',
));

$page_cfg['include_bbcode_js'] = true;

//
// BBCode templates
//
function get_bbcode_tpl()
{
    $bbcode_tpl = array();

// Quote
    $bbcode_tpl['quote_open'] = <<<HTML
	<div class="q-wrap">
		<div class="q">
HTML;

    $bbcode_tpl['quote_username_open'] = <<<HTML
	<div class="q-wrap">
		<div class="q" head="\\1">
HTML;

    $bbcode_tpl['quote_close'] = <<<HTML
		</div>
	</div>
HTML;

// Code
    $bbcode_tpl['code_open'] = <<<HTML
	<div class="c-wrap">
		<div class="c-body">
HTML;

    $bbcode_tpl['code_close'] = <<<HTML
		</div>
	</div>
HTML;

// Spoiler
    $bbcode_tpl['spoiler_open'] = <<<HTML
	<div class="sp-wrap">
		<div class="sp-body">
HTML;

    $bbcode_tpl['spoiler_title_open'] = <<<HTML
	<div class="sp-wrap">
		<div class="sp-body" title="\\1">
		<h3 class="sp-title">\\1</h3>
HTML;

    $bbcode_tpl['spoiler_close'] = <<<HTML
		</div>
	</div>
HTML;

// Image
    $bbcode_tpl['img'] = <<<HTML
	<var class="postImg" title="$1">&#10;</var>
HTML;

    $bbcode_tpl['img_aligned'] = <<<HTML
	<var class="postImg postImgAligned img-\\1" title="\\2">&#10;</var>
HTML;

// HR
    $bbcode_tpl['hr'] = <<<HTML
	<span class="post-hr">-</span>
HTML;

    array_deep($bbcode_tpl, 'bbcode_tpl_compact');
    return $bbcode_tpl;
}

function bbcode_tpl_compact($text)
{
    $text = str_compact($text);
    $text = str_replace('> <', '><', $text);
    return $text;
}

// prepare a posted message for entry into the database
function prepare_message($message)
{
    $message = \TorrentPier\Legacy\BBCode::clean_up($message);
    $message = htmlCHR($message, false, ENT_NOQUOTES);
    return $message;
}

// Fill smiley templates (or just the variables) with smileys
// Either in a window or inline
function generate_smilies($mode)
{
    global $bb_cfg, $template, $lang, $user, $datastore;

    $inline_columns = 4;
    $inline_rows = 7;
    $window_columns = 8;

    if ($mode == 'window') {
        $user->session_start();
    }

    $data = $datastore->get('smile_replacements');

    if ($sql = $data['smile']) {
        $num_smilies = 0;
        $rowset = array();
        foreach ($sql as $row) {
            if (empty($rowset[$row['smile_url']])) {
                $rowset[$row['smile_url']]['code'] = addslashes($row['code']);
                $rowset[$row['smile_url']]['emoticon'] = $row['emoticon'];
                $num_smilies++;
            }
        }

        if ($num_smilies) {
            $smilies_count = ($mode == 'inline') ? min(19, $num_smilies) : $num_smilies;
            $smilies_split_row = ($mode == 'inline') ? $inline_columns - 1 : $window_columns - 1;

            $s_colspan = 0;
            $row = 0;
            $col = 0;

            foreach ($rowset as $smile_url => $data) {
                if (!$col) {
                    $template->assign_block_vars('smilies_row', array());
                }

                $template->assign_block_vars('smilies_row.smilies_col', array(
                    'SMILEY_CODE' => $data['code'],
                    'SMILEY_IMG' => $bb_cfg['smilies_path'] . '/' . $smile_url,
                    'SMILEY_DESC' => $data['emoticon'],
                ));

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
                $template->assign_block_vars('switch_smilies_extra', array());

                $template->assign_vars(array(
                    'U_MORE_SMILIES' => POSTING_URL . "?mode=smilies",
                ));
            }

            $template->assign_vars(array(
                'PAGE_TITLE' => $lang['EMOTICONS'],
                'S_SMILIES_COLSPAN' => $s_colspan,
            ));
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
 * @param    string    Text to be stripped of quote tags
 *
 * @return    string
 */
function strip_quotes($text)
{
    $lowertext = strtolower($text);

    // find all [quote tags
    $start_pos = array();
    $curpos = 0;
    do {
        $pos = strpos($lowertext, '[quote', $curpos);
        if ($pos !== false) {
            $start_pos["$pos"] = 'start';
            $curpos = $pos + 6;
        }
    } while ($pos !== false);

    if (count($start_pos) == 0) {
        return $text;
    }

    // find all [/quote] tags
    $end_pos = array();
    $curpos = 0;
    do {
        $pos = strpos($lowertext, '[/quote', $curpos);
        if ($pos !== false) {
            $end_pos["$pos"] = 'end';
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
        $stack = array();
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
                unset($pos_list["$pos"]);
            }
        }
    } while ($stack);

    return $newtext;
}

// #############################################################################
/**
 * Strips away bbcode from a given string, leaving plain text
 *
 * @param    string    Text to be stripped of bbcode tags
 * @param    boolean    If true, strip away quote tags AND their contents
 * @param    boolean    If true, use the fast-and-dirty method rather than the shiny and nice method
 *
 * @return    string
 */
function strip_bbcode($message, $stripquotes = true, $fast_and_dirty = false, $showlinks = true)
{
    $find = array();
    $replace = array();

    if ($stripquotes) {
        // [quote=username] and [quote]
        $message = strip_quotes($message);
    }

    // a really quick and rather nasty way of removing bbcode
    if ($fast_and_dirty) {
        // any old thing in square brackets
        $find[] = '#\[.*/?\]#siU';
        $replace = '';

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

        $replace = array('[*]', '[hr]', '[br]', '[align=center]', '[align=left]', '[align=right]');
        $message = str_replace($replace, ' ', $message);
    }

    return $message;
}

function extract_search_words($text)
{
    global $bb_cfg;

    $max_words_count = $bb_cfg['max_search_words_per_post'];
    $min_word_len = max(2, $bb_cfg['search_min_word_len'] - 1);
    $max_word_len = $bb_cfg['search_max_word_len'];

    $text = ' ' . str_compact(strip_tags(mb_strtolower($text))) . ' ';
    $text = str_replace(array('&#91;', '&#93;'), array('[', ']'), $text);

    // HTML entities like &nbsp;
    $text = preg_replace('/(\w*?)&#?[0-9a-z]+;(\w*?)/iu', '', $text);
    // Remove URL's       ((www|ftp)\.[\w\#!$%&~/.\-;:=,?@а-яА-Я\[\]+]*?)
    $text = preg_replace('#\b[a-z0-9]+://[\w\#!$%&~/.\-;:=,?@а-яА-Я\[\]+]+(/[0-9a-z\?\.%_\-\+=&/]+)?#u', ' ', $text);
    $text = str_replace('[url=', ' ', $text);
    $text = str_replace('?', ' ', $text);
    $text = str_replace('!', ' ', $text);

    $text = strip_bbcode($text);

    // Filter out characters like ^, $, &, change "it's" to "its"
    $text = preg_replace('#[.,:;]#u', ' ', $text);

    // short & long words
    // $text = preg_replace('#(?<=^|\s)(\S{1,'.$min_word_len.'}|\S{'.$max_word_len.',}|\W*)(?=$|\s)#u', ' ', $text);

    // Trim 1+ spaces to one space and split this string into unique words
    $text = array_unique(explode(' ', str_compact($text)));

    // short & long words 2
    $text_out = array();
    foreach ($text as $word) {
        if (mb_strlen($word) > $min_word_len && mb_strlen($word) <= $max_word_len) {
            $text_out[] = $word;
        }
    }
    $text = $text_out;

    if (count($text) > $max_words_count) {
        #		shuffle($text);
        $text = array_splice($text, 0, $max_words_count);
    }

    return $text;
}

function add_search_words($post_id, $post_message, $topic_title = '', $only_return_words = false)
{
    global $bb_cfg;

    $text = $topic_title . ' ' . $post_message;
    $words = ($text) ? extract_search_words($text) : array();

    if ($only_return_words || $bb_cfg['search_engine_type'] == 'sphinx') {
        return implode("\n", $words);
    }

    DB()->query("DELETE FROM " . BB_POSTS_SEARCH . " WHERE post_id = $post_id");

    if ($words_sql = DB()->escape(implode("\n", $words))) {
        DB()->query("REPLACE INTO " . BB_POSTS_SEARCH . " (post_id, search_words) VALUES ($post_id, '$words_sql')");
    }
}

/**
 * @deprecated bb_code
 * Dirty class removed from here since 2.2.0
 * To add new bbcodes see at src/Legacy/BBCode.php
 */

function bbcode2html($text)
{
    global $bbcode;

    if (!isset($bbcode)) {
        $bbcode = new TorrentPier\Legacy\BBCode();
    }
    $orig_word = array();
    $replacement_word = array();
    obtain_word_list($orig_word, $replacement_word);
    if (count($orig_word)) {
        $text = preg_replace($orig_word, $replacement_word, $text);
    }
    return $bbcode->bbcode2html($text);
}

function get_words_rate($text)
{
    static $wr = null;
    if (!isset($wr)) {
        $wr = new TorrentPier\Legacy\WordsRate();
    }
    return $wr->get_words_rate($text);
}

function hide_passkey($str)
{
    global $bb_cfg;
    return preg_replace("#\?{$bb_cfg['passkey_key']}=[a-zA-Z0-9]{" . BT_AUTH_KEY_LENGTH . "}#", "?{$bb_cfg['passkey_key']}=passkey", $str);
}

function get_parsed_post($postrow, $mode = 'full', $return_chars = 600)
{
    global $bb_cfg;

    if ($bb_cfg['use_posts_cache'] && !empty($postrow['post_html'])) {
        return $postrow['post_html'];
    }

    $message = bbcode2html($postrow['post_text']);

    // Posts cache
    if ($bb_cfg['use_posts_cache']) {
        DB()->shutdown['post_html'][] = array(
            'post_id' => (int)$postrow['post_id'],
            'post_html' => (string)$message,
        );
    }

    return $message;
}

function update_post_html($postrow)
{
    DB()->query("DELETE FROM " . BB_POSTS_HTML . " WHERE post_id = " . (int)$postrow['post_id']);
}
