<?php

$t = microtime(true);
define('BB_SCRIPT', 'styleguide');
define('IN_LOGIN', true);
define('BB_ROOT', './');
require_once __DIR__ . '/common.php';

$user->session_start();

$di = \TorrentPier\Di::getInstance();

$content = $di->view->make('styleguide', [
	'name' => $di->request->get('name', $user->data['username'])
]);

/** @var \Symfony\Component\HttpFoundation\Response $response */
$response = $di->response;

$response->setContent($content);
$response->send();