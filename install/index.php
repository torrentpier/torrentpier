<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_ROOT', './../');
define('IN_INSTALL', true);

require BB_ROOT . 'common.php';

set_time_limit(600);

$step = request_var('step', 'welcoming');

// Load SQL dump
$dump_path = BB_ROOT . 'install/sql/mysql.sql';

// Drop tables & Insert sql dump
switch ($step) {
    case 'welcoming':
        break;
    case 'insert_dump':
        $temp_line = '';
        foreach (file($dump_path) as $line) {
            if (str_starts_with($line, '--') || $line == '') {
                continue;
            }

            $temp_line .= $line;
            if (str_ends_with(trim($line), ';')) {
                DB()->query($temp_line);
                $temp_line = '';
            }
        }
        break;
    default:
        bb_simple_die('Invalid step: ' . $step);
        break;
}
