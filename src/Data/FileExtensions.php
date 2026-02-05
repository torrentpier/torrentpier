<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Data;

/**
 * File extension ID mappings.
 * WARNING: Do not modify IDs - they are stored in the database!
 */
final class FileExtensions
{
    public const array ID_TO_EXT = [
        1 => 'gif',
        2 => 'gz',
        3 => 'jpg',
        4 => 'png',
        5 => 'rar',
        6 => 'tar',
        8 => 'torrent',
        9 => 'zip',
        10 => '7z',
        11 => 'bmp',
        12 => 'webp',
        13 => 'avif',
        14 => 'm3u',
    ];

    /**
     * Get all file extension mappings.
     *
     * @return array<int, string>
     */
    public static function all(): array
    {
        return self::ID_TO_EXT;
    }

    /**
     * Get file extension by ID.
     */
    public static function getExtension(int $id): ?string
    {
        return self::ID_TO_EXT[$id] ?? null;
    }

    /**
     * Get ID by file extension.
     */
    public static function getId(string $ext): ?int
    {
        $key = array_search(strtolower($ext), self::ID_TO_EXT, true);
        return $key !== false ? $key : null;
    }
}
