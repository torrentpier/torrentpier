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
$previousVersion = \TorrentPier\Helpers\VersionHelper::removerPrefix($updaterFile['previous_version']);

define('UPGRADE_DIR', BB_PATH . '/install/upgrade');
define('UPDATE_FILE_PREFIX', 'update-v');
define('UPDATE_FILE_EXTENSION', '.sql');

// Checking version
if (\z4kn4fein\SemVer\Version::equal($latestVersion, $currentVersion)) {
    $files = glob(UPGRADE_DIR . '/' . UPDATE_FILE_PREFIX . '*' . UPDATE_FILE_EXTENSION);
    $updatesVersions = [];
    foreach ($files as $file) {
        $file = pathinfo(basename($file), PATHINFO_FILENAME);
        $version = str_replace(UPDATE_FILE_PREFIX, '', $file);
        $updatesVersions[] = \z4kn4fein\SemVer\Version::parse($version);
    }

    $sortedVersionsList = \z4kn4fein\SemVer\Version::sort($updatesVersions);
    foreach ($sortedVersionsList as $version) {
        if (\z4kn4fein\SemVer\Version::greaterThan($latestVersion, $previousVersion) &&
            \z4kn4fein\SemVer\Version::lessThanOrEqual($version, $latestVersion)) {
            dump($version);
        }
    }
} elseif (\z4kn4fein\SemVer\Version::greaterThan($latestVersion, $currentVersion)) {
    // todo: need to update first
}
