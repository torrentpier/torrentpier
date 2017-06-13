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
 * Class Poll
 * @package TorrentPier\Legacy
 */
class Poll
{
    public $err_msg = '';
    public $poll_votes = [];
    public $max_votes = 0;

    public function __construct()
    {
        global $bb_cfg;
        $this->max_votes = $bb_cfg['max_poll_options'];
    }

    /**
     * Формирование результатов голосования
     *
     * @param $posted_data
     * @return string
     */
    public function build_poll_data($posted_data)
    {
        $poll_caption = (string)@$posted_data['poll_caption'];
        $poll_votes = (string)@$posted_data['poll_votes'];
        $this->poll_votes = [];

        if (!$poll_caption = str_compact($poll_caption)) {
            global $lang;
            return $this->err_msg = $lang['EMPTY_POLL_TITLE'];
        }
        $this->poll_votes[] = $poll_caption; // заголовок имеет vote_id = 0

        foreach (explode("\n", $poll_votes) as $vote) {
            if (!$vote = str_compact($vote)) {
                continue;
            }
            $this->poll_votes[] = $vote;
        }

        // проверять на "< 3" -- 2 варианта ответа + заголовок
        if (count($this->poll_votes) < 3 || count($this->poll_votes) > $this->max_votes + 1) {
            global $lang;
            return $this->err_msg = sprintf($lang['NEW_POLL_VOTES'], $this->max_votes);
        }
    }

    /**
     * Добавление голосов в базу данных
     *
     * @param integer $topic_id
     */
    public function insert_votes_into_db($topic_id)
    {
        $this->delete_votes_data($topic_id);

        $sql_ary = [];
        foreach ($this->poll_votes as $vote_id => $vote_text) {
            $sql_ary[] = [
                'topic_id' => (int)$topic_id,
                'vote_id' => (int)$vote_id,
                'vote_text' => (string)$vote_text,
                'vote_result' => (int)0,
            ];
        }
        $sql_args = DB()->build_array('MULTI_INSERT', $sql_ary);

        DB()->query("REPLACE INTO " . BB_POLL_VOTES . $sql_args);

        DB()->query("UPDATE " . BB_TOPICS . " SET topic_vote = 1 WHERE topic_id = $topic_id");
    }

    /**
     * Удаление голосования
     *
     * @param integer $topic_id
     */
    public function delete_poll($topic_id)
    {
        DB()->query("UPDATE " . BB_TOPICS . " SET topic_vote = 0 WHERE topic_id = $topic_id");
        $this->delete_votes_data($topic_id);
    }

    /**
     * Удаление информации о проголосовавших и голосов
     *
     * @param integer $topic_id
     */
    public function delete_votes_data($topic_id)
    {
        DB()->query("DELETE FROM " . BB_POLL_VOTES . " WHERE topic_id = $topic_id");
        DB()->query("DELETE FROM " . BB_POLL_USERS . " WHERE topic_id = $topic_id");
        CACHE('bb_poll_data')->rm("poll_$topic_id");
    }
}
