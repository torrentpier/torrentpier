<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $userdata, $lang;

if (!$mode = (string)$this->request['mode']) {
    $this->ajax_die('invalid mode (empty)');
}

switch ($mode) {
    case 'clear_cache':
        foreach (config()->get('cache.engines') as $cache_name => $cache_val) {
            CACHE($cache_name)->rm();
        }

        $this->response['cache_html'] = '<span class="seed bold">' . $lang['ALL_CACHE_CLEARED'] . '</span>';
        break;
    case 'clear_datastore':
        global $datastore;

        $datastore->clean();

        $this->response['datastore_html'] = '<span class="seed bold">' . $lang['DATASTORE_CLEARED'] . '</span>';
        break;
    case 'clear_template_cache':
        global $template;

        $match = XS_TPL_PREFIX;
        $dir = $template->cachedir;
        $res = @opendir($dir);
        while (($file = readdir($res)) !== false) {
            if (str_starts_with($file, $match)) {
                @unlink($dir . $file);
            }
        }
        closedir($res);

        $this->response['template_cache_html'] = '<span class="seed bold">' . $lang['ALL_TEMPLATE_CLEARED'] . '</span>';
        break;
    case 'indexer':
        $manticore = getManticoreSearch();
        if ($manticore->initialLoad()) {
            $this->response['indexer_html'] = '<span class="seed bold">' . $lang['INDEXER_SUCCESS'] . '</span>';
        } else {
            $this->response['indexer_html'] = '<span class="leech bold">' . $lang['ERROR'] . '</span>';
        }
        break;
    case 'update_user_level':
        \TorrentPier\Legacy\Group::update_user_level('all');
        $this->response['update_user_level_html'] = '<span class="seed bold">' . $lang['USER_LEVELS_UPDATED'] . '</span>';
        break;
    case 'sync_topics':
        \TorrentPier\Legacy\Admin\Common::sync('topic', 'all');
        \TorrentPier\Legacy\Admin\Common::sync_all_forums();
        $this->response['sync_topics_html'] = '<span class="seed bold">' . $lang['TOPICS_DATA_SYNCHRONIZED'] . '</span>';
        break;
    case 'sync_user_posts':
        \TorrentPier\Legacy\Admin\Common::sync('user_posts', 'all');
        $this->response['sync_user_posts_html'] = '<span class="seed bold">' . $lang['USER_POSTS_COUNT_SYNCHRONIZED'] . '</span>';
        break;
    case 'unlock_cron':
        \TorrentPier\Helpers\CronHelper::enableBoard();
        $this->response['unlock_cron_html'] = '<span class="seed bold">' . $lang['ADMIN_UNLOCKED'] . '</span>';
        break;
    default:
        $this->ajax_die('Invalid mode: ' . $mode);
}

$this->response['mode'] = $mode;
