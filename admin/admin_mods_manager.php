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
$files_count = 0;
foreach (VQMod::$_modFileList as $file) {
    $files_count++;
    $row_class = ($files_count % 2) ? 'row1' : 'row2';

    $xml = simplexml_load_file($file);

    // Perform SQL queries
    $sql_queries = explode("\n", trim($xml->sql));
    $tempLine = '';
    foreach ($sql_queries as $line) {
        if (str_starts_with($line, '--') || $line == '') {
            continue;
        }

        $tempLine .= $line;
        if (str_ends_with(trim($line), ';')) {
            if (!DB()->query($tempLine)) {
                bb_die('Что то пошло не так');
            }
            $tempLine = '';
        }
    }

    $template->assign_block_vars('modifications_list', [
        'ROW_NUMBER' => $files_count,
        'ROW_CLASS' => $row_class,
        'MOD_NAME' => htmlCHR($xml->meta->name),
        'MOD_AUTHOR' => htmlCHR($xml->meta->author),
        'MOD_VERSION' => htmlCHR($xml->meta->version),
        'MOD_LICENSE' => htmlCHR($xml->meta->license),
        'MOD_COMPATIBILITY' => htmlCHR($xml->meta->compacibility)
    ]);
}

$template->assign_vars([]);

print_page('admin_mods_manager.tpl', 'admin');
