<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('IN_UPDATER', true);
define('BB_SCRIPT', 'updater');

require __DIR__ . '/common.php';

// Start session management
$user->session_start();

// Check auth rights
if (!IS_SUPER_ADMIN || !$updaterFile = readUpdaterFile()) {
    bb_die($lang['ONLY_FOR_SUPER_ADMIN']);
}

$versionsRange = range($updaterFile['previous_version'], VERSION_CODE);

if (!$confirm = request_var('confirm', '')) {
    $msg = '<form method="POST">';
    $msg .= '<h1 style="color: red">!!! Before clicking the button, make a backup of your database !!!</h1><br>';
    $msg .= '<input type="submit" name="confirm" value="Update to ' . $bb_cfg['tp_version'] . '" />';
    $msg .= '</form>';
    bb_die($msg);
}

// Make target sql queries
$processedQueries = [];

foreach ($versionsRange as $version) {
    switch ($version) {
        case 241: // v2.4.1
            $processedQueries[] = '[v2.4.1]';
            break;
        case 242: // v2.4.2
            $processedQueries[] = '[v2.4.2]';
            break;
        case 243: // v2.4.3
            $processedQueries[] = '[v2.4.3]';
            break;
    }
}

unlink(UPDATER_FILE);
bb_die('<h1 style="color: green;">База данных обновлена</h1><hr>' . implode('<br />', array_unique($processedQueries)));
