<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy;

/**
 * Class BBCode
 * @package TorrentPier\Legacy
 */
class BBCode
{
    /** @var array Replacements for some code elements */
    public array $tpl = [];

    /** @var array Replacements for smilies */
    public array $smilies = [];

    /** @var array Tidy preprocessor configuration */
    public array $tidy_cfg = [
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

    /** @var array Define some elements as block-processed */
    public array $block_tags = [
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
     * Clean up test from trailing spaces and more
     *
     * @param string $text
     *
     * @return string
     */
    public static function clean_up(string $text): string
    {
        $text = trim($text);
        $text = str_replace("\r", '', $text);
        $text = preg_replace('#[ \t]+$#m', '', $text);

        return preg_replace('#\n{3,}#', "\n\n", $text);
    }

    /**
     * Convert bbcodes to html. Text must be prepared with htmlCHR
     *
     * @param string $text
     *
     * @return string
     */
    public function bbcode2html(string $text): string
    {
        $text = self::clean_up($text);
        $text = $this->parse($text);
        $text = $this->make_clickable($text);
        $text = $this->smilies_pass($text);
        $text = $this->new_line2html($text);

        if (config()->get('tidy_post')) {
            $text = $this->tidy($text);
        }

        return trim($text);
    }

    /**
     * Initialize replacements for elements
     */
    private function init_replacements(): void
    {
        $tpl = $this->tpl;
        $img_exp = '(https?:)?//[^\s\?&;=\#\"<>]+?\.(jpg|jpeg|gif|png|bmp|webp|avif)([a-z0-9/?&%;][^\[\]]*)?';
        $email_exp = '[a-z0-9&\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+';

        $this->preg = [
            '#\[quote="(.+?)"\]#isu' => $tpl['quote_username_open'],
            '#\[spoiler="(.+?)"\]#isu' => $tpl['spoiler_title_open'],
            '#\[acronym="(.+?)"\]#isu' => '<span class="post-acronym" title="$1">',
            '#\[list=(a|A|i|I|1)\]#isu' => '<ul type="$1">',
            '#\[\*=(\d+)\]#isu' => '<li value="$1">',
            '#\[pre\](.*?)\[/pre\]#isu' => '<pre class="post-pre">$1</pre>',
            '#\[name=([a-zA-Z0-9_]+?)\]#isu' => '<a name="$1"></a>',
            '#\[url=\#([a-zA-Z0-9_]+?)\](.*?)\[/url\]#isu' => '<a class="postLink-name" href="#$1">$2</a>',
            '#\[color=([\#0-9a-zA-Z]+)\]#isu' => '<span style="color: $1;">',
            '#\[size=([1-2]?[0-9])\]#isu' => '<span style="font-size: $1px; line-height: normal;">',
            '#\[align=(left|right|center|justify)\]#isu' => '<span class="post-align" style="text-align: $1;">',
            '#\[font="([\w\- \']+)"\]#isu' => '<span style="font-family: $1;">',
            '#\[font=([\w\- \']+)\]#isu' => '<span style="font-family: $1;">',
            "#\\[img\\]({$img_exp})\\[/img\\]#isu" => $tpl['img'],
            "#\\[img=(left|right|center)\\]({$img_exp})\\[/img\\]\\s*#isu" => $tpl['img_aligned'],
            "#\\[email\\]({$email_exp})\\[/email\\]#isu" => '<a href="mailto:$1">$1</a>',
            '#\\[qpost=([0-9]*)\\]#isu' => '<u class="q-post">$1</u>',
            '#\[box=(?:\s*[\'"])?([\#0-9a-zA-Z]+)(?:[\'"]\s*)?\]#isu' => $tpl['box_open_color_single'],
            '#\[box=(?:\s*[\'"])?([\#0-9a-zA-Z]+)(?:[\'"]\s*)?,\s*[\'"]?([\#0-9a-zA-Z]+)[\'"]?\]#isu' => $tpl['box_open_color'],
        ];

        $this->str = [
            '[quote]' => $tpl['quote_open'],
            '[/quote]' => $tpl['quote_close'],
            '[spoiler]' => $tpl['spoiler_open'],
            '[/spoiler]' => $tpl['spoiler_close'],
            '[/acronym]' => '</span>',
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
            '[sup]' => '<sup><small>',
            '[/sup]' => '</small></sup>',
            '[sub]' => '<sub><small>',
            '[/sub]' => '</small></sub>',
            '[box]' => $tpl['box_open'],
            '[/box]' => $tpl['box_close'],
            '[indent]' => '<div class="post-indent">',
            '[/indent]' => '</div>',
            '[pre]' => '<pre class="post-pre">',
            '[/pre]' => '</pre>',
            '[nfo]' => '<pre class="post-nfo">',
            '[/nfo]' => '</pre>',
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
     * Parse elements in the text
     *
     * @param string $text
     *
     * @return string
     */
    private function parse(string $text): string
    {
        // Tag parse
        if (!str_contains($text, '[')) {
            return $text;
        }

        // [code]
        $text = preg_replace_callback('#(\s*)\[code\](.+?)\[/code\](\s*)#s', [&$this, 'code_callback'], $text);

        // Escape tags inside titles in [quote="tilte"]
        $text = preg_replace_callback('#(\[(quote|spoiler)=")(.+?)("\])#', [&$this, 'escape_titles_callback'], $text);

        // [url]
        $url_exp = '[\w\#!$%&~/.\-;:=,?@а-яА-Я()\[\]+]+?';
        $text = preg_replace_callback("#\\[url\\]((?:https?://)?{$url_exp})\\[/url\\]#isu", [&$this, 'url_callback'], $text);
        $text = preg_replace_callback("#\\[url\\](www\\.{$url_exp})\\[/url\\]#isu", [&$this, 'url_callback'], $text);
        $text = preg_replace_callback("#\\[url=((?:https?://)?{$url_exp})\\]([^?\n\t].*?)\\[/url\\]#isu", [&$this, 'url_callback'], $text);
        $text = preg_replace_callback("#\\[url=(www\\.{$url_exp})\\]([^?\n\t].*?)\\[/url\\]#isu", [&$this, 'url_callback'], $text);

        // Normalize block level tags wrapped with new lines
        $block_tags = implode('|', $this->block_tags);
        $text = str_replace("\n\n[hr]\n\n", '[br][hr][br]', $text);
        $text = preg_replace("#(\\s*)(\\[/?({$block_tags})(.*?)\\])(\\s*)#", '$2', $text);

        // Tag replacements
        $text = preg_replace($this->preg_search, $this->preg_repl, $text);
        $text = str_replace($this->str_search, $this->str_repl, $text);

        return $text;
    }

    /**
     * Callback to [code]
     *
     * @param array $m
     *
     * @return string
     */
    private function code_callback(array $m): string
    {
        $code = trim($m[2]);
        $code = str_replace('  ', '&nbsp; ', $code);
        $code = str_replace('  ', ' &nbsp;', $code);
        $code = str_replace("\t", '&nbsp; ', $code);
        $code = str_replace(['[', ']', ':', ')'], ['&#91;', '&#93;', '&#58;', '&#41;'], $code);

        return $this->tpl['code_open'] . $code . $this->tpl['code_close'];
    }

    /**
     * Callback to [url]
     *
     * @param array $m
     *
     * @return string
     */
    private function url_callback(array $m): string
    {
        $url = trim($m[1]);
        $url_name = isset($m[2]) ? trim($m[2]) : $url;
        $url_parse = parse_url($url);

        if (!isset($url_parse['scheme']) && isset($url_parse['path'])) {
            if (!preg_match('/^([a-zA-Z0-9_\-\.]+\.php)(\?[^#]*)?$/', $url_parse['path'])) {
                $url = 'http://' . $url;
            }
        }

        return $this->nofollow_url($url, $url_name);
    }

    /**
     * Callback to escape titles in block elements
     *
     * @param array $m
     *
     * @return string
     */
    private function escape_titles_callback(array $m): string
    {
        $title = substr($m[3], 0, 250);
        $title = str_replace(['[', ']', ':', ')', '"'], ['&#91;', '&#93;', '&#58;', '&#41;', '&#34;'], $title);
        // reconvert because after extracting title there's a reverse convertion
        $title = htmlspecialchars($title, ENT_QUOTES);

        return $m[1] . $title . $m[4];
    }

    /**
     * Callback to make text clickable
     *
     * @param string $text
     *
     * @return string
     */
    private function make_clickable(string $text): string
    {
        $url_regexp = "#
			(?<![\"'=])
			\\b
			(
				https?://[\\w\\#!$%&~/.\\-;:=?@а-яА-Я()\\[\\]+]+
			)
			(?![\"']|\\[/url|\\[/img|</a)
			(?=[,!]?\\s|[\\)<!])
		#xiu";

        // pad it with a space so we can match things at the start of the 1st line.
        $ret = " {$text} ";

        // hide passkey
        $ret = hide_passkey($ret);

        // matches an "xxxx://yyyy" URL at the start of a line, or after a space.
        $ret = preg_replace_callback($url_regexp, [&$this, 'make_url_clickable_callback'], $ret);

        // Remove our padding..
        $ret = substr(substr($ret, 0, -1), 1);

        return $ret;
    }

    /**
     * Callback to make URL clickable
     *
     * @param array $m
     *
     * @return string
     */
    private function make_url_clickable_callback(array $m): string
    {
        $max_len = 70;
        $href = $m[1];
        $name = (mb_strlen($href, DEFAULT_CHARSET) > $max_len) ? mb_substr($href, 0, $max_len - 19) . '...' . mb_substr($href, -16) : $href;

        return $this->nofollow_url($href, $name);
    }

    /**
     * Replace smilies to images in text
     *
     * @param string $text
     *
     * @return string
     */
    private function smilies_pass(string $text): string
    {
        $this->smilies = datastore()->get('smile_replacements');

        if (!empty($this->smilies)) {
            if (\defined('IN_ADMIN')) {
                foreach ($this->smilies['repl'] as &$smile) {
                    $smile = preg_replace('/src="([^"]+)"/', 'src="./../$1"', $smile);
                }
                unset($smile);
            }

            /** @noinspection NestedPositiveIfStatementsInspection */
            if ($parsed_text = preg_replace($this->smilies['orig'], $this->smilies['repl'], $text)) {
                return $parsed_text;
            }
        }

        return $text;
    }

    /**
     * Replace text new line to html
     *
     * @param string $text
     *
     * @return string
     */
    private function new_line2html(string $text): string
    {
        $text = preg_replace('#\n{2,}#', '<span class="post-br"><br /></span>', $text);

        return str_replace("\n", '<br />', $text);
    }

    /**
     * Prepare post text with tidy preprocessor
     *
     * @param string $text
     *
     * @return string
     */
    private function tidy(string $text): string
    {
        return tidy_repair_string($text, $this->tidy_cfg, 'utf8');
    }

    /**
     * Nofollow links handling
     *
     * @param string $href
     * @param string $name
     * @return string
     */
    private function nofollow_url(string $href, string $name): string
    {
        if (\in_array(parse_url($href, PHP_URL_HOST), config()->get('nofollow.allowed_url')) || config()->get('nofollow.disabled')) {
            $link = "<a href=\"{$href}\" class=\"postLink\">{$name}</a>";
        } else {
            $link = "<a href=\"{$href}\" class=\"postLink\" rel=\"nofollow\">{$name}</a>";
        }

        return $link;
    }
}
