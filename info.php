<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'info');

require __DIR__ . '/common.php';

// Start session management
$user->session_start();

global $lang;

$info = [];
$html_dir = LANG_DIR . 'html/';
$req_mode = (string)$_REQUEST['show'];

switch ($req_mode) {
    case 'advert':
        $info['title'] = $lang['ADVERT'];
        $info['src'] = 'advert.html';
        break;

    case 'copyright_holders':
        $info['title'] = $lang['COPYRIGHT_HOLDERS'];
        $info['src'] = 'copyright_holders.html';
        break;

    case 'user_agreement':
        $info['title'] = $lang['USER_AGREEMENT'];
        $info['src'] = 'user_agreement.html';
        break;

    default:
    case 'not_found':
        $info['title'] = $lang['NOT_FOUND'];
        $info['src'] = 'not_found.html';
        break;
}

$require = file_exists($html_dir . $info['src']) ? $html_dir . $info['src'] : false;

$template->assign_vars([
    'PAGE_TITLE' => mb_strtoupper($info['title'], 'UTF-8'),
    'REQUIRE' => $require ? file_get_contents($require) : $lang['NOT_FOUND'],
]);

print_page('info.tpl', 'simple');
