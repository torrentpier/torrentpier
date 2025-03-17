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
    $template->assign_vars([
        'PAGE_TITLE' => $lang['UPDATER_TITLE'],
    ]);

    $confirm = request_var('confirm', '');
    if ($confirm) {
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
                $dump_path = ''; // todo...
                if (!is_file($dump_path) || !is_readable($dump_path)) {
                    bb_die(''); // todo...
                }

                $temp_line = '';
                foreach (file($dump_path) as $line) {
                    if (str_starts_with($line, '--') || $line == '') {
                        continue;
                    }

                    $temp_line .= $line;
                    if (str_ends_with(trim($line), ';')) {
                        if (!DB()->query($temp_line)) {
                            bb_die(DB()->sql_error()['message']);
                        }
                        $temp_line = '';
                    }
                }
            }
        }

        // Successful!
        $template->assign_vars([
            'RESULT' => '', // todo...
        ]);
    } else {
        // Welcoming
        $template->assign_vars([
            'FROM_VERSION' => $previousVersion,
            'TO_VERSION' => $currentVersion,
        ]);
    }

    print_page('updater.tpl');
} elseif (\z4kn4fein\SemVer\Version::greaterThan($latestVersion, $currentVersion)) {
    // todo: need to update first
}
