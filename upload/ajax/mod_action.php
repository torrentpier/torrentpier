<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $userdata, $bb_cfg, $lang, $datastore;

$mode = (string) $this->request['mode'];

switch ($mode)
{
	case 'tor_status':
	    $topics = (string) $this->request['topic_ids'];
	    $status = (int) $this->request['status'];

	    // Валидность статуса
		if (!isset($lang['TOR_STATUS_NAME'][$status]))
		{
			$this->ajax_die($lang['STATUS_DOES_EXIST'] . $new_status);
		}

		$topic_ids = DB()->fetch_rowset("SELECT attach_id FROM ". BB_BT_TORRENTS ." WHERE topic_id IN($topics)", 'attach_id');

		foreach($topic_ids as $attach_id)
		{
			change_tor_status($attach_id, $status);
		}
		$this->response['status'] = $bb_cfg['tor_icons'][$status];
		$this->response['topics'] = explode(',', $topics);
		break;

	case 'edit_topic_title':
        $topic_id    = (int) $this->request['topic_id'];
		$topic_title = (string) $this->request['topic_title'];
		$new_title   = clean_title($topic_title);

		if (!$topic_id) $this->ajax_die($lang['INVALID_TOPIC_ID']);
		if ($new_title == '') $this->ajax_die($lang['DONT_MESSAGE_TITLE']);

		if (!$t_data = DB()->fetch_row("SELECT forum_id FROM ". BB_TOPICS ." WHERE topic_id = $topic_id LIMIT 1"))
		{
			$this->ajax_die($lang['INVALID_TOPIC_ID_DB']);
		}
		$this->verify_mod_rights($t_data['forum_id']);

		$topic_title_sql = DB()->escape($new_title);

		DB()->query("UPDATE ". BB_TOPICS ." SET topic_title = '$topic_title_sql' WHERE topic_id = $topic_id LIMIT 1");

        //Обновление кеша новостей на главной
		$news_forums = array_flip(explode(',', $bb_cfg['latest_news_forum_id']));
		if(isset($news_forums[$t_data['forum_id']]) && $bb_cfg['show_latest_news'])
		{
			$datastore->enqueue('latest_news');
			$datastore->update('latest_news');		}
		
		$net_forums = array_flip(explode(',', $bb_cfg['network_news_forum_id']));
		if(isset($net_forums[$t_data['forum_id']]) && $bb_cfg['show_network_news'])
		{
			$datastore->enqueue('network_news');
			$datastore->update('network_news');
		}

        $this->response['topic_id'] = $topic_id;
		$this->response['topic_title'] = $new_title;
		break;
}
