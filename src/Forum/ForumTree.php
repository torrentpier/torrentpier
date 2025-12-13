<?php

declare(strict_types=1);

namespace TorrentPier\Forum;

/**
 * Forum tree singleton - provides cached access to forum hierarchy data
 */
class ForumTree
{
    private static ?self $instance = null;

    private ?array $data = null;

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get forum tree data from datastore
     */
    public function get(): array
    {
        if ($this->data === null) {
            $data = datastore()->get('cat_forums');
            if ($data === false) {
                datastore()->update('cat_forums');
                $data = datastore()->get('cat_forums');
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
        datastore()->update('cat_forums');
        $this->data = null;
    }
}
