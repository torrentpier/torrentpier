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

use TorrentPier\Image\ImageService;

/**
 * Create thumbnail
 *
 * @param string $source
 * @param string $newFile
 * @param string $mimeType
 * @return bool
 * @throws Exception
 */
function createThumbnail(string $source, string $newFile, string $mimeType): bool
{
    global $attach_config;

    // Check for source image existence
    if (!$source = realpath($source)) {
        return false;
    }

    // Checks the max allowed filesize
    $min_filesize = (int)$attach_config['img_min_thumb_filesize'];
    if (!filesize($source) || filesize($source) <= $min_filesize) {
        return false;
    }

    // Making the thumbnail image
    try {
        ImageService::read($source)
            ->scaleDown(width: 150)
            ->save($newFile, quality: 85);
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }

    // Check the thumbnail existence after creating
    if (!is_file($newFile)) {
        return false;
    }

    return true;
}
