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
        // слова начинающиеся на..
        $del_list = file_get_contents(BB_ROOT . '/library/words_rate_del_list.txt');
        $del_list = str_compact($del_list);
        $del_list = str_replace(' ', '|', preg_quote($del_list, '/'));
        $del_exp = '/\b(' . $del_list . ')[\w\-]*/i';

        $this->words_del_exp = $del_exp;
    }

    /**
     * Возвращает "показатель полезности" сообщения используемый для автоудаления коротких сообщений типа "спасибо", "круто" и т.д.
     *
     * @param string $text
     * @return int
     */
    public function get_words_rate($text)
    {
        $this->words_rate = 127;     // максимальное значение по умолчанию
        $this->deleted_words = [];
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
