<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileObject;

/**
 * Class IntegrityChecker
 * @package TorrentPier
 */
class IntegrityChecker
{
    /**
     * Separator between file path and hash
     *
     * @var string
     */
    private string $checksumSeparator = ' ';

    /**
     * List of files that will be ignored
     *
     * @var array
     */
    private array $ignoreList;

    /**
     * Constructor
     */
    public function __construct()
    {
        $ignoreList = [
            '.env.example',
            '.htaccess',
            'robots.txt',
            'install.php',
            'favicon.png',
            'composer.json',
            'composer.lock',
            hide_bb_path(CHECKSUMS_FILE),
            hide_bb_path(BB_ENABLED),
            'library/config.php',
            'library/defines.php',
            'styles/images/logo/logo.png'
        ];

        $this->ignoreList = $ignoreList;
    }

    /**
     * @throws Exception
     */
    public function generateChecksumFile($rootDir = BB_ROOT, $savePath = CHECKSUMS_FILE): bool
    {
        $checksumFile = fopen($savePath, 'w+');
        if (!$checksumFile) {
            return false;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootDir));
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                $filePath = $fileInfo->getPathname();

                $hash = hash_file('md5', $filePath);
                if ($hash === false) {
                    throw new Exception('Failed to get file checksum: ' . $filePath);
                }

                fwrite($checksumFile, $hash . $this->checksumSeparator . $filePath . PHP_EOL);
            }
        }

        fclose($checksumFile);
        return true;
    }

    /**
     * Reading file with checksums
     *
     * @return array
     * @throws Exception
     */
    public function readChecksumFile(): array
    {
        if (!is_file(CHECKSUMS_FILE)) {
            throw new Exception('Checksum file not found: ' . CHECKSUMS_FILE);
        }

        $checksumFile = new SplFileObject(CHECKSUMS_FILE, 'r');
        $checksumFile->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        $filesList = [];
        $ignoreFiles = $this->ignoreList;

        foreach ($checksumFile as $line) {
            $parts = explode($this->checksumSeparator, $line);

            if (!isset($parts[0]) || !isset($parts[1])) {
                // Skip end line
                break;
            }

            if (!empty($ignoreFiles) && in_array($parts[1], $ignoreFiles)) {
                // Skip files from "Ignoring list"
                continue;
            }

            $filesList[] = [
                'path' => trim($parts[1]),
                'hash' => trim($parts[0])
            ];
        }

        return $filesList;
    }
}
