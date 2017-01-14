<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

define('BB_SCRIPT', 'styleguide');
define('BB_ROOT', './');
require_once __DIR__ . '/common.php';

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

/** @var \TorrentPier\Cache\Adapter $cache */
$cache = $di->cache;

//var_dump($cache);
//
//$resultHas = $cache->has('key');
//$resultGet = $cache->get('key');
//$resultSet = $cache->set('key', [[1], [2]], 5);
//$resultStats = $cache->stats();

//var_dump($resultHas, $resultGet, $resultSet, $resultStats);

///** @var \TorrentPier\Db\Adapter $db */
//$db = $di->db;

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

//\TorrentPier\Log::info('You will see the style guide');
//
//$query = function(\Zend\Db\Sql\Select $select) {
//    $select->columns(['id' => 'user_id', 'name' => 'username']);
//    $select->join(['t' => BB_TOPICS], 't.topic_poster = u.user_id', ['title' => 'topic_title']);
////    $select->where(['user_id > ?' => 0]);
//};
//
//$users = $db->select(['u' => BB_USERS], $query)->all();
//$usersCount = $db->count(['u' => BB_USERS], $query);
//
//$content = $di->view->make('styleguide', [
//    'name' => $di->request->get('name', 'Admin'),
//    'users' => $users,
//    'users_count' => $usersCount
//]);
//
///** @var \Symfony\Component\HttpFoundation\Response $response */
//$response = \Symfony\Component\HttpFoundation\Response::create();
//$response->setContent($content);
//
//$response->prepare($di->request);
//$response->send();
