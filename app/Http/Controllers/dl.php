<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

use TorrentPier\Http\Response;

define('NO_GZIP', true);

$topic_id = request()->getInt('t');
$m3u = request()->getBool('m3u');

set_die_append_msg();

if (!$topic_id) {
    bb_die(__('NO_ATTACHMENT_SELECTED'));
}

// Get topic data with torrent info
$sql = '
    SELECT
        t.topic_id, t.topic_title, t.topic_poster, t.topic_first_post_id, t.forum_id, t.attach_ext_id, t.tracker_status,
        tor.tor_status, tor.poster_id
    FROM ' . BB_TOPICS . ' t
    LEFT JOIN ' . BB_BT_TORRENTS . " tor ON tor.topic_id = t.topic_id
    WHERE t.topic_id = {$topic_id}
    LIMIT 1
";

if (!$t_data = DB()->fetch_row($sql)) {
    bb_die(__('ERROR_NO_ATTACHMENT') . '[DB]');
}

if (!$t_data['attach_ext_id']) {
    bb_die(__('ERROR_NO_ATTACHMENT') . '[EXT_ID]');
}

$forum_id = $t_data['forum_id'];

// Authorization check
$is_auth = auth(AUTH_ALL, $forum_id, userdata());
set_die_append_msg($forum_id, $topic_id);

if (!$is_auth['auth_download']) {
    bb_die(__('SORRY_AUTH_VIEW_ATTACH'), 403);
}

// TorrServer M3U support
if ($m3u) {
    $torrServer = new TorrentPier\TorrServerAPI;
    if (!$m3uFile = $torrServer->getM3UPath($topic_id)) {
        bb_die(__('ERROR_NO_ATTACHMENT') . '[M3U]');
    }

    $response = Response::download($m3uFile, basename($m3uFile));
    $response->headers->set('Content-Type', 'audio/x-mpegurl');
    $response->send();
    exit;
}

// Check tor status for frozen downloads
if (!IS_AM && $t_data['tor_status']) {
    if (isset(config()->get('tor_frozen')[$t_data['tor_status']]) && !(isset(config()->get('tor_frozen_author_download')[$t_data['tor_status']]) && TorrentPier\Topic\Guard::isAuthor($t_data['poster_id']))) {
        bb_die(__('TOR_STATUS_FORBIDDEN') . __('TOR_STATUS_NAME.' . $t_data['tor_status']));
    }
}

// Check download limit
$dlCounter = new TorrentPier\Torrent\DownloadCounter;
if (!$dlCounter->recordDownload($topic_id, userdata('user_id'), IS_PREMIUM)) {
    bb_die(__('DOWNLOAD_LIMIT_EXCEEDED'));
}

// For torrents - add a passkey and send
if ($t_data['attach_ext_id'] == TORRENT_EXT_ID) {
    // Admins and topic author can download the original unmodified torrent file
    if (!(request()->query->has('original') && (IS_ADMIN || TorrentPier\Topic\Guard::isAuthor($t_data['topic_poster'])))) {
        TorrentPier\Torrent\Sender::sendWithPasskey($t_data);
    }
}

// Get a file path and send for non-torrent files
$file_path = TorrentPier\Attachment::getPath($topic_id);

if (!is_file($file_path)) {
    bb_die(__('ERROR_NO_ATTACHMENT') . ' [HDD]');
}

$ext = config()->get('file_id_ext')[$t_data['attach_ext_id']] ?? '';
$send_filename = TorrentPier\Attachment::getDownloadFilename($topic_id, $t_data['topic_title'], $ext);

Response::download($file_path, $send_filename)->send();
exit;
