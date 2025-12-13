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

use DateTime;
use FeedIo\Adapter\Http\Client;
use FeedIo\Feed;
use FeedIo\FeedIo;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Log\NullLogger;
use Throwable;
use TorrentPier\Feed\Exception\FeedGenerationException;
use TorrentPier\Feed\Provider\FeedProviderInterface;

/**
 * Feed generator
 * Generates Atom feeds using a feed-io library with caching support
 */
final class FeedGenerator
{
    private FeedIo $feedIo;

    public function __construct()
    {
        try {
            // Find PSR-18 HTTP client
            $psr18Client = Psr18ClientDiscovery::find();
            // Wrap PSR-18 client in FeedIo adapter
            $client = new Client($psr18Client);
        } catch (Throwable $e) {
            throw new FeedGenerationException('HTTP client discovery failed: ' . $e->getMessage(), 0, $e);
        }

        // Create a logger (use NullLogger to avoid dependencies)
        $logger = new NullLogger;

        // Create a FeedIo instance
        $this->feedIo = new FeedIo($client, $logger);
    }

    /**
     * Generate Atom feed from a provider
     * Results are cached for performance unless TTL is 0 or negative
     *
     * @param FeedProviderInterface $provider Feed data provider
     * @throws FeedGenerationException
     * @return string Atom XML string
     */
    public function generate(FeedProviderInterface $provider): string
    {
        try {
            $cacheTtl = (int)config()->get('atom.cache_ttl', 600); // Default 10 minutes

            // If TTL is 0 or negative, disable caching (always generate fresh)
            if ($cacheTtl <= 0) {
                return $this->generateFeed($provider);
            }

            // Include the current locale in a cache key to ensure language-specific feeds
            $locale = lang()->currentLanguage;
            $cacheKey = $provider->getCacheKey() . ':lang=' . $locale;
            $cache = CACHE('bb_cache');

            // Try to get from the cache
            $cached = $cache->get($cacheKey);
            if ($cached === false || $cached === null) {
                // Generate and cache
                $cached = $this->generateFeed($provider);
                $cache->set($cacheKey, $cached, $cacheTtl);
            }

            return $cached;
        } catch (Throwable $e) {
            throw new FeedGenerationException(
                'Failed to generate feed: ' . $e->getMessage(),
                0,
                $e,
            );
        }
    }

    /**
     * Generate feed from provider (internal method)
     */
    private function generateFeed(FeedProviderInterface $provider): string
    {
        $feed = new Feed;
        $metadata = $provider->getMetadata();

        // Set feed metadata
        $feed->setTitle($metadata->title);
        $feed->setLink($metadata->link);
        $feed->setLastModified(DateTime::createFromImmutable($metadata->lastModified));

        // Add feed entries
        foreach ($provider->getEntries() as $entryData) {
            $entry = $feed->newItem();
            $entry->setTitle($entryData->title);
            $entry->setLink($entryData->link);
            $entry->setLastModified(DateTime::createFromImmutable($entryData->lastModified));

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
}
