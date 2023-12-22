<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

/**
 * All Attachment Functions needed to determine Special Files/Dimensions
 */

/**
 * Read Long Int (4 Bytes) from File
 */
function read_longint($fp)
{
    $data = fread($fp, 4);

    $value = ord($data[0]) + (ord($data[1]) << 8) + (ord($data[2]) << 16) + (ord($data[3]) << 24);
    if ($value >= 4294967294) {
        $value -= 4294967296;
    }

    return $value;
}

/**
 * Read Word (2 Bytes) from File - Note: It's an Intel Word
 */
function read_word($fp)
{
    $data = fread($fp, 2);

    return ord($data[1]) * 256 + ord($data[0]);
}

/**
 * Read Byte
 */
function read_byte($fp)
{
    $data = fread($fp, 1);

    return ord($data);
}
