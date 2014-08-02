<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $userdata, $lang, $bb_cfg;

$mode = (string) $this->request['mode'];

switch ($mode)
{
	case 'clear_cache':

		foreach ($bb_cfg['cache']['engines'] as $cache_name => $cache_val)
		{
			if (!in_array('db_sqlite', $cache_val))
			{
				CACHE($cache_name)->rm();
			}
		}

		$this->response['cache_html'] = '<span class="seed bold">'. $lang['ALL_CACHE_CLEARED'] .'</span>';

	break;

	case 'clear_datastore':

		global $datastore;

		$datastore->clean();

		$this->response['datastore_html'] = '<span class="seed bold">'. $lang['DATASTORE_CLEARED'] .'</span>';

	break;

	case 'clear_template_cache':

		global $template;

		$match = 'tpl_';
		$match_len = strlen($match);
		$dir = $template->cachedir;
		$res = @opendir($dir);
		while (($file = readdir($res)) !== false)
		{
			if (substr($file, 0, $match_len) === $match)
			{
				@unlink($dir . $file);
			}
		}
		closedir($res);

		$this->response['template_cache_html'] = '<span class="seed bold">'. $lang['ALL_TEMPLATE_CLEARED'] .'</span>';

	break;

	case 'indexer':

		exec("indexer --config {$bb_cfg['sphinx_config_path']} --all --rotate", $result);

		if (!is_file($bb_cfg['sphinx_config_path'].".log"))
		{
			file_put_contents($bb_cfg['sphinx_config_path'].".log", "####Logger from dimka3210.####".date("H:i:s", TIMENOW)."##############################\r\n\r\n\r\n\r\n", FILE_APPEND);
		}

		file_put_contents($bb_cfg['sphinx_config_path'].".log", "##############################".date("H:i:s", TIMENOW)."##############################\r\n", FILE_APPEND);

		foreach ($result as $row)
		{
			file_put_contents($bb_cfg['sphinx_config_path'].".log", $row."\r\n", FILE_APPEND);
		}

		file_put_contents($bb_cfg['sphinx_config_path'].".log", "\r\n", FILE_APPEND);
		file_put_contents($bb_cfg['sphinx_config_path'].".log", "\r\n", FILE_APPEND);

		$this->response['indexer_html'] = '<span class="seed bold">'. $lang['INDEXER'] .'</span>';

	break;

	case 'update_user_level':

		require(INC_DIR .'functions_group.php');

		update_user_level('all');

		$this->response['update_user_level_html'] = '<span class="seed bold">'. $lang['USER_LEVELS_UPDATED'] .'</span>';

	break;

	case 'sync_topics':

		sync('topic', 'all');
		sync_all_forums();

		$this->response['sync_topics_html'] = '<span class="seed bold">'. $lang['TOPICS_DATA_SYNCHRONIZED'] .'</span>';

	break;

	case 'sync_user_posts':

		sync('user_posts', 'all');

		$this->response['sync_user_posts_html'] = '<span class="seed bold">'. $lang['USER POSTS COUNT SYNCHRONIZED'] .'</span>';

	break;
}

$this->response['mode'] = $mode;