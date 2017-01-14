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

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $userdata;

if (!IS_SUPER_ADMIN) {
    $this->ajax_die('not auth');
}

array_deep($this->request, 'trim');

$mode = (string)$this->request['mode'];
$sql_error = false;

// установка / начальная валидация значений
switch ($mode) {
    case 'load':
    case 'save':
        if (!$tpl_id = (int)$this->request['tpl_id']) {
            $this->ajax_die('Выбранный шаблон не найден, создайте новый (empty tpl_id)');
        }
        if (!$tpl_data = DB()->fetch_row("SELECT * FROM " . BB_TOPIC_TPL . " WHERE tpl_id = $tpl_id LIMIT 1")) {
            $this->ajax_die("Шаблон [id: $tpl_id] не найден в БД");
        }
        break;
}
switch ($mode) {
    case 'save':
    case 'new':
        if (!$tpl_name = htmlCHR(str_compact($this->request['tpl_name']))) {
            $this->ajax_die('не заполнено название шаблона');
        }
        $tpl_name = substr($tpl_name, 0, 60);

        if (!$tpl_src_form = htmlCHR($this->request['tpl_src_form'])) {
            $this->ajax_die('не заполнен скрипт формы шаблона');
        }
        if (!$tpl_src_title = htmlCHR($this->request['tpl_src_title'])) {
            $this->ajax_die('не заполнен формат названия темы');
        }
        $tpl_src_title = str_compact($tpl_src_title);

        if (!$tpl_src_msg = htmlCHR($this->request['tpl_src_msg'])) {
            $this->ajax_die('не заполнен формат создания сообщения');
        }

        $tpl_comment = htmlCHR($this->request['tpl_comment']);

        preg_match('#\d+#', (string)$this->request['tpl_rules'], $m);
        $tpl_rules_post_id = isset($m[0]) ? (int)$m[0] : 0;

        $sql_args = array(
            'tpl_name' => (string)$tpl_name,
            'tpl_src_form' => (string)$tpl_src_form,
            'tpl_src_title' => (string)$tpl_src_title,
            'tpl_src_msg' => (string)$tpl_src_msg,
            'tpl_comment' => (string)$tpl_comment,
            'tpl_rules_post_id' => (int)$tpl_rules_post_id,
            'tpl_last_edit_tm' => (int)TIMENOW,
            'tpl_last_edit_by' => (int)$userdata['user_id'],
        );
        break;
}
// выполнение
switch ($mode) {
    // загрузка шаблона
    case 'load':
        $this->response['val']['tpl-name-save'] = $tpl_data['tpl_name'];
        $this->response['val']['tpl-src-form'] = $tpl_data['tpl_src_form'];
        $this->response['val']['tpl-src-title'] = $tpl_data['tpl_src_title'];
        $this->response['val']['tpl-src-msg'] = $tpl_data['tpl_src_msg'];
        $this->response['val']['tpl-comment-save'] = $tpl_data['tpl_comment'];
        $this->response['val']['tpl-rules-save'] = $tpl_data['tpl_rules_post_id'];
        array_deep($this->response['val'], 'html_ent_decode');

        $this->response['val']['tpl-id-save'] = $tpl_id;
        $this->response['val']['tpl-last-edit-tst'] = $tpl_data['tpl_last_edit_tm'];
        $this->response['html']['tpl-name-old-save'] = $tpl_data['tpl_name'];
        $this->response['html']['tpl-last-edit-time'] = bb_date($tpl_data['tpl_last_edit_tm'], 'd-M-y H:i');
        $this->response['html']['tpl-last-edit-by'] = get_username(intval($tpl_data['tpl_last_edit_by']));

        $this->response['tpl_rules_href'] = POST_URL . $tpl_data['tpl_rules_post_id'] . '#' . $tpl_data['tpl_rules_post_id'];
        break;

    // включение / отключение шаблона в форуме
    case 'assign':
        if (!$tpl_id = (int)$this->request['tpl_id']) {
            $this->ajax_die('Выбранный шаблон не найден, создайте новый (empty tpl_id)');
        }
        if (!$forum_id = (int)$this->request['forum_id']) {
            $this->ajax_die('empty forum_id');
        }
        if (!forum_exists($forum_id)) {
            $this->ajax_die("нет такого форума [id: $forum_id]");
        }
        // отключение
        if ($tpl_id == -1) {
            $new_tpl_id = 0;
            $this->response['msg'] = 'Шаблоны в этом форуме отключены';
        } // включение
        else {
            if (!$tpl_name = DB()->fetch_row("SELECT tpl_name FROM " . BB_TOPIC_TPL . " WHERE tpl_id = $tpl_id LIMIT 1", 'tpl_name')) {
                $this->ajax_die("Шаблон [id: $tpl_id] не найден в БД");
            }
            $new_tpl_id = $tpl_id;
            $this->response['msg'] = "Включен шаблон $tpl_name";
        }
        DB()->query("UPDATE " . BB_FORUMS . " SET forum_tpl_id = $new_tpl_id WHERE forum_id = $forum_id LIMIT 1");
        break;

    // сохранение изменений
    case 'save':
        if ($tpl_data['tpl_last_edit_tm'] > $this->request['tpl_l_ed_tst'] && $tpl_data['tpl_last_edit_by'] != $userdata['user_id']) {
            $last_edit_by_username = get_username(intval($tpl_data['tpl_last_edit_by']));
            $msg = "Изменения не были сохранены!\n\n";
            $msg .= 'Шаблон был отредактирован: ' . html_entity_decode($last_edit_by_username) . ', ' . delta_time($tpl_data['tpl_last_edit_tm']) . " назад\n\n";
            $this->ajax_die($msg);
        }
        $sql = "UPDATE " . BB_TOPIC_TPL . " SET " . DB()->build_array('UPDATE', $sql_args) . " WHERE tpl_id = $tpl_id LIMIT 1";
        if (!DB()->query($sql)) {
            $sql_error = DB()->sql_error();
        }
        $this->response['tpl_id'] = $tpl_id;
        $this->response['tpl_name'] = $tpl_name;
        $this->response['html']['tpl-last-edit-time'] = bb_date(TIMENOW, 'd-M-y H:i');
        $this->response['html']['tpl-last-edit-by'] = $userdata['username'];
        break;

    // создание нового шаблона
    case 'new':
        $sql = "INSERT INTO " . BB_TOPIC_TPL . DB()->build_array('INSERT', $sql_args);
        if (!DB()->query($sql)) {
            $sql_error = DB()->sql_error();
        }
        break;

    // ошибочный $mode
    default:
        $this->ajax_die("invalid mode: $mode");
}

// возможный дубль названия шаблона
if ($sql_error) {
    if ($sql_error['code'] == 1062) {
        // Duplicate entry

        $this->ajax_die('Шаблон с таким названием уже существует, выберите другое название');
    }
    $this->ajax_die("db error {$sql_error['code']}: {$sql_error['message']}");
}

// выход
$this->response['mode'] = $mode;
$this->response['timestamp'] = TIMENOW;
