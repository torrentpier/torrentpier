<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

/**
 * Create thumbnail
 *
 * @param string $source
 * @param string $new_file
 * @return bool
 * @throws Exception
 */
function create_thumbnail(string $source, string $new_file): bool
{
    global $attach_config;

    // Get the file information
    $source = amod_realpath($source);
    $min_filesize = (int)$attach_config['img_min_thumb_filesize'];
    $img_filesize = file_exists($source) ? filesize($source) : false;

    // Checks the max allowed filesize
    if (!$img_filesize || $img_filesize <= $min_filesize) {
        return false;
    }

    // Making the thumbnail image
    $image = new \claviska\SimpleImage();
    $image
        ->fromFile($source)
        ->autoOrient()
        ->resize(200)
        ->toFile($new_file);

    // Check the thumbnail existence after creating
    if (!file_exists($new_file)) {
        return false;
    }

    return true;
}
