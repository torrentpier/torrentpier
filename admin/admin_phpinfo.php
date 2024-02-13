<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    if (IS_SUPER_ADMIN) {
        $module['GENERAL']['PHP_INFO'] = basename(__FILE__);
    }
    return;
}

require __DIR__ . '/pagestart.php';

if (!IS_SUPER_ADMIN) {
    bb_die($lang['ONLY_FOR_SUPER_ADMIN']);
}

// Check for demo mode
if (IN_DEMO_MODE) {
    bb_die($lang['CANT_EDIT_IN_DEMO_MODE']);
}

/** @noinspection ForgottenDebugOutputInspection */
phpinfo();
