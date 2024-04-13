<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'updater');

require __DIR__ . '/common.php';

// Init userdata
$user->session_start(['req_login' => true]);

set_die_append_msg();
if (!IS_SUPER_ADMIN) bb_die($lang['ONLY_FOR_SUPER_ADMIN']);

$confirm = request_var('confirm', '');
if ($confirm) {

} else {

}
