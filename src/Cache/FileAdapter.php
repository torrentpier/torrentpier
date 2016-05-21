<?php

namespace TorrentPier\Cache;

use Doctrine\Common\Cache\FilesystemCache;

/**
 * Class FileAdapter
 * @package TorrentPier\Cache
 */
class FileAdapter extends Adapter
{
    /**
     * @var string The cache directory.
     */
    protected $directory;

    /**
     * @var string The cache file extension.
     */
    protected $extension = FilesystemCache::EXTENSION;

    /**
     * @var int The cache file umask.
     */
    protected $umask = 0002;

    /**
     * {@inheritdoc}
     */
    public function getProvider()
    {
        if (!$this->provider) {
            $this->provider = new FilesystemCache($this->directory, $this->extension, $this->umask);
        }

        return $this->provider;
    }

    /**
     * {@inheritdoc}
     */
    protected function getType()
    {
        return 'Filesystem Cache';
    }

    /**
     * Set directory path for cache.
     *
     * @param $directory
     */
    protected function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    /**
     * Set extension file.
     *
     * @param $extension
     */
    protected function setExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * Set umask file.
     *
     * @param $umask
     */
    protected function setUmask($umask)
    {
        $this->umask = $umask;
    }
}
