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

$imagick = '';

/**
 * Calculate the needed size for Thumbnail
 */
function get_img_size_format($width, $height)
{
    // Maximum Width the Image can take
    $max_width = 400;

    if ($width > $height) {
        return [
            round($width * ($max_width / $width)),
            round($height * ($max_width / $width))
        ];
    }

    return [
        round($width * ($max_width / $height)),
        round($height * ($max_width / $height))
    ];
}

/**
 * Get supported image types
 */
function get_supported_image_types(int $type): int
{
    // Check GD extension installed
    if (!extension_loaded('gd')) {
        return false;
    }

    // Get supported image types
    $format = imagetypes();
    $new_type = null;

    // Select target type
    switch ($type) {
        case IMAGETYPE_GIF:
            $new_type = ($format & IMG_GIF) ? IMG_GIF : 0;
            break;
        case IMAGETYPE_JPEG:
            $new_type = ($format & IMG_JPG) ? IMG_JPG : 0;
            break;
        case IMAGETYPE_PNG:
            $new_type = ($format & IMG_PNG) ? IMG_PNG : 0;
            break;
        case IMAGETYPE_BMP:
            $new_type = ($format & IMG_BMP) ? IMG_BMP : 0;
            break;
        case IMAGETYPE_WEBP:
            $new_type = ($format & IMG_WEBP) ? IMG_WEBP : 0;
            break;
    }

    return $new_type;
}

/**
 * Create thumbnail
 * @throws Exception
 */
function create_thumbnail($source, $new_file, $mimetype)
{
    global $attach_config, $imagick;
    $image = null;

    $source = amod_realpath($source);
    $min_filesize = (int)$attach_config['img_min_thumb_filesize'];
    $img_filesize = (@file_exists($source)) ? @filesize($source) : false;

    if (!$img_filesize || $img_filesize <= $min_filesize) {
        return false;
    }

    [$width, $height, $type] = getimagesize($source);

    if (!$width || !$height || !$type) {
        return false;
    }

    [$new_width, $new_height] = get_img_size_format($width, $height);

    if ($type = get_supported_image_types($type)) {
        switch ($type) {
            case IMG_GIF:
                $image = imagecreatefromgif($source);
                break;
            case IMG_JPG:
                $image = imagecreatefromjpeg($source);
                break;
            case IMG_PNG:
                $image = imagecreatefrompng($source);
                break;
            case IMG_BMP:
                $image = imagecreatefrombmp($source);
                break;
            case IMG_WEBP:
                $image = imagecreatefromwebp($source);
                break;
        }

        $new_image = imagecreatetruecolor($new_width, $new_height);

        // Set transparency options for GIFs and PNGs
        if ($type == IMG_GIF || $type == IMG_PNG) {
            // Make image transparent
            imagecolortransparent($new_image, imagecolorallocate($new_image, 0, 0, 0));

            // Additional settings for PNGs
            if ($type == IMG_PNG) {
                imagealphablending($new_image, false);
                imagesavealpha($new_image, true);
            }
        }

        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        switch ($type) {
            case IMG_GIF:
                imagegif($new_image, $new_file);
                break;
            case IMG_JPG:
                imagejpeg($new_image, $new_file, 90);
                break;
            case IMG_PNG:
                imagepng($new_image, $new_file);
                break;
            case IMG_BMP:
                imagebmp($new_image, $new_file);
                break;
            case IMG_WEBP:
                imagewebp($new_image, $new_file);
                break;
        }

        imagedestroy($new_image);
    }

    if (!file_exists($new_file)) {
        return false;
    }

    chmod($new_file, 0664);
    return true;
}
