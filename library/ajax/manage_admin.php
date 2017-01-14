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

global $userdata, $lang;

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

/** @var \TorrentPier\Cache\Adapter $cache */
$cache = $di->cache;

$mode = (string)$this->request['mode'];

switch ($mode) {
    case 'clear_cache':

        $cache->flush();

        $this->response['cache_html'] = '<span class="seed bold">' . $lang['ALL_CACHE_CLEARED'] . '</span>';

        break;

    case 'clear_datastore':

        global $datastore;

        $datastore->clean();

        $this->response['datastore_html'] = '<span class="seed bold">' . $lang['DATASTORE_CLEARED'] . '</span>';

        break;

    case 'clear_template_cache':

        global $template;

        $match = 'tpl_';
        $match_len = strlen($match);
        $dir = $template->cachedir;
        $res = opendir($dir);
        while (($file = readdir($res)) !== false) {
            if (substr($file, 0, $match_len) === $match) {
                unlink($dir . $file);
            }
        }
        closedir($res);

        $this->response['template_cache_html'] = '<span class="seed bold">' . $lang['ALL_TEMPLATE_CLEARED'] . '</span>';

        break;

    case 'indexer':

        exec("indexer --config {$di->config->get('sphinx_config_path')} --all --rotate", $result);

        if (!is_file($di->config->get('sphinx_config_path') . ".log")) {
            file_put_contents($di->config->get('sphinx_config_path') . ".log", "####Logger from dimka3210.####" . date("H:i:s", TIMENOW) . "##############################\r\n\r\n\r\n\r\n", FILE_APPEND);
        }

        file_put_contents($di->config->get('sphinx_config_path') . ".log", "##############################" . date("H:i:s", TIMENOW) . "##############################\r\n", FILE_APPEND);

        foreach ($result as $row) {
            file_put_contents($di->config->get('sphinx_config_path') . ".log", $row . "\r\n", FILE_APPEND);
        }

        file_put_contents($di->config->get('sphinx_config_path') . ".log", "\r\n", FILE_APPEND);
        file_put_contents($di->config->get('sphinx_config_path') . ".log", "\r\n", FILE_APPEND);

        $this->response['indexer_html'] = '<span class="seed bold">' . $lang['INDEXER'] . '</span>';

        break;

    case 'update_user_level':

        require(INC_DIR . 'functions_group.php');

        update_user_level('all');

        $this->response['update_user_level_html'] = '<span class="seed bold">' . $lang['USER_LEVELS_UPDATED'] . '</span>';

        break;

    case 'sync_topics':

        sync('topic', 'all');
        sync_all_forums();

        $this->response['sync_topics_html'] = '<span class="seed bold">' . $lang['TOPICS_DATA_SYNCHRONIZED'] . '</span>';

        break;

    case 'sync_user_posts':

        sync('user_posts', 'all');

        $this->response['sync_user_posts_html'] = '<span class="seed bold">' . $lang['USER_POSTS_COUNT_SYNCHRONIZED'] . '</span>';

        break;

    case 'unlock_cron':

        cron_enable_board();

        $this->response['unlock_cron_html'] = '<span class="seed bold">' . $lang['ADMIN_UNLOCKED'] . '</span>';

        break;
}

$this->response['mode'] = $mode;
