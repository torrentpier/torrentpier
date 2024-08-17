<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    $module['MODS']['INSTALL_MODS_XML'] = basename(__FILE__);
    return;
}

require __DIR__ . '/pagestart.php';

$template->assign_vars([
]);

print_page('admin_install_mod.tpl', 'admin');
