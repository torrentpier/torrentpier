<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2018 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Configure\Reader;

/**
 * Class ArrayReader
 * @package TorrentPier\Configure\Reader
 */
class ArrayReader extends \ArrayIterator implements ReaderInterface
{
    /**
     * Compile application configuration array.
     *
     * @return array
     */
    public function compile(): array
    {
        return $this->getArrayCopy();
    }
}
