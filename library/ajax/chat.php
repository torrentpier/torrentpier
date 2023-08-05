<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $bb_cfg, $userdata, $lang;

$mode = (string)$this->request['mode'];

function parse_username($username)
{
    $username = $username[1];
    $row = DB()->fetch_row("SELECT user_id, user_rank FROM " . BB_USERS . " WHERE username = '" . DB()->escape($username) . "'");
    if ($row) $username = '<a href="' . PROFILE_URL . $row['user_id'] . '">' . $username . '</a>';
    return $username;
}

switch ($mode) {
    case 'select':
        $id = (int)@$this->request['id'];
        $up = (int)@$this->request['up'];

        $max_id = (int)DB()->fetch_row('SELECT MAX(id) as max_id FROM bb_chat', 'max_id');

        if (!$id) $id = $max_id - $bb_cfg['chat_message'];
        if ($id < 0) $id = 0;

        $message = '';
        if ($id <= $max_id) {
            if (!$sql = CACHE('bb_cache')->get('chat')) {
                $sql = DB()->fetch_rowset("SELECT c.*, u.username, u.user_rank, u.avatar_ext_id, u.user_opt
					FROM bb_chat c
					LEFT JOIN " . BB_USERS . " u ON(u.user_id = c.user_id)
					ORDER BY c.id DESC
					LIMIT {$bb_cfg['chat_message']}");
                CACHE('bb_cache')->set('chat', $sql);
            }

            foreach ($sql as $row) {
                if (($row['id'] > $id) && ($row['id'] <= $max_id)) {
                    $message .= '<div class="row1 chat-comment" id="pp_' . $row['id'] . '"><div style="min-height: 32px;">';
                    $message .= ($row['user_id'] == GUEST_UID) ? '' : '<a href="' . make_url(PROFILE_URL . $row['user_id']) . '">';
                    $message .= str_replace('<img', '<img align="left" height="32" width="32" style="padding-right: 3px;"', get_avatar($row['user_id'], $row['avatar_ext_id'], !bf($row['user_opt'], 'user_opt', 'dis_avatar')));
                    $message .= ($row['user_id'] == GUEST_UID) ? '' : '</a>';
                    if (IS_AM) {
                        $message .= '<input onclick="set_hid_chbox(' . $row['id'] . ');" class="floatR chat-post" type="checkbox" value="' . $row['id'] . '" />';
                        $message .= '<span onclick="edit_comment(' . $row['id'] . '); return false;" class="txtb floatR">[p]</span>';
                    }
                    $title_ip = (IS_ADMIN) ? decode_ip($row['ip']) : 'Ник в чат';
                    $message .= '<a href="#" class="bold" title="' . $title_ip . '" onclick="add_nick(\'[n]' . $row['username'] . '[/n]\'); return false;">' . str_replace('title="', 'data="', profile_url(array('username' => $row['username'], 'user_rank' => $row['user_rank']))) . '</a><div class="small">' . bb_date($row['time']) . '</div></div>';
                    $message .= '<div class="spacer_2"></div><span style="font-size: 11px;">' . $row['text_html'] . '</span></div>';
                    $message .= (IS_AM) ? '<span id="pe_' . $row['id'] . '"></span>' : '';
                }
            }

            $this->response['up'] = $up;
            $this->response['id'] = (int)@$sql[0]['id'];
            $this->response['message'] = $message;
        }
        break;

    case 'insert':
        if (IS_GUEST) $this->ajax_die('Вы должны авторизоваться на трекере');
        $message = $this->request['message'];
        if (!trim($message)) $this->ajax_die('Нахер постить пустое сообщение?');

        $row = DB()->fetch_row("SELECT * FROM bb_chat ORDER BY id DESC");
        if (!is_null($row)) {
            if ($row['text'] == $message) $this->ajax_die('Дабл пост.');
            if ($row['user_id'] == $userdata['user_id']) {
                if ($row['time'] > (TIMENOW - 5)) $this->ajax_die('Тайм флуд.');
            }
        }

        $text_html = bbcode2html(prepare_message($message));
        $text_html = preg_replace_callback("/\[n\](.*?)\[\/n\]/siu", 'parse_username', $text_html);

        DB()->query("INSERT INTO bb_chat (user_id, time, text, text_html, ip) VALUES ('{$userdata['user_id']}', '" . TIMENOW . "', '" . DB()->escape($message) . "', '" . DB()->escape($text_html) . "', '" . USER_IP . "')");

        $sql = DB()->fetch_rowset("SELECT c.*, u.username, u.user_rank, u.avatar_ext_id, u.user_opt
			FROM bb_chat c
			LEFT JOIN " . BB_USERS . " u ON(u.user_id = c.user_id)
			ORDER BY c.id DESC
			LIMIT {$bb_cfg['chat_message']}");
        CACHE('bb_cache')->set('chat', $sql);

        $this->response['id'] = (int)@$sql[0]['id'];
        $this->response['clear'] = true;
        break;

    case 'delete':
        if (!IS_AM) $this->ajax_die($lang['ONLY_FOR_MOD']);
        $ids = (string)$this->request['ids'];

        DB()->query("DELETE FROM bb_chat WHERE id IN($ids)");

        $sql = DB()->fetch_rowset("SELECT c.*, u.username, u.user_rank, u.avatar_ext_id, u.user_opt
			FROM bb_chat c
			LEFT JOIN " . BB_USERS . " u ON(u.user_id = c.user_id)
			ORDER BY c.id DESC
			LIMIT {$bb_cfg['chat_message']}");
        CACHE('bb_cache')->set('chat', $sql);

        $this->response['id'] = (int)@$sql[0]['id'];
        $this->response['del'] = explode(',', $ids);
        break;

    case 'edit':
        $post_id = (int)$this->request['post_id'];
        $text = (string)@$this->request['text'];
        $type = (int)@$this->request['type'];

        $row = DB()->fetch_row("SELECT
			c.*, u.username, u.user_rank, u.avatar_ext_id, u.user_opt
			FROM bb_chat c
			LEFT JOIN " . BB_USERS . " u ON(u.user_id = c.user_id)
			WHERE c.id = $post_id LIMIT 1");
        if (!$row) $this->ajax_die('Нет поста');

        if ($type) {
            if (mb_strlen($text) > 2) {
                $text_html = bbcode2html(prepare_message($text));
                $text_html = preg_replace_callback("/\[n\](.*?)\[\/n\]/siu", 'parse_username', $text_html);

                if ($text != $row['text']) {
                    DB()->query("UPDATE bb_chat SET text = '" . DB()->escape($text) . "', text_html = '" . DB()->escape($text_html) . "' WHERE id = $post_id LIMIT 1");
                    $sql = DB()->fetch_rowset("SELECT c.*, u.username, u.user_rank, u.avatar_ext_id, u.user_opt
						FROM bb_chat c
						LEFT JOIN " . BB_USERS . " u ON(u.user_id = c.user_id)
						ORDER BY c.id DESC
						LIMIT {$bb_cfg['chat_message']}");
                    CACHE('bb_cache')->set('chat', $sql);
                }
            } else $this->ajax_die('Слишком короткое сообщение.');

            $message = '<div style="min-height: 32px;">';
            $message .= ($row['user_id'] == GUEST_UID) ? '' : '<a href="' . make_url(PROFILE_URL . $row['user_id']) . '">';
            $message .= str_replace('<img', '<img align="left" height="32" width="32" style="padding-right: 3px;"', get_avatar($row['user_id'], $row['avatar_ext_id'], !bf($row['user_opt'], 'user_opt', 'dis_avatar')));
            $message .= ($row['user_id'] == GUEST_UID) ? '' : '</a>';
            if (IS_AM) {
                $message .= '<input onclick="set_hid_chbox(' . $row['id'] . ');" class="floatR chat-post" type="checkbox" value="' . $row['id'] . '" />';
                $message .= '<span onclick="edit_comment(' . $row['id'] . '); return false;" class="txtb floatR">[p]</span>';
            }
            $title_ip = (IS_ADMIN) ? decode_ip($row['ip']) : 'Ник в чат';
            $message .= '<a href="#" class="bold" title="' . $title_ip . '" onclick="add_nick(\'[n]' . $row['username'] . '[/n]\'); return false;">' . str_replace('title="', 'data="', profile_url(array('username' => $row['username'], 'user_rank' => $row['user_rank']))) . '</a><div class="small">' . bb_date($row['time']) . '</div></div>';
            $message .= '<div class="spacer_2"></div><span style="font-size: 11px;">' . $text_html . '</span>';

            $this->response['html'] = $message;
        } else {
            $this->response['text'] = '<div class="tCenter chat-comment med"><textarea style="height: 60px;" class="chat_message w90" id="text-' . $post_id . '">' . $row['text'] . '</textarea>
				<a href="#" onclick="edit_comment(' . $post_id . ', $(\'#text-' . $post_id . '\').val(), 1); return false;"><span class="adm">изменить</span></a> &middot;
				<a href="#" onclick="edit_comment(' . $post_id . '); return false;">отмена</a>
				</div>';
        }

        $this->response['post_id'] = $post_id;
        break;
}
