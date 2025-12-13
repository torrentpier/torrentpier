<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_ROOT', './../');
define('IN_ADMIN', true);

require dirname(__DIR__, 2) . '/library/common.php';

user()->session_start();

if (IS_GUEST) {
    redirect(LOGIN_URL . '?redirect=admin/index.php');
}

if (!IS_ADMIN) {
    bb_die(__('NOT_ADMIN'));
}

if (!userdata('session_admin')) {
    $redirect = url_arg(request()->getRequestUri(), 'admin', 1);
    redirect(LOGIN_URL . "?redirect={$redirect}");
}
