<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2017 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

$user_id = $userdata['user_id'];
$user_points = $userdata['user_points'];

if (config('tp.seed_bonus_enabled') && config('tp.bonus_upload') && config('tp.bonus_upload_price')) {
    $upload_row = unserialize(config('tp.bonus_upload'));
    $price_row = unserialize(config('tp.bonus_upload_price'));
} else {
    bb_die(trans('messages.EXCHANGE_NOT'));
}

if (isset($_POST['bonus_id'])) {
    $id = (int)$_POST['bonus_id'];

    $btu = get_bt_userdata($user_id);

    if (empty($btu)) {
        require INC_DIR . '/functions_torrent.php';
        generate_passkey($user_id, true);
        $btu = get_bt_userdata($user_id);
    }

    $upload = $upload_row[$id] * 1024 * 1024 * 1024;
    $points = $price_row[$id];

    if ($userdata['user_points'] < $points) {
        meta_refresh('index.php', 5);

        $message = trans('messages.BONUS_NOT_SUCCES') . '<br /><br /><a href="' . BONUS_URL . '">' . trans('messages.BONUS_RETURN') . '</a><br /><br /><a href="' . PROFILE_URL . $userdata['user_id'] . '">' . trans('messages.RETURN_PROFILE') . '</a><br /><br />' . sprintf(trans('messages.CLICK_RETURN_INDEX'), '<a href="index.php">', '</a>');

        bb_die($message);
    }

    OLD_DB()->query("UPDATE " . BB_BT_USERS . " bu, " . BB_USERS . " u
		SET
			bu.u_up_total   = u_up_total    + $upload,
			u.user_points   = u.user_points - $points
		WHERE
			bu.user_id      = $user_id
			AND u.user_id   = bu.user_id
	");

    cache_rm_user_sessions($user_id);
    meta_refresh(BONUS_URL, 5);

    $message = sprintf(trans('messages.BONUS_SUCCES'), humn_size($upload_row[$id] * 1024 * 1024 * 1024));
    $message .= '<br /><br /><a href="' . BONUS_URL . '">' . trans('messages.BONUS_RETURN') . '</a><br /><br /><a href="' . PROFILE_URL . $userdata['user_id'] . '">' . trans('messages.RETURN_PROFILE') . '</a><br /><br />' . sprintf(trans('messages.CLICK_RETURN_INDEX'), '<a href="index.php">', '</a>');

    bb_die($message);
} else {
    $template->assign_vars(array(
        'U_USER_PROFILE' => PROFILE_URL . $user_id,
        'S_MODE_ACTION' => 'profile.php?mode=bonus',
        'PAGE_TITLE' => trans('messages.EXCHANGE_BONUS'),
        'MY_BONUS' => sprintf(trans('messages.MY_BONUS'), $user_points),
    ));

    foreach ($price_row as $i => $price) {
        if (!$price || !$upload_row[$i]) {
            continue;
        }
        $class = ($user_points >= $price) ? 'seed' : 'leech';

        $template->assign_block_vars('bonus_upload', array(
            'ROW_CLASS' => !($i % 2) ? 'row2' : 'row1',
            'ID' => $i,
            'DESC' => sprintf(trans('messages.BONUS_UPLOAD_DESC'), humn_size($upload_row[$i] * 1024 * 1024 * 1024)),
            'PRICE' => sprintf(trans('messages.BONUS_UPLOAD_PRICE'), $class, sprintf('%.2f', $price)),
        ));
    }

    print_page('usercp_bonus.tpl');
}
