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

global $userdata, $lang, $bb_cfg;

$mode = (string)$this->request['mode'];

switch ($mode) {
    case 'clear_cache':

        clean_cache();

        $this->response['cache_html'] = '<span class="seed bold">' . $lang['ALL_CACHE_CLEARED'] . '</span>';

        break;

    case 'clear_datastore':

        clean_datastore();

        $this->response['datastore_html'] = '<span class="seed bold">' . $lang['DATASTORE_CLEARED'] . '</span>';

        break;

    case 'clear_template_cache':

        clean_tpl_cache();

        $this->response['template_cache_html'] = '<span class="seed bold">' . $lang['ALL_TEMPLATE_CLEARED'] . '</span>';

        break;

    case 'indexer':

        exec("indexer --config {$bb_cfg['sphinx_config_path']} --all --rotate", $result);

        if (!is_file($bb_cfg['sphinx_config_path'] . ".log")) {
            file_put_contents($bb_cfg['sphinx_config_path'] . ".log", "####Logger from dimka3210.####" . date("H:i:s", TIMENOW) . "##############################\r\n\r\n\r\n\r\n", FILE_APPEND);
        }

        file_put_contents($bb_cfg['sphinx_config_path'] . ".log", "##############################" . date("H:i:s", TIMENOW) . "##############################\r\n", FILE_APPEND);

        foreach ($result as $row) {
            file_put_contents($bb_cfg['sphinx_config_path'] . ".log", $row . "\r\n", FILE_APPEND);
        }

        file_put_contents($bb_cfg['sphinx_config_path'] . ".log", "\r\n", FILE_APPEND);
        file_put_contents($bb_cfg['sphinx_config_path'] . ".log", "\r\n", FILE_APPEND);

        $this->response['indexer_html'] = '<span class="seed bold">' . $lang['INDEXER'] . '</span>';

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
}

$this->response['mode'] = $mode;
