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
 * Class LogAction
 * Handles logging of moderator and administrator actions.
 *
 * @package TorrentPier\Legacy
 */
class LogAction
{
    /**
     * List of available log types (action name => ID).
     *
     * @var array<string,int>
     */
    public array $log_type = [
        'mod_topic_delete' => 1,
        'mod_topic_move' => 2,
        'mod_topic_lock' => 3,
        'mod_topic_unlock' => 4,
        'mod_post_delete' => 5,
        'mod_topic_split' => 6,
        'adm_user_delete' => 7,
        'adm_user_ban' => 8,
        'adm_user_unban' => 9,
        'mod_post_pin' => 10,
        'mod_post_unpin' => 11,
        'mod_topic_set_downloaded' => 12,
        'mod_topic_unset_downloaded' => 13,
        'mod_topic_renamed' => 14,
        'mod_topic_change_tor_status' => 15,
        'mod_topic_change_tor_type' => 16,
        'mod_topic_tor_unregister' => 17,
        'mod_topic_tor_register' => 18,
        'mod_topic_tor_delete' => 19,
        'mod_topic_poll_started' => 20,
        'mod_topic_poll_finished' => 21,
        'mod_topic_poll_deleted' => 22,
        'mod_topic_poll_added' => 23,
        'mod_topic_poll_edited' => 24
    ];

    /**
     * Log types prepared for select lists (description => ID).
     *
     * @var array<string,int>
     */
    public array $log_type_select = [];

    /**
     * Flag to disable logging.
     *
     * @var bool
     */
    public bool $log_disabled = false;

    /**
     * Initializes the log type select array using language definitions.
     *
     * @return void
     */
    public function init(): void
    {
        global $lang;

        foreach ($lang['LOG_ACTION']['LOG_TYPE'] as $log_type => $log_desc) {
            $this->log_type_select[strip_tags($log_desc)] = $this->log_type[$log_type];
        }
    }

    /**
     * Logs moderator actions.
     *
     * @param string $type_name Action type key (from $log_type).
     * @param array $args Action parameters:
     *                          - forum_id
     *                          - forum_id_new
     *                          - topic_id
     *                          - topic_id_new
     *                          - topic_title
     *                          - topic_title_new
     *                          - log_msg
     *
     * @return void
     */
    public function mod(string $type_name, array $args = []): void
    {
        global $userdata;

        if (empty($this->log_type)) {
            $this->init();
        }
        if ($this->log_disabled) {
            return;
        }

        $forum_id =& $args['forum_id'];
        $forum_id_new =& $args['forum_id_new'];
        $topic_id =& $args['topic_id'];
        $topic_id_new =& $args['topic_id_new'];
        $topic_title =& $args['topic_title'];
        $topic_title_new =& $args['topic_title_new'];
        $log_msg =& $args['log_msg'];

        if (!empty($userdata)) {
            $user_id = $userdata['user_id'];
            $session_ip = $userdata['session_ip'];
        } else {
            $user_id = '';
            $session_ip = '';
        }

        $sql_ary = [
            'log_type_id' => (int)$this->log_type[(string)$type_name],
            'log_user_id' => (int)$user_id,
            'log_user_ip' => (string)$session_ip,
            'log_forum_id' => (int)$forum_id,
            'log_forum_id_new' => (int)$forum_id_new,
            'log_topic_id' => (int)$topic_id,
            'log_topic_id_new' => (int)$topic_id_new,
            'log_topic_title' => (string)$topic_title,
            'log_topic_title_new' => (string)$topic_title_new,
            'log_time' => (int)TIMENOW,
            'log_msg' => (string)$log_msg,
        ];
        $sql_args = DB()->build_array('INSERT', $sql_ary);

        DB()->query("INSERT INTO " . BB_LOG . " $sql_args");
    }

    /**
     * Logs administrator actions (wrapper for mod()).
     *
     * @param string $type_name Action type key (from $log_type).
     * @param array $args Action parameters (same as mod()).
     *
     * @return void
     */
    public function admin(string $type_name, array $args = []): void
    {
        $this->mod($type_name, $args);
    }
}
