<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine.
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 *
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 *
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */
if (!defined('IN_AJAX')) {
    exit(basename(__FILE__));
}

global $userdata, $lang, $datastore, $log_action;

if (!$mode = (string) $this->request['mode']) {
    $this->ajax_die('invalid mode (empty)');
}

switch ($mode) {
    case 'tor_status':
        $topics = (string) $this->request['topic_ids'];
        $status = (int) $this->request['status'];

        // Check status validity
        if (!isset($lang['TOR_STATUS_NAME'][$status])) {
            $this->ajax_die($lang['TOR_STATUS_FAILED']);
        }

        $topic_ids = DB()->fetch_rowset('SELECT attach_id FROM '.BB_BT_TORRENTS." WHERE topic_id IN($topics)", 'attach_id');

        foreach ($topic_ids as $attach_id) {
            $tor = DB()->fetch_row('
                SELECT
                    tor.forum_id, tor.topic_id, t.topic_title, tor.tor_status
                FROM       '.BB_BT_TORRENTS.' tor
                INNER JOIN '.BB_TOPICS." t ON(t.topic_id = tor.topic_id)
                WHERE tor.attach_id = $attach_id LIMIT 1");

            if (!$tor) {
                $this->ajax_die($lang['TORRENT_FAILED']);
            }

            \TorrentPier\Legacy\Torrent::change_tor_status($attach_id, $status);

            // Log action
            $log_msg = sprintf($lang['TOR_STATUS_LOG_ACTION'], config()->get('tor_icons')[$status].' <b> '.$lang['TOR_STATUS_NAME'][$status].'</b>', config()->get('tor_icons')[$tor['tor_status']].' <b> '.$lang['TOR_STATUS_NAME'][$tor['tor_status']].'</b>');
            $log_action->mod('mod_topic_change_tor_status', [
                'forum_id'    => $tor['forum_id'],
                'topic_id'    => $tor['topic_id'],
                'topic_title' => $tor['topic_title'],
                'log_msg'     => $log_msg.'<br/>-------------',
            ]);
        }
        $this->response['status'] = config()->get('tor_icons')[$status];
        $this->response['topics'] = explode(',', $topics);
        break;

    case 'edit_topic_title':
        $topic_id = (int) $this->request['topic_id'];
        $old_title = get_topic_title($topic_id);
        $new_title = clean_title((string) $this->request['topic_title']);

        if (!$topic_id) {
            $this->ajax_die($lang['INVALID_TOPIC_ID']);
        }
        if ($new_title == '') {
            $this->ajax_die($lang['DONT_MESSAGE_TITLE']);
        }

        if (!$t_data = DB()->fetch_row('SELECT forum_id FROM '.BB_TOPICS." WHERE topic_id = $topic_id LIMIT 1")) {
            $this->ajax_die($lang['INVALID_TOPIC_ID_DB']);
        }
        $this->verify_mod_rights($t_data['forum_id']);

        $topic_title_sql = DB()->escape($new_title);

        DB()->query('UPDATE '.BB_TOPICS." SET topic_title = '$topic_title_sql' WHERE topic_id = $topic_id LIMIT 1");

        // Update the news cache on the index page
        $news_forums = array_flip(explode(',', config()->get('latest_news_forum_id')));
        if (isset($news_forums[$t_data['forum_id']]) && config()->get('show_latest_news')) {
            $datastore->enqueue([
                'latest_news',
            ]);
            $datastore->update('latest_news');
        }

        $net_forums = array_flip(explode(',', config()->get('network_news_forum_id')));
        if (isset($net_forums[$t_data['forum_id']]) && config()->get('show_network_news')) {
            $datastore->enqueue([
                'network_news',
            ]);
            $datastore->update('network_news');
        }

        // Log action
        $log_action->mod('mod_topic_renamed', [
            'forum_id'        => $t_data['forum_id'],
            'topic_id'        => $topic_id,
            'topic_id_new'    => $topic_id,
            'topic_title'     => $old_title,
            'topic_title_new' => $new_title,
        ]);

        $this->response['topic_id'] = $topic_id;
        $this->response['topic_title'] = $new_title;
        break;

    case 'profile_ip':
        $user_id = (int) $this->request['user_id'];
        $profiledata = get_userdata($user_id);

        if (!$user_id) {
            $this->ajax_die($lang['NO_USER_ID_SPECIFIED']);
        }

        $reg_ip = DB()->fetch_rowset('SELECT username, user_id, user_rank FROM '.BB_USERS."
			WHERE user_reg_ip = '{$profiledata['user_reg_ip']}'
				AND user_reg_ip != 0
				AND user_id != {$profiledata['user_id']}
			ORDER BY username ASC");

        $last_ip = DB()->fetch_rowset('SELECT username, user_id, user_rank FROM '.BB_USERS."
			WHERE user_last_ip = '{$profiledata['user_last_ip']}'
				AND user_last_ip != 0
				AND user_id != {$profiledata['user_id']}");

        $link_reg_ip = $link_last_ip = '';

        if (!empty($reg_ip)) {
            $link_reg_ip .= $lang['OTHER_IP'].'&nbsp';
            foreach ($reg_ip as $row) {
                $link_reg_ip .= profile_url($row).', ';
            }
            $link_reg_ip = rtrim($link_reg_ip, ', ');
        }

        if (!empty($last_ip)) {
            $link_last_ip .= $lang['OTHER_IP'].'&nbsp';
            foreach ($last_ip as $row) {
                $link_last_ip .= profile_url($row).', ';
            }
            $link_last_ip = rtrim($link_last_ip, ', ');
        }

        if ($profiledata['user_level'] == ADMIN && !IS_ADMIN) {
            $reg_ip = $last_ip = $lang['HIDDEN'];
        } elseif ($profiledata['user_level'] == MOD && !IS_AM) {
            $reg_ip = $last_ip = $lang['HIDDEN'];
        } else {
            $user_reg_ip = \TorrentPier\Helpers\IPHelper::long2ip_extended($profiledata['user_reg_ip']);
            $user_last_ip = \TorrentPier\Helpers\IPHelper::long2ip_extended($profiledata['user_last_ip']);
            $reg_ip = '<a href="'.config()->get('whois_info').$user_reg_ip.'" class="gen" target="_blank">'.$user_reg_ip.'</a>';
            $last_ip = '<a href="'.config()->get('whois_info').$user_last_ip.'" class="gen" target="_blank">'.$user_last_ip.'</a>';
        }

        $this->response['ip_list_html'] = '
			<br /><table class="mod_ip bCenter borderless" cellspacing="1">
				<tr class="row5" >
					<td>'.$lang['REG_IP'].'</td>
					<td class="tCenter">'.$reg_ip.'</td>
					<td><div>'.$link_reg_ip.'</div></td>
				</tr>
				<tr class="row4">
					<td>'.$lang['LAST_IP'].'</td>
					<td class="tCenter">'.$last_ip.'</td>
					<td><div>'.$link_last_ip.'</div></td>
				</tr>
			</table><br />
		';
        break;

    default:
        $this->ajax_die('Invalid mode: '.$mode);
}
