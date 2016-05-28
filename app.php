<?php

require_once __DIR__ . '/bootstrap.php';

/** @var \TorrentPier\View $view */
$view = $di->view;
/** @var \TorrentPier\Db\Adapter $db */
$db = $di->db;


$view->addGlobal('title', 'Title Page Simple');


$categories = $db->select('bb_categories', function (\Zend\Db\Sql\Select $query) {
    $query->join('bb_forums', 'bb_categories.cat_id = bb_forums.cat_id');
})->all();




echo $view->make('app', [
    'data' => 'Hello world',
    'categories' => $categories
]);
