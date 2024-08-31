<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $bb_cfg, $lang;

if (!$bb_cfg['torr_server']['enabled']) {
    $this->ajax_die($lang['MODULE_OFF']);
}

if (!$attach_id = (int)$this->request['attach_id']) {
    $this->ajax_die($lang['INVALID_ATTACH_ID']);
}

if (!$file_index = (int)$this->request['file_index']) {
    $this->ajax_die('Invalid file index');
}

if (!$info_hash = (string)$this->request['info_hash']) {
    $this->ajax_die('Invalid info_hash');
}

dump($attach_id);
dump($file_index);
dump($info_hash);

$this->response['ffprobe_data'] = '123';
