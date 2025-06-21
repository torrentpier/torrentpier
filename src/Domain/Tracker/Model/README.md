# Tracker Domain Models

Contains aggregate roots and entities for the BitTorrent tracker:
- `Torrent`: Aggregate root for torrent management
- `Peer`: Entity representing a BitTorrent peer
- `TorrentStatistics`: Value object for torrent stats

Example:
```php
class Torrent extends AggregateRoot
{
    public function announce(Peer $peer, AnnounceEvent $event): void
    {
        // Business logic for handling announces
    }
}
```