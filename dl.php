<?php

define('BB_SCRIPT', 'dl');
define('BB_ROOT', './');
require_once __DIR__ . '/common.php';

$di = \TorrentPier\Di::getInstance();

if (!$topic_id = $di->request->get('t', 0)) {
    bb_simple_die($di->translator->trans('Invalid request: not specified %data%', ['%data%' => 'topic_id']));
}

$user->session_start();

global $userdata;

// $t_data
$sql = "SELECT t.*, f.* FROM " . BB_TOPICS . " t, " . BB_FORUMS . " f WHERE t.topic_id = $topic_id AND f.forum_id = t.forum_id LIMIT 1";
if (!$t_data = DB()->fetch_row($sql)) {
    bb_simple_die($di->translator->trans('File not found: %location%', ['%location%' => '[DB]']));
}
if (!$t_data['attach_ext_id']) {
    bb_simple_die($di->translator->trans('File not found: %location%', ['%location%' => '[EXT_ID]']));
}

// Auth check
$is_auth = auth(AUTH_ALL, $t_data['forum_id'], $userdata, $t_data);
if (!IS_GUEST) {
    if (!$is_auth['auth_download']) login_redirect($di->config->get('dl_url') . $topic_id);
} elseif (!$di->config->get('tracker.guest_tracker')) {
    login_redirect($di->config->get('dl_url') . $topic_id);
}

// Downloads counter
DB()->sql_query('UPDATE ' . BB_TOPICS . ' SET attach_dl_cnt = attach_dl_cnt + 1 WHERE topic_id = ' . $topic_id);

// Captcha for guest
if (IS_GUEST && !bb_captcha('check')) {
    $redirectUrl = $di->request->get('redirect_url');
    $redirectTemplate = $redirectUrl ? $redirectUrl : $di->request->server->get('HTTP_REFERER', '/');

    $content = $di->view->make('dl', [
        'captcha' => bb_captcha('get'),
        'download_url' => DOWNLOAD_URL . $topic_id,
        'redirect_template' => $redirectTemplate,
    ]);

    $response = \Symfony\Component\HttpFoundation\Response::create();
    $response->setContent($content);

    $response->prepare($di->request);
    $response->send();
}

$t_data['user_id'] = $userdata['user_id'];
$t_data['is_am'] = IS_AM;

// Torrent
if ($t_data['attach_ext_id'] == 8) {
    require(INC_DIR . 'functions_torrent.php');
    send_torrent_with_passkey($t_data);
}

// All other
$file_path = get_attach_path($topic_id, $t_data['attach_ext_id']);

if (($file_contents = @file_get_contents($file_path)) === false) {
    bb_simple_die($di->translator->trans('File not found: %location%', ['%location%' => '[HDD]']));
}

$send_filename = "t-$topic_id." . $di->config->get('file_id_ext')[$t_data['attach_ext_id']];

$response = \Symfony\Component\HttpFoundation\BinaryFileResponse::create();
$response->setFile($file_path, 'attachment; filename=' . $send_filename);

$response->prepare($di->request);
$response->send();