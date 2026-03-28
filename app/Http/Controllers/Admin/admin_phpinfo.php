<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!IS_SUPER_ADMIN) {
    bb_die(__('ONLY_FOR_SUPER_ADMIN'));
}

/** @noinspection ForgottenDebugOutputInspection */
ob_start();
phpinfo();
$phpinfoHtml = ob_get_clean();

// Extract only the body content
if (preg_match('#<body[^>]*>(.*)</body>#si', $phpinfoHtml, $matches)) {
    $phpinfoHtml = $matches[1];
}

// Remove inline styles that conflict with admin CSS
$phpinfoHtml = preg_replace('#<style[^>]*>.*?</style>#si', '', $phpinfoHtml);

template()->assign_vars([
    'PHPINFO_OUTPUT' => $phpinfoHtml,
]);

print_page('admin_phpinfo.tpl', 'admin');
