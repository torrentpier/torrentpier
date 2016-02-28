<?php

define('BB_SCRIPT', 'dl');
define('BB_ROOT', './');
require_once __DIR__ . '/common.php';

$di = \TorrentPier\Di::getInstance();

/** @var \TorrentPier\Db\Adapter $db */
$db = $di->db;

$topic_id = $di->request->query->getInt('t');

if (!$topic_id) {
    bb_simple_die($di->translator->trans('Invalid request: not specified %data%', ['%data%' => 'topic_id']));
}

$user->session_start();

global $userdata;

// TODO: явное указание полей, для send_torrent_with_passkey и auth нужен рефакторинг
$t_data = $db->select(['t' => BB_TOPICS], function (\Zend\Db\Sql\Select $select) use ($topic_id) {
    $select->where(function (\Zend\Db\Sql\Where $where) use ($topic_id) {
        $where->equalTo('topic_id', $topic_id);
        $where->greaterThan('attach_ext_id', 0);
    });
    $select->join(['f' => BB_FORUMS], 'f.forum_id = t.forum_id');
})->one();

if (!$t_data) {
    bb_simple_die($di->translator->trans('File not found: %location%', ['%location%' => 'database']));
}

// Auth check
$is_auth = auth(AUTH_ALL, $t_data->forum_id, $userdata, $t_data);
if (!IS_GUEST) {
    if (!$is_auth['auth_download']) login_redirect($di->config->get('dl_url') . $topic_id);
} elseif (!$di->config->get('tracker.guest_tracker')) {
    login_redirect($di->config->get('dl_url') . $topic_id);
}

// Downloads counter
$db->increment(BB_TOPICS, 'attach_dl_cnt', ['topic_id' => $topic_id]);

// Captcha for guest
if (IS_GUEST && !bb_captcha('check')) {
    $redirectUrl = $di->request->get('redirect_url');
    $redirectTemplate = $redirectUrl ? $redirectUrl : $di->request->server->get('HTTP_REFERER', '/');

    $content = $di->view->make('dl', [
        'captcha' => bb_captcha('get'),
        'download_url' => DOWNLOAD_URL . $topic_id,
        'redirect_template' => $redirectTemplate,
    ]);

    /** @var \Symfony\Component\HttpFoundation\Response $response */
    $response = \Symfony\Component\HttpFoundation\Response::create();
    $response->setContent($content);

    $response->prepare($di->request);
    $response->send();
}

// Torrent
if ($t_data->attach_ext_id == 8) {
    require(INC_DIR . 'functions_torrent.php');
    send_torrent_with_passkey($t_data);
}

// All other
$file_path = get_attach_path($topic_id, $t_data->attach_ext_id);

if (!file_exists($file_path)) {
    bb_simple_die($di->translator->trans('File not found: %location%', ['%location%' => '[HDD]']));
}

$send_filename = "t-$topic_id." . $di->config->get('file_id_ext')[$t_data->attach_ext_id];

/** @var \Symfony\Component\HttpFoundation\BinaryFileResponse $response */
$response = \Symfony\Component\HttpFoundation\BinaryFileResponse::create();
$response->setFile($file_path, 'attachment; filename=' . $send_filename);

$response->prepare($di->request);
$response->send();
