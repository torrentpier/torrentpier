<?php

// ACP Header - START
if (!empty($setmodules))
{
	$module['General']['PHP Info'] = basename(__FILE__);
	return;
}
require('./pagestart.php');
// ACP Header - END

phpinfo();