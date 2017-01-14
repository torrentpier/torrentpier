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
));

$page_cfg['include_bbcode_js'] = true;

//
// BBCode templates
//
/**
 * @return array
 */
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

/**
 * @param $text
 * @return mixed
 */
function bbcode_tpl_compact($text)
{
    $text = str_compact($text);
    $text = str_replace('> <', '><', $text);
    return $text;
}

// prepare a posted message for entry into the database
/**
 * @param $message
 * @return mixed|string
 */
function prepare_message($message)
{
    $message = bbcode::clean_up($message);
    $message = htmlCHR($message, false, ENT_NOQUOTES);
    return $message;
}

// Fill smiley templates (or just the variables) with smileys
// Either in a window or inline
/**
 * @param $mode
 */
function generate_smilies($mode)
{
    global $template, $lang, $datastore;

    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    $inline_columns = 4;
    $inline_rows = 7;
    $window_columns = 8;

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
            $smilies_split_row = ($mode == 'inline') ? $inline_columns - 1 : $window_columns - 1;

            $s_colspan = 0;
            $row = 0;
            $col = 0;

            while (list($smile_url, $data) = each($rowset)) {
                if (!$col) {
                    $template->assign_block_vars('smilies_row', array());
                }

                $template->assign_block_vars('smilies_row.smilies_col', array(
                    'SMILEY_CODE' => $data['code'],
                    'SMILEY_IMG' => $di->config->get('smilies_path') . '/' . $smile_url,
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

    if (sizeof($start_pos) == 0) {
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

    if (sizeof($end_pos) == 0) {
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
            $stacksize = sizeof($stack);
            if ($type == 'start') {
                // empty stack, so add from the last close tag or the beginning of the string
                if ($stacksize == 0) {
                    $newtext .= substr($text, $substr_pos, $pos - $substr_pos);
                }
                array_push($stack, $pos);
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

/**
 * Strips away bbcode from a given string, leaving plain text
 *
 * @param      string    Text to be stripped of bbcode tags
 * @param bool $stripquotes
 * @param bool $fast_and_dirty
 * @param bool $showlinks
 *
 * @return string
 * @internal param \If $boolean true, strip away quote tags AND their contents
 * @internal param \If $boolean true, use the fast-and-dirty method rather than the shiny and nice method
 *
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

/**
 * @param $text
 * @return mixed|string
 */
function extract_search_words($text)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    $max_words_count = $di->config->get('max_search_words_per_post');
    $min_word_len = max(2, $di->config->get('search_min_word_len') - 1);
    $max_word_len = $di->config->get('search_max_word_len');

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

    $text = remove_stopwords($text);
#	$text = replace_synonyms($text);

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

    if (sizeof($text) > $max_words_count) {
        #		shuffle($text);
        $text = array_splice($text, 0, $max_words_count);
    }

    return $text;
}

/**
 * @param $text
 * @return mixed
 */
function replace_synonyms($text)
{
    static $syn_match = null, $syn_replace = null;

    if (is_null($syn_match)) {
        preg_match_all("#(\w+) (\w+)(\r?\n|$)#", file_get_contents(LANG_DIR . 'search_synonyms.txt'), $m);

        $syn_match = $m[2];
        $syn_replace = $m[1];

        array_deep($syn_match, 'pad_with_space');
        array_deep($syn_replace, 'pad_with_space');
    }

    return ($syn_match && $syn_replace) ? str_replace($syn_match, $syn_replace, $text) : $text;
}

/**
 * @param $post_id
 * @param $post_message
 * @param string $topic_title
 * @param bool $only_return_words
 * @return string
 */
function add_search_words($post_id, $post_message, $topic_title = '', $only_return_words = false)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    $text = $topic_title . ' ' . $post_message;
    $words = ($text) ? extract_search_words($text) : array();

    if ($only_return_words || $di->config->get('sphinx_enabled')) {
        return join("\n", $words);
    } else {
        DB()->query("DELETE FROM " . BB_POSTS_SEARCH . " WHERE post_id = $post_id");

        if ($words_sql = DB()->escape(join("\n", $words))) {
            DB()->query("REPLACE INTO " . BB_POSTS_SEARCH . " (post_id, search_words) VALUES ($post_id, '$words_sql')");
        }
    }
}

/**
 * Class bbcode
 */
class bbcode
{
    public $tpl = array(); // шаблоны для замены тегов
    public $smilies = null;    // смайлы
    public $found_spam = null;    // найденные спам "слова"
    public $del_words = array(); // см. get_words_rate()
    public $tidy_cfg = array(
        'drop-empty-paras' => false,
        'fix-uri' => false,
        'force-output' => true,
        'hide-comments' => true,
        'join-classes' => false,
        'join-styles' => false,
        'merge-divs' => false,
        'newline' => 'LF',
        'output-xhtml' => true,
        'preserve-entities' => true,
        'quiet' => true,
        'quote-ampersand' => false,
        'show-body-only' => true,
        'show-errors' => false,
        'show-warnings' => false,
        'wrap' => 0,
    );
    public $block_tags = array(
        'align',
        'br',
        'clear',
        'hr',
        'list',
        'pre',
        'quote',
        'spoiler',
    );
    public $preg = array();
    public $str = array();
    public $preg_search = array();
    public $preg_repl = array();
    public $str_search = array();
    public $str_repl = array();

    /**
     * Constructor
     */
    public function bbcode()
    {
        $this->tpl = get_bbcode_tpl();

        $this->init_replacements();
    }

    /**
     * init_replacements
     */
    public function init_replacements()
    {
        $tpl = $this->tpl;
        $img_exp = '(https?:)?//[^\s\?&;=\#\"<>]+?\.(jpg|jpeg|gif|png)([a-z0-9/?&%;][^\[\]]*)?';
        $email_exp = '[a-z0-9&\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+';

        $this->preg = array(
            '#\[quote="(.+?)"\]#isu' => $tpl['quote_username_open'],
            '#\[spoiler="(.+?)"\]#isu' => $tpl['spoiler_title_open'],
            '#\[list=(a|A|i|I|1)\]#isu' => '<ul type="$1">',
            '#\[\*=(\d+)\]#isu' => '<li value="$1">',
            '#\[pre\](.*?)\[/pre\]#isu' => '<pre class="post-pre">$1</pre>',
            '#\[name=([a-zA-Z0-9_]+?)\]#isu' => '<a name="$1"></a>',
            '#\[url=\#([a-zA-Z0-9_]+?)\](.*?)\[/url\]#isu' => '<a class="postLink-name" href="#$1">$2</a>',
            '#\[color=([\#0-9a-zA-Z]+)\]#isu' => '<span style="color: $1;">',
            '#\[size=([1-2]?[0-9])\]#isu' => '<span style="font-size: $1px; line-height: normal;">',
            '#\[align=(left|right|center|justify)\]#isu' => '<span class="post-align" style="text-align: $1;">',
            '#\[font="([\w\- \']+)"\]#isu' => '<span style="font-family: $1;">',
            "#\[img\]($img_exp)\[/img\]#isu" => $tpl['img'],
            "#\[img=(left|right|center)\]($img_exp)\[/img\]\s*#isu" => $tpl['img_aligned'],
            "#\[email\]($email_exp)\[/email\]#isu" => '<a href="mailto:$1">$1</a>',
            "#\[qpost=([0-9]*)\]#isu" => '<u class="q-post">$1</u>',
        );

        $this->str = array(
            '[quote]' => $tpl['quote_open'],
            '[/quote]' => $tpl['quote_close'],
            '[spoiler]' => $tpl['spoiler_open'],
            '[/spoiler]' => $tpl['spoiler_close'],
            '[list]' => '<ul>',
            '[*]' => '<li>',
            '[/list]' => '</ul>',
            '[/color]' => '</span>',
            '[/size]' => '</span>',
            '[/align]' => '</span>',
            '[/font]' => '</span>',
            '[tab]' => '&nbsp;&nbsp;&nbsp;&nbsp;',
            '[br]' => "\n\n",
            '[hr]' => $tpl['hr'],
            '[b]' => '<span class="post-b">',
            '[/b]' => '</span>',
            '[u]' => '<span class="post-u">',
            '[/u]' => '</span>',
            '[i]' => '<span class="post-i">',
            '[/i]' => '</span>',
            '[s]' => '<span class="post-s">',
            '[/s]' => '</span>',
            '[del]' => '<span class="post-s">',
            '[/del]' => '</span>',
            '[clear]' => '<div class="clear">&nbsp;</div>',
        );

        $this->preg_search = array_keys($this->preg);
        $this->preg_repl = array_values($this->preg);
        $this->str_search = array_keys($this->str);
        $this->str_repl = array_values($this->str);
    }

    /**
     * bbcode2html
     * $text должен быть уже обработан htmlCHR($text, false, ENT_NOQUOTES);
     *
     * @param $text
     *
     * @return string
     */
    public function bbcode2html($text)
    {
        /** @var \TorrentPier\Di $di */
        $di = \TorrentPier\Di::getInstance();

        $text = " $text ";
        $text = $this->clean_up($text);
        $text = $this->spam_filter($text);

        // Tag parse
        if (strpos($text, '[') !== false) {
            // [code]
            $text = preg_replace_callback('#(\s*)\[code\](.+?)\[/code\](\s*)#s', array(&$this, 'code_callback'), $text);

            // Escape tags inside tiltes in [quote="tilte"]
            $text = preg_replace_callback('#(\[(quote|spoiler)=")(.+?)("\])#', array(&$this, 'escape_tiltes_callback'), $text);

            // [url]
            $url_exp = "[\w\#!$%&~/.\-;':=,?@а-яА-Я()\[\]+]+?";
            $text = preg_replace_callback("#\[url\]((?:https?://)?$url_exp)\[/url\]#isu", array(&$this, 'url_callback'), $text);
            $text = preg_replace_callback("#\[url\](www\.$url_exp)\[/url\]#isu", array(&$this, 'url_callback'), $text);
            $text = preg_replace_callback("#\[url=((?:https?://)?$url_exp)\]([^?\n\t].*?)\[/url\]#isu", array(&$this, 'url_callback'), $text);
            $text = preg_replace_callback("#\[url=(www\.$url_exp)\]([^?\n\t].*?)\[/url\]#isu", array(&$this, 'url_callback'), $text);

            // Normalize block level tags wrapped with new lines
            $block_tags = join('|', $this->block_tags);
            $text = str_replace("\n\n[hr]\n\n", '[br][hr][br]', $text);
            $text = preg_replace("#(\s*)(\[/?($block_tags)(.*?)\])(\s*)#", '$2', $text);

            // Tag replacements
            $text = preg_replace($this->preg_search, $this->preg_repl, $text);
            $text = str_replace($this->str_search, $this->str_repl, $text);
        }

        $text = $this->make_clickable($text);
        $text = $this->smilies_pass($text);
        $text = $this->new_line2html($text);
        $text = trim($text);

        if ($di->config->get('tidy_post')) {
            $text = $this->tidy($text);
        }

        return trim($text);
    }

    /**
     * Clean up
     *
     * @param $text
     *
     * @return mixed|string
     */
    public static function clean_up($text)
    {
        $text = trim($text);
        $text = str_replace("\r", '', $text);
        $text = preg_replace('#[ \t]+$#m', '', $text); // trailing spaces
        $text = preg_replace('#\n{3,}#', "\n\n", $text);
        return $text;
    }

    /**
     * Spam filter
     *
     * @param $text
     *
     * @return mixed|string
     */
    private function spam_filter($text)
    {
        /** @var \TorrentPier\Di $di */
        $di = \TorrentPier\Di::getInstance();

        static $spam_words = null;
        static $spam_replace = ' СПАМ';

        if (isset($this)) {
            $found_spam =& $this->found_spam;
        }

        // set $spam_words and $spam_replace
        if (!$di->config->get('spam_filter_file_path')) {
            return $text;
        }
        if (is_null($spam_words)) {
            $spam_words = file_get_contents($di->config->get('spam_filter_file_path'));
            $spam_words = strtolower($spam_words);
            $spam_words = explode("\n", $spam_words);
        }

        $found_spam = array();

        $msg_decoded = $text;
        $msg_decoded = html_entity_decode($msg_decoded);
        $msg_decoded = urldecode($msg_decoded);
        $msg_decoded = str_replace('&', ' &', $msg_decoded);

        $msg_search = strtolower($msg_decoded);

        foreach ($spam_words as $spam_str) {
            if (!$spam_str = trim($spam_str)) {
                continue;
            }
            if (strpos($msg_search, $spam_str) !== false) {
                $found_spam[] = $spam_str;
            }
        }
        if ($found_spam) {
            $spam_exp = array();
            foreach ($found_spam as $keyword) {
                $spam_exp[] = preg_quote($keyword, '/');
            }
            $spam_exp = join('|', $spam_exp);

            $text = preg_replace("/($spam_exp)(\S*)/i", $spam_replace, $msg_decoded);
            $text = htmlCHR($text, false, ENT_NOQUOTES);
        }

        return $text;
    }

    /**
     * [code] callback
     *
     * @param $m
     *
     * @return string
     */
    public function code_callback($m)
    {
        $code = trim($m[2]);
        $code = str_replace('  ', '&nbsp; ', $code);
        $code = str_replace('  ', ' &nbsp;', $code);
        $code = str_replace("\t", '&nbsp; ', $code);
        $code = str_replace(array('[', ']', ':', ')'), array('&#91;', '&#93;', '&#58;', '&#41;'), $code);
        return $this->tpl['code_open'] . $code . $this->tpl['code_close'];
    }

    /**
     * [url] callback
     *
     * @param $m
     *
     * @return string
     */
    public function url_callback($m)
    {
        /** @var \TorrentPier\Di $di */
        $di = \TorrentPier\Di::getInstance();

        $url = trim($m[1]);
        $url_name = (isset($m[2])) ? trim($m[2]) : $url;

        if (!preg_match("#^https?://#isu", $url) && !preg_match("/^#/", $url)) {
            $url = 'http://' . $url;
        }

        if (in_array(parse_url($url, PHP_URL_HOST), $di->config->get('nofollow.allowed_url')) || $di->config->get('nofollow.disabled')) {
            $link = "<a href=\"$url\" class=\"postLink\">$url_name</a>";
        } else {
            $link = "<a href=\"$url\" class=\"postLink\" rel=\"nofollow\">$url_name</a>";
        }

        return $link;
    }

    /**
     * Escape tags inside tiltes in [quote="tilte"]
     *
     * @param $m
     *
     * @return string
     */
    public function escape_tiltes_callback($m)
    {
        $tilte = substr($m[3], 0, 250);
        $tilte = str_replace(array('[', ']', ':', ')', '"'), array('&#91;', '&#93;', '&#58;', '&#41;', '&#34;'), $tilte);
        // еще раз htmlspecialchars, т.к. при извлечении из title происходит обратное преобразование
        $tilte = htmlspecialchars($tilte, ENT_QUOTES);
        return $m[1] . $tilte . $m[4];
    }

    /**
     * make_clickable
     *
     * @param $text
     *
     * @return string
     */
    public function make_clickable($text)
    {
        $url_regexp = "#
			(?<![\"'=])
			\b
			(
				https?://[\w\#!$%&~/.\-;':=?@а-яА-Я()\[\]+]+
			)
			(?![\"']|\[/url|\[/img|</a)
			(?=[,!]?\s|[\)<!])
		#xiu";

        // pad it with a space so we can match things at the start of the 1st line.
        $ret = " $text ";

        // hide passkey
        $ret = hide_passkey($ret);

        // matches an "xxxx://yyyy" URL at the start of a line, or after a space.
        $ret = preg_replace_callback($url_regexp, array(&$this, 'make_url_clickable_callback'), $ret);

        // Remove our padding..
        $ret = substr(substr($ret, 0, -1), 1);

        return ($ret);
    }

    /**
     * make_url_clickable_callback
     *
     * @param $m
     *
     * @return string
     */
    public function make_url_clickable_callback($m)
    {
        /** @var \TorrentPier\Di $di */
        $di = \TorrentPier\Di::getInstance();

        $max_len = 70;
        $href = $m[1];
        $name = (mb_strlen($href, 'UTF-8') > $max_len) ? mb_substr($href, 0, $max_len - 19) . '...' . mb_substr($href, -16) : $href;

        if (in_array(parse_url($href, PHP_URL_HOST), $di->config->get('nofollow.allowed_url')) || $di->config->get('nofollow.disabled')) {
            $link = "<a href=\"$href\" class=\"postLink\">$name</a>";
        } else {
            $link = "<a href=\"$href\" class=\"postLink\" rel=\"nofollow\">$name</a>";
        }

        return $link;
    }

    /**
     * smilies_pass
     *
     * @param $text
     *
     * @return mixed
     */
    public function smilies_pass($text)
    {
        global $datastore;

        if (is_null($this->smilies)) {
            $this->smilies = $datastore->get('smile_replacements');
        }
        if ($this->smilies) {
            $parsed_text = preg_replace($this->smilies['orig'], $this->smilies['repl'], $text, 101, $smilies_cnt);
            $text = ($smilies_cnt <= 100) ? $parsed_text : $text;
        }

        return $text;
    }

    /**
     * new_line2html
     *
     * @param $text
     *
     * @return mixed
     */
    public function new_line2html($text)
    {
        $text = preg_replace('#\n{2,}#', '<span class="post-br"><br /></span>', $text);
        $text = str_replace("\n", '<br />', $text);
        return $text;
    }

    /**
     * tidy
     *
     * @param $text
     *
     * @return string
     */
    public function tidy($text)
    {
        $text = tidy_repair_string($text, $this->tidy_cfg, 'utf8');
        return $text;
    }
}

/**
 * @param $text
 * @return string
 */
function bbcode2html($text)
{
    global $bbcode;

    if (!isset($bbcode)) {
        $bbcode = new bbcode();
    }
    $orig_word = array();
    $replacement_word = array();
    obtain_word_list($orig_word, $replacement_word);
    if (count($orig_word)) {
        $text = preg_replace($orig_word, $replacement_word, $text);
    }
    return $bbcode->bbcode2html($text);
}

/**
 * Class words_rate
 */
class words_rate
{
    public $dbg_mode = false;
    public $words_rate = 0;
    public $deleted_words = array();
    public $del_text_hl = '';
    public $words_del_exp = '';
    public $words_cnt_exp = '#[a-zA-Zа-яА-ЯёЁ]{4,}#';

    public function words_rate()
    {
        // слова начинающиеся на..
        $del_list = file_get_contents(BB_ROOT . '/library/words_rate_del_list.txt');
        $del_list = str_compact($del_list);
        $del_list = str_replace(' ', '|', preg_quote($del_list, '/'));
        $del_exp = '/\b(' . $del_list . ')[\w\-]*/i';

        $this->words_del_exp = $del_exp;
    }

    /**
     * возвращает "показатель полезности" сообщения используемый для автоудаления коротких сообщений типа "спасибо", "круто" и т.д.
     *
     * @param $text
     *
     * @return int
     */
    public function get_words_rate($text)
    {
        $this->words_rate = 127;     // максимальное значение по умолчанию
        $this->deleted_words = array();
        $this->del_text_hl = $text;

        // длинное сообщение
        if (strlen($text) > 600) {
            return $this->words_rate;
        }
        // вырезаем цитаты если содержит +1
        if (preg_match('#\+\d+#', $text)) {
            $text = strip_quotes($text);
        }
        // содержит ссылку
        if (strpos($text, '://')) {
            return $this->words_rate;
        }
        // вопрос
        if ($questions = preg_match_all('#\w\?+#', $text, $m)) {
            if ($questions >= 1) {
                return $this->words_rate;
            }
        }

        if ($this->dbg_mode) {
            preg_match_all($this->words_del_exp, $text, $this->deleted_words);
            $text_dbg = preg_replace($this->words_del_exp, '<span class="del-word">$0</span>', $text);
            $this->del_text_hl = '<div class="prune-post">' . $text_dbg . '</div>';
        }
        $text = preg_replace($this->words_del_exp, '', $text);

        // удаление смайлов
        $text = preg_replace('#:\w+:#', '', $text);
        // удаление bbcode тегов
        $text = preg_replace('#\[\S+\]#', '', $text);

        $words_count = preg_match_all($this->words_cnt_exp, $text, $m);

        if ($words_count !== false && $words_count < 127) {
            $this->words_rate = ($words_count == 0) ? 1 : $words_count;
        }

        return $this->words_rate;
    }
}

/**
 * @param $text
 * @return int
 */
function get_words_rate($text)
{
    static $wr = null;
    if (!isset($wr)) {
        $wr = new words_rate();
    }
    return $wr->get_words_rate($text);
}

/**
 * @param $str
 * @return mixed
 */
function hide_passkey($str)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    return preg_replace("#\?{$di->config->get('passkey_key')}=[a-zA-Z0-9]{" . BT_AUTH_KEY_LENGTH . "}#", "?{$di->config->get('passkey_key')}=passkey", $str);
}

/**
 * @param $postrow
 * @param string $mode
 * @param int $return_chars
 * @return string
 */
function get_parsed_post($postrow, $mode = 'full', $return_chars = 600)
{
    /** @var \TorrentPier\Di $di */
    $di = \TorrentPier\Di::getInstance();

    if ($di->config->get('use_posts_cache') && !empty($postrow['post_html'])) {
        return $postrow['post_html'];
    }

    $message = bbcode2html($postrow['post_text']);

    // Posts cache
    if ($di->config->get('use_posts_cache')) {
        DB()->shutdown['post_html'][] = array(
            'post_id' => (int)$postrow['post_id'],
            'post_html' => (string)$message,
        );
    }

    return $message;
}

/**
 * @param $postrow
 */
function update_post_html($postrow)
{
    DB()->query("DELETE FROM " . BB_POSTS_HTML . " WHERE post_id = " . (int)$postrow['post_id'] . " LIMIT 1");
}
