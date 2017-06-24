<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2017 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'terms');
define('BB_ROOT', './');
require __DIR__ . '/common.php';
require INC_DIR . '/bbcode.php';

// Start session management
$user->session_start();

if (!$bb_cfg['terms'] && !IS_ADMIN) {
    redirectToUrl('index.php');
}

$template->assign_vars(array(
    'TERMS_EDIT' => bbcode2html(sprintf($lang['TERMS_EMPTY_TEXT'], $domain_name)),
    'TERMS_HTML' => bbcode2html($bb_cfg['terms']),
));

print_page('terms.tpl');
