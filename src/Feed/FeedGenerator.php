<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Feed;

use Exception;
use FeedIo\Feed;
use FeedIo\FeedIo;
use TorrentPier\Feed\Exception\FeedGenerationException;
use TorrentPier\Feed\Provider\FeedProviderInterface;

/**
 * Feed generator (singleton)
 * Generates Atom feeds using a feed-io library with caching support
 */
class FeedGenerator
{
    private static ?self $instance = null;
    private FeedIo $feedIo;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        // Find PSR-18 HTTP client
        $psr18Client = \Http\Discovery\Psr18ClientDiscovery::find();

        // Wrap PSR-18 client in FeedIo adapter
        $client = new \FeedIo\Adapter\Http\Client($psr18Client);

        // Create a logger (use NullLogger to avoid dependencies)
        $logger = new \Psr\Log\NullLogger();

        // Create a FeedIo instance
        $this->feedIo = new FeedIo($client, $logger);
    }

    /**
     * Get a singleton instance
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Generate Atom feed from a provider
     * Results are cached for performance
     *
     * @param FeedProviderInterface $provider Feed data provider
     * @return string Atom XML string
     * @throws FeedGenerationException
     */
    public function generate(FeedProviderInterface $provider): string
    {
        $cacheKey = $provider->getCacheKey();
        $cacheTtl = config()->get('atom.cache_ttl') ?? 600; // Default 10 minutes

        try {
            $cache = CACHE('bb_cache');

            // Try to get from the cache
            if (!$cached = $cache->get($cacheKey)) {
                // Generate and cache
                $cached = $this->generateFeed($provider);
                $cache->set($cacheKey, $cached, $cacheTtl);
            }

            return $cached;
        } catch (\Throwable $e) {
            throw new FeedGenerationException(
                "Failed to generate feed: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Generate feed from provider (internal method)
     *
     * @param FeedProviderInterface $provider
     * @return string
     */
    private function generateFeed(FeedProviderInterface $provider): string
    {
        $feed = new Feed();
        $metadata = $provider->getMetadata();

        // Set feed metadata
        $feed->setTitle($metadata->title);
        $feed->setLink($metadata->link);
        $feed->setLastModified($metadata->lastModified);

        // Add feed entries
        foreach ($provider->getEntries() as $entryData) {
            $entry = $feed->newItem();
            $entry->setTitle($entryData->title);
            $entry->setLink($entryData->link);
            $entry->setLastModified($entryData->lastModified);

            if ($entryData->description !== null) {
                $entry->setContent($entryData->description);
            }

            // Set author
            $author = $entry->newAuthor();
            $author->setName($entryData->author);
            $entry->setAuthor($author);

            $feed->add($entry);
        }

        // Format as Atom XML
        return $this->feedIo->format($feed, 'atom');
    }

    /**
     * Prevent cloning
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
