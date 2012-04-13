<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$user_id     = $userdata['user_id'];
$user_points = $userdata['user_points'];

if($bb_cfg['bonus_upload'] && $bb_cfg['bonus_upload_price'])
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

	if(empty($upload_row[$id]) || empty($price_row[$id]))
	{
		bb_die('false');
	}

	$upload = $upload_row[$id]*1024*1024*1024;
	$points = $price_row[$id];

    if($userdata['user_points'] <= $points) bb_die('false2');

	DB()->query("UPDATE ". BB_BT_USERS ." bu, ". BB_USERS ." u
		SET
			bu.u_up_total   = u_up_total    + $upload,
			u.user_points   = u.user_points - $points
		WHERE
			bu.user_id      = $user_id
			AND u.user_id   = bu.user_id
	");

    cache_rm_user_sessions ($user_id);

    bb_die(sprintf($lang['BONUS_SUCCES'], $_POST['bonus_id']));
}
else
{
	$template->assign_vars(array(
		'U_USER_PROFILE'	=> PROFILE_URL . $user_id,
		'S_MODE_ACTION'		=> 'profile.php?mode=bonus',
	));

	foreach($price_row as $i => $price)
	{
		if(!$price || !$upload_row[$i]) continue;
		$class = ($user_points >= $price) ? 'seed' : 'leech';

		$template->assign_block_vars('bonus_upload', array(
			'ID'    => $i,
			'DESC'  => sprintf($lang['BONUS_UPLOAD_DESC'], humn_size($upload_row[$i]*1024*1024*1024)),
			'PRICE' => sprintf($lang['BONUS_UPLOAD_PRICE'], sprintf('%.2f', $price), $class, $user_points),
		));
	}

	print_page('usercp_bonus.tpl');
}