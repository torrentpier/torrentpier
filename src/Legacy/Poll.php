<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy;

use JsonException;

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
        $this->max_votes = config()->get('max_poll_options');
    }

    /**
     * Get poll items
     *
     * @param $topic_id
     * @throws JsonException
     * @return array|false|mixed|string
     */
    public static function get_poll_data_items_js($topic_id)
    {
        if (!$topic_id_csv = get_id_csv($topic_id)) {
            return \is_array($topic_id) ? [] : false;
        }
        $items = [];

        if (!$poll_data = CACHE('bb_poll_data')->get("poll_{$topic_id}")) {
            $poll_data = DB()->table(BB_POLL_VOTES)
                ->select('topic_id, vote_id, vote_text, vote_result')
                ->where('topic_id IN (?)', explode(',', $topic_id_csv))
                ->order('topic_id, vote_id')
                ->fetchAll();
            CACHE('bb_poll_data')->set("poll_{$topic_id}", $poll_data);
        }

        foreach ($poll_data as $row) {
            $opt_text_for_js = htmlCHR($row['vote_text']);
            $opt_result_for_js = (int)$row['vote_result'];

            $items[$row['topic_id']][$row['vote_id']] = [$opt_text_for_js, $opt_result_for_js];
        }
        foreach ($items as $k => $v) {
            $items[$k] = json_encode($v, JSON_THROW_ON_ERROR);
        }

        return \is_array($topic_id) ? $items : $items[$topic_id];
    }

    /**
     * Checks whether the user has voted in a poll
     *
     * @param int $topic_id
     * @param int $user_id
     * @return bool
     */
    public static function userIsAlreadyVoted(int $topic_id, int $user_id): bool
    {
        return (bool)DB()->table(BB_POLL_USERS)
            ->where('topic_id', $topic_id)
            ->where('user_id', $user_id)
            ->fetch();
    }

    /**
     * Check whether poll is active
     *
     * @param array $t_data
     * @return bool
     */
    public static function pollIsActive(array $t_data): bool
    {
        return $t_data['topic_vote'] == POLL_STARTED && $t_data['topic_time'] > TIMENOW - config()->get('poll_max_days') * 86400;
    }

    /**
     * Forming poll results
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
            return $this->err_msg = __('EMPTY_POLL_TITLE');
        }
        $this->poll_votes[] = $poll_caption; // header is vote_id = 0

        foreach (explode("\n", $poll_votes) as $vote) {
            if (!$vote = str_compact($vote)) {
                continue;
            }
            $this->poll_votes[] = $vote;
        }

        // check for "< 3" -- 2 answer variants + header
        if (\count($this->poll_votes) < 3 || \count($this->poll_votes) > $this->max_votes + 1) {
            return $this->err_msg = \sprintf(__('NEW_POLL_VOTES'), $this->max_votes);
        }
    }

    /**
     * Recording poll info to the database
     *
     * @param int $topic_id
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
        // Delete existing poll data first, then insert new data
        foreach ($sql_ary as $poll_vote) {
            DB()->table(BB_POLL_VOTES)->insert($poll_vote);
        }

        DB()->table(BB_TOPICS)
            ->where('topic_id', $topic_id)
            ->update(['topic_vote' => POLL_STARTED]);
    }

    /**
     * Remove poll
     *
     * @param int $topic_id
     */
    public function delete_poll($topic_id)
    {
        DB()->table(BB_TOPICS)
            ->where('topic_id', $topic_id)
            ->update(['topic_vote' => POLL_DELETED]);
        $this->delete_votes_data($topic_id);
    }

    /**
     * Remove info about voters and their choices
     *
     * @param int $topic_id
     */
    public function delete_votes_data($topic_id)
    {
        DB()->table(BB_POLL_VOTES)
            ->where('topic_id', $topic_id)
            ->delete();
        DB()->table(BB_POLL_USERS)
            ->where('topic_id', $topic_id)
            ->delete();
        CACHE('bb_poll_data')->rm("poll_{$topic_id}");
    }
}
