<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'info');

require __DIR__ . '/common.php';

// Start session management
user()->session_start();

$info = [];
$htmlDir = LANG_DIR . 'html/';
$show = isset($_REQUEST['show']) ? (string)$_REQUEST['show'] : '';

switch ($show) {
    case 'advert':
        $info['title'] = __('ADVERT');
        $info['src'] = 'advert.html';
        break;

    case 'copyright_holders':
        $info['title'] = __('COPYRIGHT_HOLDERS');
        $info['src'] = 'copyright_holders.html';
        break;

    case 'user_agreement':
        $info['title'] = __('USER_AGREEMENT');
        $info['src'] = 'user_agreement.html';
        break;

    default:
    case 'not_found':
        $info['title'] = __('NOT_FOUND');
        $info['src'] = 'not_found.html';
        break;
}

$require = is_file($htmlDir . $info['src']) ? ($htmlDir . $info['src']) : false;

template()->assign_vars([
    'PAGE_TITLE' => mb_strtoupper($info['title'], DEFAULT_CHARSET),
    'REQUIRE' => $require ? file_get_contents($require) : __('NOT_FOUND'),
]);

print_page('info.tpl', 'simple');
