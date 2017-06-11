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
            'log_type_id' => (int)$this->log_type["$type_name"],
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
