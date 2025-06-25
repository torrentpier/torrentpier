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

global $lang;

if (!isset($this->request['attach_id'])) {
    $this->ajax_die($lang['EMPTY_ATTACH_ID']);
}
$attach_id = (int) $this->request['attach_id'];

$torrent = DB()->fetch_row('SELECT attach_id, physical_filename FROM '.BB_ATTACHMENTS_DESC." WHERE attach_id = $attach_id LIMIT 1");
if (!$torrent) {
    $this->ajax_die($lang['ERROR_BUILD']);
}

$file_contents = null;
$filename = get_attachments_dir().'/'.$torrent['physical_filename'];
if (!is_file($filename) || !$file_contents = file_get_contents($filename)) {
    $this->ajax_die($lang['ERROR_NO_ATTACHMENT']."\n\n".htmlCHR($filename));
}

try {
    $tor = \Arokettu\Bencode\Bencode::decode($file_contents, dictType: \Arokettu\Bencode\Bencode\Collection::ARRAY);
} catch (\Exception $e) {
    $this->response['html'] = htmlCHR("{$lang['TORFILE_INVALID']}: {$e->getMessage()}");

    return;
}

$torrent = new TorrentPier\Legacy\TorrentFileList($tor);

$tor_filelist = $torrent->get_filelist();

$this->response['html'] = $tor_filelist;
