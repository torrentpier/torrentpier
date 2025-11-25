<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Image;

use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

/**
 * Centralized image processing service
 */
class ImageService
{
    private static ?ImageManager $manager = null;

    /**
     * Get ImageManager instance (singleton)
     */
    public static function getManager(): ImageManager
    {
        if (self::$manager === null) {
            self::$manager = new ImageManager(new GdDriver());
        }
        return self::$manager;
    }

    /**
     * Read image from a file
     */
    public static function read(string $path): ImageInterface
    {
        return self::getManager()->read($path);
    }

    /**
     * Create a new blank image
     */
    public static function create(int $width, int $height): ImageInterface
    {
        return self::getManager()->create($width, $height);
    }
}
