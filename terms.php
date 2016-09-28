<?php

define('BB_SCRIPT', 'terms');
define('BB_ROOT', './');
require_once __DIR__ . '/common.php';
require_once(INC_DIR . 'bbcode.php');

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

$user->session_start();

if (!$di->config->get('terms') && !IS_ADMIN) {
    redirect('index.php');
}

$content = $di->view->make('terms', [
    'isAdmin' => IS_ADMIN,
    'termsHtml' => bbcode2html($di->config->get('terms')),
    'transUrl' => make_url('admin/admin_terms.php'),
    'transUrlName' => $di->translator->trans('Control panel'),
]);

/** @var \Symfony\Component\HttpFoundation\Response $response */
$response = \Symfony\Component\HttpFoundation\Response::create();
$response->setContent($content);

$response->prepare($di->request);
$response->send();
