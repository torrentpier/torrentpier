<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy;

/**
 * Class LogAction
 * @package TorrentPier\Legacy
 */
class LogAction
{
    public $log_type = [
        'mod_topic_delete' => 1,
        'mod_topic_move' => 2,
        'mod_topic_lock' => 3,
        'mod_topic_unlock' => 4,
        'mod_post_delete' => 5,
        'mod_topic_split' => 6,
        'adm_user_delete' => 7,
        'adm_user_ban' => 8,
        'adm_user_unban' => 9,
    ];
    public $log_type_select = [];
    public $log_disabled = false;

    public function init()
    {
        global $lang, $bb_cfg;

        foreach ($lang['LOG_ACTION']['LOG_TYPE'] as $log_type => $log_desc) {
            $this->log_type_select[strip_tags($log_desc)] = $this->log_type[$log_type];
        }
    }

    /**
     * @param $type_name
     * @param array $args
     */
    public function mod($type_name, array $args = [])
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
     * @param $type_name
     * @param array $args
     */
    public function admin($type_name, array $args = [])
    {
        $this->mod($type_name, $args);
    }
}
