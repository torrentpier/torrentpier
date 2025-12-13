<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Forum;

use TorrentPier\Cache\DatastoreManager;

/**
 * Forum tree - provides cached access to forum hierarchy data
 */
class ForumTree
{
    private ?array $data = null;

    public function __construct(
        private readonly DatastoreManager $datastore,
    ) {}

    /**
     * Get forum tree data from datastore
     */
    public function get(): array
    {
        if ($this->data === null) {
            $data = $this->datastore->get('cat_forums');
            if ($data === false) {
                $this->datastore->update('cat_forums');
                $data = $this->datastore->get('cat_forums');
            }
            $this->data = $data ?: [];
        }

        return $this->data;
    }

    /**
     * Rebuild forum tree data from a database and clear cache
     */
    public function refresh(): void
    {
        $this->datastore->update('cat_forums');
        $this->data = null;
    }
}
