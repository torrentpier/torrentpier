<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'updater');

require __DIR__ . '/common.php';

$user->session_start(['req_login' => true]);

// Check admin rights
if (!IS_SUPER_ADMIN) {
    bb_die($lang['ONLY_FOR_SUPER_ADMIN']);
}

// Read updater file
$updaterFile = readUpdaterFile();
if (empty($updaterFile) || !is_array($updaterFile)) {
    redirect('index.php');
    exit;
}

$latestVersion = \TorrentPier\Helpers\VersionHelper::removerPrefix($updaterFile['latest_version']);
$currentVersion = \TorrentPier\Helpers\VersionHelper::removerPrefix($bb_cfg['tp_version']);

// Checking version
if (\z4kn4fein\SemVer\Version::equal($latestVersion, $currentVersion)) {

} elseif (\z4kn4fein\SemVer\Version::greaterThan($latestVersion, $currentVersion)) {
    // todo: need to update first
}
