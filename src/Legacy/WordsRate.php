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
 * Class WordsRate
 * @package TorrentPier\Legacy
 */
class WordsRate
{
    public $dbg_mode = false;
    public $words_rate = 0;
    public $deleted_words = [];
    public $del_text_hl = '';
    public $words_del_exp = '';
    public $words_cnt_exp = '#[a-zA-Zа-яА-ЯёЁ]{4,}#';

    public function __construct()
    {
        // words starting with..
        $del_list = file_get_contents(BB_ROOT . '/library/words_rate_del_list.txt');
        $del_list = str_compact($del_list);
        $del_list = str_replace(' ', '|', preg_quote($del_list, '/'));
        $del_exp = '/\b(' . $del_list . ')[\w\-]*/i';

        $this->words_del_exp = $del_exp;
    }

    /**
     * Returns "usefulness coefficient" for automatic deletion of short sentences as "thanks", "cool" and etc.
     *
     * @param string $text
     * @return int
     */
    public function get_words_rate($text)
    {
        $this->words_rate = 127;     // maximum value by default
        $this->deleted_words = [];
        $this->del_text_hl = $text;

        // Long text
        if (mb_strlen($text, DEFAULT_CHARSET) > 600) {
            return $this->words_rate;
        }
        // Crop quotes if contains +1
        if (preg_match('#\+\d+#', $text)) {
            $text = strip_quotes($text);
        }
        // Contains a link
        if (strpos($text, '://')) {
            return $this->words_rate;
        }
        // Question
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

        // Delete smilies
        $text = preg_replace('#:\w+:#', '', $text);
        // Delete bb_code tags
        $text = preg_replace('#\[\S+\]#', '', $text);

        $words_count = preg_match_all($this->words_cnt_exp, $text, $m);

        if ($words_count !== false && $words_count < 127) {
            $this->words_rate = ($words_count == 0) ? 1 : $words_count;
        }

        return $this->words_rate;
    }
}
