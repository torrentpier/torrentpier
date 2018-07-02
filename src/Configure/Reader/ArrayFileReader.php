<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2018 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Configure\Reader;

use TorrentPier\Configure\Exception\FileNotExistException;
use TorrentPier\Configure\Exception\ReadUnavailableException;

/**
 * Class ArrayFileReader
 * @package TorrentPier\Configure\Reader
 */
class ArrayFileReader extends ArrayReader
{
    /**
     * ArrayFileConfig constructor.
     *
     * @param string $pathToFile
     * @param string $name
     *
     * @throws FileNotExistException
     * @throws ReadUnavailableException
     */
    public function __construct($pathToFile, $name = 'config')
    {
        ${$name} = [];

        if (!file_exists($pathToFile)) {
            throw new FileNotExistException();
        }

        if (!is_readable($pathToFile)) {
            throw new ReadUnavailableException();
        }

        /** @noinspection PhpIncludeInspection */
        require $pathToFile;

        parent::__construct(${$name});
    }
}
