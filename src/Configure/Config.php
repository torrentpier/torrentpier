<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2018 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Configure;

use TorrentPier\Configure\Reader\ReaderInterface;

/**
 * Class Config
 * @package TorrentPier\Configure
 */
class Config
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var ReaderInterface[]|array
     */
    private $loaders;

    /**
     * Config constructor.
     *
     * @param ReaderInterface[]|array $loaders
     */
    public function __construct(array $loaders)
    {
        $this->loaders = $loaders;
        $this->configure();
    }

    /**
     * Get all config keys.
     *
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->data);
    }

    /**
     * Get value by key from config.
     *
     * @param $key
     *
     * @return mixed
     */
    public function get($key)
    {
        $keys = explode('.', $key);
        $countKeys = count($keys);

        $tmpData = $this->data;

        for ($i = 0; $i < $countKeys; $i++) {
            $item = (string)$keys[$i];

            if (!array_key_exists($item, $tmpData)) {
                break;
            }

            if ($i < ($countKeys - 1) && \is_array($tmpData[$item])) {
                $tmpData = $tmpData[$item];
            } else {
                return $tmpData[$item];
            }
        }

        return null;
    }

    /**
     * Configure application.
     */
    protected function configure(): void
    {
        foreach ($this->loaders as $loader) {
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $this->data = array_merge($this->data, $loader->compile());
        }
    }
}
