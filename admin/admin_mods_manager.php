<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!empty($setmodules)) {
    $module['MODS']['MODS_MANAGER'] = basename(__FILE__);
    return;
}

require __DIR__ . '/pagestart.php';

// Install modification
if (isset($_POST['submit'])) {
    if (!isset($_FILES['xml_file']) || $_FILES['xml_file']['error'] != 0) {
        bb_die('Ошибка...');
    }

    $file_name = $_FILES['xml_file']['name'];
    $temp_path = $_FILES['xml_file']['tmp_name'];

    if (pathinfo($file_name, PATHINFO_EXTENSION) !== 'xml') {
        bb_die('Ошибка...');
    }

    if (move_uploaded_file($temp_path, VQMOD_DIR . '/xml/' . $file_name)) {
        bb_die('Модификация успешно установлена!');
    }
}

// Modification list
$xmlFiles = glob(VQMOD_DIR . '/xml/*.xml');
$files_count = 0;
foreach ($xmlFiles as $file) {
    $files_count++;
    $row_class = ($files_count % 2) ? 'row1' : 'row2';

    $xml = simplexml_load_file($file);
    $template->assign_block_vars('modifications_list', [
        'ROW_NUMBER' => $files_count,
        'ROW_CLASS' => $row_class,
        'MOD_NAME' => $xml->meta->name,
        'MOD_AUTHOR' => $xml->meta->author,
        'MOD_VERSION' => $xml->meta->version,
        'MOD_LICENSE' => $xml->meta->license,
        'MOD_COMPATIBILITY' => $xml->meta->compacibility
    ]);
}

$template->assign_vars([]);

print_page('admin_mods_manager.tpl', 'admin');
