<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$clear_dir = BB_ROOT . 'thumbnail'; // Директория в которой хранятся минипостеры

$dir = opendir($clear_dir);
while(($file = readdir($dir)))
if(is_file($clear_dir."/".$file))
unlink($clear_dir."/".$file);
