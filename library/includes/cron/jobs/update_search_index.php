<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

$offset = 0;
$limit = 1000;
$total_indexed = 0;

do {
    $indexed = index_data_to_manticore($limit, $offset);
    if ($indexed === false) {
        echo "Error during indexing at offset $offset\n";
        break;
    }

    $total_indexed += $indexed;
    $offset += $limit;

    echo "Indexed $indexed records, total: $total_indexed\n";
    usleep(100000); // 0.1 секунды
} while ($indexed > 0);

echo "Indexing completed. Total records: $total_indexed\n";
