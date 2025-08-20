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
            global $lang;
            return $this->err_msg = $lang['EMPTY_POLL_TITLE'];
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
            global $lang;
            return $this->err_msg = sprintf($lang['NEW_POLL_VOTES'], $this->max_votes);
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
        $sql_args = DB()->build_array('MULTI_INSERT', $sql_ary);

        DB()->query("REPLACE INTO " . BB_POLL_VOTES . $sql_args);

        DB()->query("UPDATE " . BB_TOPICS . " SET topic_vote = " . POLL_STARTED . " WHERE topic_id = $topic_id");
    }

    /**
     * Remove poll
     *
     * @param int $topic_id
     */
    public function delete_poll($topic_id)
    {
        DB()->query("UPDATE " . BB_TOPICS . " SET topic_vote = " . POLL_DELETED . " WHERE topic_id = $topic_id");
        $this->delete_votes_data($topic_id);
    }

    /**
     * Remove info about voters and their choices
     *
     * @param int $topic_id
     */
    public function delete_votes_data($topic_id)
    {
        DB()->query("DELETE FROM " . BB_POLL_VOTES . " WHERE topic_id = $topic_id");
        DB()->query("DELETE FROM " . BB_POLL_USERS . " WHERE topic_id = $topic_id");
        CACHE('bb_poll_data')->rm("poll_$topic_id");
    }

    /**
     * Get poll items
     *
     * @param $topic_id
     * @return array|false|mixed|string
     * @throws \JsonException
     */
    public static function get_poll_data_items_js($topic_id)
    {
        if (!$topic_id_csv = get_id_csv($topic_id)) {
            return is_array($topic_id) ? [] : false;
        }
        $items = [];

        if (!$poll_data = CACHE('bb_poll_data')->get("poll_$topic_id")) {
            $poll_data = DB()->fetch_rowset("
			SELECT topic_id, vote_id, vote_text, vote_result
			FROM " . BB_POLL_VOTES . "
			WHERE topic_id IN($topic_id_csv)
			ORDER BY topic_id, vote_id
		");
            CACHE('bb_poll_data')->set("poll_$topic_id", $poll_data);
        }

        foreach ($poll_data as $row) {
            $opt_text_for_js = htmlCHR($row['vote_text']);
            $opt_result_for_js = (int)$row['vote_result'];

            $items[$row['topic_id']][$row['vote_id']] = [$opt_text_for_js, $opt_result_for_js];
        }
        foreach ($items as $k => $v) {
            $items[$k] = json_encode($v, JSON_THROW_ON_ERROR);
        }

        return is_array($topic_id) ? $items : $items[$topic_id];
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
        return (bool)DB()->fetch_row("SELECT 1 FROM " . BB_POLL_USERS . " WHERE topic_id = $topic_id AND user_id = $user_id LIMIT 1");
    }

    /**
     * Check whether poll is active
     *
     * @param array $t_data
     * @return bool
     */
    public static function pollIsActive(array $t_data): bool
    {
        global $bb_cfg;
        return ($t_data['topic_vote'] == POLL_STARTED && $t_data['topic_time'] > TIMENOW - $bb_cfg['poll_max_days'] * 86400);
    }
}
