<?php

// ACP Header - START
if (!empty($setmodules))
{
	$module['General']['eXtreme Styles'] = 'xs_index.php';
	return;
}
require('./pagestart.php');
// ACP Header - END

define('IN_XS', true);
define('XS_ADMIN_OVERRIDE', true);
include('xs_include.php');
return;