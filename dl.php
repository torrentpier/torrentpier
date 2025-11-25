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
    bb_die($lang['NO_ATTACHMENT_SELECTED']);
}

// Get topic data with torrent info
$sql = "
    SELECT
        t.topic_id, t.topic_title, t.topic_poster, t.forum_id, t.attach_ext_id,
        tor.tor_status, tor.poster_id
    FROM " . BB_TOPICS . " t
    LEFT JOIN " . BB_BT_TORRENTS . " tor ON tor.topic_id = t.topic_id
    WHERE t.topic_id = $topic_id
    LIMIT 1
";

if (!$topic_data = DB()->fetch_row($sql)) {
    bb_die($lang['ERROR_NO_ATTACHMENT']);
}

if (!$topic_data['attach_ext_id']) {
    bb_die($lang['ERROR_NO_ATTACHMENT']);
}

$forum_id = $topic_data['forum_id'];

// Authorization check
$is_auth = auth(AUTH_ALL, $forum_id, $userdata);
set_die_append_msg($forum_id, $topic_id);

if (!$is_auth['auth_download']) {
    bb_die($lang['SORRY_AUTH_VIEW_ATTACH'], 403);
}

// TorrServer M3U support
if ($m3u) {
    $torrServer = new \TorrentPier\TorrServerAPI();
    if (!$m3uFile = $torrServer->getM3UPath($topic_id)) {
        bb_die($lang['ERROR_NO_ATTACHMENT']);
    }

    $filename = basename($m3uFile);
    header('Content-Type: ' . mime_content_type($m3uFile));
    header("Content-Disposition: attachment; filename=\"$filename\"");
    readfile($m3uFile);
    exit;
}

// Check tor status for frozen downloads
if (!IS_AM && $topic_data['tor_status']) {
    $frozen = config()->get('tor_frozen');
    $frozen_author = config()->get('tor_frozen_author_download');

    if (isset($frozen[$topic_data['tor_status']])) {
        $is_author = $userdata['user_id'] === $topic_data['poster_id'];
        if (!(isset($frozen_author[$topic_data['tor_status']]) && $is_author)) {
            bb_die($lang['TOR_STATUS_FORBIDDEN'] . $lang['TOR_STATUS_NAME'][$topic_data['tor_status']]);
        }
    }
}

// For torrents - add passkey and send
if ($topic_data['attach_ext_id'] == 8) {
    if (!(isset($_GET['original']) && IS_ADMIN)) {
        \TorrentPier\Legacy\Torrent::send_torrent_with_passkey_by_topic($topic_data);
    }
}

// Get file path and send for non-torrent files
$file_path = get_attach_path($topic_id);

if (!is_file($file_path)) {
    bb_die($lang['ERROR_NO_ATTACHMENT'] . ' [HDD]');
}

$ext = config()->get('file_id_ext')[$topic_data['attach_ext_id']] ?? '';
$send_filename = "t-$topic_id" . ($ext ? ".$ext" : '');

header('Pragma: public');
header("Content-Type: application/octet-stream; name=\"$send_filename\"");
header("Content-Disposition: attachment; filename=\"$send_filename\"");
header('Content-Length: ' . filesize($file_path));

readfile($file_path);
exit;
