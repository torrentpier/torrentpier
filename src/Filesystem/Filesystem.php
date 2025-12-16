<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Filesystem;

use Illuminate\Filesystem\Filesystem as IlluminateFilesystem;
use Illuminate\Support\LazyCollection;
use SplFileInfo;

/**
 * Filesystem wrapper around Illuminate\Filesystem with TorrentPier-specific features.
 *
 * @method bool exists(string $path)
 * @method bool missing(string $path)
 * @method string get(string $path, bool $lock = false)
 * @method array json(string $path, int $flags = 0, bool $lock = false)
 * @method LazyCollection lines(string $path)
 * @method string|false hash(string $path, string $algorithm = 'md5')
 * @method int|bool put(string $path, string $contents, bool $lock = false)
 * @method void replace(string $path, string $content, ?int $mode = null)
 * @method void replaceInFile(array|string $search, array|string $replace, string $path)
 * @method int|bool prepend(string $path, string $data)
 * @method int|bool append(string $path, string $data, bool $lock = false)
 * @method string|bool chmod(string $path, ?int $mode = null)
 * @method bool delete(string|array $paths)
 * @method bool move(string $path, string $target)
 * @method bool copy(string $path, string $target)
 * @method bool|null link(string $target, string $link)
 * @method void relativeLink(string $target, string $link)
 * @method string name(string $path)
 * @method string basename(string $path)
 * @method string dirname(string $path)
 * @method string extension(string $path)
 * @method string|null guessExtension(string $path)
 * @method string type(string $path)
 * @method string|false mimeType(string $path)
 * @method int size(string $path)
 * @method int lastModified(string $path)
 * @method bool isDirectory(string $directory)
 * @method bool isEmptyDirectory(string $directory, bool $ignoreDotFiles = false)
 * @method bool isReadable(string $path)
 * @method bool isWritable(string $path)
 * @method bool hasSameHash(string $firstFile, string $secondFile)
 * @method bool isFile(string $file)
 * @method array glob(string $pattern, int $flags = 0)
 * @method SplFileInfo[] files(string $directory, bool $hidden = false, array|string|int $depth = 0)
 * @method SplFileInfo[] allFiles(string $directory, bool $hidden = false)
 * @method SplFileInfo[] directories(string $directory, array|string|int $depth = 0)
 * @method string[] allDirectories(string $directory)
 * @method bool makeDirectory(string $path, int $mode = 0755, bool $recursive = false, bool $force = false)
 * @method bool moveDirectory(string $from, string $to, bool $overwrite = false)
 * @method bool copyDirectory(string $directory, string $destination, ?int $options = null)
 * @method bool deleteDirectory(string $directory, bool $preserve = false)
 * @method bool deleteDirectories(string $directory)
 * @method bool cleanDirectory(string $directory)
 *
 * @see IlluminateFilesystem
 */
readonly class Filesystem
{
    public function __construct(private IlluminateFilesystem $fs) {}

    public function __call(string $method, array $args): mixed
    {
        return $this->fs->{$method}(...$args);
    }

    /**
     * Append content with automatic rotation when the size limit is reached.
     */
    public function appendWithRotation(
        string $path,
        string $content,
        int    $maxSize = 1048576,
        bool   $lock = true,
    ): int|false {
        clearstatcache(true, $path);

        if ($maxSize > 0 && $this->fs->exists($path) && $this->fs->size($path) >= $maxSize) {
            $info = pathinfo($path);
            $rotatedName = \sprintf(
                '%s/%s_[old]_%s_%d.%s',
                $info['dirname'],
                $info['filename'],
                date('Y-m-d_H-i-s'),
                getmypid(),
                $info['extension'] ?? 'log',
            );

            clearstatcache(true, $rotatedName);
            if (!$this->fs->exists($rotatedName)) {
                $this->fs->move($path, $rotatedName);
            }
        }

        $this->ensureDirectoryExists(\dirname($path));

        return $this->appendWithLock($path, $content, $lock);
    }

    /**
     * Write content to a file with locking support.
     */
    public function write(string $path, string $content, bool $lock = true): int|false
    {
        $this->ensureDirectoryExists(\dirname($path));

        if ($lock) {
            $fp = fopen($path, 'wb');
            if (!$fp) {
                return false;
            }

            flock($fp, LOCK_EX);
            $bytes = fwrite($fp, $content);
            flock($fp, LOCK_UN);
            fclose($fp);

            return $bytes;
        }

        $result = $this->fs->put($path, $content);

        return $result === false ? false : (int)$result;
    }

    /**
     * Ensure a directory exists with proper permissions (uses umask(0)).
     */
    public function ensureDirectoryExists(string $path, int $mode = 0777): bool
    {
        if ($this->fs->isDirectory($path)) {
            return $path !== '.' && $path !== '..' && $this->fs->isWritable($path);
        }

        $oldUmask = umask(0);
        $result = $this->ensureParentAndCreate($path, $mode);
        umask($oldUmask);

        return $result;
    }

    private function appendWithLock(string $path, string $content, bool $lock): int|false
    {
        $fp = fopen($path, 'ab+');
        if (!$fp) {
            return false;
        }

        if ($lock) {
            flock($fp, LOCK_EX);
        }

        $bytes = fwrite($fp, $content);

        if ($lock) {
            flock($fp, LOCK_UN);
        }

        fclose($fp);

        return $bytes;
    }

    private function ensureParentAndCreate(string $path, int $mode): bool
    {
        if ($this->fs->isDirectory($path)) {
            return $this->fs->isWritable($path);
        }

        $parent = \dirname($path);
        if (!$this->ensureParentAndCreate($parent, $mode)) {
            return false;
        }

        return mkdir($path, $mode);
    }
}
