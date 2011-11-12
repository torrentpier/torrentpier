<?php

define('START_CRON', true);
$dirname = str_replace('\\', '/', __DIR__);
if (substr($dirname, -1) != '/') $dirname .= '/';
define('BB_ROOT', $dirname);

require(BB_ROOT. 'common.php');