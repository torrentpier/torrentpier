<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2024 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use Nemorize\Indexnow\Exceptions\IndexnowException;

/**
 * Class IndexNow
 * @package TorrentPier
 */
class IndexNow
{
    /**
     * IndexNow instance
     *
     * @var \Nemorize\Indexnow\Indexnow
     */
    private \Nemorize\Indexnow\Indexnow $indexNow;

    /**
     * IndexNow Key-file extension
     *
     * @var string
     */
    public static string $keyFileExtension = '.txt';

    /**
     * Log filename
     *
     * @var string
     */
    private static string $logFile = 'index_now';

    /**
     * Available hosts
     *
     * @var array|string[]
     */
    public array $hosts = [
        'yandex' => 'yandex.com',
        'bing' => 'bing.com',
    ];

    public function __construct()
    {
        global $bb_cfg;

        $this->indexNow = new \Nemorize\Indexnow\Indexnow();
        $this->indexNow->setKey($bb_cfg['indexnow_key']);
        if (in_array($bb_cfg['indexnow_settings']['host'], array_keys($this->hosts))) {
            $this->indexNow->setHost($this->hosts[$bb_cfg['indexnow_settings']['host']]);
        } else {
            bb_log("IndexNow (ERROR): Invalid host: {$bb_cfg['indexnow_settings']['host']}\n", self::$logFile);
            bb_die('Invalid host: ' . $bb_cfg['indexnow_settings']['host']);
        }
        $this->indexNow->setKeyLocation(FULL_URL . $bb_cfg['indexnow_key'] . self::$keyFileExtension);
    }

    /**
     * Submit page to IndexNow
     *
     * @param string $url
     * @return void
     */
    public function submit(string $url): void
    {
        try {
            $this->indexNow->submit($url);
        } catch (IndexnowException $e) {
            bb_log("IndexNow (ERROR) [$url]: Message: {$e->getMessage()}\n", self::$logFile);
            bb_die($e->getMessage());
        }
    }
}
