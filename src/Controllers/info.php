<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

$info = [];
$htmlDir = LANG_DIR . 'html/';
$show = isset($_REQUEST['show']) ? (string)$_REQUEST['show'] : '';

switch ($show) {
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

$require = is_file($htmlDir . $info['src']) ? ($htmlDir . $info['src']) : false;

$template->assign_vars([
    'PAGE_TITLE' => mb_strtoupper($info['title'], DEFAULT_CHARSET),
    'REQUIRE' => $require ? file_get_contents($require) : $lang['NOT_FOUND'],
]);

print_page('info.tpl', 'simple');
