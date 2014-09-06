<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$user_id     = $userdata['user_id'];
$user_points = $userdata['user_points'];

if($bb_cfg['seed_bonus_enabled'] && $bb_cfg['bonus_upload'] && $bb_cfg['bonus_upload_price'])
{
	$upload_row = unserialize($bb_cfg['bonus_upload']);
	$price_row  = unserialize($bb_cfg['bonus_upload_price']);
}
else bb_die($lang['EXCHANGE_NOT']);

if (isset($_POST['bonus_id']))
{
	$id = (int) $_POST['bonus_id'];

	$btu = get_bt_userdata($user_id);

	if (empty($btu))
	{
		require(INC_DIR .'functions_torrent.php');
		generate_passkey($user_id, true);
		$btu = get_bt_userdata($user_id);
	}

	$upload = $upload_row[$id]*1024*1024*1024;
	$points = $price_row[$id];

	if ($userdata['user_points'] < $points)
	{
		meta_refresh('index.php', 5);

		$message = $lang['BONUS_NOT_SUCCES'] .'<br /><br /><a href="'. BONUS_URL .'">'. $lang['BONUS_RETURN'] .'</a><br /><br /><a href="'. PROFILE_URL . $userdata['user_id'] .'">'. $lang['RETURN_PROFILE'] .'</a><br /><br />'. sprintf($lang['CLICK_RETURN_INDEX'],  '<a href="index.php">', '</a>');

		bb_die($message);
	}

	DB()->query("UPDATE ". BB_BT_USERS ." bu, ". BB_USERS ." u
		SET
			bu.u_up_total   = u_up_total    + $upload,
			u.user_points   = u.user_points - $points
		WHERE
			bu.user_id      = $user_id
			AND u.user_id   = bu.user_id
	");

	cache_rm_user_sessions($user_id);
	meta_refresh(BONUS_URL, 5);

	$message = sprintf($lang['BONUS_SUCCES'], humn_size($upload_row[$id]*1024*1024*1024));
	$message .= '<br /><br /><a href="'. BONUS_URL .'">'. $lang['BONUS_RETURN'] .'</a><br /><br /><a href="'. PROFILE_URL . $userdata['user_id'] .'">'. $lang['RETURN_PROFILE'] .'</a><br /><br />'. sprintf($lang['CLICK_RETURN_INDEX'],  '<a href="index.php">', '</a>');

	bb_die($message);
}
else
{
	$template->assign_vars(array(
		'U_USER_PROFILE'  => PROFILE_URL . $user_id,
		'S_MODE_ACTION'	  => 'profile.php?mode=bonus',
		'PAGE_TITLE'	  => $lang['EXCHANGE_BONUS'],
		'MY_BONUS'        => sprintf($lang['MY_BONUS'], $user_points),
	));

	foreach($price_row as $i => $price)
	{
		if(!$price || !$upload_row[$i]) continue;
		$class = ($user_points >= $price) ? 'seed' : 'leech';

		$template->assign_block_vars('bonus_upload', array(
			'ROW_CLASS' => !($i % 2) ? 'row2' : 'row1',
			'ID'        => $i,
			'DESC'      => sprintf($lang['BONUS_UPLOAD_DESC'], humn_size($upload_row[$i]*1024*1024*1024)),
			'PRICE'     => sprintf($lang['BONUS_UPLOAD_PRICE'], $class, sprintf('%.2f', $price)),
		));
	}

	print_page('usercp_bonus.tpl');
}