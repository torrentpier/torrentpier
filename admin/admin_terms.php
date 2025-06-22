<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    $module['GENERAL']['TERMS'] = basename(__FILE__);
    return;
}

require __DIR__ . '/pagestart.php';
require INC_DIR . '/bbcode.php';

$preview = isset($_POST['preview']);

if (isset($_POST['post']) && (tp_config()->get('terms') !== $_POST['message'])) {
    bb_update_config(['terms' => $_POST['message']]);
    bb_die($lang['TERMS_UPDATED_SUCCESSFULLY'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_TERMS_CONFIG'], '<a href="admin_terms.php">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
}

$template->assign_vars([
    'S_ACTION' => 'admin_terms.php',
    'EXT_LINK_NW' => tp_config()->get('ext_link_new_win'),
    'MESSAGE' => $preview ? $_POST['message'] : tp_config()->get('terms'),
    'PREVIEW_HTML' => $preview ? bbcode2html($_POST['message']) : '',
]);

print_page('admin_terms.tpl', 'admin');
