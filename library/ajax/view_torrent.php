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

global $lang;

if (!isset($this->request['t'])) {
    $this->ajax_die($lang['EMPTY_ATTACH_ID']);
}
$topic_id = (int)$this->request['t'];

// Получение торрент-файла
$file_path = get_attach_path($topic_id, 8);

if (file_exists($file_path) && !$file_contents = file_get_contents($file_path)) {
    if (IS_AM) {
        $this->ajax_die($lang['ERROR_NO_ATTACHMENT'] . "\n\n" . htmlCHR($file_path));
    } else {
        $this->ajax_die($lang['ERROR_NO_ATTACHMENT']);
    }
}

if (!$tor = \Rych\Bencode\Bencode::decode($file_contents)) {
    return $lang['TORFILE_INVALID'];
}

$torrent = new TorrentPier\Legacy\TorrentFileList($tor);
$tor_filelist = $torrent->get_filelist();

$this->response['html'] = $tor_filelist;
