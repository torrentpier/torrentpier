<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'terms');

// Skip loading common.php if already loaded (when run through routing system)
if (!defined('IN_TORRENTPIER')) {
    require __DIR__ . '/../common.php';
}
require INC_DIR . '/bbcode.php';

// Start session management
$user->session_start();

if (!tp_config()->get('terms') && !IS_ADMIN) {
    redirect('index.php');
}

$template->assign_vars([
    'TERMS_EDIT' => bbcode2html(sprintf($lang['TERMS_EMPTY_TEXT'], make_url('admin/admin_terms.php'))),
    'TERMS_HTML' => bbcode2html(tp_config()->get('terms')),
]);

print_page('terms.tpl');
