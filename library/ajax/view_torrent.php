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

global $lang, $userdata;

if (!isset($this->request['topic_id'])) {
    $this->ajax_die($lang['EMPTY_ATTACH_ID']);
}
$topic_id = (int)$this->request['topic_id'];

$topic = DB()->fetch_row("
    SELECT
        t.topic_id, t.forum_id, t.attach_ext_id
    FROM " . BB_TOPICS . " t
    WHERE t.topic_id = $topic_id LIMIT 1");

if (!$topic || $topic['attach_ext_id'] != 8) {
    $this->ajax_die($lang['ERROR_BUILD']);
}

// Check rights
$is_auth = auth(AUTH_ALL, $topic['forum_id'], $userdata);
if (!$is_auth['auth_view']) {
    $this->ajax_die($lang['SORRY_AUTH_VIEW_ATTACH']);
}

$file_contents = null;
$filename = get_attach_path($topic_id);
if (!is_file($filename) || !$file_contents = file_get_contents($filename)) {
    $this->ajax_die($lang['ERROR_NO_ATTACHMENT'] . "\n\n" . htmlCHR($filename));
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
