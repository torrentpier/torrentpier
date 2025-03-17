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
    $files = glob(BB_PATH . '/install/upgrade/update-*.sql');
    $updatesVersions = [];
    foreach ($files as $file) {
        $version = str_replace('update-v', '', pathinfo(basename($file), PATHINFO_FILENAME));
        $updatesVersions[] = [
            'path' => hide_bb_path($file),
            'version' => \z4kn4fein\SemVer\Version::parse($version)
        ];
    }

    $sortedVersionsList = \z4kn4fein\SemVer\Version::sort(array_column($updatesVersions, 'version'));
    foreach ($sortedVersionsList as $version) {
        if (\z4kn4fein\SemVer\Version::greaterThan($version, $currentVersion) &&
            \z4kn4fein\SemVer\Version::lessThanOrEqual($version, $latestVersion)) {
            // todo: тут запросы
        }
    }
} elseif (\z4kn4fein\SemVer\Version::greaterThan($latestVersion, $currentVersion)) {
    // todo: need to update first
}
