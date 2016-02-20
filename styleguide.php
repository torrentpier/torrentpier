<?php

define('BB_SCRIPT', 'styleguide');
define('BB_ROOT', './');
require_once __DIR__ . '/common.php';

$user->session_start();

$di = \TorrentPier\Di::getInstance();

$content = $di->view->make('styleguide', [
    'name' => $di->request->get('name', $user->data['username'])
]);

/** @var \Symfony\Component\HttpFoundation\Response $response */
$response = \Symfony\Component\HttpFoundation\Response::create();
$response->setContent($content);

$response->prepare($di->request);
$response->send();
