<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2026 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

$info = [];
$show = request()->getString('show');

switch ($show) {
    case 'advert':
        $info['title'] = __('ADVERT');
        $info['src'] = 'advert.twig';
        break;

    case 'copyright_holders':
        $info['title'] = __('COPYRIGHT_HOLDERS');
        $info['src'] = 'copyright_holders.twig';
        break;

    case 'user_agreement':
        $info['title'] = __('USER_AGREEMENT');
        $info['src'] = 'user_agreement.twig';
        break;

    default:
    case 'not_found':
        $info['title'] = __('NOT_FOUND');
        $info['src'] = 'not_found.twig';
        break;
}

$twig = template()->getTwig();
$templateName = '@lang/' . $info['src'];
$require = $twig->getLoader()->exists($templateName) ? $twig->render($templateName) : __('NOT_FOUND');

print_page('info.twig', 'simple', variables: [
    'PAGE_TITLE' => mb_strtoupper($info['title'], DEFAULT_CHARSET),
    'REQUIRE' => $require,
]);
