<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine.
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 *
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 *
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */
if (!empty($setmodules)) {
    $module['MODS']['ROBOTS_TXT_EDITOR_TITLE'] = basename(__FILE__);

    return;
}

require __DIR__.'/pagestart.php';

$robots_file = BB_ROOT.'robots.txt';

// Обработка сохранения
if (isset($_POST['save'])) {
    $robots_txt = $_POST['robots_txt'] ?? '';

    if (!is_writable($robots_file) && is_file($robots_file)) {
        bb_die('File robots.txt is not writable #1');
    }

    $bytes = file_put_contents($robots_file, $robots_txt);
    if ($bytes === false) {
        bb_die('Could not write robots.txt #2');
    }

    bb_die($lang['ROBOTS_TXT_UPDATED_SUCCESSFULLY'].'<br /><br />'.sprintf($lang['CLICK_RETURN_ROBOTS_TXT_CONFIG'], '<a href="admin_robots.php">', '</a>').'<br /><br />'.sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
}

$current_content = '';
if (is_file($robots_file)) {
    $current_content = file_get_contents($robots_file);
}

$template->assign_vars([
    'S_ACTION'   => 'admin_robots.php',
    'ROBOTS_TXT' => htmlCHR($current_content),
]);

print_page('admin_robots.tpl', 'admin');
