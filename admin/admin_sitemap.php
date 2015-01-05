<?php

if (!empty($setmodules))
{
	$module['MODS']['SITEMAP'] = basename(__FILE__);
	return;
}
require('./pagestart.php');

if(request_var('submit', '')) {
	if(bb_update_config(array('static_sitemap' => request_var('static_sitemap', '')))) {
		CACHE('bb_config')->rm();
	}
	bb_die('<a href="admin_sitemap.php">'. $lang['GO_BACK'] .'</a>');
}

$s_mess = $lang['SITEMAP_CREATED'].': <b>'. bb_date($new['sitemap_time'], $bb_cfg['post_date_format']) .'</b> '. $lang['SITEMAP_AVAILABLE'] .': <a href="'. make_url('sitemap.xml') .'" target="_blank">'. make_url('sitemap.xml') .'</a>';

$template->assign_vars(array(
	'STATIC_SITEMAP' => $new['static_sitemap'],
	'MESSAGE'        => (file_exists(INT_DATA_DIR ."sitemap/sitemap.xml")) ? $s_mess : $lang['SITEMAP_NOT_CREATED'],
));

print_page('admin_sitemap.tpl', 'admin');