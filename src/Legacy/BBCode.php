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

namespace TorrentPier\Legacy;

/**
 * Class BBCode
 * @package TorrentPier\Legacy
 */
class BBCode
{
    public $tpl = []; // шаблоны для замены тегов
    public $smilies;    // смайлы
    public $found_spam;    // найденные спам "слова"
    public $del_words = []; // см. get_words_rate()
    public $tidy_cfg = [
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
    ];
    public $block_tags = [
        'align',
        'br',
        'clear',
        'hr',
        'list',
        'pre',
        'quote',
        'spoiler',
    ];
    public $preg = [];
    public $str = [];
    public $preg_search = [];
    public $preg_repl = [];
    public $str_search = [];
    public $str_repl = [];

    /**
     * Constructor
     */
    public function __construct()
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

        $this->preg = [
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
        ];

        $this->str = [
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
        ];

        $this->preg_search = array_keys($this->preg);
        $this->preg_repl = array_values($this->preg);
        $this->str_search = array_keys($this->str);
        $this->str_repl = array_values($this->str);
    }

    /**
     * bbcode2html
     *
     * @param string $text должен быть уже обработан htmlCHR($text, false, ENT_NOQUOTES)
     * @return string
     */
    public function bbcode2html($text)
    {
        global $bb_cfg;

        $text = " $text ";
        $text = static::clean_up($text);
        $text = $this->spam_filter($text);

        // Tag parse
        if (strpos($text, '[') !== false) {
            // [code]
            $text = preg_replace_callback('#(\s*)\[code\](.+?)\[/code\](\s*)#s', [&$this, 'code_callback'], $text);

            // Escape tags inside tiltes in [quote="tilte"]
            $text = preg_replace_callback('#(\[(quote|spoiler)=")(.+?)("\])#', [&$this, 'escape_tiltes_callback'], $text);

            // [url]
            $url_exp = '[\w\#!$%&~/.\-;:=,?@а-яА-Я()\[\]+]+?';
            $text = preg_replace_callback("#\[url\]((?:https?://)?$url_exp)\[/url\]#isu", [&$this, 'url_callback'], $text);
            $text = preg_replace_callback("#\[url\](www\.$url_exp)\[/url\]#isu", [&$this, 'url_callback'], $text);
            $text = preg_replace_callback("#\[url=((?:https?://)?$url_exp)\]([^?\n\t].*?)\[/url\]#isu", [&$this, 'url_callback'], $text);
            $text = preg_replace_callback("#\[url=(www\.$url_exp)\]([^?\n\t].*?)\[/url\]#isu", [&$this, 'url_callback'], $text);

            // Normalize block level tags wrapped with new lines
            $block_tags = implode('|', $this->block_tags);
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

        if ($bb_cfg['tidy_post']) {
            $text = $this->tidy($text);
        }

        return trim($text);
    }

    /**
     * Clean up
     *
     * @param string $text
     * @return string
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
     * @param string $text
     * @return string
     */
    private function spam_filter($text)
    {
        global $bb_cfg;
        static $spam_words = null;
        static $spam_replace = ' СПАМ';

        if (isset($this)) {
            $found_spam =& $this->found_spam;
        }

        // set $spam_words and $spam_replace
        if (!$bb_cfg['spam_filter_file_path']) {
            return $text;
        }
        if (null === $spam_words) {
            $spam_words = file_get_contents($bb_cfg['spam_filter_file_path']);
            $spam_words = strtolower($spam_words);
            $spam_words = explode("\n", $spam_words);
        }

        $found_spam = [];

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
            $spam_exp = [];
            foreach ($found_spam as $keyword) {
                $spam_exp[] = preg_quote($keyword, '/');
            }
            $spam_exp = implode('|', $spam_exp);

            $text = preg_replace("/($spam_exp)(\S*)/i", $spam_replace, $msg_decoded);
            $text = htmlCHR($text, false, ENT_NOQUOTES);
        }

        return $text;
    }

    /**
     * [code] callback
     *
     * @param string $m
     * @return string
     */
    public function code_callback($m)
    {
        $code = trim($m[2]);
        $code = str_replace('  ', '&nbsp; ', $code);
        $code = str_replace('  ', ' &nbsp;', $code);
        $code = str_replace("\t", '&nbsp; ', $code);
        $code = str_replace(['[', ']', ':', ')'], ['&#91;', '&#93;', '&#58;', '&#41;'], $code);
        return $this->tpl['code_open'] . $code . $this->tpl['code_close'];
    }

    /**
     * [url] callback
     *
     * @param string $m
     * @return string
     */
    public function url_callback($m)
    {
        global $bb_cfg;

        $url = trim($m[1]);
        $url_name = isset($m[2]) ? trim($m[2]) : $url;

        if (!preg_match('#^https?://#iu', $url) && !preg_match('/^#/', $url)) {
            $url = 'http://' . $url;
        }

        if (in_array(parse_url($url, PHP_URL_HOST), $bb_cfg['nofollow']['allowed_url']) || $bb_cfg['nofollow']['disabled']) {
            $link = "<a href=\"$url\" class=\"postLink\">$url_name</a>";
        } else {
            $link = "<a href=\"$url\" class=\"postLink\" rel=\"nofollow\">$url_name</a>";
        }

        return $link;
    }

    /**
     * Escape tags inside titles in [quote="title"]
     *
     * @param string $m
     * @return string
     */
    public function escape_tiltes_callback($m)
    {
        $tilte = substr($m[3], 0, 250);
        $tilte = str_replace(['[', ']', ':', ')', '"'], ['&#91;', '&#93;', '&#58;', '&#41;', '&#34;'], $tilte);
        // еще раз htmlspecialchars, т.к. при извлечении из title происходит обратное преобразование
        $tilte = htmlspecialchars($tilte, ENT_QUOTES);
        return $m[1] . $tilte . $m[4];
    }

    /**
     * Make clickable
     *
     * @param $text
     * @return string
     */
    public function make_clickable($text)
    {
        $url_regexp = "#
			(?<![\"'=])
			\b
			(
				https?://[\w\#!$%&~/.\-;:=?@а-яА-Я()\[\]+]+
			)
			(?![\"']|\[/url|\[/img|</a)
			(?=[,!]?\s|[\)<!])
		#xiu";

        // pad it with a space so we can match things at the start of the 1st line.
        $ret = " $text ";

        // hide passkey
        $ret = hide_passkey($ret);

        // matches an "xxxx://yyyy" URL at the start of a line, or after a space.
        $ret = preg_replace_callback($url_regexp, [&$this, 'make_url_clickable_callback'], $ret);

        // Remove our padding..
        $ret = substr(substr($ret, 0, -1), 1);

        return ($ret);
    }

    /**
     * Make url clickable
     *
     * @param string $m
     * @return string
     */
    public function make_url_clickable_callback($m)
    {
        global $bb_cfg;

        $max_len = 70;
        $href = $m[1];
        $name = (mb_strlen($href, 'UTF-8') > $max_len) ? mb_substr($href, 0, $max_len - 19) . '...' . mb_substr($href, -16) : $href;

        if (in_array(parse_url($href, PHP_URL_HOST), $bb_cfg['nofollow']['allowed_url']) || $bb_cfg['nofollow']['disabled']) {
            $link = "<a href=\"$href\" class=\"postLink\">$name</a>";
        } else {
            $link = "<a href=\"$href\" class=\"postLink\" rel=\"nofollow\">$name</a>";
        }

        return $link;
    }

    /**
     * Add smilies
     *
     * @param string $text
     * @return string
     */
    public function smilies_pass($text)
    {
        global $datastore;

        if (null === $this->smilies) {
            $this->smilies = $datastore->get('smile_replacements');
        }
        if ($this->smilies) {
            $parsed_text = preg_replace($this->smilies['orig'], $this->smilies['repl'], $text, 101, $smilies_cnt);
            $text = ($smilies_cnt <= 100) ? $parsed_text : $text;
        }

        return $text;
    }

    /**
     * Replace new line code to html
     *
     * @param string $text
     * @return string
     */
    public function new_line2html($text)
    {
        $text = preg_replace('#\n{2,}#', '<span class="post-br"><br /></span>', $text);
        $text = str_replace("\n", '<br />', $text);
        return $text;
    }

    /**
     * Tidy
     *
     * @param string $text
     * @return string
     */
    public function tidy($text)
    {
        $text = tidy_repair_string($text, $this->tidy_cfg, 'utf8');
        return $text;
    }
}
