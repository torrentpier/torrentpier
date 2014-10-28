<?php

if (!empty($setmodules))
{
	$module['GENERAL']['PHP_INFO'] = basename(__FILE__);
	return;
}
require('./pagestart.php');

phpinfo();