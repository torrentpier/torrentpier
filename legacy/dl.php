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
define('BB_SCRIPT', 'dl');
define('NO_GZIP', true);

require __DIR__.'/common.php';
require ATTACH_DIR.'/attachment_mod.php';

$datastore->enqueue([
    'attach_extensions',
    'cat_forums',
]);

$download_id = request_var('id', 0);
$thumbnail = request_var('thumb', 0);
$m3u = isset($_GET['m3u']) && $_GET['m3u'];

// Send file to browser
function send_file_to_browser($attachment, $upload_dir)
{
    global $lang;

    $filename = $upload_dir.'/'.$attachment['physical_filename'];
    $gotit = false;

    if (is_file(realpath($filename))) {
        $gotit = true;
    } else {
        bb_die($lang['ERROR_NO_ATTACHMENT'].'<br /><br />'.htmlCHR($filename));
    }

    // Correct the mime type - we force application/octet-stream for all files, except images
    // Please do not change this, it is a security precaution
    if (!str_contains($attachment['mimetype'], 'image')) {
        $attachment['mimetype'] = 'application/octet-stream';
    } else {
        header('Cache-Control: public, max-age=3600');
    }

    //bt
    if (!(isset($_GET['original']) && !IS_USER)) {
        \TorrentPier\Legacy\Torrent::send_torrent_with_passkey($filename);
    }

    // Now the tricky part... let's dance
    header('Pragma: public');
    $real_filename = clean_filename(basename($attachment['real_filename']));
    $mimetype = $attachment['mimetype'].';';
    $charset = 'charset='.DEFAULT_CHARSET.';';

    // Send out the Headers
    header("Content-Type: $mimetype $charset name=\"$real_filename\"");
    header("Content-Disposition: inline; filename=\"$real_filename\"");
    unset($real_filename);

    // Now send the File Contents to the Browser
    if ($gotit) {
        $size = filesize($filename);
        if ($size) {
            header("Content-length: $size");
        }
        readfile($filename);
    } else {
        bb_die($lang['ERROR_NO_ATTACHMENT'].'<br /><br />'.htmlCHR($filename));
    }

    exit;
}

//
// Start Session Management
//
$user->session_start();

set_die_append_msg();

if (!$download_id) {
    bb_die($lang['NO_ATTACHMENT_SELECTED']);
}

if ($attach_config['disable_mod'] && !IS_ADMIN) {
    bb_die($lang['ATTACHMENT_FEATURE_DISABLED']);
}

$sql = 'SELECT * FROM '.BB_ATTACHMENTS_DESC.' WHERE attach_id = '.(int) $download_id;

if (!($result = DB()->sql_query($sql))) {
    bb_die('Could not query attachment information #1');
}

if (!($attachment = DB()->sql_fetchrow($result))) {
    bb_die($lang['ERROR_NO_ATTACHMENT']);
}

$attachment['physical_filename'] = basename($attachment['physical_filename']);

if ($thumbnail) {
    // Re-define $attachment['physical_filename'] for thumbnails
    $attachment['physical_filename'] = THUMB_DIR.'/t_'.$attachment['physical_filename'];
} elseif ($m3u) {
    // Check m3u file exist
    if (!$m3uFile = (new \TorrentPier\TorrServerAPI())->getM3UPath($download_id)) {
        bb_die($lang['ERROR_NO_ATTACHMENT']);
    }

    $attachment['physical_filename'] = $attachment['real_filename'] = basename($m3uFile);
    $attachment['mimetype'] = mime_content_type($m3uFile);
    $attachment['extension'] = str_replace('.', '', \TorrentPier\TorrServerAPI::M3U['extension']);
}

DB()->sql_freeresult($result);

// get forum_id for attachment authorization or private message authorization
$authorised = false;

$sql = 'SELECT * FROM '.BB_ATTACHMENTS.' WHERE attach_id = '.(int) $attachment['attach_id'];

if (!($result = DB()->sql_query($sql))) {
    bb_die('Could not query attachment information #2');
}

$auth_pages = DB()->sql_fetchrowset($result);
$num_auth_pages = DB()->num_rows($result);

for ($i = 0; $i < $num_auth_pages && $authorised == false; $i++) {
    $auth_pages[$i]['post_id'] = (int) $auth_pages[$i]['post_id'];

    if ($auth_pages[$i]['post_id'] != 0) {
        $sql = 'SELECT forum_id, topic_id FROM '.BB_POSTS.' WHERE post_id = '.(int) $auth_pages[$i]['post_id'];

        if (!($result = DB()->sql_query($sql))) {
            bb_die('Could not query post information');
        }

        $row = DB()->sql_fetchrow($result);

        $topic_id = $row['topic_id'];
        $forum_id = $row['forum_id'];

        $is_auth = auth(AUTH_ALL, $forum_id, $userdata);
        set_die_append_msg($forum_id, $topic_id);

        if ($is_auth['auth_download']) {
            $authorised = true;
        }
    }
}

// Check the auth rights
if (!$authorised) {
    bb_die($lang['SORRY_AUTH_VIEW_ATTACH'], 403);
}

$datastore->rm('cat_forums');

// Check tor status
if (!IS_AM && ($attachment['mimetype'] === TORRENT_MIMETYPE)) {
    $sql = 'SELECT tor_status, poster_id FROM '.BB_BT_TORRENTS.' WHERE attach_id = '.(int) $attachment['attach_id'];

    if (!($result = DB()->sql_query($sql))) {
        bb_die('Could not query tor_status information');
    }

    $row = DB()->sql_fetchrow($result);

    if (isset(config()->get('tor_frozen')[$row['tor_status']]) && !(isset(config()->get('tor_frozen_author_download')[$row['tor_status']]) && $userdata['user_id'] === $row['poster_id'])) {
        bb_die($lang['TOR_STATUS_FORBIDDEN'].$lang['TOR_STATUS_NAME'][$row['tor_status']]);
    }

    DB()->sql_freeresult($result);
}

// Get Information on currently allowed Extensions
$rows = get_extension_informations();
$num_rows = count($rows);

$allowed_extensions = $download_mode = [];
for ($i = 0; $i < $num_rows; $i++) {
    $extension = strtolower(trim($rows[$i]['extension']));
    // Get allowed extensions
    if ((int) $rows[$i]['allow_group'] === 1) {
        $allowed_extensions[] = $extension;
    }
    $download_mode[$extension] = $rows[$i]['download_mode'];
}

// Disallowed
if (!in_array($attachment['extension'], $allowed_extensions) && !IS_ADMIN) {
    bb_die(sprintf($lang['EXTENSION_DISABLED_AFTER_POSTING'], $attachment['extension']).'<br /><br />'.$lang['FILENAME'].':&nbsp;'.$attachment['physical_filename']);
}

// Getting download mode by extension
if (isset($download_mode[$attachment['extension']])) {
    $download_mode = (int) $download_mode[$attachment['extension']];
} else {
    bb_die(sprintf($lang['EXTENSION_DISABLED_AFTER_POSTING'], $attachment['extension']).'<br /><br />'.$lang['FILENAME'].':&nbsp;'.$attachment['physical_filename']);
}

// Update download count
if (!$m3u && !$thumbnail && is_file(realpath($upload_dir.'/'.$attachment['physical_filename']))) {
    $sql = 'UPDATE '.BB_ATTACHMENTS_DESC.' SET download_count = download_count + 1 WHERE attach_id = '.(int) $attachment['attach_id'];

    if (!DB()->sql_query($sql)) {
        bb_die('Could not update attachment download count');
    }
}

// Determine the 'presenting'-method
switch ($download_mode) {
    case PHYSICAL_LINK:
        $url = make_url($upload_dir.'/'.$attachment['physical_filename']);
        header('Location: '.$url);
        exit;
    case INLINE_LINK:
        if (IS_GUEST && !config()->get('captcha.disabled') && !bb_captcha('check')) {
            global $template;

            $redirect_url = $_POST['redirect_url'] ?? $_SERVER['HTTP_REFERER'] ?? '/';
            $message = '<form action="'.DL_URL.$attachment['attach_id'].'" method="post">';
            $message .= $lang['CAPTCHA'].':';
            $message .= '<div  class="mrg_10" align="center">'.bb_captcha('get').'</div>';
            $message .= '<input type="hidden" name="redirect_url" value="'.$redirect_url.'" />';
            $message .= '<input type="submit" class="bold" value="'.$lang['SUBMIT'].'" /> &nbsp;';
            $message .= '<input type="button" class="bold" value="'.$lang['GO_BACK'].'" onclick="document.location.href = \''.$redirect_url.'\';" />';
            $message .= '</form>';

            $template->assign_vars(['ERROR_MESSAGE' => $message]);

            require PAGE_HEADER;
            require PAGE_FOOTER;
        }

        send_file_to_browser($attachment, $upload_dir);
        exit;
    default:
        bb_die('Incorrect download mode: '.$download_mode);
}
