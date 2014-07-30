<?php

// ACP Header - START
if (!empty($setmodules))
{
	$module['General']['Php_info'] = basename(__FILE__);
	return;
}
require('./pagestart.php');
// ACP Header - END

phpinfo();