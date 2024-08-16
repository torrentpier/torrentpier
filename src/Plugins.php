<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

/**
 * Class Plugins
 * @package TorrentPier
 */
class Plugins
{
    private object $pluginXml;

    public function __construct(string $path)
    {
        $this->pluginXml = simplexml_load_file($path);
    }

    public function removePlugin()
    {
    }

    private function makeBackup()
    {
    }

    private function getMetaInfo()
    {
    }
}
