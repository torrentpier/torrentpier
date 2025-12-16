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


$preview = request()->post->has('preview');

if (request()->post->has('post') && (config()->get('terms') !== request()->post->get('message'))) {
    bb_update_config(['terms' => request()->post->get('message')]);
    bb_die(__('TERMS_UPDATED_SUCCESSFULLY') . '<br /><br />' . sprintf(__('CLICK_RETURN_TERMS_CONFIG'), '<a href="admin_terms.php">', '</a>') . '<br /><br />' . sprintf(__('CLICK_RETURN_ADMIN_INDEX'), '<a href="index.php?pane=right">', '</a>'));
}

template()->assign_vars([
    'S_ACTION' => 'admin_terms.php',
    'EXT_LINK_NW' => config()->get('ext_link_new_win'),
    'MESSAGE' => $preview ? request()->post->get('message') : config()->get('terms'),
    'PREVIEW_HTML' => $preview ? bbcode2html(request()->post->get('message')) : '',
]);

print_page('admin_terms.tpl', 'admin');
