<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'dl');
define('NO_GZIP', true);

require __DIR__ . '/common.php';

$topic_id = (int)request_var('t', 0);
$m3u = isset($_GET['m3u']) && $_GET['m3u'];

// Start session
$user->session_start();
set_die_append_msg();

if (!$topic_id) {
    bb_die(__('NO_ATTACHMENT_SELECTED'));
}

// Get topic data with torrent info
$sql = "
    SELECT
        t.topic_id, t.topic_title, t.topic_poster, t.topic_first_post_id, t.forum_id, t.attach_ext_id, t.tracker_status,
        tor.tor_status, tor.poster_id
    FROM " . BB_TOPICS . " t
    LEFT JOIN " . BB_BT_TORRENTS . " tor ON tor.topic_id = t.topic_id
    WHERE t.topic_id = $topic_id
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
$is_auth = auth(AUTH_ALL, $forum_id, $userdata);
set_die_append_msg($forum_id, $topic_id);

if (!$is_auth['auth_download']) {
    bb_die(__('SORRY_AUTH_VIEW_ATTACH'), 403);
}

// TorrServer M3U support
if ($m3u) {
    $torrServer = new \TorrentPier\TorrServerAPI();
    if (!$m3uFile = $torrServer->getM3UPath($topic_id)) {
        bb_die(__('ERROR_NO_ATTACHMENT') . '[M3U]');
    }

    $filename = basename($m3uFile);
    header('Content-Type: ' . mime_content_type($m3uFile));
    header("Content-Disposition: attachment; filename=\"$filename\"");
    // $attachment['extension'] = str_replace('.', '', \TorrentPier\TorrServerAPI::M3U['extension']);
    readfile($m3uFile);
    exit;
}

// Check tor status for frozen downloads
if (!IS_AM && $t_data['tor_status']) {
    $row = DB()->table(BB_BT_TORRENTS)
        ->select('tor_status, poster_id')
        ->where('topic_id', $topic_id)
        ->fetch();

    if (isset(config()->get('tor_frozen')[$row['tor_status']]) && !(isset(config()->get('tor_frozen_author_download')[$row['tor_status']]) && $userdata['user_id'] === $row['poster_id'])) {
        bb_die(__('TOR_STATUS_FORBIDDEN') . __('TOR_STATUS_NAME.' . $row['tor_status']));
    }
}

// Check download limit
$dlTracker = new \TorrentPier\Torrent\DownloadTracker();
if (!$dlTracker->recordDownload($topic_id, $userdata['user_id'], IS_PREMIUM)) {
    bb_die(__('DOWNLOAD_LIMIT_EXCEEDED'));
}

// For torrents - add a passkey and send
if ($t_data['attach_ext_id'] == 8) {
    // Only admins can download the original unmodified torrent file
    if (!(isset($_GET['original']) && IS_ADMIN)) {
        \TorrentPier\Torrent\Sender::sendWithPasskey($t_data);
    }
}

// Get a file path and send for non-torrent files
$file_path = get_attach_path($topic_id);

if (!is_file($file_path)) {
    bb_die(__('ERROR_NO_ATTACHMENT') . ' [HDD]');
}

$ext = config()->get('file_id_ext')[$t_data['attach_ext_id']] ?? '';
$send_filename = "t-$topic_id" . ($ext ? ".$ext" : '');

header('Content-Type: application/octet-stream');
header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($send_filename));
header('Content-Length: ' . filesize($file_path));

readfile($file_path);
exit;
