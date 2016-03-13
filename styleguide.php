<?php

define('BB_SCRIPT', 'styleguide');
define('BB_ROOT', './');
require_once __DIR__ . '/common.php';

$di = \TorrentPier\Di::getInstance();

/** @var \TorrentPier\Db\Adapter $db */
$db = $di->db;

///** @var \Symfony\Component\HttpFoundation\Request $request */
//$request = $di->request;
//$request->request->has()


//$id = $db->insert(BB_USERS, [
//    'username' => 'user_' . rand(1, 50)
//]);
//
//$result = $db->update(BB_USERS, ['user_email' => 'user_' . rand(1, 50) . '@test.com'], ['user_id' => $id]);
//
//var_dump($result);
//
//$result = $db->delete(BB_USERS, ['user_id' => $id]);
//
//var_dump($result);
///** @var \TorrentPier\Db\Result $users */
//$users = $db->select(BB_USERS, function(\Zend\Db\Sql\Select $select) {
//    $select->columns(['id' => 'user_id', 'name' => 'username']);
//    $select->join(['t' => BB_TOPICS], 't.topic_poster = u.user_id', ['title' => 'topic_title']);
//    $select->where(function(\Zend\Db\Sql\Where $where) {
//        $where->greaterThanOrEqualTo('user_id', 3);
////        $where->equalTo('user_id', 3);
//    });
//});

///** @var \Monolog\Logger $log */
//$log = $di->log;
//$log->debug('test debug information');

\TorrentPier\Log::info('You will see the style guide');

$query = function(\Zend\Db\Sql\Select $select) {
    $select->columns(['id' => 'user_id', 'name' => 'username']);
    $select->join(['t' => BB_TOPICS], 't.topic_poster = u.user_id', ['title' => 'topic_title']);
//    $select->where(['user_id > ?' => 0]);
};

$users = $db->select(['u' => BB_USERS], $query)->all();
$usersCount = $db->count(['u' => BB_USERS], $query);

$content = $di->view->make('styleguide', [
    'name' => $di->request->get('name', 'Admin'),
    'users' => $users,
    'users_count' => $usersCount
]);

/** @var \Symfony\Component\HttpFoundation\Response $response */
$response = \Symfony\Component\HttpFoundation\Response::create();
$response->setContent($content);

$response->prepare($di->request);
$response->send();
